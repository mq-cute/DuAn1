<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/app/Core/Environment.php';
\App\Core\Environment::load($root . '/.env');
require_once $root . '/app/Core/Database.php';

$db = \App\Core\Database::connection();

// Insert or update roles
$roles = [
    'admin' => 'Quản trị viên',
    'staff' => 'Nhân viên phục vụ',
    'kitchen' => 'Nhân viên bếp',
    'customer' => 'Khách hàng',
];
foreach ($roles as $name => $desc) {
    $stmt = $db->prepare('INSERT INTO roles (name, description) VALUES (:name, :desc) ON DUPLICATE KEY UPDATE description = VALUES(description)');
    $stmt->execute(['name' => $name, 'desc' => $desc]);
}

$roleMap = $db->query('SELECT name, id FROM roles')->fetchAll(PDO::FETCH_KEY_PAIR);

// Users
$users = [
    [
        'role_id' => $roleMap['admin'],
        'name' => 'Quản trị viên Hệ thống',
        'email' => 'admin@lepicure.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '0901000001',
    ],
    [
        'role_id' => $roleMap['staff'],
        'name' => 'Nguyễn Hoàng Nam',
        'email' => 'staff@lepicure.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '0901000002',
    ],
    [
        'role_id' => $roleMap['kitchen'],
        'name' => 'Trần Bếp Trưởng',
        'email' => 'kitchen@lepicure.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '0901000003',
    ],
    [
        'role_id' => $roleMap['customer'],
        'name' => 'Jean-Pierre Laurent',
        'email' => 'customer@lepicure.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '0901000004',
    ],
    [
        'role_id' => $roleMap['customer'],
        'name' => 'Trần Minh Thư',
        'email' => 'thu.tran@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '0902345678',
    ],
    [
        'role_id' => $roleMap['customer'],
        'name' => 'David Sterling',
        'email' => 'david.sterling@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '0903456789',
    ],
];

foreach ($users as $u) {
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $u['email']]);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('INSERT INTO users (role_id, name, email, password, phone, status) VALUES (:role_id, :name, :email, :password, :phone, "active")');
        $insert->execute($u);
    }
}

// Dining areas
$areas = [
    ['name' => 'Phòng VIP Bordeaux', 'description' => 'Phòng riêng sang trọng, ấm cúng dành cho thực khách VIP', 'sort_order' => 1],
    ['name' => 'Sảnh Vòm Grand Hall', 'description' => 'Không gian vòm cổ điển Pháp, thưởng thức nhạc thính phòng', 'sort_order' => 2],
    ['name' => 'Ban công Sân vườn', 'description' => 'Bàn tròn ngoài trời thoáng đãng, view đài phun nước', 'sort_order' => 3],
];

foreach ($areas as $a) {
    $stmt = $db->prepare('SELECT id FROM dining_areas WHERE name = :name');
    $stmt->execute(['name' => $a['name']]);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('INSERT INTO dining_areas (name, description, sort_order) VALUES (:name, :description, :sort_order)');
        $insert->execute($a);
    }
}

$areaMap = $db->query('SELECT name, id FROM dining_areas')->fetchAll(PDO::FETCH_KEY_PAIR);

