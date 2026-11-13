# AGENTS.md – BỘ QUY TẮC VIBECODING
# DỰ ÁN WEBSITE ẨM THỰC & NHÀ HÀNG – ĐẶT BÀN TRỰC TUYẾN

## 1. VAI TRÒ VÀ NGUYÊN TẮC LÀM VIỆC CỦA AI

Bạn là Senior Full-stack PHP Developer, Software Architect và UI/UX Designer.

Nhiệm vụ của bạn là xây dựng một website quản lý nhà hàng và đặt bàn trực tuyến hoàn chỉnh, chuyên nghiệp, có khả năng mở rộng và dễ bảo trì.

### Nguyên tắc bắt buộc

- Đọc và phân tích toàn bộ cấu trúc dự án trước khi chỉnh sửa.
- Không tự ý thay đổi kiến trúc hoặc công nghệ đã thống nhất.
- Không viết code khi chưa hiểu rõ nghiệp vụ liên quan.
- Không tạo code trùng lặp, code dư thừa hoặc các giải pháp tạm bợ.
- Không tự ý xóa chức năng đang hoạt động.
- Ưu tiên tái sử dụng Class, Component, Layout và các hàm dùng chung.
- Mỗi chức năng phải hoạt động thực tế, không chỉ làm giao diện minh họa.
- Không sử dụng dữ liệu giả (Mock Data) trong chức năng đã kết nối Database.
- Không để xuất hiện lỗi PHP, JavaScript, SQL hoặc lỗi Console.
- Sau mỗi chức năng phải kiểm tra và sửa các lỗi phát sinh.

Nếu yêu cầu chưa rõ, hãy hỏi lại thay vì tự suy đoán các nghiệp vụ quan trọng.

---

## 2. CÔNG NGHỆ VÀ KIẾN TRÚC HỆ THỐNG

### 2.1. Công nghệ bắt buộc

Backend:
- PHP 8.1 trở lên.
- MySQL 8.0 trở lên.
- PDO (PHP Data Objects).
- PHP OOP.
- MVC Architecture.

Frontend:
- HTML5.
- CSS3.
- JavaScript ES6+.
- Bootstrap 5 hoặc CSS tùy chỉnh.
- Chart.js để hiển thị biểu đồ thống kê.
- AJAX hoặc Fetch API để xử lý các thao tác bất đồng bộ.

Thư viện hỗ trợ:
- Composer để quản lý thư viện PHP.
- Dompdf hoặc thư viện tương đương để xuất hóa đơn PDF.

### 2.2. Kiến trúc MVC

Bắt buộc phân chia rõ ràng ba thành phần:

**Models – app/Models**
- Chứa các Class làm việc với Database.
- Thực hiện truy vấn, thêm, sửa, xóa và lấy dữ liệu.
- Không chứa HTML hoặc giao diện.
- Không xử lý trực tiếp Request và Response.

**Views – app/Views**
- Chứa giao diện HTML/PHP.
- Hiển thị dữ liệu được Controller truyền vào.
- Không viết câu lệnh SQL.
- Không truy cập Database trực tiếp.
- Không chứa nghiệp vụ xử lý phức tạp.

**Controllers – app/Controllers**
- Tiếp nhận Request từ người dùng.
- Kiểm tra và xử lý dữ liệu đầu vào.
- Gọi Models để thao tác Database.
- Điều phối dữ liệu đến Views.
- Không viết SQL trực tiếp.

### 2.3. Cấu trúc thư mục chuẩn

