# L'Épicure Gastronomie

Website đặt bàn và quản lý nhà hàng, sử dụng PHP 8.1+, MySQL 8+, PDO và kiến trúc MVC.

## Yêu cầu

- PHP 8.1 trở lên, bật `pdo`, `pdo_mysql`, `mbstring` và `fileinfo`
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

## Cấp quyền quản trị ban đầu

Không có tài khoản admin mặc định trong source. Sau khi đăng ký tài khoản của bạn, dùng MySQL Workbench hoặc console để cấp quyền quản trị cho đúng email:

```sql
UPDATE users
SET role_id = (SELECT id FROM roles WHERE name = 'admin')
WHERE email = 'email-cua-ban@example.com';
```

Sau đó đăng nhập lại; menu quản trị danh mục và món ăn chỉ xuất hiện với vai trò `admin`.

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

## Thực đơn và danh mục

- Khách: `/menu`, `/menu/{slug}` và `/cart`. Giỏ chọn món chỉ lưu trong session; đây chưa phải đặt bàn hay đơn gọi món.
- Quản trị: `/admin/menu` và `/admin/menu/categories`. Danh mục đang chứa món không thể xóa; món đã được dùng trong lịch sử giao dịch cũng không thể xóa.
- Ảnh món chỉ nhận JPEG, PNG hoặc WebP, giới hạn 5 MB. File được đổi tên ngẫu nhiên và lưu trong `public/assets/uploads/dishes`.

## Đặt bàn

- Khách hàng đăng nhập có thể tìm bàn và gửi yêu cầu tại `/bookings/new`; yêu cầu mới ở trạng thái `pending` cho đến khi nhân viên xác nhận.
- `/account/bookings` hiển thị lịch sử và chi tiết đặt bàn; khách có thể hủy khi trạng thái còn `pending` hoặc `confirmed`.
- Nhân viên và admin xử lý yêu cầu tại `/admin/bookings` theo luồng xác nhận, nhận bàn, hoàn tất, từ chối/hủy hoặc đánh dấu khách không đến.
- Hệ thống kiểm tra sức chứa, thời gian trùng và trạng thái bàn lại trong transaction trước khi tạo đặt bàn. Món chọn trước được kiểm tra giá/trạng thái trên server và lưu giá snapshot trong `booking_details`.
- Thời lượng giữ bàn hiện được đặt mặc định là 120 phút trong `Booking::DURATION_MINUTES`; đây là giả định cấu hình cho giai đoạn hiện tại. Cần có khu vực và bàn trong `dining_areas`/`restaurant_tables` trước khi khách có thể tìm bàn.

## Kiểm tra nhanh

```powershell
php tests/RouterTest.php
php tests/ViewsSmokeTest.php
```

Các luồng đọc/ghi danh mục và món ăn cần MySQL đã cấu hình trong `.env` và schema/roles đã được import.
