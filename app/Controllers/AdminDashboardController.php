<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use App\Models\Table;
use PDO;

final class AdminDashboardController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();

        // 1. Kích hoạt tự động hủy các bàn đến muộn theo số phút cài đặt
        $bookingModel = new Booking($db);
        $bookingModel->autoReleaseLateBookings();

        // 2. Thống kê nhanh hôm nay
        $today = date('Y-m-d');

        // Doanh thu hôm nay từ orders đã thanh toán hoặc hoàn tất
        $revStmt = $db->prepare(
            "SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE DATE(created_at) = :today AND (payment_status = 'paid' OR status = 'completed')"
        );
        $revStmt->execute(['today' => $today]);
        $todayRevenue = (float)$revStmt->fetchColumn();

        // Lượt đặt bàn hôm nay
        $bookCountStmt = $db->prepare(
            "SELECT COUNT(*) FROM bookings WHERE booking_date = :today"
        );
        $bookCountStmt->execute(['today' => $today]);
        $todayBookingsCount = (int)$bookCountStmt->fetchColumn();

        // Đếm trạng thái bàn hiện tại
        $tableStmt = $db->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('in_use', 'serving', 'awaiting_payment') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'empty' THEN 1 ELSE 0 END) as empty,
                SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved,
                SUM(CASE WHEN status = 'cleaning' THEN 1 ELSE 0 END) as cleaning,
                SUM(CASE WHEN status = 'locked' THEN 1 ELSE 0 END) as locked
             FROM restaurant_tables"
        );
        $tableStats = $tableStmt->fetch(PDO::FETCH_ASSOC);

        // Món hoàn tất hôm nay
        $dishesDoneStmt = $db->prepare(
            "SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE DATE(created_at) = :today AND status IN ('served', 'ready')"
        );
        $dishesDoneStmt->execute(['today' => $today]);
        $dishesDoneCount = (int)$dishesDoneStmt->fetchColumn();

        // Top 5 món bán chạy nhất
        $topDishesStmt = $db->query(
            "SELECT dish_name, SUM(quantity) as qty, SUM(subtotal) as total 
             FROM order_items 
             WHERE status <> 'cancelled' 
             GROUP BY dish_name 
             ORDER BY qty DESC 
             LIMIT 5"
        );
        $topDishes = $topDishesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Đơn đặt bàn sắp tới trong ngày
        $upcomingStmt = $db->prepare(
            "SELECT b.*, t.name as table_name, t.code as table_code, t.is_vip, c.name as combo_name 
             FROM bookings b 
             LEFT JOIN restaurant_tables t ON t.id = b.table_id 
             LEFT JOIN combo_setups c ON c.id = b.combo_id 
             WHERE b.booking_date = :today 
             ORDER BY b.booking_time ASC 
             LIMIT 6"
        );
        $upcomingStmt->execute(['today' => $today]);
        $upcomingBookings = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

        // Danh sách bàn thực tế theo từng khu vực
        $tableModel = new Table($db);
        $floorData = $tableModel->getFloorPlanData();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Tổng quan Vận hành & Doanh thu — FOODIES Admin',
            'currentUser' => $currentUser,
            'todayRevenue' => $todayRevenue,
            'todayBookingsCount' => $todayBookingsCount,
            'tableStats' => $tableStats,
            'dishesDoneCount' => $dishesDoneCount,
            'topDishes' => $topDishes,
            'upcomingBookings' => $upcomingBookings,
            'floorData' => $floorData,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/admin');
    }

    public function reports(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();

        // 1. Tổng doanh thu toàn thời gian
        $totalRevStmt = $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE payment_status = 'paid' OR status = 'completed'");
        $totalRevenue = (float)$totalRevStmt->fetchColumn();

        // 2. Thống kê theo ngày trong 7 ngày gần nhất
        $dailyRevStmt = $db->query(
            "SELECT DATE(created_at) as order_date, COALESCE(SUM(grand_total), 0) as daily_total, COUNT(*) as order_count
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
               AND (payment_status = 'paid' OR status = 'completed')
             GROUP BY DATE(created_at)
             ORDER BY order_date ASC"
        );
        $dailyRevenues = $dailyRevStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Tỷ lệ đơn hàng (Hoàn thành vs Đã hủy)
        $orderRatioStmt = $db->query(
            "SELECT 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                COUNT(*) as total_orders
             FROM orders"
        );
        $orderRatio = $orderRatioStmt->fetch(PDO::FETCH_ASSOC);

        // 4. Món ăn bán chạy nhất (Top 10)
        $topDishesStmt = $db->query(
            "SELECT dish_name, SUM(quantity) as total_qty, SUM(subtotal) as total_sales
             FROM order_items
             WHERE status <> 'cancelled'
             GROUP BY dish_name
             ORDER BY total_qty DESC
             LIMIT 10"
        );
        $topDishes = $topDishesStmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Thống kê theo khu vực bàn
        $areaStatsStmt = $db->query(
            "SELECT a.name as area_name, COUNT(o.id) as total_orders, COALESCE(SUM(o.grand_total), 0) as total_revenue
             FROM dining_areas a
             JOIN restaurant_tables t ON t.area_id = a.id
             LEFT JOIN orders o ON o.table_id = t.id AND (o.payment_status = 'paid' OR o.status = 'completed')
             GROUP BY a.id, a.name
             ORDER BY total_revenue DESC"
        );
        $areaStats = $areaStatsStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('admin/reports/index', [
            'pageTitle' => 'Báo Cáo & Thống Kê Doanh Thu — FOODIES Admin',
            'currentUser' => $currentUser,
            'totalRevenue' => $totalRevenue,
            'dailyRevenues' => $dailyRevenues,
            'orderRatio' => $orderRatio,
            'topDishes' => $topDishes,
            'areaStats' => $areaStats,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/admin');
    }
}