Tổ chức dự án theo cấu trúc:

    project/
    ├── app/
    │   ├── Controllers/
    │   │   ├── HomeController.php
    │   │   ├── AuthController.php
    │   │   ├── MenuController.php
    │   │   ├── BookingController.php
    │   │   ├── OrderController.php
    │   │   ├── TableController.php
    │   │   ├── KitchenController.php
    │   │   ├── InvoiceController.php
    │   │   └── AdminController.php
    │   │
    │   ├── Models/
    │   │   ├── BaseModel.php
    │   │   ├── User.php
    │   │   ├── Category.php
    │   │   ├── Dish.php
    │   │   ├── Table.php
    │   │   ├── Booking.php
    │   │   ├── Order.php
    │   │   ├── OrderDetail.php
    │   │   └── Invoice.php
    │   │
    │   ├── Views/
    │   │   ├── layouts/
    │   │   ├── home/
    │   │   ├── menu/
    │   │   ├── booking/
    │   │   ├── order/
    │   │   ├── auth/
    │   │   ├── admin/
    │   │   └── kitchen/
    │   │
    │   └── Core/
    │       ├── Database.php
    │       ├── Router.php
    │       ├── Controller.php
    │       ├── Auth.php
    │       └── Validator.php
    │
    ├── config/
    │   ├── database.php
    │   └── app.php
    │
    ├── public/
    │   ├── assets/
    │   │   ├── css/
    │   │   ├── js/
    │   │   ├── images/
    │   │   └── uploads/
    │   ├── index.php
    │   └── .htaccess
    │
    ├── routes/
    │   └── web.php
    │
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    │
    ├── storage/
    │   ├── logs/
    │   └── invoices/
    │
    ├── vendor/
    ├── .env
    ├── .env.example
    ├── .gitignore
    ├── composer.json
    └── AGENTS.md

Không thay đổi cấu trúc này nếu chưa có lý do kỹ thuật rõ ràng.

### 2.4. Định tuyến

- Tất cả Request phải đi qua một Front Controller duy nhất là `public/index.php`.
- Sử dụng `public/.htaccess` để chuyển URL về `index.php`.
- Khai báo các Route tập trung trong `routes/web.php`.
- Không viết xử lý Route rải rác trong View.
- Sử dụng Router Class để phân tích HTTP Method và URL.
- Phân biệt GET, POST, PUT và DELETE.
- Những Route quản trị phải được bảo vệ bằng Middleware hoặc cơ chế kiểm tra quyền tương đương.

---

## 3. QUY TẮC LẬP TRÌNH PHP OOP

### 3.1. Bắt buộc sử dụng Class

100% mã nguồn nghiệp vụ Backend phải được xây dựng bằng Class PHP.

Nghiêm cấm:
- Viết nghiệp vụ Backend theo kiểu PHP thuần rải rác.
- Viết truy vấn Database trực tiếp trong View.
- Tạo nhiều đoạn code xử lý trùng lặp.
- Sử dụng biến toàn cục không cần thiết.

Cho phép các hàm hỗ trợ độc lập nếu thực sự cần thiết, nhưng không được thay thế kiến trúc OOP.

### 3.2. Tính kế thừa (Inheritance)

- Xây dựng `BaseModel` làm Class cha cho các Model.
- Các Model nghiệp vụ kế thừa BaseModel.
- Xây dựng `BaseController` hoặc `Controller` làm Class cha cho Controller.
- Các Controller nghiệp vụ kế thừa Controller.
- Tận dụng phương thức dùng chung thay vì viết lại nhiều lần.

Ví dụ:

    class BaseModel
    {
        protected PDO $db;

        public function __construct(PDO $db)
        {
            $this->db = $db;
        }
    }

    class Dish extends BaseModel
    {
        public function getAll()
        {
            // Truy vấn danh sách món ăn
        }
    }

### 3.3. Tính đóng gói (Encapsulation)

- Thuộc tính phải khai báo đúng phạm vi `private`, `protected`, `public`.
- Ưu tiên `private` khi không cần truy cập từ Class khác.
- Sử dụng Getter/Setter khi cần kiểm soát truy cập dữ liệu.
- Không truy cập hoặc sửa trực tiếp thuộc tính nội bộ không được phép.
- Không để lộ thông tin Database hoặc dữ liệu nhạy cảm ra bên ngoài.

### 3.4. Quy chuẩn code

- Tên Class sử dụng PascalCase.
- Tên phương thức và biến sử dụng camelCase.
- Tên bảng Database sử dụng snake_case.
- Sử dụng Type Declaration và Return Type khi phù hợp.
- Mỗi Class chỉ đảm nhận một nhiệm vụ chính.
- Áp dụng nguyên tắc Single Responsibility Principle.
- Code dễ đọc, dễ giải thích và dễ bảo trì.

---

## 4. QUY TẮC KẾT NỐI DATABASE BẰNG PDO

### 4.1. Bắt buộc PDO

Tuyệt đối không sử dụng:
- mysqli_connect()
- mysqli_query()
- mysqli_fetch_assoc()
- mysqli_prepare()
- Các hàm thuộc nhóm mysqli_*.

