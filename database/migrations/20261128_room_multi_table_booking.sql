ALTER TABLE `dining_areas`
  ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `sort_order`;

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `room_type_id` bigint NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_rooms_code` (`code`),
  INDEX `idx_rooms_type_active` (`room_type_id`, `is_active`),
  CONSTRAINT `fk_rooms_type` FOREIGN KEY (`room_type_id`) REFERENCES `dining_areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `rooms` (`room_type_id`, `code`, `name`, `description`, `is_active`, `sort_order`)
SELECT
  a.id,
  CONCAT('ROOM-AUTO-', a.id),
  CONCAT('Phòng ', a.name),
  'Phòng tự động tạo khi nâng cấp dữ liệu bàn cũ.',
  1,
  a.sort_order
FROM `dining_areas` a;

ALTER TABLE `restaurant_tables`
  ADD COLUMN `room_id` bigint NULL AFTER `area_id`,
  ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `status`,
  ADD INDEX `idx_tables_room_active` (`room_id`, `is_active`);

UPDATE `restaurant_tables` t
INNER JOIN `rooms` r ON r.room_type_id = t.area_id AND r.code = CONCAT('ROOM-AUTO-', t.area_id)
SET t.room_id = r.id;

ALTER TABLE `restaurant_tables`
  ADD CONSTRAINT `fk_tables_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT;

CREATE TABLE IF NOT EXISTS `booking_tables` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint NOT NULL,
  `table_id` bigint NOT NULL,
  `table_fee_snapshot` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_booking_table` (`booking_id`, `table_id`),
  INDEX `idx_booking_tables_table` (`table_id`),
  CONSTRAINT `fk_booking_tables_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_tables_table` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `booking_tables` (`booking_id`, `table_id`, `table_fee_snapshot`)
SELECT b.id, b.table_id,
       CASE WHEN t.table_fee > 0 THEN t.table_fee ELSE (CASE WHEN t.is_vip = 1 THEN 200000 ELSE 100000 END) END
FROM `bookings` b
INNER JOIN `restaurant_tables` t ON t.id = b.table_id
WHERE b.table_id IS NOT NULL;

CREATE TABLE IF NOT EXISTS `booking_table_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint NOT NULL,
  `room_id` bigint NULL,
  `old_table_ids` json NULL,
  `new_table_ids` json NULL,
  `old_total_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `new_total_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reason` varchar(2000) NOT NULL,
  `note` text NULL,
  `changed_by` bigint NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_booking_table_histories_booking` (`booking_id`, `created_at`),
  CONSTRAINT `fk_booking_table_histories_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_table_histories_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_booking_table_histories_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `bookings`
  ADD COLUMN `room_id` bigint NULL AFTER `area_id`,
  ADD COLUMN `actual_arrival_at` datetime NULL AFTER `status`,
  ADD COLUMN `late_hold_expires_at` datetime NULL AFTER `actual_arrival_at`,
  ADD COLUMN `late_contacted_at` datetime NULL AFTER `late_hold_expires_at`,
  ADD COLUMN `late_contact_note` text NULL AFTER `late_contacted_at`,
  ADD INDEX `idx_bookings_room_status` (`room_id`, `status`),
  ADD CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

UPDATE `bookings` b
INNER JOIN `restaurant_tables` t ON t.id = b.table_id
SET b.room_id = t.room_id
WHERE b.room_id IS NULL;

ALTER TABLE `bookings`
  MODIFY COLUMN `status` ENUM (
    'pending',
    'confirmed',
    'seated',
    'waiting_pending',
    'completed',
    'cancelled',
    'no_show'
  ) NOT NULL DEFAULT 'pending';

