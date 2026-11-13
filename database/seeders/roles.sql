INSERT INTO roles (name, description)
VALUES
  ('admin', 'Quản trị viên'),
  ('staff', 'Nhân viên phục vụ'),
  ('kitchen', 'Nhân viên bếp'),
  ('customer', 'Khách hàng')
ON DUPLICATE KEY UPDATE description = VALUES(description);
