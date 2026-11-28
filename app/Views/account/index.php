<?php

declare(strict_types=1);

use App\Core\View;

$roleLabels = [
    'admin' => 'Quản trị viên',
    'staff' => 'Nhân viên phục vụ',
    'kitchen' => 'Nhân viên bếp',
    'customer' => 'Thực khách thân thiết',
];

$message = $message ?? '';
$error = $error ?? '';
$csrfToken = $csrfToken ?? '';
$bookingCount = is_countable($bookings ?? null) ? count($bookings) : 0;
?>

<section class="page-heading">
    <p class="eyebrow">MON PROFIL · FOODIES HAUTE CUISINE</p>
    <h1>Thông tin tài khoản</h1>
    <p class="muted">Quản lý hồ sơ cá nhân, lịch sử ẩm thực và thiết lập bảo mật tài khoản thành viên.</p>
</section>

<section class="account-shell">
    <!-- Hero Profile Summary Card -->
    <div class="account-hero-card">
        <div class="account-hero-left">
            <div class="account-avatar">
                <?php if (!empty($currentUser['avatar'])): ?>
                    <img src="<?= View::escape($currentUser['avatar']) ?>" alt="<?= View::escape($currentUser['name']) ?>">
                <?php else: ?>
                    <span><?= mb_strtoupper(mb_substr($currentUser['name'], 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <div class="account-hero-info">
                <div class="account-badge-wrap">
                    <span class="badge-role badge-role-<?= View::escape($currentUser['role']) ?>">
                        ★ <?= View::escape($roleLabels[$currentUser['role']] ?? $currentUser['role']) ?>
                    </span>
                    <span class="account-status-pill">Đang hoạt động</span>
                </div>
                <h2 class="account-display-name"><?= View::escape($currentUser['name']) ?></h2>
                <div class="account-contact-info">
                    <span>✉ <?= View::escape($currentUser['email'] ?? '') ?></span>
                    <span>•</span>
                    <span>☎ <?= View::escape(!empty($currentUser['phone']) ? $currentUser['phone'] : 'Chưa cập nhật SĐT') ?></span>
                </div>
            </div>
        </div>

        <div class="account-hero-actions">
            <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                <a class="button button-small" href="/bookings/new">
                    <span>+ Đặt bàn mới</span>
                </a>
            <?php endif; ?>
            <a class="button button-small button-outline" href="/account/bookings">
                <span>Lịch sử đặt bàn (<?= (int) $bookingCount ?>)</span>
            </a>
            <a class="button button-small button-outline" href="/account/orders">
                <span>Đơn của tôi</span>
            </a>
            <a class="button button-small button-outline" href="/account/notifications">
                <span>Thông báo</span>
            </a>
            <?php if (in_array($currentUser['role'], ['admin', 'staff'], true)): ?>
                <a class="button button-small button-admin" href="/tables">
                    <span>⚙ Trang quản trị</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message === 'profile_updated'): ?>
        <div class="alert alert-success" role="status">
            <strong>Thành công:</strong> Hồ sơ cá nhân của bạn đã được cập nhật thành công!
        </div>
    <?php elseif ($message === 'password_changed'): ?>
        <div class="alert alert-success" role="status">
            <strong>Thành công:</strong> Đổi mật khẩu thành công! Mật khẩu mới đã được mã hóa bảo mật.
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error" role="alert">
            <strong>Lỗi:</strong> <?= View::escape($error) ?>
        </div>
    <?php endif; ?>

    <!-- Two-column Forms Grid -->
    <div class="account-forms-grid">
        <!-- Card 1: Thông Tin Cá Nhân -->
        <article class="account-card">
            <div class="account-card-header">
                <div class="card-header-icon">👤</div>
                <div>
                    <h2>Thông tin cá nhân</h2>
                    <p class="card-subtitle">Cập nhật thông tin định danh và liên hệ của bạn.</p>
                </div>
            </div>

            <form action="/account/profile" method="post" class="account-form">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div class="form-group">
                    <label for="profile-name">Họ và tên <span class="required">*</span></label>
                    <input id="profile-name" type="text" name="name" value="<?= View::escape($currentUser['name']) ?>" required maxlength="150" placeholder="Họ và tên của bạn">
                </div>

                <div class="form-group">
                    <label for="profile-email">Địa chỉ Email</label>
                    <input id="profile-email" type="email" value="<?= View::escape($currentUser['email']) ?>" disabled class="input-disabled">
                    <small class="form-hint">Email dùng làm tài khoản đăng nhập và không thể thay đổi.</small>
                </div>

                <div class="form-group">
                    <label for="profile-phone">Số điện thoại liên hệ</label>
                    <input id="profile-phone" type="tel" name="phone" value="<?= View::escape($currentUser['phone'] ?? '') ?>" placeholder="Ví dụ: 0901234567" maxlength="20">
                    <small class="form-hint">Dùng để nhà hàng gọi xác nhận hoặc liên hệ đón bàn.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button">Lưu thay đổi</button>
                </div>
            </form>
        </article>

        <!-- Card 2: Bảo Mật & Đổi Mật Khẩu -->
        <article class="account-card">
            <div class="account-card-header">
                <div class="card-header-icon">🔒</div>
                <div>
                    <h2>Bảo mật &amp; Đổi mật khẩu</h2>
                    <p class="card-subtitle">Nên sử dụng mật khẩu mạnh để bảo vệ tài khoản.</p>
                </div>
            </div>

            <form action="/account/password" method="post" class="account-form">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div class="form-group">
                    <label for="old-password">Mật khẩu hiện tại <span class="required">*</span></label>
                    <input id="old-password" type="password" name="old_password" required placeholder="Nhập mật khẩu hiện tại">
                </div>

                <div class="form-group">
                    <label for="new-password">Mật khẩu mới <span class="required">*</span></label>
                    <input id="new-password" type="password" name="new_password" minlength="6" required placeholder="Tối thiểu 6 ký tự">
                </div>

                <div class="form-group">
                    <label for="confirm-password">Xác nhận mật khẩu mới <span class="required">*</span></label>
                    <input id="confirm-password" type="password" name="confirm_password" minlength="6" required placeholder="Nhập lại mật khẩu mới">
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-outline">Cập nhật mật khẩu</button>
                </div>
            </form>
        </article>
    </div>
</section>
