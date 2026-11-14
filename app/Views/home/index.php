<?php
use App\Core\View;
?>
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">MICHELIN SELECTED · ẨM THỰC PHÁP & Á ĐÔNG</p>
        <h1>Vũ khúc ẩm thực<br><em>đương đại</em></h1>
        <p class="lead">Một trải nghiệm ẩm thực tinh tế, được chăm chút từ nguyên liệu đến khoảnh khắc bạn ngồi vào bàn.</p>
        <div class="hero-actions">
            <a class="button" href="/menu">Khám phá thực đơn</a>
            <a class="button button-outline" href="/bookings/new">Đặt bàn</a>
            <?php if (empty($currentUser)): ?>
            <a class="button" href="/register">Tạo tài khoản khách hàng</a>
            <a class="button button-outline" href="/login">Đăng nhập</a>
            <?php endif; ?>
        </div>
        <?php if (!empty($currentUser)): ?>
            <p class="welcome-note">Xin chào, <?= View::escape($currentUser['name']) ?>.</p>
        <?php endif; ?>
    </div>
    <div class="hero-art" role="img" aria-label="Không gian nhà hàng ấm cúng">
        <div class="art-caption"><span>THE ART OF DINING</span><strong>Thưởng thức trọn vẹn.</strong></div>
    </div>
</section>
<section class="intro">
    <p class="eyebrow">L'EXPÉRIENCE</p>
    <h2>Một buổi tối đáng nhớ<br> bắt đầu từ đây</h2>
    <p>Khám phá thực đơn được cập nhật trực tiếp từ nhà hàng. Những món bạn chọn sẽ được giữ trong giỏ phiên này để chuẩn bị cho luồng đặt bàn.</p>
</section>
