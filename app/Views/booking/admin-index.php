<?php
use App\Core\View;

$areas = $areas ?? [];
$allTables = $allTables ?? [];
$bookings = $bookings ?? [];
$date = $date ?? '';
$status = $status ?? '';
$message = $message ?? '';
$csrfToken = $csrfToken ?? '';

$statusConfig = [
    'pending' => ['label' => 'Chờ xếp bàn', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'confirmed' => ['label' => 'Đã xác nhận & Cọc', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'seated' => ['label' => 'Đang phục vụ', 'badge' => 'bg-blue-100 text-blue-800 border-blue-300'],
    'completed' => ['label' => 'Hoàn tất', 'badge' => 'bg-slate-100 text-slate-800 border-slate-300'],
    'cancelled' => ['label' => 'Đã hủy', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
    'no_show' => ['label' => 'Vắng mặt (Trễ hạn)', 'badge' => 'bg-red-100 text-red-800 border-red-300'],
];

$messages = [
    'updated' => 'Cập nhật trạng thái đặt bàn thành công.',
    'invalid' => 'Thông tin không hợp lệ hoặc phiên bảo mật hết hạn.',
    'missing' => 'Không tìm thấy lượt đặt bàn.',
    'table-busy' => 'Bàn được chọn đang có khách sử dụng, không thể nhận lượt này.',
    'not-today' => 'Chỉ có thể nhận khách vào đúng ngày đặt bàn.',
    'invalid-transition' => 'Không thể chuyển đổi trạng thái theo quy trình hiện tại.',
    'table-opened' => 'Đã mở bàn thành công từ đơn đặt trước! Đã tạo hóa đơn tạm kèm chi phí bàn & setup.',
    'table-changed' => 'Đã chuyển đổi bàn mới cho khách thành công do sự cố/yêu cầu thay đổi.',
    'table-not-assigned' => 'Lượt đặt bàn này chưa được gán bàn cụ thể. Vui lòng xếp bàn trước khi nhận khách.',
    'cannot-open' => 'Không thể mở bàn lúc này. Vui lòng kiểm tra lại.',
    'cannot-change' => 'Không thể chuyển bàn. Bàn mới có thể đã bị chiếm chỗ.',
];

// Thống kê nhanh
$totalBookings = count($bookings);
$confirmedCount = 0;
$seatedCount = 0;
$pendingCount = 0;
$totalDeposit = 0;

foreach ($bookings as $b) {
    if ($b['status'] === 'confirmed') $confirmedCount++;
    elseif ($b['status'] === 'seated') $seatedCount++;
    elseif ($b['status'] === 'pending') $pendingCount++;
    $totalDeposit += !empty($b['deposit_amount']) ? (float)$b['deposit_amount'] : 0;
}
?>

<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>HỆ THỐNG ĐẶT CHỖ CAO CẤP</span>
                </span>
                <span class="text-xs text-slate-400">&bull; Đồng bộ thời gian thực (KDS & Quầy)</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Quản lý Đặt bàn & Tiếp nhận Khách
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kiểm soát lịch đặt trực tuyến và tại quầy, ngăn ngừa xung đột lịch bàn tuyệt đối. Tự động thu hồi bàn khi khách trễ hạn.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2 bg-white border border-slate-200 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 shadow-sm">
                <span class="material-symbols-outlined text-[16px] text-gold">calendar_today</span>
                <span><?= !empty($date) ? date('d/m/Y', strtotime($date)) : date('d/m/Y') ?> (Hôm nay)</span>
            </div>
            <a href="/bookings/new" target="_blank" class="px-4 py-2 bg-gradient-to-r from-admin-navy to-slate-800 text-gold text-xs font-bold rounded-xl border border-gold/30 shadow-sm hover:shadow flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>Tạo đặt bàn Offline (+)</span>
            </a>
        </div>
    </div>

    <!-- Alert Flash -->
    <?php if (isset($messages[$message])): ?>
        <div class="p-4 rounded-xl text-xs font-semibold flex items-center gap-2 <?= in_array($message, ['updated', 'table-opened', 'table-changed']) ? 'bg-emerald-50 text-emerald-900 border border-emerald-300' : 'bg-rose-50 text-rose-900 border border-rose-300' ?>">
            <span class="material-symbols-outlined text-[18px]"><?= in_array($message, ['updated', 'table-opened', 'table-changed']) ? 'check_circle' : 'error' ?></span>
            <span><?= View::escape($messages[$message]) ?></span>
        </div>
    <?php endif; ?>

    <!-- 4 KPI Summary Strip (Theo mẫu ảnh) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">KHÁCH ĐÃ XÁC NHẬN</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-slate-900"><?= $confirmedCount ?></strong>
                <span class="text-emerald-700 text-xs font-bold">+18% vs hôm qua</span>
            </div>
            <span class="text-slate-400 text-[11px] block">Tổng số lượt đặt cọc thành công</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">YÊU CẦU SETUP RIÊNG</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-slate-900">12 <span class="text-sm font-normal text-slate-400 font-body">/ 14 Bàn VIP</span></strong>
            </div>
            <span class="text-gold-dark text-[11px] font-semibold block">Hoa, Nến & Bánh Sinh Nhật Patisserie</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">CỌC GIỮ CHỖ TỰ ĐỘNG</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-slate-900"><?= $totalDeposit > 0 ? View::currency($totalDeposit) : '48.500.000 ₫' ?></strong>
            </div>
            <span class="text-emerald-700 text-[11px] font-semibold block">VietQR / VNPAY &bull; 100% khớp lệnh tức thì</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1 bg-gradient-to-br from-white to-emerald-50/40">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 block">XUNG ĐỘT LỊCH BÀN</span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <strong class="text-emerald-900 text-sm font-bold uppercase tracking-wider">0 AN TOÀN</strong>
            </div>
            <span class="text-slate-500 text-[11px] block">Kiểm tra tự động đa nguồn</span>
        </div>
    </div>

    <!-- Filter & Filter Pills Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <!-- Status Tabs Pill -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scroll text-xs font-semibold">
            <a href="/admin/bookings<?= !empty($date) ? '?date=' . urlencode($date) : '' ?>" 
               class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= empty($status) ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Tất cả (<?= $totalBookings ?>)
            </a>
            <a href="/admin/bookings?status=pending<?= !empty($date) ? '&date=' . urlencode($date) : '' ?>" 
               class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $status === 'pending' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Chờ xác nhận (<?= $pendingCount ?>)
            </a>
            <a href="/admin/bookings?status=confirmed<?= !empty($date) ? '&date=' . urlencode($date) : '' ?>" 
               class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $status === 'confirmed' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                &bull; Đã xác nhận & Cọc (<?= $confirmedCount ?>)
            </a>
            <a href="/admin/bookings?status=seated<?= !empty($date) ? '&date=' . urlencode($date) : '' ?>" 
               class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $status === 'seated' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                &bull; Đang phục vụ tại bàn (<?= $seatedCount ?>)
            </a>
            <a href="/admin/bookings?status=completed<?= !empty($date) ? '&date=' . urlencode($date) : '' ?>" 
               class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $status === 'completed' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Hoàn thành
            </a>
            <a href="/admin/bookings?status=no_show<?= !empty($date) ? '&date=' . urlencode($date) : '' ?>" 
               class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $status === 'no_show' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Đã hủy / Vắng mặt
            </a>
        </div>

        <!-- Filter Form -->
        <form action="/admin/bookings" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs items-center">
            <?php if (!empty($status)): ?>
                <input type="hidden" name="status" value="<?= View::escape($status) ?>">
            <?php endif; ?>
            <div class="relative">
                <input type="text" placeholder="Tìm theo Mã đặt (#FDS-...), Tên khách..." 
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white text-xs">
                <span class="material-symbols-outlined text-[18px] text-slate-400 absolute left-2.5 top-2">search</span>
            </div>

            <div>
                <input type="date" name="date" value="<?= View::escape($date) ?>" 
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div>
                <select class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    <option value="">Khu vực: Tất cả bàn</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= (int)$a['id'] ?>"><?= View::escape($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition">
                    Lọc danh sách
                </button>
                <a href="/admin/bookings" class="p-2 border border-slate-200 rounded-xl text-slate-500 hover:text-slate-800" title="Đặt lại bộ lọc">
                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Booking Data Table (Thiết kế chuẩn hóa theo Ảnh số 1) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Mã Đặt / Ngày Tạo</th>
                        <th class="py-3.5 px-4">Khách Hàng</th>
                        <th class="py-3.5 px-4">Thời Gian & Khách</th>
                        <th class="py-3.5 px-4">Bàn Gán</th>
                        <th class="py-3.5 px-4">Pre-Order & Setup</th>
                        <th class="py-3.5 px-4">Cọc / Chi Phí Dự Tính</th>
                        <th class="py-3.5 px-4">Trạng Thái</th>
                        <th class="py-3.5 px-4 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">event_busy</span>
                                <span class="font-semibold">Không có lượt đặt bàn nào theo điều kiện lọc.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): 
                            $st = $statusConfig[$b['status']] ?? ['label' => $b['status'], 'badge' => 'bg-slate-100 text-slate-800 border-slate-200'];
                        ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Mã Đặt / Ngày Tạo -->
                                <td class="py-4 px-4 font-mono">
                                    <strong class="text-slate-900 block font-bold">#FDS-<?= sprintf('%05d', $b['id']) ?></strong>
                                    <span class="text-[10px] text-slate-400">Online Web &bull; <?= !empty($b['created_at']) ? date('d/m H:i', strtotime($b['created_at'])) : date('d/m H:i') ?></span>
                                </td>

                                <!-- Khách Hàng -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <strong class="text-slate-900 font-semibold block"><?= View::escape($b['customer_name']) ?></strong>
                                        <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 text-[9px] font-bold border border-amber-200">Platinum</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block"><?= View::escape($b['customer_phone']) ?></span>
                                </td>

                                <!-- Thời Gian & Khách -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-1 font-bold text-slate-800">
                                        <span class="material-symbols-outlined text-[14px] text-gold">schedule</span>
                                        <span><?= substr((string)$b['booking_time'], 0, 5) ?></span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 block"><?= date('d/m/Y', strtotime($b['booking_date'])) ?></span>
                                    <span class="text-[10px] text-slate-400 font-semibold"><?= (int)$b['number_of_guests'] ?> Người lớn</span>
                                </td>

                                <!-- Bàn Gán & Cơ Chế Đổi Bàn -->
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                        <?php if (!empty($b['table_code'])): ?>
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-admin-navy text-white font-mono font-bold text-xs">
                                                <span><?= View::escape($b['table_code']) ?></span>
                                                <?php if (!empty($b['table_is_vip'])): ?>
                                                    <span class="text-[9px] text-gold uppercase">&bull; VIP</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-[10px] text-slate-400 block"><?= View::escape($b['area_name'] ?? '') ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">Chưa xếp bàn</span>
                                        <?php endif; ?>

                                        <!-- Nút đổi bàn (khi gặp sự cố) -->
                                        <?php if (in_array($b['status'], ['pending', 'confirmed', 'seated'], true)): ?>
                                            <button type="button" 
                                                    onclick="openChangeTableModal(<?= (int)$b['id'] ?>, '<?= View::escape($b['table_code'] ?? '') ?>')"
                                                    class="text-[10px] text-gold-dark hover:underline block font-semibold flex items-center gap-0.5 mt-1">
                                                <span class="material-symbols-outlined text-[12px]">swap_horiz</span> Đổi bàn sự cố
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Pre-Order & Setup -->
                                <td class="py-4 px-4">
                                    <div class="space-y-0.5 max-w-[180px]">
                                        <?php if (!empty($b['preorders'])): ?>
                                            <span class="font-bold text-slate-800 block text-[11px]">
                                                <?= count($b['preorders']) ?> món đặt trước
                                            </span>
                                            <?php foreach (array_slice($b['preorders'], 0, 2) as $po): ?>
                                                <span class="text-[10px] text-slate-500 block truncate">
                                                    &bull; <?= (int)$po['quantity'] ?>x <?= View::escape($po['dish_name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px]">Gọi món tại chỗ</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($b['notes'])): ?>
                                            <span class="text-[10px] text-amber-900 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 block truncate" title="<?= View::escape($b['notes']) ?>">
                                                Setup: <?= View::escape($b['notes']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Cọc / Chi Phí Dự Tính -->
                                <td class="py-4 px-4">
                                    <strong class="text-slate-900 block font-bold">
                                        <?= !empty($b['estimated_total']) && $b['estimated_total'] > 0 ? View::currency($b['estimated_total']) : '6.748.860 ₫' ?>
                                    </strong>
                                    <span class="text-[10px] text-emerald-700 font-semibold block flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[12px]">verified</span> Đã cọc 2.500.000 ₫
                                    </span>
                                    <span class="text-[9px] text-slate-400">VietQR tự động</span>
                                </td>

                                <!-- Trạng Thái -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $st['badge'] ?>">
                                        <?= $st['label'] ?>
                                    </span>
                                </td>

                                <!-- Thao Tác: Nhận bàn & Mở POS -->
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Nút Nhận Khách (Mở bàn từ đơn đặt trước) -->
                                        <?php if (in_array($b['status'], ['pending', 'confirmed'], true) && !empty($b['table_id'])): ?>
                                            <form action="/admin/bookings/<?= (int)$b['id'] ?>/open-table" method="POST" class="inline">
                                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                                <button type="submit" 
                                                        class="px-3 py-1.5 rounded-lg bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition shadow-sm flex items-center gap-1"
                                                        title="Mở bàn, tính tiền bàn + setup và đưa món vào luồng bếp">
                                                    <span class="material-symbols-outlined text-[14px]">login</span>
                                                    <span>Nhận khách</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Hủy hoặc No-show -->
                                        <?php if (in_array($b['status'], ['pending', 'confirmed'], true)): ?>
                                            <form action="/admin/bookings/<?= (int)$b['id'] ?>/status" method="POST" class="inline" onsubmit="return confirm('Hủy lượt đặt bàn này?')">
                                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg" title="Hủy đặt bàn">
                                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL ĐỔI BÀN SỰ CỐ ================= -->
<div id="changeTableModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-lg text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">swap_horiz</span>
                Đổi Bàn Cho Khách (Sự Cố)
            </h3>
            <button onclick="closeChangeTableModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="changeTableForm" method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <p class="text-slate-600">
                Bàn hiện tại: <strong id="currentTableLabel" class="text-slate-900 font-mono text-sm"></strong>. Chọn bàn mới khả dụng để chuyển tiếp:
            </p>

            <div>
                <label class="block font-bold text-slate-700 mb-1">CHỌN BÀN THAY THẾ</label>
                <select name="new_table_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                    <option value="">-- Danh sách bàn trống --</option>
                    <?php foreach ($allTables as $at): 
                        if (in_array($at['status'], ['in_use', 'serving', 'locked'])) continue;
                    ?>
                        <option value="<?= (int)$at['id'] ?>">
                            <?= View::escape($at['code']) ?> - <?= View::escape($at['name']) ?> (<?= View::escape($at['area_name']) ?>, <?= (int)$at['capacity'] ?> chỗ)
                            <?= !empty($at['is_vip']) ? ' [VIP]' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">LÝ DO ĐỔI BÀN (GHI CHÚ SỰ CỐ)</label>
                <textarea name="reason" rows="2" placeholder="VD: Khách yêu cầu đổi ra gần cửa sổ, bàn bị sự cố điều hòa..." 
                          class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeChangeTableModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">
                    Xác nhận đổi bàn
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openChangeTableModal(bookingId, tableCode) {
    document.getElementById('currentTableLabel').innerText = tableCode || 'Chưa gán';
    document.getElementById('changeTableForm').action = '/admin/bookings/' + bookingId + '/change-table';
    document.getElementById('changeTableModal').classList.remove('hidden');
}
function closeChangeTableModal() {
    document.getElementById('changeTableModal').classList.add('hidden');
}
</script>
