<?php
use App\Core\View;
?>
<section class="auth-shell">
    <div class="auth-aside">
        <p class="eyebrow">L'EXPÉRIENCE L'ÉPICURE</p>
        <h1>Khởi đầu<br>một trải nghiệm.</h1>
        <p>Tạo tài khoản khách hàng để sẵn sàng sử dụng các dịch vụ trực tuyến.</p>
    </div>
    <div class="auth-card">
        <p class="eyebrow">HỘI VIÊN L'ÉPICURE</p>
        <h2>Tạo tài khoản</h2>
        <p class="muted">Đăng ký dành cho khách hàng. Tài khoản nhân viên do quản trị viên cấp.</p>
        <?php if ($errors !== []): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="/register" method="post" class="form-stack">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <label for="name">Họ và tên</label>
            <input id="name" name="name" type="text" autocomplete="name" maxlength="150" required value="<?= View::escape($old['name'] ?? '') ?>">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="150" required value="<?= View::escape($old['email'] ?? '') ?>">
            <label for="password">Mật khẩu <span class="field-hint">(ít nhất 12 ký tự)</span></label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required>
            <label for="password-confirmation">Nhập lại mật khẩu</label>
            <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required>
            <button class="button button-full" type="submit">Tạo tài khoản</button>
        </form>
        <p class="form-footnote">Đã có tài khoản? <a href="/login">Đăng nhập</a></p>
    </div>
</section>
