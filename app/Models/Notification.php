<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class Notification extends BaseModel
{
    public function create(?int $userId, string $type, string $title, string $message, ?array $data = null): int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO notifications (user_id, type, title, message, data, is_read, created_at)
                 VALUES (:user_id, :type, :title, :message, :data, 0, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data !== null ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (Throwable) {
            return 0;
        }
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications
             WHERE user_id = :user_id OR user_id IS NULL
             ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['data'] = !empty($row['data']) ? json_decode((string) $row['data'], true) : [];
        }

        return $rows;
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications
             WHERE (user_id = :user_id OR user_id IS NULL)
               AND is_read = 0'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function recentForUser(int $userId, int $limit = 5): array
    {
        $lim = max(1, (int) $limit);
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications
             WHERE user_id = :user_id OR user_id IS NULL
             ORDER BY created_at DESC
             LIMIT $lim"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['data'] = !empty($row['data']) ? json_decode((string) $row['data'], true) : [];
        }

        return $rows;
    }

    public function markAsRead(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)'
        );

        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0'
        );

        return $stmt->execute(['user_id' => $userId]);
    }
}
