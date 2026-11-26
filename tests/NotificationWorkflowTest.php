<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Models/BaseModel.php';
require dirname(__DIR__) . '/app/Models/Notification.php';

// Mock PDO for unit testing Notification model methods
class MockPDOStatement extends PDOStatement
{
    private array $data;
    public function __construct(array $data = []) { $this->data = $data; }
    public function execute(?array $params = null): bool { return true; }
    public function fetchColumn(int $col = 0): mixed { return count($this->data); }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->data; }
}

class MockPDO extends PDO
{
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement {
        return new MockPDOStatement([
            [
                'id' => 10,
                'user_id' => null,
                'type' => 'booking_new',
                'title' => 'Đơn Đặt Bàn Mới #101',
                'message' => 'Khách hàng Trần Văn B vừa đặt bàn ngày 2026-11-26 lúc 19:00',
                'data' => json_encode(['booking_id' => 101]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }
    public function lastInsertId(?string $name = null): string|false { return '10'; }
}

$mockDb = new MockPDO();
$notifModel = new \App\Models\Notification($mockDb);

// 1. Test create notification
$id = $notifModel->create(null, 'booking_new', 'Đơn Mới', 'Nội dung test', ['booking_id' => 101]);
if ($id !== 10) {
    echo "FAILED: Notification create did not return expected ID\n";
    exit(1);
}
echo "PASSED: Notification created successfully (ID: $id)\n";

// 2. Test recentForUser
$recent = $notifModel->recentForUser(1, 5);
if (count($recent) !== 1 || $recent[0]['type'] !== 'booking_new') {
    echo "FAILED: recentForUser did not return parsed notification\n";
    exit(1);
}
echo "PASSED: recentForUser retrieved and parsed payload correctly!\n";

// 3. Test unreadCount
$unread = $notifModel->unreadCount(1);
if ($unread !== 1) {
    echo "FAILED: unreadCount returned $unread instead of 1\n";
    exit(1);
}
echo "PASSED: unreadCount returned: $unread\n";
