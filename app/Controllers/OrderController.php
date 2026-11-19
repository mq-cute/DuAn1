<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Table;
use DomainException;
use Throwable;

final class OrderController extends Controller
{
    public function pos(): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $db = Database::connection();
        $tableModel = new Table($db);
        $orderModel = new Order($db);
        $dishModel = new Dish($db);

        $selectedAreaId = isset($_GET['area_id']) && is_numeric($_GET['area_id']) ? (int) $_GET['area_id'] : null;
        $statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : null;

        $areas = $tableModel->allAreas();
        $tables = $tableModel->getActiveTablesForPos($selectedAreaId, $statusFilter);
        $allDishes = $dishModel->allVisible();

        // Determine currently active table
        $selectedTableId = isset($_GET['table_id']) && is_numeric($_GET['table_id']) ? (int) $_GET['table_id'] : null;
        $selectedTable = null;
        $activeOrder = null;

        if ($selectedTableId !== null) {
            foreach ($tables as $t) {
                if ((int) $t['id'] === $selectedTableId) {
                    $selectedTable = $t;
                    break;
                }
            }
            if ($selectedTable === null) {
                $selectedTable = $tableModel->findById($selectedTableId);
            }
        } elseif ($tables !== []) {
            // Default to first active serving/awaiting table if available, else first table
            foreach ($tables as $t) {
                if (in_array($t['status'], ['serving', 'awaiting_payment', 'in_use'], true)) {
                    $selectedTable = $t;
                    $selectedTableId = (int) $t['id'];
                    break;
                }
            }
            if ($selectedTable === null) {
                $selectedTable = $tables[0];
                $selectedTableId = (int) $selectedTable['id'];
            }
        }

        if ($selectedTableId !== null) {
            $activeOrder = $orderModel->getActiveOrderByTableId($selectedTableId);
            if ($activeOrder !== null) {
                $activeOrder = $orderModel->getOrderDetails((int) $activeOrder['id']);
            }
        }

        // Fetch empty tables for "Mở bàn mới"
        $emptyTables = array_filter(
            $tableModel->allWithAreas(),
            static fn (array $t): bool => in_array($t['status'], ['empty', 'reserved'], true)
        );

        $this->render('order/pos', [
            'pageTitle' => 'Quản lý Phục vụ tại bàn & POS — L\'Épicure',
            'currentUser' => $currentUser,
            'areas' => $areas,
            'tables' => $tables,
            'selectedTable' => $selectedTable,
            'selectedTableId' => $selectedTableId,
            'activeOrder' => $activeOrder,
            'allDishes' => $allDishes,
            'emptyTables' => $emptyTables,
            'selectedAreaId' => $selectedAreaId,
            'statusFilter' => $statusFilter,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/staff');
    }

    public function createAtTable(): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/staff/pos?error=csrf_expired');
        }

        $tableId = isset($_POST['table_id']) && is_numeric($_POST['table_id']) ? (int) $_POST['table_id'] : 0;
        $customerName = isset($_POST['customer_name']) && is_string($_POST['customer_name']) ? trim($_POST['customer_name']) : null;
        $notes = isset($_POST['notes']) && is_string($_POST['notes']) ? trim($_POST['notes']) : null;

        // Parse items from form
        // items format: items[<dish_id>][quantity], items[<dish_id>][note]
        // or items array of objects
        $rawItems = $_POST['items'] ?? [];
        $items = [];

        if (is_array($rawItems)) {
            foreach ($rawItems as $key => $data) {
                $dishId = is_numeric($key) ? (int) $key : (int) ($data['dish_id'] ?? 0);
                $qty = (int) ($data['quantity'] ?? 0);
                $note = isset($data['note']) && is_string($data['note']) ? trim($data['note']) : '';

                if ($dishId > 0 && $qty > 0) {
                    $items[] = [
                        'dish_id' => $dishId,
                        'quantity' => $qty,
                        'note' => $note,
                    ];
                }
            }
        }

        // Single quick item addition support
        if (isset($_POST['single_dish_id']) && is_numeric($_POST['single_dish_id'])) {
            $dishId = (int) $_POST['single_dish_id'];
            $qty = isset($_POST['single_quantity']) && is_numeric($_POST['single_quantity']) ? (int) $_POST['single_quantity'] : 1;
            $items[] = [
                'dish_id' => $dishId,
                'quantity' => $qty,
                'note' => isset($_POST['single_note']) ? trim((string) $_POST['single_note']) : '',
            ];
        }

