# FOODIES — Haute Cuisine & Restaurant Management System
> **Dự án Website Ẩm Thực, Đặt Bàn Trực Tuyến & Hệ Thống Quản Trị Nhà Hàng Đương Đại**  
> Xây dựng theo kiến trúc **MVC**, sử dụng **PHP 8.1+ OOP**, **MySQL 8.0+**, **PDO Prepared Statements**, **Tailwind CSS**, và **Chart.js**.

---

## 📌 MỤC LỤC
1. [Giới Thiệu Dự Án](#-giới-thiệu-dự-án)
2. [Công Nghệ & Kiến Trúc](#-công-nghệ--kiến-trúc)
3. [Phân Quyền & 5 Nhóm Người Dùng (Actors)](#-phân-quyền--5-nhóm-người-dùng-actors)
4. [Các Tính Năng Đã Hoàn Thiện](#-các-tính-năng-đã-hoàn-thiện)
   - [Trang Chủ Khách Hàng (Client Front-End)](#1-trang-chủ-khách-hàng-client-front-end)
   - [Quy Trình Đặt Bàn & Pre-Order Ẩm Thực](#2-quy-trình-đặt-bàn--pre-order-ẩm-thực)
   - [Cổng Thông Tin & Lịch Sử Khách Hàng](#3-cổng-thông-tin--lịch-sử-khách-hàng)
   - [Trung Tâm Quản Trị Admin (FOODIES Admin Suite)](#4-trung-tâm-quản-trị-admin-foodies-admin-suite)
   - [Vận Hành Phục Vụ Tại Bàn (Staff POS)](#5-vận-hành-phục-vụ-tại-bàn-staff-pos)
   - [Màn Hình Điều Phối Bếp KDS (Kitchen Display System)](#6-màn-hình-điều-phối-bếp-kds-kitchen-display-system)
   - [Hóa Đơn & Thanh Toán](#7-hóa-đơn--thanh-toán)
5. [Cấu Trúc Thư Mục Dự Án](#-cấu-trúc-thư-mục-dự-án)
6. [Cơ Sở Dữ Liệu (Database Schema)](#-cơ-sở-dữ-liệu-database-schema)
7. [Hướng Dẫn Cài Đặt & Khởi Chạy](#-hướng-dẫn-cài-đặt--khởi-chạy)
8. [Kiểm Thử Tự Động (Automated Testing)](#-kiểm-thử-tự-động-automated-testing)

---

## 🍽️ GIỚI THIỆU DỰ ÁN
**FOODIES Haute Cuisine** là nền tảng quản lý nhà hàng fine-dining và đặt bàn trực tuyến khép kín, phục vụ từ khâu tiếp thị thực đơn cao cấp, đặt bàn trước trực tuyến, chọn món trước (Pre-order), cho đến vận hành nội bộ tại nhà hàng (sơ đồ bàn ăn trực quan, mở bàn từ lịch hẹn, đổi bàn sự cố, gọi món tại bàn, luồng chế biến món ăn ở bếp KDS và xuất hóa đơn thanh toán).

Hệ thống được thiết kế theo tiêu chuẩn nhận diện thương hiệu sang trọng:
- Màu chủ đạo: **Navy Hoàng Gia** (`#121820`), **Vàng Gold** (`#c5a059`), **Đỏ Mận** (`#870000`) trên nền kem sáng (`#f8fafc`).
- Typo: Phong cách biên tập ẩm thực Pháp với font Headline serif quý phái và Body sans thanh lịch.

---

## 🛠️ CÔNG NGHỆ & KIẾN TRÚC

### Backend
- **PHP 8.1+**: 100% hướng đối tượng (OOP), tuân thủ nguyên tắc Single Responsibility, đóng gói nghiêm ngặt.
- **Kiến trúc MVC**: Phân tách triệt để Controller, Model, View và Core router.
- **PDO & Prepared Statements**: Chống tấn công SQL Injection tuyệt đối; không nối chuỗi dữ liệu người dùng vào câu truy vấn.
- **Bảo mật**: CSRF Token cho toàn bộ form thay đổi dữ liệu, mã hóa mật khẩu `password_hash()` / `password_verify()`, session cookie bảo mật HttpOnly, chống XSS qua `htmlspecialchars()`.

### Frontend
- **Tailwind CSS**: Thiết kế giao diện hiện đại, responsive trên Desktop, Tablet và Mobile.
- **JavaScript (ES6+) / Fetch API**: Xử lý tương tác thời gian thực, cập nhật trạng thái bàn, kéo thả và bộ lọc bất đồng bộ.
- **Chart.js**: Biểu đồ trực quan hóa dữ liệu tài chính, doanh thu theo thời gian và tỷ lệ hoàn tất đơn.
- **Google Material Symbols**: Hệ thống icon đồng bộ chuẩn nhận diện cao cấp.

---

## 👥 PHÂN QUYỀN & 5 NHÓM NGƯỜI DÙNG (ACTORS)

1. **Khách vãng lai (Guest):**
   - Xem trang chủ giới thiệu nhà hàng, di sản ẩm thực, dịch vụ setup độc bản.
   - Xem thực đơn theo danh mục, tìm kiếm và lọc món ăn, xem chi tiết món.
   - Đăng ký và đăng nhập tài khoản.

2. **Khách hàng (Customer):**
   - Đặt bàn trực tuyến, chọn khu vực/bàn mong muốn, chọn ngày giờ và số khách.
   - Chọn món ăn trước khi đến (Pre-order), nhập ghi chú cho từng món ăn.
   - Xem lịch sử đặt bàn, hủy lịch đặt trước nếu chưa đến giờ hẹn.
   - Theo dõi tiến độ chế biến món ăn theo thời gian thực (Đang chuẩn bị, Đang chế biến, Đã phục vụ).
   - Xem và tải hóa đơn điện tử của chính mình.
   - Quản lý thông báo cá nhân.

3. **Nhân viên phục vụ (Staff):**
   - Giao diện POS phục vụ riêng tại quầy/bàn: `/staff/pos`, `/staff/orders`.
   - Nhận khách, mở bàn, tạo đơn gọi món tại bàn.
   - Thêm món mới vào đơn đang phục vụ, cập nhật trạng thái món đã mang ra bàn (`served`).
   - In hóa đơn tạm tính (Prebill) và xác nhận thanh toán tiền mặt/chuyển khoản/thẻ.

4. **Nhân viên bếp (Kitchen / KDS):**
   - Màn hình KDS chuyên biệt: `/kitchen`, `/kitchen/queue`.
   - Tiếp nhận món gọi từ khách và nhân viên theo thứ tự thời gian.
   - Cập nhật trạng thái chế biến từng món: `pending` &rarr; `preparing` &rarr; `ready`.
   - Báo món đã nấu xong cho nhân viên phục vụ mang ra bàn.

5. **Quản trị viên (Admin):**
   - Toàn quyền quản trị hệ thống qua giao diện **FOODIES Admin Suite** độc lập.
   - Không bị rối mắt bởi các màn hình POS/Bếp của nhân viên.
   - Tự động điều hướng ngay vào `/admin/dashboard` sau khi đăng nhập tài khoản quản trị.

---

## 🚀 CÁC TÍNH NĂNG ĐÃ HOÀN THIỆN

### 1. Trang Chủ Khách Hàng (Client Front-End)
- **Hero Banner:** Tiêu đề *"Nghệ Thuật Ẩm Thực Đương Đại"*, thanh tìm kiếm bàn và đặt chỗ ưu tiên thời gian thực.
- **Ba Cột Trụ Di Sản:** Giới thiệu nguồn nguyên liệu nhập khẩu đỉnh cao, Bếp trưởng tài hoa quốc tế và chuẩn mực phục vụ 5 sao.
- **Thực Đơn Trứ Danh:** Trưng bày các mỹ vị thượng hạng (Bò Wagyu A5 Miyazaki, Gan ngỗng áp chảo, Tôm hùm Alaska) với nút chọn món/đặt trước tức thì.
- **Dạ Tiệc & Setup Không Gian Độc Bản:** Giới thiệu 4 gói tiệc độc quyền (Kỷ niệm tình yêu, Sinh nhật sang trọng, Tiệc rượu doanh nhân, Phòng VIP gia đình) với hình ảnh gallery trực quan.
- **Đánh Giá & Báo Giới:** Cảm nhận của thực khách thượng lưu, Sommelier và chứng nhận ẩm thực (Michelin Selected).
- **Voucher Khuyến Mãi:** Banner mã ưu đãi đặt chỗ trực tuyến (VD: `FOODIESFIRST15` - Giảm 15% cho lần đầu).

### 2. Quy Trình Đặt Bàn & Pre-Order Ẩm Thực
- Chọn ngày, giờ và số lượng khách; tự động kiểm tra bàn phù hợp sức chứa.
- Kiểm tra trùng lịch đặt bàn trên cùng một bàn trong khung giờ vận hành.
- **Chọn món ăn trước (Pre-order):** Thêm món vào giỏ đặt bàn, lưu giá snapshot tại thời điểm đặt để bảo toàn lịch sử giao dịch.
- Nhập ghi chú cho từng món ăn và ghi chú setup sự kiện đặc biệt.
- **Tính toán chi phí minh bạch:** Tổng tiền = Giá món ăn + Phí phụ thu phòng/bàn VIP (nếu chọn) + Phí gói setup (nếu có) - Khuyến mãi voucher.

### 3. Cổng Thông Tin & Lịch Sử Khách Hàng
- Quản lý thông tin cá nhân: `/account`.
- Quản lý lịch sử đặt bàn: `/account/bookings`, xem chi tiết và hủy đặt bàn hợp lệ.
- Theo dõi đơn hàng thời gian thực: `/account/orders/{id}/status` với thanh tiến trình từng món.
- Xem hóa đơn điện tử: `/account/invoices/{id}`.
- Trung tâm thông báo: `/account/notifications`.

### 4. Trung Tâm Quản Trị Admin (FOODIES Admin Suite)
Hệ thống sử dụng layout riêng biệt `app/Views/layouts/admin.php` với Sidebar 5 nhóm chuyên nghiệp:
- **Nhóm 1: Tổng quan (`/admin/dashboard`):**
  - KPI doanh thu hôm nay, lượt đặt bàn, công suất bàn, số món đã hoàn tất ở bếp KDS.
  - Biểu đồ phân tích doanh thu theo giờ (A La Carte vs Combo Tiệc).
  - Danh sách đơn đặt bàn sắp tới trong ngày.
  - Bảng xếp hạng Top 5 món ăn bán chạy nhất hôm nay.
- **Nhóm 2: Vận hành & Đặt chỗ:**
  - **Quản lý Đặt bàn & Tiếp nhận khách (`/admin/bookings`):**
    - Lọc theo ngày, trạng thái, khu vực.
    - **Mở bàn từ đơn đặt trước (`open-table`):** Khách đến quầy chỉ cần 1 click để nhận bàn và chuyển sang trạng thái đang phục vụ.
    - **Cơ chế đổi bàn do sự cố (`change-table`):** Khi bàn gặp sự cố ngoài ý muốn, hệ thống cho phép chuyển khách sang bàn trống khác một cách an toàn.
    - **Tự động giải phóng bàn trễ hẹn:** Khách đến muộn quá số phút cấu hình (mặc định 30 phút trong cài đặt) sẽ tự động chuyển bàn về trạng thái trống.
  - **Sơ đồ Bàn ăn & Không gian Thực tế (`/admin/tables`):**
    - **Phân tầng trực quan:** Tab Tầng 1 (Sảnh Hoàng Gia, Sân khấu Cello, Bar Sommelier, Sảnh chính, Ban công Terrace) & Tab Tầng 2 (VIP Suites & Hầm rượu Sommelier).
    - **Thao tác 1-Click nhanh:** Chuyển bàn sang Trống, Đang phục vụ, Đang dọn dẹp, Tạm khóa; Đổi bàn sự cố.
    - **Cấu hình Bàn VIP:** Bật/tắt trạng thái VIP, cài đặt giá phụ thu phòng/bàn và nhập mô tả đặc quyền ưu đãi riêng.
    - **Quản lý toàn diện:** Thêm bàn mới, sửa bàn, xóa bàn; Thêm, sửa, xóa khu vực ẩm thực (kiểm tra an toàn nếu có bàn trực thuộc).
  - **Yêu cầu Setup riêng (`/admin/setups`):**
    - Theo dõi danh sách các bàn yêu cầu setup hoa tươi, nến thơm, bánh kem, rượu vang để đội ngũ Decor/Host chuẩn bị trước giờ tiệc.
- **Nhóm 3: Thực đơn & Bếp:**
  - **Quản lý Món ăn (`/admin/menu`):** Danh sách món, chỉ số món còn/hết, công tắc toggle Còn món / Hết món KDS, toggle hiển thị thực đơn, form thêm/sửa món chuẩn Tailwind.
  - **Quản lý Danh mục (`/admin/menu/categories`):** Thêm, sửa, xóa danh mục; kiểm tra ràng buộc không cho xóa danh mục đang chứa món.
  - **Combo & Khuyến mãi (`/admin/combos`):** Quản lý các gói combo tiệc độc bản và tạo mới/quản lý mã Voucher giảm giá.
- **Nhóm 4: Bán hàng & Tài chính:**
  - **Quản lý Hóa đơn (`/admin/invoices`):** Xem danh sách hóa đơn bán hàng, lọc theo ngày/phương thức thanh toán, hoàn tiền hóa đơn.
  - **Doanh thu & Báo cáo (`/admin/reports`):** Biểu đồ Chart.js doanh thu 7 ngày gần nhất, tỷ lệ hoàn thành/hủy đơn, phân tích doanh thu theo khu vực sảnh tiệc và Top 10 món mang lại doanh số cao nhất.
- **Nhóm 5: Hệ thống:**
  - **Nhân viên & Phân quyền (`/admin/users`):** Quản lý tài khoản Admin, Staff, Kitchen, Customer; đổi mật khẩu, phân vai trò và xem nhật ký thao tác (`/admin/users/logs`).
  - **Cấu hình Nhà hàng (`/admin/restaurant`):** Cài đặt thông tin nhà hàng, giờ mở cửa các ngày trong tuần, cấu hình thuế VAT, phí dịch vụ, chính sách đặt cọc và số phút tự động hủy bàn muộn.

### 5. Vận Hành Phục Vụ Tại Bàn (Staff POS)
- Giao diện tối ưu cho nhân viên phục vụ tại bàn: `/staff/pos`.
- Xem nhanh trạng thái toàn bộ bàn ăn, bàn đang có khách dùng bữa kèm thời lượng phút đã ngồi.
- Thêm món mới vào bàn ăn bất kỳ lúc nào, gửi yêu cầu chế biến thẳng đến nhà bếp.
- In hóa đơn tạm tính (Prebill) trước khi thanh toán.

### 6. Màn Hình Điều Phối Bếp KDS (Kitchen Display System)
- Màn hình bếp trực quan: `/kitchen`.
- Phân loại món theo hàng đợi: Món mới chờ nhận (`pending`) &rarr; Đang chế biến (`preparing`) &rarr; Chế biến xong (`ready`).
- Khi bếp bấm hoàn thành, hệ thống gửi thông báo cho nhân viên phục vụ mang ra bàn.

### 7. Hóa Đơn & Thanh Toán
- Lưu trữ snapshot chi tiết món, giá, phụ thu bàn VIP, phí gói setup, giảm giá voucher, phí dịch vụ và thuế VAT.
- Đầy đủ phương thức thanh toán: Tiền mặt, Thẻ tín dụng, Chuyển khoản QR (VietQR / VNPAY).
- Khách hàng có thể tra cứu và xem hóa đơn điện tử hợp lệ bất cứ lúc nào.

---

## 📂 CẤU TRÚC THƯ MỤC DỰ ÁN

```text
DuAn1/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php             # Trang chủ thương hiệu FOODIES
│   │   ├── AuthController.php             # Đăng ký, đăng nhập & điều hướng vai trò
│   │   ├── AccountController.php          # Quản lý hồ sơ cá nhân khách hàng
│   │   ├── MenuController.php             # Xem thực đơn, giỏ hàng & chi tiết món
│   │   ├── BookingController.php          # Đặt bàn, chọn món trước, lịch sử đặt
│   │   ├── OrderController.php            # Nhân viên POS, phục vụ bàn, đơn hàng khách
│   │   ├── KitchenController.php          # Màn hình điều phối bếp KDS
│   │   ├── InvoiceController.php          # Quản lý hóa đơn & thanh toán
│   │   ├── TableController.php            # Sơ đồ bàn cho nhân viên
│   │   ├── NotificationController.php     # Hệ thống thông báo
│   │   ├── AdminDashboardController.php   # Dashboard trung tâm & Báo cáo doanh thu
│   │   ├── BookingAdminController.php     # Quản lý đặt bàn, mở bàn, đổi bàn sự cố
│   │   ├── TableAdminController.php       # Quản lý bàn ăn, khu vực, đặc quyền VIP
│   │   ├── DishAdminController.php        # Quản lý món ăn & kho KDS
│   │   ├── CategoryAdminController.php    # Quản lý danh mục ẩm thực
│   │   ├── PromotionAdminController.php   # Quản lý Combo setup tiệc & Vouchers
│   │   ├── UserAdminController.php        # Quản lý tài khoản & phân quyền
│   │   └── RestaurantAdminController.php  # Cấu hình thông tin & chính sách nhà hàng
│   │
│   ├── Models/
│   │   ├── BaseModel.php                  # Model cha chứa kết nối PDO
│   │   ├── User.php                       # Quản lý người dùng & vai trò
│   │   ├── Category.php                   # Danh mục món ăn
│   │   ├── Dish.php                       # Món ăn & tình trạng phục vụ
│   │   ├── Table.php                      # Bàn ăn, khu vực, phụ thu & VIP
│   │   ├── Booking.php                    # Đặt bàn, giải phóng bàn muộn
│   │   ├── Order.php                      # Đơn gọi món, chi tiết món & tính tiền
│   │   ├── Invoice.php                    # Hóa đơn thanh toán
│   │   ├── ComboSetup.php                 # Gói combo setup tiệc độc bản
│   │   ├── Voucher.php                    # Mã ưu đãi giảm giá
│   │   ├── RestaurantSetting.php          # Cài đặt tham số hệ thống
│   │   ├── ActivityLog.php                # Nhật ký hoạt động quản trị
│   │   └── Notification.php               # Thông báo người dùng
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── admin.php                  # Layout Sidebar độc lập cho Quản trị viên
│   │   │   ├── guest.php                  # Layout sang trọng cho Khách hàng
│   │   │   └── staff.php                  # Layout gọn gàng cho Nhân viên phục vụ/Bếp
│   │   ├── home/                          # Giao diện trang chủ FOODIES
│   │   ├── menu/                          # Thực đơn & chi tiết món
│   │   ├── booking/                       # Form đặt bàn & quản lý đặt bàn
│   │   ├── admin/                         # Các trang quản trị (Dashboard, Bàn, Thực đơn, Danh mục, Combo, Báo cáo...)
│   │   ├── order/                         # Giao diện POS phục vụ bàn
│   │   ├── kitchen/                       # Màn hình bếp KDS
│   │   └── invoice/                       # Hóa đơn thanh toán
│   │
│   └── Core/                              # Thư viện hạt nhân (Router, PDO Database, View, Session, Csrf, Validator...)
│
├── config/
│   ├── database.php                       # Cấu hình kết nối MySQL PDO
│   └── app.php                            # Thiết lập môi trường ứng dụng
│
├── database/
│   ├── schema.sql                         # Cấu trúc toàn bộ bảng cơ sở dữ liệu
│   ├── migrations/                        # Các tệp cập nhật cấu trúc DB bổ sung
│   └── seeders/                           # Dữ liệu mẫu (món ăn, bàn ăn, tài khoản, combo)
│
├── public/
│   ├── index.php                          # Front Controller duy nhất tiếp nhận request
│   ├── .htaccess                          # Định tuyến URL về index.php
│   └── assets/                            # Ảnh món ăn, uploads, CSS, JS
│
├── routes/
│   └── web.php                            # Khai báo tập trung toàn bộ route của hệ thống
│
├── tests/
│   ├── RouterTest.php                     # Kiểm thử định tuyến Route
│   └── ViewsSmokeTest.php                 # Kiểm thử tự động render toàn bộ 38 trang view
│
├── AGENTS.md                              # Bộ quy tắc kiến trúc & tiêu chuẩn dự án
└── README.md                              # Tài liệu dự án
```

---

## 🗄️ CƠ SỞ DỮ LIỆU (DATABASE SCHEMA)

Hệ thống bao gồm các bảng chính:
- **`roles` & `users`**: Quản lý tài khoản, mật khẩu băm, vai trò (admin, staff, kitchen, customer).
- **`dining_areas` & `restaurant_tables`**: Khu vực ẩm thực, bàn ăn, sức chứa, tọa độ sơ đồ, cờ `is_vip`, phụ thu `table_fee`, đặc quyền `vip_privileges`.
- **`categories` & `dishes`**: Danh mục và món ăn, giá niêm yết, thời gian chế biến, trạng thái còn món KDS.
- **`combo_setups` & `vouchers`**: Gói setup tiệc sinh nhật/kỷ niệm và mã giảm giá voucher.
- **`bookings` & `booking_details`**: Lượt đặt bàn, thời gian, số khách, pre-order món ăn, tiền đặt cọc và ước tính chi phí.
- **`orders` & `order_items`**: Đơn gọi món tại bàn, chi tiết món ăn, trạng thái chế biến từng món và thời gian phục vụ.
- **`invoices` & `payments`**: Hóa đơn thanh toán và phương thức giao dịch.
- **`restaurant_settings`**: Cài đặt giờ mở cửa, VAT, phí dịch vụ, số phút tự động hủy bàn muộn (`auto_release_late_minutes`).
- **`activity_logs` & `notifications`**: Lịch sử thao tác quản trị và thông báo cho khách hàng/nhân viên.

---

## 💻 HƯỚNG DẪN CÀI ĐẶT & KHỞI CHẠY

### 1. Yêu cầu môi trường
- PHP 8.1 trở lên (bật extension `pdo`, `pdo_mysql`, `mbstring`, `fileinfo`).
- MySQL 8.0 trở lên.
- XAMPP / WampServer / Docker hoặc PHP CLI built-in server.

### 2. Các bước cài đặt
1. **Clone mã nguồn dự án:**
   ```bash
   git clone <repository_url> DuAn1
   cd DuAn1
   ```
2. **Tạo Database:**
   Tạo cơ sở dữ liệu MySQL mới tên `restaurant_booking` với charset `utf8mb4` và collation `utf8mb4_unicode_ci`.
3. **Import dữ liệu:**
   - Import file [`database/schema.sql`](./database/schema.sql).
   - Chạy các migration bổ sung trong [`database/migrations/`](./database/migrations/).
   - Import dữ liệu mẫu trong [`database/seeders/sample_data.php`](./database/seeders/sample_data.php) (tùy chọn).
4. **Cấu hình môi trường:**
   Tạo tệp `.env` từ `.env.example`:
   ```ini
   APP_NAME="FOODIES Haute Cuisine"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=restaurant_booking
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. **Khởi chạy ứng dụng:**
   Sử dụng PHP Built-in Server:
   ```bash
   php -S localhost:8000 -t public
   ```
   Hoặc cấu hình Document Root trên Apache/XAMPP trỏ thẳng vào thư mục `public/`.
6. **Truy cập ứng dụng:**
   Mở trình duyệt tại: `http://localhost:8000`

---

## 🧪 KIỂM THỬ TỰ ĐỘNG (AUTOMATED TESTING)

Dự án tích hợp bộ kiểm thử tự động toàn diện:
```bash
# 1. Kiểm thử định tuyến toàn bộ Routes
php tests/RouterTest.php

# 2. Kiểm thử khói tự động render toàn bộ 38 trang Views & Layouts
php tests/ViewsSmokeTest.php
```

**Kết quả kiểm thử:**
- `RouterTest.php`: **Passed 100%** (Tất cả route cho Admin, Bàn ăn, Bếp KDS, POS, Đặt bàn đều đăng ký chuẩn xác).
- `ViewsSmokeTest.php`: **Passed 100% (38/38 trang view)** (Đảm bảo không phát sinh lỗi PHP, không vỡ layout, tuân thủ escape dữ liệu chống XSS).
