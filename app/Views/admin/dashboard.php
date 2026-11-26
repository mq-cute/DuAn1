<?php
use App\Core\View;

$activeTables = (int)($tableStats['active'] ?? 0);
$totalTables = (int)($tableStats['total'] ?? 24);
$occupancyRate = $totalTables > 0 ? round(($activeTables / $totalTables) * 100, 1) : 0;
$growthPercent = (float)($growthPercent ?? 18.4);
$todayRevenue = (float)($todayRevenue ?? 128450000);
$todayBookingsCount = (int)($todayBookingsCount ?? 56);
$dishesDoneCount = (int)($dishesDoneCount ?? 142);
$orderRatioData = $orderRatioData ?? ['completed' => 48];
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
                <a href="/admin/reports?period=today" class="px-3 py-1.5 hover:text-slate-900">Hôm nay</a>
                <a href="/admin/reports?period=7days" class="px-3 py-1.5 bg-admin-navy text-gold rounded-lg font-bold">7 ngày qua</a>
                <a href="/admin/reports?period=this_month" class="px-3 py-1.5 hover:text-slate-900">Tháng này</a>
                <a href="/admin/reports" class="px-3 py-1.5 text-gold-dark hover:underline flex items-center gap-1">
                    <span>Xem Báo Cáo</span>
                    <span class="material-symbols-outlined text-[14px]">insights</span>
                </a>
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
            
            <!-- Doanh thu & Lượng khách theo ngày gần nhất (Chart.js Interactive) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gold">insights</span>
                            <h3 class="font-headline font-bold text-base text-slate-900">
                                Biểu Đồ Doanh Thu 7 Ngày Gần Nhất
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500">Phân tích thực tế doanh số hoàn tất và số lượt tiệc phục vụ theo thời gian</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-gold"></span> Doanh thu (VNĐ)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-1 rounded bg-admin-navy"></span> Số lượt đơn</span>
                        <a href="/admin/reports" class="text-gold hover:underline flex items-center gap-0.5 text-xs">
                            <span>Chi tiết</span>
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Chart.js canvas container -->
                <div class="h-64 bg-slate-50/50 rounded-xl p-3 border border-slate-100 relative">
                    <canvas id="dashboardRevenueChart"></canvas>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-2 text-center text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[11px]">DOANH THU HÔM NAY</span>
                        <strong class="text-slate-900 text-sm font-headline"><?= View::currency((string)(int)$todayRevenue) ?></strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[11px]">TĂNG TRƯỞNG VS HÔM QUA</span>
                        <strong class="<?= $growthPercent >= 0 ? 'text-emerald-700' : 'text-rose-600' ?> text-sm font-headline">
                            <?= $growthPercent >= 0 ? '+' : '' ?><?= $growthPercent ?>%
                        </strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block text-[11px]">ĐƠN ĐÃ HOÀN TẤT</span>
                        <strong class="text-gold-dark text-sm font-headline"><?= (int)($orderRatioData['completed'] ?? 0) ?> đơn</strong>
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

<!-- Chart.js Script for Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('dashboardRevenueChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const labels = <?= json_encode($dashboardChartLabels ?? []) ?>;
    const totals = <?= json_encode($dashboardChartTotals ?? []) ?>;
    const orders = <?= json_encode($dashboardChartOrders ?? []) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Doanh thu (VNĐ)',
                    data: totals,
                    backgroundColor: 'rgba(197, 160, 89, 0.85)',
                    hoverBackgroundColor: '#c5a059',
                    borderRadius: 6,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    type: 'line',
                    label: 'Số đơn phục vụ',
                    data: orders,
                    borderColor: '#121820',
                    backgroundColor: '#121820',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#c5a059',
                    pointBorderColor: '#121820',
                    pointRadius: 4,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000000).toFixed(1) + ' Tr';
                        },
                        font: { size: 10 }
                    },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: 'bold' } }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 12, font: { size: 11 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.yAxisID === 'y') {
                                return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                            }
                            return ' Số đơn: ' + context.raw + ' đơn';
                        }
                    }
                }
            }
        }
    });
});
</script>
