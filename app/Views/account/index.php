<?php
use App\Core\View;
?>
<section class="account-panel">
    <p class="eyebrow">TÀI KHOẢN</p>
    <h1>Xin chào, <?= View::escape($currentUser['name']) ?></h1>
    <p>Đăng nhập thành công với vai trò <strong><?= View::escape($currentUser['role']) ?></strong>.</p>
    <p class="muted">Các mục lịch sử đặt bàn, thực đơn và quản lý sẽ được kết nối khi những giai đoạn tiếp theo được triển khai.</p>
    <a class="button button-outline" href="/">Về trang chủ</a>
</section>
