<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/app/Core/Environment.php';
\App\Core\Environment::load($root . '/.env');
require_once $root . '/app/Core/Database.php';

$db = \App\Core\Database::connection();

// 1. Roles
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

// 2. Demo Users (Bảo toàn tài khoản hiện có nếu có)
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

// 3. Dining Areas (Khu vực nhà hàng thật)
$areas = [
    [
        'name' => 'Sảnh Vòm Grand Hall',
        'description' => 'Không gian vòm trần cao phong cách Pháp cổ điển, thưởng thức tiệc tối sang trọng và nhạc thính phòng',
        'sort_order' => 1,
    ],
    [
        'name' => 'Phòng VIP Hoàng Gia (Bordeaux Suite)',
        'description' => 'Phòng riêng biệt lập ấm cúng sang trọng, lý tưởng cho tiếp đãi ngoại giao và sự kiện kỷ niệm riêng tư',
        'sort_order' => 2,
    ],
    [
        'name' => 'Ban công Sân vườn & Terrace',
        'description' => 'Bàn tròn ngoài trời thoáng đãng nhìn ra đài phun nước trung tâm, ngắm hoàng hôn lãng mạn',
        'sort_order' => 3,
    ],
    [
        'name' => 'Quầy Lounge & Hầm Rượu Sommelier',
        'description' => 'Không gian ấm cúng thưởng thức cocktail tinh tế, rượu vang Grand Cru và các món khai vị nhẹ',
        'sort_order' => 4,
    ],
];

