-- SQL dump generated using DBML (dbml.dbdiagram.io)
-- Database: MySQL
-- Generated at: 2026-11-07T10:18:08.852Z

CREATE TABLE `roles` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` ENUM ('admin', 'staff', 'kitchen', 'customer') UNIQUE NOT NULL,
  `description` varchar(255),
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `role_id` bigint NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20),
  `avatar` varchar(255),
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active | inactive',
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `categories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) UNIQUE NOT NULL,
  `description` text,
  `image` varchar(255),
  `sort_order` int NOT NULL DEFAULT 0,
  `is_visible` boolean NOT NULL DEFAULT true,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

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
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `dining_areas` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `restaurant_tables` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `area_id` bigint NOT NULL,
  `code` varchar(20) UNIQUE NOT NULL COMMENT 'ma ban',
  `name` varchar(100) NOT NULL,
  `capacity` int NOT NULL,
  `status` ENUM ('empty', 'reserved', 'in_use', 'serving', 'awaiting_payment', 'cleaning', 'locked') NOT NULL DEFAULT 'empty',
  `position_x` int COMMENT 'vi tri tren so do',
  `position_y` int,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

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
  `notes` text COMMENT 'ghi chu chung',
  `status` ENUM ('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `booking_details` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint NOT NULL,
  `dish_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(12,2) NOT NULL COMMENT 'snapshot gia tai thoi diem dat',
  `note` text COMMENT 'ghi chu rieng cho mon',
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `booking_status_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint NOT NULL,
  `old_status` ENUM ('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'),
  `new_status` ENUM ('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') NOT NULL,
  `changed_by` bigint,
  `note` text,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `orders` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `booking_id` bigint,
  `table_id` bigint NOT NULL,
  `created_by` bigint COMMENT 'nhan vien tao don',
  `customer_name` varchar(150),
  `status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM ('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `discount` decimal(12,2) NOT NULL DEFAULT 0,
  `service_charge` decimal(12,2) NOT NULL DEFAULT 0,
  `tax` decimal(12,2) NOT NULL DEFAULT 0,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0,
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

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
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `order_status_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `old_status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'),
  `new_status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled') NOT NULL,
  `changed_by` bigint,
  `note` text,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `kitchen_tickets` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `ticket_number` varchar(30) UNIQUE NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending | received | in_progress | done',
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `kitchen_ticket_items` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ticket_id` bigint NOT NULL,
  `order_item_id` bigint NOT NULL,
  `status` ENUM ('pending', 'confirmed', 'preparing', 'ready', 'served', 'cancelled') NOT NULL DEFAULT 'pending',
  `started_at` datetime,
  `completed_at` datetime,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `invoices` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint UNIQUE NOT NULL,
  `invoice_number` varchar(30) UNIQUE NOT NULL,
  `customer_name` varchar(150),
  `issued_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount` decimal(12,2) NOT NULL DEFAULT 0,
  `service_charge` decimal(12,2) NOT NULL DEFAULT 0,
  `tax` decimal(12,2) NOT NULL DEFAULT 0,
  `total` decimal(12,2) NOT NULL DEFAULT 0,
  `payment_method` ENUM ('cash', 'card', 'transfer', 'ewallet'),
  `status` ENUM ('draft', 'issued', 'paid', 'cancelled') NOT NULL DEFAULT 'draft',
  `pdf_path` varchar(255),
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `payments` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `invoice_id` bigint NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` ENUM ('cash', 'card', 'transfer', 'ewallet') NOT NULL,
  `paid_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `received_by` bigint,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `notifications` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint COMMENT 'nguoi nhan (null = broadcast)',
  `type` varchar(50) NOT NULL COMMENT 'booking_new, order_new, dish_ready, ...',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` json COMMENT 'payload chi tiet',
  `is_read` boolean NOT NULL DEFAULT false,
  `created_at` datetime NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE INDEX `users_index_0` ON `users` (`role_id`);

CREATE UNIQUE INDEX `users_index_1` ON `users` (`email`);

CREATE INDEX `users_index_2` ON `users` (`status`);

CREATE UNIQUE INDEX `categories_index_3` ON `categories` (`slug`);

CREATE INDEX `categories_index_4` ON `categories` (`sort_order`);

CREATE INDEX `categories_index_5` ON `categories` (`is_visible`);

CREATE INDEX `dishes_index_6` ON `dishes` (`category_id`);

CREATE UNIQUE INDEX `dishes_index_7` ON `dishes` (`slug`);

CREATE INDEX `dishes_index_8` ON `dishes` (`is_available`);

CREATE INDEX `dishes_index_9` ON `dishes` (`is_visible`);

CREATE INDEX `dishes_index_10` ON `dishes` (`price`);

CREATE INDEX `dining_areas_index_11` ON `dining_areas` (`sort_order`);

CREATE INDEX `restaurant_tables_index_12` ON `restaurant_tables` (`area_id`);

CREATE UNIQUE INDEX `restaurant_tables_index_13` ON `restaurant_tables` (`code`);

CREATE INDEX `restaurant_tables_index_14` ON `restaurant_tables` (`status`);

CREATE INDEX `restaurant_tables_index_15` ON `restaurant_tables` (`capacity`);

CREATE INDEX `bookings_index_16` ON `bookings` (`user_id`);

CREATE INDEX `bookings_index_17` ON `bookings` (`table_id`);

CREATE INDEX `bookings_index_18` ON `bookings` (`area_id`);

CREATE INDEX `bookings_index_19` ON `bookings` (`booking_date`);

CREATE INDEX `bookings_index_20` ON `bookings` (`booking_time`);

CREATE INDEX `bookings_index_21` ON `bookings` (`status`);

CREATE INDEX `bookings_index_22` ON `bookings` (`customer_phone`);

CREATE INDEX `booking_details_index_23` ON `booking_details` (`booking_id`);

CREATE INDEX `booking_details_index_24` ON `booking_details` (`dish_id`);

CREATE INDEX `booking_status_histories_index_25` ON `booking_status_histories` (`booking_id`);

CREATE INDEX `booking_status_histories_index_26` ON `booking_status_histories` (`changed_by`);

CREATE INDEX `booking_status_histories_index_27` ON `booking_status_histories` (`created_at`);

CREATE INDEX `orders_index_28` ON `orders` (`booking_id`);

CREATE INDEX `orders_index_29` ON `orders` (`table_id`);

CREATE INDEX `orders_index_30` ON `orders` (`created_by`);

CREATE INDEX `orders_index_31` ON `orders` (`status`);

CREATE INDEX `orders_index_32` ON `orders` (`payment_status`);

CREATE INDEX `orders_index_33` ON `orders` (`created_at`);

CREATE INDEX `order_items_index_34` ON `order_items` (`order_id`);

CREATE INDEX `order_items_index_35` ON `order_items` (`dish_id`);

CREATE INDEX `order_items_index_36` ON `order_items` (`status`);

CREATE INDEX `order_status_histories_index_37` ON `order_status_histories` (`order_id`);

CREATE INDEX `order_status_histories_index_38` ON `order_status_histories` (`changed_by`);

CREATE INDEX `order_status_histories_index_39` ON `order_status_histories` (`created_at`);

CREATE INDEX `kitchen_tickets_index_40` ON `kitchen_tickets` (`order_id`);

CREATE UNIQUE INDEX `kitchen_tickets_index_41` ON `kitchen_tickets` (`ticket_number`);

CREATE INDEX `kitchen_tickets_index_42` ON `kitchen_tickets` (`status`);

CREATE INDEX `kitchen_ticket_items_index_43` ON `kitchen_ticket_items` (`ticket_id`);

CREATE INDEX `kitchen_ticket_items_index_44` ON `kitchen_ticket_items` (`order_item_id`);

CREATE INDEX `kitchen_ticket_items_index_45` ON `kitchen_ticket_items` (`status`);

CREATE UNIQUE INDEX `invoices_index_46` ON `invoices` (`order_id`);

CREATE UNIQUE INDEX `invoices_index_47` ON `invoices` (`invoice_number`);

CREATE INDEX `invoices_index_48` ON `invoices` (`status`);

CREATE INDEX `invoices_index_49` ON `invoices` (`issued_at`);

CREATE INDEX `payments_index_50` ON `payments` (`invoice_id`);

CREATE INDEX `payments_index_51` ON `payments` (`received_by`);

CREATE INDEX `payments_index_52` ON `payments` (`paid_at`);

CREATE INDEX `notifications_index_53` ON `notifications` (`user_id`);

CREATE INDEX `notifications_index_54` ON `notifications` (`type`);

CREATE INDEX `notifications_index_55` ON `notifications` (`is_read`);

CREATE INDEX `notifications_index_56` ON `notifications` (`created_at`);

ALTER TABLE `roles` COMMENT = 'Nhom quyen nguoi dung';

ALTER TABLE `users` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `dishes` ADD FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `restaurant_tables` ADD FOREIGN KEY (`area_id`) REFERENCES `dining_areas` (`id`);

ALTER TABLE `bookings` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `bookings` ADD FOREIGN KEY (`area_id`) REFERENCES `dining_areas` (`id`);

ALTER TABLE `bookings` ADD FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`);

ALTER TABLE `booking_details` ADD FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

ALTER TABLE `booking_details` ADD FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`);

ALTER TABLE `booking_status_histories` ADD FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

ALTER TABLE `booking_status_histories` ADD FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`);

ALTER TABLE `order_status_histories` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_status_histories` ADD FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

ALTER TABLE `kitchen_tickets` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `kitchen_ticket_items` ADD FOREIGN KEY (`ticket_id`) REFERENCES `kitchen_tickets` (`id`);

ALTER TABLE `kitchen_ticket_items` ADD FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`);

ALTER TABLE `invoices` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
