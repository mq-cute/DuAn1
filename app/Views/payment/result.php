<?php

use App\Core\View;

$isSuccess = $isSuccess ?? false;
$orderId = (int)($orderId ?? 0);
$invoiceId = (int)($invoiceId ?? 0);
$amount = (float)($amount ?? 0);
$transactionNo = (string)($transactionNo ?? '');
$gateway = (string)($gateway ?? 'Cổng thanh toán trực tuyến');
$responseCode = (string)($responseCode ?? '99');
$currentUser = $currentUser ?? null;
$ordersUrl = ($currentUser['role'] ?? '') === 'customer' ? '/account/orders' : '/staff/orders';
?>

<section class="payment-result-page">
    <article class="payment-result-card <?= $isSuccess ? 'is-success' : 'is-failure' ?>">
        <div class="payment-result-brand">
            <span class="payment-result-brand-mark">FOODIES</span>
            <span>HAUTE CUISINE · THANH TOÁN AN TOÀN</span>
        </div>

        <div class="payment-result-symbol" aria-hidden="true">
            <?php if ($isSuccess): ?>
                <svg viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="29" stroke="currentColor" stroke-width="2" />
                    <path d="m19 32 9 9 18-19" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            <?php else: ?>
                <svg viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="29" stroke="currentColor" stroke-width="2" />
                    <path d="M32 18v17m0 9v.1" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" />
                </svg>
            <?php endif; ?>
        </div>

        <p class="payment-result-eyebrow"><?= $isSuccess ? 'GIAO DỊCH THÀNH CÔNG' : 'GIAO DỊCH CHƯA HOÀN TẤT' ?></p>
        <h1><?= $isSuccess ? 'Thanh toán hoàn tất' : 'Chưa thể hoàn tất thanh toán' ?></h1>
        <p class="payment-result-lead">
            <?= $isSuccess
                ? 'Cảm ơn quý khách. Thanh toán đã được xác nhận và hóa đơn đã sẵn sàng.'
                : 'Giao dịch bị hủy hoặc chưa được ngân hàng xác nhận. Quý khách có thể thử lại.' ?>
        </p>

        <div class="payment-result-details">
            <div class="payment-result-row">
                <span>Mã đơn hàng</span>
                <strong>#<?= $orderId ?></strong>
            </div>
            <div class="payment-result-row">
                <span>Cổng thanh toán</span>
                <strong><?= View::escape($gateway) ?></strong>
            </div>
            <?php if ($transactionNo !== ''): ?>
                <div class="payment-result-row">
                    <span>Mã giao dịch</span>
                    <strong class="transaction-code"><?= View::escape($transactionNo) ?></strong>
                </div>
            <?php endif; ?>
            <div class="payment-result-row">
                <span><?= $isSuccess ? 'Số tiền đã thanh toán' : 'Mã phản hồi' ?></span>
                <strong class="payment-result-amount">
                    <?= $isSuccess
                        ? View::currency((string)(int)$amount)
                        : View::escape($responseCode) ?>
                </strong>
            </div>
            <?php if ($isSuccess): ?>
                <div class="payment-result-time">Ghi nhận lúc <?= date('H:i · d/m/Y') ?></div>
            <?php endif; ?>
        </div>

        <div class="payment-result-actions">
            <?php if ($isSuccess && $invoiceId > 0): ?>
                <a class="payment-result-button primary" href="/invoices/<?= $invoiceId ?>">Xem hóa đơn</a>
                <a class="payment-result-button secondary" href="/invoices/<?= $invoiceId ?>/pdf" target="_blank" rel="noopener">Tải hóa đơn PDF</a>
            <?php elseif (!$isSuccess && $orderId > 0): ?>
                <a class="payment-result-button primary" href="/payment/checkout/<?= $orderId ?>">Thử thanh toán lại</a>
            <?php endif; ?>
            <a class="payment-result-button <?= $isSuccess && $invoiceId > 0 ? 'text-link' : 'secondary' ?>" href="<?= View::escape($ordersUrl) ?>">
                <?= ($currentUser['role'] ?? '') === 'customer' ? 'Lịch sử đơn hàng' : 'Danh sách đơn hàng' ?>
            </a>
        </div>

        <p class="payment-result-help">Cần hỗ trợ? Vui lòng liên hệ nhân viên nhà hàng và cung cấp mã đơn hàng.</p>
    </article>
</section>
