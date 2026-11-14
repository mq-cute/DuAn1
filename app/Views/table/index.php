<?php

declare(strict_types=1);

use App\Core\View;
use App\Models\Table;

$statusConfig = [
    'empty' => [
        'label' => 'Trống',
        'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'circle' => 'bg-emerald-600',
        'ring' => 'border-emerald-500/40',
        'bg' => 'bg-emerald-50/40',
    ],
    'reserved' => [
        'label' => 'Đã đặt',
        'badge' => 'bg-amber-100 text-amber-800 border-amber-300',
        'circle' => 'bg-amber-500',
        'ring' => 'border-amber-400/50',
        'bg' => 'bg-amber-50/40',
    ],
    'in_use' => [
        'label' => 'Đang sử dụng',
        'badge' => 'bg-blue-100 text-blue-800 border-blue-300',
        'circle' => 'bg-blue-600',
        'ring' => 'border-blue-500/40',
        'bg' => 'bg-blue-50/40',
    ],
    'serving' => [
        'label' => 'Đang phục vụ',
        'badge' => 'bg-red-100 text-red-800 border-red-300',
        'circle' => 'bg-primary',
        'ring' => 'border-primary/60',
        'bg' => 'bg-red-50/30',
    ],
    'awaiting_payment' => [
        'label' => 'Chờ thanh toán',
        'badge' => 'bg-orange-100 text-orange-800 border-orange-300',
        'circle' => 'bg-orange-500',
        'ring' => 'border-orange-400/50',
        'bg' => 'bg-orange-50/40',
    ],
    'cleaning' => [
        'label' => 'Đang dọn dẹp',
        'badge' => 'bg-stone-200 text-stone-700 border-stone-300',
        'circle' => 'bg-stone-500',
        'ring' => 'border-stone-400/50',
        'bg' => 'bg-stone-50',
    ],
    'locked' => [
        'label' => 'Tạm khóa',
        'badge' => 'bg-gray-200 text-gray-800 border-gray-400',
        'circle' => 'bg-gray-600',
        'ring' => 'border-gray-500',
        'bg' => 'bg-gray-100',
    ],
];

$messages = [
    'status_updated' => 'Cập nhật trạng thái bàn thành công!',
    'update_failed' => 'Không thể cập nhật trạng thái bàn.',
    'invalid_csrf' => 'Biểu mẫu hết hạn, vui lòng thử lại.',
];
?>

