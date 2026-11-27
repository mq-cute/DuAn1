-- FOODIES Haute Cuisine — Database Schema
-- Fully consolidated schema including all core and admin features
-- Database: MySQL 8.0+

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `invoice_refunds`;
DROP TABLE IF EXISTS `invoice_edit_histories`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `kitchen_ticket_items`;
DROP TABLE IF EXISTS `kitchen_tickets`;
DROP TABLE IF EXISTS `order_status_histories`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `booking_status_histories`;
DROP TABLE IF EXISTS `booking_details`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `restaurant_tables`;
DROP TABLE IF EXISTS `dining_areas`;
DROP TABLE IF EXISTS `dishes`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `combo_setups`;
DROP TABLE IF EXISTS `vouchers`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `restaurant_operating_hours`;
DROP TABLE IF EXISTS `restaurant_settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Roles
CREATE TABLE `roles` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` ENUM ('admin', 'staff', 'kitchen', 'customer') UNIQUE NOT NULL,
  `description` varchar(255),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users
CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `role_id` bigint NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20),
  `avatar` varchar(255),
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active | inactive',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories
CREATE TABLE `categories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) UNIQUE NOT NULL,
  `description` text,
  `image` varchar(255),
  `sort_order` int NOT NULL DEFAULT 0,
  `is_visible` boolean NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Dishes
CREATE TABLE `dishes` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `category_id` bigint NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) UNIQUE NOT NULL,
  `description` text,
  `image` varchar(255),
  `price` decimal(12,2) NOT NULL,
  `preparation_time` int COMMENT 'phut',
  `is_available` boolean NOT NULL DEFAULT true COMMENT 'con mon / het mon',
  `is_visible` boolean NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Dining Areas
CREATE TABLE `dining_areas` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Restaurant Tables (with VIP configuration)
CREATE TABLE `restaurant_tables` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `area_id` bigint NOT NULL,
  `code` varchar(20) UNIQUE NOT NULL COMMENT 'ma ban',
  `name` varchar(100) NOT NULL,
  `capacity` int NOT NULL,
  `is_vip` tinyint(1) NOT NULL DEFAULT 0,
  `table_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vip_privileges` text NULL,
  `description` text NULL,
  `status` ENUM ('empty', 'reserved', 'in_use', 'serving', 'awaiting_payment', 'cleaning', 'locked') NOT NULL DEFAULT 'empty',
  `position_x` int COMMENT 'vi tri tren so do',
  `position_y` int,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`area_id`) REFERENCES `dining_areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Combo Setups (Gói sự kiện, tiệc setup độc bản)
CREATE TABLE `combo_setups` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) UNIQUE NOT NULL,
  `subtitle` varchar(255) NULL,
  `category` varchar(50) DEFAULT 'combo',
  `price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) NULL,
  `description` text NULL,
  `includes` text NULL,
  `suitable_for` varchar(100) NULL,
  `image` varchar(255) NULL,
  `is_featured` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Vouchers (Mã ưu đãi giảm giá)
CREATE TABLE `vouchers` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(50) UNIQUE NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NULL,
  `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(12,2) NOT NULL,
  `min_order_amount` decimal(12,2) DEFAULT 0,
  `max_discount_amount` decimal(12,2) NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bookings (with Combo, Deposit & Estimates)