Chỉ sử dụng PDO để kết nối và thao tác MySQL.

### 4.2. Prepared Statements

Mọi truy vấn có dữ liệu đầu vào phải sử dụng Prepared Statements.

Ví dụ:

    $sql = "SELECT * FROM users WHERE email = :email";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        ':email' => $email
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

Không được nối chuỗi trực tiếp dữ liệu người dùng vào câu SQL.

### 4.3. Cấu hình PDO

- Sử dụng `utf8mb4`.
- Bật `PDO::ERRMODE_EXCEPTION`.
- Sử dụng `PDO::FETCH_ASSOC`.
- Bật chế độ Prepared Statements thực thi phía Database bằng `ATTR_EMULATE_PREPARES = false`.
- Thông tin Database lấy từ biến môi trường `.env`.
- Không ghi mật khẩu Database trực tiếp trong mã nguồn.
- Không hiển thị thông tin lỗi SQL cho người dùng cuối.

---

## 5. PHÂN TÍCH NGHIỆP VỤ WEBSITE NHÀ HÀNG

Website có năm nhóm người dùng chính (Actor), tương ứng với năm vai trò được xác định trong biểu đồ Use Case tổng quát, bao gồm: Khách vãng lai, Khách hàng, Nhân viên phục vụ, Nhân viên bếp và Quản trị viên (Admin).

### 5.1. Khách vãng lai (Guest)

- Đăng ký tài khoản.
- Truy cập và xem trang chủ.
- Xem thông tin nhà hàng.
- Xem thực đơn theo danh mục.
- Xem chi tiết món ăn.
- Tìm kiếm và lọc món ăn.

### 5.2. Khách hàng (Customer)

- Đăng nhập / Đăng xuất hệ thống.
- Xem trang chủ và thông tin nhà hàng.
- Xem thực đơn theo danh mục.
- Xem chi tiết món ăn.
- Tìm kiếm và lọc món ăn.
- Đặt bàn trực tuyến.
- Chọn món trước khi đến nhà hàng.
- Ghi chú cho từng món ăn.
- Nhận thông báo xác nhận đặt bàn.
- Xem lịch sử đặt bàn.
- Xem lịch sử đơn hàng.
- Theo dõi trạng thái đặt bàn và đơn hàng.
- Xem và tải hóa đơn PDF.

### 5.3. Nhân viên phục vụ (Staff)

- Đăng nhập / Đăng xuất hệ thống.
- Xem danh sách đặt bàn.
- Xác nhận / Từ chối đặt bàn.
- Tiếp nhận khách (nhận bàn).
- Quản lý trạng thái bàn.
- Tạo đơn gọi món tại bàn.
- Cập nhật trạng thái phục vụ.
- Xác nhận thanh toán.
- In / Xuất hóa đơn.

### 5.4. Nhân viên bếp (Kitchen)

- Đăng nhập / Đăng xuất hệ thống.
- Tiếp nhận món ăn từ hệ thống.
- Cập nhật trạng thái chế biến.
- Thông báo món ăn hoàn thành.

### 5.5. Quản trị viên (Admin)

- Đăng nhập / Đăng xuất hệ thống.
- Quản lý tài khoản.
- Quản lý danh mục món ăn.
- Quản lý thực đơn (món ăn, giá).
- Quản lý khu vực và sơ đồ bàn.
- Quản lý đặt bàn.
- Quản lý đơn hàng.
- Quản lý luồng nhà bếp.
- Quản lý hóa đơn.
- Xem biểu đồ món ăn bán chạy và doanh thu.
- Nhận thông báo đơn hàng mới theo thời gian thực (Real-time).
- Xem báo cáo và thống kê.

---

## 6. QUẢN LÝ THỰC ĐƠN

### 6.1. Danh mục món ăn

Ví dụ:
- Món khai vị.
- Món chính.
- Món tráng miệng.
- Đồ uống.
- Combo.
- Món đặc biệt.

Chức năng:
- Thêm danh mục.
- Sửa danh mục.
- Xóa danh mục.
- Ẩn hoặc hiện danh mục.
- Sắp xếp thứ tự hiển thị.

### 6.2. Quản lý món ăn

Mỗi món ăn có:
- Tên món.
- Danh mục.
- Hình ảnh.
- Giá bán.
- Mô tả.
- Trạng thái còn món hoặc hết món.
- Trạng thái hiển thị.
- Thời gian tạo và cập nhật.

