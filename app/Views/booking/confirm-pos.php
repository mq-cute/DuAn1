<?php
use App\Core\View;

$bookingId = (int) $booking['id'];
$bookingDateTime = strtotime((string) $booking['booking_date'] . ' ' . (string) $booking['booking_time']);
$currency = static fn (float|int|string $amount): string => View::currency((string) (int) round((float) $amount));
?>
<section class="min-h-[70vh] w-full bg-slate-50 px-4 py-8 md:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-amber-700">Đặt bàn · chuyển vào POS</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Xác nhận đặt bàn #<?= $bookingId ?></h1>
                <p class="mt-1 text-sm text-slate-500">Kiểm tra thông tin khách, tiền cọc và hóa đơn tạm tính trước khi mở bàn trên POS.</p>
            </div>
            <a href="/admin/bookings" class="text-sm font-semibold text-slate-600 hover:text-slate-900">← Quay lại quản lý đặt bàn</a>
        </header>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,.8fr)]">
            <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900">Thông tin khách và bàn</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-bold <?= $booking['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>"><?= $booking['status'] === 'pending' ? 'Chờ xác nhận' : 'Đã xác nhận' ?></span>
                </div>
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Khách hàng</dt><dd class="mt-1 font-semibold text-slate-900"><?= View::escape($booking['customer_name']) ?></dd></div>
                    <div><dt class="text-xs text-slate-500">Điện thoại</dt><dd class="mt-1 font-semibold text-slate-900"><?= View::escape($booking['customer_phone']) ?></dd></div>
                    <div><dt class="text-xs text-slate-500">Bàn đặt</dt><dd class="mt-1 font-semibold text-slate-900"><?= View::escape($booking['table_code']) ?> · <?= View::escape($booking['table_name']) ?></dd><dd class="text-xs text-slate-500"><?= View::escape($booking['area_name'] ?? '') ?></dd></div>
                    <div><dt class="text-xs text-slate-500">Ngày giờ</dt><dd class="mt-1 font-semibold text-slate-900"><?= $bookingDateTime === false ? '' : date('d/m/Y H:i', $bookingDateTime) ?></dd></div>
                    <div><dt class="text-xs text-slate-500">Số khách</dt><dd class="mt-1 font-semibold text-slate-900"><?= (int) $booking['number_of_guests'] ?> khách</dd></div>
                    <div><dt class="text-xs text-slate-500">Tiền đã cọc</dt><dd class="mt-1 font-bold text-emerald-700"><?= $currency($booking['deposit_amount'] ?? 0) ?></dd></div>
                </dl>
                <?php if (!empty($booking['notes'])): ?>
                    <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-950"><strong>Ghi chú:</strong> <?= nl2br(View::escape($booking['notes'])) ?></div>
                <?php endif; ?>

                <div>
                    <h2 class="mb-3 text-lg font-bold text-slate-900">Món đặt trước</h2>
                    <?php if ($details === []): ?>
                        <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Khách chưa đặt món trước.</p>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 px-4">
                            <?php foreach ($details as $item): ?>
                                <div class="flex items-start justify-between gap-4 py-3 text-sm">
                                    <div><strong class="text-slate-800"><?= View::escape($item['dish_name']) ?></strong><div class="text-xs text-slate-500"><?= (int) $item['quantity'] ?> × <?= $currency($item['unit_price']) ?></div><?php if (!empty($item['note'])): ?><div class="mt-1 text-xs text-slate-500">Ghi chú: <?= View::escape($item['note']) ?></div><?php endif; ?></div>
                                    <strong class="whitespace-nowrap text-slate-900"><?= $currency((float) $item['unit_price'] * (int) $item['quantity']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                <h2 class="text-lg font-bold text-slate-900">Hóa đơn tạm tính</h2>
                <p class="mb-5 mt-1 text-xs leading-5 text-slate-500">Hóa đơn chính thức được lập khi thanh toán. POS sẽ mở cùng bàn và đơn món sau khi xác nhận.</p>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Tiền món</dt><dd class="font-semibold text-slate-900"><?= $currency($foodSubtotal) ?></dd></div>
                    <?php if ($tableFee > 0): ?><div class="flex justify-between gap-3"><dt class="text-slate-500">Phí bàn</dt><dd class="font-semibold text-slate-900"><?= $currency($tableFee) ?></dd></div><?php endif; ?>
                    <?php if ($setupFee > 0): ?><div class="flex justify-between gap-3"><dt class="text-slate-500">Combo / setup</dt><dd class="font-semibold text-slate-900"><?= $currency($setupFee) ?></dd></div><?php endif; ?>
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-3"><dt class="text-slate-500">Tạm tính</dt><dd class="font-semibold text-slate-900"><?= $currency($subtotal) ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Phí phục vụ (5%)</dt><dd class="font-semibold text-slate-900"><?= $currency($serviceCharge) ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Thuế (8%)</dt><dd class="font-semibold text-slate-900"><?= $currency($tax) ?></dd></div>
                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-4 text-base"><dt class="font-bold text-slate-900">Tổng tạm tính</dt><dd class="font-extrabold text-amber-800"><?= $currency($estimatedTotal) ?></dd></div>
                    <?php if ((float) ($booking['deposit_amount'] ?? 0) > 0): ?><div class="flex justify-between gap-3 text-emerald-700"><dt>Tiền đã cọc</dt><dd class="font-bold"><?= $currency($booking['deposit_amount']) ?></dd></div><?php endif; ?>
                </dl>
                <form action="/admin/bookings/<?= $bookingId ?>/confirm-pos" method="post" class="mt-7">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white shadow-sm transition hover:bg-emerald-800">
                        <span class="material-symbols-outlined">point_of_sale</span>
                        Xác nhận và chuyển vào POS
                    </button>
                </form>
            </aside>
        </div>
    </div>
</section>
