-- Migration for 3 Admin Features: Invoices, Users, Restaurant Settings
-- Created: 2026-11-19

-- 1. Cập nhật ENUM status của bảng invoices để hỗ trợ hoàn tiền
ALTER TABLE `invoices` 
MODIFY COLUMN `status` ENUM('draft', 'issued', 'paid', 'cancelled', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'draft';

-- 2. Bảng quản lý hoàn tiền (invoice_refunds)
CREATE TABLE IF NOT EXISTS `invoice_refunds` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `invoice_id` bigint NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` text NOT NULL,
  `refund_method` ENUM('cash', 'card', 'transfer', 'ewallet') NOT NULL DEFAULT 'cash',
  `status` ENUM('pending', 'completed', 'rejected') NOT NULL DEFAULT 'completed',
  `processed_by` bigint NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng lịch sử sửa sai hóa đơn (invoice_edit_histories)
CREATE TABLE IF NOT EXISTS `invoice_edit_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `invoice_id` bigint NOT NULL,
  `edited_by` bigint NOT NULL,
  `reason` text NOT NULL,
  `old_data` json NOT NULL,
  `new_data` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng nhật ký hoạt động người dùng và quản trị (activity_logs)
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` bigint NULL,
  `details` json NULL,
  `ip_address` varchar(45) NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_activity_logs_action` (`action`),
  INDEX `idx_activity_logs_target` (`target_type`, `target_id`),
  INDEX `idx_activity_logs_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng cấu hình thông tin nhà hàng (restaurant_settings)
CREATE TABLE IF NOT EXISTS `restaurant_settings` (
  `key` varchar(100) PRIMARY KEY,
  `value` text NULL,
  `group_name` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng cấu hình giờ hoạt động theo từng ngày (restaurant_operating_hours)
CREATE TABLE IF NOT EXISTS `restaurant_operating_hours` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') UNIQUE NOT NULL,
  `day_name` varchar(50) NOT NULL,
  `open_time` time NOT NULL DEFAULT '10:00:00',
  `close_time` time NOT NULL DEFAULT '23:00:00',
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dữ liệu mẫu cấu hình nhà hàng
INSERT INTO `restaurant_settings` (`key`, `value`, `group_name`) VALUES
('restaurant_name', 'L''Épicure Gastronomie', 'general'),
('restaurant_tagline', 'Haute Cuisine Française & Grand Cru Wine Cellar', 'general'),
('restaurant_address', 'Số 18 Đại Lộ Lê Duẩn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'general'),
('restaurant_phone', '(+84) 28 3822 8899', 'general'),
('restaurant_email', 'contact@lepicure.com', 'general'),
('restaurant_tax_number', '0314892341', 'general'),
('restaurant_description', 'L''Épicure Gastronomie tự hào mang đến hành trình mỹ vị Pháp đỉnh cao giữa lòng Sài Gòn, giao thoa hoàn hảo giữa kỹ nghệ ẩm thực cổ điển và phong vị đương đại.', 'general'),
('restaurant_logo', '/assets/images/logo.png', 'general'),
('restaurant_cover_image', '/assets/images/banner.jpg', 'general'),
('tax_rate', '8', 'pricing'),
('vat_enabled', '1', 'pricing'),
('service_charge_rate', '5', 'pricing'),
('service_charge_enabled', '1', 'pricing'),
('booking_policy', 'Quý khách vui lòng đặt bàn trước ít nhất 2 giờ. Bàn đặt được bảo lưu tối đa 15 phút so với giờ hẹn trước khi tự động hủy nếu không có thông báo trước.', 'policies'),
('cancellation_policy', 'Khách hàng có thể hủy hoặc đổi giờ đặt bàn miễn phí trước 2 giờ so với thời điểm nhận bàn. Với các tiệc đặt cọc trước, việc hoàn cọc tuân theo chính sách hoàn tiền.', 'policies'),
('refund_policy', 'Nhà hàng hoàn tiền 100% trong trường hợp lỗi hệ thống thanh toán hoặc dịch vụ bị hủy trước giờ quy định. Các trường hợp hoàn tiền tại bàn được xử lý qua phiếu hoàn tiền nội bộ có chữ ký quản trị viên.', 'policies'),
('general_terms', 'Nhà hàng phục vụ trang phục lịch sự (Smart Casual). Trân trọng cảm ơn quý khách.', 'policies')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Dữ liệu giờ mở cửa 7 ngày trong tuần
INSERT INTO `restaurant_operating_hours` (`day_of_week`, `day_name`, `open_time`, `close_time`, `is_closed`) VALUES
('monday', 'Thứ Hai', '10:00:00', '23:00:00', 0),
('tuesday', 'Thứ Ba', '10:00:00', '23:00:00', 0),
('wednesday', 'Thứ Tư', '10:00:00', '23:00:00', 0),
('thursday', 'Thứ Năm', '10:00:00', '23:00:00', 0),
('friday', 'Thứ Sáu', '10:00:00', '23:30:00', 0),
('saturday', 'Thứ Bảy', '10:00:00', '23:30:00', 0),
('sunday', 'Chủ Nhật', '10:00:00', '23:00:00', 0)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;
