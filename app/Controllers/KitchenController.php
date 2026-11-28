<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Order;
use PDO;

final class KitchenController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireRole('kitchen');
        $db = Database::connection();
        $orderModel = new Order($db);

        $statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : 'all';
        $viewMode = isset($_GET['view']) && is_string($_GET['view']) ? trim($_GET['view']) : 'tickets';

        $tickets = $orderModel->getKitchenTickets($statusFilter);
        $summary = $orderModel->getKitchenSummary();
        $stats = $orderModel->getKitchenStats();

        $this->render('kitchen/index', [
            'pageTitle' => 'Hàng Đợi Bếp & Màn Hình KDS — FOODIES',
            'currentUser' => $currentUser,
            'tickets' => $tickets,
            'summary' => $summary,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
            'viewMode' => $viewMode,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/staff');
    }

    public function apiQueue(): void
    {
        $currentUser = $this->requireRole('kitchen');
        $db = Database::connection();
        $orderModel = new Order($db);

        $statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : 'all';
        $tickets = $orderModel->getKitchenTickets($statusFilter);
        $summary = $orderModel->getKitchenSummary();
        $stats = $orderModel->getKitchenStats();

        $hashData = [
            'stats' => $stats,
            'orders' => array_map(static fn (array $t): array => [
                'id' => $t['order_id'],
                'items' => array_map(static fn (array $i): array => [$i['item_id'], $i['item_status']], $t['items']),
            ], $tickets),
        ];
        $hash = md5((string) json_encode($hashData, JSON_UNESCAPED_UNICODE));

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'hash' => $hash,
            'server_time' => date('H:i:s'),
            'timestamp' => time(),
            'stats' => $stats,
            'tickets' => $tickets,
            'summary' => $summary,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function updateItemStatus(string $id): void
    {
        $currentUser = $this->requireRole('kitchen');
        $itemId = (int) $id;
        $targetStatus = isset($_POST['status']) && is_string($_POST['status']) ? trim($_POST['status']) : null;

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

        if (!$this->verifyCsrf()) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8', true, 419);
                echo json_encode(['success' => false, 'error' => 'CSRF token expired']);
                exit;
            }
            $this->redirect('/kitchen?error=csrf_expired');
        }

        $db = Database::connection();
        $orderModel = new Order($db);

        $res = $orderModel->advanceKitchenItemStatus($itemId, $targetStatus, (int) $currentUser['id']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $msg = $res['success'] ? 'item_updated' : 'update_failed';
        $this->redirect('/kitchen?message=' . $msg);
    }

    public function updateTicketStatus(string $id): void
    {
        $currentUser = $this->requireRole('kitchen');
        $orderId = (int) $id;
        $targetStatus = isset($_POST['status']) && is_string($_POST['status']) ? trim($_POST['status']) : 'preparing';

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

        if (!$this->verifyCsrf()) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8', true, 419);
                echo json_encode(['success' => false, 'error' => 'CSRF token expired']);
                exit;
            }
            $this->redirect('/kitchen?error=csrf_expired');
        }

        $db = Database::connection();
        $orderModel = new Order($db);

        $res = $orderModel->advanceKitchenTicketStatus($orderId, $targetStatus, (int) $currentUser['id']);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->redirect('/kitchen?message=ticket_updated');
    }

    public function apiReadyItems(): void
    {
        $currentUser = $this->requireRole('staff', 'kitchen');
        $db = Database::connection();

        $stmt = $db->query(
            'SELECT oi.id AS item_id, oi.dish_name, oi.quantity, oi.updated_at,
                    o.id AS order_id, t.code AS table_code, t.name AS table_name
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             INNER JOIN restaurant_tables t ON t.id = o.table_id
             WHERE oi.status = "ready"
               AND o.status NOT IN ("completed", "cancelled")
             ORDER BY oi.updated_at DESC
             LIMIT 10'
        );
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
