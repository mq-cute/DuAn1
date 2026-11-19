<?php

declare(strict_types=1);

use App\Core\View;

$actionLabels = [
    'create_user' => ['label' => 'Tạo tài khoản', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'update_user' => ['label' => 'Sửa tài khoản', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
    'change_password' => ['label' => 'Đổi mật khẩu', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
    'reset_password' => ['label' => 'Admin đặt lại MK', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'confirm_payment' => ['label' => 'Thanh toán & Xuất HĐ', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'refund_invoice' => ['label' => 'Hoàn tiền hóa đơn', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
    'edit_invoice' => ['label' => 'Sửa sai hóa đơn', 'badge' => 'bg-stone-200 text-stone-800 border-stone-300'],
    'update_restaurant_settings' => ['label' => 'Cập nhật thông tin quán', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
    'update_operating_hours' => ['label' => 'Cập nhật giờ mở cửa', 'badge' => 'bg-teal-100 text-teal-800 border-teal-300'],
];
?>

<div class="max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary">BẢO MẬT &amp; KIỂM TOÁN</span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">Tổng nhật ký: <strong><?= (int) $totalLogs ?></strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">Nhật Ký Hoạt Động Hệ Thống</h1>
            <p class="text-sm text-on-surface-variant">Theo dõi toàn diện các thao tác thay đổi dữ liệu, phân quyền, hoàn tiền và cấu hình nhà hàng.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/users" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 bg-white text-on-surface text-xs font-semibold hover:border-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Quay lại Quản lý tài khoản</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-outline-variant/30 shadow-sm">
        <form action="/admin/users/logs" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filter-action" class="block text-xs font-bold text-on-surface uppercase mb-1">Hành động</label>
                <select id="filter-action" name="action" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    <option value="">Tất cả hành động</option>
                    <?php foreach ($actionLabels as $aKey => $aData): ?>
                        <option value="<?= View::escape($aKey) ?>" <?= $action === $aKey ? 'selected' : '' ?>>
                            <?= View::escape($aData['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="filter-target" class="block text-xs font-bold text-on-surface uppercase mb-1">Đối tượng tác động</label>
                <select id="filter-target" name="target_type" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    <option value="">Tất cả đối tượng</option>
                    <option value="users" <?= $targetType === 'users' ? 'selected' : '' ?>>Người dùng (users)</option>
                    <option value="invoices" <?= $targetType === 'invoices' ? 'selected' : '' ?>>Hóa đơn (invoices)</option>
                    <option value="restaurant_settings" <?= $targetType === 'restaurant_settings' ? 'selected' : '' ?>>Cài đặt quán (settings)</option>
                    <option value="restaurant_operating_hours" <?= $targetType === 'restaurant_operating_hours' ? 'selected' : '' ?>>Giờ mở cửa (hours)</option>
                </select>
            </div>

            <div>
                <label for="filter-date" class="block text-xs font-bold text-on-surface uppercase mb-1">Ngày ghi nhận</label>
                <input id="filter-date" type="date" name="date" value="<?= View::escape($date) ?>" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-inverse-surface text-inverse-on-surface rounded-xl text-xs font-bold hover:opacity-90 transition flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    <span>Lọc nhật ký</span>
                </button>
                <a href="/admin/users/logs" class="py-2 px-3 border border-outline-variant/40 rounded-xl text-xs font-semibold hover:bg-stone-50 transition" title="Đặt lại">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-outline-variant/30 shadow-sm overflow-hidden">
        <?php if ($logs === []): ?>
            <div class="py-16 text-center space-y-3">
                <span class="material-symbols-outlined text-5xl text-outline-variant">history</span>
                <p class="text-base font-semibold text-on-surface">Chưa có nhật ký hoạt động nào phù hợp</p>
                <p class="text-xs text-on-surface-variant">Thử điều chỉnh lại điều kiện lọc hoặc thực hiện các thao tác quản trị.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/60 text-xs font-bold uppercase tracking-wider text-outline border-b border-outline-variant/20">
                            <th class="py-4 px-6">Thời Gian</th>
                            <th class="py-4 px-4">Người Thực Hiện</th>
                            <th class="py-4 px-4">Hành Động</th>
                            <th class="py-4 px-4">Đối Tượng</th>
                            <th class="py-4 px-4">Chi Tiết Thay Đổi</th>
                            <th class="py-4 px-6">Địa Chỉ IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20 text-xs">
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $actionInfo = $actionLabels[$log['action']] ?? [
                                'label' => $log['action'],
                                'badge' => 'bg-stone-100 text-stone-800 border-stone-200'
                            ];
                            $details = !empty($log['details']) ? json_decode($log['details'], true) : null;
                            ?>
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="font-bold text-on-surface"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                    <div class="text-[11px] text-stone-400"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <?php if (!empty($log['user_name'])): ?>
                                        <div class="font-bold text-on-surface"><?= View::escape($log['user_name']) ?></div>
                                        <div class="text-[11px] text-stone-400"><?= View::escape($log['user_email']) ?> (<?= View::escape($log['user_role'] ?? '') ?>)</div>
                                    <?php else: ?>
                                        <span class="text-stone-400 italic">Hệ thống / Vô danh</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $actionInfo['badge'] ?>">
                                        <?= View::escape($actionInfo['label']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-on-surface">
                                    <?= View::escape($log['target_type']) ?>
                                    <?php if (!empty($log['target_id'])): ?>
                                        <span class="text-stone-400 text-[11px]">#<?= (int) $log['target_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 max-w-md">
                                    <?php if (is_array($details)): ?>
                                        <pre class="bg-stone-50 p-2 rounded-xl text-[10px] font-mono text-stone-700 border border-stone-200 max-h-24 overflow-y-auto whitespace-pre-wrap"><?= View::escape(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                    <?php else: ?>
                                        <span class="text-stone-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-stone-500 font-mono text-[11px]">
                                    <?= View::escape($log['ip_address'] ?: '127.0.0.1') ?>
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
                        Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= $totalLogs ?> bản ghi)
                    </span>
                    <div class="flex items-center space-x-1.5">
                        <?php if ($page > 1): ?>
                            <a href="/admin/users/logs?page=<?= $page - 1 ?>&action=<?= urlencode($action) ?>&target_type=<?= urlencode($targetType) ?>&date=<?= urlencode($date) ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-xs font-semibold hover:bg-stone-50">
                                Trước
                            </a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === 1 || $p === $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                <a href="/admin/users/logs?page=<?= $p ?>&action=<?= urlencode($action) ?>&target_type=<?= urlencode($targetType) ?>&date=<?= urlencode($date) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $p === $page ? 'bg-primary text-on-primary' : 'border border-outline-variant/40 hover:bg-stone-50' ?>">
                                    <?= $p ?>
                                </a>
                            <?php elseif ($p === $page - 3 || $p === $page + 3): ?>
                                <span class="px-2 text-stone-400">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="/admin/users/logs?page=<?= $page + 1 ?>&action=<?= urlencode($action) ?>&target_type=<?= urlencode($targetType) ?>&date=<?= urlencode($date) ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-xs font-semibold hover:bg-stone-50">
                                Tiếp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
