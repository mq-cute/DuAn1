-- ==============================================================================
-- DỮ LIỆU NHÀ HÀNG THẬT: DANH MỤC, MÓN ĂN, KHU VỰC VÀ BÀN ĂN
-- Dự án: L'Épicure Gastronomie (Dự án 1)
-- ==============================================================================

USE `restaurant_booking`;

-- 1. ROLES
INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Quản trị viên'),
('staff', 'Nhân viên phục vụ'),
('kitchen', 'Nhân viên bếp'),
('customer', 'Khách hàng')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 2. KHU VỰC NHÀ HÀNG (dining_areas)
INSERT INTO `dining_areas` (`id`, `name`, `description`, `sort_order`) VALUES
(1, 'Sảnh Vòm Grand Hall', 'Không gian vòm trần cao phong cách Pháp cổ điển, thưởng thức tiệc tối sang trọng và nhạc thính phòng', 1),
(2, 'Phòng VIP Hoàng Gia (Bordeaux Suite)', 'Phòng riêng biệt lập ấm cúng sang trọng, lý tưởng cho tiếp đãi ngoại giao và sự kiện kỷ niệm riêng tư', 2),
(3, 'Ban công Sân vườn & Terrace', 'Bàn tròn ngoài trời thoáng đãng nhìn ra đài phun nước trung tâm, ngắm hoàng hôn lãng mạn', 3),
(4, 'Quầy Lounge & Hầm Rượu Sommelier', 'Không gian ấm cúng thưởng thức cocktail tinh tế, rượu vang Grand Cru và các món khai vị nhẹ', 4)
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `sort_order` = VALUES(`sort_order`);

-- 3. BÀN ĂN (restaurant_tables)
INSERT INTO `restaurant_tables` (`area_id`, `code`, `name`, `capacity`, `status`, `position_x`, `position_y`) VALUES
-- Sảnh Vòm Grand Hall
(1, 'T-01', 'Bàn Cặp Đôi Vòm Đông', 2, 'empty', 1, 1),
(1, 'T-02', 'Bàn Cặp Đôi Vòm Tây', 2, 'empty', 2, 1),
(1, 'T-03', 'Bàn Tiệc Gia Đình 01', 4, 'empty', 1, 2),
(1, 'T-04', 'Bàn Tiệc Gia Đình 02', 4, 'serving', 2, 2),
(1, 'T-05', 'Bàn Tròn Trung Tâm', 6, 'empty', 3, 2),
(1, 'T-06', 'Bàn Cạnh Dương Cầm', 6, 'reserved', 1, 3),
(1, 'T-07', 'Bàn Cửa Sổ Grand 01', 4, 'empty', 2, 3),
(1, 'T-08', 'Bàn Cửa Sổ Grand 02', 4, 'empty', 3, 3),

-- Phòng VIP Hoàng Gia (Bordeaux Suite)
(2, 'VIP-01', 'Bàn VIP Bordeaux Petit', 4, 'empty', 1, 1),
(2, 'VIP-02', 'Bàn VIP Champagne Suite', 6, 'reserved', 2, 1),
(2, 'VIP-03', 'Bàn VIP Bourgogne Grand', 8, 'empty', 1, 2),
(2, 'VIP-04', 'Bàn Đại Tiệc Ngoại Giao', 12, 'empty', 2, 2),

-- Ban công Sân vườn & Terrace
(3, 'B-01', 'Bàn Đài Phun Nước 01', 2, 'empty', 1, 1),
(3, 'B-02', 'Bàn Đài Phun Nước 02', 2, 'empty', 2, 1),
(3, 'B-03', 'Bàn Dưới Giàn Hoa', 4, 'empty', 1, 2),
(3, 'B-04', 'Bàn Terrace Giữa Vườn', 4, 'awaiting_payment', 2, 2),
(3, 'B-05', 'Bàn Tiệc Trà Hoàng Hôn', 6, 'empty', 1, 3),
(3, 'B-06', 'Bàn Tiệc Ngoài Trời VIP', 8, 'empty', 2, 3),

