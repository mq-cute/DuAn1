<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use App\Models\ComboSetup;
use App\Models\Table;
use Throwable;

final class TableAdminController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();
        $tableModel = new Table($db);

        // Kích hoạt giải phóng các bàn đặt trước mà khách đến muộn
        (new Booking($db))->autoReleaseLateBookings();

        $selectedAreaId = isset($_GET['area_id']) && is_numeric($_GET['area_id']) ? (int) $_GET['area_id'] : null;
        $floorData = $tableModel->getFloorPlanData($selectedAreaId);
        $allTables = $tableModel->allWithAreas();

        // Lấy danh sách yêu cầu setup riêng từ các booking hôm nay
        $setupStmt = $db->query(
            "SELECT b.*, t.name as table_name, t.code as table_code, t.is_vip, t.table_fee, t.vip_privileges,
                    c.name as combo_name, c.price as combo_price, c.includes as setup_details
             FROM bookings b
             JOIN restaurant_tables t ON t.id = b.table_id
             LEFT JOIN combo_setups c ON c.id = b.combo_id
             WHERE b.booking_date = CURDATE()
               AND (b.combo_id IS NOT NULL OR b.notes LIKE '%setup%' OR t.is_vip = 1)
             ORDER BY b.booking_time ASC"
        );
        $setupRequests = $setupStmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/tables/index', [
            'pageTitle' => 'Sơ đồ Bàn ăn & Quản lý Không gian — FOODIES Admin',
            'currentUser' => $currentUser,
            'areas' => $floorData['areas'],
            'tablesByArea' => $floorData['tablesByArea'],
            'totalTables' => $floorData['totalTables'],
            'counts' => $floorData['counts'],
            'allTables' => $allTables,
            'selectedAreaId' => $selectedAreaId,
            'setupRequests' => $setupRequests,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
        ], 200, 'layouts/admin');
    }

    public function createArea(): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $this->redirect('/admin/tables?message=area_name_required');
        }

        $db = Database::connection();
        $tableModel = new Table($db);
        $tableModel->createArea([
            'name' => $name,
            'description' => trim((string)($_POST['description'] ?? '')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);

        $this->redirect('/admin/tables?message=area_created');
    }

    public function updateArea(string $id): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $this->redirect('/admin/tables?message=area_name_required');
        }

        $db = Database::connection();
        $tableModel = new Table($db);
        $tableModel->updateArea((int)$id, [
            'name' => $name,
            'description' => trim((string)($_POST['description'] ?? '')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);

        $this->redirect('/admin/tables?message=area_updated');
    }

    public function deleteArea(string $id): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $db = Database::connection();
        $tableModel = new Table($db);
        $success = $tableModel->deleteArea((int)$id);

        if (!$success) {
            $this->redirect('/admin/tables?message=area_has_tables');
        }

        $this->redirect('/admin/tables?message=area_deleted');
    }

    public function createTable(): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $code = strtoupper(trim((string)($_POST['code'] ?? '')));
        $name = trim((string)($_POST['name'] ?? ''));
        $areaId = (int)($_POST['area_id'] ?? 0);
        $capacity = max(1, (int)($_POST['capacity'] ?? 4));

        if ($code === '' || $name === '' || $areaId <= 0) {
            $this->redirect('/admin/tables?message=table_info_required');
        }

        $db = Database::connection();
        $tableModel = new Table($db);

        // Kiểm tra mã bàn trùng
        if ($tableModel->findByCode($code) !== null) {
            $this->redirect('/admin/tables?message=table_code_exists');
        }

        $tableModel->createTable([
            'area_id' => $areaId,
            'code' => $code,
            'name' => $name,
            'capacity' => $capacity,
            'status' => (string)($_POST['status'] ?? 'empty'),
            'position_x' => (int)($_POST['position_x'] ?? 50),
            'position_y' => (int)($_POST['position_y'] ?? 50),
            'is_vip' => !empty($_POST['is_vip']) ? 1 : 0,
            'table_fee' => (float)($_POST['table_fee'] ?? 0.0),
            'vip_privileges' => trim((string)($_POST['vip_privileges'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
        ]);

        $this->redirect('/admin/tables?message=table_created');
    }

    public function updateTable(string $id): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $tableId = (int)$id;
        $code = strtoupper(trim((string)($_POST['code'] ?? '')));
        $name = trim((string)($_POST['name'] ?? ''));
        $areaId = (int)($_POST['area_id'] ?? 0);
        $capacity = max(1, (int)($_POST['capacity'] ?? 4));

        if ($code === '' || $name === '' || $areaId <= 0) {
            $this->redirect('/admin/tables?message=table_info_required');
        }

        $db = Database::connection();
        $tableModel = new Table($db);

        // Kiểm tra mã bàn trùng với bàn khác
        $existing = $tableModel->findByCode($code);
        if ($existing !== null && (int)$existing['id'] !== $tableId) {
            $this->redirect('/admin/tables?message=table_code_exists');
        }

        $tableModel->updateTable($tableId, [
            'area_id' => $areaId,
            'code' => $code,
            'name' => $name,
            'capacity' => $capacity,
            'status' => (string)($_POST['status'] ?? 'empty'),
            'position_x' => (int)($_POST['position_x'] ?? 50),
            'position_y' => (int)($_POST['position_y'] ?? 50),
            'is_vip' => !empty($_POST['is_vip']) ? 1 : 0,
            'table_fee' => (float)($_POST['table_fee'] ?? 0.0),
            'vip_privileges' => trim((string)($_POST['vip_privileges'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
        ]);

        $this->redirect('/admin/tables?message=table_updated');
    }

    public function deleteTable(string $id): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $tableId = (int)$id;
        $db = Database::connection();
        $tableModel = new Table($db);
        $success = $tableModel->deleteTable($tableId);

        if (!$success) {
            $this->redirect('/admin/tables?message=table_in_use');
        }

        $this->redirect('/admin/tables?message=table_deleted');
    }

    public function savePositions(): void
    {
        $this->requireRole('admin');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!is_array($data) || !isset($data['positions']) || !is_array($data['positions'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid data format']);
            exit;
        }

        $db = Database::connection();
        $tableModel = new Table($db);
        $tableModel->updatePositions($data['positions']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Lưu vị trí sơ đồ thành công']);
        exit;
    }

    public function updateStatus(string $id): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $tableId = (int)$id;
        $newStatus = (string)($_POST['status'] ?? '');

        $db = Database::connection();
        $tableModel = new Table($db);
        $success = $tableModel->updateStatus($tableId, $newStatus);

        $this->redirect('/admin/tables?message=' . ($success ? 'status_updated' : 'update_failed'));
    }

    public function updateVip(string $id): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/tables?message=invalid_csrf');
        }

        $tableId = (int)$id;
        $isVip = !empty($_POST['is_vip']) ? 1 : 0;
        $tableFee = isset($_POST['table_fee']) ? (float)$_POST['table_fee'] : 0.0;
        $vipPrivileges = trim((string)($_POST['vip_privileges'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));

        $db = Database::connection();
        $stmt = $db->prepare("UPDATE restaurant_tables SET is_vip = :is_vip, table_fee = :fee, vip_privileges = :priv, description = :desc, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'is_vip' => $isVip,
            'fee' => $tableFee,
            'priv' => $vipPrivileges,
            'desc' => $desc,
            'id' => $tableId,
        ]);

        $this->redirect('/admin/tables?message=vip_updated');
    }
}
