<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\ComboSetup;
use App\Models\Voucher;
use Throwable;

final class PromotionAdminController extends Controller
{
    public function combos(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();
        $comboModel = new ComboSetup($db);
        $voucherModel = new Voucher($db);

        $combos = $comboModel->allActive();
        $vouchers = $voucherModel->allActive();

        $this->render('admin/promotions/index', [
            'pageTitle' => 'Quản Lý Combo & Khuyến Mãi — FOODIES Admin',
            'currentUser' => $currentUser,
            'combos' => $combos,
            'vouchers' => $vouchers,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/admin');
    }

    public function createVoucher(): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/combos?error=invalid_csrf');
        }

        $code = strtoupper(trim((string)($_POST['code'] ?? '')));
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $discountType = (string)($_POST['discount_type'] ?? 'percentage');
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $minOrderAmount = (float)($_POST['min_order_amount'] ?? 0);
        $startDate = (string)($_POST['start_date'] ?? date('Y-m-d H:i:s'));
        $endDate = (string)($_POST['end_date'] ?? date('Y-m-d H:i:s', strtotime('+30 days')));

        if ($code === '' || $title === '' || $discountValue <= 0) {
            $this->redirect('/admin/combos?error=invalid_data');
        }

        $db = Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO vouchers (code, title, description, discount_type, discount_value, min_order_amount, start_date, end_date, is_active, created_at, updated_at)
             VALUES (:code, :title, :desc, :type, :val, :min_ord, :sdate, :edate, 1, NOW(), NOW())"
        );
        try {
            $stmt->execute([
                'code' => $code,
                'title' => $title,
                'desc' => $description,
                'type' => $discountType,
                'val' => $discountValue,
                'min_ord' => $minOrderAmount,
                'sdate' => $startDate,
                'edate' => $endDate,
            ]);
            $this->redirect('/admin/combos?message=voucher_created');
        } catch (Throwable) {
            $this->redirect('/admin/combos?error=code_exists');
        }
    }

    public function setups(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();

        $stmt = $db->query(
            "SELECT b.*, t.name as table_name, t.code as table_code, t.is_vip, t.table_fee, t.vip_privileges,
                    c.name as combo_name, c.price as combo_price, c.includes as setup_details
             FROM bookings b
             JOIN restaurant_tables t ON t.id = b.table_id
             LEFT JOIN combo_setups c ON c.id = b.combo_id
             WHERE b.booking_date >= CURDATE()
               AND (b.combo_id IS NOT NULL OR b.notes LIKE '%setup%' OR t.is_vip = 1)
             ORDER BY b.booking_date ASC, b.booking_time ASC"
        );
        $setups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/setups/index', [
            'pageTitle' => 'Quản Lý Yêu Cầu Setup Riêng & Tiệc Độc Bản — FOODIES Admin',
            'currentUser' => $currentUser,
            'setups' => $setups,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/admin');
    }
}
