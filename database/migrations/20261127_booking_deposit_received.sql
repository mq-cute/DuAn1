ALTER TABLE bookings
    ADD COLUMN deposit_received_at DATETIME NULL DEFAULT NULL AFTER deposit_amount,
    ADD COLUMN deposit_received_by BIGINT NULL DEFAULT NULL AFTER deposit_received_at,
    ADD CONSTRAINT fk_bookings_deposit_received_by
        FOREIGN KEY (deposit_received_by) REFERENCES users(id) ON DELETE SET NULL;
