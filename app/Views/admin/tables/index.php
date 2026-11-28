<?php
use App\Core\View;

$statusConfig = [
    'empty' => ['label' => 'Bàn Trống', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-300', 'color' => '#10b981'],
    'reserved' => ['label' => 'Đã đặt trước', 'badge' => 'bg-amber-50 text-amber-900 border-amber-300', 'color' => '#f59e0b'],
    'in_use' => ['label' => 'Đang phục vụ', 'badge' => 'bg-admin-navy text-white border-admin-navy', 'color' => '#121820'],
    'serving' => ['label' => 'Đang dùng bữa', 'badge' => 'bg-admin-navy text-white border-admin-navy', 'color' => '#121820'],
    'awaiting_payment' => ['label' => 'Chờ TT', 'badge' => 'bg-orange-50 text-orange-900 border-orange-300', 'color' => '#f97316'],
    'cleaning' => ['label' => 'Đang dọn dẹp', 'badge' => 'bg-slate-200 text-slate-700 border-slate-300', 'color' => '#94a3b8'],
    'locked' => ['label' => 'Tạm khóa', 'badge' => 'bg-gray-100 text-gray-700 border-gray-300', 'color' => '#6b7280'],
];

$emptyCount = (int)($counts['empty'] ?? 0);
$reservedCount = (int)($counts['reserved'] ?? 0);
$inUseCount = (int)($counts['in_use'] ?? 0) + (int)($counts['serving'] ?? 0) + (int)($counts['awaiting_payment'] ?? 0);
$cleaningCount = (int)($counts['cleaning'] ?? 0);
$lockedCount = (int)($counts['locked'] ?? 0);

$allTables = $allTables ?? [];
$areas = $areas ?? [];
$tablesByArea = $tablesByArea ?? [];
$setupRequests = $setupRequests ?? [];

// Phân nhóm bàn theo tầng / khu vực
$floor1Tables = [];
$floor2Tables = [];

foreach ($allTables as $t) {
    $areaName = mb_strtolower((string)($t['area_name'] ?? ''));
    if (str_contains($areaName, 'vip') || str_contains($areaName, 'hầm rượu') || str_contains($areaName, 'lounge')) {
        $floor2Tables[] = $t;
    } else {
        $floor1Tables[] = $t;
    }
}
?>

<div class="space-y-6">
    <!-- Notifications / Alerts -->
    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl text-xs font-semibold flex items-center justify-between <?= in_array($message, ['table_created', 'table_updated', 'table_deleted', 'area_created', 'area_updated', 'area_deleted', 'status_updated', 'vip_updated']) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">
                    <?= in_array($message, ['table_created', 'table_updated', 'table_deleted', 'area_created', 'area_updated', 'area_deleted', 'status_updated', 'vip_updated']) ? 'check_circle' : 'error' ?>
                </span>
                <span>
                    <?php
                    switch ($message) {
                        case 'table_created': echo 'Thêm bàn mới thành công.'; break;
                        case 'table_updated': echo 'Cập nhật thông tin bàn thành công.'; break;
                        case 'table_deleted': echo 'Đã xóa bàn thành công.'; break;
                        case 'table_in_use': echo 'Không thể xóa bàn đang có khách hoặc có lịch đặt trước!'; break;
                        case 'table_code_exists': echo 'Mã bàn đã tồn tại, vui lòng chọn mã khác!'; break;
                        case 'area_created': echo 'Thêm khu vực mới thành công.'; break;
                        case 'area_updated': echo 'Cập nhật khu vực thành công.'; break;
                        case 'area_deleted': echo 'Đã xóa khu vực thành công.'; break;
                        case 'area_has_tables': echo 'Không thể xóa khu vực đang chứa bàn ăn! Vui lòng chuyển hoặc xóa các bàn trước.'; break;
                        case 'status_updated': echo 'Đã cập nhật trạng thái bàn.'; break;
                        case 'vip_updated': echo 'Cập nhật đặc quyền VIP và giá phụ thu thành công.'; break;
                        case 'invalid_csrf': echo 'Yêu cầu không hợp lệ (hết phiên bảo mật).'; break;
                        default: echo View::escape($message); break;
                    }
                    ?>
                </span>
            </div>
            <a href="/admin/tables" class="text-slate-400 hover:text-slate-600">&times;</a>
        </div>
    <?php endif; ?>

    <!-- Header Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>HỆ THỐNG ĐIỀU PHỐI KHÔNG GIAN THỰC THỜI</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Sơ đồ Bàn ăn & Điều phối Không gian Thực tế
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Mô phỏng mặt bằng trực quan theo tầng, kiểm soát trạng thái phục vụ và thao tác nhanh chóng, chính xác.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openAddTableModal()" class="px-3.5 py-2 rounded-xl bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>+ Thêm Bàn</span>
            </button>

            <button type="button" onclick="openAddAreaModal()" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">domain_add</span>
                <span>+ Thêm Khu Vực</span>
            </button>

            <a href="/admin/tables" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm" title="Làm mới trạng thái">
                <span class="material-symbols-outlined text-[16px]">refresh</span>
                <span>Làm mới Layout</span>
            </a>
        </div>
    </div>

    <details class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <summary class="cursor-pointer text-sm font-bold text-slate-800">Quản lý loại phòng & phòng</summary>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
            <form action="/admin/rooms" method="post" class="space-y-3">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold mb-1">Loại phòng</label>
                        <select name="room_type_id" required class="w-full border rounded-lg p-2 text-sm">
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id'] ?>"><?= View::escape($area['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1">Mã phòng</label>
                        <input name="code" required maxlength="30" class="w-full border rounded-lg p-2 text-sm" placeholder="VIP-01">
                    </div>
                </div>
                <label class="block text-xs font-bold">Tên phòng</label>
                <input name="name" required maxlength="150" class="w-full border rounded-lg p-2 text-sm" placeholder="Phòng VIP 01">
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="is_active" value="1" checked> Đang hoạt động</label>
                <button class="px-3 py-2 rounded-lg bg-admin-navy text-gold text-xs font-bold" type="submit">Thêm phòng</button>
            </form>
            <div>
                <h3 class="text-xs font-bold uppercase text-slate-500 mb-2">Danh sách phòng</h3>
                <div class="space-y-2 max-h-64 overflow-auto">
                    <?php foreach (($rooms ?? []) as $room): ?>
                        <form action="/admin/rooms/<?= (int) $room['id'] ?>/edit" method="post" class="grid grid-cols-12 gap-2 items-center border rounded-lg p-2">
                            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                            <input name="code" value="<?= View::escape($room['code']) ?>" class="col-span-3 border rounded p-1 text-xs">
                            <input name="name" value="<?= View::escape($room['name']) ?>" class="col-span-4 border rounded p-1 text-xs">
                            <select name="room_type_id" class="col-span-3 border rounded p-1 text-xs">
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= (int) $area['id'] ?>" <?= (int) $area['id'] === (int) $room['room_type_id'] ? 'selected' : '' ?>><?= View::escape($area['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="col-span-1 text-center text-xs"><input type="checkbox" name="is_active" value="1" <?= (int) $room['is_active'] === 1 ? 'checked' : '' ?>></label>
                            <button class="col-span-1 text-xs font-bold text-admin-navy" type="submit">Lưu</button>
                        </form>
                    <?php endforeach; ?>
                    <?php if (($rooms ?? []) === []): ?><p class="text-xs text-slate-500">Chưa có phòng.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </details>

    <!-- Status KPI Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block">TRỐNG / SẴN SÀNG</span>
                <strong class="text-xl font-headline font-bold text-emerald-800"><?= sprintf('%02d', $emptyCount) ?> <span class="text-xs font-normal text-slate-400 font-body">bàn</span></strong>
            </div>
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block">ĐÃ ĐẶT TRƯỚC</span>
                <strong class="text-xl font-headline font-bold text-amber-700"><?= sprintf('%02d', $reservedCount) ?> <span class="text-xs font-normal text-slate-400 font-body">bàn</span></strong>
            </div>
            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block">ĐANG PHỤC VỤ</span>
                <strong class="text-xl font-headline font-bold text-admin-navy"><?= sprintf('%02d', $inUseCount) ?> <span class="text-xs font-normal text-slate-400 font-body">bàn</span></strong>
            </div>
            <span class="w-3 h-3 rounded-full bg-admin-navy"></span>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block">ĐANG DỌN DẸP</span>
                <strong class="text-xl font-headline font-bold text-slate-600"><?= sprintf('%02d', $cleaningCount) ?> <span class="text-xs font-normal text-slate-400 font-body">bàn</span></strong>
            </div>
            <span class="w-3 h-3 rounded-full bg-slate-400"></span>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block">TẠM KHÓA</span>
                <strong class="text-xl font-headline font-bold text-slate-400"><?= sprintf('%02d', $lockedCount) ?> <span class="text-xs font-normal text-slate-400 font-body">bàn</span></strong>
            </div>
            <span class="w-3 h-3 rounded-full bg-gray-400"></span>
        </div>
    </div>

    <!-- Navigation Tabs: Chuyển Chế Độ & Chọn Tầng Trực Quan -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <!-- Chuyển Tab Chế độ xem -->
        <div class="flex items-center gap-2">
            <button type="button" id="tabMapBtn" onclick="switchViewMode('map')" class="px-4 py-2 font-headline font-bold text-xs rounded-xl bg-admin-navy text-gold shadow-sm flex items-center gap-1.5 transition">
                <span class="material-symbols-outlined text-[17px]">map</span>
                <span>Sơ Đồ Mặt Bằng Tầng</span>
            </button>
            <button type="button" id="tabListBtn" onclick="switchViewMode('list')" class="px-4 py-2 font-headline font-bold text-xs rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 shadow-sm flex items-center gap-1.5 transition">
                <span class="material-symbols-outlined text-[17px]">table_restaurant</span>
                <span>Danh Sách & Quản Lý Bàn</span>
            </button>
        </div>

        <!-- Bộ chọn Tầng 1 / Tầng 2 theo mẫu Ảnh 3 -->
        <div id="floorTabSelector" class="flex items-center bg-white border border-slate-200 rounded-xl p-1 text-xs font-semibold text-slate-600 shadow-sm">
            <button type="button" id="btnFloor1" onclick="switchFloor(1)" class="px-4 py-1.5 rounded-lg font-bold transition bg-admin-navy text-gold shadow-sm">
                Tầng 1: Sảnh Hoàng Gia & Terrace
            </button>
            <button type="button" id="btnFloor2" onclick="switchFloor(2)" class="px-4 py-1.5 rounded-lg font-semibold hover:text-slate-900 transition text-slate-600">
                Tầng 2: VIP Độc Bản & Hầm Rượu
            </button>
        </div>
    </div>

    <!-- TAB 1: SƠ ĐỒ MẶT BẰNG TRỰC QUAN (VISUAL SPATIAL FLOOR PLAN) -->
    <div id="viewMapMode" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Floor Interactive Map Layout (2 spans) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- KHU VỰC TẦNG 1: SẢNH HOÀNG GIA & TERRACE -->
            <div id="floor1Content" class="space-y-6">
                
                <!-- Mặt Bằng Sơ Đồ Không Gian Tầng 1 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                    
                    <!-- Header Mặt bằng Tầng 1 -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="px-3 py-1 rounded-lg bg-admin-navy text-gold font-headline font-bold text-xs uppercase tracking-wider">
                                MẶT BẰNG TẦNG 1
                            </span>
                            <span class="text-xs text-slate-500 font-medium">Sức chứa tối đa: 96 khách (Thời gian thực)</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-400 text-xs">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Trống</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Đã đặt</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-admin-navy"></span> Phục vụ</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Dọn dẹp</span>
                        </div>
                    </div>

                    <!-- Tiện ích cố định: Sân khấu Cello & Bar Sommelier (Ảnh số 3) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl flex items-center gap-3.5 shadow-sm">
                            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-2xl">piano</span>
                            </div>
                            <div>
                                <strong class="text-sm font-headline font-bold text-amber-950 block">Sân khấu Cello & Grand Piano</strong>
                                <span class="text-[11px] text-amber-800 font-medium block mt-0.5">Set biểu diễn: 19:30 – 21:00 (Nghệ sĩ Elena & Trio)</span>
                            </div>
                        </div>

                        <div class="p-4 bg-admin-navy text-white rounded-2xl flex items-center gap-3.5 shadow-sm">
                            <div class="w-11 h-11 rounded-xl bg-slate-800 text-gold flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-2xl">wine_bar</span>
                            </div>
                            <div>
                                <strong class="text-sm font-headline font-bold text-gold block">Bar Sommelier</strong>
                                <span class="text-[11px] text-slate-300 font-medium block mt-0.5">Bespoke Fine Dining Cellar</span>
                            </div>
                        </div>
                    </div>

                    <!-- PHÂN KHU 1: KHU VỰC BÀN TIỆC VIP & TRUNG TÂM -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                                KHU VỰC BÀN TIỆC VIP & TRUNG TÂM
                            </span>
                            <span class="text-[11px] text-gold-dark font-semibold">Ưu tiên trải nghiệm cao cấp</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Bàn VIP-01 (Đang phục vụ / Navy) -->
                            <div class="table-card p-5 rounded-2xl border-2 transition-all cursor-pointer relative bg-admin-navy text-white border-admin-navy hover:shadow-lg"
                                 onclick="selectAdminTableByCode('VIP-01')">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gold text-xl">stars</span>
                                        <span class="font-headline font-bold text-lg text-gold">VIP-01</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gold text-admin-navy uppercase tracking-wider">
                                        Đang phục vụ
                                    </span>
                                </div>

                                <div class="space-y-1.5 text-xs">
                                    <div class="flex justify-between text-slate-300">
                                        <span>Bàn Tròn Lớn (Vòm Đông)</span>
                                        <strong class="text-white">8 - 10 Khách</strong>
                                    </div>
                                    <div class="text-[11px] text-slate-300 bg-slate-800/80 p-2.5 rounded-xl border border-slate-700 space-y-1">
                                        <div class="flex items-center justify-between font-bold text-gold">
                                            <span>Đoàn Doanh nhân Tech Summit</span>
                                            <span>Đã dùng: 55p</span>
                                        </div>
                                        <p class="text-slate-400 text-[10px]">Menu Omakase 7 món &bull; Quản gia riêng phục vụ</p>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-slate-800 flex items-center justify-between text-[11px]">
                                    <span class="text-gold font-bold">Phụ thu: 500.000 ₫</span>
                                    <span class="text-slate-400 underline">Xem chi tiết &rarr;</span>
                                </div>
                            </div>

                            <!-- Bàn VIP-02 (Đã đặt / Vàng Gold Nhạt) -->
                            <div class="table-card p-5 rounded-2xl border-2 transition-all cursor-pointer relative bg-amber-50/90 text-amber-950 border-amber-300 hover:shadow-lg"
                                 onclick="selectAdminTableByCode('VIP-02')">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-amber-700 text-xl">event_seat</span>
                                        <span class="font-headline font-bold text-lg text-amber-900">VIP-02</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 uppercase tracking-wider">
                                        Đã đặt 19:00
                                    </span>
                                </div>

                                <div class="space-y-1.5 text-xs">
                                    <div class="flex justify-between text-amber-900">
                                        <span>Bàn Vuông Sang Trọng</span>
                                        <strong>4 Khách</strong>
                                    </div>
                                    <div class="text-[11px] text-amber-950 bg-white/80 p-2.5 rounded-xl border border-amber-200 space-y-1">
                                        <div class="flex items-center justify-between font-bold text-amber-800">
                                            <span>Kỷ niệm ngày cưới (Anh Quân)</span>
                                            <span class="text-emerald-700">&bull; Đã setup</span>
                                        </div>
                                        <p class="text-amber-800/80 text-[10px]">Moet Champagne + Hoa hồng tươi Ecuador</p>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-amber-200 flex items-center justify-between text-[11px]">
                                    <span class="text-gold-dark font-bold">Phụ thu: 500.000 ₫</span>
                                    <span class="text-amber-800 underline">Xem chi tiết &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PHÂN KHU 2: KHU VỰC SẢNH CHÍNH (MAIN DINING HALL) -->
                    <div class="space-y-3 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                                KHU VỰC SẢNH CHÍNH (MAIN DINING HALL)
                            </span>
                            <span class="text-[11px] text-slate-500 font-semibold">8 Bàn Tiêu Chuẩn</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                            <?php 
                            // Lọc các bàn T-01 đến T-08
                            foreach ($allTables as $t): 
                                if (!str_starts_with($t['code'], 'T-')) continue;
                                $st = $t['status'];
                                $isServing = ($st === 'in_use' || $st === 'serving');
                                $isReserved = ($st === 'reserved');
                                $isCleaning = ($st === 'cleaning');
                            ?>
                                <div class="table-card p-3.5 rounded-2xl border transition-all cursor-pointer hover:shadow-md flex flex-col justify-between h-32 relative
                                     <?= $isServing ? 'bg-admin-navy text-white border-admin-navy shadow-sm' : ($isReserved ? 'bg-amber-50/90 text-amber-950 border-amber-300' : ($isCleaning ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-white text-slate-800 border-slate-200 hover:border-gold/60')) ?>"
                                     onclick="selectAdminTable(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)"
                                     id="card-table-<?= (int)$t['id'] ?>">
                                    
                                    <div class="flex items-center justify-between">
                                        <strong class="font-mono text-sm <?= $isServing ? 'text-white' : 'text-slate-900' ?>">
                                            <?= View::escape($t['code']) ?>
                                        </strong>
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?= $statusConfig[$st]['color'] ?? '#94a3b8' ?>"></span>
                                    </div>

                                    <div>
                                        <span class="text-xs font-semibold block truncate <?= $isServing ? 'text-slate-200' : 'text-slate-800' ?>">
                                            <?= View::escape($t['name']) ?>
                                        </span>
                                        <span class="text-[10px] <?= $isServing ? 'text-slate-400' : 'text-slate-500' ?>">
                                            <?= (int)$t['capacity'] ?> ghế ngồi
                                        </span>
                                    </div>

                                    <div class="pt-1.5 border-t <?= $isServing ? 'border-slate-800 text-gold' : 'border-slate-100 text-slate-500' ?> flex items-center justify-between text-[10px]">
                                        <span class="font-bold">
                                            <?= $isServing ? 'Đang phục vụ' : ($isReserved ? 'Đã đặt trước' : ($isCleaning ? 'Đang dọn' : 'Trống')) ?>
                                        </span>
                                        <span class="text-[9px] opacity-75">Chi tiết &bull;</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- PHÂN KHU 3: BAN CÔNG TERRACE & SÂN VƯỜN -->
                    <div class="space-y-3 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                                KHU VỰC BAN CÔNG TERRACE (SKY VIEW & GARDEN)
                            </span>
                            <span class="text-[11px] text-slate-500 font-semibold">6 Bàn Ngoài Trời</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                            <?php 
                            // Lọc các bàn B-01 đến B-06
                            foreach ($allTables as $t): 
                                if (!str_starts_with($t['code'], 'B-')) continue;
                                $st = $t['status'];
                                $isServing = ($st === 'in_use' || $st === 'serving');
                                $isReserved = ($st === 'reserved');
                            ?>
                                <div class="table-card p-3.5 rounded-2xl border transition-all cursor-pointer hover:shadow-md flex flex-col justify-between h-28
                                     <?= $isServing ? 'bg-admin-navy text-white border-admin-navy' : ($isReserved ? 'bg-amber-50/90 text-amber-950 border-amber-300' : 'bg-white text-slate-800 border-slate-200 hover:border-gold/60') ?>"
                                     onclick="selectAdminTable(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)"
                                     id="card-table-<?= (int)$t['id'] ?>">
                                    
                                    <div class="flex items-center justify-between">
                                        <strong class="font-mono text-xs"><?= View::escape($t['code']) ?></strong>
                                        <span class="w-2 h-2 rounded-full" style="background-color: <?= $statusConfig[$st]['color'] ?? '#94a3b8' ?>"></span>
                                    </div>
                                    
                                    <div>
                                        <span class="text-[11px] font-semibold block truncate"><?= View::escape($t['name']) ?></span>
                                        <span class="text-[10px] text-slate-400"><?= (int)$t['capacity'] ?> ghế ngồi</span>
                                    </div>

                                    <div class="pt-1 border-t <?= $isServing ? 'border-slate-800 text-gold' : 'border-slate-100 text-slate-500' ?> flex items-center justify-between text-[10px]">
                                        <span class="font-bold"><?= $statusConfig[$st]['label'] ?? $st ?></span>
                                        <span>&bull;</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KHU VỰC TẦNG 2: PHÒNG VIP ĐỘC BẢN & HẦM RƯỢU SOMMELIER -->
            <div id="floor2Content" class="hidden space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="px-3 py-1 rounded-lg bg-gold text-admin-navy font-headline font-bold text-xs uppercase tracking-wider">
                                MẶT BẰNG TẦNG 2 (VIP SUITES & CELLAR)
                            </span>
                            <span class="text-xs text-slate-500 font-medium">Khu biệt lập &bull; Quản gia riêng biệt</span>
                        </div>
                    </div>

                    <!-- Danh sách Phòng VIP Tầng 2 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php 
                        foreach ($allTables as $t): 
                            if (!str_starts_with($t['code'], 'VIP-') && !str_starts_with($t['code'], 'BAR-') && !str_starts_with($t['code'], 'LOUNGE-')) continue;
                            if ($t['code'] === 'VIP-01' || $t['code'] === 'VIP-02') continue; // Đã ở tầng 1
                            $st = $t['status'];
                            $fee = !empty($t['table_fee']) ? (float)$t['table_fee'] : 0;
                        ?>
                            <div class="table-card p-4 rounded-2xl border-2 transition-all cursor-pointer bg-white text-slate-800 border-slate-200 hover:border-gold hover:shadow-md"
                                 onclick="selectAdminTable(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)"
                                 id="card-table-<?= (int)$t['id'] ?>">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gold text-lg">star</span>
                                        <strong class="font-mono text-sm"><?= View::escape($t['code']) ?></strong>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $statusConfig[$st]['badge'] ?? 'bg-slate-100' ?>">
                                        <?= $statusConfig[$st]['label'] ?? $st ?>
                                    </span>
                                </div>
                                <h4 class="font-headline font-bold text-sm text-slate-900"><?= View::escape($t['name']) ?></h4>
                                <p class="text-xs text-slate-500 mt-1"><?= (int)$t['capacity'] ?> khách &bull; Phụ thu: <?= $fee > 0 ? View::currency((string)(int)$fee) : 'Miễn phí' ?></p>
                                <p class="text-[11px] text-slate-600 bg-slate-50 p-2 rounded-xl mt-2 line-clamp-2"><?= View::escape($t['vip_privileges'] ?? 'Đặc quyền phòng VIP biệt lập.') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

        </div>

        <!-- Panel Thao Tác Bàn Nhanh & Chi Tiết Bên Phải (Ảnh số 3) -->
        <div class="space-y-6">
            <div id="tableDetailCard" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5 sticky top-24">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">THAO TÁC BÀN NHANH</span>
                        <h3 id="panelTableCode" class="font-headline font-bold text-xl text-slate-900">Bàn VIP-02</h3>
                    </div>
                    <span id="panelTableBadge" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gold/15 text-gold-dark border border-gold/30 uppercase">
                        ĐÃ ĐẶT TRƯỚC
                    </span>
                </div>

                <!-- Thông tin chi tiết bàn -->
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Sức chứa tiêu chuẩn:</span>
                        <strong id="panelTableCap" class="text-slate-800">4 Khách</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Khu vực:</span>
                        <strong id="panelTableArea" class="text-slate-800">Sảnh Hoàng Gia</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Phụ thu dịch vụ:</span>
                        <strong id="panelTableFee" class="text-gold font-bold">500.000 ₫</strong>
                    </div>
                    <div class="py-1">
                        <span class="text-slate-400 block mb-1">Đặc quyền ưu đãi riêng:</span>
                        <p id="panelTablePrivileges" class="text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100 leading-relaxed text-[11px]">
                            Quản gia riêng phục vụ, Khăn nóng ướp tinh dầu hoa hồng, 01 Đĩa trái cây cao cấp, Hoa tươi trên bàn tiệc.
                        </p>
                    </div>
                </div>

                <!-- FORM CHUYỂN TRẠNG THÁI TỨC THÌ (Ảnh số 3) -->
                <form id="panelStatusForm" action="/admin/tables/1/status" method="POST" class="space-y-3 pt-2 border-t border-slate-100">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    <label class="block text-xs font-bold text-slate-700">CHUYỂN TRẠNG THÁI TỨC THÌ</label>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="submit" name="status" value="in_use" class="p-2.5 rounded-xl bg-admin-navy text-gold font-bold hover:bg-slate-800 transition flex items-center justify-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            <span>Khách Nhận Bàn</span>
                        </button>
                        <button type="submit" name="status" value="cleaning" class="p-2.5 rounded-xl border border-slate-300 bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">cleaning_services</span>
                            <span>Dọn Dẹp</span>
                        </button>
                        <button type="submit" name="status" value="empty" class="p-2.5 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-800 font-bold hover:bg-emerald-100 transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">event_available</span>
                            <span>Đặt Về Trống</span>
                        </button>
                        <button type="submit" name="status" value="locked" class="p-2.5 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 font-bold hover:bg-rose-100 transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">lock</span>
                            <span>Tạm Khóa</span>
                        </button>
                    </div>
                </form>

                <!-- Các nút hành động nâng cao cho bàn -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <button type="button" onclick="openVipModal()" class="w-full py-2.5 rounded-xl border border-gold/40 text-gold-dark font-bold text-xs hover:bg-gold/5 transition flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">stars</span>
                        <span>Cấu hình Giá & Đặc quyền VIP</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="openEditTableModal(currentSelectedTable)" class="py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">edit</span>
                            <span>Sửa Bàn</span>
                        </button>
                        <button type="button" onclick="confirmDeleteTable(currentSelectedTable)" class="py-2 rounded-xl border border-rose-200 text-rose-700 bg-rose-50/50 font-bold text-xs hover:bg-rose-100 transition flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">delete</span>
                            <span>Xóa Bàn</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- TAB 2: DANH SÁCH & QUẢN LÝ BÀN / KHU VỰC (BẢNG QUẢN LÝ TOÀN DIỆN) -->
    <div id="viewListMode" class="hidden space-y-8">
        
        <!-- Bảng 1: Quản lý Danh sách Bàn ăn -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-headline font-bold text-lg text-slate-900">Danh Sách Toàn Bộ Bàn Ăn (Restaurant Tables)</h3>
                    <p class="text-xs text-slate-500">Tạo mới, chỉnh sửa thông tin, giá phụ thu VIP và xóa bàn ăn khỏi hệ thống.</p>
                </div>
                <button type="button" onclick="openAddTableModal()" class="px-4 py-2 rounded-xl bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Thêm Bàn Mới</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                            <th class="py-3 px-4">MÃ BÀN</th>
                            <th class="py-3 px-4">TÊN HIỂN THỊ</th>
                            <th class="py-3 px-4">KHU VỰC</th>
                            <th class="py-3 px-4">SỨC CHỨA</th>
                            <th class="py-3 px-4">LOẠI BÀN</th>
                            <th class="py-3 px-4">PHỤ THU</th>
                            <th class="py-3 px-4">TRẠNG THÁI</th>
                            <th class="py-3 px-4 text-right">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($allTables as $t): 
                            $st = $t['status'];
                            $fee = !empty($t['table_fee']) ? (float)$t['table_fee'] : 0;
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900"><?= View::escape($t['code']) ?></td>
                                <td class="py-3 px-4 font-semibold text-slate-800"><?= View::escape($t['name']) ?></td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap"><?= View::escape($t['area_name'] ?? '') ?></td>
                                <td class="py-3 px-4 font-bold text-slate-700 whitespace-nowrap"><?= (int)$t['capacity'] ?> khách</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php if (!empty($t['is_vip'])): ?>
                                        <span class="px-2 py-0.5 rounded bg-gold text-admin-navy font-bold text-[9px] uppercase tracking-wider shadow-sm">VIP</span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">Tiêu chuẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-bold text-gold-dark whitespace-nowrap">
                                    <?= $fee > 0 ? View::currency((string)(int)$fee) : '0 ₫' ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border whitespace-nowrap <?= $statusConfig[$st]['badge'] ?? 'bg-slate-100 text-slate-700' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: <?= $statusConfig[$st]['color'] ?? '#94a3b8' ?>"></span>
                                        <span><?= $statusConfig[$st]['label'] ?? $st ?></span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <button type="button" onclick="openEditTableModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs">
                                        Sửa
                                    </button>
                                    <button type="button" onclick="confirmDeleteTable(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-xs">
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bảng 2: Quản lý Danh sách Khu vực (Dining Areas) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-headline font-bold text-lg text-slate-900">Danh Sách Khu Vực (Dining Areas)</h3>
                    <p class="text-xs text-slate-500">Quản lý các sảnh tiệc, ban công sân vườn, phòng VIP độc lập của nhà hàng.</p>
                </div>
                <button type="button" onclick="openAddAreaModal()" class="px-4 py-2 rounded-xl bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">domain_add</span>
                    <span>Thêm Khu Vực</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">TÊN KHU VỰC</th>
                            <th class="py-3 px-4">MÔ TẢ KHÔNG GIAN</th>
                            <th class="py-3 px-4">THỨ TỰ</th>
                            <th class="py-3 px-4 text-right">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($areas as $ar): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= (int)$ar['id'] ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= View::escape($ar['name']) ?></td>
                                <td class="py-3 px-4 text-slate-600 max-w-md"><?= View::escape($ar['description'] ?? 'Không gian ẩm thực cao cấp') ?></td>
                                <td class="py-3 px-4 font-mono text-slate-500"><?= (int)($ar['sort_order'] ?? 0) ?></td>
                                <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <button type="button" onclick="openEditAreaModal(<?= htmlspecialchars(json_encode($ar), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs">
                                        Sửa
                                    </button>
                                    <button type="button" onclick="confirmDeleteArea(<?= htmlspecialchars(json_encode($ar), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-xs">
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Danh sách Yêu cầu Setup Độc bản hôm nay (Ảnh số 3 nửa dưới) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">MỤC IV.5 QUY TRÌNH VẬN HÀNH &bull; CA TỐI 18:00 - 22:30</span>
                <h3 class="font-headline font-bold text-lg text-slate-900">
                    Danh Sách Yêu Cầu Setup Độc Bản Hôm Nay
                </h3>
            </div>
            <a href="/admin/setups" class="px-4 py-2 rounded-xl bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">celebration</span>
                <span>Xem Tất Cả Setup</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">BÀN & VỊ TRÍ</th>
                        <th class="py-3 px-4">GÓI SETUP & CHI TIẾT CHUẨN BỊ</th>
                        <th class="py-3 px-4">PHÍ SETUP</th>
                        <th class="py-3 px-4">TIẾN ĐỘ & TRẠNG THÁI</th>
                        <th class="py-3 px-4 text-right">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($setupRequests)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Hôm nay không có yêu cầu setup đặc biệt nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($setupRequests as $sr): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded bg-admin-navy text-gold font-mono mr-1.5"><?= View::escape($sr['table_code']) ?></span>
                                    <span><?= View::escape($sr['table_name']) ?></span>
                                    <span class="text-[11px] text-slate-400 block"><?= substr((string)$sr['booking_time'], 0, 5) ?> &bull; <?= View::escape($sr['customer_name']) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <strong class="text-slate-900 block"><?= View::escape($sr['combo_name'] ?? 'Setup theo yêu cầu riêng') ?></strong>
                                    <p class="text-slate-500 text-[11px] max-w-md mt-0.5"><?= View::escape($sr['setup_details'] ?? $sr['notes'] ?? 'Setup nến, hoa tươi & bàn tiệc lãng mạn.') ?></p>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-gold-dark">
                                    <?= !empty($sr['combo_price']) ? View::currency((string)(int)$sr['combo_price']) : '450.000 ₫' ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200">
                                        &bull; Đã chuẩn bị xong
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="/admin/bookings" class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold">Xem đơn</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODALS QUẢN TRỊ BÀN & KHU VỰC ==================== -->

<!-- Modal 1: Thêm Bàn Ăn Mới -->
<div id="addTableModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">add_circle</span>
                Thêm Bàn Ăn Mới
            </h3>
            <button onclick="closeModal('addTableModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="/admin/tables" method="POST" class="space-y-3.5">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">MÃ BÀN (*)</label>
                    <input type="text" name="code" required placeholder="VD: T-09, VIP-05" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono uppercase font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">KHU VỰC (*)</label>
                    <select name="area_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                        <?php foreach ($areas as $ar): ?>
                            <option value="<?= (int)$ar['id'] ?>"><?= View::escape($ar['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">TÊN HIỂN THỊ (*)</label>
                    <input type="text" name="name" required placeholder="VD: Bàn Cửa Sổ Hướng Tây" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">SỨC CHỨA (GHẾ) (*)</label>
                    <input type="number" name="capacity" min="1" max="50" value="4" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                </div>
            </div>

            <div class="p-3 bg-gold/5 rounded-xl border border-gold/20 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="addIsVip" name="is_vip" value="1" class="rounded border-slate-300 text-gold focus:ring-gold">
                    <label for="addIsVip" class="font-bold text-slate-800">ĐẶT LÀ BÀN VIP / PHÒNG ĐẶC BIỆT</label>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">PHÍ PHỤ THU VIP (VNĐ)</label>
                    <input type="number" name="table_fee" step="10000" min="0" value="0" placeholder="0" class="w-full p-2 bg-white border border-slate-200 rounded-lg font-bold text-gold-dark">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ĐẶC QUYỀN ƯU ĐÃI RIÊNG</label>
                    <textarea name="vip_privileges" rows="2" placeholder="VD: Quản gia riêng, rượu vang khai tiệc..." class="w-full p-2 bg-white border border-slate-200 rounded-lg resize-none"></textarea>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">MÔ TẢ CHI TIẾT</label>
                <textarea name="description" rows="2" placeholder="Ghi chú về góc nhìn cảnh quan, trang thiết bị..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('addTableModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">Thêm Bàn</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Sửa Thông Tin Bàn Ăn -->
<div id="editTableModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">edit</span>
                Chỉnh Sửa Thông Tin Bàn Ăn
            </h3>
            <button onclick="closeModal('editTableModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form id="editTableForm" action="" method="POST" class="space-y-3.5">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">MÃ BÀN (*)</label>
                    <input type="text" id="editTableCode" name="code" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono uppercase font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">KHU VỰC (*)</label>
                    <select id="editTableAreaId" name="area_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                        <?php foreach ($areas as $ar): ?>
                            <option value="<?= (int)$ar['id'] ?>"><?= View::escape($ar['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">TÊN HIỂN THỊ (*)</label>
                    <input type="text" id="editTableName" name="name" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">SỨC CHỨA (GHẾ) (*)</label>
                    <input type="number" id="editTableCap" name="capacity" min="1" max="50" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                </div>
            </div>

            <div class="p-3 bg-gold/5 rounded-xl border border-gold/20 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="editIsVip" name="is_vip" value="1" class="rounded border-slate-300 text-gold focus:ring-gold">
                    <label for="editIsVip" class="font-bold text-slate-800">ĐẶT LÀ BÀN VIP / PHÒNG ĐẶC BIỆT</label>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">PHÍ PHỤ THU VIP (VNĐ)</label>
                    <input type="number" id="editTableFee" name="table_fee" step="10000" min="0" class="w-full p-2 bg-white border border-slate-200 rounded-lg font-bold text-gold-dark">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ĐẶC QUYỀN ƯU ĐÃI RIÊNG</label>
                    <textarea id="editTablePrivileges" name="vip_privileges" rows="2" class="w-full p-2 bg-white border border-slate-200 rounded-lg resize-none"></textarea>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">MÔ TẢ CHI TIẾT</label>
                <textarea id="editTableDesc" name="description" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('editTableModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Xác nhận Xóa Bàn -->
<div id="deleteTableModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center gap-2 text-rose-600 font-bold text-sm">
            <span class="material-symbols-outlined">warning</span>
            <span>Xác nhận xóa bàn ăn?</span>
        </div>
        <p class="text-slate-600 leading-relaxed">
            Bạn có chắc muốn xóa bàn <strong id="deleteTableCodeText" class="font-mono text-slate-900"></strong>? Thao tác này không thể hoàn tác nếu bàn không có giao dịch dở dang.
        </p>
        <form id="deleteTableForm" action="" method="POST" class="flex justify-end gap-2 pt-2">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <button type="button" onclick="closeModal('deleteTableModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
            <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700 transition">Xóa vĩnh viễn</button>
        </form>
    </div>
</div>

<!-- Modal 4: Thêm Khu Vực Mới -->
<div id="addAreaModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">domain_add</span>
                Thêm Khu Vực Mới
            </h3>
            <button onclick="closeModal('addAreaModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="/admin/dining-areas" method="POST" class="space-y-3.5">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">TÊN KHU VỰC (*)</label>
                <input type="text" name="name" required placeholder="VD: Sảnh Hoàng Gia, Ban Công Terrace..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">THỨ TỰ HIỂN THỊ</label>
                <input type="number" name="sort_order" value="1" min="0" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">MÔ TẢ KHÔNG GIAN</label>
                <textarea name="description" rows="3" placeholder="Không gian sang trọng, hướng view trực diện thành phố..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('addAreaModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">Thêm Khu Vực</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Sửa Khu Vực -->
<div id="editAreaModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">edit</span>
                Chỉnh Sửa Khu Vực
            </h3>
            <button onclick="closeModal('editAreaModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form id="editAreaForm" action="" method="POST" class="space-y-3.5">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">TÊN KHU VỰC (*)</label>
                <input type="text" id="editAreaName" name="name" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">THỨ TỰ HIỂN THỊ</label>
                <input type="number" id="editAreaSort" name="sort_order" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">MÔ TẢ KHÔNG GIAN</label>
                <textarea id="editAreaDesc" name="description" rows="3" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('editAreaModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">Lưu Khu Vực</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 6: Xác nhận Xóa Khu Vực -->
<div id="deleteAreaModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center gap-2 text-rose-600 font-bold text-sm">
            <span class="material-symbols-outlined">warning</span>
            <span>Xác nhận xóa khu vực?</span>
        </div>
        <p class="text-slate-600 leading-relaxed">
            Bạn có chắc muốn xóa khu vực <strong id="deleteAreaNameText" class="text-slate-900"></strong>? Lưu ý khu vực chỉ có thể xóa khi không còn bàn nào trực thuộc.
        </p>
        <form id="deleteAreaForm" action="" method="POST" class="flex justify-end gap-2 pt-2">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <button type="button" onclick="closeModal('deleteAreaModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
            <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700 transition">Xóa khu vực</button>
        </form>
    </div>
</div>

<!-- Modal 7: Chỉnh sửa Giá bàn & Đặc quyền VIP -->
<div id="vipConfigModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">stars</span>
                Cấu hình Bàn VIP & Phụ Thu
            </h3>
            <button onclick="closeModal('vipConfigModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form id="vipConfigForm" action="" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div class="flex items-center gap-2">
                <input type="checkbox" id="modalIsVip" name="is_vip" value="1" class="rounded border-slate-300 text-gold focus:ring-gold">
                <label for="modalIsVip" class="font-bold text-slate-800">ĐÁNH DẤU LÀ BÀN VIP / PHÒNG ĐẶC BIỆT</label>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">PHÍ PHỤ THU BÀN / PHÒNG (VNĐ)</label>
                <input type="number" id="modalTableFee" name="table_fee" step="10000" min="0" placeholder="VD: 500000"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-gold-dark">
                <span class="text-[11px] text-slate-400 mt-1 block">Chi phí này sẽ tự động tính vào hóa đơn khi mở bàn VIP.</span>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">ĐẶC QUYỀN ƯU ĐÃI RIÊNG CỦA BÀN</label>
                <textarea id="modalPrivileges" name="vip_privileges" rows="3" placeholder="VD: Quản gia riêng phục vụ, 01 Đĩa trái cây cao cấp, Hoa tươi trên bàn..." 
                          class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('vipConfigModal')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">Lưu cấu hình</button>
            </div>
        </form>
    </div>
</div>

<!-- JAVASCRIPT ĐIỀU PHỐI GIAO DIỆN & TƯƠNG TÁC BÀN NHANH -->
<script>
let currentSelectedTable = null;
const allTablesData = <?= json_encode($allTables) ?>;

function switchViewMode(mode) {
    const mapTab = document.getElementById('viewMapMode');
    const listTab = document.getElementById('viewListMode');
    const tabMapBtn = document.getElementById('tabMapBtn');
    const tabListBtn = document.getElementById('tabListBtn');
    const floorTabSelector = document.getElementById('floorTabSelector');

    if (mode === 'map') {
        mapTab.classList.remove('hidden');
        listTab.classList.add('hidden');
        tabMapBtn.className = 'px-4 py-2 font-headline font-bold text-xs rounded-xl bg-admin-navy text-gold shadow-sm flex items-center gap-1.5 transition';
        tabListBtn.className = 'px-4 py-2 font-headline font-bold text-xs rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 shadow-sm flex items-center gap-1.5 transition';
        floorTabSelector.classList.remove('hidden');
    } else {
        mapTab.classList.add('hidden');
        listTab.classList.remove('hidden');
        tabListBtn.className = 'px-4 py-2 font-headline font-bold text-xs rounded-xl bg-admin-navy text-gold shadow-sm flex items-center gap-1.5 transition';
        tabMapBtn.className = 'px-4 py-2 font-headline font-bold text-xs rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 shadow-sm flex items-center gap-1.5 transition';
        floorTabSelector.classList.add('hidden');
    }
}

function switchFloor(floorNum) {
    const floor1 = document.getElementById('floor1Content');
    const floor2 = document.getElementById('floor2Content');
    const btn1 = document.getElementById('btnFloor1');
    const btn2 = document.getElementById('btnFloor2');

    if (floorNum === 1) {
        floor1.classList.remove('hidden');
        floor2.classList.add('hidden');
        btn1.className = 'px-4 py-1.5 rounded-lg font-bold transition bg-admin-navy text-gold shadow-sm';
        btn2.className = 'px-4 py-1.5 rounded-lg font-semibold hover:text-slate-900 transition text-slate-600';
    } else {
        floor1.classList.add('hidden');
        floor2.classList.remove('hidden');
        btn2.className = 'px-4 py-1.5 rounded-lg font-bold transition bg-admin-navy text-gold shadow-sm';
        btn1.className = 'px-4 py-1.5 rounded-lg font-semibold hover:text-slate-900 transition text-slate-600';
    }
}

function selectAdminTableByCode(code) {
    const found = allTablesData.find(t => t.code === code);
    if (found) {
        selectAdminTable(found);
    }
}

function selectAdminTable(table) {
    currentSelectedTable = table;
    document.getElementById('panelTableCode').innerText = 'Bàn ' + table.code;
    document.getElementById('panelTableArea').innerText = table.area_name || 'Khu vực chung';
    document.getElementById('panelTableCap').innerText = table.capacity + ' Khách';
    
    const fee = parseFloat(table.table_fee || 0);
    document.getElementById('panelTableFee').innerText = fee > 0 ? (new Intl.NumberFormat('vi-VN').format(fee) + ' ₫') : 'Miễn phí';
    document.getElementById('panelTablePrivileges').innerText = table.vip_privileges || (table.is_vip ? 'Đặc quyền phòng VIP biệt lập, quản gia phục vụ riêng.' : 'Tiêu chuẩn phục vụ thượng hạng.');
    
    // Badge trạng thái
    const badge = document.getElementById('panelTableBadge');
    if (table.status === 'in_use' || table.status === 'serving') {
        badge.innerText = 'ĐANG PHỤC VỤ';
        badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-admin-navy text-gold border border-gold/30 uppercase';
    } else if (table.status === 'reserved') {
        badge.innerText = 'ĐÃ ĐẶT TRƯỚC';
        badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 uppercase';
    } else if (table.status === 'cleaning') {
        badge.innerText = 'ĐANG DỌN DẸP';
        badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700 border border-slate-300 uppercase';
    } else if (table.status === 'locked') {
        badge.innerText = 'TẠM KHÓA';
        badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 uppercase';
    } else {
        badge.innerText = 'TRỐNG / SẴN SÀNG';
        badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 uppercase';
    }

    document.getElementById('panelStatusForm').action = '/admin/tables/' + table.id + '/status';
    
    // Modal data setup
    document.getElementById('modalIsVip').checked = !!parseInt(table.is_vip);
    document.getElementById('modalTableFee').value = fee;
    document.getElementById('modalPrivileges').value = table.vip_privileges || '';
    document.getElementById('vipConfigForm').action = '/admin/tables/' + table.id + '/vip';

    // Highlight card
    document.querySelectorAll('.table-card').forEach(el => el.classList.remove('ring-4', 'ring-gold', 'shadow-lg'));
    const currentCard = document.getElementById('card-table-' + table.id);
    if (currentCard) {
        currentCard.classList.add('ring-4', 'ring-gold', 'shadow-lg');
    }
}

// CÁC HÀM MỞ MODALS
function openAddTableModal() {
    document.getElementById('addTableModal').classList.remove('hidden');
}

function openEditTableModal(table) {
    if (!table) return;
    document.getElementById('editTableForm').action = '/admin/tables/' + table.id + '/edit';
    document.getElementById('editTableCode').value = table.code;
    document.getElementById('editTableName').value = table.name;
    document.getElementById('editTableAreaId').value = table.area_id;
    document.getElementById('editTableCap').value = table.capacity;
    document.getElementById('editIsVip').checked = !!parseInt(table.is_vip);
    document.getElementById('editTableFee').value = parseFloat(table.table_fee || 0);
    document.getElementById('editTablePrivileges').value = table.vip_privileges || '';
    document.getElementById('editTableDesc').value = table.description || '';
    document.getElementById('editTableModal').classList.remove('hidden');
}

function confirmDeleteTable(table) {
    if (!table) return;
    document.getElementById('deleteTableCodeText').innerText = table.code + ' (' + table.name + ')';
    document.getElementById('deleteTableForm').action = '/admin/tables/' + table.id + '/delete';
    document.getElementById('deleteTableModal').classList.remove('hidden');
}

function openAddAreaModal() {
    document.getElementById('addAreaModal').classList.remove('hidden');
}

function openEditAreaModal(area) {
    if (!area) return;
    document.getElementById('editAreaForm').action = '/admin/dining-areas/' + area.id + '/edit';
    document.getElementById('editAreaName').value = area.name;
    document.getElementById('editAreaSort').value = area.sort_order || 0;
    document.getElementById('editAreaDesc').value = area.description || '';
    document.getElementById('editAreaModal').classList.remove('hidden');
}

function confirmDeleteArea(area) {
    if (!area) return;
    document.getElementById('deleteAreaNameText').innerText = area.name;
    document.getElementById('deleteAreaForm').action = '/admin/dining-areas/' + area.id + '/delete';
    document.getElementById('deleteAreaModal').classList.remove('hidden');
}

function openVipModal() {
    if (!currentSelectedTable) return;
    document.getElementById('vipConfigModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// Khởi chạy chọn bàn đầu tiên khi load trang
document.addEventListener('DOMContentLoaded', () => {
    if (allTablesData && allTablesData.length > 0) {
        selectAdminTable(allTablesData[0]);
    }
});
</script>