        if ($tableId <= 0 || $items === []) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=missing_items_or_table');
        }

        $db = Database::connection();
        $orderModel = new Order($db);

        try {
            $orderId = $orderModel->createOrderAtTable(
                $tableId,
                $customerName,
                (int) $currentUser['id'],
                $items,
                $notes
            );
            $this->redirect('/staff/pos?table_id=' . $tableId . '&order_id=' . $orderId . '&message=order_created');
        } catch (DomainException $e) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=system_error');
        }
    }

    public function addItems(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/staff/pos?error=csrf_expired');
        }

        $orderId = (int) $id;
        $tableId = isset($_POST['table_id']) && is_numeric($_POST['table_id']) ? (int) $_POST['table_id'] : 0;

        $items = [];
        $rawItems = $_POST['items'] ?? [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $key => $data) {
                $dishId = is_numeric($key) ? (int) $key : (int) ($data['dish_id'] ?? 0);
                $qty = (int) ($data['quantity'] ?? 0);
                $note = isset($data['note']) && is_string($data['note']) ? trim($data['note']) : '';

                if ($dishId > 0 && $qty > 0) {
                    $items[] = [
                        'dish_id' => $dishId,
                        'quantity' => $qty,
                        'note' => $note,
                    ];
                }
            }
        }

        if (isset($_POST['single_dish_id']) && is_numeric($_POST['single_dish_id'])) {
            $dishId = (int) $_POST['single_dish_id'];
            $qty = isset($_POST['single_quantity']) && is_numeric($_POST['single_quantity']) ? (int) $_POST['single_quantity'] : 1;
            $items[] = [
                'dish_id' => $dishId,
                'quantity' => $qty,
                'note' => isset($_POST['single_note']) ? trim((string) $_POST['single_note']) : '',
            ];
        }

        if ($items === []) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=no_items_selected');
        }

        $db = Database::connection();
        $orderModel = new Order($db);

        try {
            $orderModel->addItemsToOrder($orderId, $items, (int) $currentUser['id']);
            $this->redirect('/staff/pos?table_id=' . $tableId . '&message=items_added');
        } catch (DomainException $e) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=system_error');
        }
    }

    public function updateItemStatus(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/staff/pos?error=csrf_expired');
        }

        $itemId = (int) $id;
        $newStatus = (string) ($_POST['status'] ?? 'served');
        $tableId = isset($_POST['table_id']) && is_numeric($_POST['table_id']) ? (int) $_POST['table_id'] : 0;

        $db = Database::connection();
        $orderModel = new Order($db);
        $success = $orderModel->updateOrderItemStatus($itemId, $newStatus, (int) $currentUser['id']);

        $this->redirect('/staff/pos?table_id=' . $tableId . '&message=' . ($success ? 'item_updated' : 'update_failed'));
    }

    public function updateStatus(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/staff/orders?error=csrf_expired');
        }

        $orderId = (int) $id;
        $newStatus = (string) ($_POST['status'] ?? '');
        $note = isset($_POST['note']) && is_string($_POST['note']) ? trim($_POST['note']) : null;
        $tableId = isset($_POST['table_id']) && is_numeric($_POST['table_id']) ? (int) $_POST['table_id'] : 0;

        $db = Database::connection();
        $orderModel = new Order($db);
        $success = $orderModel->updateOrderStatus($orderId, $newStatus, (int) $currentUser['id'], $note);

        $redirect = isset($_POST['redirect']) && is_string($_POST['redirect']) ? $_POST['redirect'] : '/staff/pos?table_id=' . $tableId;
        $this->redirect($redirect . (str_contains($redirect, '?') ? '&' : '?') . 'message=' . ($success ? 'status_updated' : 'update_failed'));
    }

    public function checkinBooking(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/bookings?message=invalid_csrf');
        }

        $bookingId = (int) $id;
        $db = Database::connection();
        $orderModel = new Order($db);

        try {
            $orderId = $orderModel->checkinBooking($bookingId, (int) $currentUser['id']);
            $order = $orderModel->findById($orderId);
            $tableId = $order['table_id'] ?? 0;
            $this->redirect('/staff/pos?table_id=' . $tableId . '&message=booking_seated');
        } catch (DomainException $e) {
            $this->redirect('/admin/bookings?message=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/admin/bookings?message=checkin_failed');
        }
    }

    public function index(): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $db = Database::connection();
        $orderModel = new Order($db);

        $status = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : null;
        $date = isset($_GET['date']) && is_string($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');

        $orders = $orderModel->allOrders($status, $date);

        $this->render('order/index', [
            'pageTitle' => 'Quản lý Gọi món & Tiến độ ẩm thực — L\'Épicure',
            'currentUser' => $currentUser,
            'orders' => $orders,
            'selectedStatus' => $status,
            'selectedDate' => $date,
            'orderStatuses' => Order::ORDER_STATUSES,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
        ], 200, 'layouts/staff');
    }

    public function show(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $db = Database::connection();
        $orderModel = new Order($db);

        $orderId = (int) $id;
        $order = $orderModel->getOrderDetails($orderId);

        if ($order === null) {
            $this->redirect('/staff/orders?message=not_found');
        }

        $this->render('order/show', [
            'pageTitle' => 'Chi tiết Đơn gọi món #' . $orderId . ' — L\'Épicure',
            'currentUser' => $currentUser,
            'order' => $order,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
        ], 200, 'layouts/staff');
    }

    public function customerOrders(): void
    {
        $currentUser = $this->requireAuthenticated();
        $db = Database::connection();
        $orderModel = new Order($db);

        $status = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : '';
        $orders = $orderModel->getOrdersByCustomer(
            (int) $currentUser['id'],
            $currentUser['name'] ?? null,
            $currentUser['email'] ?? null,
            $currentUser['phone'] ?? null
        );

        if ($status !== '') {
            $orders = array_values(array_filter($orders, static function (array $o) use ($status): bool {
                if ($status === 'active') {
                    return !in_array($o['status'], ['completed', 'cancelled'], true);
                }
                return $o['status'] === $status;
            }));
        }

        $this->render('order/customer-index', [
            'pageTitle' => 'Đơn Của Tôi — L\'Épicure Gastronomie',
            'currentUser' => $currentUser,
            'orders' => $orders,
            'selectedStatus' => $status,
            'orderStatuses' => Order::ORDER_STATUSES,
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/guest');
    }

    public function customerOrderDetail(string $id): void
    {
        $currentUser = $this->requireAuthenticated();
        $orderId = (int) $id;
        $db = Database::connection();
        $orderModel = new Order($db);

        $order = $orderModel->getCustomerOrderDetails(
            $orderId,
            (int) $currentUser['id'],
            $currentUser['name'] ?? null,
            $currentUser['email'] ?? null,
            $currentUser['phone'] ?? null
        );

        if ($order === null) {
            $this->redirect('/account/orders?error=not_found');
        }

        $this->render('order/customer-tracking', [
            'pageTitle' => 'Theo Dõi Đơn Hàng #' . $orderId . ' — L\'Épicure Gastronomie',
            'currentUser' => $currentUser,
            'order' => $order,
            'orderStatuses' => Order::ORDER_STATUSES,
            'itemStatuses' => Order::ITEM_STATUSES,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/guest');
    }

    public function customerOrderStatus(string $id): void
    {
        $currentUser = $this->requireAuthenticated();
        $orderId = (int) $id;
        $db = Database::connection();
        $orderModel = new Order($db);

        $order = $orderModel->getCustomerOrderDetails(
            $orderId,
            (int) $currentUser['id'],
            $currentUser['name'] ?? null,
            $currentUser['email'] ?? null,
            $currentUser['phone'] ?? null
        );

        header('Content-Type: application/json; charset=utf-8');

        if ($order === null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Calculate progress steps
        $stepMap = ['pending' => 1, 'confirmed' => 2, 'preparing' => 3, 'ready' => 4, 'served' => 5, 'completed' => 6, 'cancelled' => -1];
        $currentStep = $stepMap[$order['status']] ?? 1;

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'status' => $order['status'],
            'status_label' => Order::ORDER_STATUSES[$order['status']] ?? $order['status'],
            'payment_status' => $order['payment_status'],
            'step' => $currentStep,
            'items' => array_map(static fn (array $item): array => [
                'id' => (int) $item['id'],
                'dish_name' => $item['dish_name'],
                'quantity' => (int) $item['quantity'],
                'status' => $item['status'],
                'status_label' => Order::ITEM_STATUSES[$item['status']] ?? $item['status'],
            ], $order['items']),
            'updated_at' => $order['updated_at'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
