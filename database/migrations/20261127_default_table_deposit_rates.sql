-- Default deposit rates for tables without an individually configured rate.
-- Standard: 100,000 VND; VIP: 200,000 VND.
UPDATE restaurant_tables
SET table_fee = CASE WHEN is_vip = 1 THEN 200000.00 ELSE 100000.00 END
WHERE table_fee IS NULL OR table_fee <= 0;
