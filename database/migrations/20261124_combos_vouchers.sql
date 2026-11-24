-- Migration: Tạo bảng combo_setups và vouchers
CREATE TABLE IF NOT EXISTS `combo_setups` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `subtitle` VARCHAR(255) NULL,
    `category` VARCHAR(50) DEFAULT 'combo', -- 'combo' hoặc 'setup'
    `price` DECIMAL(12,2) NOT NULL,
    `original_price` DECIMAL(12,2) NULL,
    `description` TEXT NULL,
    `includes` TEXT NULL, -- Danh sách món ăn / dịch vụ đi kèm
    `suitable_for` VARCHAR(100) NULL, -- Phù hợp: 2 người, gia đình, sự kiện...
    `image` VARCHAR(255) NULL,
    `is_featured` TINYINT(1) DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vouchers` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    `discount_value` DECIMAL(12,2) NOT NULL,
    `min_order_amount` DECIMAL(12,2) DEFAULT 0,
    `max_discount_amount` DECIMAL(12,2) NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `usage_limit` INT DEFAULT NULL,
    `used_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu combo setups
INSERT INTO `combo_setups` (`name`, `slug`, `subtitle`, `category`, `price`, `original_price`, `description`, `includes`, `suitable_for`, `image`, `is_featured`, `is_active`) VALUES
('Set Menu Dạ Tiệc Tình Yêu (Romance Menu)', 'set-menu-da-tiec-tinh-yeu', 'Setup hoa hồng tươi, nến lung linh & menu 5 món hoàng gia', 'setup', 2890000.00, 3400000.00, 'Không gian bàn riêng tư trang hoàng nến thơm & cánh hoa hồng, thực đơn 5 món Pháp thượng hạng dành riêng cho 2 người.', '2 Ly vang nổ Champagne, Gan ngỗng Rougié áp chảo, Súp kem nấm Truffle đen, Bò Wagyu A5 sốt tiêu đen, Bánh Lava Chocolate & Setup nến hoa', 'Dành cho 2 khách', 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80', 1, 1),
('Gói Setup Sinh Nhật Sang Trọng & Kỷ Niệm', 'goi-setup-sinh-nhat-sang-trong', 'Bóng bay nghệ thuật, bảng tên cá nhân hóa & bánh kem Chef làm riêng', 'setup', 1500000.00, 1800000.00, 'Gói trang trí sự kiện đặc quyền với hoa tươi cao cấp, backdrop chụp ảnh và nến nghệ thuật.', 'Backdrop thiết kế riêng, Hoa tươi bàn tiệc, Bánh kem tươi cao cấp từ Pastry Chef, Thiệp chúc mừng dát vàng', 'Nhóm 4 - 12 khách', 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=800&q=80', 1, 1),
('Combo Tiệc Rượu Doanh Nhân & Đối Tác', 'combo-tiec-ruou-doanh-nhan', 'Hầm rượu riêng tư, Sommelier phục vụ rượu & thực đơn Tapas cao cấp', 'combo', 4500000.00, 5200000.00, 'Không gian hầm rượu biệt lập cho cuộc gặp gỡ đối tác ngoại giao, thưởng thức bộ sưu tập Grand Cru cùng hải sản biển sâu.', '1 Chai vang đỏ Grand Cru Bordeaux, Đĩa hàu sữa Gillardeau N°2, Đùi heo muối Iberico Bellota, Thăn cừu New Zealand nướng thảo mộc', 'Nhóm 4 - 6 khách', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80', 1, 1),
('Combo Gia Đình Sum Vầy (L\'Épicure Signature)', 'combo-gia-dinh-sum-vay', 'Bàn tròn lớn ấm cúng & thực đơn phong phú tròn vị các thế hệ', 'combo', 3600000.00, 4200000.00, 'Sự hòa quyện giữa tinh hoa ẩm thực Pháp và khẩu vị Á Đông, tròn vẹn từng phút giây quây quần bên người thân.', 'Salad cá ngừ Niçoise, Bò Wagyu Kagoshima nướng than, Cá hồi Na Uy sốt bơ chanh, Mỳ Ý tôm hùm bơ tỏi, Tráng miệng Crème Brûlée', 'Gia đình 4 - 6 khách', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80', 1, 1);

-- Thêm dữ liệu mẫu vouchers
INSERT INTO `vouchers` (`code`, `title`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `start_date`, `end_date`, `usage_limit`, `used_count`, `is_active`) VALUES
('FOODIESFIRST15', 'Tặng Voucher Ưu Đãi 15% Cho Lần Đặt Bàn Đầu Tiên', 'Nhập mã đặc quyền khi đặt chỗ trực tuyến qua website để nhận ưu đãi khấu trừ trực tiếp trên tổng hóa đơn ẩm thực và miễn phí khai vị Champagne cao cấp.', 'percentage', 15.00, 2000000.00, 500000.00, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 500, 28, 1),
('EPICUREVIP10', 'Giảm 10% Cho Hội Viên Đặt Bàn VIP', 'Ưu đãi dành cho thành viên đặt phòng VIP Hoàng Gia hoặc tiệc doanh nhân cuối tuần.', 'percentage', 10.00, 3000000.00, 1000000.00, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 200, 15, 1),
('CHAMPAGNEFREE', 'Miễn Phí 02 Ly Champagne Pháp', 'Tặng kèm 02 ly Champagne hảo hạng khi đặt bàn kỷ niệm hoặc sinh nhật vào buổi tối.', 'fixed', 350000.00, 1500000.00, 350000.00, '2026-01-01 00:00:00', '2027-12-31 23:59:59', 1000, 64, 1);