foreach ($areas as $a) {
    $stmt = $db->prepare('SELECT id FROM dining_areas WHERE name = :name');
    $stmt->execute(['name' => $a['name']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $update = $db->prepare('UPDATE dining_areas SET description = :description, sort_order = :sort_order WHERE id = :id');
        $update->execute(['description' => $a['description'], 'sort_order' => $a['sort_order'], 'id' => $existing['id']]);
    } else {
        $insert = $db->prepare('INSERT INTO dining_areas (name, description, sort_order) VALUES (:name, :description, :sort_order)');
        $insert->execute($a);
    }
}

$areaMap = $db->query('SELECT name, id FROM dining_areas')->fetchAll(PDO::FETCH_KEY_PAIR);

// 4. Restaurant Tables (Bàn ăn thật với sức chứa đa dạng từ 2 đến 12 khách)
$tables = [
    // Sảnh Vòm Grand Hall
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-01', 'name' => 'Bàn Cặp Đôi Vòm Đông', 'capacity' => 2, 'status' => 'empty', 'position_x' => 1, 'position_y' => 1],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-02', 'name' => 'Bàn Cặp Đôi Vòm Tây', 'capacity' => 2, 'status' => 'empty', 'position_x' => 2, 'position_y' => 1],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-03', 'name' => 'Bàn Tiệc Gia Đình 01', 'capacity' => 4, 'status' => 'empty', 'position_x' => 1, 'position_y' => 2],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-04', 'name' => 'Bàn Tiệc Gia Đình 02', 'capacity' => 4, 'status' => 'serving', 'position_x' => 2, 'position_y' => 2],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-05', 'name' => 'Bàn Tròn Trung Tâm', 'capacity' => 6, 'status' => 'empty', 'position_x' => 3, 'position_y' => 2],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-06', 'name' => 'Bàn Cạnh Dương Cầm', 'capacity' => 6, 'status' => 'reserved', 'position_x' => 1, 'position_y' => 3],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-07', 'name' => 'Bàn Cửa Sổ Grand 01', 'capacity' => 4, 'status' => 'empty', 'position_x' => 2, 'position_y' => 3],
    ['area_id' => $areaMap['Sảnh Vòm Grand Hall'], 'code' => 'T-08', 'name' => 'Bàn Cửa Sổ Grand 02', 'capacity' => 4, 'status' => 'empty', 'position_x' => 3, 'position_y' => 3],

    // Phòng VIP Hoàng Gia (Bordeaux Suite)
    ['area_id' => $areaMap['Phòng VIP Hoàng Gia (Bordeaux Suite)'], 'code' => 'VIP-01', 'name' => 'Bàn VIP Bordeaux Petit', 'capacity' => 4, 'status' => 'empty', 'position_x' => 1, 'position_y' => 1],
    ['area_id' => $areaMap['Phòng VIP Hoàng Gia (Bordeaux Suite)'], 'code' => 'VIP-02', 'name' => 'Bàn VIP Champagne Suite', 'capacity' => 6, 'status' => 'reserved', 'position_x' => 2, 'position_y' => 1],
    ['area_id' => $areaMap['Phòng VIP Hoàng Gia (Bordeaux Suite)'], 'code' => 'VIP-03', 'name' => 'Bàn VIP Bourgogne Grand', 'capacity' => 8, 'status' => 'empty', 'position_x' => 1, 'position_y' => 2],
    ['area_id' => $areaMap['Phòng VIP Hoàng Gia (Bordeaux Suite)'], 'code' => 'VIP-04', 'name' => 'Bàn Đại Tiệc Ngoại Giao', 'capacity' => 12, 'status' => 'empty', 'position_x' => 2, 'position_y' => 2],

    // Ban công Sân vườn & Terrace
    ['area_id' => $areaMap['Ban công Sân vườn & Terrace'], 'code' => 'B-01', 'name' => 'Bàn Đài Phun Nước 01', 'capacity' => 2, 'status' => 'empty', 'position_x' => 1, 'position_y' => 1],
    ['area_id' => $areaMap['Ban công Sân vườn & Terrace'], 'code' => 'B-02', 'name' => 'Bàn Đài Phun Nước 02', 'capacity' => 2, 'status' => 'empty', 'position_x' => 2, 'position_y' => 1],
    ['area_id' => $areaMap['Ban công Sân vườn & Terrace'], 'code' => 'B-03', 'name' => 'Bàn Dưới Giàn Hoa', 'capacity' => 4, 'status' => 'empty', 'position_x' => 1, 'position_y' => 2],
    ['area_id' => $areaMap['Ban công Sân vườn & Terrace'], 'code' => 'B-04', 'name' => 'Bàn Terrace Giữa Vườn', 'capacity' => 4, 'status' => 'awaiting_payment', 'position_x' => 2, 'position_y' => 2],
    ['area_id' => $areaMap['Ban công Sân vườn & Terrace'], 'code' => 'B-05', 'name' => 'Bàn Tiệc Trà Hoàng Hôn', 'capacity' => 6, 'status' => 'empty', 'position_x' => 1, 'position_y' => 3],
    ['area_id' => $areaMap['Ban công Sân vườn & Terrace'], 'code' => 'B-06', 'name' => 'Bàn Tiệc Ngoài Trời VIP', 'capacity' => 8, 'status' => 'empty', 'position_x' => 2, 'position_y' => 3],

    // Quầy Lounge & Hầm Rượu Sommelier
    ['area_id' => $areaMap['Quầy Lounge & Hầm Rượu Sommelier'], 'code' => 'BAR-01', 'name' => 'Ghế Sommelier Bar 01', 'capacity' => 2, 'status' => 'empty', 'position_x' => 1, 'position_y' => 1],
    ['area_id' => $areaMap['Quầy Lounge & Hầm Rượu Sommelier'], 'code' => 'BAR-02', 'name' => 'Ghế Sommelier Bar 02', 'capacity' => 2, 'status' => 'empty', 'position_x' => 2, 'position_y' => 1],
    ['area_id' => $areaMap['Quầy Lounge & Hầm Rượu Sommelier'], 'code' => 'LOUNGE-01', 'name' => 'Sofa Hầm Rượu 01', 'capacity' => 4, 'status' => 'empty', 'position_x' => 1, 'position_y' => 2],
    ['area_id' => $areaMap['Quầy Lounge & Hầm Rượu Sommelier'], 'code' => 'LOUNGE-02', 'name' => 'Sofa Hầm Rượu 02', 'capacity' => 6, 'status' => 'empty', 'position_x' => 2, 'position_y' => 2],
];

