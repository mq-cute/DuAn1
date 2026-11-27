<?php

declare(strict_types=1);

use App\Core\View;
use App\Models\Order;

$statusConfig = [
    'pending' => ['label' => 'Chờ tiếp nhận', 'badge' => 'bg-stone-100 text-stone-800 border-stone-200'],
    'confirmed' => ['label' => 'Đã tiếp nhận', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
    'preparing' => ['label' => 'Đang chế biến', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200'],
    'ready' => ['label' => 'Sẵn sàng ra món', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
    'served' => ['label' => 'Đã lên bàn', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
    'completed' => ['label' => 'Hoàn tất', 'badge' => 'bg-stone-200 text-stone-700 border-stone-300'],
    'cancelled' => ['label' => 'Đã hủy', 'badge' => 'bg-red-100 text-red-800 border-red-200'],
];

$paymentStatusLabels = [
    'unpaid' => ['label' => 'Chưa thanh toán', 'badge' => 'bg-red-50 text-red-700 border-red-200'],
    'paid' => ['label' => 'Đã thanh toán', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'refunded' => ['label' => 'Đã hoàn tiền', 'badge' => 'bg-stone-100 text-stone-600 border-stone-200'],
];
?>

<div class="max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary">VẬN HÀNH BẾP &amp; PHỤC VỤ</span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">Tổng đơn tìm thấy: <strong><?= count($orders) ?></strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">Quản Lý Đơn Gọi Món Tại Bàn</h1>
            <p class="text-sm text-on-surface-variant">Theo dõi toàn diện các đơn gọi món, tiến độ bếp và lịch sử thanh toán.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/staff/pos" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                <span>Màn hình phục vụ POS</span>
            </a>
            <a href="/tables" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 bg-white text-on-surface text-xs font-semibold hover:border-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">table_restaurant</span>
                <span>Sơ đồ bàn</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-outline-variant/30 shadow-sm">
        <form action="/staff/orders" method="get" class="flex flex-wrap items-center gap-4">
            <div>
                <label for="filter-date" class="block text-xs font-bold text-on-surface uppercase mb-1">Ngày gọi món</label>
                <input id="filter-date" type="date" name="date" value="<?= View::escape($selectedDate) ?>" class="text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
            </div>

            <div>
                <label for="filter-status" class="block text-xs font-bold text-on-surface uppercase mb-1">Trạng thái đơn</label>
                <select id="filter-status" name="status" class="text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($orderStatuses as $stKey => $stLabel): ?>
                        <option value="<?= View::escape($stKey) ?>" <?= $selectedStatus === $stKey ? 'selected' : '' ?>>
                            <?= View::escape($stLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pt-5 flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-inverse-surface text-inverse-on-surface rounded-xl text-xs font-bold hover:opacity-90 transition">
                    Lọc danh sách
                </button>
                <a href="/staff/orders" class="px-4 py-2 border border-outline-variant/40 rounded-xl text-xs font-semibold hover:bg-stone-50 transition">
                    Đặt lại
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl border border-outline-variant/30 shadow-sm overflow-hidden">
        <?php if ($orders === []): ?>
            <div class="py-16 text-center space-y-3">
                <span class="material-symbols-outlined text-5xl text-outline-variant">receipt_long</span>
                <p class="text-base font-semibold text-on-surface">Không có đơn gọi món nào phù hợp</p>
                <p class="text-xs text-on-surface-variant">Thử chọn ngày khác hoặc mở đơn gọi món mới tại màn hình phục vụ POS.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/60 text-xs font-bold uppercase tracking-wider text-outline border-b border-outline-variant/20">
                            <th class="py-4 px-6">Mã đơn &amp; Thời gian</th>
                            <th class="py-4 px-4">Bàn &amp; Khu vực</th>
                            <th class="py-4 px-4">Khách hàng</th>
                            <th class="py-4 px-4 text-center">Tiến độ ra món</th>
                            <th class="py-4 px-4">Tổng tiền</th>
                            <th class="py-4 px-4">Thanh toán</th>
                            <th class="py-4 px-4">Trạng thái đơn</th>
                            <th class="py-4 px-6 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20 text-xs">
                        <?php foreach ($orders as $ord): ?>
                            <?php 
                                $stConf = $statusConfig[$ord['status']] ?? ['label' => $ord['status'], 'badge' => 'bg-stone-100'];
                                $payConf = $paymentStatusLabels[$ord['payment_status']] ?? ['label' => $ord['payment_status'], 'badge' => 'bg-stone-100'];
                            ?>
                            <tr class="hover:bg-surface-container-low/30 transition">
                                <td class="py-4 px-6 font-semibold">
                                    <span class="font-headline text-sm font-bold text-primary">#<?= (int) $ord['id'] ?></span>
                                    <span class="block text-[11px] text-on-surface-variant font-normal"><?= date('H:i - d/m/Y', strtotime($ord['created_at'])) ?></span>
                                </td>
                                <td class="py-4 px-4">
                                    <strong class="text-on-surface font-headline"><?= View::escape($ord['table_code']) ?></strong>
                                    <span class="block text-[11px] text-on-surface-variant"><?= View::escape($ord['area_name']) ?></span>
                                </td>
                                <td class="py-4 px-4 font-medium text-on-surface">
                                    <?= View::escape($ord['customer_name'] ?: 'Khách tại bàn') ?>
                                    <span class="block text-[10px] text-stone-400">Phục vụ: <?= View::escape($ord['staff_name'] ?? 'Nhân viên') ?></span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?= (int) $ord['served_count'] === (int) $ord['item_count'] && (int) $ord['item_count'] > 0 ? 'bg-emerald-100 text-secondary' : 'bg-surface-container text-tertiary-container' ?>">
                                        <?= (int) $ord['served_count'] ?>/<?= (int) $ord['item_count'] ?> món
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-on-surface">
                                    <?= View::currency($ord['grand_total']) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold border <?= $payConf['badge'] ?>">
                                        <?= View::escape($payConf['label']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border uppercase <?= $stConf['badge'] ?>">
                                        <?= View::escape($stConf['label']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <a href="/staff/orders/<?= (int) $ord['id'] ?>" class="font-bold text-primary hover:underline">Chi tiết</a>
                                    <a href="/staff/pos?table_id=<?= (int) $ord['table_id'] ?>" class="font-bold text-secondary hover:underline">Vào POS</a>
                                    <?php if ($ord['payment_status'] === 'paid'): ?>
                                        <a href="/staff/orders/<?= (int) $ord['id'] ?>/invoice" class="font-bold text-blue-700 hover:underline">Hóa đơn</a>
                                    <?php else: ?>
                                        <a href="/staff/orders/<?= (int) $ord['id'] ?>/prebill" target="_blank" class="font-bold text-tertiary hover:underline">In tạm tính</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
