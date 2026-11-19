<?php

declare(strict_types=1);

use App\Core\View;

$statusConfig = [
    'draft' => ['label' => 'Bản nháp', 'badge' => 'bg-stone-100 text-stone-700 border-stone-200'],
    'issued' => ['label' => 'Đã phát hành', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
    'paid' => ['label' => 'Đã thanh toán', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'partially_refunded' => ['label' => 'Hoàn tiền 1 phần', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'refunded' => ['label' => 'Đã hoàn tiền', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
    'cancelled' => ['label' => 'Đã hủy', 'badge' => 'bg-red-100 text-red-800 border-red-200'],
];

$methodConfig = [
    'cash' => 'Tiền mặt',
    'card' => 'Thẻ ngân hàng',
    'transfer' => 'Chuyển khoản',
    'ewallet' => 'Ví điện tử',
];
?>

<div class="max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary">TÀI CHÍNH &amp; HÓA ĐƠN</span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">Tổng hóa đơn: <strong><?= (int) $totalCount ?></strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">Quản Lý Hóa Đơn &amp; Hoàn Tiền</h1>
            <p class="text-sm text-on-surface-variant">Tra cứu toàn diện các hóa đơn bán hàng, lịch sử hoàn tiền và điều chỉnh sai sót.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/staff/pos" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                <span>Thu ngân POS</span>
            </a>
            <a href="/staff/orders" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 bg-white text-on-surface text-xs font-semibold hover:border-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                <span>Quản lý gọi món</span>
            </a>
        </div>
    </div>

    <!-- Thông báo kết quả -->
    <?php if ($message === 'payment_confirmed'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Xác nhận thanh toán và phát hành hóa đơn thành công.</span>
        </div>
    <?php elseif ($message === 'refund_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Ghi nhận hoàn tiền cho hóa đơn thành công.</span>
        </div>
    <?php elseif ($message === 'edit_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Chỉnh sửa thông tin hóa đơn thành công và đã lưu vết lịch sử.</span>
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
        <form action="/admin/invoices" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <label for="filter-search" class="block text-xs font-bold text-on-surface uppercase mb-1">Tìm kiếm</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-[18px]">search</span>
                    <input id="filter-search" type="text" name="search" value="<?= View::escape($search) ?>" placeholder="Mã HĐ, Tên khách, Bàn..." class="w-full text-xs py-2 pl-9 pr-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>
            </div>

            <div>
                <label for="filter-date-from" class="block text-xs font-bold text-on-surface uppercase mb-1">Từ ngày</label>
                <input id="filter-date-from" type="date" name="date_from" value="<?= View::escape($dateFrom) ?>" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="filter-date-to" class="block text-xs font-bold text-on-surface uppercase mb-1">Đến ngày</label>
                <input id="filter-date-to" type="date" name="date_to" value="<?= View::escape($dateTo) ?>" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="filter-status" class="block text-xs font-bold text-on-surface uppercase mb-1">Trạng thái</label>
                <select id="filter-status" name="status" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statuses as $stKey => $stLabel): ?>
                        <option value="<?= View::escape($stKey) ?>" <?= $status === $stKey ? 'selected' : '' ?>>
                            <?= View::escape($stLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-inverse-surface text-inverse-on-surface rounded-xl text-xs font-bold hover:opacity-90 transition flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    <span>Lọc dữ liệu</span>
                </button>
                <a href="/admin/invoices" class="py-2 px-3 border border-outline-variant/40 rounded-xl text-xs font-semibold hover:bg-stone-50 transition" title="Đặt lại bộ lọc">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-3xl border border-outline-variant/30 shadow-sm overflow-hidden">
        <?php if ($invoices === []): ?>
            <div class="py-16 text-center space-y-3">
                <span class="material-symbols-outlined text-5xl text-outline-variant">receipt</span>
                <p class="text-base font-semibold text-on-surface">Không tìm thấy hóa đơn nào phù hợp</p>
                <p class="text-xs text-on-surface-variant">Thử điều chỉnh điều kiện lọc hoặc thanh toán đơn hàng mới.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/60 text-xs font-bold uppercase tracking-wider text-outline border-b border-outline-variant/20">
                            <th class="py-4 px-6">Mã Hóa Đơn</th>
                            <th class="py-4 px-4">Đơn Hàng &amp; Bàn</th>
                            <th class="py-4 px-4">Khách Hàng</th>
                            <th class="py-4 px-4">Ngày Phát Hành</th>
                            <th class="py-4 px-4 text-right">Tổng Tiền</th>
                            <th class="py-4 px-4 text-right">Đã Hoàn</th>
                            <th class="py-4 px-4">Phương Thức</th>
                            <th class="py-4 px-4">Trạng Thái</th>
                            <th class="py-4 px-6 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20 text-xs">
                        <?php foreach ($invoices as $inv): ?>
                            <?php
                            $stInfo = $statusConfig[$inv['status']] ?? ['label' => $inv['status'], 'badge' => 'bg-stone-100 text-stone-700'];
                            $pmLabel = $methodConfig[$inv['payment_method']] ?? ($inv['payment_method'] ?: 'Chưa chọn');
                            $totalRefunded = (float) ($inv['total_refunded'] ?? 0);
                            ?>
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="py-4 px-6 font-bold text-primary">
                                    <a href="/invoices/<?= (int) $inv['id'] ?>" class="hover:underline flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]">receipt</span>
                                        <span><?= View::escape($inv['invoice_number']) ?></span>
                                    </a>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-semibold text-on-surface">#ORD-<?= sprintf('%04d', (int) $inv['order_id']) ?></span>
                                    <div class="text-[11px] text-on-surface-variant">
                                        <?= View::escape($inv['table_code'] ?? 'Bàn') ?> • <?= View::escape($inv['area_name'] ?? '') ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-medium text-on-surface">
                                    <?= View::escape($inv['customer_name'] ?: 'Khách tại bàn') ?>
                                </td>
                                <td class="py-4 px-4 text-on-surface-variant">
                                    <div><?= date('d/m/Y', strtotime($inv['issued_at'])) ?></div>
                                    <div class="text-[11px] text-stone-400"><?= date('H:i:s', strtotime($inv['issued_at'])) ?></div>
                                </td>
                                <td class="py-4 px-4 text-right font-bold text-on-surface">
                                    <?= number_format((float) $inv['total'], 0, ',', '.') ?> đ
                                </td>
                                <td class="py-4 px-4 text-right font-medium <?= $totalRefunded > 0 ? 'text-purple-700' : 'text-stone-400' ?>">
                                    <?= $totalRefunded > 0 ? number_format($totalRefunded, 0, ',', '.') . ' đ' : '—' ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 text-stone-700 font-medium">
                                        <span class="material-symbols-outlined text-[16px] text-stone-500">payments</span>
                                        <?= View::escape($pmLabel) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $stInfo['badge'] ?>">
                                        <?= View::escape($stInfo['label']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/invoices/<?= (int) $inv['id'] ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-on-surface hover:border-primary hover:text-primary transition flex items-center gap-1 font-semibold" title="Xem chi tiết & Hoàn tiền">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                            <span>Chi tiết</span>
                                        </a>
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
                        Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= $totalCount ?> hóa đơn)
                    </span>
                    <div class="flex items-center space-x-1.5">
                        <?php if ($page > 1): ?>
                            <a href="/admin/invoices?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&status=<?= urlencode($status) ?>&payment_method=<?= urlencode($paymentMethod) ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-xs font-semibold hover:bg-stone-50">
                                Trước
                            </a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === 1 || $p === $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                <a href="/admin/invoices?page=<?= $p ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&status=<?= urlencode($status) ?>&payment_method=<?= urlencode($paymentMethod) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $p === $page ? 'bg-primary text-on-primary' : 'border border-outline-variant/40 hover:bg-stone-50' ?>">
                                    <?= $p ?>
                                </a>
                            <?php elseif ($p === $page - 3 || $p === $page + 3): ?>
                                <span class="px-2 text-stone-400">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="/admin/invoices?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&status=<?= urlencode($status) ?>&payment_method=<?= urlencode($paymentMethod) ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant/40 text-xs font-semibold hover:bg-stone-50">
                                Tiếp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
