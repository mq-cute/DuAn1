<?php

declare(strict_types=1);

use App\Core\View;

$roleLabels = [
    'admin' => 'Quản trị viên',
    'staff' => 'Nhân viên phục vụ',
    'kitchen' => 'Nhân viên bếp',
    'customer' => 'Thực khách',
];

$message = $message ?? '';
$error = $error ?? '';
$csrfToken = $csrfToken ?? '';
?>

<div class="max-w-5xl w-full mx-auto p-4 md:p-8 space-y-8">
    <!-- Profile Card Header -->
    <div class="bg-surface rounded-3xl p-6 sm:p-10 border border-outline-variant/30 shadow-sm flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-center gap-6 text-center sm:text-left">
            <div class="w-20 h-20 rounded-full bg-primary/10 text-primary border-2 border-primary/20 flex items-center justify-center text-3xl font-bold font-headline overflow-hidden">
                <?php if (!empty($currentUser['avatar'])): ?>
                    <img src="<?= View::escape($currentUser['avatar']) ?>" alt="<?= View::escape($currentUser['name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($currentUser['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase bg-tertiary-fixed text-on-tertiary-fixed border border-outline-variant/30">
                    <span class="material-symbols-outlined text-[14px]">verified</span>
                    <span><?= View::escape($roleLabels[$currentUser['role']] ?? $currentUser['role']) ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface"><?= View::escape($currentUser['name']) ?></h1>
                <p class="text-xs text-on-surface-variant"><?= View::escape($currentUser['email'] ?? '') ?> • <?= View::escape(!empty($currentUser['phone']) ? $currentUser['phone'] : 'Chưa cập nhật SĐT') ?></p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="/account/bookings" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 text-on-surface text-xs font-semibold hover:border-primary hover:text-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                <span>Lịch sử đặt bàn</span>
            </a>
            <?php if (in_array($currentUser['role'], ['admin', 'staff'], true)): ?>
                <a href="/staff/pos" class="px-4 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                    <span>Vận hành bàn</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message === 'profile_updated'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Hồ sơ cá nhân đã được cập nhật thành công!</span>
        </div>
    <?php elseif ($message === 'password_changed'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Đổi mật khẩu thành công! Mật khẩu mới đã được bảo mật an toàn.</span>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span><?= View::escape($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Two-column Forms Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Form Cập Nhật Thông Tin -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/30 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                <span class="material-symbols-outlined text-primary text-[22px]">person_edit</span>
                <h2 class="text-base font-headline font-bold">Thông Tin Cá Nhân</h2>
            </div>

            <form action="/account/profile" method="post" class="space-y-4 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div>
                    <label for="profile-name" class="block font-bold text-on-surface uppercase mb-1">Họ và tên *</label>
                    <input id="profile-name" type="text" name="name" value="<?= View::escape($currentUser['name']) ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary font-semibold">
                </div>

                <div>
                    <label for="profile-email" class="block font-bold text-on-surface uppercase mb-1">Địa chỉ Email (Cố định)</label>
                    <input id="profile-email" type="email" value="<?= View::escape($currentUser['email']) ?>" disabled class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/20 bg-stone-100 text-stone-500 cursor-not-allowed">
                    <span class="text-[10px] text-stone-400">Email dùng để đăng nhập và nhận thông báo xác nhận đặt chỗ.</span>
                </div>

                <div>
                    <label for="profile-phone" class="block font-bold text-on-surface uppercase mb-1">Số điện thoại liên hệ</label>
                    <input id="profile-phone" type="text" name="phone" value="<?= View::escape($currentUser['phone'] ?? '') ?>" placeholder="0901234567" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>

                <div class="pt-3 border-t border-outline-variant/30 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-inverse-surface text-inverse-on-surface font-bold rounded-xl hover:opacity-90 transition">
                        Lưu thông tin
                    </button>
                </div>
            </form>
        </section>

        <!-- Form Đổi Mật Khẩu Cá Nhân -->
        <section class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/30 shadow-sm space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                <span class="material-symbols-outlined text-primary text-[22px]">lock</span>
                <h2 class="text-base font-headline font-bold">Đổi Mật Khẩu Bảo Mật</h2>
            </div>

            <form action="/account/password" method="post" class="space-y-4 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div>
                    <label for="old-password" class="block font-bold text-on-surface uppercase mb-1">Mật khẩu hiện tại *</label>
                    <input id="old-password" type="password" name="old_password" required placeholder="••••••••" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label for="new-password" class="block font-bold text-on-surface uppercase mb-1">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                    <input id="new-password" type="password" name="new_password" minlength="6" required placeholder="••••••••" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label for="confirm-password" class="block font-bold text-on-surface uppercase mb-1">Xác nhận mật khẩu mới *</label>
                    <input id="confirm-password" type="password" name="confirm_password" minlength="6" required placeholder="••••••••" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>

                <div class="pt-3 border-t border-outline-variant/30 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary font-bold rounded-xl hover:bg-primary-container transition">
                        Cập nhật mật khẩu
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