foreach ($tables as $t) {
    $stmt = $db->prepare('SELECT id FROM restaurant_tables WHERE code = :code');
    $stmt->execute(['code' => $t['code']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $update = $db->prepare('UPDATE restaurant_tables SET area_id = :area_id, name = :name, capacity = :capacity, status = :status, position_x = :position_x, position_y = :position_y WHERE id = :id');
        $update->execute([
            'area_id' => $t['area_id'],
            'name' => $t['name'],
            'capacity' => $t['capacity'],
            'status' => $t['status'],
            'position_x' => $t['position_x'],
            'position_y' => $t['position_y'],
            'id' => $existing['id'],
        ]);
    } else {
        $insert = $db->prepare('INSERT INTO restaurant_tables (area_id, code, name, capacity, status, position_x, position_y) VALUES (:area_id, :code, :name, :capacity, :status, :position_x, :position_y)');
        $insert->execute($t);
    }
}

// 5. Categories (Danh mục ẩm thực thật)
$categories = [
    [
        'name' => 'Món khai vị',
        'slug' => 'mon-khai-vi',
        'description' => 'Mở đầu tinh tế cho bữa tiệc ẩm thực Pháp cao cấp với các món khai vị tinh xảo kích thích trọn vẹn vị giác.',
        'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80',
        'sort_order' => 1,
    ],
    [
        'name' => 'Món chính - Bò & Thịt Thượng Hạng',
        'slug' => 'mon-chinh-thit-bo',
        'description' => 'Tuyệt tác ẩm thực từ thịt bò Wagyu Kagoshima A5 vân mỡ hoa cẩm thạch, sườn cừu non Provence và gan ngỗng hảo hạng.',
        'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=800&q=80',
        'sort_order' => 2,
    ],
    [
        'name' => 'Món chính - Hải Sản Biển Sâu',
        'slug' => 'mon-chinh-hai-san',
        'description' => 'Nguồn hải sản tươi sống đánh bắt tự nhiên từ cá tuyết Nam Cực, tôm hùm bông Nha Trang đến sò điệp biển Nhật Bản.',
        'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
        'sort_order' => 3,
    ],
    [
        'name' => 'Món tráng miệng',
        'slug' => 'mon-trang-mieng',
        'description' => 'Những sáng tạo ngọt ngào đầy thi vị tôn vinh kỹ nghệ làm bánh cổ điển danh tiếng của nước Pháp.',
        'image' => 'https://images.unsplash.com/photo-1579372786545-d24232daf58c?auto=format&fit=crop&w=800&q=80',
        'sort_order' => 4,
    ],
    [
        'name' => 'Đồ uống & Rượu vang',
        'slug' => 'do-uong-ruou-vang',
        'description' => 'Tuyển chọn từ những hầm rượu vang danh giá Bordeaux, Champagne thượng hạng và các thức uống thanh mát.',
        'image' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80',
        'sort_order' => 5,
    ],
];

foreach ($categories as $c) {
    $stmt = $db->prepare('SELECT id FROM categories WHERE slug = :slug');
    $stmt->execute(['slug' => $c['slug']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $update = $db->prepare('UPDATE categories SET name = :name, description = :description, image = :image, sort_order = :sort_order, is_visible = 1 WHERE id = :id');
        $update->execute([
            'name' => $c['name'],
            'description' => $c['description'],
            'image' => $c['image'],
            'sort_order' => $c['sort_order'],
            'id' => $existing['id'],
        ]);
    } else {
        $insert = $db->prepare('INSERT INTO categories (name, slug, description, image, sort_order, is_visible) VALUES (:name, :slug, :description, :image, :sort_order, 1)');
        $insert->execute($c);
    }
}

$catMap = $db->query('SELECT slug, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);

// 6. Dishes (Món ăn phong phú, giá tiền và mô tả thực tế chuẩn Fine Dining)
$dishes = [
    // Món khai vị
    [
        'category_id' => $catMap['mon-khai-vi'],
        'name' => 'Súp nấm Truffle đen Alba',
        'slug' => 'sup-nam-truffle-den-alba',
        'price' => 450000,
        'description' => 'Súp kem nấm béo ngậy kết hợp nấm kim cương đen Alba tươi nhập khẩu, dùng kèm bánh mì giòn bơ tỏi.',
        'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 15,
    ],
    [
        'category_id' => $catMap['mon-khai-vi'],
        'name' => 'Hàu tươi Gillardeau N°2',
        'slug' => 'hau-tuoi-gillardeau-no2',
        'price' => 680000,
        'description' => 'Đĩa 6 con hàu sữa hoàng gia Gillardeau số 2 từ Normandie, phục vụ trên đá kèm chanh vàng và sốt Mignonette giấm vang đỏ.',
        'image' => 'https://images.unsplash.com/photo-1625937329931-7e88c039fb2b?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 10,
    ],
    [
        'category_id' => $catMap['mon-khai-vi'],
        'name' => 'Trứng cá Tầm Beluga Caviar',
        'slug' => 'trung-ca-tam-beluga-caviar',
        'price' => 2400000,
        'description' => 'Hộp 30g trứng cá tầm đen Beluga nguyên bản ăn kèm bánh blini nóng hổi, kem chua crème fraîche và hành tây băm nhuyễn.',
        'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 10,
    ],
    [
        'category_id' => $catMap['mon-khai-vi'],
        'name' => 'Gan ngỗng áp chảo Rougié',
        'slug' => 'gan-ngong-ap-chao-rougie',
        'price' => 650000,
        'description' => 'Gan ngỗng Pháp Rougié áp chảo xém cạnh sốt quả mọng rừng và giấm đen Balsamic Modena 12 năm ủ thùng gỗ sồi.',
        'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 20,
    ],
    [
        'category_id' => $catMap['mon-khai-vi'],
        'name' => 'Salad Hoàng Gia Salade Niçoise',
        'slug' => 'salad-hoang-gia-salade-nicoise',
        'price' => 350000,
        'description' => 'Salad phong cách vùng Nice với cá ngừ vây vàng áp chảo tái, trứng lòng đào, ô liu Kalamata và sốt mù tạt Dijon.',
        'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 15,
    ],

    // Món chính - Thịt & Bò
    [
        'category_id' => $catMap['mon-chinh-thit-bo'],
        'name' => 'Bò Wagyu Kagoshima A5 Áp Chảo',
        'slug' => 'bo-wagyu-kagoshima-a5-ap-chao',
        'price' => 2850000,
        'description' => 'Thịt bò Wagyu Nhật Bản thượng hạng hạng A5 vân mỡ hoa cẩm thạch tuyệt mỹ, ăn kèm khoai tây nghiền nấm Truffle và sốt vang đỏ Bordeaux.',
        'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 25,
    ],
    [
        'category_id' => $catMap['mon-chinh-thit-bo'],
        'name' => 'Thăn cừu non nướng thảo mộc Provence',
        'slug' => 'than-cuu-non-nuong-thao-moc-provence',
        'price' => 1250000,
        'description' => 'Sườn cừu non New Zealand nướng đút lò phủ lớp bột thảo mộc thơm lừng vùng Provence, cà rốt baby om bơ và sốt demi-glace.',
        'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 25,
    ],
    [
        'category_id' => $catMap['mon-chinh-thit-bo'],
        'name' => 'Thăn nội bò Úc Black Angus sốt rượu vang',
        'slug' => 'than-noi-bo-uc-black-angus',
        'price' => 1450000,
        'description' => 'Filet Mignon bò Black Angus ủ sáp tuyết mềm mại tan chảy trong miệng, ăn kèm măng tây xào bơ tỏi và sốt vang đỏ nấm bụng dê.',
        'image' => 'https://images.unsplash.com/photo-1546964124-0cce460f38ef?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 20,
    ],
    [
        'category_id' => $catMap['mon-chinh-thit-bo'],
        'name' => 'Ức vịt sốt cam Magret de Canard',
        'slug' => 'uc-vit-sot-cam-magret-de-canard',
        'price' => 850000,
        'description' => 'Ức vịt Pháp áp chảo giòn da sốt cam mật ong Grand Marnier thanh dịu, củ cải đường nướng caramel béo thơm.',
        'image' => 'https://images.unsplash.com/photo-1514944298352-f41852037920?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 25,
    ],
    [
        'category_id' => $catMap['mon-chinh-thit-bo'],
        'name' => 'Sườn heo Iberico Tây Ban Nha nướng than',
        'slug' => 'suon-heo-iberico-nuong-than',
        'price' => 920000,
        'description' => 'Thịt heo Iberico Bellota nuôi thả tự nhiên bằng quả sồi, nướng than hoa thơm lừng và sốt nấm Morels rừng thơm ngát.',
        'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 25,
    ],

    // Món chính - Hải sản
    [
        'category_id' => $catMap['mon-chinh-hai-san'],
        'name' => 'Tôm hùm bông Nha Trang nướng sốt Thermidor',
        'slug' => 'tom-hum-bong-nuong-sot-thermidor',
        'price' => 1950000,
        'description' => 'Nửa con tôm hùm bông tươi sống sốt kem nấm mù tạt Pháp đút lò phô mai Gruyère vàng óng, ăn kèm rau củ hấp bơ tỏi.',
        'image' => 'https://images.unsplash.com/photo-1559737558-245cb7c16bb8?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 30,
    ],
    [
        'category_id' => $catMap['mon-chinh-hai-san'],
        'name' => 'Cá tuyết Nam Cực áp chảo sốt bơ chanh',
        'slug' => 'ca-tuyet-nam-cuc-ap-chao-sot-bo-chanh',
        'price' => 980000,
        'description' => 'Cá tuyết Patagonian Toothfish trắng ngần mọng nước, áp chảo da giòn tan kết hợp sốt bơ chanh Capers béo ngậy thanh tao.',
        'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 20,
    ],
    [
        'category_id' => $catMap['mon-chinh-hai-san'],
        'name' => 'Sò điệp Hokkaido áp chảo sốt nghệ tây Saffron',
        'slug' => 'so-diep-hokkaido-ap-chao-sot-saffron',
        'price' => 720000,
        'description' => 'Sò điệp Nhật Bản cỡ lớn áp chảo xém vàng, dùng kèm măng tây xanh, sốt bơ kem nghệ tây Saffron và trứng cá chuồn.',
        'image' => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 15,
    ],
    [
        'category_id' => $catMap['mon-chinh-hai-san'],
        'name' => 'Cá hồi Na Uy áp chảo sốt kem thì là',
        'slug' => 'ca-hoi-na-uy-ap-chao-sot-kem-thi-la',
        'price' => 690000,
        'description' => 'Cá hồi Na Uy phi lê áp chảo vừa chín tới mọng nước, sốt kem trắng rượu Chablis và lá thì là tươi.',
        'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 20,
    ],

    // Món tráng miệng
    [
        'category_id' => $catMap['mon-trang-mieng'],
        'name' => 'Bánh Soufflé Cam Grand Marnier',
        'slug' => 'banh-souffle-cam-grand-marnier',
        'price' => 320000,
        'description' => 'Bánh Soufflé bông xốp nướng trực tiếp tại bàn khi thực khách gọi, dậy mùi rượu cam Grand Marnier, dùng kèm kem vani Bourbon.',
        'image' => 'https://images.unsplash.com/photo-1579372786545-d24232daf58c?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 20,
    ],
    [
        'category_id' => $catMap['mon-trang-mieng'],
        'name' => 'Bánh Tart Tatin Táo đút lò & Kem Vani',
        'slug' => 'banh-tart-tatin-tao-kem-vani',
        'price' => 280000,
        'description' => 'Bánh táo nướng lật ngược caramel truyền thống kiểu Pháp với táo Granny Smith ngọt thanh và lớp vỏ ngàn lớp giòn rụm.',
        'image' => 'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 15,
    ],
    [
        'category_id' => $catMap['mon-trang-mieng'],
        'name' => 'Crème Brûlée hạt dẻ cười Pistachio',
        'slug' => 'creme-brulee-hat-de-cuoi-pistachio',
        'price' => 250000,
        'description' => 'Kem trứng nướng béo mịn vị hạt dẻ cười với lớp đường nâu đốt caramen giòn tan thơm phức chuẩn phong vị ẩm thực Paris.',
        'image' => 'https://images.unsplash.com/photo-1470124182917-cc6e71b22ecc?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 10,
    ],
    [
        'category_id' => $catMap['mon-trang-mieng'],
        'name' => 'Mousse Socola đen Valrhona 70% dát vàng',
        'slug' => 'mousse-socola-den-valrhona-dat-vang',
        'price' => 310000,
        'description' => 'Mousse socola đen Pháp thượng hạng Valrhona 70% đắng nhẹ êm ái, điểm xuyết lá vàng thực phẩm 24k quý phái.',
        'image' => 'https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 10,
    ],

    // Đồ uống & Rượu vang
    [
        'category_id' => $catMap['do-uong-ruou-vang'],
        'name' => 'Vang đỏ Château Margaux Premier Grand Cru 2012',
        'slug' => 'vang-chateau-margaux-premier-grand-cru-2012',
        'price' => 19500000,
        'description' => 'Chai 750ml, niên vụ 2012 lừng danh của lâu đài Château Margaux vùng Bordeaux. Hương vị phức hợp từ trái cây đen, gỗ tuyết tùng và thuốc lá Cuba.',
        'image' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 5,
    ],
    [
        'category_id' => $catMap['do-uong-ruou-vang'],
        'name' => 'Vang đỏ Margaux Pavillon Rouge 2015',
        'slug' => 'vang-margaux-pavillon-rouge-2015',
        'price' => 6800000,
        'description' => 'Dòng vang thứ hai trứ danh của Château Margaux với cấu trúc tannin mượt mà như nhung và hậu vị sâu lắng kéo dài.',
        'image' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 5,
    ],
    [
        'category_id' => $catMap['do-uong-ruou-vang'],
        'name' => 'Vang trắng Chablis Premier Cru Domaine Laroche 2020',
        'slug' => 'vang-trang-chablis-premier-cru-2020',
        'price' => 3400000,
        'description' => 'Chai 750ml giống nho Chardonnay từ vùng Bourgogne trứ danh, hương khoáng đạt đậm đà và acid tươi mát, hoàn hảo khi dùng với hải sản và hàu tươi.',
        'image' => 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 5,
    ],
    [
        'category_id' => $catMap['do-uong-ruou-vang'],
        'name' => 'Champagne Dom Pérignon Vintage Brut',
        'slug' => 'champagne-dom-perignon-vintage-brut',
        'price' => 8900000,
        'description' => 'Tuyệt tác Champagne cổ điển nước Pháp với bọt sủi tăm mịn màng, hương bánh mì nướng brioche và trái cây khô sang trọng.',
        'image' => 'https://images.unsplash.com/photo-1569919659476-f0852f6834b7?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 5,
    ],
    [
        'category_id' => $catMap['do-uong-ruou-vang'],
        'name' => 'Nước khoáng có ga San Pellegrino 750ml',
        'slug' => 'nuoc-khoang-san-pellegrino-750ml',
        'price' => 160000,
        'description' => 'Nước khoáng có ga đóng chai thủy tinh tự nhiên nhập khẩu từ dãy Alps nước Ý, mang vị khoáng dịu nhẹ kích thích khẩu vị.',
        'image' => 'https://images.unsplash.com/photo-1559839914-17aae19cec71?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 2,
    ],
    [
        'category_id' => $catMap['do-uong-ruou-vang'],
        'name' => 'Nước ép quả mọng hữu cơ Épicure Detox',
        'slug' => 'nuoc-ep-qua-mong-huu-co-epicure-detox',
        'price' => 180000,
        'description' => 'Nước ép tươi pha chế từ mâm xôi đen, việt quất và dâu tây hữu cơ, kết hợp mật ong hoa rừng tinh khiết.',
        'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
        'preparation_time' => 5,
    ],
];

foreach ($dishes as $d) {
    $stmt = $db->prepare('SELECT id FROM dishes WHERE slug = :slug');
    $stmt->execute(['slug' => $d['slug']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $update = $db->prepare('UPDATE dishes SET category_id = :category_id, name = :name, price = :price, description = :description, image = :image, preparation_time = :preparation_time, is_available = 1, is_visible = 1 WHERE id = :id');
        $update->execute([
            'category_id' => $d['category_id'],
            'name' => $d['name'],
            'price' => $d['price'],
            'description' => $d['description'],
            'image' => $d['image'],
            'preparation_time' => $d['preparation_time'],
            'id' => $existing['id'],
        ]);
    } else {
        $insert = $db->prepare('INSERT INTO dishes (category_id, name, slug, price, description, image, preparation_time, is_available, is_visible) VALUES (:category_id, :name, :slug, :price, :description, :image, :preparation_time, 1, 1)');
        $insert->execute($d);
    }
}

// Thống kê kết quả
$totalCategories = $db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalDishes = $db->query('SELECT COUNT(*) FROM dishes')->fetchColumn();
$totalAreas = $db->query('SELECT COUNT(*) FROM dining_areas')->fetchColumn();
$totalTables = $db->query('SELECT COUNT(*) FROM restaurant_tables')->fetchColumn();
$totalUsers = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

echo "=== HOÀN TẤT SEED DỮ LIỆU NHÀ HÀNG THẬT ===\n";
echo "- Danh mục (categories): " . $totalCategories . "\n";
echo "- Món ăn (dishes): " . $totalDishes . "\n";
echo "- Khu vực (dining_areas): " . $totalAreas . "\n";
echo "- Bàn ăn (restaurant_tables): " . $totalTables . "\n";
echo "- Người dùng (users): " . $totalUsers . "\n";
