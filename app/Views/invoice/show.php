<?php

declare(strict_types=1);

use App\Core\View;

$paymentMethodLabels = [
    'cash' => 'Tiền mặt (Cash)',
    'card' => 'Thẻ thanh toán (Credit / Debit Card)',
    'transfer' => 'Chuyển khoản trực tiếp (Bank Transfer)',
    'ewallet' => 'Ví điện tử (E-Wallet)',
];
?>

<div class="max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
    <!-- Action Bar (Not shown in print) -->
    <div class="no-print flex flex-col sm:flex-row items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div class="flex items-center space-x-3">
            <a href="/staff/pos" class="p-2 rounded-xl border border-outline-variant/40 hover:bg-stone-100 text-on-surface-variant" title="Trở về POS">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                        ĐÃ THANH TOÁN
                    </span>
                    <h1 class="text-lg font-headline font-bold text-on-surface">Hóa Đơn #<?= View::escape($invoice['invoice_number']) ?></h1>
                </div>
                <p class="text-xs text-on-surface-variant">Phát hành lúc <?= date('H:i:s - d/m/Y', strtotime($invoice['issued_at'])) ?></p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center space-x-2">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>In Hóa Đơn / Xuất PDF</span>
            </button>
            <a href="/staff/pos?table_id=<?= (int) ($invoice['table_id'] ?? 0) ?>" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 text-on-surface text-xs font-bold hover:bg-stone-50 transition">
                Tiếp tục phục vụ
            </a>
        </div>
    </div>

    <!-- Official Printable Invoice Area -->
    <div class="print-area bg-white rounded-3xl p-8 sm:p-12 border border-outline-variant/30 shadow-md space-y-8">
        <!-- Restaurant Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start border-b border-outline-variant/30 pb-6 gap-6">
            <div class="space-y-1">
                <div class="flex items-baseline space-x-3">
                    <span class="text-3xl font-headline font-bold text-primary tracking-tight">L'Épicure</span>
                    <span class="text-xs font-bold uppercase tracking-widest text-tertiary-container">Gastronomie</span>
                </div>
                <p class="text-xs text-stone-600 font-medium">Ẩm thực Pháp Đương đại &amp; Hầm rượu Thượng hạng</p>
                <p class="text-xs text-on-surface-variant mt-2">
                    Địa chỉ: Số 18 Đại Lộ Lê Duẩn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh<br/>
                    Hotline: (+84) 28 3822 8899 • MST: 0314892341
                </p>
            </div>

            <div class="sm:text-right space-y-1">
                <span class="text-xs uppercase tracking-wider text-outline font-bold">PHIẾU HÓA ĐƠN TÍNH TIỀN</span>
                <div class="text-xl font-headline font-bold text-primary">
                    <?= View::escape($invoice['invoice_number']) ?>
                </div>
                <p class="text-xs text-on-surface-variant">
                    Ngày: <strong><?= date('d/m/Y', strtotime($invoice['issued_at'])) ?></strong><br/>
                    Giờ: <strong><?= date('H:i', strtotime($invoice['issued_at'])) ?></strong>
                </p>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-surface-container-low/50 text-xs text-on-surface-variant border border-outline-variant/20">
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Bàn &amp; Khu vực:</span>
                <strong class="text-on-surface text-sm"><?= View::escape($invoice['table_code'] ?? 'Bàn') ?></strong>
                <span class="block text-[11px]"><?= View::escape($invoice['area_name'] ?? '') ?></span>
            </div>
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Thực khách:</span>
                <strong class="text-on-surface text-sm"><?= View::escape($invoice['customer_name'] ?: 'Khách tại bàn') ?></strong>
            </div>
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Thu ngân / Phục vụ:</span>
                <strong class="text-on-surface text-sm"><?= View::escape($invoice['cashier_name'] ?? $currentUser['name'] ?? 'Nhân viên') ?></strong>
            </div>
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Phương thức:</span>
                <strong class="text-secondary text-sm"><?= View::escape($paymentMethodLabels[$invoice['payment_method']] ?? $invoice['payment_method']) ?></strong>
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b-2 border-outline-variant/40 text-on-surface uppercase font-bold tracking-wider">
                        <th class="py-3 px-2 w-10 text-center">STT</th>
                        <th class="py-3 px-4">Tên món ăn &amp; Đồ uống</th>
                        <th class="py-3 px-3 text-center w-16">SL</th>
                        <th class="py-3 px-4 text-right w-32">Đơn giá</th>
                        <th class="py-3 px-4 text-right w-36">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    <?php $index = 1; ?>
                    <?php foreach ($invoice['items'] as $item): ?>
                        <tr>
                            <td class="py-3 px-2 text-center text-stone-400 font-medium"><?= $index++ ?></td>
                            <td class="py-3 px-4 font-semibold text-on-surface">
                                <?= View::escape($item['dish_name']) ?>
                                <?php if (!empty($item['note'])): ?>
                                    <span class="block text-[10px] text-stone-400 font-normal">Ghi chú: <?= View::escape($item['note']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-on-surface"><?= (int) $item['quantity'] ?></td>
                            <td class="py-3 px-4 text-right text-stone-600"><?= View::currency($item['unit_price']) ?></td>
                            <td class="py-3 px-4 text-right font-bold text-on-surface"><?= View::currency($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary Breakdown -->
        <div class="flex justify-end pt-4 border-t border-outline-variant/30">
            <div class="w-full sm:w-80 space-y-2 text-xs text-on-surface-variant">
                <div class="flex justify-between">
                    <span>Tổng tiền món &amp; rượu:</span>
                    <strong class="text-on-surface"><?= View::currency($invoice['subtotal']) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Phí dịch vụ (5% Service charge):</span>
                    <strong class="text-on-surface"><?= View::currency($invoice['service_charge']) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Thuế GTGT ẩm thực (8% VAT):</span>
                    <strong class="text-on-surface"><?= View::currency($invoice['tax']) ?></strong>
                </div>
                <?php if ((float) $invoice['discount'] > 0): ?>
                    <div class="flex justify-between text-secondary">
                        <span>Giảm giá / Ưu đãi:</span>
                        <strong>-<?= View::currency($invoice['discount']) ?></strong>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between pt-3 border-t-2 border-outline-variant/40 text-base font-headline font-bold text-on-surface">
                    <span>TỔNG THANH TOÁN:</span>
                    <span class="text-primary text-xl font-headline font-bold"><?= View::currency($invoice['total']) ?></span>
                </div>
            </div>
        </div>

        <!-- Footer / QR / Sign Off -->
        <div class="pt-8 border-t border-dashed border-outline-variant/40 text-center space-y-2">
            <p class="font-headline italic text-on-surface text-sm">
                Cảm ơn Quý khách đã trải nghiệm ẩm thực tại L'Épicure Gastronomie!
            </p>
            <p class="text-[11px] text-stone-400">
                Hóa đơn đã được thanh toán đầy đủ và lưu trữ trên hệ thống điện tử.<br/>
                Chúc Quý khách một ngày tuyệt vời và hẹn gặp lại!
            </p>
        </div>
    </div>
</div>