// Tables
$tables = [
    ['area_id' => $areaMap['Phòng VIP Bordeaux'], 'code' => 'VIP-01', 'name' => 'Bàn VIP 01', 'capacity' => 4, 'status' => 'empty', 'position_x' => 1, 'position_y' => 1],
    ['area_id' => $areaMap['Phòng VIP Bordeaux'], 'code' => 'VIP-02', 'name' => 'Bàn VIP 02', 'capacity' => 6, 'status' => 'serving', 'position_x' => 2, 'position_y' => 1],
    ['area_id' => $areaMap['Phòng VIP Bordeaux'], 'code' => 'VIP-03', 'name' => 'Bàn VIP 03', 'capacity' => 8, 'status' => 'empty', 'position_x' => 3, 'position_y' => 1],

    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-01', 'name' => 'Bàn T-01', 'capacity' => 2, 'status' => 'empty', 'position_x' => 1, 'position_y' => 2],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-02', 'name' => 'Bàn T-02', 'capacity' => 4, 'status' => 'empty', 'position_x' => 2, 'position_y' => 2],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-03', 'name' => 'Bàn T-03', 'capacity' => 4, 'status' => 'empty', 'position_x' => 3, 'position_y' => 2],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-07', 'name' => 'Bàn T-07', 'capacity' => 4, 'status' => 'reserved', 'position_x' => 1, 'position_y' => 3],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-08', 'name' => 'Bàn T-08', 'capacity' => 4, 'status' => 'serving', 'position_x' => 2, 'position_y' => 3],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-09', 'name' => 'Bàn T-09', 'capacity' => 4, 'status' => 'empty', 'position_x' => 3, 'position_y' => 3],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-10', 'name' => 'Bàn T-10', 'capacity' => 2, 'status' => 'empty', 'position_x' => 4, 'position_y' => 3],

    ['area_id' => $areaMap['Ban công Sân vườn'], 'code' => 'B-01', 'name' => 'Bàn B-01', 'capacity' => 2, 'status' => 'empty', 'position_x' => 1, 'position_y' => 4],
    ['area_id' => $areaMap['Ban công Sân vườn'], 'code' => 'B-02', 'name' => 'Bàn B-02', 'capacity' => 4, 'status' => 'empty', 'position_x' => 2, 'position_y' => 4],
    ['area_id' => $areaMap['Ban công Sân vườn'], 'code' => 'B-04', 'name' => 'Bàn B-04', 'capacity' => 6, 'status' => 'awaiting_payment', 'position_x' => 3, 'position_y' => 4],
];

foreach ($tables as $t) {
    $stmt = $db->prepare('SELECT id FROM restaurant_tables WHERE code = :code');
    $stmt->execute(['code' => $t['code']]);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('INSERT INTO restaurant_tables (area_id, code, name, capacity, status, position_x, position_y) VALUES (:area_id, :code, :name, :capacity, :status, :position_x, :position_y)');
        $insert->execute($t);
    }
}

// Categories
$categories = [
    ['name' => 'Món khai vị', 'slug' => 'mon-khai-vi', 'description' => 'Mở đầu tinh tế cho bữa tiệc ẩm thực Pháp cao cấp', 'sort_order' => 1],
    ['name' => 'Món chính', 'slug' => 'mon-chinh', 'description' => 'Tuyệt tác ẩm thực từ thịt bò Wagyu, gan ngỗng và hải sản thượng hạng', 'sort_order' => 2],
    ['name' => 'Món tráng miệng', 'slug' => 'mon-trang-mieng', 'description' => 'Ngọt ngào, nhẹ nhàng kết thúc bữa tiệc hoàn hảo', 'sort_order' => 3],
    ['name' => 'Đồ uống & Rượu vang', 'slug' => 'do-uong-ruou-vang', 'description' => 'Tuyển chọn từ những hầm rượu danh tiếng của Bordeaux và Champagne', 'sort_order' => 4],
];

foreach ($categories as $c) {
    $stmt = $db->prepare('SELECT id FROM categories WHERE slug = :slug');
    $stmt->execute(['slug' => $c['slug']]);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('INSERT INTO categories (name, slug, description, sort_order, is_visible) VALUES (:name, :slug, :description, :sort_order, 1)');
        $insert->execute($c);
    }
}

