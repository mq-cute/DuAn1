<?php

declare(strict_types=1);

use App\Core\View;
use App\Models\Order;

$statusConfig = [
    'pending' => ['label' => 'Chờ tiếp nhận', 'badge' => 'bg-stone-100 text-stone-800 border-stone-200', 'dot' => 'bg-stone-400'],
    'confirmed' => ['label' => 'Đã tiếp nhận', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200', 'dot' => 'bg-blue-500'],
    'preparing' => ['label' => 'Đang nấu', 'badge' => 'bg-amber-100 text-amber-900 border-amber-300', 'dot' => 'bg-amber-500 animate-pulse'],
    'ready' => ['label' => 'Sẵn sàng ra món', 'badge' => 'bg-emerald-100 text-emerald-900 border-emerald-300', 'dot' => 'bg-emerald-500 animate-ping'],
    'served' => ['label' => 'Đã lên bàn', 'badge' => 'bg-stone-100 text-stone-600 border-stone-200', 'dot' => 'bg-stone-400'],
];

$statusFilter = $statusFilter ?? 'all';
$viewMode = $viewMode ?? 'tickets';
$csrfToken = $csrfToken ?? '';
$message = $message ?? '';
$error = $error ?? '';
?>

<div class="max-w-[1700px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Top Action & Real-time Status Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span>LIVE KDS · BẾP THỜI GIAN THỰC</span>
                </span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant font-medium" id="kdsLastSync">Vừa đồng bộ lúc <?= date('H:i:s') ?></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">
                Màn Hình Điều Phối Bếp (Kitchen Display System)
            </h1>
            <p class="text-xs text-on-surface-variant">
                Tiếp nhận đơn gọi món từ phòng ăn, cập nhật tiến độ chế biến và tự động thông báo nhân viên phục vụ khi món ăn hoàn thành.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Sound Toggle -->
            <button type="button" id="toggleSoundBtn" onclick="toggleKdsSound()" class="px-3.5 py-2 rounded-xl border border-outline-variant/40 bg-white text-xs font-bold text-on-surface hover:bg-stone-50 transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-primary" id="soundIcon">volume_up</span>
                <span id="soundLabel">Âm chuông: BẬT</span>
            </button>

            <!-- View Mode Switch -->
            <div class="bg-surface-container-low p-1 rounded-xl border border-outline-variant/30 flex items-center">
                <a href="/kitchen?view=tickets&status=<?= urlencode($statusFilter) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 <?= $viewMode === 'tickets' ? 'bg-inverse-surface text-inverse-on-surface shadow-sm' : 'text-on-surface-variant hover:text-primary' ?>">
                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                    <span>Thẻ phiếu bàn</span>
                </a>
                <a href="/kitchen?view=summary&status=<?= urlencode($statusFilter) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 <?= $viewMode === 'summary' ? 'bg-inverse-surface text-inverse-on-surface shadow-sm' : 'text-on-surface-variant hover:text-primary' ?>">
                    <span class="material-symbols-outlined text-[16px]">soup_kitchen</span>
                    <span>Tổng hợp món</span>
                </a>
            </div>

            <!-- Manual Refresh Button -->
            <button type="button" onclick="refreshKitchenQueue(true)" class="p-2.5 rounded-xl border border-outline-variant/40 bg-white text-on-surface hover:border-primary hover:text-primary transition shadow-sm" title="Làm mới hàng đợi ngay">
                <span class="material-symbols-outlined text-[20px]" id="refreshIcon">sync</span>
            </button>

            <?php if (($currentUser['role'] ?? '') !== 'kitchen'): ?>
            <a href="/staff/pos" class="px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">room_service</span>
                <span>Phục vụ POS</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert / Flash Messages -->
    <?php if ($message === 'item_updated'): ?>
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>Đã cập nhật trạng thái món ăn thành công! Hệ thống đã thông báo nhân viên phục vụ.</span>
        </div>
    <?php elseif ($message === 'ticket_updated'): ?>
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>Đã cập nhật tiến độ toàn bộ món ăn trong phiếu gọi món!</span>
        </div>
    <?php endif; ?>

    <!-- KPI Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-3 2xl:grid-cols-6 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-outline-variant/30 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-outline block">Món đang đợi bếp</span>
            <div class="flex items-baseline justify-between">
                <strong class="text-2xl font-headline font-bold text-on-surface" id="statTotalActive"><?= (int) $stats['total_active'] ?></strong>
                <span class="text-xs text-stone-400">suất ăn</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-blue-200 shadow-sm space-y-1 bg-gradient-to-br from-white to-blue-50/30">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">Đã tiếp nhận</span>
            <div class="flex items-baseline justify-between">
                <strong class="text-2xl font-headline font-bold text-blue-700" id="statConfirmed"><?= (int) $stats['confirmed_count'] ?></strong>
                <span class="text-[10px] font-semibold text-blue-600">chờ nấu</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm space-y-1 bg-gradient-to-br from-white to-amber-50/40">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 block">Đang chế biến</span>
            <div class="flex items-baseline justify-between">
                <strong class="text-2xl font-headline font-bold text-amber-700" id="statPreparing"><?= (int) $stats['preparing_count'] ?></strong>
                <span class="text-[10px] font-semibold text-amber-600 animate-pulse">trên bếp</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm space-y-1 bg-gradient-to-br from-white to-emerald-50/40">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Sẵn sàng ra món</span>
            <div class="flex items-baseline justify-between">
                <strong class="text-2xl font-headline font-bold text-emerald-700" id="statReady"><?= (int) $stats['ready_count'] ?></strong>
                <span class="text-[10px] font-semibold text-emerald-600 font-bold">báo phục vụ</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-red-200 shadow-sm space-y-1 bg-gradient-to-br from-white to-red-50/30">
            <span class="text-[10px] font-bold uppercase tracking-wider text-red-800 block">Chờ quá 15 phút</span>
            <div class="flex items-baseline justify-between">
                <strong class="text-2xl font-headline font-bold <?= (int) $stats['urgent_count'] > 0 ? 'text-red-700 animate-pulse' : 'text-stone-400' ?>" id="statUrgent"><?= (int) $stats['urgent_count'] ?></strong>
                <span class="text-[10px] font-bold uppercase <?= (int) $stats['urgent_count'] > 0 ? 'text-red-600' : 'text-stone-400' ?>">Ưu tiên</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-outline-variant/30 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-outline block">Đã lên bàn hôm nay</span>
            <div class="flex items-baseline justify-between">
                <strong class="text-2xl font-headline font-bold text-secondary" id="statServedToday"><?= (int) $stats['served_today_count'] ?></strong>
                <span class="text-xs text-stone-400">hoàn tất</span>
            </div>
        </div>
    </div>

    <!-- Filter Pills Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-outline-variant/30 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-outline px-2">Lọc trạng thái:</span>
            <a href="/kitchen?status=all&view=<?= urlencode($viewMode) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-stone-100 text-on-surface-variant hover:bg-stone-200' ?>">
                Tất cả hàng đợi (<?= (int) $stats['total_active'] ?>)
            </a>
            <a href="/kitchen?status=confirmed&view=<?= urlencode($viewMode) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'confirmed' ? 'bg-blue-700 text-white shadow-sm' : 'bg-blue-50 text-blue-900 border border-blue-200 hover:bg-blue-100' ?>">
                Chờ nấu (<?= (int) $stats['confirmed_count'] ?>)
            </a>
            <a href="/kitchen?status=preparing&view=<?= urlencode($viewMode) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'preparing' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100' ?>">
                Đang nấu (<?= (int) $stats['preparing_count'] ?>)
            </a>
            <a href="/kitchen?status=ready&view=<?= urlencode($viewMode) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'ready' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-emerald-50 text-emerald-900 border border-emerald-200 hover:bg-emerald-100' ?>">
                Sẵn sàng ra món (<?= (int) $stats['ready_count'] ?>)
            </a>
        </div>

        <div class="text-xs text-on-surface-variant flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-secondary"></span>
            <span>Tự động cập nhật mỗi <strong>4 giây</strong></span>
        </div>
    </div>

    <!-- Dynamic Notification Toast Container (Fixed in bottom-right) -->
    <div id="kdsToastContainer" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none max-w-sm"></div>

    <!-- MAIN VIEW CONTAINER -->
    <div id="kdsContentContainer">
        <?php if ($viewMode === 'summary'): ?>
            <!-- CONSOLIDATED DISH SUMMARY VIEW -->
            <div class="bg-white rounded-3xl border border-outline-variant/30 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-outline-variant/20 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold font-headline text-on-surface">Tổng Hợp Món Cần Nấu Ngay</h2>
                        <p class="text-xs text-on-surface-variant">Tổng hợp toàn bộ các món đang chờ chế biến từ các bàn để bếp trưởng phân công.</p>
                    </div>
                    <span class="text-xs font-bold text-secondary bg-secondary-container/40 px-3 py-1 rounded-full">
                        <?= count($summary) ?> loại món
                    </span>
                </div>

                <?php if ($summary === []): ?>
                    <div class="py-16 text-center space-y-3">
                        <span class="material-symbols-outlined text-5xl text-outline-variant">check_circle</span>
                        <p class="text-base font-semibold text-on-surface">Không có món nào đang chờ chế biến</p>
                        <p class="text-xs text-on-surface-variant">Nhà bếp đã hoàn thành tất cả các món ăn trong hàng đợi.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-surface-container-low/60 uppercase font-bold text-outline border-b border-outline-variant/20 tracking-wider">
                                    <th class="py-3.5 px-6">Món ăn</th>
                                    <th class="py-3.5 px-4">Danh mục</th>
                                    <th class="py-3.5 px-4 text-center">Tổng SL</th>
                                    <th class="py-3.5 px-4 text-center">Chờ tiếp nhận</th>
                                    <th class="py-3.5 px-4 text-center">Đang nấu</th>
                                    <th class="py-3.5 px-4 text-center">Sẵn sàng</th>
                                    <th class="py-3.5 px-6">Các bàn gọi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/20" id="summaryTableBody">
                                <?php foreach ($summary as $item): ?>
                                    <tr class="hover:bg-stone-50 transition">
                                        <td class="py-3.5 px-6 font-semibold text-on-surface text-sm flex items-center gap-3">
                                            <?php if (!empty($item['dish_image'])): ?>
                                                <img src="<?= View::escape($item['dish_image']) ?>" class="w-10 h-10 rounded-lg object-cover border border-outline-variant/30" alt="">
                                            <?php else: ?>
                                                <span class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center font-headline font-bold text-primary">L'É</span>
                                            <?php endif; ?>
                                            <span><?= View::escape($item['dish_name']) ?></span>
                                        </td>
                                        <td class="py-3.5 px-4 text-on-surface-variant"><?= View::escape($item['category_name']) ?></td>
                                        <td class="py-3.5 px-4 text-center font-bold text-base text-primary">
                                            <span class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary"><?= (int) $item['total_qty'] ?></span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-md font-bold <?= (int) $item['pending_qty'] + (int) $item['confirmed_qty'] > 0 ? 'bg-blue-100 text-blue-900' : 'text-stone-300' ?>">
                                                <?= (int) $item['pending_qty'] + (int) $item['confirmed_qty'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-md font-bold <?= (int) $item['preparing_qty'] > 0 ? 'bg-amber-100 text-amber-900 animate-pulse' : 'text-stone-300' ?>">
                                                <?= (int) $item['preparing_qty'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-md font-bold <?= (int) $item['ready_qty'] > 0 ? 'bg-emerald-100 text-emerald-900' : 'text-stone-300' ?>">
                                                <?= (int) $item['ready_qty'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-6">
                                            <div class="flex flex-wrap gap-1.5">
                                                <?php foreach ($item['tables'] as $tbl): ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-stone-100 text-stone-700 font-semibold text-[11px] border border-stone-200">
                                                        <?= View::escape($tbl) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- TICKETS GRID VIEW (STANDARD KDS) -->
            <?php if ($tickets === []): ?>
                <div class="bg-white rounded-3xl border border-outline-variant/30 p-16 text-center space-y-4 shadow-sm">
                    <span class="material-symbols-outlined text-6xl text-secondary animate-bounce">task_alt</span>
                    <h2 class="text-xl font-headline font-bold text-on-surface">Hàng Đợi Bếp Đang Trống</h2>
                    <p class="text-xs text-on-surface-variant max-w-md mx-auto">
                        Tất cả các món ăn đã được phục vụ ra bàn hoặc chưa có đơn gọi món mới. Khi phục vụ tạo đơn tại bàn, phiếu chế biến sẽ tự động xuất hiện kèm âm thanh thông báo.
                    </p>
                    <?php if (($currentUser['role'] ?? '') !== 'kitchen'): ?>
                    <div class="pt-2">
                        <a href="/staff/pos" class="px-4 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            <span>Mở bàn &amp; Gọi món tại POS</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="ticketsGrid">
                    <?php foreach ($tickets as $ticket): ?>
                        <?php
                        $isUrgent = $ticket['is_urgent'];
                        $elapsed = (int) $ticket['elapsed_minutes'];
                        $urgencyClass = $isUrgent ? 'border-red-400 ring-2 ring-red-400/30' : ($elapsed >= 10 ? 'border-amber-300' : 'border-outline-variant/30');
                        ?>
                        <article class="bg-white rounded-3xl border shadow-sm overflow-hidden flex flex-col transition hover:shadow-md <?= $urgencyClass ?>" data-order-id="<?= (int) $ticket['order_id'] ?>">
                            <!-- Ticket Card Header -->
                            <div class="p-4 bg-surface-container-low/70 border-b border-outline-variant/20 flex items-start justify-between gap-3">
                                <div class="space-y-0.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2.5 py-0.5 rounded-md font-bold text-xs bg-primary text-white font-mono tracking-tight">
                                            <?= View::escape($ticket['table_code']) ?>
                                        </span>
                                        <strong class="text-sm font-headline text-on-surface"><?= View::escape($ticket['table_name']) ?></strong>
                                    </div>
                                    <p class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                        <span><?= View::escape($ticket['area_name']) ?></span>
                                        <span>•</span>
                                        <span class="font-medium"><?= View::escape($ticket['customer_name']) ?></span>
                                    </p>
                                </div>

                                <div class="text-right space-y-1">
                                    <span class="text-[10px] font-mono text-outline block"><?= View::escape($ticket['ticket_number']) ?></span>
                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold <?= $isUrgent ? 'bg-red-100 text-red-800 animate-pulse' : ($elapsed >= 10 ? 'bg-amber-100 text-amber-900' : 'bg-stone-100 text-stone-700') ?>">
                                        <span class="material-symbols-outlined text-[13px]">timer</span>
                                        <span><?= $elapsed ?>p</span>
                                        <?php if ($isUrgent): ?>
                                            <span class="text-[9px] uppercase tracking-wider font-extrabold text-red-600">[TRỄ]</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Order Notes -->
                            <?php if (!empty($ticket['order_notes'])): ?>
                                <div class="px-4 py-2 bg-amber-50/70 border-b border-amber-200/50 text-[11px] text-amber-950 flex items-start gap-1.5">
                                    <span class="material-symbols-outlined text-[15px] text-amber-700 shrink-0 mt-0.5">priority_high</span>
                                    <span><strong>Ghi chú bàn:</strong> <?= View::escape($ticket['order_notes']) ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Items List -->
                            <div class="p-4 flex-1 divide-y divide-outline-variant/15 space-y-3">
                                <?php foreach ($ticket['items'] as $item): ?>
                                    <?php
                                    $st = $item['item_status'];
                                    $stMeta = $statusConfig[$st] ?? ['label' => $st, 'badge' => 'bg-stone-100 text-stone-700', 'dot' => 'bg-stone-400'];
                                    ?>
                                    <div class="pt-3 first:pt-0 flex items-start justify-between gap-3 text-xs" id="itemRow-<?= (int) $item['item_id'] ?>">
                                        <div class="space-y-1 flex-1">
                                            <div class="flex items-baseline space-x-2">
                                                <span class="px-1.5 py-0.5 rounded bg-primary/10 text-primary font-bold text-xs font-mono">
                                                    x<?= (int) $item['quantity'] ?>
                                                </span>
                                                <strong class="text-sm font-semibold text-on-surface"><?= View::escape($item['dish_name']) ?></strong>
                                            </div>

                                            <?php if (!empty($item['note'])): ?>
                                                <div class="text-[11px] text-red-700 font-semibold bg-red-50 px-2 py-0.5 rounded border border-red-200/60 inline-flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[13px]">warning</span>
                                                    <span><?= View::escape($item['note']) ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex items-center space-x-2 pt-0.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border <?= $stMeta['badge'] ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full <?= $stMeta['dot'] ?>"></span>
                                                    <span id="itemStatusText-<?= (int) $item['item_id'] ?>"><?= View::escape($stMeta['label']) ?></span>
                                                </span>
                                                <span class="text-[10px] text-stone-400"><?= View::escape($item['category_name'] ?? '') ?></span>
                                            </div>
                                        </div>

                                        <!-- Fast Action Buttons on Item -->
                                        <div class="shrink-0 flex flex-col gap-1.5 items-end">
                                            <?php if ($st === 'pending' || $st === 'confirmed'): ?>
                                                <button type="button" onclick="updateItemStatusAjax(<?= (int) $item['item_id'] ?>, 'preparing')" class="px-3 py-1.5 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 shadow-sm transition flex items-center gap-1" title="Bắt đầu chế biến món này">
                                                    <span class="material-symbols-outlined text-[14px]">skillet</span>
                                                    <span>Nấu ngay</span>
                                                </button>
                                            <?php elseif ($st === 'preparing'): ?>
                                                <button type="button" onclick="updateItemStatusAjax(<?= (int) $item['item_id'] ?>, 'ready')" class="px-3 py-1.5 rounded-xl bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 shadow-sm transition flex items-center gap-1 animate-pulse" title="Món đã nấu xong, báo nhân viên phục vụ">
                                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                                    <span>Món xong</span>
                                                </button>
                                            <?php elseif ($st === 'ready'): ?>
                                                <button type="button" onclick="updateItemStatusAjax(<?= (int) $item['item_id'] ?>, 'served')" class="px-2.5 py-1 rounded-lg border border-emerald-300 text-emerald-800 bg-emerald-50 text-[11px] font-semibold hover:bg-emerald-100 transition" title="Xác nhận đã mang lên bàn">
                                                    Đã lên bàn
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Ticket Card Footer Batch Actions -->
                            <div class="p-3 bg-surface-container-low/50 border-t border-outline-variant/20 flex items-center justify-between text-xs gap-2">
                                <span class="text-[11px] text-on-surface-variant">
                                    Đã xong: <strong><?= $ticket['counts']['ready'] ?></strong> / <?= $ticket['counts']['total'] ?> món
                                </span>

                                <div class="flex items-center gap-1.5">
                                    <?php if ($ticket['counts']['preparing'] < $ticket['counts']['total'] && $ticket['counts']['ready'] < $ticket['counts']['total']): ?>
                                        <button type="button" onclick="updateTicketStatusAjax(<?= (int) $ticket['order_id'] ?>, 'preparing')" class="px-2.5 py-1 rounded-lg border border-amber-300 text-amber-900 bg-amber-50 hover:bg-amber-100 text-[11px] font-bold transition">
                                            Nấu tất cả
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($ticket['counts']['ready'] < $ticket['counts']['total']): ?>
                                        <button type="button" onclick="updateTicketStatusAjax(<?= (int) $ticket['order_id'] ?>, 'ready')" class="px-3 py-1 rounded-lg bg-emerald-700 text-white hover:bg-emerald-800 text-[11px] font-bold transition flex items-center gap-1 shadow-sm">
                                            <span class="material-symbols-outlined text-[14px]">done_all</span>
                                            <span>Xong toàn bộ vé</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-100">
                                            ✓ Toàn bộ món đã sẵn sàng
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Hidden inputs for CSRF and View State -->
<input type="hidden" id="csrfToken" value="<?= View::escape($csrfToken) ?>">
<input type="hidden" id="currentStatusFilter" value="<?= View::escape($statusFilter) ?>">
<input type="hidden" id="currentViewMode" value="<?= View::escape($viewMode) ?>">

<!-- Real-time Polling & Sound Engine -->
<script>
(function() {
    let lastStateHash = "";
    let isSoundEnabled = localStorage.getItem("kds_sound_enabled") !== "0";
    let isPollingActive = true;
    let pollInterval = 4000; // 4 seconds

    // Web Audio Synthesizer: 2-tone pleasant fine dining restaurant chime
    function playChime(isUrgent = false) {
        if (!isSoundEnabled) return;
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const now = ctx.currentTime;

            // Tone 1: High crisp bell
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = "sine";
            osc1.frequency.setValueAtTime(isUrgent ? 880 : 587.33, now); // A5 or D5
            osc1.frequency.exponentialRampToValueAtTime(isUrgent ? 1174.66 : 880, now + 0.15);
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.6);

            // Tone 2: Echo resonance
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = "sine";
            osc2.frequency.setValueAtTime(isUrgent ? 1318.51 : 880, now + 0.18);
            gain2.gain.setValueAtTime(0.2, now + 0.18);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.85);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.18);
            osc2.stop(now + 0.85);
        } catch (e) {
            // Browser autoplay restrictions
        }
    }

    window.toggleKdsSound = function() {
        isSoundEnabled = !isSoundEnabled;
        localStorage.setItem("kds_sound_enabled", isSoundEnabled ? "1" : "0");
        updateSoundButtonUI();
        if (isSoundEnabled) {
            playChime();
            showToast("🔔 Âm thanh thông báo KDS đã được kích hoạt!", "info");
        } else {
            showToast("🔇 Đã tắt âm thanh thông báo KDS.", "info");
        }
    };

    function updateSoundButtonUI() {
        const icon = document.getElementById("soundIcon");
        const label = document.getElementById("soundLabel");
        if (icon && label) {
            if (isSoundEnabled) {
                icon.textContent = "volume_up";
                label.textContent = "Âm chuông: BẬT";
                icon.className = "material-symbols-outlined text-[18px] text-primary";
            } else {
                icon.textContent = "volume_off";
                label.textContent = "Âm chuông: TẮT";
                icon.className = "material-symbols-outlined text-[18px] text-stone-400";
            }
        }
    }

    function showToast(message, type = "success") {
        const container = document.getElementById("kdsToastContainer");
        if (!container) return;

        const toast = document.createElement("div");
        toast.className = "p-3.5 rounded-2xl bg-inverse-surface text-inverse-on-surface shadow-2xl text-xs font-semibold flex items-center justify-between gap-3 border border-outline-variant/40 pointer-events-auto transition-all duration-300 opacity-0 translate-y-3";
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] ${type === 'urgent' ? 'text-red-400' : 'text-emerald-400'}">notifications_active</span>
                <span>${message}</span>
            </div>
            <button type="button" class="text-stone-400 hover:text-white" onclick="this.parentElement.remove()">✕</button>
        `;
        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove("opacity-0", "translate-y-3");
        }, 10);

        // Auto remove after 5s
        setTimeout(() => {
            toast.classList.add("opacity-0", "translate-y-3");
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // AJAX Status Updates without reload
    window.updateItemStatusAjax = function(itemId, targetStatus) {
        const csrf = document.getElementById("csrfToken").value;
        const formData = new FormData();
        formData.append("_csrf_token", csrf);
        formData.append("status", targetStatus);

        fetch("/kitchen/items/" + itemId + "/status", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                playChime();
                showToast(`✓ Đã chuyển món "${data.dish_name}" (Bàn ${data.table_code}) sang trạng thái: ${targetStatus.toUpperCase()}`);
                refreshKitchenQueue(false);
            } else {
                alert(data.error || "Không thể cập nhật món. Vui lòng thử lại.");
            }
        })
        .catch(err => {
            console.error(err);
        });
    };

    window.updateTicketStatusAjax = function(orderId, targetStatus) {
        const csrf = document.getElementById("csrfToken").value;
        const formData = new FormData();
        formData.append("_csrf_token", csrf);
        formData.append("status", targetStatus);

        fetch("/kitchen/tickets/" + orderId + "/status", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                playChime();
                showToast(`✓ Đã cập nhật ${data.updated_count} món trong phiếu đơn #${orderId}!`);
                refreshKitchenQueue(false);
            } else {
                alert("Không thể cập nhật vé. Vui lòng thử lại.");
            }
        })
        .catch(err => {
            console.error(err);
        });
    };

    // Auto-Poll Engine
    window.refreshKitchenQueue = function(isManual = false) {
        const filter = document.getElementById("currentStatusFilter").value;
        const refreshIcon = document.getElementById("refreshIcon");
        if (refreshIcon) refreshIcon.classList.add("animate-spin");

        fetch(`/kitchen/api/queue?status=${encodeURIComponent(filter)}`, {
            headers: { "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {
            if (refreshIcon) setTimeout(() => refreshIcon.classList.remove("animate-spin"), 300);
            if (!data || !data.success) return;

            const timeLabel = document.getElementById("kdsLastSync");
            if (timeLabel) timeLabel.textContent = `Vừa đồng bộ lúc ${data.server_time}`;

            // Update stats
            if (data.stats) {
                const sActive = document.getElementById("statTotalActive");
                if (sActive) sActive.textContent = data.stats.total_active;
                const sConf = document.getElementById("statConfirmed");
                if (sConf) sConf.textContent = data.stats.confirmed_count;
                const sPrep = document.getElementById("statPreparing");
                if (sPrep) sPrep.textContent = data.stats.preparing_count;
                const sRdy = document.getElementById("statReady");
                if (sRdy) sRdy.textContent = data.stats.ready_count;
                const sUrg = document.getElementById("statUrgent");
                if (sUrg) sUrg.textContent = data.stats.urgent_count;
                const sServ = document.getElementById("statServedToday");
                if (sServ) sServ.textContent = data.stats.served_today_count;
            }

            // Check if queue content has changed
            if (lastStateHash && data.hash !== lastStateHash) {
                playChime(data.stats && data.stats.urgent_count > 0);
                showToast("⚡ Hàng đợi bếp vừa có cập nhật mới!", "info");
                // Seamless reload page content or refresh
                window.location.reload();
            }
            lastStateHash = data.hash;
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove("animate-spin");
        });
    };

    // Initialize UI and Start Polling
    updateSoundButtonUI();
    refreshKitchenQueue(false);
    setInterval(() => {
        if (isPollingActive) {
            refreshKitchenQueue(false);
        }
    }, pollInterval);
})();
</script>
