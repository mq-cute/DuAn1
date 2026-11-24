<?php
use App\Core\View;

$setups = $setups ?? [];
?>

<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>VẬN HÀNH & ĐIỀU PHỐI TIỆC</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Yêu Cầu Setup Riêng & Bàn Tiệc Độc Bản
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kiểm soát danh sách các bàn có yêu cầu trang trí độc bản (sinh nhật, cầu hôn, đối tác VIP) để bộ phận Host và Decor chuẩn bị trước giờ đón khách.
            </p>
        </div>

        <a href="/admin/bookings" class="px-4 py-2.5 rounded-xl bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition flex items-center gap-2 shadow-sm self-start">
            <span class="material-symbols-outlined text-[18px]">calendar_month</span>
            <span>Xem Lịch Đặt Bàn Tổng Thể</span>
        </a>
    </div>

    <!-- Danh sách Setup -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-headline font-bold text-lg text-slate-900">Tiến Độ Chuẩn Bị & Bàn Giao Setup</h3>
                <p class="text-xs text-slate-500">Các lượt đặt bàn yêu cầu hoa tươi, nến thơm, rượu ướp lạnh và decor không gian riêng.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-gold/15 text-gold-dark font-bold text-xs">
                <?= count($setups) ?> Yêu Cầu Setup Đang Theo Dõi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">BÀN & VỊ TRÍ</th>
                        <th class="py-3 px-4">KHÁCH HÀNG & THỜI GIAN</th>
                        <th class="py-3 px-4">GÓI SETUP / CHI TIẾT YÊU CẦU</th>
                        <th class="py-3 px-4">PHÍ SETUP</th>
                        <th class="py-3 px-4">TIẾN ĐỘ CHUẨN BỊ</th>
                        <th class="py-3 px-4 text-right">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($setups)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">event_available</span>
                                Hiện tại không có yêu cầu setup đặc biệt nào cần xử lý.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($setups as $sr): 
                            $isVip = !empty($sr['is_vip']);
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg bg-admin-navy text-gold font-mono font-bold text-xs">
                                            <?= View::escape($sr['table_code']) ?>
                                        </span>
                                        <div>
                                            <strong class="text-slate-900 block"><?= View::escape($sr['table_name']) ?></strong>
                                            <?php if ($isVip): ?>
                                                <span class="text-[9px] text-gold font-bold uppercase tracking-wider">PHÒNG VIP BIỆT LẬP</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <strong class="text-slate-900 block"><?= View::escape($sr['customer_name']) ?></strong>
                                    <span class="text-slate-500 text-[11px]"><?= View::escape($sr['customer_phone']) ?></span>
                                    <div class="text-[10px] text-amber-700 font-bold mt-0.5">
                                        <?= date('d/m/Y', strtotime((string)$sr['booking_date'])) ?> &bull; <?= substr((string)$sr['booking_time'], 0, 5) ?> (<?= (int)$sr['number_of_guests'] ?> khách)
                                    </div>
                                </td>

                                <td class="py-4 px-4 max-w-xs">
                                    <span class="px-2 py-0.5 rounded bg-gold/15 text-gold-dark font-bold text-[10px] uppercase block w-fit mb-1">
                                        <?= View::escape($sr['combo_name'] ?? 'Yêu cầu decor riêng') ?>
                                    </span>
                                    <p class="text-slate-600 text-[11px] leading-relaxed">
                                        <?= View::escape($sr['setup_details'] ?? $sr['notes'] ?? 'Setup nến, hoa tươi cao cấp & rượu vang ướp lạnh.') ?>
                                    </p>
                                </td>

                                <td class="py-4 px-4 font-bold text-gold-dark">
                                    <?= !empty($sr['combo_price']) ? View::currency((string)(int)$sr['combo_price']) : (!empty($sr['table_fee']) ? View::currency((string)(int)$sr['table_fee']) : '450.000 ₫') ?>
                                </td>

                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Đã sẵn sàng trước giờ hẹn</span>
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-right">
                                    <a href="/admin/bookings" class="px-3 py-1.5 rounded-lg bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition">
                                        Chi tiết đơn
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