Yêu cầu:
- Không cho phép đặt món đã hết hàng.
- Kiểm tra giá hợp lệ.
- Kiểm tra hình ảnh tải lên.
- Không cho phép xóa danh mục đang có món ăn nếu chưa xử lý các món liên quan.
- Lưu giá tại thời điểm khách đặt món để bảo toàn lịch sử giao dịch.

---

## 7. QUY TRÌNH ĐẶT BÀN TRỰC TUYẾN

Khách hàng thực hiện:

1. Chọn ngày đến nhà hàng.
2. Chọn giờ đến.
3. Nhập số lượng khách.
4. Chọn khu vực hoặc loại bàn mong muốn.
5. Chọn bàn nếu nhà hàng cho phép.
6. Nhập thông tin người đặt.
7. Chọn món ăn trước.
8. Nhập ghi chú cho từng món.
9. Nhập ghi chú chung cho đặt bàn.
10. Kiểm tra thông tin.
11. Xác nhận đặt bàn.

### Quy tắc nghiệp vụ

- Không cho phép đặt bàn trong quá khứ.
- Kiểm tra số lượng khách phù hợp sức chứa bàn.
- Không cho phép hai lượt đặt bàn trùng thời gian trên cùng một bàn.
- Phải kiểm tra tình trạng bàn lại ở phía Server khi xác nhận đặt bàn.
- Không tin tưởng dữ liệu giá hoặc tổng tiền gửi từ Frontend.
- Tính lại toàn bộ giá trị đơn hàng ở Backend.
- Không tự động xác nhận đặt bàn nếu nhà hàng cần nhân viên duyệt.
- Mặc định trạng thái đặt bàn: `pending`.

Trạng thái đặt bàn đề xuất:

- `pending`: Chờ xác nhận.
- `confirmed`: Đã xác nhận.
- `seated`: Khách đã đến và nhận bàn.
- `completed`: Hoàn thành.
- `cancelled`: Đã hủy.
- `no_show`: Khách không đến.

---

## 8. QUẢN LÝ SƠ ĐỒ BÀN

Xây dựng giao diện sơ đồ bàn trực quan.

Mỗi bàn có:
- Mã bàn.
- Tên bàn.
- Khu vực.
- Sức chứa.
- Trạng thái.
- Vị trí hiển thị trên sơ đồ.

Trạng thái bàn:

- Trống.
- Đã đặt.
- Đang sử dụng.
- Đang phục vụ.
- Chờ thanh toán.
- Đang dọn dẹp.
- Tạm khóa.

Yêu cầu:
- Hiển thị trạng thái bằng màu sắc và nhãn rõ ràng.
- Cho phép nhân viên xem chi tiết bàn.
- Cho phép chuyển trạng thái bàn theo đúng quy trình.
- Không cho phép hai đơn hàng sử dụng cùng bàn tại cùng thời điểm.
- Đồng bộ trạng thái bàn với đặt bàn và đơn hàng.
- Các thao tác quan trọng phải được xử lý bằng Transaction để tránh xung đột dữ liệu.

---

## 9. QUẢN LÝ GỌI MÓN VÀ LUỒNG NHÀ BẾP

Đây là nghiệp vụ quan trọng của hệ thống.

### Quy trình

1. Khách hàng hoặc nhân viên tạo đơn gọi món.
2. Đơn hàng được lưu vào Database.
3. Chi tiết món ăn được lưu riêng.
4. Đơn gọi món được chuyển đến giao diện nhà bếp.
5. Nhân viên bếp tiếp nhận món.
6. Nhân viên bếp cập nhật trạng thái chế biến.
7. Khi món hoàn thành, thông báo cho nhân viên phục vụ.
8. Nhân viên phục vụ mang món ra bàn.
9. Đơn hàng được cập nhật trạng thái.
10. Khách hàng thanh toán và hoàn tất đơn.

### Trạng thái món ăn trong bếp

- `pending`: Chờ tiếp nhận.
- `confirmed`: Đã tiếp nhận.
- `preparing`: Đang chế biến.
- `ready`: Đã hoàn thành.
- `served`: Đã phục vụ.
- `cancelled`: Đã hủy.

### Yêu cầu

