ALTER TABLE bookings
    ADD COLUMN voucher_code VARCHAR(50) NULL AFTER deposit_amount,
    ADD COLUMN voucher_discount_type ENUM('percentage', 'fixed') NULL AFTER voucher_code,
    ADD COLUMN voucher_discount_value DECIMAL(12,2) NULL AFTER voucher_discount_type,
    ADD COLUMN voucher_min_order_amount DECIMAL(12,2) NULL AFTER voucher_discount_value,
    ADD COLUMN voucher_max_discount_amount DECIMAL(12,2) NULL AFTER voucher_min_order_amount;
