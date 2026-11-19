<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ActivityLog extends BaseModel
{
    public static function log(
        PDO $db,
        ?int $userId,
        string $action,
        string $targetType,
        ?int $targetId = null,
        ?array $details = null,
        ?string $ipAddress = null
    ): int {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        $stmt = $db->prepare(
            'INSERT INTO activity_logs (user_id, action, target_type, target_id, details, ip_address, created_at)
             VALUES (:user_id, :action, :target_type, :target_id, :details, :ip_address, CURRENT_TIMESTAMP)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ipAddress,
        ]);

        return (int) $db->lastInsertId();
    }

    public function all(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['action'])) {
            $conditions[] = 'al.action = :action';
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['target_type'])) {
            $conditions[] = 'al.target_type = :target_type';
            $params['target_type'] = $filters['target_type'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = 'al.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['date'])) {
            $conditions[] = 'DATE(al.created_at) = :date';
            $params['date'] = $filters['date'];
        }

        $whereClause = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT al.*, u.name AS user_name, u.email AS user_email, r.name AS user_role
                FROM activity_logs al
                LEFT JOIN users u ON u.id = al.user_id
                LEFT JOIN roles r ON r.id = u.role_id
                {$whereClause}
                ORDER BY al.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['action'])) {
            $conditions[] = 'action = :action';
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['target_type'])) {
            $conditions[] = 'target_type = :target_type';
            $params['target_type'] = $filters['target_type'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = 'user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['date'])) {
            $conditions[] = 'DATE(created_at) = :date';
            $params['date'] = $filters['date'];
        }

        $whereClause = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM activity_logs {$whereClause}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}