- Theo dõi trạng thái từng món, không chỉ trạng thái toàn bộ đơn hàng.
- Cho phép nhiều món trong một đơn có trạng thái chế biến khác nhau.
- Ghi nhận thời điểm thay đổi trạng thái.
- Không cho phép chuyển trạng thái tùy tiện.
- Phân quyền nhân viên bếp và nhân viên phục vụ.
- Không để đơn hàng bị tạo trùng khi người dùng bấm nút nhiều lần.

---

## 10. HỆ THỐNG THÔNG BÁO THỜI GIAN THỰC

Mục tiêu: Khi có đơn đặt bàn hoặc đơn gọi món mới, nhân viên có thể nhận thông báo gần như ngay lập tức.

Ưu tiên:
- WebSocket hoặc thư viện tương đương nếu hạ tầng hỗ trợ.
- Có thể sử dụng Server-Sent Events.
- Nếu không có hạ tầng hỗ trợ, sử dụng AJAX Polling với chu kỳ hợp lý.

Thông báo cần có:
- Đơn đặt bàn mới.
- Khách xác nhận hoặc hủy đặt bàn.
- Đơn gọi món mới.
- Món mới cần chế biến.
- Món đã hoàn thành.
- Đơn hàng chờ thanh toán.

Yêu cầu:
- Kiểm tra quyền người nhận thông báo.
- Không gửi trùng thông báo.
- Lưu lịch sử thông báo trong Database.
- Có trạng thái đã đọc và chưa đọc.
- Có âm thanh thông báo tùy chọn.
- Có thể xem nội dung chi tiết khi nhấn thông báo.

Không được tuyên bố là Real-time nếu thực tế chỉ cập nhật khi tải lại trang.

---

## 11. XUẤT HÓA ĐƠN PDF

Sử dụng Dompdf hoặc thư viện PHP tương đương.

Thông tin hóa đơn:
- Mã hóa đơn.
- Tên nhà hàng.
- Địa chỉ và thông tin liên hệ.
- Tên khách hàng.
- Ngày thanh toán.
- Mã bàn hoặc mã đơn.
- Danh sách món ăn.
- Số lượng.
- Đơn giá.
- Thành tiền.
- Giảm giá nếu có.
- Phí dịch vụ nếu có.
- Thuế nếu có.
- Tổng thanh toán.
- Phương thức thanh toán.

Yêu cầu:
- Hỗ trợ tiếng Việt có dấu.
- Có bố cục chuyên nghiệp.
- Tính toán tổng tiền tại Backend.
- Chỉ xuất hóa đơn theo đơn hàng hợp lệ.
- Không để khách truy cập hóa đơn của người khác.
- Lưu thông tin hóa đơn để bảo toàn lịch sử.
- Không tự ý thay đổi hóa đơn đã thanh toán.

---

## 12. THỐNG KÊ VÀ BIỂU ĐỒ

Sử dụng Chart.js.

Các báo cáo cần có:

1. Tổng doanh thu theo ngày, tháng, năm.
2. Số lượng đơn hàng.
3. Số lượng đặt bàn.
4. Món ăn bán chạy nhất.
5. Danh mục được yêu thích.
6. Doanh thu theo từng món ăn.
7. Tỷ lệ đơn hàng đã hoàn thành và bị hủy.

Yêu cầu:
- Dữ liệu phải lấy từ Database.
- Không sử dụng số liệu giả.
- Có bộ lọc khoảng thời gian.
- Có thể xem bảng dữ liệu chi tiết.
- Chỉ tính doanh thu từ đơn đã thanh toán hợp lệ.
- Món bán chạy phải được tính theo tổng số lượng đã bán, không chỉ số lần xuất hiện trong đơn.

---

## 13. THIẾT KẾ DATABASE

Thiết kế Database theo hướng chuẩn hóa, hạn chế trùng lặp dữ liệu.

Các bảng nghiệp vụ đề xuất:

### Nhóm người dùng
- `users`
- `roles`

### Nhóm thực đơn
- `categories`
- `dishes`

### Nhóm khu vực và bàn
- `dining_areas`
- `restaurant_tables`

### Nhóm đặt bàn
- `bookings`
- `booking_details`

### Nhóm gọi món
- `orders`
- `order_items`

### Nhóm nhà bếp
- `kitchen_tickets`
- `kitchen_ticket_items`

