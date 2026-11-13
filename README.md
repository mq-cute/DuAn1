# L'Épicure Gastronomie

Website đặt bàn và quản lý nhà hàng, sử dụng PHP 8.1+, MySQL 8+, PDO và kiến trúc MVC.

## Yêu cầu

- PHP 8.1 trở lên, bật `pdo` và `pdo_mysql`
- MySQL 8 trở lên
- Composer (không cần thư viện bên thứ ba ở giai đoạn hiện tại)

## Cài đặt và chạy

1. Tạo database `restaurant_booking` với charset `utf8mb4`.
2. Import toàn bộ [`database/schema.sql`](./database/schema.sql).
3. Import [`database/seeders/roles.sql`](./database/seeders/roles.sql) để tạo các vai trò.
4. Sao chép `.env.example` thành `.env`, rồi điền cấu hình kết nối MySQL.
5. Mở terminal tại thư mục dự án và chạy `php -S localhost:8000 -t public`.
6. Truy cập `http://localhost:8000`, đăng ký tài khoản khách hàng và thử đăng nhập.

Với Apache, đặt document root vào thư mục `public` để các rewrite rules hoạt động. Không đặt thư mục gốc dự án làm document root.

## Đăng nhập và phân quyền

Khách hàng có thể tự đăng ký tại `/register`. Chỉ tài khoản `customer` được tạo theo cách này; tài khoản nhân viên cần do quản trị viên cấp. Mật khẩu được lưu bằng `password_hash()`. Form POST có CSRF token; session dùng cookie HttpOnly và được đổi ID sau đăng nhập.

## Cấu trúc chính

- `app/Controllers`, `app/Models`, `app/Views`, `app/Core`: ứng dụng MVC
- `routes/web.php`: khai báo route
- `config`: cấu hình ứng dụng và PDO
- `public`: front controller và tài nguyên công khai
- `database`: schema và dữ liệu vai trò ban đầu
- `storage/logs`: log lỗi ứng dụng

Không commit `.env`. Mặc định lỗi chi tiết được ghi vào `storage/logs/app.log` và không hiển thị cho người dùng khi `APP_DEBUG=false`.
