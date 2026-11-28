ALTER TABLE `booking_status_histories`
  MODIFY COLUMN `old_status` ENUM (
    'pending', 'confirmed', 'seated', 'waiting_pending', 'completed', 'cancelled', 'no_show'
  ) NULL,
  MODIFY COLUMN `new_status` ENUM (
    'pending', 'confirmed', 'seated', 'waiting_pending', 'completed', 'cancelled', 'no_show'
  ) NOT NULL;
