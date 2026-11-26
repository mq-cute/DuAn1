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

        // Doanh thu hôm qua để so sánh tăng trưởng
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $revYestStmt = $db->prepare(
            "SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE DATE(created_at) = :yesterday AND (payment_status = 'paid' OR status = 'completed')"
        );
        $revYestStmt->execute(['yesterday' => $yesterday]);
        $yesterdayRevenue = (float)$revYestStmt->fetchColumn();
        $growthPercent = $yesterdayRevenue > 0 
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1) 
            : ($todayRevenue > 0 ? 100.0 : 0.0);

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

        // Dữ liệu biểu đồ doanh thu 7 ngày qua cho Dashboard Chart.js
        $chart7DaysStmt = $db->query(
            "SELECT DATE(created_at) as order_date, COALESCE(SUM(grand_total), 0) as daily_total, COUNT(*) as order_count
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND (payment_status = 'paid' OR status = 'completed')
             GROUP BY DATE(created_at)
             ORDER BY order_date ASC"
        );
        $recent7DaysRaw = $chart7DaysStmt->fetchAll(PDO::FETCH_ASSOC);
        $recentDaysMap = [];
        foreach ($recent7DaysRaw as $row) {
            $recentDaysMap[$row['order_date']] = [
                'total' => (float)$row['daily_total'],
                'orders' => (int)$row['order_count']
            ];
        }

        // Tạo chuỗi 7 ngày liên tục kể cả ngày không có đơn
        $dashboardChartLabels = [];
        $dashboardChartTotals = [];
        $dashboardChartOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dashboardChartLabels[] = date('d/m', strtotime($d));
            $dashboardChartTotals[] = $recentDaysMap[$d]['total'] ?? 0;
            $dashboardChartOrders[] = $recentDaysMap[$d]['orders'] ?? 0;
        }

        // Tỷ lệ trạng thái đơn hàng toàn hệ thống
        $ratioStmt = $db->query(
            "SELECT 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status IN ('serving', 'ready', 'preparing') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                COUNT(*) as total
             FROM orders"
        );
        $orderRatioData = $ratioStmt->fetch(PDO::FETCH_ASSOC);

        // Danh sách bàn thực tế theo từng khu vực
        $tableModel = new Table($db);
        $floorData = $tableModel->getFloorPlanData();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Tổng quan Vận hành & Doanh thu — FOODIES Admin',
            'currentUser' => $currentUser,
            'todayRevenue' => $todayRevenue,
            'yesterdayRevenue' => $yesterdayRevenue,
            'growthPercent' => $growthPercent,
            'todayBookingsCount' => $todayBookingsCount,
            'tableStats' => $tableStats,
            'dishesDoneCount' => $dishesDoneCount,
            'topDishes' => $topDishes,
            'upcomingBookings' => $upcomingBookings,
            'floorData' => $floorData,
            'dashboardChartLabels' => $dashboardChartLabels,
            'dashboardChartTotals' => $dashboardChartTotals,
            'dashboardChartOrders' => $dashboardChartOrders,
            'orderRatioData' => $orderRatioData,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/admin');
    }

    public function reports(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();

        // 1. Xác định kỳ báo cáo (Period filter)
        $period = isset($_GET['period']) && is_string($_GET['period']) ? trim($_GET['period']) : '7days';
        $fromDate = isset($_GET['from']) && is_string($_GET['from']) ? trim($_GET['from']) : '';
        $toDate = isset($_GET['to']) && is_string($_GET['to']) ? trim($_GET['to']) : '';

        $todayStr = date('Y-m-d');
        switch ($period) {
            case 'today':
                $startDate = $todayStr;
                $endDate = $todayStr;
                $periodName = 'Hôm nay (' . date('d/m/Y') . ')';
                break;
            case '30days':
                $startDate = date('Y-m-d', strtotime('-29 days'));
                $endDate = $todayStr;
                $periodName = '30 ngày qua';
                break;
            case 'this_month':
                $startDate = date('Y-m-01');
                $endDate = $todayStr;
                $periodName = 'Tháng này (' . date('m/Y') . ')';
                break;
            case 'custom':
                $startDate = !empty($fromDate) ? $fromDate : date('Y-m-d', strtotime('-6 days'));
                $endDate = !empty($toDate) ? $toDate : $todayStr;
                $periodName = 'Từ ' . date('d/m/Y', strtotime($startDate)) . ' đến ' . date('d/m/Y', strtotime($endDate));
                break;
            case '7days':
            default:
                $period = '7days';
                $startDate = date('Y-m-d', strtotime('-6 days'));
                $endDate = $todayStr;
                $periodName = '7 ngày qua';
                break;
        }

        // 2. Tổng doanh thu theo kỳ đã chọn
        $periodRevStmt = $db->prepare(
            "SELECT COALESCE(SUM(grand_total), 0) FROM orders 
             WHERE DATE(created_at) BETWEEN :start_date AND :end_date 
               AND (payment_status = 'paid' OR status = 'completed')"
        );
        $periodRevStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $totalRevenue = (float)$periodRevStmt->fetchColumn();

        // Doanh thu toàn thời gian để đối chiếu
        $allTimeRevStmt = $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE payment_status = 'paid' OR status = 'completed'");
        $allTimeRevenue = (float)$allTimeRevStmt->fetchColumn();

        // 3. Doanh thu theo từng ngày trong kỳ
        $dailyRevStmt = $db->prepare(
            "SELECT DATE(created_at) as order_date, COALESCE(SUM(grand_total), 0) as daily_total, COUNT(*) as order_count
             FROM orders
             WHERE DATE(created_at) BETWEEN :start_date AND :end_date
               AND (payment_status = 'paid' OR status = 'completed')
             GROUP BY DATE(created_at)
             ORDER BY order_date ASC"
        );
        $dailyRevStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $dailyRevenuesRaw = $dailyRevStmt->fetchAll(PDO::FETCH_ASSOC);

        $dailyMap = [];
        foreach ($dailyRevenuesRaw as $dr) {
            $dailyMap[$dr['order_date']] = [
                'total' => (float)$dr['daily_total'],
                'count' => (int)$dr['order_count'],
            ];
        }

        // Lấp đầy các ngày trong khoảng
        $chartDates = [];
        $chartTotals = [];
        $chartOrderCounts = [];
        $curr = strtotime($startDate);
        $end = strtotime($endDate);
        while ($curr <= $end) {
            $d = date('Y-m-d', $curr);
            $chartDates[] = date('d/m', $curr);
            $chartTotals[] = $dailyMap[$d]['total'] ?? 0;
            $chartOrderCounts[] = $dailyMap[$d]['count'] ?? 0;
            $curr = strtotime('+1 day', $curr);
        }

        // 4. Tỷ lệ đơn hàng theo trạng thái trong kỳ
        $orderRatioStmt = $db->prepare(
            "SELECT 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status IN ('serving', 'ready', 'preparing') THEN 1 ELSE 0 END) as in_progress_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                COUNT(*) as total_orders
             FROM orders
             WHERE DATE(created_at) BETWEEN :start_date AND :end_date"
        );
        $orderRatioStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $orderRatio = $orderRatioStmt->fetch(PDO::FETCH_ASSOC);

        // 5. Món ăn bán chạy nhất trong kỳ (Top 10)
        $topDishesStmt = $db->prepare(
            "SELECT oi.dish_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_sales
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date
               AND oi.status <> 'cancelled'
             GROUP BY oi.dish_name
             ORDER BY total_qty DESC, total_sales DESC
             LIMIT 10"
        );
        $topDishesStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $topDishes = $topDishesStmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Thống kê theo khu vực ẩm thực trong kỳ
        $areaStatsStmt = $db->prepare(
            "SELECT a.name as area_name, COUNT(o.id) as total_orders, COALESCE(SUM(o.grand_total), 0) as total_revenue
             FROM dining_areas a
             JOIN restaurant_tables t ON t.area_id = a.id
             LEFT JOIN orders o ON o.table_id = t.id 
                AND DATE(o.created_at) BETWEEN :start_date AND :end_date 
                AND (o.payment_status = 'paid' OR o.status = 'completed')
             GROUP BY a.id, a.name
             ORDER BY total_revenue DESC"
        );
        $areaStatsStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $areaStats = $areaStatsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Thống kê phương thức thanh toán
        $payMethodStmt = $db->prepare(
            "SELECT payment_method, COUNT(*) as count, COALESCE(SUM(total), 0) as total_amount
             FROM invoices
             WHERE DATE(issued_at) BETWEEN :start_date AND :end_date
               AND status = 'paid'
             GROUP BY payment_method"
        );
        $payMethodStmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $paymentMethodStats = $payMethodStmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả JSON nếu có query format=json
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'period' => $period,
                'periodName' => $periodName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalRevenue' => $totalRevenue,
                'chartDates' => $chartDates,
                'chartTotals' => $chartTotals,
                'chartOrderCounts' => $chartOrderCounts,
                'orderRatio' => $orderRatio,
                'topDishes' => $topDishes,
                'areaStats' => $areaStats,
                'paymentMethodStats' => $paymentMethodStats
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->render('admin/reports/index', [
            'pageTitle' => 'Báo Cáo & Thống Kê Doanh Thu — FOODIES Admin',
            'currentUser' => $currentUser,
            'period' => $period,
            'periodName' => $periodName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRevenue' => $totalRevenue,
            'allTimeRevenue' => $allTimeRevenue,
            'chartDates' => $chartDates,
            'chartTotals' => $chartTotals,
            'chartOrderCounts' => $chartOrderCounts,
            'orderRatio' => $orderRatio,
            'topDishes' => $topDishes,
            'areaStats' => $areaStats,
            'paymentMethodStats' => $paymentMethodStats,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/admin');
    }
}