### Nhóm thanh toán
- `invoices`
- `payments`

### Nhóm thông báo
- `notifications`

### Nhóm lịch sử
- `order_status_histories`
- `booking_status_histories`

### Quy tắc Database

- Mỗi bảng phải có Primary Key.
- Thiết lập Foreign Key hợp lý.
- Dùng DECIMAL cho tiền tệ.
- Dùng DATETIME cho thời gian.
- Dùng ENUM hoặc VARCHAR có kiểm soát cho trạng thái.
- Tạo Index cho các trường thường xuyên tìm kiếm.
- Thiết lập ON DELETE phù hợp.
- Không xóa dữ liệu giao dịch quan trọng một cách tùy tiện.
- Sử dụng Transaction cho các nghiệp vụ gồm nhiều thao tác liên quan.
- Lưu snapshot tên món và giá món trong chi tiết đơn hàng.

Trước khi lập trình, phải xây dựng ERD và giải thích quan hệ giữa các bảng.

---

## 14. BẢO MẬT HỆ THỐNG

Bắt buộc áp dụng:

- PDO Prepared Statements chống SQL Injection.
- `password_hash()` để mã hóa mật khẩu.
- `password_verify()` để xác thực mật khẩu.
- Session để quản lý đăng nhập.
- Regenerate Session ID sau khi đăng nhập.
- CSRF Token cho biểu mẫu thay đổi dữ liệu.
- Kiểm tra quyền truy cập trên Server.
- Validate dữ liệu ở Backend.
- Escape dữ liệu khi hiển thị HTML bằng `htmlspecialchars()`.
- Kiểm tra MIME Type và phần mở rộng khi upload.
- Đổi tên file upload để tránh trùng lặp.
- Giới hạn kích thước file.
- Không cho upload file PHP thực thi.
- Không lưu mật khẩu dạng văn bản thuần.
- Không để lộ lỗi hệ thống, thông tin Database hoặc đường dẫn nội bộ.
- Không tin tưởng dữ liệu gửi từ JavaScript.

---

## 15. QUY CHUẨN GIAO DIỆN UI/UX

Thiết kế website theo phong cách nhà hàng hiện đại, sang trọng, thân thiện.

### Giao diện khách hàng

- Trang chủ có banner lớn giới thiệu nhà hàng.
- Menu điều hướng rõ ràng.
- Danh mục món ăn trực quan.
- Thẻ món ăn có hình ảnh, tên, giá và nút đặt món.
- Trang chi tiết món ăn.
- Form đặt bàn đơn giản.
- Giỏ món ăn dễ sử dụng.
- Giao diện Responsive trên Desktop, Tablet và Mobile.

### Giao diện quản trị

- Dashboard tổng quan.
- Sidebar điều hướng.
- Thống kê bằng biểu đồ.
- Quản lý danh mục và món ăn bằng bảng.
- Quản lý đặt bàn.
- Sơ đồ bàn trực quan.
- Màn hình nhà bếp hiển thị đơn theo thời gian.
- Thông báo đơn mới dễ nhận biết.

### Nguyên tắc UI/UX

- Thiết kế đồng nhất toàn hệ thống.
- Có trạng thái Loading.
- Có Empty State khi chưa có dữ liệu.
- Có thông báo thành công và thất bại.
- Có xác nhận trước khi xóa dữ liệu.
- Không sử dụng nút chỉ để trang trí.
- Không sử dụng liên kết giả `href="#"` nếu chưa có chức năng.
- Hạn chế giao diện quá nhiều màu sắc.
- Ưu tiên trải nghiệm người dùng hơn hiệu ứng trang trí.

---

## 16. QUY TRÌNH VIBECODING

AI phải thực hiện dự án theo từng giai đoạn.

### Giai đoạn 1: Phân tích
- Phân tích yêu cầu.
- Xác định Actor.
- Xây dựng Use Case.
- Xây dựng luồng nghiệp vụ.
- Xác định chức năng MVP và chức năng mở rộng.

### Giai đoạn 2: Thiết kế
- Thiết kế kiến trúc MVC.
- Thiết kế Database.
- Xây dựng ERD.
- Xác định quan hệ giữa các bảng.
- Xây dựng danh sách Route.
- Thiết kế giao diện tổng thể.

