<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fff8f5">
    <title><?= \App\Core\View::escape($pageTitle ?? "L'Épicure Gastronomie") ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/" aria-label="L'Épicure - Trang chủ">
            <span class="brand-mark">L'Épicure</span>
            <span class="brand-caption">GASTRONOMIE</span>
        </a>
        <nav class="header-nav" aria-label="Điều hướng chính">
            <a href="/">Trang chủ</a>
            <a href="/menu">Thực đơn</a>
            <a href="/cart">Giỏ chọn món</a>
            <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                <a href="/account/bookings">Lịch sử đặt bàn</a>
                <a class="button button-small" href="/bookings/new">Đặt bàn</a>
            <?php elseif (in_array(($currentUser['role'] ?? ''), ['admin', 'staff'], true)): ?>
                <a href="/admin/bookings">Quản lý đặt bàn</a>
            <?php endif; ?>
            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                <a href="/admin/menu">Quản trị thực đơn</a>
            <?php endif; ?>
            <?php if (!empty($currentUser)): ?>
                <a href="/account">Tài khoản</a>
                <form action="/logout" method="post" class="inline-form">
                    <input type="hidden" name="_csrf_token" value="<?= \App\Core\View::escape(\App\Core\Csrf::token()) ?>">
                    <button class="nav-button" type="submit">Đăng xuất</button>
                </form>
            <?php else: ?>
                <a href="/login">Đăng nhập</a>
                <a class="button button-small" href="/register">Tạo tài khoản</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?= $content ?>
    </main>
    <footer class="site-footer">
        <span class="brand-mark">L'Épicure</span>
        <span>Ẩm thực Pháp đương đại · Trân trọng từng trải nghiệm</span>
    </footer>
</body>
</html>