-- Quầy Lounge & Hầm Rượu Sommelier
(4, 'BAR-01', 'Ghế Sommelier Bar 01', 2, 'empty', 1, 1),
(4, 'BAR-02', 'Ghế Sommelier Bar 02', 2, 'empty', 2, 1),
(4, 'LOUNGE-01', 'Sofa Hầm Rượu 01', 4, 'empty', 1, 2),
(4, 'LOUNGE-02', 'Sofa Hầm Rượu 02', 6, 'empty', 2, 2)
ON DUPLICATE KEY UPDATE
    `area_id` = VALUES(`area_id`),
    `name` = VALUES(`name`),
    `capacity` = VALUES(`capacity`),
    `status` = VALUES(`status`),
    `position_x` = VALUES(`position_x`),
    `position_y` = VALUES(`position_y`);

-- 4. DANH MỤC ẨM THỰC (categories)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `sort_order`, `is_visible`) VALUES
(1, 'Món khai vị', 'mon-khai-vi', 'Mở đầu tinh tế cho bữa tiệc ẩm thực Pháp cao cấp với các món khai vị tinh xảo kích thích trọn vẹn vị giác.', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80', 1, 1),
(2, 'Món chính - Bò & Thịt Thượng Hạng', 'mon-chinh-thit-bo', 'Tuyệt tác ẩm thực từ thịt bò Wagyu Kagoshima A5 vân mỡ hoa cẩm thạch, sườn cừu non Provence và gan ngỗng hảo hạng.', 'https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=800&q=80', 2, 1),
(3, 'Món chính - Hải Sản Biển Sâu', 'mon-chinh-hai-san', 'Nguồn hải sản tươi sống đánh bắt tự nhiên từ cá tuyết Nam Cực, tôm hùm bông Nha Trang đến sò điệp biển Nhật Bản.', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80', 3, 1),
(4, 'Món tráng miệng', 'mon-trang-mieng', 'Những sáng tạo ngọt ngào đầy thi vị tôn vinh kỹ nghệ làm bánh cổ điển danh tiếng của nước Pháp.', 'https://images.unsplash.com/photo-1579372786545-d24232daf58c?auto=format&fit=crop&w=800&q=80', 4, 1),
(5, 'Đồ uống & Rượu vang', 'do-uong-ruou-vang', 'Tuyển chọn từ những hầm rượu vang danh giá Bordeaux, Champagne thượng hạng và các thức uống thanh mát.', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80', 5, 1)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `image` = VALUES(`image`),
    `sort_order` = VALUES(`sort_order`),
    `is_visible` = VALUES(`is_visible`);

-- 5. MÓN ĂN THỰC ĐƠN (dishes)
INSERT INTO `dishes` (`category_id`, `name`, `slug`, `price`, `description`, `image`, `preparation_time`, `is_available`, `is_visible`) VALUES
-- Món khai vị
(1, 'Súp nấm Truffle đen Alba', 'sup-nam-truffle-den-alba', 450000.00, 'Súp kem nấm béo ngậy kết hợp nấm kim cương đen Alba tươi nhập khẩu, dùng kèm bánh mì giòn bơ tỏi.', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80', 15, 1, 1),
(1, 'Hàu tươi Gillardeau N°2', 'hau-tuoi-gillardeau-no2', 680000.00, 'Đĩa 6 con hàu sữa hoàng gia Gillardeau số 2 từ Normandie, phục vụ trên đá kèm chanh vàng và sốt Mignonette giấm vang đỏ.', 'https://images.unsplash.com/photo-1625937329931-7e88c039fb2b?auto=format&fit=crop&w=800&q=80', 10, 1, 1),
(1, 'Trứng cá Tầm Beluga Caviar', 'trung-ca-tam-beluga-caviar', 2400000.00, 'Hộp 30g trứng cá tầm đen Beluga nguyên bản ăn kèm bánh blini nóng hổi, kem chua crème fraîche và hành tây băm nhuyễn.', 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=800&q=80', 10, 1, 1),
(1, 'Gan ngỗng áp chảo Rougié', 'gan-ngong-ap-chao-rougie', 650000.00, 'Gan ngỗng Pháp Rougié áp chảo xém cạnh sốt quả mọng rừng và giấm đen Balsamic Modena 12 năm ủ thùng gỗ sồi.', 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80', 20, 1, 1),
(1, 'Salad Hoàng Gia Salade Niçoise', 'salad-hoang-gia-salade-nicoise', 350000.00, 'Salad phong cách vùng Nice với cá ngừ vây vàng áp chảo tái, trứng lòng đào, ô liu Kalamata và sốt mù tạt Dijon.', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80', 15, 1, 1),

-- Món chính - Bò & Thịt Thượng Hạng
(2, 'Bò Wagyu Kagoshima A5 Áp Chảo', 'bo-wagyu-kagoshima-a5-ap-chao', 2850000.00, 'Thịt bò Wagyu Nhật Bản thượng hạng hạng A5 vân mỡ hoa cẩm thạch tuyệt mỹ, ăn kèm khoai tây nghiền nấm Truffle và sốt vang đỏ Bordeaux.', 'https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=800&q=80', 25, 1, 1),
(2, 'Thăn cừu non nướng thảo mộc Provence', 'than-cuu-non-nuong-thao-moc-provence', 1250000.00, 'Sườn cừu non New Zealand nướng đút lò phủ lớp bột thảo mộc thơm lừng vùng Provence, cà rốt baby om bơ và sốt demi-glace.', 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80', 25, 1, 1),
(2, 'Thăn nội bò Úc Black Angus sốt rượu vang', 'than-noi-bo-uc-black-angus', 1450000.00, 'Filet Mignon bò Black Angus ủ sáp tuyết mềm mại tan chảy trong miệng, ăn kèm măng tây xào bơ tỏi và sốt vang đỏ nấm bụng dê.', 'https://images.unsplash.com/photo-1546964124-0cce460f38ef?auto=format&fit=crop&w=800&q=80', 20, 1, 1),
(2, 'Ức vịt sốt cam Magret de Canard', 'uc-vit-sot-cam-magret-de-canard', 850000.00, 'Ức vịt Pháp áp chảo giòn da sốt cam mật ong Grand Marnier thanh dịu, củ cải đường nướng caramel béo thơm.', 'https://images.unsplash.com/photo-1514944298352-f41852037920?auto=format&fit=crop&w=800&q=80', 25, 1, 1),
(2, 'Sườn heo Iberico Tây Ban Nha nướng than', 'suon-heo-iberico-nuong-than', 920000.00, 'Thịt heo Iberico Bellota nuôi thả tự nhiên bằng quả sồi, nướng than hoa thơm lừng và sốt nấm Morels rừng thơm ngát.', 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80', 25, 1, 1),

-- Món chính - Hải Sản Biển Sâu
(3, 'Tôm hùm bông Nha Trang nướng sốt Thermidor', 'tom-hum-bong-nuong-sot-thermidor', 1950000.00, 'Nửa con tôm hùm bông tươi sống sốt kem nấm mù tạt Pháp đút lò phô mai Gruyère vàng óng, ăn kèm rau củ hấp bơ tỏi.', 'https://images.unsplash.com/photo-1559737558-245cb7c16bb8?auto=format&fit=crop&w=800&q=80', 30, 1, 1),
(3, 'Cá tuyết Nam Cực áp chảo sốt bơ chanh', 'ca-tuyet-nam-cuc-ap-chao-sot-bo-chanh', 980000.00, 'Cá tuyết Patagonian Toothfish trắng ngần mọng nước, áp chảo da giòn tan kết hợp sốt bơ chanh Capers béo ngậy thanh tao.', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80', 20, 1, 1),
(3, 'Sò điệp Hokkaido áp chảo sốt nghệ tây Saffron', 'so-diep-hokkaido-ap-chao-sot-saffron', 720000.00, 'Sò điệp Nhật Bản cỡ lớn áp chảo xém vàng, dùng kèm măng tây xanh, sốt bơ kem nghệ tây Saffron và trứng cá chuồn.', 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=800&q=80', 15, 1, 1),
(3, 'Cá hồi Na Uy áp chảo sốt kem thì là', 'ca-hoi-na-uy-ap-chao-sot-kem-thi-la', 690000.00, 'Cá hồi Na Uy phi lê áp chảo vừa chín tới mọng nước, sốt kem trắng rượu Chablis và lá thì là tươi.', 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80', 20, 1, 1),

-- Món tráng miệng
(4, 'Bánh Soufflé Cam Grand Marnier', 'banh-souffle-cam-grand-marnier', 320000.00, 'Bánh Soufflé bông xốp nướng trực tiếp tại bàn khi thực khách gọi, dậy mùi rượu cam Grand Marnier, dùng kèm kem vani Bourbon.', 'https://images.unsplash.com/photo-1579372786545-d24232daf58c?auto=format&fit=crop&w=800&q=80', 20, 1, 1),
(4, 'Bánh Tart Tatin Táo đút lò & Kem Vani', 'banh-tart-tatin-tao-kem-vani', 280000.00, 'Bánh táo nướng lật ngược caramel truyền thống kiểu Pháp với táo Granny Smith ngọt thanh và lớp vỏ ngàn lớp giòn rụm.', 'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?auto=format&fit=crop&w=800&q=80', 15, 1, 1),
(4, 'Crème Brûlée hạt dẻ cười Pistachio', 'creme-brulee-hat-de-cuoi-pistachio', 250000.00, 'Kem trứng nướng béo mịn vị hạt dẻ cười với lớp đường nâu đốt caramen giòn tan thơm phức chuẩn phong vị ẩm thực Paris.', 'https://images.unsplash.com/photo-1470124182917-cc6e71b22ecc?auto=format&fit=crop&w=800&q=80', 10, 1, 1),
(4, 'Mousse Socola đen Valrhona 70% dát vàng', 'mousse-socola-den-valrhona-dat-vang', 310000.00, 'Mousse socola đen Pháp thượng hạng Valrhona 70% đắng nhẹ êm ái, điểm xuyết lá vàng thực phẩm 24k quý phái.', 'https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?auto=format&fit=crop&w=800&q=80', 10, 1, 1),

-- Đồ uống & Rượu vang
(5, 'Vang đỏ Château Margaux Premier Grand Cru 2012', 'vang-chateau-margaux-premier-grand-cru-2012', 19500000.00, 'Chai 750ml, niên vụ 2012 lừng danh của lâu đài Château Margaux vùng Bordeaux. Hương vị phức hợp từ trái cây đen, gỗ tuyết tùng và thuốc lá Cuba.', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80', 5, 1, 1),
(5, 'Vang đỏ Margaux Pavillon Rouge 2015', 'vang-margaux-pavillon-rouge-2015', 6800000.00, 'Dòng vang thứ hai trứ danh của Château Margaux với cấu trúc tannin mượt mà như nhung và hậu vị sâu lắng kéo dài.', 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?auto=format&fit=crop&w=800&q=80', 5, 1, 1),
(5, 'Vang trắng Chablis Premier Cru Domaine Laroche 2020', 'vang-trang-chablis-premier-cru-2020', 3400000.00, 'Chai 750ml giống nho Chardonnay từ vùng Bourgogne trứ danh, hương khoáng đạt đậm đà và acid tươi mát, hoàn hảo khi dùng với hải sản và hàu tươi.', 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?auto=format&fit=crop&w=800&q=80', 5, 1, 1),
(5, 'Champagne Dom Pérignon Vintage Brut', 'champagne-dom-perignon-vintage-brut', 8900000.00, 'Tuyệt tác Champagne cổ điển nước Pháp với bọt sủi tăm mịn màng, hương bánh mì nướng brioche và trái cây khô sang trọng.', 'https://images.unsplash.com/photo-1569919659476-f0852f6834b7?auto=format&fit=crop&w=800&q=80', 5, 1, 1),
(5, 'Nước khoáng có ga San Pellegrino 750ml', 'nuoc-khoang-san-pellegrino-750ml', 160000.00, 'Nước khoáng có ga đóng chai thủy tinh tự nhiên nhập khẩu từ dãy Alps nước Ý, mang vị khoáng dịu nhẹ kích thích khẩu vị.', 'https://images.unsplash.com/photo-1559839914-17aae19cec71?auto=format&fit=crop&w=800&q=80', 2, 1, 1),
(5, 'Nước ép quả mọng hữu cơ Épicure Detox', 'nuoc-ep-qua-mong-huu-co-epicure-detox', 180000.00, 'Nước ép tươi pha chế từ mâm xôi đen, việt quất và dâu tây hữu cơ, kết hợp mật ong hoa rừng tinh khiết.', 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80', 5, 1, 1)
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `price` = VALUES(`price`),
    `description` = VALUES(`description`),
    `image` = VALUES(`image`),
    `preparation_time` = VALUES(`preparation_time`),
    `is_available` = VALUES(`is_available`),
    `is_visible` = VALUES(`is_visible`);

-- 6. TÀI KHOẢN KHÁCH HÀNG & NHÂN VIÊN MẪU
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `phone`, `status`) VALUES
(2, 1, 'Quản trị viên Hệ thống', 'admin@lepicure.com', '$2y$10$wT0lqfAovZJmPcrz3U8MGOo6eA9xT9H9L2Yf3w1g4q9x7Y9H9L2Yf', '0901000001', 'active'),
(3, 2, 'Nguyễn Hoàng Nam', 'staff@lepicure.com', '$2y$10$wT0lqfAovZJmPcrz3U8MGOo6eA9xT9H9L2Yf3w1g4q9x7Y9H9L2Yf', '0901000002', 'active'),
(4, 3, 'Trần Bếp Trưởng', 'kitchen@lepicure.com', '$2y$10$wT0lqfAovZJmPcrz3U8MGOo6eA9xT9H9L2Yf3w1g4q9x7Y9H9L2Yf', '0901000003', 'active'),
(5, 4, 'Jean-Pierre Laurent', 'customer@lepicure.com', '$2y$10$wT0lqfAovZJmPcrz3U8MGOo6eA9xT9H9L2Yf3w1g4q9x7Y9H9L2Yf', '0901000004', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 7. ĐẶT BÀN & ĐƠN GỌI MÓN MẪU ĐỂ TEST "ĐƠN CỦA TÔI" & "THÔNG BÁO"
INSERT INTO `bookings` (`id`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `booking_date`, `booking_time`, `number_of_guests`, `area_id`, `table_id`, `notes`, `status`) VALUES
(1, 5, 'Jean-Pierre Laurent', '0901000004', 'customer@lepicure.com', CURRENT_DATE, '19:00:00', 4, 1, 4, 'Bàn tiệc gia đình, kỷ niệm ngày cưới', 'seated')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `orders` (`id`, `booking_id`, `table_id`, `created_by`, `customer_name`, `status`, `payment_status`, `total_amount`, `discount`, `service_charge`, `tax`, `grand_total`, `notes`) VALUES
(1, 1, 4, 3, 'Jean-Pierre Laurent', 'preparing', 'unpaid', 6550000.00, 0, 327500.00, 524000.00, 7401500.00, 'Bò nướng chín vừa, vang phục vụ trước'),
(2, NULL, 9, 3, 'Jean-Pierre Laurent', 'completed', 'paid', 4860000.00, 0, 243000.00, 388800.00, 5491800.00, 'Tiệc chiêu đãi đối tác VIP')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `order_items` (`order_id`, `dish_id`, `dish_name`, `quantity`, `unit_price`, `subtotal`, `note`, `status`) VALUES
(1, 1, 'Súp nấm Truffle đen Alba', 2, 450000.00, 900000.00, 'Không tiêu đen', 'ready'),
(1, 4, 'Gan ngỗng áp chảo Rougié', 1, 650000.00, 650000.00, 'Sốt thêm quả mọng', 'ready'),
(1, 6, 'Bò Wagyu Kagoshima A5 Áp Chảo', 1, 2850000.00, 2850000.00, 'Chín vừa medium rare', 'preparing'),
(1, 7, 'Thăn cừu non nướng thảo mộc Provence', 1, 1250000.00, 1250000.00, 'Thảo mộc nướng kỹ', 'preparing'),
(2, 2, 'Hàu tươi Gillardeau N°2', 2, 680000.00, 1360000.00, 'Chanh vàng thêm', 'served'),
(2, 12, 'Cá tuyết Nam Cực áp chảo sốt bơ chanh', 2, 980000.00, 1960000.00, '', 'served'),
(2, 15, 'Bánh Soufflé Cam Grand Marnier', 2, 320000.00, 640000.00, '', 'served')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `data`, `is_read`) VALUES
(5, 'order_created', 'Đơn gọi món mới đã được tạo #1', 'Quý khách đã được xếp bàn tại T-04 (Sảnh Vòm Grand Hall). Đơn gọi món đã được gửi tới nhà bếp.', '{"order_id": 1, "table": "T-04"}', 1),
(5, 'order_preparing', 'Bếp đang chế biến món ăn — Đơn #1', 'Bếp trưởng đang thực hiện chế biến món Súp nấm Truffle đen và Bò Wagyu A5 cho bàn của bạn.', '{"order_id": 1, "status": "preparing"}', 0),
(5, 'order_completed', 'Hóa đơn thanh toán hoàn tất — Đơn #2', 'Cảm ơn quý khách đã dùng bữa tại L\'Épicure. Hóa đơn #INV-VIP01 đã được thanh toán thành công qua thẻ tín dụng.', '{"order_id": 2}', 1);

