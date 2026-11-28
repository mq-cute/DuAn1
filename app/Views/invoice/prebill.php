<?php

declare(strict_types=1);

use App\Core\View;

$order = $prebill['order'];
$items = $prebill['items'];
$currency = static fn (mixed $amount): string => View::currency((string) (int) round((float) $amount));
?>

<style>
    .prebill-page { width: 100%; max-width: 760px; margin: 0 auto; padding: 24px; color: #231a11; }
    .prebill-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e4beb8; }
    .prebill-actions a, .prebill-actions button { display: inline-flex; align-items: center; gap: 7px; padding: 10px 14px; border: 1px solid #870000; border-radius: 10px; color: #870000; background: #fff; font: 600 12px "Plus Jakarta Sans", Arial, sans-serif; text-decoration: none; cursor: pointer; }
    .prebill-actions button { color: #fff; background: #870000; }
    .prebill-card { padding: 32px; border: 1px solid #e4beb8; border-radius: 20px; background: #fff; box-shadow: 0 10px 32px rgba(43, 33, 24, .08); }
    .prebill-card table { width: 100%; border-collapse: collapse; }
    .prebill-card th, .prebill-card td { padding: 10px 6px; border-bottom: 1px solid #eee7e2; }
    .prebill-card th { color: #70645f; font-size: 10px; text-transform: uppercase; }
    .prebill-card .prebill-total { color: #870000; font-size: 16px; font-weight: 800; }
    @media (max-width: 600px) { .prebill-page { padding: 14px; } .prebill-card { padding: 18px 14px; } .prebill-actions { align-items: stretch; flex-direction: column; } .prebill-actions a, .prebill-actions button { justify-content: center; } .prebill-card th, .prebill-card td { padding: 8px 3px; font-size: 10px; } }
    @media print {
        @page { size: A4; margin: 14mm; }
        body { min-height: 0 !important; background: #fff !important; }
        body > * { visibility: hidden; }
        .prebill-page, .prebill-page * { visibility: visible; }
        .prebill-page { position: absolute; inset: 0 auto auto 0; width: 100%; max-width: none; margin: 0; padding: 0; }
        .prebill-actions { display: none !important; }
        .prebill-card { padding: 0; border: 0; border-radius: 0; box-shadow: none; }
    }
</style>

<div class="prebill-page">
    <!-- Action buttons (Hidden during printing) -->
    <div class="prebill-actions no-print">
        <?php if (($currentUser['role'] ?? '') === 'staff'): ?>
        <a href="/staff/pos?table_id=<?= (int) $order['table_id'] ?>" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Quay lại POS</span>
        </a>
        <?php endif; ?>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-primary-container shadow-sm transition flex items-center space-x-1.5">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>In Phiếu Tạm Tính</span>
            </button>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <?php $voucherErrors = [
            'voucher_invalid' => 'Mã voucher không tồn tại hoặc đã bị tắt.',
            'voucher_expired' => 'Voucher chưa đến hạn hoặc đã hết hạn.',
            'voucher_unavailable' => 'Voucher đã hết lượt sử dụng.',
            'voucher_minimum' => 'Hóa đơn chưa đạt giá trị tối thiểu của voucher.',
            'csrf_expired' => 'Phiên thao tác đã hết hạn. Vui lòng tải lại trang.',
        ]; ?>
        <div class="no-print mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">
            <?= View::escape($voucherErrors[$error] ?? 'Không thể áp dụng voucher. Vui lòng thử lại.') ?>
        </div>
    <?php elseif (!empty($message)): ?>
        <div class="no-print mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" role="status">
            <?= $message === 'voucher_removed' ? 'Đã gỡ voucher khỏi hóa đơn.' : 'Đã áp dụng voucher vào hóa đơn.' ?>
        </div>
    <?php endif; ?>

    <section class="no-print mb-5 rounded-2xl border border-outline-variant/30 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-bold">Áp dụng voucher cho hóa đơn</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <form action="/staff/orders/<?= (int) ($order['id'] ?? 0) ?>/voucher" method="post" class="flex gap-2">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <select name="voucher_code" class="min-w-0 flex-1 rounded-lg border border-outline-variant/50 px-3 py-2 text-xs">
                    <option value="">Chọn voucher đang hiệu lực</option>
                    <?php foreach (($vouchers ?? []) as $voucher): ?>
                        <option value="<?= View::escape($voucher['code']) ?>" <?= ($prebill['voucherCode'] ?? '') === $voucher['code'] ? 'selected' : '' ?>>
                            <?= View::escape($voucher['title']) ?> · <?= View::escape($voucher['code']) ?>
                            (<?= $voucher['discount_type'] === 'percentage' ? View::escape($voucher['discount_value']) . '%' : View::currency($voucher['discount_value']) ?>)
                            · tối thiểu <?= View::currency($voucher['min_order_amount']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white">Áp dụng</button>
            </form>
            <form action="/staff/orders/<?= (int) ($order['id'] ?? 0) ?>/voucher" method="post" class="flex gap-2">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <input name="voucher_code" maxlength="50" autocomplete="off" placeholder="Nhập mã khách cung cấp" class="min-w-0 flex-1 rounded-lg border border-outline-variant/50 px-3 py-2 text-xs">
                <button type="submit" class="rounded-lg border border-primary px-4 py-2 text-xs font-bold text-primary">Kiểm tra mã</button>
            </form>
        </div>
        <?php if (!empty($prebill['voucherCode'])): ?>
            <form action="/staff/orders/<?= (int) ($order['id'] ?? 0) ?>/voucher" method="post" class="mt-3">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <input type="hidden" name="voucher_code" value="">
                <button type="submit" class="text-xs font-semibold text-red-700 underline">Gỡ voucher</button>
            </form>
        <?php endif; ?>
        <p class="mt-2 text-[11px] text-stone-500">Voucher chỉ giảm tiền hóa đơn cuối cùng. Tiền đặt cọc đã nhận không thay đổi.</p>
    </section>

    <!-- Pre-bill Printable Card -->
    <div class="prebill-card print-area space-y-6 text-on-surface">
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
                <span class="font-semibold text-on-surface"><?= $currency($prebill['subtotal']) ?></span>
            </div>
            <?php if ((float) ($prebill['tableFee'] ?? 0) > 0): ?>
                <div class="flex justify-between"><span>Phí bàn:</span><span class="font-semibold text-on-surface"><?= $currency($prebill['tableFee']) ?></span></div>
            <?php endif; ?>
            <?php if ((float) ($prebill['setupFee'] ?? 0) > 0): ?>
                <div class="flex justify-between"><span>Phí setup:</span><span class="font-semibold text-on-surface"><?= $currency($prebill['setupFee']) ?></span></div>
            <?php endif; ?>
            <?php if ((float) ($prebill['discount'] ?? 0) > 0): ?>
                <div class="flex justify-between text-secondary">
                    <span>Voucher <?= View::escape($prebill['voucherCode'] ?? '') ?>:</span>
                    <span class="font-bold">-<?= $currency($prebill['discount']) ?></span>
                </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <span>Phí dịch vụ (5%):</span>
                <span class="font-semibold text-on-surface"><?= $currency($prebill['serviceCharge']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Thuế GTGT (8%):</span>
                <span class="font-semibold text-on-surface"><?= $currency($prebill['tax']) ?></span>
            </div>
            <?php if (!empty($prebill['depositReceivedAt']) && (float) ($prebill['bookingDepositAmount'] ?? 0) > 0): ?>
                <div class="flex justify-between text-emerald-700">
                    <span>Tiền đặt cọc đã nhận:</span>
                    <span class="font-bold"><?= $currency($prebill['bookingDepositAmount']) ?></span>
                </div>
            <?php elseif ((float) ($prebill['bookingDepositAmount'] ?? 0) > 0): ?>
                <div class="flex justify-between text-amber-700">
                    <span>Tiền cọc đang chờ xác nhận:</span>
                    <span class="font-bold"><?= $currency($prebill['bookingDepositAmount']) ?></span>
                </div>
            <?php endif; ?>
            <div class="flex justify-between pt-2 border-t border-outline-variant/30 text-base font-headline font-bold text-on-surface">
                <span>TỔNG TẠM TÍNH:</span>
                <span class="prebill-total"><?= $currency($prebill['grandTotal']) ?></span>
            </div>
            <?php if ((float) ($prebill['bookingDepositAmount'] ?? 0) > 0): ?>
                <div class="flex justify-between font-semibold text-on-surface">
                    <span>CÒN PHẢI TRẢ<?= (float) ($prebill['depositAmount'] ?? 0) > 0 ? ' (đã trừ cọc nhận được)' : '' ?>:</span>
                    <span><?= $currency($prebill['amountDue']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="pt-4 border-t border-dashed border-outline-variant/30 text-center space-y-1 text-[11px] text-stone-400">
            <p>Phiếu này chưa phải là hóa đơn thanh toán chính thức.</p>
            <p>Quý khách vui lòng kiểm tra lại món trước khi nhân viên xuất hóa đơn.</p>
        </div>
    </div>
</div>
