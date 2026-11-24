<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#121820">
    <title><?= \App\Core\View::escape($pageTitle ?? "FOODIES — Haute Gastronomie") ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="site-body">
    <!-- Top Global Announcement or Header Bar -->
    <header class="site-header-new">
        <div class="header-container">
            <a class="brand-new" href="/" aria-label="FOODIES - Haute Cuisine">
                <span class="brand-mark-new">FOODIES</span>
                <span class="brand-caption-new">HAUTE CUISINE</span>
            </a>

            <nav class="header-nav-new" aria-label="Điều hướng chính">
                <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/' ? 'is-active' : '' ?>">Trang chủ</a>
                <a href="/menu" class="nav-link">Thực đơn</a>
                <a href="/bookings/new" class="nav-link">Đặt bàn trực tuyến</a>
                <a href="/#combos-setup" class="nav-link">Ưu đãi & Voucher</a>
                <a href="/#combos-setup" class="nav-link">Dạ tiệc độc bản & Không gian</a>
                <a href="/cart" class="nav-link">Giỏ món</a>

                <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                    <a href="/account/bookings" class="nav-link">Lịch sử đặt bàn</a>
                <?php elseif (($currentUser['role'] ?? '') === 'admin'): ?>
                    <a href="/admin/dashboard" class="nav-link font-bold text-gold">Vào trang Quản Trị</a>
                <?php elseif (($currentUser['role'] ?? '') === 'staff'): ?>
                    <a href="/tables" class="nav-link">Sơ đồ bàn</a>
                    <a href="/staff/pos" class="nav-link">Phục vụ</a>
                    <a href="/staff/orders" class="nav-link">Gọi món</a>
                    <a href="/kitchen" class="nav-link">KDS Bếp</a>
                <?php elseif (($currentUser['role'] ?? '') === 'kitchen'): ?>
                    <a href="/kitchen" class="nav-link">Màn hình Bếp</a>
                <?php endif; ?>
            </nav>

            <div class="header-actions-new">
                <a href="/bookings/new" class="btn-book-now">
                    <span>ĐẶT BÀN NGAY</span>
                </a>

                <?php if (!empty($currentUser)): ?>
                    <div class="user-dropdown-wrap">
                        <a href="/account" class="btn-user-profile" title="Tài khoản">
                            <span class="user-initial"><?= mb_substr(\App\Core\View::escape($currentUser['name'] ?? 'U'), 0, 1) ?></span>
                            <span class="user-name-short"><?= \App\Core\View::escape($currentUser['name'] ?? 'Khách') ?></span>
                        </a>
                        <form action="/logout" method="post" class="inline-logout-form">
                            <input type="hidden" name="_csrf_token" value="<?= \App\Core\View::escape(\App\Core\Csrf::token()) ?>">
                            <button type="submit" class="btn-logout" title="Đăng xuất">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn-login-link">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="site-main-content">
        <?= $content ?>
    </main>

    <!-- FOOTER ĐỒNG BỘ THEO MẪU ẢNH -->
    <footer class="site-footer-rich">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Cột 1: Brand & Giới thiệu -->
                <div class="footer-col footer-col-brand">
                    <div class="footer-brand">
                        <span class="footer-brand-title">FOODIES</span>
                        <span class="footer-brand-sub">HAUTE CUISINE</span>
                    </div>
                    <p class="footer-desc">
                        Đỉnh cao nghệ thuật ẩm thực đương đại kết hợp tinh hoa truyền thống thế giới. Foodies mang đến những trải nghiệm dạ tiệc thanh lịch, xứng tầm đẳng cấp người sành ăn.
                    </p>
                    <div class="footer-badge">
                        <span class="footer-badge-dot"></span>
                        <span>MICHELIN SELECTED &bull; GASTRONOMY GUILD</span>
                    </div>
                </div>

                <!-- Cột 2: Giờ phục vụ ẩm thực -->
                <div class="footer-col">
                    <h4 class="footer-heading">GIỜ PHỤC VỤ ẨM THỰC</h4>
                    <ul class="footer-hours-list">
                        <li>
                            <span class="fh-label">Bữa trưa (Luncheon):</span>
                            <span class="fh-time">11:00 - 14:00</span>
                        </li>
                        <li>
                            <span class="fh-label">Bữa tối (Dinner):</span>
                            <span class="fh-time">17:30 - 22:30</span>
                        </li>
                        <li>
                            <span class="fh-label">Phục vụ rượu & Cigar:</span>
                            <span class="fh-time">22:00 - 01:00</span>
                        </li>
                    </ul>
                    <div class="footer-booking-note">
                        <strong>Mở cửa tất cả các ngày trong tuần & Lễ Tết</strong>
                    </div>
                </div>

                <!-- Cột 3: Chính sách & Hỗ trợ -->
                <div class="footer-col">
                    <h4 class="footer-heading">CHÍNH SÁCH & HỖ TRỢ</h4>
                    <ul class="footer-links-list">
                        <li><a href="/bookings/new">Quy định đặt bàn</a></li>
                        <li><a href="/#combos-setup">Hủy bàn & Hoàn tiền</a></li>
                        <li><a href="/#combos-setup">Tiêu chuẩn trang phục VIP</a></li>
                        <li><a href="/">Bảo mật thông tin khách hàng</a></li>
                        <li><a href="/menu">Chính sách dị ứng thực phẩm</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Đặc quyền thành viên -->
                <div class="footer-col">
                    <h4 class="footer-heading">ĐẶC QUYỀN THÀNH VIÊN</h4>
                    <p class="footer-sub-text">
                        Đăng ký nhận thư ngỏ dạ tiệc, lịch thử rượu mới và voucher ưu đãi dành riêng cho quý khách.
                    </p>
                    <form class="footer-newsletter-form" onsubmit="event.preventDefault(); alert('Cảm ơn quý khách đã đăng ký nhận bản tin đặc quyền!');">
                        <div class="fn-input-wrap">
                            <input type="email" placeholder="Nhập email của quý khách..." required>
                            <button type="submit">GỬI</button>
                        </div>
                    </form>
                    <span class="footer-newsletter-note">Cam kết bảo mật tuyệt đối danh tính và thư từ.</span>
                </div>
            </div>

            <div class="footer-bottom-bar">
                <span>&copy; <?= date('Y') ?> FOODIES HAUTE CUISINE. Bản quyền thuộc về nhà hàng.</span>
                <span>Nghệ thuật ẩm thực đương đại &bull; Tinh hoa vị giác</span>
            </div>
        </div>
    </footer>
</body>
</html>
