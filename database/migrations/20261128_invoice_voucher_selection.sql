ALTER TABLE `orders`
  ADD COLUMN `voucher_id` bigint NULL AFTER `voucher_code`;

ALTER TABLE `invoices`
  ADD COLUMN `voucher_id` bigint NULL AFTER `discount`;

UPDATE `vouchers` v
SET v.`used_count` = (
  SELECT COUNT(*)
  FROM `invoices` i
  INNER JOIN `orders` o ON o.`id` = i.`order_id`
  INNER JOIN `bookings` b ON b.`id` = o.`booking_id`
  WHERE b.`voucher_code` COLLATE utf8mb4_unicode_ci = v.`code` COLLATE utf8mb4_unicode_ci
    AND i.`status` IN ('paid', 'partially_refunded', 'refunded')
);
