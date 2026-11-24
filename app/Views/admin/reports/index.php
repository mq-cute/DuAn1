<?php
use App\Core\View;

$totalRevenue = (float)($totalRevenue ?? 0);
$dailyRevenues = $dailyRevenues ?? [];
$orderRatio = $orderRatio ?? [];
$topDishes = $topDishes ?? [];
$areaStats = $areaStats ?? [];

$completedOrders = (int)($orderRatio['completed_orders'] ?? 0);
$cancelledOrders = (int)($orderRatio['cancelled_orders'] ?? 0);
$totalOrders = (int)($orderRatio['total_orders'] ?? 0);

// Chuẩn bị dữ liệu cho Chart.js
$dates = [];
$totals = [];
foreach ($dailyRevenues as $dr) {
    $dates[] = date('d/m', strtotime((string)$dr['order_date']));
    $totals[] = (float)$dr['daily_total'];
}
?>

<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>TRUNG TÂM PHÂN TÍCH TÀI CHÍNH</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Báo Cáo Doanh Thu & Hiệu Suất Vận Hành
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Thống kê doanh thu thực tế từ các đơn hàng đã hoàn tất, tỷ lệ hoàn tất đơn, món ăn mang lại doanh thu cao nhất và công suất các khu vực.
            </p>
        </div>

        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm self-start">
            <span class="material-symbols-outlined text-[18px]">print</span>
            <span>In Báo Cáo Doanh Thu</span>
        </button>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TỔNG DOANH THU TOÀN HỆ THỐNG</span>
            <div class="text-2xl font-headline font-bold text-gold-dark"><?= View::currency((string)(int)$totalRevenue) ?></div>
            <span class="text-xs text-emerald-700 font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                <span>Tăng trưởng ổn định</span>
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TỔNG ĐƠN HÀNG ĐÃ PHỤC VỤ</span>
            <div class="text-2xl font-headline font-bold text-slate-900"><?= $totalOrders ?> <span class="text-xs font-normal text-slate-400">đơn</span></div>
            <span class="text-xs text-slate-500">Hoàn thành: <strong><?= $completedOrders ?></strong> đơn</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TỶ LỆ HOÀN TẤT ĐƠN HÀNG</span>
            <div class="text-2xl font-headline font-bold text-emerald-800">
                <?= $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 100 ?>%
            </div>
            <span class="text-xs text-rose-600">Đơn hủy: <?= $cancelledOrders ?> đơn</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">GIÁ TRỊ TRUNG BÌNH / ĐƠN</span>
            <div class="text-2xl font-headline font-bold text-admin-navy">
                <?= $completedOrders > 0 ? View::currency((string)(int)($totalRevenue / $completedOrders)) : '0 ₫' ?>
            </div>
            <span class="text-xs text-slate-400">Phân khúc Fine Dining cao cấp</span>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Doanh thu 7 ngày gần nhất (2 spans) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-gold">insights</span>
                    <h3 class="font-headline font-bold text-base text-slate-900">Biểu Đồ Doanh Thu 7 Ngày Gần Nhất</h3>
                </div>
                <span class="text-xs text-slate-400">Đồng bộ tự động từ hóa đơn</span>
            </div>
            
            <div class="h-64 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Tỷ lệ Đơn Hàng & Thống Kê Khu Vực (1 span) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="material-symbols-outlined text-gold">pie_chart</span>
                <h3 class="font-headline font-bold text-base text-slate-900">Tỷ Lệ Đơn Hàng</h3>
            </div>

            <div class="h-44 relative">
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
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span>Đã Hủy / Không Đến:</span>
                    </span>
                    <strong class="text-slate-900"><?= $cancelledOrders ?> đơn</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Chi tiết Doanh Thu Theo Khu Vực & Top 10 Món Bán Chạy -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Doanh thu theo khu vực -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-headline font-bold text-base text-slate-900">Hiệu Suất Theo Khu Vực Ẩm Thực</h3>
                <span class="text-xs text-slate-400">Sảnh tiệc & Phòng VIP</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-200">
                            <th class="py-2.5 px-3">KHU VỰC</th>
                            <th class="py-2.5 px-3">SỐ LƯỢT ĐƠN</th>
                            <th class="py-2.5 px-3 text-right">TỔNG DOANH THU</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($areaStats as $ast): ?>
                            <tr>
                                <td class="py-3 px-3 font-bold text-slate-900"><?= View::escape($ast['area_name']) ?></td>
                                <td class="py-3 px-3 text-slate-600"><?= (int)$ast['total_orders'] ?> đơn</td>
                                <td class="py-3 px-3 text-right font-bold text-gold-dark"><?= View::currency((string)(int)$ast['total_revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Món Bán Chạy Nhất -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-headline font-bold text-base text-slate-900">Top 10 Món Ăn Bán Chạy Nhất</h3>
                <span class="text-xs text-slate-400">Theo sản lượng tiêu thụ</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-200">
                            <th class="py-2.5 px-3">TÊN MÓN</th>
                            <th class="py-2.5 px-3">ĐÃ BÁN</th>
                            <th class="py-2.5 px-3 text-right">DOANH THU MÓN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($topDishes as $td): ?>
                            <tr>
                                <td class="py-3 px-3 font-semibold text-slate-900"><?= View::escape($td['dish_name']) ?></td>
                                <td class="py-3 px-3 font-bold text-admin-navy"><?= (int)$td['total_qty'] ?> suất</td>
                                <td class="py-3 px-3 text-right font-bold text-gold-dark"><?= View::currency((string)(int)$td['total_sales']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Biểu đồ đường doanh thu 7 ngày
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: <?= json_encode($totals) ?>,
                borderColor: '#c5a059',
                backgroundColor: 'rgba(197, 160, 89, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#121820',
                pointBorderColor: '#c5a059',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000000).toFixed(1) + ' Tr';
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 2. Biểu đồ tròn tỷ lệ hoàn tất
    const pieCtx = document.getElementById('orderPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hoàn thành', 'Hủy / No-show'],
            datasets: [{
                data: [<?= $completedOrders ?>, <?= $cancelledOrders ?>],
                backgroundColor: ['#059669', '#e11d48'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
