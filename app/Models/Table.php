<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Table extends BaseModel
{
    public const VALID_STATUSES = [
        'empty' => 'Trống',
        'reserved' => 'Đã đặt',
        'in_use' => 'Đang sử dụng',
        'serving' => 'Đang phục vụ',
        'awaiting_payment' => 'Chờ thanh toán',
        'cleaning' => 'Đang dọn dẹp',
        'locked' => 'Tạm khóa',
    ];

    public function findAreaById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM dining_areas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function createArea(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO dining_areas (name, description, sort_order, created_at, updated_at)
             VALUES (:name, :description, :sort_order, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => trim((string)($data['name'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateArea(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE dining_areas
             SET name = :name, description = :description, sort_order = :sort_order, updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            'name' => trim((string)($data['name'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'id' => $id,
        ]);
    }

    public function deleteArea(int $id): bool
    {
        // Kiểm tra xem khu vực có bàn trực thuộc không
        $checkStmt = $this->db->prepare('SELECT COUNT(*) FROM restaurant_tables WHERE area_id = :id');
        $checkStmt->execute(['id' => $id]);
        if ((int)$checkStmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM dining_areas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function createTable(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO restaurant_tables 
             (area_id, code, name, capacity, status, position_x, position_y, is_vip, table_fee, vip_privileges, description, created_at, updated_at)
             VALUES (:area_id, :code, :name, :capacity, :status, :position_x, :position_y, :is_vip, :table_fee, :vip_privileges, :description, NOW(), NOW())'
        );
        $stmt->execute([
            'area_id' => (int)$data['area_id'],
            'code' => strtoupper(trim((string)$data['code'])),
            'name' => trim((string)$data['name']),
            'capacity' => max(1, (int)$data['capacity']),
            'status' => in_array($data['status'] ?? '', array_keys(self::VALID_STATUSES), true) ? $data['status'] : 'empty',
            'position_x' => isset($data['position_x']) ? (int)$data['position_x'] : 50,
            'position_y' => isset($data['position_y']) ? (int)$data['position_y'] : 50,
            'is_vip' => !empty($data['is_vip']) ? 1 : 0,
            'table_fee' => isset($data['table_fee']) ? (float)$data['table_fee'] : 0.0,
            'vip_privileges' => trim((string)($data['vip_privileges'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateTable(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE restaurant_tables
             SET area_id = :area_id,
                 code = :code,
                 name = :name,
                 capacity = :capacity,
                 status = :status,
                 position_x = :position_x,
                 position_y = :position_y,
                 is_vip = :is_vip,
                 table_fee = :table_fee,
                 vip_privileges = :vip_privileges,
                 description = :description,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            'area_id' => (int)$data['area_id'],
            'code' => strtoupper(trim((string)$data['code'])),
            'name' => trim((string)$data['name']),
            'capacity' => max(1, (int)$data['capacity']),
            'status' => in_array($data['status'] ?? '', array_keys(self::VALID_STATUSES), true) ? $data['status'] : 'empty',
            'position_x' => isset($data['position_x']) ? (int)$data['position_x'] : 50,
            'position_y' => isset($data['position_y']) ? (int)$data['position_y'] : 50,
            'is_vip' => !empty($data['is_vip']) ? 1 : 0,
            'table_fee' => isset($data['table_fee']) ? (float)$data['table_fee'] : 0.0,
            'vip_privileges' => trim((string)($data['vip_privileges'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
            'id' => $id,
        ]);
    }

    public function deleteTable(int $id): bool
    {
        // Kiểm tra xem bàn có đang phục vụ hoặc có đơn hàng active / booking không
        $orderStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM orders WHERE table_id = :id AND status NOT IN ('completed', 'cancelled')"
        );
        $orderStmt->execute(['id' => $id]);
        if ((int)$orderStmt->fetchColumn() > 0) {
            return false;
        }

        $bookingStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM bookings WHERE table_id = :id AND status IN ('pending', 'confirmed', 'seated')"
        );
        $bookingStmt->execute(['id' => $id]);
        if ((int)$bookingStmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM restaurant_tables WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function updatePositions(array $positions): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE restaurant_tables SET position_x = :x, position_y = :y, updated_at = NOW() WHERE id = :id'
        );

        foreach ($positions as $pos) {
            if (isset($pos['id'], $pos['x'], $pos['y'])) {
                $stmt->execute([
                    'id' => (int)$pos['id'],
                    'x' => (int)$pos['x'],
                    'y' => (int)$pos['y'],
                ]);
            }
        }

        return true;
    }

    public function allAreas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, name, description, sort_order
             FROM dining_areas
             ORDER BY sort_order ASC, name ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allWithAreas(?int $areaId = null, ?string $status = null): array
    {
        $conditions = [];
        $params = [];

        if ($areaId !== null) {
            $conditions[] = 't.area_id = :area_id';
            $params['area_id'] = $areaId;
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 't.status = :status';
            $params['status'] = $status;
        }

        $sql = 'SELECT t.*, a.name AS area_name, a.sort_order AS area_sort
                FROM restaurant_tables t
                INNER JOIN dining_areas a ON a.id = t.area_id'
            . ($conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '')
            . ' ORDER BY a.sort_order ASC, t.code ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, a.name AS area_name
             FROM restaurant_tables t
             INNER JOIN dining_areas a ON a.id = t.area_id
             WHERE t.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, a.name AS area_name
             FROM restaurant_tables t
             INNER JOIN dining_areas a ON a.id = t.area_id
             WHERE t.code = :code
             LIMIT 1'
        );
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function updateStatus(int $tableId, string $status): bool
    {
        if (!array_key_exists($status, self::VALID_STATUSES)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE restaurant_tables
             SET status = :status, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $stmt->execute([
            'status' => $status,
            'id' => $tableId,
        ]);
    }

    public function getFloorPlanData(?int $areaId = null): array
    {
        $areas = $this->allAreas();
        $tables = $this->allWithAreas($areaId);

        // Fetch active order information for non-empty tables
        $activeOrdersStmt = $this->db->query(
            "SELECT o.id AS order_id, o.table_id, o.customer_name, o.grand_total, o.created_at, o.status AS order_status
             FROM orders o
             WHERE o.status NOT IN ('completed', 'cancelled')
               AND o.payment_status = 'unpaid'
             ORDER BY o.id DESC"
        );
        $activeOrders = [];
        foreach ($activeOrdersStmt->fetchAll(PDO::FETCH_ASSOC) as $order) {
            $tId = (int) $order['table_id'];
            if (!isset($activeOrders[$tId])) {
                $activeOrders[$tId] = $order;
            }
        }

        $tablesByArea = [];
        foreach ($areas as $area) {
            $tablesByArea[$area['id']] = [
                'area' => $area,
                'tables' => [],
            ];
        }

        foreach ($tables as $table) {
            $tId = (int) $table['id'];
            $aId = (int) $table['area_id'];
            $table['active_order'] = $activeOrders[$tId] ?? null;

            if (isset($tablesByArea[$aId])) {
                $tablesByArea[$aId]['tables'][] = $table;
            }
        }

        return [
            'areas' => $areas,
            'tablesByArea' => $tablesByArea,
            'totalTables' => count($tables),
            'counts' => $this->getStatusCounts(),
        ];
    }

    public function getStatusCounts(): array
    {
        $stmt = $this->db->query(
            'SELECT status, COUNT(*) AS count
             FROM restaurant_tables
             GROUP BY status'
        );
        $counts = array_fill_keys(array_keys(self::VALID_STATUSES), 0);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }

        return $counts;
    }

    public function getActiveTablesForPos(?int $areaId = null, ?string $statusFilter = null): array
    {
        $conditions = [];
        $params = [];

        if ($areaId !== null) {
            $conditions[] = 't.area_id = :area_id';
            $params['area_id'] = $areaId;
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $conditions[] = 't.status = :status';
            $params['status'] = $statusFilter;
        }

        $sql = 'SELECT t.id, t.code, t.name, t.capacity, t.status, a.name AS area_name,
                       o.id AS active_order_id, o.customer_name, o.grand_total, o.created_at AS order_created_at,
                       o.status AS order_status, o.payment_status
                FROM restaurant_tables t
                INNER JOIN dining_areas a ON a.id = t.area_id
                LEFT JOIN orders o ON o.table_id = t.id AND o.status NOT IN ("completed", "cancelled") AND o.payment_status = "unpaid"'
            . ($conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '')
            . ' ORDER BY CASE 
                    WHEN t.status IN ("serving", "in_use", "awaiting_payment") THEN 1
                    WHEN t.status = "reserved" THEN 2
                    WHEN t.status = "cleaning" THEN 3
                    ELSE 4
                END ASC, a.sort_order ASC, t.code ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enrich tables that have active orders with item progress
        foreach ($tables as &$tbl) {
            if (!empty($tbl['active_order_id'])) {
                $orderId = (int) $tbl['active_order_id'];
                $itemStmt = $this->db->prepare(
                    'SELECT id, dish_name, quantity, status, note
                     FROM order_items
                     WHERE order_id = :order_id'
                );
                $itemStmt->execute(['order_id' => $orderId]);
                $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                $tbl['items'] = $items;

                $servedCount = 0;
                $totalCount = count($items);
                foreach ($items as $item) {
                    if ($item['status'] === 'served') {
                        $servedCount++;
                    }
                }
                $tbl['progress_served'] = $servedCount;
                $tbl['progress_total'] = $totalCount;

                // Duration in minutes
                $createdTimestamp = strtotime((string) $tbl['order_created_at']);
                $tbl['minutes_seated'] = max(1, (int) round((time() - $createdTimestamp) / 60));
            } else {
                $tbl['items'] = [];
                $tbl['progress_served'] = 0;
                $tbl['progress_total'] = 0;
                $tbl['minutes_seated'] = 0;
            }
        }
        unset($tbl);

        return $tables;
    }
}