<div class="max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header & Summary Metrics -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary">VẬN HÀNH NHÀ HÀNG</span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">Tổng số: <strong><?= (int) $totalTables ?> bàn</strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">Sơ Đồ Bàn &amp; Trạng Thái Chỗ Ngồi</h1>
            <p class="text-sm text-on-surface-variant">Theo dõi tình trạng bàn theo thời gian thực và quản lý phục vụ trực tiếp.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="/staff/pos" class="px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                <span>Màn hình POS phục vụ</span>
            </a>
            <a href="/admin/bookings" class="px-4 py-2 rounded-xl border border-outline-variant/50 bg-white text-on-surface text-xs font-semibold hover:border-primary hover:text-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                <span>Duyệt đặt bàn</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message) && isset($messages[$message])): ?>
        <div class="p-4 rounded-xl <?= $message === 'status_updated' ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-red-50 text-red-900 border border-red-200' ?> flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="material-symbols-outlined text-[20px]"><?= $message === 'status_updated' ? 'check_circle' : 'error' ?></span>
                <span class="text-sm font-semibold"><?= View::escape($messages[$message]) ?></span>
            </div>
            <a href="/tables" class="text-xs font-bold underline hover:no-underline">Đóng</a>
        </div>
    <?php endif; ?>

    <!-- Status Filter / Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <?php foreach ($statusConfig as $sKey => $sVal): ?>
            <div class="p-3.5 rounded-2xl bg-white border border-outline-variant/30 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-on-surface-variant"><?= View::escape($sVal['label']) ?></span>
                    <span class="w-2.5 h-2.5 rounded-full <?= $sVal['circle'] ?>"></span>
                </div>
                <div class="text-xl font-bold font-headline text-on-surface mt-2">
                    <?= (int) ($counts[$sKey] ?? 0) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Zone Tabs Filter -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-outline-variant/20 custom-scroll">
        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mr-2 shrink-0">Khu vực:</span>
        <a href="/tables" class="px-4 py-1.5 rounded-full text-xs font-bold shrink-0 transition <?= $selectedAreaId === null ? 'bg-inverse-surface text-inverse-on-surface shadow-sm' : 'bg-white border border-outline-variant/40 text-on-surface-variant hover:text-primary' ?>">
            Tất cả khu vực (<?= (int) $totalTables ?>)
        </a>
        <?php foreach ($areas as $area): ?>
            <a href="/tables?area_id=<?= (int) $area['id'] ?>" class="px-4 py-1.5 rounded-full text-xs font-bold shrink-0 transition <?= $selectedAreaId === (int) $area['id'] ? 'bg-inverse-surface text-inverse-on-surface shadow-sm' : 'bg-white border border-outline-variant/40 text-on-surface-variant hover:text-primary' ?>">
                <?= View::escape($area['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Floor Plan Map Display by Area -->
    <div class="space-y-8">
        <?php foreach ($tablesByArea as $areaId => $areaData): ?>
            <?php if ($selectedAreaId !== null && $selectedAreaId !== (int) $areaId) continue; ?>
            <section class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/30 shadow-sm space-y-6">
                <!-- Area Title -->
                <div class="flex items-center justify-between pb-3 border-b border-outline-variant/20">
                    <div class="flex items-center space-x-3">
                        <span class="w-3 h-3 rounded-full bg-primary"></span>
                        <h2 class="text-xl font-headline font-bold text-on-surface"><?= View::escape($areaData['area']['name']) ?></h2>
                        <span class="text-xs text-on-surface-variant hidden sm:inline">(<?= View::escape($areaData['area']['description'] ?? '') ?>)</span>
                    </div>
                    <span class="text-xs font-semibold text-secondary bg-secondary-container/40 px-3 py-1 rounded-full">
                        <?= count($areaData['tables']) ?> bàn trong khu vực
                    </span>
                </div>

                <!-- Tables Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <?php foreach ($areaData['tables'] as $table): ?>
                        <?php 
                            $conf = $statusConfig[$table['status']] ?? $statusConfig['empty'];
                            $hasActiveOrder = !empty($table['active_order']);
                        ?>
                        <div class="rounded-2xl border-2 <?= $conf['ring'] ?> <?= $conf['bg'] ?> p-5 flex flex-col justify-between hover:shadow-md transition relative group">
                            <!-- Top: Table Code & Capacity -->
                            <div>
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-lg font-headline font-bold text-on-surface"><?= View::escape($table['code']) ?></h3>
                                            <span class="text-xs text-on-surface-variant"><?= View::escape($table['name']) ?></span>
                                        </div>
                                        <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-0.5">
                                            <span class="material-symbols-outlined text-[15px]">group</span>
                                            <span>Sức chứa: <strong><?= (int) $table['capacity'] ?> khách</strong></span>
                                        </p>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border uppercase tracking-wider <?= $conf['badge'] ?>">
                                        <?= View::escape($conf['label']) ?>
                                    </span>
                                </div>

                                <!-- Middle: Active Order Details (if any) -->
                                <?php if ($hasActiveOrder): ?>
                                    <div class="mt-4 p-3 rounded-xl bg-white/80 border border-outline-variant/30 space-y-1.5 text-xs">
                                        <div class="flex items-center justify-between text-on-surface font-semibold">
                                            <span class="truncate"><?= View::escape($table['active_order']['customer_name']) ?></span>
                                            <span class="text-primary font-bold"><?= View::currency($table['active_order']['grand_total']) ?></span>
                                        </div>
                                        <div class="flex items-center justify-between text-on-surface-variant text-[11px]">
                                            <span>Bắt đầu: <?= date('H:i', strtotime($table['active_order']['created_at'])) ?></span>
                                            <span class="text-secondary font-medium">Đơn #<?= (int) $table['active_order']['order_id'] ?></span>
                                        </div>
                                    </div>
                                <?php elseif ($table['status'] === 'reserved'): ?>
                                    <div class="mt-4 p-3 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 space-y-1">
                                        <p class="font-medium">Khách đã đặt trước cho ca này.</p>
                                        <p class="text-[11px] text-amber-700">Vui lòng kiểm tra danh sách đặt bàn để đón khách.</p>
                                    </div>
                                <?php elseif ($table['status'] === 'cleaning'): ?>
                                    <div class="mt-4 p-3 rounded-xl bg-stone-100 border border-stone-300 text-xs text-stone-700 flex items-center justify-between">
                                        <span>Bàn đang dọn dẹp</span>
                                        <form action="/tables/<?= (int) $table['id'] ?>/status" method="post" class="m-0">
                                            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                            <input type="hidden" name="status" value="empty">
                                            <button type="submit" class="px-2 py-1 bg-emerald-600 text-white rounded text-[10px] font-bold hover:bg-emerald-700">Đã dọn xong</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-4 p-3 rounded-xl bg-white/50 border border-dashed border-outline-variant/40 text-xs text-on-surface-variant text-center">
                                        Bàn đang sẵn sàng phục vụ
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Bottom Actions -->
                            <div class="mt-5 pt-3 border-t border-outline-variant/20 flex items-center justify-between gap-2">
                                <?php if (in_array($table['status'], ['serving', 'in_use', 'awaiting_payment'], true)): ?>
                                    <a href="/staff/pos?table_id=<?= (int) $table['id'] ?>" class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-xs font-bold text-center hover:bg-primary-container transition flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                        <span>Mở POS gọi món</span>
                                    </a>
                                <?php else: ?>
                                    <a href="/staff/pos?table_id=<?= (int) $table['id'] ?>" class="flex-1 py-2 px-3 rounded-xl bg-emerald-700 text-white text-xs font-bold text-center hover:bg-emerald-800 transition flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                        <span>Mở bàn &amp; Gọi món</span>
                                    </a>
                                <?php endif; ?>

                                <!-- Change Status Dropdown / Fast Action -->
                                <div class="relative">
                                    <form action="/tables/<?= (int) $table['id'] ?>/status" method="post" class="inline m-0">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <select name="status" onchange="this.form.submit()" class="text-[11px] py-1.5 px-2 bg-white rounded-xl border border-outline-variant/40 text-on-surface font-medium focus:ring-1 focus:ring-primary">
                                            <option value="" disabled selected>Đổi...</option>
                                            <option value="empty" <?= $table['status'] === 'empty' ? 'selected' : '' ?>>Trống</option>
                                            <option value="serving" <?= $table['status'] === 'serving' ? 'selected' : '' ?>>Đang phục vụ</option>
                                            <option value="awaiting_payment" <?= $table['status'] === 'awaiting_payment' ? 'selected' : '' ?>>Chờ thanh toán</option>
                                            <option value="cleaning" <?= $table['status'] === 'cleaning' ? 'selected' : '' ?>>Đang dọn dẹp</option>
                                            <option value="locked" <?= $table['status'] === 'locked' ? 'selected' : '' ?>>Tạm khóa</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</div>
