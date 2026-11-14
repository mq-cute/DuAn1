<?php

declare(strict_types=1);

use App\Core\View;

$order = $prebill['order'];
$items = $prebill['items'];
?>

<div class="max-w-xl w-full mx-auto p-4 md:p-8 space-y-6">
    <!-- Action buttons (Hidden during printing) -->
    <div class="no-print flex items-center justify-between pb-4 border-b border-outline-variant/30">
        <a href="/staff/pos?table_id=<?= (int) $order['table_id'] ?>" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Quay lại POS</span>
        </a>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center space-x-1.5">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>In Phiếu Tạm Tính</span>
            </button>
        </div>
    </div>

    <!-- Pre-bill Printable Card -->
    <div class="print-area bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/30 shadow-md space-y-6 text-on-surface">
        <div class="text-center space-y-1 pb-4 border-b border-dashed border-outline-variant/40">
            <span class="text-2xl font-headline font-bold text-primary tracking-tight">L'Épicure Gastronomie</span>
            <p class="text-[11px] uppercase tracking-widest text-tertiary-container font-semibold">PHIẾU TẠM TÍNH (PRE-BILL)</p>
            <p class="text-xs text-stone-500">Bàn: <strong><?= View::escape($order['table_code']) ?></strong> • <?= View::escape($order['area_name']) ?></p>
            <p class="text-[10px] text-stone-400">Mã: <?= View::escape($prebill['prebillNumber']) ?> • Thời gian in: <?= date('H:i - d/m/Y', strtotime($prebill['generatedAt'])) ?></p>
        </div>

        <div class="text-xs space-y-1 text-on-surface-variant">
            <div class="flex justify-between">
                <span>Khách hàng:</span>
                <strong class="text-on-surface"><?= View::escape($order['customer_name'] ?: 'Khách tại bàn') ?></strong>
            </div>
            <div class="flex justify-between">
                <span>Nhân viên phục vụ:</span>
                <span><?= View::escape($currentUser['name'] ?? 'Phục vụ bàn') ?></span>
            </div>
            <div class="flex justify-between">
                <span>Giờ vào bàn:</span>
                <span><?= date('H:i', strtotime($order['created_at'])) ?></span>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b border-outline-variant/30 font-bold uppercase text-[10px] text-stone-500">
                    <th class="py-2">Món</th>
                    <th class="py-2 text-center w-12">SL</th>
                    <th class="py-2 text-right w-24">Đơn giá</th>
                    <th class="py-2 text-right w-28">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="py-2.5 pr-2">
                            <span class="font-semibold text-on-surface"><?= View::escape($item['dish_name']) ?></span>
                            <?php if (!empty($item['note'])): ?>
                                <span class="block text-[10px] text-stone-400 italic">Ghi chú: <?= View::escape($item['note']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 text-center font-bold"><?= (int) $item['quantity'] ?></td>
                        <td class="py-2.5 text-right text-stone-600"><?= View::currency($item['unit_price']) ?></td>
                        <td class="py-2.5 text-right font-bold"><?= View::currency($item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="pt-4 border-t border-dashed border-outline-variant/40 space-y-1.5 text-xs text-on-surface-variant">
            <div class="flex justify-between">
                <span>Cộng tiền món:</span>
                <span class="font-semibold text-on-surface"><?= View::currency($prebill['subtotal']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Phí dịch vụ (5%):</span>
                <span class="font-semibold text-on-surface"><?= View::currency($prebill['serviceCharge']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Thuế GTGT (8%):</span>
                <span class="font-semibold text-on-surface"><?= View::currency($prebill['tax']) ?></span>
            </div>
            <div class="flex justify-between pt-2 border-t border-outline-variant/30 text-base font-headline font-bold text-on-surface">
                <span>TỔNG TẠM TÍNH:</span>
                <span class="text-primary font-bold"><?= View::currency($prebill['grandTotal']) ?></span>
            </div>
        </div>

        <div class="pt-4 border-t border-dashed border-outline-variant/30 text-center space-y-1 text-[11px] text-stone-400">
            <p>Phiếu này chưa phải là hóa đơn thanh toán chính thức.</p>
            <p>Quý khách vui lòng kiểm tra lại món trước khi nhân viên xuất hóa đơn.</p>
        </div>
    </div>
</div>
