<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/app/Core/Environment.php';
App\Core\Environment::load($root . '/.env');
require_once $root . '/app/Core/Database.php';
$pdo = App\Core\Database::connection();

// 1. Thêm các cột cho restaurant_tables
$cols = $pdo->query('SHOW COLUMNS FROM restaurant_tables')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('is_vip', $cols)) {
    $pdo->exec('ALTER TABLE restaurant_tables ADD COLUMN is_vip TINYINT(1) DEFAULT 0 AFTER capacity');
}
if (!in_array('table_fee', $cols)) {
    $pdo->exec('ALTER TABLE restaurant_tables ADD COLUMN table_fee DECIMAL(12,2) DEFAULT 0.00 AFTER is_vip');
}
if (!in_array('vip_privileges', $cols)) {
    $pdo->exec('ALTER TABLE restaurant_tables ADD COLUMN vip_privileges TEXT NULL AFTER table_fee');
}
if (!in_array('description', $cols)) {
    $pdo->exec('ALTER TABLE restaurant_tables ADD COLUMN description TEXT NULL AFTER vip_privileges');
}

// 2. Thêm các cột cho bookings
$bCols = $pdo->query('SHOW COLUMNS FROM bookings')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('combo_id', $bCols)) {
    $pdo->exec('ALTER TABLE bookings ADD COLUMN combo_id BIGINT NULL AFTER table_id');
}
if (!in_array('deposit_amount', $bCols)) {
    $pdo->exec('ALTER TABLE bookings ADD COLUMN deposit_amount DECIMAL(12,2) DEFAULT 0.00 AFTER combo_id');
}
if (!in_array('estimated_total', $bCols)) {
    $pdo->exec('ALTER TABLE bookings ADD COLUMN estimated_total DECIMAL(12,2) DEFAULT 0.00 AFTER deposit_amount');
}

// 3. Thêm các cột cho orders
$oCols = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('table_fee', $oCols)) {
    $pdo->exec('ALTER TABLE orders ADD COLUMN table_fee DECIMAL(12,2) DEFAULT 0.00 AFTER total_amount');
}
if (!in_array('setup_fee', $oCols)) {
    $pdo->exec('ALTER TABLE orders ADD COLUMN setup_fee DECIMAL(12,2) DEFAULT 0.00 AFTER table_fee');
}
if (!in_array('voucher_code', $oCols)) {
    $pdo->exec('ALTER TABLE orders ADD COLUMN voucher_code VARCHAR(50) NULL AFTER discount');
}

// 4. Lưu mã giao dịch từ cổng thanh toán nếu database được tạo từ schema cũ
$pCols = $pdo->query('SHOW COLUMNS FROM payments')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('transaction_id', $pCols, true)) {
    $pdo->exec('ALTER TABLE payments ADD COLUMN transaction_id VARCHAR(100) NULL AFTER received_by');
}

// 5. Thêm setting tự động hủy bàn quá hạn
$st = $pdo->prepare('INSERT IGNORE INTO restaurant_settings (`key`, `value`, `group_name`, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
$st->execute(['auto_release_late_minutes', '30', 'booking']);

// 6. Cập nhật VIP cho một số bàn để có dữ liệu thực tế
$pdo->exec("UPDATE restaurant_tables SET is_vip = 1, table_fee = 500000.00, vip_privileges = 'Quản gia riêng phục vụ, Khăn nóng ướp tinh dầu hoa hồng, 01 Đĩa trái cây cao cấp, Hoa tươi trên bàn', description = 'Không gian VIP sang trọng, view sông Sài Gòn, cách âm riêng tư' WHERE code LIKE 'VIP%' OR id IN (2, 3)");

echo "Database schema updated successfully!\n";
