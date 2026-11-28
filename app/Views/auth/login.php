<?php
use App\Core\View;
?>
<section class="auth-shell">
    <div class="auth-aside">
        <p class="eyebrow">CHÀO MỪNG ĐẾN VỚI FOODIES</p>
        <h1>Chào mừng<br>trở lại.</h1>
        <p>Đăng nhập để tiếp tục hành trình ẩm thực của bạn.</p>
    </div>
    <div class="auth-card">
        <p class="eyebrow">TÀI KHOẢN KHÁCH HÀNG</p>
        <h2>Đăng nhập</h2>
        <p class="muted">Dùng email và mật khẩu đã đăng ký.</p>
        <?php if ($errors !== []): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="/login" method="post" class="form-stack">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="150" required value="<?= View::escape($old['email'] ?? '') ?>" placeholder="ten@email.com">
            <label for="password">Mật khẩu</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="button button-full" type="submit">Đăng nhập</button>
        </form>
        <p class="form-footnote">Chưa có tài khoản? <a href="/register">Đăng ký khách hàng</a></p>
    </div>
</section>
