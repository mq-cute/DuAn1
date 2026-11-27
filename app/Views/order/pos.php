<?php

declare(strict_types=1);

use App\Core\View;
use App\Models\Order;
use App\Models\Table;

$statusConfig = [
    'empty' => ['label' => 'Trống', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'dot' => 'bg-emerald-600'],
    'reserved' => ['label' => 'Đã đặt', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300', 'dot' => 'bg-amber-500'],
    'in_use' => ['label' => 'Đang sử dụng', 'badge' => 'bg-blue-100 text-blue-800 border-blue-300', 'dot' => 'bg-blue-600'],
    'serving' => ['label' => 'Đang phục vụ', 'badge' => 'bg-red-100 text-red-800 border-red-300', 'dot' => 'bg-primary'],
    'awaiting_payment' => ['label' => 'Chờ thanh toán', 'badge' => 'bg-orange-100 text-orange-800 border-orange-300', 'dot' => 'bg-orange-500'],
    'cleaning' => ['label' => 'Đang dọn dẹp', 'badge' => 'bg-stone-200 text-stone-700 border-stone-300', 'dot' => 'bg-stone-500'],
    'locked' => ['label' => 'Tạm khóa', 'badge' => 'bg-gray-200 text-gray-800 border-gray-400', 'dot' => 'bg-gray-600'],
];

$itemStatusConfig = [
    'pending' => ['label' => 'Chờ tiếp nhận', 'class' => 'text-outline-variant bg-stone-100 border-stone-200', 'dot' => 'bg-stone-400'],
    'confirmed' => ['label' => 'Đã tiếp nhận', 'class' => 'text-blue-700 bg-blue-50 border-blue-200', 'dot' => 'bg-blue-500'],
    'preparing' => ['label' => 'Bếp đang chế biến', 'class' => 'text-amber-800 bg-amber-50 border-amber-200', 'dot' => 'bg-amber-500 animate-pulse'],
    'ready' => ['label' => 'Món đã sẵn sàng', 'class' => 'text-primary font-bold bg-red-50 border-red-200', 'dot' => 'bg-primary animate-ping'],
    'served' => ['label' => 'Đã lên bàn', 'class' => 'text-secondary font-medium bg-emerald-50 border-emerald-200', 'dot' => 'bg-secondary'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'text-stone-400 line-through bg-stone-50 border-stone-200', 'dot' => 'bg-stone-300'],
];

$messages = [
    'order_created' => 'Tạo đơn gọi món tại bàn thành công! Bếp đã nhận được phiếu chế biến.',
    'items_added' => 'Đã thêm món mới vào đơn và chuyển vé đến nhà bếp.',
    'item_updated' => 'Cập nhật trạng thái phục vụ món ăn thành công.',
    'status_updated' => 'Cập nhật trạng thái đơn hàng thành công.',
    'booking_seated' => 'Đã tiếp nhận khách vào bàn và chuyển món đặt trước đến bếp.',
];
?>

<!-- SUB-HEADER / ZONE & STATE FILTER BAR -->
<section class="bg-surface-container-low border-b border-outline-variant/30 px-6 md:px-10 py-3.5 sticky top-20 z-40">
    <div class="max-w-[1536px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Zone Tabs -->
        <div class="flex items-center space-x-2 overflow-x-auto custom-scroll pb-1 md:pb-0">
            <span class="text-xs font-bold uppercase tracking-wider text-outline mr-2 shrink-0">Khu vực:</span>
            <a href="/staff/pos<?= $statusFilter ? '?status=' . urlencode($statusFilter) : '' ?>" 
               class="px-4 py-1.5 rounded-full text-xs font-bold shrink-0 transition <?= $selectedAreaId === null ? 'bg-inverse-surface text-inverse-on-surface shadow-sm' : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary border border-outline-variant/40' ?>">
                Tất cả (<?= count($tables) ?> bàn)
            </a>
            <?php foreach ($areas as $area): ?>
                <a href="/staff/pos?area_id=<?= (int) $area['id'] ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" 
                   class="px-4 py-1.5 rounded-full text-xs font-bold shrink-0 transition <?= $selectedAreaId === (int) $area['id'] ? 'bg-inverse-surface text-inverse-on-surface shadow-sm' : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary border border-outline-variant/40' ?>">
                    <?= View::escape($area['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Status Quick Filters -->
        <div class="flex items-center space-x-2 shrink-0 overflow-x-auto pb-1 md:pb-0">
            <span class="text-xs font-bold uppercase tracking-wider text-outline mr-1 shrink-0">Trạng thái:</span>
            <a href="/staff/pos?status=serving<?= $selectedAreaId ? '&area_id=' . $selectedAreaId : '' ?>" 
               class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold <?= $statusFilter === 'serving' ? 'bg-primary text-white' : 'bg-surface-container text-primary border border-primary/20 hover:bg-primary-fixed/40' ?> transition">
                <span class="w-2 h-2 rounded-full bg-primary mr-1.5 <?= $statusFilter === 'serving' ? 'bg-white' : '' ?>"></span>
                Đang phục vụ
            </a>
            <a href="/staff/pos?status=awaiting_payment<?= $selectedAreaId ? '&area_id=' . $selectedAreaId : '' ?>" 
               class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold <?= $statusFilter === 'awaiting_payment' ? 'bg-tertiary-container text-white' : 'bg-surface-container-lowest text-tertiary border border-tertiary/20 hover:bg-tertiary-fixed/30' ?> transition">
                <span class="w-2 h-2 rounded-full bg-orange-500 mr-1.5"></span>
                Chờ thanh toán
            </a>
            <a href="/staff/pos?status=empty<?= $selectedAreaId ? '&area_id=' . $selectedAreaId : '' ?>" 
               class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium <?= $statusFilter === 'empty' ? 'bg-secondary text-white' : 'bg-surface-container-lowest text-secondary border border-secondary/20 hover:bg-secondary-fixed/30' ?> transition">
                <span class="w-2 h-2 rounded-full bg-secondary mr-1.5 <?= $statusFilter === 'empty' ? 'bg-white' : '' ?>"></span>
                Trống
            </a>
            <?php if ($statusFilter !== null): ?>
                <a href="/staff/pos<?= $selectedAreaId ? '?area_id=' . $selectedAreaId : '' ?>" class="text-xs text-on-surface-variant hover:text-primary font-bold underline pl-1">
                    Bỏ lọc
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- MAIN DUAL-RAIL WORKSPACE -->
<main class="flex-1 max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-4">
    <!-- Notifications / Alerts -->
    <?php if (!empty($message) && isset($messages[$message])): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-950 border border-emerald-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
                <span class="text-sm font-semibold"><?= View::escape($messages[$message]) ?></span>
            </div>
            <a href="/staff/pos<?= $selectedTableId ? '?table_id=' . $selectedTableId : '' ?>" class="text-xs font-bold underline">Đóng</a>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-2xl bg-red-50 text-red-950 border border-red-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="material-symbols-outlined text-[20px] text-error">error</span>
                <span class="text-sm font-semibold"><?= View::escape(urldecode($error)) ?></span>
            </div>
            <a href="/staff/pos<?= $selectedTableId ? '?table_id=' . $selectedTableId : '' ?>" class="text-xs font-bold underline">Đóng</a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- LEFT RAIL: TABLE CARDS GRID (5 Cols, ~42%) -->
        <section class="lg:col-span-5 space-y-4">
            <div class="flex items-center justify-between px-1">
                <div>
                    <h2 class="text-xl font-headline font-bold text-on-surface">Danh sách bàn trực tiếp</h2>
                    <p class="text-xs text-on-surface-variant">Chọn bàn để gọi món, xem tiến độ bếp hoặc thanh toán</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button type="button" onclick="openBookingConfirmModal()" class="text-xs font-bold text-emerald-800 hover:text-emerald-900 flex items-center gap-1 bg-emerald-100 px-2.5 py-1.5 rounded-lg transition">
                        <span class="material-symbols-outlined text-[16px]">event_available</span>
                        <span>Xác nhận đặt bàn<?= $bookingConfirmations !== [] ? ' (' . count($bookingConfirmations) . ')' : '' ?></span>
                    </button>
                    <button type="button" onclick="openNewOrderModal()" class="text-xs font-bold text-primary hover:text-primary-container flex items-center gap-1 bg-primary-fixed/40 px-2.5 py-1.5 rounded-lg transition">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                        <span>Mở bàn mới</span>
                    </button>
                </div>
            </div>

            <!-- Table Cards Container -->
            <div class="space-y-4">
                <?php if ($tables === []): ?>
                    <div class="p-8 bg-white rounded-2xl border border-outline-variant/30 text-center space-y-2">
                        <span class="material-symbols-outlined text-4xl text-outline">table_restaurant</span>
                        <p class="text-sm font-semibold text-on-surface">Không tìm thấy bàn phù hợp với bộ lọc</p>
                        <a href="/staff/pos" class="text-xs text-primary font-bold hover:underline">Xóa bộ lọc</a>
                    </div>
                <?php endif; ?>

                <?php foreach ($tables as $tbl): ?>
                    <?php 
                        $isSelected = $selectedTableId === (int) $tbl['id'];
                        $conf = $statusConfig[$tbl['status']] ?? $statusConfig['empty'];
                        $hasOrder = !empty($tbl['active_order_id']);
                    ?>
                    <div class="relative bg-surface-container-lowest rounded-2xl p-5 border-2 <?= $isSelected ? 'border-primary shadow-[0_8px_24px_-6px_rgba(135,0,0,0.14)]' : 'border-outline-variant/30 hover:border-outline shadow-sm' ?> transition-all cursor-pointer"
                         onclick="window.location.href='/staff/pos?table_id=<?= (int) $tbl['id'] ?><?= $selectedAreaId ? '&area_id=' . $selectedAreaId : '' ?>'">
                        
                        <!-- Selected Indicator Pill -->
                        <?php if ($isSelected): ?>
                            <div class="absolute -top-3 right-6 bg-primary text-on-primary text-[10px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider shadow-sm flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-on-primary animate-ping"></span>
                                <span>Đang chọn</span>
                            </div>
                        <?php endif; ?>

                        <!-- Card Header -->
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-headline font-bold text-on-surface"><?= View::escape($tbl['code']) ?></h3>
                                    <span class="bg-surface-container text-tertiary-container text-[11px] font-semibold px-2 py-0.5 rounded-md border border-tertiary-fixed-dim/40">
                                        <?= View::escape($tbl['area_name']) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-on-surface-variant mt-0.5">
                                    <?php if ($hasOrder): ?>
                                        Khách: <strong class="text-on-surface"><?= View::escape($tbl['customer_name'] ?? 'Khách tại bàn') ?></strong>
                                    <?php else: ?>
                                        <span class="text-outline uppercase text-[10px] tracking-wider"><?= View::escape($conf['label']) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center text-xs font-semibold <?= $conf['badge'] ?> px-2.5 py-1 rounded-lg border">
                                    <span class="material-symbols-outlined text-[15px] mr-1">group</span> <?= (int) $tbl['capacity'] ?> khách
                                </span>
                                <?php if ($hasOrder && !empty($tbl['minutes_seated'])): ?>
                                    <p class="text-[11px] text-outline mt-1 font-medium">Ngồi <?= (int) $tbl['minutes_seated'] ?> phút</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- KDS Progress Mini-Timeline -->
                        <?php if ($hasOrder && !empty($tbl['items'])): ?>
                            <div class="bg-surface-container-low/70 rounded-xl p-3 border border-outline-variant/30 space-y-2 mb-3">
                                <div class="flex items-center justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Tiến độ ra món bếp:</span>
                                    <span class="text-primary"><?= (int) $tbl['progress_served'] ?>/<?= (int) $tbl['progress_total'] ?> món</span>
                                </div>
                                <div class="space-y-1">
                                    <?php foreach (array_slice($tbl['items'], 0, 3) as $it): ?>
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center space-x-2 truncate pr-2">
                                                <span class="w-2 h-2 rounded-full shrink-0 <?= $it['status'] === 'served' ? 'bg-secondary' : ($it['status'] === 'preparing' ? 'bg-amber-500 animate-pulse' : 'bg-outline-variant') ?>"></span>
                                                <span class="truncate <?= $it['status'] === 'served' ? 'text-outline line-through' : 'text-on-surface font-medium' ?>"><?= View::escape($it['dish_name']) ?></span>
                                            </div>
                                            <span class="text-[10px] shrink-0 font-medium <?= $it['status'] === 'served' ? 'text-secondary' : 'text-on-surface-variant' ?>">
                                                <?= View::escape($itemStatusConfig[$it['status']]['label'] ?? $it['status']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($tbl['items']) > 3): ?>
                                        <p class="text-[10px] text-stone-400 text-right">+<?= count($tbl['items']) - 3 ?> món khác...</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Action Buttons -->
                        <div class="grid grid-cols-3 gap-2 pt-1 border-t border-outline-variant/20">
                            <?php if ($hasOrder): ?>
                                <button type="button" onclick="event.stopPropagation(); openAddDishModal(<?= (int) $tbl['active_order_id'] ?>, '<?= View::escape($tbl['code']) ?>')" class="py-1.5 px-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-primary text-xs font-semibold border border-outline-variant/30 hover:border-primary/40 transition flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[15px]">add_shopping_cart</span>
                                    <span>Thêm món</span>
                                </button>
                                <form action="/tables/<?= (int) $tbl['id'] ?>/status" method="post" onclick="event.stopPropagation()" class="m-0">
                                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                    <input type="hidden" name="status" value="cleaning">
                                    <input type="hidden" name="redirect" value="/staff/pos?table_id=<?= (int) $tbl['id'] ?>">
                                    <button type="submit" class="w-full py-1.5 px-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-secondary text-xs font-semibold border border-outline-variant/30 hover:border-secondary/40 transition flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-[15px]">cleaning_services</span>
                                        <span>Dọn đĩa</span>
                                    </button>
                                </form>
                                <a href="/staff/orders/<?= (int) $tbl['active_order_id'] ?>/prebill" target="_blank" onclick="event.stopPropagation()" class="py-1.5 px-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-tertiary text-xs font-semibold border border-outline-variant/30 hover:border-tertiary/40 transition flex items-center justify-center gap-1 text-center">
                                    <span class="material-symbols-outlined text-[15px]">receipt_long</span>
                                    <span>In tạm tính</span>
                                </a>
                            <?php else: ?>
                                <button type="button" onclick="event.stopPropagation(); openNewOrderForTable(<?= (int) $tbl['id'] ?>, '<?= View::escape($tbl['code']) ?>')" class="col-span-3 py-2 px-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                    <span>Mở bàn &amp; Gọi món ngay</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- RIGHT RAIL: ACTIVE TABLE ORDER DETAIL (7 Cols, ~58%) -->
        <section class="lg:col-span-7 bg-surface-container-lowest rounded-3xl p-6 md:p-8 border border-outline-variant/30 shadow-sm flex flex-col justify-between min-h-[680px]">
            <?php if ($selectedTable !== null): ?>
                <div>
                    <!-- Header of Active Table -->
                    <div class="flex flex-wrap items-center justify-between pb-5 border-b border-outline-variant/30 gap-4">
                        <div>
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl font-headline font-bold text-on-surface"><?= View::escape($selectedTable['code']) ?></span>
                                <span class="bg-tertiary-fixed text-on-tertiary-fixed text-xs px-3 py-1 rounded-full flex items-center space-x-1 font-semibold">
                                    <span class="material-symbols-outlined text-[15px]">restaurant</span>
                                    <span><?= View::escape($selectedTable['area_name'] ?? '') ?></span>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border uppercase <?= $statusConfig[$selectedTable['status']]['badge'] ?? '' ?>">
                                    <?= View::escape($statusConfig[$selectedTable['status']]['label'] ?? $selectedTable['status']) ?>
                                </span>
                            </div>
                            <p class="text-sm text-on-surface-variant mt-1">
                                <?php if ($activeOrder !== null): ?>
                                    Khách: <strong class="text-on-surface"><?= View::escape($activeOrder['customer_name'] ?: 'Khách tại bàn') ?></strong>
                                    • Đơn: <strong class="text-primary">#<?= (int) $activeOrder['id'] ?></strong>
                                    • Sức chứa: <?= (int) $selectedTable['capacity'] ?> chỗ
                                <?php else: ?>
                                    Bàn hiện chưa có đơn gọi món đang hoạt động. Bấm "Mở bàn &amp; Gọi món" để bắt đầu phục vụ.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Table Options -->
                        <div class="flex items-center space-x-2">
                            <form action="/tables/<?= (int) $selectedTable['id'] ?>/status" method="post" class="inline m-0">
                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                <input type="hidden" name="redirect" value="/staff/pos?table_id=<?= (int) $selectedTable['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-xl border border-outline-variant/40 bg-white text-on-surface font-semibold focus:ring-1 focus:ring-primary">
                                    <option value="" disabled selected>Đổi trạng thái bàn...</option>
                                    <option value="empty" <?= $selectedTable['status'] === 'empty' ? 'selected' : '' ?>>Bàn trống</option>
                                    <option value="serving" <?= $selectedTable['status'] === 'serving' ? 'selected' : '' ?>>Đang phục vụ</option>
                                    <option value="awaiting_payment" <?= $selectedTable['status'] === 'awaiting_payment' ? 'selected' : '' ?>>Chờ thanh toán</option>
                                    <option value="cleaning" <?= $selectedTable['status'] === 'cleaning' ? 'selected' : '' ?>>Đang dọn dẹp</option>
                                    <option value="locked" <?= $selectedTable['status'] === 'locked' ? 'selected' : '' ?>>Tạm khóa</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <?php if ($activeOrder !== null): ?>
                        <!-- Quick Dish Picker Mini-Bar (Direct POS fast adding) -->
                        <div class="py-4 border-b border-outline-variant/20">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-xs font-bold uppercase tracking-wider text-outline">Gọi nhanh món đặc tuyển:</span>
                                <button type="button" onclick="openAddDishModal(<?= (int) $activeOrder['id'] ?>, '<?= View::escape($selectedTable['code']) ?>')" class="text-xs font-bold text-secondary hover:text-primary hover:underline flex items-center gap-0.5">
                                    <span>Mở Catalog Thực Đơn</span>
                                    <span class="material-symbols-outlined text-[16px]">menu_book</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <?php foreach (array_slice($allDishes, 0, 4) as $quickDish): ?>
                                    <form action="/staff/orders/<?= (int) $activeOrder['id'] ?>/items" method="post" class="m-0">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <input type="hidden" name="table_id" value="<?= (int) $selectedTable['id'] ?>">
                                        <input type="hidden" name="single_dish_id" value="<?= (int) $quickDish['id'] ?>">
                                        <input type="hidden" name="single_quantity" value="1">
                                        <button type="submit" class="w-full p-2.5 rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/30 text-left transition flex flex-col justify-between group">
                                            <span class="text-xs font-semibold text-on-surface group-hover:text-primary truncate"><?= View::escape($quickDish['name']) ?></span>
                                            <span class="text-xs font-bold text-secondary mt-1">+<?= View::currency($quickDish['price']) ?></span>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Active Table Item List (With Served Action Button) -->
                        <div class="py-4 space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold text-outline px-1 uppercase tracking-wider">
                                <span>DANH SÁCH MÓN ĂN &amp; ĐỒ UỐNG ĐANG GỌI (<?= count($activeOrder['items']) ?>)</span>
                                <span>TRẠNG THÁI PHỤC VỤ</span>
                            </div>

                            <?php if ($activeOrder['items'] === []): ?>
                                <p class="text-sm text-stone-500 italic py-4 text-center">Đơn này chưa có món nào. Bấm vào món bên trên để thêm.</p>
                            <?php endif; ?>

                            <?php foreach ($activeOrder['items'] as $item): ?>
                                <?php $itConf = $itemStatusConfig[$item['status']] ?? $itemStatusConfig['pending']; ?>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-surface-container-low/50 border border-outline-variant/20 hover:border-outline-variant/40 transition gap-3">
                                    <div class="flex items-start space-x-3">
                                        <!-- Fast Checkbox / Button to mark as served -->
                                        <?php if ($item['status'] !== 'served' && $item['status'] !== 'cancelled'): ?>
                                            <form action="/staff/order-items/<?= (int) $item['id'] ?>/status" method="post" class="m-0 pt-0.5">
                                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                                <input type="hidden" name="table_id" value="<?= (int) $selectedTable['id'] ?>">
                                                <input type="hidden" name="status" value="served">
                                                <button type="submit" title="Đánh dấu đã phục vụ lên bàn" class="w-6 h-6 rounded-md border-2 border-primary/60 hover:bg-primary/20 flex items-center justify-center text-primary transition">
                                                    <span class="material-symbols-outlined text-[16px]">done</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="w-6 h-6 rounded-md bg-secondary text-white flex items-center justify-center shrink-0 mt-0.5" title="Món đã phục vụ">
                                                <span class="material-symbols-outlined text-[16px]">check</span>
                                            </span>
                                        <?php endif; ?>

                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h4 class="text-sm font-semibold text-on-surface"><?= View::escape($item['dish_name']) ?></h4>
                                                <span class="text-xs bg-surface-container-highest px-2 py-0.5 rounded text-on-surface-variant font-bold">
                                                    x<?= (int) $item['quantity'] ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-on-surface-variant mt-0.5">
                                                Đơn giá: <?= View::currency($item['unit_price']) ?>
                                                <?php if (!empty($item['note'])): ?>
                                                    • <span class="text-primary font-medium">Ghi chú: <?= View::escape($item['note']) ?></span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Status Badge & Action -->
                                    <div class="flex items-center justify-between sm:justify-end space-x-3 text-right">
                                        <div>
                                            <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full border <?= $itConf['class'] ?>">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 <?= $itConf['dot'] ?>"></span>
                                                <?= View::escape($itConf['label']) ?>
                                            </span>
                                            <div class="text-xs font-bold text-on-surface mt-0.5">
                                                <?= View::currency($item['subtotal']) ?>
                                            </div>
                                        </div>

                                        <?php if ($item['status'] !== 'served' && $item['status'] !== 'cancelled'): ?>
                                            <form action="/staff/order-items/<?= (int) $item['id'] ?>/status" method="post" class="m-0">
                                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                                <input type="hidden" name="table_id" value="<?= (int) $selectedTable['id'] ?>">
                                                <input type="hidden" name="status" value="served">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-secondary text-white text-xs font-bold hover:brightness-110 shadow-sm flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[14px]">room_service</span>
                                                    <span>Lên bàn</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Special Instruction Box -->
                        <?php if (!empty($activeOrder['notes'])): ?>
                            <div class="p-3.5 rounded-2xl bg-surface-container-low/60 border border-outline-variant/20 flex items-start space-x-3 mb-4">
                                <span class="material-symbols-outlined text-tertiary-container text-[20px] mt-0.5">sticky_note_2</span>
                                <div class="flex-1 text-xs">
                                    <span class="font-bold text-on-surface">Ghi chú phục vụ bàn &amp; Bếp:</span>
                                    <p class="text-on-surface-variant mt-0.5"><?= View::escape($activeOrder['notes']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Financial Summary & Primary POS CTAs -->
                        <div class="mt-6 pt-5 border-t border-outline-variant/30 space-y-4">
                            <div class="space-y-1.5 text-xs text-on-surface-variant">
                                <div class="flex justify-between">
                                    <span>Tổng tiền món &amp; Rượu:</span>
                                    <span class="font-semibold text-on-surface"><?= View::currency($activeOrder['total_amount']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Phí dịch vụ nhà hàng (5% Service Charge):</span>
                                    <span class="font-semibold text-on-surface"><?= View::currency($activeOrder['service_charge']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Thuế GTGT ẩm thực (8% VAT):</span>
                                    <span class="font-semibold text-on-surface"><?= View::currency($activeOrder['tax']) ?></span>
                                </div>
                                <div class="flex justify-between text-base font-headline font-bold text-on-surface pt-2 border-t border-outline-variant/20">
                                    <span>Tổng thanh toán:</span>
                                    <span class="text-primary text-xl font-headline font-bold"><?= View::currency($activeOrder['grand_total']) ?></span>
                                </div>
                            </div>

                            <!-- POS Primary Action Buttons -->
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
                                <a href="/staff/orders/<?= (int) $activeOrder['id'] ?>/prebill" target="_blank" class="sm:col-span-4 py-3.5 px-4 rounded-xl border border-secondary text-secondary font-bold text-xs hover:bg-secondary/10 transition flex items-center justify-center space-x-2 text-center">
                                    <span class="material-symbols-outlined text-[18px]">print</span>
                                    <span>In Tạm Tính (Pre-Bill)</span>
                                </a>
                                <button type="button" onclick="openPaymentModal(<?= (int) $activeOrder['id'] ?>, '<?= View::escape($selectedTable['code']) ?>', '<?= View::escape(number_format((float) $activeOrder['grand_total'], 0, ',', '.')) ?> ₫')" class="sm:col-span-8 py-3.5 px-6 rounded-xl bg-primary text-on-primary font-bold text-xs hover:bg-primary-container transition shadow-md flex items-center justify-center space-x-2">
                                    <span class="material-symbols-outlined text-[20px]">credit_card</span>
                                    <span>Xác nhận Thanh toán &amp; Xuất Hóa đơn</span>
                                </button>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Empty State for Table with No Active Order -->
                        <div class="py-16 text-center space-y-4">
                            <span class="material-symbols-outlined text-6xl text-outline-variant">restaurant_menu</span>
                            <div class="max-w-md mx-auto space-y-1">
                                <h3 class="text-xl font-headline font-bold text-on-surface">Bàn <?= View::escape($selectedTable['code']) ?> đang sẵn sàng</h3>
                                <p class="text-xs text-on-surface-variant">Bàn chưa có lượt khách hoặc đơn gọi món nào đang diễn ra. Tạo đơn mới để bắt đầu tiếp đón thực khách.</p>
                            </div>
                            <button type="button" onclick="openNewOrderForTable(<?= (int) $selectedTable['id'] ?>, '<?= View::escape($selectedTable['code']) ?>')" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-xs hover:bg-primary-container shadow-sm transition">
                                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                <span>Tạo Đơn Gọi Món Cho Bàn Này</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="py-24 text-center text-on-surface-variant">
                    <p class="font-medium">Vui lòng chọn một bàn từ danh sách bên trái để thao tác.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- ==================== BOOKING CONFIRMATION PICKER ==================== -->
<div id="bookingConfirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-inverse-surface/60 backdrop-blur-sm items-center justify-center p-4">
    <section class="w-full max-w-3xl rounded-2xl bg-white p-5 shadow-2xl md:p-7" role="dialog" aria-modal="true" aria-labelledby="bookingConfirmTitle">
        <div class="mb-5 flex items-start justify-between gap-4 border-b border-outline-variant/30 pb-4">
            <div>
                <h2 id="bookingConfirmTitle" class="text-xl font-bold text-on-surface">Đặt bàn cần xác nhận</h2>
                <p class="mt-1 text-xs text-on-surface-variant">Chọn lượt đặt để xem thông tin khách, món, tiền cọc và hóa đơn tạm tính.</p>
            </div>
            <button type="button" onclick="closeBookingConfirmModal()" class="rounded-lg p-2 text-outline hover:bg-surface-container-low" aria-label="Đóng">✕</button>
        </div>
        <?php if ($bookingConfirmations === []): ?>
            <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">Hiện không có đặt bàn nào đang chờ xác nhận hoặc nhận khách.</div>
        <?php else: ?>
            <div class="max-h-[65vh] space-y-3 overflow-y-auto">
                <?php foreach ($bookingConfirmations as $pendingBooking): ?>
                    <?php $bookingTimestamp = strtotime((string) $pendingBooking['booking_date'] . ' ' . (string) $pendingBooking['booking_time']); ?>
                    <article class="flex flex-col gap-3 rounded-xl border border-outline-variant/40 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <strong class="text-sm text-on-surface">#<?= (int) $pendingBooking['id'] ?> · <?= View::escape($pendingBooking['customer_name']) ?></strong>
                            <p class="mt-1 text-xs text-on-surface-variant"><?= View::escape($pendingBooking['table_code'] ?? 'Chưa xếp bàn') ?> · <?= $bookingTimestamp === false ? '' : date('d/m/Y H:i', $bookingTimestamp) ?> · <?= (int) $pendingBooking['number_of_guests'] ?> khách</p>
                            <p class="mt-1 text-xs font-semibold text-emerald-700">Tiền cọc: <?= View::currency((string) (int) round((float) ($pendingBooking['deposit_amount'] ?? 0))) ?></p>
                        </div>
                        <a href="/admin/bookings/<?= (int) $pendingBooking['id'] ?>/confirm-pos" class="inline-flex shrink-0 items-center justify-center gap-1 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800">
                            <span class="material-symbols-outlined text-[16px]">fact_check</span> Xem form xác nhận
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ==================== MODAL 1: MỞ BÀN MỚI / TẠO ĐƠN GỌI MÓN (Requirement 1) ==================== -->
<div id="newOrderModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-inverse-surface/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-outline-variant/40 space-y-6 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/30">
            <div>
                <h3 class="text-xl font-headline font-bold text-on-surface">Tạo Đơn Gọi Món Tại Bàn</h3>
                <p class="text-xs text-on-surface-variant">Chọn bàn, thực khách và danh sách món khai tiệc</p>
            </div>
            <button type="button" onclick="closeNewOrderModal()" class="p-2 text-on-surface-variant hover:text-primary rounded-xl">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form action="/staff/orders" method="post" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1">Chọn bàn phục vụ *</label>
                    <select id="newOrderTableSelect" name="table_id" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 bg-surface-container-low focus:ring-2 focus:ring-primary">
                        <option value="" disabled selected>-- Chọn bàn trống --</option>
                        <?php foreach ($emptyTables as $et): ?>
                            <option value="<?= (int) $et['id'] ?>">Bàn <?= View::escape($et['code']) ?> (<?= View::escape($et['area_name']) ?> - <?= (int) $et['capacity'] ?> chỗ)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1">Tên khách hàng</label>
                    <input type="text" name="customer_name" placeholder="Ví dụ: Jean-Pierre Laurent / Bàn VIP" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1">Ghi chú phục vụ &amp; Bếp trưởng</label>
                <textarea name="notes" rows="2" placeholder="Ghi chú sở thích khách, dị ứng, kỷ niệm..." class="w-full text-xs p-3 rounded-xl border border-outline-variant/40 focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <!-- Dish Selection Table -->
            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Chọn món khai tiệc</label>
                <div class="max-h-64 overflow-y-auto custom-scroll border border-outline-variant/30 rounded-2xl divide-y divide-outline-variant/20 p-2 space-y-1">
                    <?php foreach ($allDishes as $d): ?>
                        <div class="p-2 flex items-center justify-between text-xs hover:bg-surface-container-low/40 rounded-xl transition">
                            <div class="flex-1 pr-3">
                                <span class="font-semibold text-on-surface"><?= View::escape($d['name']) ?></span>
                                <span class="text-secondary font-bold ml-2"><?= View::currency($d['price']) ?></span>
                                <input type="text" name="items[<?= (int) $d['id'] ?>][note]" placeholder="Ghi chú món (nếu có)" class="mt-1 w-full text-[11px] py-1 px-2 rounded-lg border border-outline-variant/30">
                            </div>
                            <div class="flex items-center space-x-1.5 shrink-0">
                                <span class="text-xs font-medium text-on-surface-variant">SL:</span>
                                <input type="number" name="items[<?= (int) $d['id'] ?>][quantity]" value="0" min="0" max="99" class="w-14 text-xs py-1 px-2 rounded-lg border border-outline-variant/40 text-center font-bold">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3 border-t border-outline-variant/20">
                <button type="button" onclick="closeNewOrderModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 text-xs font-semibold hover:bg-stone-100">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm">
                    Tạo đơn &amp; Gửi nhà bếp
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 2: GỌI THÊM MÓN / CATALOG (Requirement 7) ==================== -->
<div id="addDishModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-inverse-surface/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-outline-variant/40 space-y-6 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/30">
            <div>
                <h3 class="text-xl font-headline font-bold text-on-surface">Gọi Thêm Món Vào Bàn <span id="addDishTableCode" class="text-primary"></span></h3>
                <p class="text-xs text-on-surface-variant">Chọn các món cần bổ sung từ thực đơn nhà hàng</p>
            </div>
            <button type="button" onclick="closeAddDishModal()" class="p-2 text-on-surface-variant hover:text-primary rounded-xl">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="addDishForm" action="" method="post" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <input type="hidden" name="table_id" value="<?= (int) ($selectedTable['id'] ?? 0) ?>">

            <div class="max-h-80 overflow-y-auto custom-scroll border border-outline-variant/30 rounded-2xl divide-y divide-outline-variant/20 p-2 space-y-1">
                <?php foreach ($allDishes as $d): ?>
                    <div class="p-2 flex items-center justify-between text-xs hover:bg-surface-container-low/40 rounded-xl transition">
                        <div class="flex-1 pr-3">
                            <span class="font-semibold text-on-surface"><?= View::escape($d['name']) ?></span>
                            <span class="text-secondary font-bold ml-2"><?= View::currency($d['price']) ?></span>
                            <input type="text" name="items[<?= (int) $d['id'] ?>][note]" placeholder="Ghi chú chế biến..." class="mt-1 w-full text-[11px] py-1 px-2 rounded-lg border border-outline-variant/30">
                        </div>
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <span class="text-xs font-medium text-on-surface-variant">SL:</span>
                            <input type="number" name="items[<?= (int) $d['id'] ?>][quantity]" value="0" min="0" max="99" class="w-14 text-xs py-1 px-2 rounded-lg border border-outline-variant/40 text-center font-bold">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3 border-t border-outline-variant/20">
                <button type="button" onclick="closeAddDishModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 text-xs font-semibold hover:bg-stone-100">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm">
                    Gửi bổ sung vào bếp
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 3: XÁC NHẬN THANH TOÁN (Requirement 3) ==================== -->
<div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-inverse-surface/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-outline-variant/40 space-y-6 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/30">
            <div>
                <h3 class="text-xl font-headline font-bold text-on-surface">Xác Nhận Thanh Toán</h3>
                <p class="text-xs text-on-surface-variant">Bàn <span id="paymentTableCode" class="font-bold text-primary"></span></p>
            </div>
            <button type="button" onclick="closePaymentModal()" class="p-2 text-on-surface-variant hover:text-primary rounded-xl">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="paymentForm" action="" method="post" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <input type="hidden" name="table_id" value="<?= (int) ($selectedTable['id'] ?? 0) ?>">

            <!-- Total display -->
            <div class="p-4 rounded-2xl bg-surface-container-low text-center space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Tổng số tiền cần thanh toán</span>
                <div id="paymentTotalAmount" class="text-2xl font-headline font-bold text-primary"></div>
                <span class="text-[11px] text-stone-500">(Đã bao gồm 5% Phí dịch vụ &amp; 8% VAT)</span>
            </div>

            <!-- Payment Methods -->
            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Chọn phương thức thanh toán *</label>
                <div class="grid grid-cols-2 gap-2.5">
                    <label class="flex min-h-[52px] w-full items-center gap-2.5 rounded-xl border border-outline-variant/40 p-3 hover:border-primary cursor-pointer transition" style="display:flex;align-items:center;justify-content:flex-start;box-sizing:border-box;">
                        <input type="radio" name="payment_method" value="cash" checked class="text-primary focus:ring-0" style="flex:0 0 16px;width:16px;height:16px;margin:0;">
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-on-surface" style="display:flex;align-items:center;gap:6px;min-width:0;">
                            <span class="material-symbols-outlined text-[18px] text-secondary" style="flex:0 0 auto;">payments</span>
                            <span>Tiền mặt</span>
                        </div>
                    </label>
                    <a id="posOnlineGatewayLink" href="#" target="_blank" class="flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-amber-50 p-3 text-center text-xs font-bold text-amber-900 hover:bg-amber-100 transition shadow-sm" style="display:flex;align-items:center;justify-content:center;box-sizing:border-box;">
                        <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                        <span>Cổng VNPAY / VietQR</span>
                    </a>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-outline-variant/20 flex-wrap gap-2">
                <span></span>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl border border-outline-variant/40 text-xs font-semibold hover:bg-stone-100">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-md flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        <span>Xác nhận &amp; Xuất Hóa Đơn</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openBookingConfirmModal() {
        const modal = document.getElementById('bookingConfirmModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeBookingConfirmModal() {
        const modal = document.getElementById('bookingConfirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function openNewOrderModal() {
        document.getElementById('newOrderModal').classList.remove('hidden');
    }
    function closeNewOrderModal() {
        document.getElementById('newOrderModal').classList.add('hidden');
    }
    function openNewOrderForTable(tableId, tableCode) {
        const select = document.getElementById('newOrderTableSelect');
        if (select) {
            select.value = tableId;
        }
        openNewOrderModal();
    }

    function openAddDishModal(orderId, tableCode) {
        document.getElementById('addDishTableCode').innerText = tableCode;
        document.getElementById('addDishForm').action = '/staff/orders/' + orderId + '/items';
        document.getElementById('addDishModal').classList.remove('hidden');
    }
    function closeAddDishModal() {
        document.getElementById('addDishModal').classList.add('hidden');
    }

    function openPaymentModal(orderId, tableCode, totalFormatted) {
        document.getElementById('paymentTableCode').innerText = tableCode;
        document.getElementById('paymentTotalAmount').innerText = totalFormatted;
        document.getElementById('paymentForm').action = '/staff/orders/' + orderId + '/pay';
        var gatewayLink = document.getElementById('posOnlineGatewayLink');
        if (gatewayLink) {
            gatewayLink.href = '/payment/checkout/' + orderId;
        }
        document.getElementById('paymentModal').classList.remove('hidden');
    }
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }
</script>