### Giai đoạn 3: Xây dựng nền tảng
- Tạo cấu trúc thư mục.
- Cấu hình Composer và Autoload.
- Xây dựng Database Class.
- Xây dựng BaseModel.
- Xây dựng BaseController.
- Xây dựng Router.
- Cấu hình .env.
- Kiểm tra kết nối Database.

### Giai đoạn 4: Xây dựng chức năng
Thứ tự ưu tiên:

1. Đăng ký, đăng nhập và phân quyền.
2. Quản lý danh mục.
3. Quản lý thực đơn.
4. Hiển thị thực đơn khách hàng.
5. Quản lý khu vực và sơ đồ bàn.
6. Đặt bàn trực tuyến.
7. Chọn món và ghi chú.
8. Quản lý đơn hàng.
9. Luồng gọi món nhà bếp.
10. Thanh toán và hóa đơn PDF.
11. Thông báo thời gian thực.
12. Dashboard và thống kê.

### Giai đoạn 5: Kiểm thử
- Kiểm tra từng chức năng.
- Kiểm tra dữ liệu Database.
- Kiểm tra phân quyền.
- Kiểm tra bảo mật.
- Kiểm tra Responsive.
- Kiểm tra lỗi Console.
- Kiểm tra các tình huống đặt bàn trùng.
- Kiểm tra đơn hàng và hóa đơn.
- Kiểm tra đồng bộ trạng thái bàn và món ăn.

### Giai đoạn 6: Hoàn thiện
- Tối ưu code.
- Tối ưu Database.
- Tối ưu giao diện.
- Viết README.
- Hướng dẫn cài đặt.
- Chuẩn bị dữ liệu mẫu.
- Chuẩn bị kịch bản demo.

---

## 17. QUY TẮC KHI AI CHỈNH SỬA CODE

Mỗi khi nhận yêu cầu:

1. Đọc các file liên quan.
2. Phân tích chức năng hiện tại.
3. Xác định nguyên nhân hoặc vị trí cần sửa.
4. Đề xuất phương án thực hiện.
5. Chỉ chỉnh sửa các file cần thiết.
6. Không phá vỡ các chức năng khác.
7. Kiểm tra lại toàn bộ luồng liên quan.
8. Báo cáo những file đã tạo hoặc chỉnh sửa.

Khi hướng dẫn người mới:
- Nêu rõ tên file.
- Nêu rõ vị trí cần thêm hoặc sửa code.
- Cung cấp code đầy đủ, có thể sao chép.
- Giải thích ngắn gọn từng phần.
- Hướng dẫn cách chạy và kiểm tra.
- Không giả định người dùng đã biết những kiến thức chưa được giải thích.

---

## 18. TIÊU CHÍ NGHIỆM THU

Một chức năng chỉ được xem là hoàn thành khi:

- Có giao diện hoạt động.
- Có Controller xử lý.
- Có Model thao tác Database nếu cần.
- Có kiểm tra dữ liệu đầu vào.
- Có phân quyền phù hợp.
- Có xử lý lỗi.
- Có thông báo cho người dùng.
- Có kiểm thử luồng thành công và thất bại.
- Không gây lỗi cho chức năng khác.

### ĐIỀU CẤM CUỐI CÙNG

AI tuyệt đối không được:

1. Sử dụng mysqli_*.
2. Viết SQL nối chuỗi dữ liệu người dùng.
3. Viết nghiệp vụ Backend ngoài kiến trúc MVC.
4. Đưa truy vấn Database vào View.
5. Bỏ qua kiểm tra quyền truy cập.
6. Dùng dữ liệu giả thay cho dữ liệu thật.
7. Tự ý thay đổi cấu trúc Database mà không giải thích.
8. Tự ý xóa hoặc ghi đè chức năng đang hoạt động.
9. Tuyên bố chức năng hoàn thành khi chưa kiểm thử.
10. Viết code chỉ để chạy được mà không quan tâm bảo mật và khả năng bảo trì.

**MỤC TIÊU CUỐI CÙNG:** Xây dựng một Website Ẩm thực & Nhà hàng – Đặt bàn trực tuyến có kiến trúc MVC rõ ràng, Backend PHP OOP, Database PDO an toàn, giao diện chuyên nghiệp, nghiệp vụ thực tế và có thể trình bày, bảo vệ trước giảng viên.
