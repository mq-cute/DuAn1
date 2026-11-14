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

$itemStatusConfig = [
    'pending' => ['label' => 'Chờ tiếp nhận', 'class' => 'text-outline-variant bg-stone-100 border-stone-200'],
    'confirmed' => ['label' => 'Đã tiếp nhận', 'class' => 'text-blue-700 bg-blue-50 border-blue-200'],
    'preparing' => ['label' => 'Bếp đang chế biến', 'class' => 'text-amber-800 bg-amber-50 border-amber-200'],
    'ready' => ['label' => 'Món đã sẵn sàng', 'class' => 'text-primary font-bold bg-red-50 border-red-200'],
    'served' => ['label' => 'Đã lên bàn', 'class' => 'text-secondary font-medium bg-emerald-50 border-emerald-200'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'text-stone-400 line-through bg-stone-50 border-stone-200'],
];
?>

<div class="max-w-[1280px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="/staff/orders" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Quay lại danh sách đơn</span>
                </a>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">Chi tiết đơn gọi món</span>
            </div>
            <div class="flex items-center space-x-3 mt-1">
                <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface">Đơn Hàng #<?= (int) $order['id'] ?></h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border uppercase <?= $statusConfig[$order['status']]['badge'] ?? '' ?>">
                    <?= View::escape($statusConfig[$order['status']]['label'] ?? $order['status']) ?>
                </span>
            </div>
            <p class="text-xs text-on-surface-variant mt-1">
                Bàn <strong><?= View::escape($order['table_code']) ?></strong> (<?= View::escape($order['area_name']) ?>)
                • Thực khách: <strong><?= View::escape($order['customer_name'] ?: 'Khách tại bàn') ?></strong>
                • Thời gian tạo: <?= date('H:i - d/m/Y', strtotime($order['created_at'])) ?>
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="/staff/pos?table_id=<?= (int) $order['table_id'] ?>" class="px-4 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary-container shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">room_service</span>
                <span>Vào màn hình POS bàn này</span>
            </a>
            <?php if ($order['payment_status'] === 'paid'): ?>
                <a href="/staff/orders/<?= (int) $order['id'] ?>/invoice" class="px-4 py-2 bg-secondary text-white rounded-xl text-xs font-bold hover:brightness-110 shadow-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">receipt</span>
                    <span>Xem Hóa Đơn</span>
                </a>
            <?php else: ?>
                <a href="/staff/orders/<?= (int) $order['id'] ?>/prebill" target="_blank" class="px-4 py-2 border border-outline-variant/40 rounded-xl text-xs font-bold text-on-surface hover:border-primary transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    <span>In Tạm Tính</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dual Layout: Items List & Order Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Order Items List (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-outline-variant/30 shadow-sm space-y-4">
            <h2 class="text-lg font-headline font-bold text-on-surface">Danh sách món ăn &amp; Đồ uống</h2>

            <div class="divide-y divide-outline-variant/20">
                <?php foreach ($order['items'] as $item): ?>
                    <?php $itConf = $itemStatusConfig[$item['status']] ?? $itemStatusConfig['pending']; ?>
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-surface-container-high text-primary font-headline font-bold flex items-center justify-center shrink-0 text-sm">
                                <?= (int) $item['quantity'] ?>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-on-surface"><?= View::escape($item['dish_name']) ?></h3>
                                <p class="text-xs text-on-surface-variant">
                                    Đơn giá: <?= View::currency($item['unit_price']) ?>
                                    <?php if (!empty($item['note'])): ?>
                                        • <span class="text-primary font-medium">Ghi chú: <?= View::escape($item['note']) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end space-x-4">
                            <span class="text-xs px-2.5 py-0.5 rounded-full border <?= $itConf['class'] ?>">
                                <?= View::escape($itConf['label']) ?>
                            </span>
                            <span class="text-sm font-bold text-on-surface min-w-[100px] text-right">
                                <?= View::currency($item['subtotal']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Notes -->
            <?php if (!empty($order['notes'])): ?>
                <div class="p-4 rounded-2xl bg-surface-container-low/60 border border-outline-variant/20 text-xs text-on-surface-variant">
                    <strong class="text-on-surface">Ghi chú chung:</strong> <?= View::escape($order['notes']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Summary & Histories (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-3xl p-6 border border-outline-variant/30 shadow-sm space-y-4">
                <h2 class="text-lg font-headline font-bold text-on-surface">Chi tiết thanh toán</h2>

                <div class="space-y-2 text-xs text-on-surface-variant">
                    <div class="flex justify-between">
                        <span>Tiền món &amp; rượu:</span>
                        <span class="font-semibold text-on-surface"><?= View::currency($order['total_amount']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Phí phục vụ (5%):</span>
                        <span class="font-semibold text-on-surface"><?= View::currency($order['service_charge']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Thuế GTGT (8%):</span>
                        <span class="font-semibold text-on-surface"><?= View::currency($order['tax']) ?></span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-outline-variant/20 text-base font-bold text-on-surface">
                        <span>Tổng cộng:</span>
                        <span class="text-primary font-headline"><?= View::currency($order['grand_total']) ?></span>
                    </div>
                    <div class="flex justify-between pt-1 text-xs">
                        <span>Trạng thái:</span>
                        <strong class="<?= $order['payment_status'] === 'paid' ? 'text-secondary' : 'text-error' ?>">
                            <?= $order['payment_status'] === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN' ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Histories Timeline -->
            <div class="bg-white rounded-3xl p-6 border border-outline-variant/30 shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-outline">Lịch sử chuyển trạng thái</h2>

                <?php if ($order['histories'] === []): ?>
                    <p class="text-xs text-stone-400">Chưa có lịch sử trạng thái.</p>
                <?php else: ?>
                    <div class="space-y-3 relative pl-4 border-l border-outline-variant/40">
                        <?php foreach ($order['histories'] as $h): ?>
                            <div class="relative text-xs">
                                <span class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-primary border-2 border-white"></span>
                                <div class="font-semibold text-on-surface">
                                    <?= View::escape($statusConfig[$h['new_status']]['label'] ?? $h['new_status']) ?>
                                </div>
                                <p class="text-[11px] text-on-surface-variant mt-0.5"><?= View::escape($h['note'] ?? '') ?></p>
                                <span class="text-[10px] text-stone-400"><?= date('H:i - d/m/Y', strtotime($h['created_at'])) ?> (<?= View::escape($h['changed_by_name'] ?? 'Hệ thống') ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
