<?php

declare(strict_types=1);

use App\Core\View;

$roleBadges = [
    'admin' => 'bg-red-100 text-red-800 border-red-200',
    'staff' => 'bg-blue-100 text-blue-800 border-blue-200',
    'kitchen' => 'bg-amber-100 text-amber-800 border-amber-200',
    'customer' => 'bg-stone-100 text-stone-800 border-stone-200',
];

$statusBadges = [
    'active' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'inactive' => 'bg-stone-200 text-stone-700 border-stone-300',
];
?>

<div class="max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary">QUẢN TRỊ VIÊN</span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">Tổng tài khoản: <strong><?= (int) $totalUsers ?></strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">Quản Lý Tài Khoản &amp; Phân Quyền</h1>
            <p class="text-sm text-on-surface-variant">Quản lý người dùng, phân cấp vai trò (Admin, Staff, Kitchen, Customer), bảo vệ tài khoản.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/users/logs" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 bg-white text-on-surface text-xs font-semibold hover:border-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">history</span>
                <span>Nhật ký hoạt động</span>
            </a>
            <button type="button" onclick="document.getElementById('createUserModal').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>Thêm tài khoản mới</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message === 'create_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Khởi tạo tài khoản người dùng mới thành công.</span>
        </div>
    <?php elseif ($message === 'update_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Cập nhật thông tin và vai trò tài khoản thành công.</span>
        </div>
    <?php elseif ($message === 'reset_password_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Đặt lại mật khẩu cho tài khoản thành công.</span>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span><?= View::escape($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-outline-variant/30 shadow-sm">
        <form action="/admin/users" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filter-search" class="block text-xs font-bold text-on-surface uppercase mb-1">Tìm kiếm</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-[18px]">search</span>
                    <input id="filter-search" type="text" name="search" value="<?= View::escape($search) ?>" placeholder="Tên, Email, Số điện thoại..." class="w-full text-xs py-2 pl-9 pr-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>
            </div>

            <div>
                <label for="filter-role" class="block text-xs font-bold text-on-surface uppercase mb-1">Vai trò</label>
                <select id="filter-role" name="role" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    <option value="">Tất cả vai trò</option>
                    <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                        <option value="<?= View::escape($rKey) ?>" <?= $selectedRole === $rKey ? 'selected' : '' ?>>
                            <?= View::escape($rLabel) ?> (<?= View::escape($rKey) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="filter-status" class="block text-xs font-bold text-on-surface uppercase mb-1">Trạng thái</label>
                <select id="filter-status" name="status" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statusLabels as $sKey => $sLabel): ?>
                        <option value="<?= View::escape($sKey) ?>" <?= $selectedStatus === $sKey ? 'selected' : '' ?>>
                            <?= View::escape($sLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-inverse-surface text-inverse-on-surface rounded-xl text-xs font-bold hover:opacity-90 transition flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    <span>Lọc danh sách</span>
                </button>
                <a href="/admin/users" class="py-2 px-3 border border-outline-variant/40 rounded-xl text-xs font-semibold hover:bg-stone-50 transition" title="Đặt lại bộ lọc">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-outline-variant/30 shadow-sm overflow-hidden">
        <?php if ($users === []): ?>
            <div class="py-16 text-center space-y-3">
                <span class="material-symbols-outlined text-5xl text-outline-variant">person_off</span>
                <p class="text-base font-semibold text-on-surface">Không tìm thấy tài khoản nào phù hợp</p>
                <p class="text-xs text-on-surface-variant">Thử kiểm tra lại từ khóa tìm kiếm hoặc điều kiện lọc.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/60 text-xs font-bold uppercase tracking-wider text-outline border-b border-outline-variant/20">
                            <th class="py-4 px-6">Họ Tên &amp; Avatar</th>
                            <th class="py-4 px-4">Email</th>
                            <th class="py-4 px-4">Số Điện Thoại</th>
                            <th class="py-4 px-4">Vai Trò</th>
                            <th class="py-4 px-4">Trạng Thái</th>
                            <th class="py-4 px-4">Ngày Đăng Ký</th>
                            <th class="py-4 px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20 text-xs">
                        <?php foreach ($users as $u): ?>
                            <?php
                            $roleBadge = $roleBadges[$u['role']] ?? 'bg-stone-100 text-stone-800 border-stone-200';
                            $statusBadge = $statusBadges[$u['status']] ?? 'bg-stone-100 text-stone-800 border-stone-200';
                            $roleName = $roleLabels[$u['role']] ?? $u['role'];
                            $statusName = $statusLabels[$u['status']] ?? $u['status'];
                            ?>
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-surface-container-high text-primary flex items-center justify-center font-bold text-sm border border-outline-variant/30 overflow-hidden">
                                            <?php if (!empty($u['avatar'])): ?>
                                                <img src="<?= View::escape($u['avatar']) ?>" alt="<?= View::escape($u['name']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?= mb_strtoupper(mb_substr($u['name'], 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-on-surface text-sm">
                                                <?= View::escape($u['name']) ?>
                                                <?php if ((int) $u['id'] === (int) $currentUser['id']): ?>
                                                    <span class="text-[10px] text-stone-400 font-normal ml-1">(Bạn)</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-[11px] text-stone-400">ID: #<?= sprintf('%04d', (int) $u['id']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-medium text-on-surface">
                                    <?= View::escape($u['email']) ?>
                                </td>
                                <td class="py-4 px-4 text-on-surface-variant font-medium">
                                    <?= View::escape($u['phone'] ?: '—') ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $roleBadge ?>">
                                        <?= View::escape($roleName) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $statusBadge ?>">
                                        <?= View::escape($statusName) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-on-surface-variant">
                                    <div><?= date('d/m/Y', strtotime($u['created_at'])) ?></div>
                                    <div class="text-[11px] text-stone-400"><?= date('H:i', strtotime($u['created_at'])) ?></div>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-on-surface hover:border-primary hover:text-primary transition flex items-center gap-1 font-semibold" title="Chỉnh sửa thông tin">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                            <span>Sửa</span>
                                        </button>
                                        <button type="button" onclick="openResetPasswordModal(<?= (int) $u['id'] ?>, '<?= View::escape($u['email']) ?>')" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-on-surface hover:border-primary hover:text-primary transition flex items-center gap-1 font-semibold" title="Đặt lại mật khẩu">
                                            <span class="material-symbols-outlined text-[16px]">lock_reset</span>
                                            <span>Mật khẩu</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="p-4 border-t border-outline-variant/20 flex items-center justify-between">
                    <span class="text-xs text-on-surface-variant">
                        Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= $totalUsers ?> tài khoản)
                    </span>
                    <div class="flex items-center space-x-1.5">
                        <?php if ($page > 1): ?>
                            <a href="/admin/users?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($selectedRole) ?>&status=<?= urlencode($selectedStatus) ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-xs font-semibold hover:bg-stone-50">
                                Trước
                            </a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === 1 || $p === $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                <a href="/admin/users?page=<?= $p ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($selectedRole) ?>&status=<?= urlencode($selectedStatus) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $p === $page ? 'bg-primary text-on-primary' : 'border border-outline-variant/40 hover:bg-stone-50' ?>">
                                    <?= $p ?>
                                </a>
                            <?php elseif ($p === $page - 3 || $p === $page + 3): ?>
                                <span class="px-2 text-stone-400">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="/admin/users?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($selectedRole) ?>&status=<?= urlencode($selectedStatus) ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-xs font-semibold hover:bg-stone-50">
                                Tiếp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Thêm Tài Khoản Mới -->
<div id="createUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">person_add</span>
                <h3 class="text-base font-bold font-headline text-on-surface">Tạo Tài Khoản Mới</h3>
            </div>
            <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="p-1 rounded-lg text-stone-400 hover:text-stone-700">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form action="/admin/users" method="post" class="space-y-4 text-xs">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

            <div>
                <label for="create-name" class="block font-bold text-on-surface uppercase mb-1">Họ và tên *</label>
                <input id="create-name" type="text" name="name" required placeholder="Nguyễn Văn A" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="create-email" class="block font-bold text-on-surface uppercase mb-1">Địa chỉ Email *</label>
                <input id="create-email" type="email" name="email" required placeholder="user@lepicure.com" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="create-phone" class="block font-bold text-on-surface uppercase mb-1">Số điện thoại</label>
                <input id="create-phone" type="text" name="phone" placeholder="0901234567" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="create-role" class="block font-bold text-on-surface uppercase mb-1">Vai trò *</label>
                    <select id="create-role" name="role" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                        <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                            <option value="<?= View::escape($rKey) ?>"><?= View::escape($rLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="create-status" class="block font-bold text-on-surface uppercase mb-1">Trạng thái *</label>
                    <select id="create-status" name="status" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Tạm khóa</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="create-password" class="block font-bold text-on-surface uppercase mb-1">Mật khẩu khởi tạo * (tối thiểu 6 ký tự)</label>
                <input id="create-password" type="password" name="password" minlength="6" required placeholder="••••••••" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-outline-variant/30">
                <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="px-4 py-2 border border-outline-variant/40 rounded-xl font-semibold hover:bg-stone-50">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary-container transition">
                    Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Chỉnh Sửa Tài Khoản -->
<div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">manage_accounts</span>
                <h3 class="text-base font-bold font-headline text-on-surface">Chỉnh Sửa Tài Khoản</h3>
            </div>
            <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="p-1 rounded-lg text-stone-400 hover:text-stone-700">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="editUserForm" action="" method="post" class="space-y-4 text-xs">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

            <div>
                <label for="edit-name" class="block font-bold text-on-surface uppercase mb-1">Họ và tên *</label>
                <input id="edit-name" type="text" name="name" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="edit-email" class="block font-bold text-on-surface uppercase mb-1">Địa chỉ Email *</label>
                <input id="edit-email" type="email" name="email" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="edit-phone" class="block font-bold text-on-surface uppercase mb-1">Số điện thoại</label>
                <input id="edit-phone" type="text" name="phone" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit-role" class="block font-bold text-on-surface uppercase mb-1">Vai trò *</label>
                    <select id="edit-role" name="role" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                        <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                            <option value="<?= View::escape($rKey) ?>"><?= View::escape($rLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="edit-status" class="block font-bold text-on-surface uppercase mb-1">Trạng thái *</label>
                    <select id="edit-status" name="status" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Tạm khóa</option>
                    </select>
                </div>
            </div>

            <p class="text-[11px] text-stone-500 italic">
                Lưu ý: Hệ thống tự động bảo vệ Admin cuối cùng, không thể khóa hoặc hạ quyền nếu chỉ còn 1 Quản trị viên.
            </p>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-outline-variant/30">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-4 py-2 border border-outline-variant/40 rounded-xl font-semibold hover:bg-stone-50">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary-container transition">
                    Lưu cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Đặt Lại Mật Khẩu -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">lock_reset</span>
                <h3 class="text-base font-bold font-headline text-on-surface">Đặt Lại Mật Khẩu</h3>
            </div>
            <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')" class="p-1 rounded-lg text-stone-400 hover:text-stone-700">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="resetPasswordForm" action="" method="post" class="space-y-4 text-xs">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

            <div class="p-3 rounded-xl bg-stone-50 border border-stone-200">
                <p class="text-stone-600">Đặt lại mật khẩu cho tài khoản: <strong id="reset-target-email" class="text-on-surface"></strong></p>
            </div>

            <div>
                <label for="reset-new-password" class="block font-bold text-on-surface uppercase mb-1">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                <input id="reset-new-password" type="password" name="new_password" minlength="6" required placeholder="Nhập mật khẩu mới..." class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-outline-variant/30">
                <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')" class="px-4 py-2 border border-outline-variant/40 rounded-xl font-semibold hover:bg-stone-50">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary-container transition">
                    Cập nhật mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('editUserForm').action = '/admin/users/' + user.id;
    document.getElementById('edit-name').value = user.name || '';
    document.getElementById('edit-email').value = user.email || '';
    document.getElementById('edit-phone').value = user.phone || '';
    document.getElementById('edit-role').value = user.role || 'customer';
    document.getElementById('edit-status').value = user.status || 'active';
    document.getElementById('editUserModal').classList.remove('hidden');
}

function openResetPasswordModal(userId, email) {
    document.getElementById('resetPasswordForm').action = '/admin/users/' + userId + '/reset-password';
    document.getElementById('reset-target-email').textContent = email;
    document.getElementById('reset-new-password').value = '';
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}
</script>
