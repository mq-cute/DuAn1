<?php

declare(strict_types=1);

use App\Core\View;
use App\Models\Order;

$orderStatuses = Order::ORDER_STATUSES;
$itemStatuses = Order::ITEM_STATUSES;

// Steps mapping for the progress bar
$steps = [
    'confirmed' => [
        'step' => 1,
        'title' => 'Đã tiếp nhận',
        'desc' => 'Nhà hàng đã xác nhận đơn gọi món',
        'icon' => 'receipt',
    ],
    'preparing' => [
        'step' => 2,
        'title' => 'Đang chế biến',
        'desc' => 'Bếp trưởng & phụ bếp đang chuẩn bị món',
        'icon' => 'skillet',
    ],
    'ready' => [
        'step' => 3,
        'title' => 'Sẵn sàng ra món',
        'desc' => 'Món đã hoàn thành, sẵn sàng phục vụ',
        'icon' => 'room_service',
    ],
    'served' => [
        'step' => 4,
        'title' => 'Đã lên bàn',
        'desc' => 'Món ăn đã được phục vụ trọn vẹn tại bàn',
        'icon' => 'restaurant',
    ],
    'completed' => [
        'step' => 5,
        'title' => 'Hoàn tất',
        'desc' => 'Đã hoàn tất thanh toán & bữa tiệc',
        'icon' => 'check_circle',
    ],
];

$stepOrder = ['pending' => 1, 'confirmed' => 1, 'preparing' => 2, 'ready' => 3, 'served' => 4, 'completed' => 5, 'cancelled' => -1];
$currentStepNum = $stepOrder[$order['status']] ?? 1;
$isCancelled = $order['status'] === 'cancelled';
$isLive = in_array($order['status'], ['pending', 'confirmed', 'preparing', 'ready', 'served'], true);
?>

