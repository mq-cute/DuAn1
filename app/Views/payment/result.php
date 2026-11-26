<?php
use App\Core\View;

$isSuccess = $isSuccess ?? false;
$orderId = (int)($orderId ?? 0);
$invoiceId = (int)($invoiceId ?? 0);
$amount = (float)($amount ?? 0);
$transactionNo = $transactionNo ?? '';
$gateway = $gateway ?? 'Cổng Thanh Toán Trực Tuyến';
$responseCode = $responseCode ?? '00';
?>

<div class="max-w-2xl mx-auto py-12 px-4">
    <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-sm text-center space-y-6">
        
        <?php if ($isSuccess): ?>
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                <span class="material-symbols-outlined text-[42px]">check_circle</span>
            </div>

            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">GIAO DỊCH THÀNH CÔNG</span>
                <h1 class="text-2xl sm:text-3xl font-headline font-bold text-slate-900">
                    Thanh Toán Hoàn Tất!
                </h1>
                <p class="text-xs text-slate-500">
                    Cảm ơn quý khách đã thanh toán đơn tiệc ẩm thực tại L'Épicure Gastronomie.
                </p>
            </div>

            <!-- Transaction Details Box -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 text-xs space-y-2.5 text-left">
                <div class="flex justify-between">
                    <span class="text-slate-500">Mã đơn hàng:</span>
                    <strong class="text-slate-900 font-bold">#<?= $orderId ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Cổng thanh toán:</span>
                    <span class="font-semibold text-slate-800"><?= View::escape($gateway) ?></span>
                </div>
                <?php if ($transactionNo !== ''): ?>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Mã tham chiếu GD:</span>
                        <span class="font-mono font-bold text-slate-700"><?= View::escape($transactionNo) ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between">
                    <span class="text-slate-500">Thời gian giao dịch:</span>
                    <span class="text-slate-700"><?= date('H:i:s, d/m/Y') ?></span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
                    <span class="font-bold text-slate-800 uppercase">SỐ TIỀN ĐÃ THANH TOÁN:</span>
                    <strong class="text-xl font-headline font-bold text-gold-dark">
                        <?= View::currency(is_numeric($amount) && $amount > 0 ? (string)(int)$amount : '0') ?>
                    </strong>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <?php if ($invoiceId > 0): ?>
                    <a href="/invoices/<?= $invoiceId ?>" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gold text-slate-950 font-bold text-xs hover:opacity-90 shadow-sm transition flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">receipt</span>
                        <span>Xem Hóa Đơn Điện Tử</span>
                    </a>
                    <a href="/invoices/<?= $invoiceId ?>/pdf" target="_blank" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 shadow-sm transition flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                        <span>Tải File PDF</span>
                    </a>
                <?php endif; ?>
                <a href="/account/orders" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-semibold text-xs hover:bg-slate-100 transition flex items-center justify-center gap-1">
                    <span>Lịch sử đơn</span>
                </a>
            </div>

        <?php else: ?>
            <!-- Failure Icon -->
            <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                <span class="material-symbols-outlined text-[42px]">error</span>
            </div>

            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-700">GIAO DỊCH CHƯA HOÀN TẤT</span>
                <h1 class="text-2xl sm:text-3xl font-headline font-bold text-slate-900">
                    Thanh Toán Không Thành Công
                </h1>
                <p class="text-xs text-slate-500">
                    Giao dịch đã bị hủy hoặc có lỗi xảy ra từ phía ngân hàng phát hành (Mã phản hồi: <?= View::escape($responseCode) ?>).
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-center gap-3 pt-4">
                <?php if ($orderId > 0): ?>
                    <a href="/payment/checkout/<?= $orderId ?>" class="px-6 py-3 rounded-xl bg-admin-navy text-gold font-bold text-xs hover:opacity-90 transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">refresh</span>
                        <span>Thử Thanh Toán Lại</span>
                    </a>
                <?php endif; ?>
                <a href="/account/orders" class="px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                    Về danh sách đơn
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>
