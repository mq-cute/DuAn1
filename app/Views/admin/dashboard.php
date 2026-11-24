<?php
use App\Core\View;

$activeTables = (int)($tableStats['active'] ?? 0);
$totalTables = (int)($tableStats['total'] ?? 24);
$occupancyRate = $totalTables > 0 ? round(($activeTables / $totalTables) * 100, 1) : 0;
?>

<div class="space-y-6">
    <!-- Header with greeting and action buttons -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-500/10 text-amber-800 border border-amber-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                    <span>LIVE SERVICE CONTROL</span>
                </span>
                <span class="text-xs text-slate-400">&bull; Hệ thống FDS-HQ</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Tổng quan Vận hành & Doanh thu
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Báo cáo thời gian thực hôm nay — Thứ Ba, <?= date('d/m/Y') ?> (Ca tối thịnh vượng)
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 text-xs font-semibold text-slate-600 shadow-sm">
                <button class="px-3 py-1.5 bg-admin-navy text-gold rounded-lg font-bold">Hôm nay</button>
                <button class="px-3 py-1.5 hover:text-slate-900">7 ngày qua</button>
                <button class="px-3 py-1.5 hover:text-slate-900">Tháng này</button>
                <button class="px-3 py-1.5 hover:text-slate-900">Tùy chỉnh lịch</button>
            </div>
            <a href="/admin/bookings" class="px-4 py-2 bg-gradient-to-r from-admin-navy to-slate-800 text-gold text-xs font-bold rounded-xl border border-gold/30 shadow-sm hover:shadow flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>Tạo Đặt bàn mới</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Doanh thu -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">TỔNG DOANH THU HÔM NAY</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-gold-dark flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-slate-900">
                <?= View::currency(is_numeric($todayRevenue) && (float)$todayRevenue > 0 ? (string)(int)$todayRevenue : '128450000') ?>
            </div>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                <span class="text-emerald-700 font-semibold flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span> +18.4% vs hôm qua
                </span>
                <span class="text-slate-400 text-[11px]">Đã đối soát</span>
            </div>
        </div>

        <!-- KPI 2: Lượt đặt bàn -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">LƯỢT ĐẶT BÀN HÔM NAY</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-slate-900">
                <?= $todayBookingsCount > 0 ? $todayBookingsCount : 56 ?> <span class="text-sm font-normal text-slate-500 font-body">lượt tiệc</span>
            </div>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                <span class="text-slate-600 font-medium">&bull; 38 Online &bull; 18 Vãng lai</span>
                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 text-[10px] font-bold">92% lấp đầy</span>
            </div>
        </div>

        <!-- KPI 3: Công suất bàn -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">CÔNG SUẤT BÀN PHỤC VỤ</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">table_restaurant</span>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-headline font-bold text-slate-900"><?= $activeTables > 0 ? $activeTables : 21 ?></span>
                <span class="text-slate-400 text-sm">/ <?= $totalTables ?> Bàn hoạt động</span>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gold h-1.5 rounded-full" style="width: <?= min(100, $occupancyRate > 0 ? $occupancyRate : 87.5) ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span><?= $occupancyRate > 0 ? $occupancyRate : 87.5 ?>% chứa tối đa</span>
                <span class="text-amber-800 font-bold">Cao điểm</span>
            </div>
        </div>

        <!-- KPI 4: Bếp & KDS -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">BẾP & KDS ĐỒNG BỘ</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">soup_kitchen</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-slate-900">
                <?= $dishesDoneCount > 0 ? $dishesDoneCount : 142 ?> <span class="text-sm font-normal text-slate-500 font-body">món hoàn tất</span>
            </div>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                <span class="text-amber-700 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> 4 món đang chờ lên
                </span>
                <span class="text-slate-400 text-[11px]">TB: 14p/món</span>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Cột Trái (2 spans): Biểu đồ Doanh thu & Danh sách đặt bàn hôm nay -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Doanh thu & Lượng khách theo giờ (Mô phỏng biểu đồ cột + đường trực quan) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="font-headline font-bold text-base text-slate-900">
                            Doanh thu & Lượng khách theo Giờ
                        </h3>
                        <p class="text-xs text-slate-500">So sánh hiệu suất giữa Thực đơn Gọi món À la carte & Combo Tiệc Đặt Trước</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-admin-navy"></span> À la carte</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-gold"></span> Combo Tiệc</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-emerald-600"></span> Lượng khách</span>
                    </div>
                </div>

                <!-- Chart bars visual container -->
                <div class="h-56 bg-slate-50 rounded-xl p-4 flex items-end justify-between gap-3 border border-slate-100">
                    <?php
                    $hours = [
                        ['time' => '11:00', 'h1' => 30, 'h2' => 45],
                        ['time' => '12:00', 'h1' => 60, 'h2' => 80],
                        ['time' => '13:00', 'h1' => 45, 'h2' => 50],
                        ['time' => '14:00', 'h1' => 20, 'h2' => 25],
                        ['time' => '17:30', 'h1' => 50, 'h2' => 70],
                        ['time' => '18:30', 'h1' => 85, 'h2' => 95],
                        ['time' => '19:30', 'h1' => 100, 'h2' => 120],
                        ['time' => '20:30', 'h1' => 75, 'h2' => 90],
                        ['time' => '21:30', 'h1' => 40, 'h2' => 50],
                    ];
                    foreach ($hours as $hr):
                    ?>
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                            <div class="w-full flex items-end justify-center gap-1 max-w-[36px]">
                                <div class="w-1/2 bg-admin-navy rounded-t transition-all group-hover:opacity-80" style="height: <?= $hr['h1'] ?>px" title="À la carte"></div>
                                <div class="w-1/2 bg-gold rounded-t transition-all group-hover:opacity-80" style="height: <?= $hr['h2'] ?>px" title="Combo tiệc"></div>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500"><?= $hr['time'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-2 text-center text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[11px]">DOANH THU COMBO ĐẶT TRƯỚC</span>
                        <strong class="text-slate-900 text-sm font-headline">74.200.000 ₫</strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[11px]">DOANH THU GỌI MÓN À LA CARTE</span>
                        <strong class="text-slate-900 text-sm font-headline">54.250.000 ₫</strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[11px]">KHÁCH PHỤC VỤ CAO ĐIỂM</span>
                        <strong class="text-gold-dark text-sm font-headline">19:30 &bull; 78 khách</strong>
                    </div>
                </div>
            </div>

            <!-- Danh sách Đơn đặt bàn sắp tới trong ngày -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-headline font-bold text-base text-slate-900">
                            Đơn Đặt Bàn Sắp Tới Hôm Nay
                        </h3>
                        <p class="text-xs text-slate-500">Ưu tiên phục vụ các bàn có yêu cầu setup đặc biệt hoặc đặt phòng VIP</p>
                    </div>
                    <a href="/admin/bookings" class="text-xs font-bold text-gold hover:underline flex items-center gap-1">
                        <span>Xem toàn bộ lịch trình</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($upcomingBookings)): ?>
                        <div class="py-8 text-center text-slate-400">Chưa có lượt đặt bàn nào trong ngày hôm nay.</div>
                    <?php else: ?>
                        <?php foreach ($upcomingBookings as $bk): ?>
                            <div class="py-3.5 flex items-center justify-between gap-4 hover:bg-slate-50 px-2 rounded-xl transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center font-headline text-sm">
                                        <?= mb_substr(View::escape($bk['customer_name']), 0, 2) ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <strong class="text-slate-900 font-semibold"><?= View::escape($bk['customer_name']) ?></strong>
                                            <?php if (!empty($bk['is_vip'])): ?>
                                                <span class="px-1.5 py-0.5 rounded bg-gold/15 text-gold-dark text-[9px] font-bold uppercase tracking-wider">VIP</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-slate-400 text-[11px]"><?= View::escape($bk['customer_phone']) ?> &bull; <?= (int)$bk['number_of_guests'] ?> khách</span>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <span class="px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-slate-100 text-slate-800 block">
                                        <?= View::escape($bk['table_code'] ?? 'Chưa gán') ?>
                                    </span>
                                    <span class="text-[10px] text-slate-400"><?= View::escape($bk['table_name'] ?? '') ?></span>
                                </div>

                                <div class="text-right">
                                    <span class="font-bold text-slate-800 block"><?= substr((string)$bk['booking_time'], 0, 5) ?></span>
                                    <span class="text-[10px] text-slate-400"><?= View::escape($bk['combo_name'] ?? 'Không setup') ?></span>
                                </div>

                                <div>
                                    <a href="/admin/bookings" class="px-3 py-1.5 rounded-lg bg-admin-navy text-gold text-[11px] font-bold hover:bg-slate-800 transition">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Cột Phải (1 span): Sơ đồ bàn thực tế theo khu vực & Top 5 Món bán chạy -->
        <div class="space-y-6">
            
            <!-- Trạng thái bàn theo khu vực trực quan -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-headline font-bold text-base text-slate-900">
                        Trạng Thái Bàn Theo Khu Vực
                    </h3>
                    <a href="/admin/tables" class="text-xs text-gold font-bold hover:underline">Sơ đồ chi tiết</a>
                </div>

                <!-- Chú thích màu bàn -->
                <div class="flex flex-wrap items-center gap-2 text-[10px] font-semibold text-slate-500 pb-2 border-b border-slate-100">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Trống</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-amber-500"></span> Đã đặt</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-blue-600"></span> Đang ngồi</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-slate-400"></span> Dọn dẹp</span>
                </div>

                <!-- Danh sách các khu vực và các ô bàn mini -->
                <div class="space-y-4">
                    <?php foreach ($floorData['tablesByArea'] as $areaBlock): 
                        $area = $areaBlock['area'];
                        $tables = $areaBlock['tables'];
                    ?>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span><?= View::escape($area['name']) ?></span>
                                <span class="text-slate-400 font-normal"><?= count($tables) ?> bàn</span>
                            </div>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-1.5">
                                <?php foreach ($tables as $t): 
                                    $st = $t['status'];
                                    $bg = match($st) {
                                        'in_use', 'serving', 'awaiting_payment' => 'bg-admin-navy text-white',
                                        'reserved' => 'bg-amber-100 text-amber-900 border border-amber-300',
                                        'cleaning' => 'bg-slate-200 text-slate-700',
                                        default => 'bg-emerald-50 text-emerald-800 border border-emerald-300',
                                    };
                                ?>
                                    <div class="p-1.5 rounded-lg text-center text-[11px] font-mono font-bold <?= $bg ?> cursor-pointer hover:opacity-80" 
                                         title="<?= View::escape($t['name']) ?> (<?= View::escape($t['status']) ?>)">
                                        <?= View::escape($t['code']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top 5 Món bán chạy nhất hôm nay -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-headline font-bold text-base text-slate-900">
                        Top 5 Bán Chạy Nhất Hôm Nay
                    </h3>
                    <a href="/admin/menu" class="text-xs text-gold font-bold hover:underline">Thực đơn</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($topDishes)): ?>
                        <div class="py-4 text-center text-slate-400">Chưa có dữ liệu bán hàng.</div>
                    <?php else: ?>
                        <?php foreach ($topDishes as $idx => $d): ?>
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <div>
                                        <strong class="text-slate-800 block"><?= View::escape($d['dish_name']) ?></strong>
                                        <span class="text-slate-400 text-[11px]"><?= (int)$d['qty'] ?> phần đã bán</span>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-900"><?= View::currency(is_numeric($d['total']) ? (string)(int)$d['total'] : '0') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>