<div class="tracking-page-shell">
    <div class="tracking-top-bar">
        <a class="back-link" href="/account/orders">← Quay lại danh sách đơn của tôi</a>
        <?php if ($isLive): ?>
            <div class="live-indicator">
                <span class="pulse-dot"></span>
                <span>Theo dõi thời gian thực (Tự động cập nhật)</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header Card -->
    <header class="tracking-header-card">
        <div class="header-main-info">
            <span class="eyebrow">THEO DÕI TIẾN ĐỘ ẨM THỰC</span>
            <h1>Đơn gọi món #<?= (int) $order['id'] ?></h1>
            <p class="meta-line">
                <span>Bàn: <strong><?= View::escape($order['table_code']) ?> — <?= View::escape($order['table_name']) ?></strong></span>
                <span>•</span>
                <span>Khu vực: <strong><?= View::escape($order['area_name']) ?></strong></span>
                <span>•</span>
                <span>Thời gian: <?= date('H:i · d/m/Y', strtotime($order['created_at'])) ?></span>
            </p>
        </div>
        <div class="header-status-badge">
            <span class="status-badge-lg status-badge-<?= View::escape($order['status']) ?>">
                <?php if ($isLive): ?><span class="pulse-dot"></span><?php endif; ?>
                <?= View::escape($orderStatuses[$order['status']] ?? $order['status']) ?>
            </span>
            <span class="payment-badge-sm <?= $order['payment_status'] === 'paid' ? 'pay-paid' : 'pay-unpaid' ?>">
                <?= $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
            </span>
        </div>
    </header>

    <?php if ($isCancelled): ?>
        <div class="alert alert-error" role="alert">
            <strong>Đơn hàng này đã được hủy.</strong> Nếu cần hỗ trợ, vui lòng liên hệ nhân viên phục vụ tại bàn hoặc quầy lễ tân.
        </div>
    <?php else: ?>
        <!-- Visual Stepper Progress Bar -->
        <section class="tracking-stepper-card">
            <div class="stepper-track">
                <?php $i = 1; foreach ($steps as $key => $sData): ?>
                    <?php
                    $isDone = $currentStepNum > $sData['step'];
                    $isCurrent = $currentStepNum === $sData['step'];
                    $stepClass = $isDone ? 'is-done' : ($isCurrent ? 'is-current' : 'is-upcoming');
                    ?>
                    <div class="step-node <?= $stepClass ?>" data-step="<?= (int) $sData['step'] ?>">
                        <div class="step-circle">
                            <?php if ($isDone): ?>
                                <span>✓</span>
                            <?php else: ?>
                                <span><?= (int) $sData['step'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="step-text">
                            <strong><?= View::escape($sData['title']) ?></strong>
                            <small><?= View::escape($sData['desc']) ?></small>
                        </div>
                    </div>
                <?php $i++; endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <div class="tracking-content-grid">
        <!-- Left Column: Item by Item Status -->
        <section class="tracking-panel">
            <div class="panel-header">
                <h2>Chi tiết món ăn &amp; Tiến độ bếp</h2>
                <span class="item-count-badge"><?= count($order['items']) ?> món</span>
            </div>

            <div class="dishes-progress-list">
                <?php foreach ($order['items'] as $item): ?>
                    <?php
                    $itemStatusKey = $item['status'] ?? 'pending';
                    $itemStatusLabel = $itemStatuses[$itemStatusKey] ?? $itemStatusKey;
                    ?>
                    <article class="dish-progress-item" data-item-id="<?= (int) $item['id'] ?>">
                        <div class="dish-thumb">
                            <?php if (!empty($item['dish_image'])): ?>
                                <img src="<?= View::escape($item['dish_image']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <span>L'É</span>
                            <?php endif; ?>
                        </div>
                        <div class="dish-info">
                            <h3><?= View::escape($item['dish_name']) ?></h3>
                            <?php if (!empty($item['category_name'])): ?>
                                <span class="dish-cat"><?= View::escape($item['category_name']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['note'])): ?>
                                <p class="dish-note">Ghi chú: "<?= View::escape($item['note']) ?>"</p>
                            <?php endif; ?>
                        </div>
                        <div class="dish-qty-price">
                            <span class="qty">x<?= (int) $item['quantity'] ?></span>
                            <strong class="price"><?= View::currency((string) $item['subtotal']) ?></strong>
                        </div>
                        <div class="dish-status-col">
                            <span class="dish-status-pill dish-status-<?= View::escape($itemStatusKey) ?>">
                                <?= View::escape($itemStatusLabel) ?>
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Notes if any -->
            <?php if (!empty($order['notes'])): ?>
                <div class="order-notes-box">
                    <strong>Ghi chú chung của đơn:</strong>
                    <p><?= View::escape($order['notes']) ?></p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Right Column: Bill Summary & Activity Timeline -->
        <div class="tracking-side-stack">
            <!-- Order Bill Summary -->
            <section class="tracking-panel">
                <div class="panel-header">
                    <h2>Chi phí đơn hàng</h2>
                </div>
                <div class="bill-summary-list">
                    <div class="bill-row">
                        <span>Tạm tính món ăn</span>
                        <strong><?= View::currency((string) $order['total_amount']) ?></strong>
                    </div>
                    <?php if ((float) $order['service_charge'] > 0): ?>
                        <div class="bill-row">
                            <span>Phí phục vụ bàn (5%)</span>
                            <span><?= View::currency((string) $order['service_charge']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ((float) $order['tax'] > 0): ?>
                        <div class="bill-row">
                            <span>Thuế VAT (8%)</span>
                            <span><?= View::currency((string) $order['tax']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ((float) $order['discount'] > 0): ?>
                        <div class="bill-row text-discount">
                            <span>Giảm giá</span>
                            <span>-<?= View::currency((string) $order['discount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="bill-divider"></div>
                    <div class="bill-row bill-grand-total">
                        <span>Tổng thanh toán</span>
                        <strong class="price-highlight"><?= View::currency((string) $order['grand_total']) ?></strong>
                    </div>
                </div>

                <div class="payment-action-block">
                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <div class="payment-confirmed-banner">
                            <span class="check-icon">✓</span>
                            <div>
                                <strong>Đơn hàng đã thanh toán thành công</strong>
                                <p class="text-xs">Cảm ơn quý khách đã thưởng thức ẩm thực tại L'Épicure.</p>
                            </div>
                        </div>
                        <a class="button button-outline button-block" href="/staff/orders/<?= (int) $order['id'] ?>/invoice" target="_blank">
                            Xem &amp; Tải Hóa Đơn VAT
                        </a>
                    <?php else: ?>
                        <div class="payment-pending-banner">
                            <p>Đơn hàng sẽ được thanh toán tại bàn hoặc quầy thu ngân khi quý khách hoàn tất dùng bữa.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Activity Logs / Status Timeline -->
            <?php if (!empty($order['histories'])): ?>
                <section class="tracking-panel">
                    <div class="panel-header">
                        <h2>Nhật ký tiến trình</h2>
                    </div>
                    <ol class="timeline-list">
                        <?php foreach ($order['histories'] as $history): ?>
                            <li class="timeline-item">
                                <span class="timeline-dot"></span>
                                <div class="timeline-content">
                                    <div class="timeline-time"><?= date('H:i · d/m/Y', strtotime($history['created_at'])) ?></div>
                                    <div class="timeline-status">
                                        Chuyển sang <strong><?= View::escape($orderStatuses[$history['new_status']] ?? $history['new_status']) ?></strong>
                                    </div>
                                    <?php if (!empty($history['note'])): ?>
                                        <p class="timeline-note"><?= View::escape($history['note']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($isLive): ?>
<script>
// Auto-polling for real-time status updates every 6 seconds
(function() {
    var orderId = <?= (int) $order['id'] ?>;
    var currentStatus = "<?= View::escape($order['status']) ?>";

    function checkStatus() {
        fetch('/account/orders/' + orderId + '/status', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data && data.success && data.status && data.status !== currentStatus) {
                // Status changed in kitchen or by staff -> reload page seamlessly
                window.location.reload();
            }
        })
        .catch(function(err) {
            // Silently ignore network interruptions
        });
    }

    setInterval(checkStatus, 6000);
})();
</script>
<?php endif; ?>
