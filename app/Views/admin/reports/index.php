<?php
use App\Core\View;

$totalRevenue = (float)($totalRevenue ?? 0);
$allTimeRevenue = (float)($allTimeRevenue ?? 0);
$period = $period ?? '7days';
$periodName = $periodName ?? '7 ngày qua';
$startDate = $startDate ?? date('Y-m-d', strtotime('-6 days'));
$endDate = $endDate ?? date('Y-m-d');

$orderRatio = $orderRatio ?? [];
$completedOrders = (int)($orderRatio['completed_orders'] ?? 0);
$inProgressOrders = (int)($orderRatio['in_progress_orders'] ?? 0);
$cancelledOrders = (int)($orderRatio['cancelled_orders'] ?? 0);
$pendingOrders = (int)($orderRatio['pending_orders'] ?? 0);
$totalOrders = (int)($orderRatio['total_orders'] ?? 0);

$topDishes = $topDishes ?? [];
$areaStats = $areaStats ?? [];
$paymentMethodStats = $paymentMethodStats ?? [];

$chartDates = $chartDates ?? [];
$chartTotals = $chartTotals ?? [];
$chartOrderCounts = $chartOrderCounts ?? [];
?>

<div class="space-y-6">
    <!-- Header Controls & Period Selector -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold animate-pulse"></span>
                    <span>TRUNG TÂM PHÂN TÍCH TÀI CHÍNH & VẬN HÀNH</span>
                </span>
                <span class="text-xs text-slate-400">&bull; Chuẩn dữ liệu FDS</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Báo Cáo Doanh Thu & Hiệu Suất
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Đang xem kỳ: <strong class="text-gold-dark font-bold"><?= View::escape($periodName) ?></strong> (<?= date('d/m/Y', strtotime($startDate)) ?> &rarr; <?= date('d/m/Y', strtotime($endDate)) ?>)
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>In Báo Cáo</span>
            </button>
            <button type="button" onclick="exportCsvReport()" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm" title="Xuất dữ liệu định dạng CSV">
                <span class="material-symbols-outlined text-[16px]">file_download</span>
                <span>Xuất CSV</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Quick Period Buttons -->
        <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-semibold text-slate-600 w-full md:w-auto overflow-x-auto">
            <a href="/admin/reports?period=today" class="px-3.5 py-1.5 rounded-lg transition <?= $period === 'today' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'hover:text-slate-900' ?>">
                Hôm nay
            </a>
            <a href="/admin/reports?period=7days" class="px-3.5 py-1.5 rounded-lg transition <?= $period === '7days' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'hover:text-slate-900' ?>">
                7 ngày qua
            </a>
            <a href="/admin/reports?period=30days" class="px-3.5 py-1.5 rounded-lg transition <?= $period === '30days' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'hover:text-slate-900' ?>">
                30 ngày qua
            </a>
            <a href="/admin/reports?period=this_month" class="px-3.5 py-1.5 rounded-lg transition <?= $period === 'this_month' ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'hover:text-slate-900' ?>">
                Tháng này
            </a>
        </div>

        <!-- Custom Date Range Form -->
        <form method="GET" action="/admin/reports" class="flex items-center gap-2 w-full md:w-auto text-xs">
            <input type="hidden" name="period" value="custom">
            <span class="text-slate-400 font-medium whitespace-nowrap">Từ:</span>
            <input type="date" name="from" value="<?= View::escape($startDate) ?>" class="px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-gold bg-slate-50">
            <span class="text-slate-400 font-medium whitespace-nowrap">Đến:</span>
            <input type="date" name="to" value="<?= View::escape($endDate) ?>" class="px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-gold bg-slate-50">
            <button type="submit" class="px-3 py-1.5 bg-admin-navy text-gold text-xs font-bold rounded-lg hover:opacity-95 transition flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">filter_alt</span>
                <span>Lọc</span>
            </button>
        </form>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Doanh thu theo kỳ -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">DOANH THU THEO KỲ</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-gold-dark flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-gold-dark"><?= View::currency((string)(int)$totalRevenue) ?></div>
            <div class="text-xs text-slate-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span>Toàn thời gian:</span>
                <strong class="text-slate-800"><?= View::currency((string)(int)$allTimeRevenue) ?></strong>
            </div>
        </div>

        <!-- Tổng đơn hàng trong kỳ -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TỔNG ĐƠN HÀNG TRONG KỲ</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-slate-900">
                <?= $totalOrders ?> <span class="text-xs font-normal text-slate-400 font-body">đơn tiệc</span>
            </div>
            <div class="text-xs text-slate-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span>Đã hoàn thành:</span>
                <strong class="text-emerald-700"><?= $completedOrders ?> đơn</strong>
            </div>
        </div>

        <!-- Tỷ lệ hoàn tất đơn -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TỶ LỆ HOÀN TẤT ĐƠN</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-emerald-700">
                <?= $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 100 ?>%
            </div>
            <div class="text-xs text-slate-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span class="text-rose-600">Đơn hủy: <?= $cancelledOrders ?></span>
                <span class="text-amber-700">Đang phục vụ: <?= $inProgressOrders ?></span>
            </div>
        </div>

        <!-- Giá trị trung bình đơn (AOV) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">GIÁ TRỊ TRUNG BÌNH (AOV)</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">analytics</span>
                </span>
            </div>
            <div class="text-2xl font-headline font-bold text-admin-navy">
                <?= $completedOrders > 0 ? View::currency((string)(int)($totalRevenue / $completedOrders)) : '0 ₫' ?>
            </div>
            <div class="text-xs text-slate-400 pt-1 border-t border-slate-100">
                <span>Phân khúc ẩm thực Fine Dining</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Biểu đồ đường Doanh thu theo kỳ (2 spans) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-gold">insights</span>
                    <div>
                        <h3 class="font-headline font-bold text-base text-slate-900">Xu Hướng Doanh Thu Theo Ngày</h3>
                        <p class="text-xs text-slate-400">Doanh số thực thu qua các hóa đơn đã thanh toán</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-1 rounded bg-gold"></span> Doanh thu</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-admin-navy"></span> Số lượt đơn</span>
                </div>
            </div>
            
            <div class="h-72 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Biểu đồ tròn Tỷ Lệ Trạng Thái Đơn Hàng (1 span) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="material-symbols-outlined text-gold">pie_chart</span>
                <div>
                    <h3 class="font-headline font-bold text-base text-slate-900">Tỷ Lệ Đơn Hàng</h3>
                    <p class="text-xs text-slate-400">Cơ cấu trạng thái toàn bộ đơn trong kỳ</p>
                </div>
            </div>

            <div class="h-52 relative">
                <canvas id="orderPieChart"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1.5 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <span>Đã Hoàn Thành:</span>
                    </span>
                    <strong class="text-slate-900"><?= $completedOrders ?> đơn</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1.5 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span>Đang Phục Vụ / Bếp:</span>
                    </span>
                    <strong class="text-slate-900"><?= $inProgressOrders ?> đơn</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1.5 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span>Đã Hủy / No-show:</span>
                    </span>
                    <strong class="text-slate-900"><?= $cancelledOrders ?> đơn</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Chi tiết Doanh Thu Theo Khu Vực, Phương Thức & Top 10 Món Bán Chạy -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Top Món Bán Chạy Nhất (2 spans) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-gold">hotel_class</span>
                    <div>
                        <h3 class="font-headline font-bold text-base text-slate-900">Top 10 Món Ăn Bán Chạy Nhất</h3>
                        <p class="text-xs text-slate-400">Xếp hạng theo sản lượng tiêu thụ và tổng doanh số mang lại</p>
                    </div>
                </div>
                <span class="text-xs text-slate-400">Kỳ: <?= View::escape($periodName) ?></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="topDishesTable">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-200">
                            <th class="py-2.5 px-3 w-10">TOP</th>
                            <th class="py-2.5 px-3">TÊN MÓN ĂN</th>
                            <th class="py-2.5 px-3 text-center">SỐ LƯỢNG ĐÃ BÁN</th>
                            <th class="py-2.5 px-3 text-right">DOANH THU MÓN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($topDishes)): ?>
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Chưa ghi nhận dữ liệu bán món trong kỳ này.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topDishes as $idx => $td): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-3">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs <?= $idx === 0 ? 'bg-amber-100 text-amber-800' : ($idx === 1 ? 'bg-slate-200 text-slate-700' : ($idx === 2 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500')) ?>">
                                            <?= $idx + 1 ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-slate-900"><?= View::escape($td['dish_name']) ?></td>
                                    <td class="py-3 px-3 text-center font-bold text-admin-navy">
                                        <span class="px-2.5 py-1 bg-slate-100 rounded-lg"><?= (int)$td['total_qty'] ?> suất</span>
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-gold-dark"><?= View::currency((string)(int)$td['total_sales']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cột Phải: Doanh thu theo khu vực & Phương thức thanh toán (1 span) -->
        <div class="space-y-6">
            
            <!-- Doanh thu theo khu vực -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-headline font-bold text-sm text-slate-900">Hiệu Suất Theo Khu Vực</h3>
                    <span class="text-[11px] text-slate-400">Sảnh tiệc & VIP</span>
                </div>

                <div class="space-y-3">
                    <?php if (empty($areaStats)): ?>
                        <div class="text-xs text-slate-400 text-center py-4">Chưa có dữ liệu.</div>
                    <?php else: ?>
                        <?php foreach ($areaStats as $ast): ?>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                                <div class="flex justify-between items-center text-xs">
                                    <strong class="text-slate-800 font-semibold"><?= View::escape($ast['area_name']) ?></strong>
                                    <span class="text-gold-dark font-bold"><?= View::currency((string)(int)$ast['total_revenue']) ?></span>
                                </div>
                                <div class="flex justify-between items-center text-[11px] text-slate-400">
                                    <span><?= (int)$ast['total_orders'] ?> lượt phục vụ</span>
                                    <span><?= $totalRevenue > 0 ? round(((float)$ast['total_revenue'] / $totalRevenue) * 100, 1) : 0 ?>% tỷ trọng</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-headline font-bold text-sm text-slate-900">Kênh Thanh Toán</h3>
                    <span class="text-[11px] text-slate-400">Đối soát thực tế</span>
                </div>

                <div class="space-y-2 text-xs">
                    <?php
                    $methodLabels = [
                        'cash' => 'Tiền mặt tại bàn',
                        'card' => 'Thẻ tín dụng / POS',
                        'transfer' => 'Chuyển khoản VietQR',
                        'ewallet' => 'Cổng VNPAY / Ví điện tử'
                    ];
                    foreach ($paymentMethodStats as $pms):
                        $mName = $methodLabels[$pms['payment_method']] ?? $pms['payment_method'];
                    ?>
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 bg-slate-50/50">
                            <span class="text-slate-600 font-medium"><?= View::escape($mName) ?> (<?= (int)$pms['count'] ?>)</span>
                            <strong class="text-slate-900 font-bold"><?= View::currency((string)(int)$pms['total_amount']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($paymentMethodStats)): ?>
                        <div class="text-slate-400 text-center py-2">Chưa phát sinh giao dịch trong kỳ.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Biểu đồ đường doanh thu theo kỳ
    const revCanvas = document.getElementById('revenueChart');
    if (revCanvas) {
        const revCtx = revCanvas.getContext('2d');
        const dates = <?= json_encode($chartDates) ?>;
        const totals = <?= json_encode($chartTotals) ?>;
        const counts = <?= json_encode($chartOrderCounts) ?>;

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Doanh thu (VNĐ)',
                        data: totals,
                        borderColor: '#c5a059',
                        backgroundColor: 'rgba(197, 160, 89, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#121820',
                        pointBorderColor: '#c5a059',
                        pointHoverRadius: 6,
                        pointRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Số đơn hoàn tất',
                        data: counts,
                        borderColor: '#121820',
                        backgroundColor: '#121820',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        fill: false,
                        tension: 0.2,
                        pointBackgroundColor: '#121820',
                        pointRadius: 3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return (value / 1000000).toFixed(1) + ' Tr';
                            },
                            font: { size: 10 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.04)' }
                    },
                    y1: {
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
    }

    // 2. Biểu đồ tròn tỷ lệ hoàn tất
    const pieCanvas = document.getElementById('orderPieChart');
    if (pieCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Đang phục vụ / Bếp', 'Đã hủy / No-show'],
                datasets: [{
                    data: [<?= $completedOrders ?>, <?= $inProgressOrders ?>, <?= $cancelledOrders ?>],
                    backgroundColor: ['#059669', '#3b82f6', '#f43f5e'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }
});

// Hàm xuất báo cáo CSV
function exportCsvReport() {
    const table = document.getElementById('topDishesTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.trim().replace(/"/g, '""') + '"');
        }
        csv.push(row.join(','));
    }

    const csvContent = "\uFEFF" + csv.join("\n");
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "bao_cao_doanh_thu_" + "<?= $period ?>" + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