CREATE TABLE `bookings` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint COMMENT 'khach hang (nullable neu khach vang lai)',
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(150),
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `number_of_guests` int NOT NULL,
  `area_id` bigint,
  `table_id` bigint,
  `combo_id` bigint NULL,
  `deposit_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `voucher_code` varchar(50) NULL,
  `voucher_discount_type` ENUM('percentage', 'fixed') NULL,
  `voucher_discount_value` decimal(12,2) NULL,
  `voucher_min_order_amount` decimal(12,2) NULL,
  `voucher_max_discount_amount` decimal(12,2) NULL,
  `deposit_received_at` datetime DEFAULT NULL,
  `deposit_received_by` bigint DEFAULT NULL,
  `estimated_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text COMMENT 'ghi chu chung',
  `status` ENUM ('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`area_id`) REFERENCES `dining_areas` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`combo_id`) REFERENCES `combo_setups` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`deposit_received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Booking Details (Pre-orders)
CREATE TABLE `booking_details` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint NOT NULL,
  `dish_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(12,2) NOT NULL COMMENT 'snapshot gia tai thoi diem dat',
  `note` text COMMENT 'ghi chu rieng cho mon',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Booking Status Histories
CREATE TABLE `booking_status_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint NOT NULL,
  `old_status` ENUM ('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'),
  `new_status` ENUM ('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') NOT NULL,
  `changed_by` bigint,
  `note` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Orders (with Fees & Voucher Code)
CREATE TABLE `orders` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint,
  `table_id` bigint NOT NULL,
  `created_by` bigint COMMENT 'nhan vien tao don',
  `customer_name` varchar(150),
  `status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM ('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `table_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `setup_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0,
  `voucher_code` varchar(50) NULL,
  `service_charge` decimal(12,2) NOT NULL DEFAULT 0,
  `tax` decimal(12,2) NOT NULL DEFAULT 0,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0,
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Order Items
CREATE TABLE `order_items` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `dish_id` bigint NOT NULL,
  `dish_name` varchar(200) NOT NULL COMMENT 'snapshot ten mon',
  `quantity` int NOT NULL,
  `unit_price` decimal(12,2) NOT NULL COMMENT 'snapshot gia',
  `subtotal` decimal(12,2) NOT NULL,
  `note` text COMMENT 'ghi chu rieng cho mon',
  `status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Order Status Histories
CREATE TABLE `order_status_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `old_status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'),
  `new_status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') NOT NULL,
  `changed_by` bigint,
  `note` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Kitchen Tickets
CREATE TABLE `kitchen_tickets` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `ticket_number` varchar(30) UNIQUE NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending | received | in_progress | done',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Kitchen Ticket Items
CREATE TABLE `kitchen_ticket_items` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ticket_id` bigint NOT NULL,
  `order_item_id` bigint NOT NULL,
  `status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'cancelled') NOT NULL DEFAULT 'pending',
  `started_at` datetime,
  `completed_at` datetime,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `kitchen_tickets` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Invoices (with Refund Statuses & PDF Path)
CREATE TABLE `invoices` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint UNIQUE NOT NULL,
  `invoice_number` varchar(30) UNIQUE NOT NULL,
  `customer_name` varchar(150),
  `issued_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount` decimal(12,2) NOT NULL DEFAULT 0,
  `service_charge` decimal(12,2) NOT NULL DEFAULT 0,
  `tax` decimal(12,2) NOT NULL DEFAULT 0,
  `total` decimal(12,2) NOT NULL DEFAULT 0,
  `payment_method` ENUM ('cash', 'card', 'transfer', 'ewallet'),
  `status` ENUM ('draft', 'issued', 'paid', 'cancelled', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'draft',
  `pdf_path` varchar(255),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Payments
CREATE TABLE `payments` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `invoice_id` bigint NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` ENUM ('cash', 'card', 'transfer', 'ewallet') NOT NULL,
  `paid_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `received_by` bigint,
  `transaction_id` varchar(100) NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Invoice Refunds (Quản lý hoàn tiền hóa đơn)
CREATE TABLE `invoice_refunds` (
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

-- 20. Invoice Edit Histories (Nhật ký sửa sai hóa đơn)
CREATE TABLE `invoice_edit_histories` (
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

-- 21. Activity Logs (Nhật ký thao tác hệ thống)
CREATE TABLE `activity_logs` (
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

-- 22. Notifications
CREATE TABLE `notifications` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint COMMENT 'nguoi nhan (null = broadcast)',
  `type` varchar(50) NOT NULL COMMENT 'booking_new, order_new, dish_ready, ...',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` json COMMENT 'payload chi tiet',
  `is_read` boolean NOT NULL DEFAULT false,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Restaurant Settings (Cài đặt hệ thống nhà hàng)
CREATE TABLE `restaurant_settings` (
  `key` varchar(100) PRIMARY KEY,
  `value` text NULL,
  `group_name` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Restaurant Operating Hours (Giờ mở cửa theo ngày)
CREATE TABLE `restaurant_operating_hours` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') UNIQUE NOT NULL,
  `day_name` varchar(50) NOT NULL,
  `open_time` time NOT NULL DEFAULT '10:00:00',
  `close_time` time NOT NULL DEFAULT '23:00:00',
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX `idx_users_role` ON `users` (`role_id`);
CREATE INDEX `idx_dishes_category` ON `dishes` (`category_id`);
CREATE INDEX `idx_dishes_available` ON `dishes` (`is_available`);
CREATE INDEX `idx_tables_area` ON `restaurant_tables` (`area_id`);
CREATE INDEX `idx_tables_status` ON `restaurant_tables` (`status`);
CREATE INDEX `idx_bookings_date` ON `bookings` (`booking_date`);
CREATE INDEX `idx_bookings_status` ON `bookings` (`status`);
CREATE INDEX `idx_orders_table` ON `orders` (`table_id`);
CREATE INDEX `idx_orders_status` ON `orders` (`status`);
CREATE INDEX `idx_orders_payment` ON `orders` (`payment_status`);
CREATE INDEX `idx_invoices_status` ON `invoices` (`status`);
CREATE INDEX `idx_notifications_user` ON `notifications` (`user_id`);
CREATE INDEX `idx_notifications_read` ON `notifications` (`is_read`);

-- Initial Essential System Settings
INSERT INTO `restaurant_settings` (`key`, `value`, `group_name`) VALUES
('restaurant_name', 'FOODIES — L''Épicure Gastronomie', 'general'),
('restaurant_tagline', 'Haute Cuisine Française & Grand Cru Wine Cellar', 'general'),
('restaurant_address', 'Số 18 Đại Lộ Lê Duẩn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'general'),
('restaurant_phone', '(+84) 28 3822 8899', 'general'),
('restaurant_email', 'contact@lepicure.com', 'general'),
('restaurant_tax_number', '0314892341', 'general'),
('restaurant_description', 'L''Épicure Gastronomie tự hào mang đến hành trình mỹ vị Pháp đỉnh cao giữa lòng Sài Gòn.', 'general'),
('tax_rate', '8', 'pricing'),
('vat_enabled', '1', 'pricing'),
('service_charge_rate', '5', 'pricing'),
('service_charge_enabled', '1', 'pricing'),
('auto_release_late_minutes', '30', 'booking'),
('booking_policy', 'Quý khách vui lòng đặt bàn trước ít nhất 2 giờ. Bàn đặt được bảo lưu tối đa 30 phút.', 'policies'),
('cancellation_policy', 'Khách hàng có thể hủy hoặc đổi giờ đặt bàn miễn phí trước 2 giờ.', 'policies')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Initial Operating Hours
INSERT INTO `restaurant_operating_hours` (`day_of_week`, `day_name`, `open_time`, `close_time`, `is_closed`) VALUES
('monday', 'Thứ Hai', '10:00:00', '23:00:00', 0),
('tuesday', 'Thứ Ba', '10:00:00', '23:00:00', 0),
('wednesday', 'Thứ Tư', '10:00:00', '23:00:00', 0),
('thursday', 'Thứ Năm', '10:00:00', '23:00:00', 0),
('friday', 'Thứ Sáu', '10:00:00', '23:30:00', 0),
('saturday', 'Thứ Bảy', '10:00:00', '23:30:00', 0),
('sunday', 'Chủ Nhật', '10:00:00', '23:00:00', 0)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;