$catMap = $db->query('SELECT slug, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);

// Dishes
$dishes = [
    ['category_id' => $catMap['mon-khai-vi'], 'name' => 'Súp nấm Truffle đen Alba', 'slug' => 'sup-nam-truffle-den-alba', 'price' => 450000, 'description' => 'Súp kem béo mịn kết hợp nấm Truffle đen Alba nhập khẩu tươi ngon.', 'preparation_time' => 15],
    ['category_id' => $catMap['mon-khai-vi'], 'name' => 'Hàu Gillardeau N°2', 'slug' => 'hau-gillardeau-no2', 'price' => 680000, 'description' => 'Hàu sống thượng hạng dùng kèm chanh vàng và sốt Mignonette giấm vang đỏ.', 'preparation_time' => 10],
    ['category_id' => $catMap['mon-khai-vi'], 'name' => 'Trứng cá Tầm Beluga', 'slug' => 'trung-ca-tam-beluga', 'price' => 2400000, 'description' => 'Trứng cá tầm đen Beluga nguyên bản ăn kèm bánh blini và kem chua crème fraîche.', 'preparation_time' => 10],
    ['category_id' => $catMap['mon-khai-vi'], 'name' => 'Gan ngỗng áp chảo Rougié', 'slug' => 'gan-ngong-ap-chao-rougie', 'price' => 650000, 'description' => 'Foie gras Rougié áp chảo sốt quả mọng và giấm Balsamic Modena 12 năm.', 'preparation_time' => 20],

    ['category_id' => $catMap['mon-chinh'], 'name' => 'Bò Wagyu Kagoshima A5 áp chảo', 'slug' => 'bo-wagyu-kagoshima-a5', 'price' => 2850000, 'description' => 'Bò Wagyu Kagoshima vân mỡ cẩm thạch tuyệt mỹ, ăn kèm khoai tây nghiền Truffle và sốt rượu vang đỏ.', 'preparation_time' => 25],
    ['category_id' => $catMap['mon-chinh'], 'name' => 'Thăn cừu nướng thảo mộc Provence', 'slug' => 'than-cuu-nuong-thao-moc', 'price' => 1250000, 'description' => 'Sườn cừu non nướng lớp vỏ thảo mộc vùng Provence thơm lừng, sốt demi-glace.', 'preparation_time' => 25],
    ['category_id' => $catMap['mon-chinh'], 'name' => 'Cá tuyết Nam Cực áp chảo sốt bơ chanh', 'slug' => 'ca-tuyet-nam-cuc-sot-bo-chanh', 'price' => 980000, 'description' => 'Cá tuyết thịt trắng ngọt ngào, áp chảo da giòn tan kết hợp sốt bơ chanh béo ngậy.', 'preparation_time' => 20],

    ['category_id' => $catMap['mon-trang-mieng'], 'name' => 'Bánh Soufflé Cam Grand Marnier', 'slug' => 'banh-souffle-cam-grand-marnier', 'price' => 320000, 'description' => 'Soufflé bông xốp nướng trực tiếp tại chỗ kết hợp kem vani Madagascar.', 'preparation_time' => 20],
    ['category_id' => $catMap['mon-trang-mieng'], 'name' => 'Tart Tatin Táo & Kem Vani', 'slug' => 'tart-tatin-tao-kem-vani', 'price' => 280000, 'description' => 'Bánh táo nướng caramel truyền thống Pháp, dùng nóng cùng một viên kem vani.', 'preparation_time' => 15],

    ['category_id' => $catMap['do-uong-ruou-vang'], 'name' => 'Vang Château Margaux Premier Grand Cru 2012', 'slug' => 'chateau-margaux-2012', 'price' => 19500000, 'description' => 'Tuyệt tác rượu vang Bordeaux phân hạng Premier Grand Cru Classé 1855, hương thơm phong phú.', 'preparation_time' => 5],
    ['category_id' => $catMap['do-uong-ruou-vang'], 'name' => 'Vang Margaux Pavillon Rouge 2015', 'slug' => 'margaux-pavillon-rouge-2015', 'price' => 6800000, 'description' => 'Dòng vang thứ hai của Château Margaux với cấu trúc cân bằng hoàn hảo.', 'preparation_time' => 5],
    ['category_id' => $catMap['do-uong-ruou-vang'], 'name' => 'Nước khoáng San Pellegrino 750ml', 'slug' => 'nuoc-khoang-san-pellegrino', 'price' => 160000, 'description' => 'Nước khoáng có ga tự nhiên từ Ý, chai thủy tinh thanh lịch.', 'preparation_time' => 2],
];

foreach ($dishes as $d) {
    $stmt = $db->prepare('SELECT id FROM dishes WHERE slug = :slug');
    $stmt->execute(['slug' => $d['slug']]);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('INSERT INTO dishes (category_id, name, slug, price, description, preparation_time, is_available, is_visible) VALUES (:category_id, :name, :slug, :price, :description, :preparation_time, 1, 1)');
        $insert->execute($d);
    }
}

echo "Sample data successfully seeded!\n";
