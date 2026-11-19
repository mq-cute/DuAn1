<?php

declare(strict_types=1);

use App\Core\View;
use App\Models\Order;

$statusConfig = [
    'pending' => ['label' => 'Chờ tiếp nhận', 'class' => 'status-badge-pending'],
    'confirmed' => ['label' => 'Đã tiếp nhận', 'class' => 'status-badge-confirmed'],
    'preparing' => ['label' => 'Đang chế biến', 'class' => 'status-badge-preparing'],
    'ready' => ['label' => 'Sẵn sàng phục vụ', 'class' => 'status-badge-ready'],
    'served' => ['label' => 'Đã lên bàn', 'class' => 'status-badge-served'],
    'completed' => ['label' => 'Hoàn tất', 'class' => 'status-badge-completed'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'status-badge-cancelled'],
];

$paymentStatusLabels = [
    'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'pay-unpaid'],
    'paid' => ['label' => 'Đã thanh toán', 'class' => 'pay-paid'],
    'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'pay-refunded'],
];
?>

<section class="page-heading">
    <p class="eyebrow">MES COMMANDES · L'EXPÉRIENCE</p>
    <h1>Đơn của tôi</h1>
    <p class="muted">Theo dõi hành trình ẩm thực từ lúc bếp tiếp nhận, chế biến đến khi món ăn được phục vụ trọn vẹn tại bàn.</p>
</section>

<section class="customer-orders-shell">
    <?php if ($error === 'not_found'): ?>
        <div class="alert alert-error" role="alert">Không tìm thấy thông tin đơn hàng hoặc bạn không có quyền xem đơn này.</div>
    <?php endif; ?>

    <!-- Status Filter Tabs -->
    <nav class="category-pills" aria-label="Lọc trạng thái đơn">
        <a class="<?= $selectedStatus === '' ? 'is-selected' : '' ?>" href="/account/orders">Tất cả đơn</a>
        <a class="<?= $selectedStatus === 'active' ? 'is-selected' : '' ?>" href="/account/orders?status=active">Đang phục vụ / Chế biến</a>
        <a class="<?= $selectedStatus === 'completed' ? 'is-selected' : '' ?>" href="/account/orders?status=completed">Hoàn tất</a>
        <a class="<?= $selectedStatus === 'cancelled' ? 'is-selected' : '' ?>" href="/account/orders?status=cancelled">Đã hủy</a>
    </nav>

    <?php if ($orders === []): ?>
        <div class="empty-state">
            <h2>Chưa có đơn gọi món nào</h2>
            <p>Khi bạn đặt bàn và gọi món tại nhà hàng, toàn bộ lịch sử đơn và tiến độ phục vụ từng món sẽ được cập nhật trực tiếp tại đây.</p>
            <div class="empty-actions">
                <a class="button" href="/menu">Khám phá thực đơn</a>
                <a class="button button-outline" href="/bookings/new">Đặt bàn trực tuyến</a>
            </div>
        </div>
    <?php else: ?>
        <div class="customer-orders-grid">
            <?php foreach ($orders as $order): ?>
                <?php
                $sConf = $statusConfig[$order['status']] ?? ['label' => $order['status'], 'class' => 'status-badge-pending'];
                $pConf = $paymentStatusLabels[$order['payment_status']] ?? ['label' => $order['payment_status'], 'class' => 'pay-unpaid'];
                $isLive = in_array($order['status'], ['pending', 'confirmed', 'preparing', 'ready', 'served'], true);
                ?>
                <article class="order-summary-card <?= $isLive ? 'order-card-live' : '' ?>">
                    <div class="order-summary-header">
                        <div class="order-meta">
                            <span class="order-id">Đơn #<?= (int) $order['id'] ?></span>
                            <span class="order-date"><?= date('H:i · d/m/Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="order-badges">
                            <span class="order-badge <?= $sConf['class'] ?>">
                                <?php if ($isLive): ?><span class="pulse-dot"></span><?php endif; ?>
                                <?= View::escape($sConf['label']) ?>
                            </span>
                            <span class="order-badge <?= $pConf['class'] ?>"><?= View::escape($pConf['label']) ?></span>
                        </div>
                    </div>

                    <div class="order-summary-body">
                        <div class="order-table-info">
                            <div class="info-row">
                                <span class="info-label">Vị trí bàn:</span>
                                <strong class="info-value"><?= View::escape($order['table_code']) ?> — <?= View::escape($order['table_name']) ?></strong>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Khu vực:</span>
                                <span class="info-value"><?= View::escape($order['area_name']) ?></span>
                            </div>
                            <?php if (!empty($order['booking_id'])): ?>
                                <div class="info-row">
                                    <span class="info-label">Mã đặt bàn:</span>
                                    <a class="text-link" href="/account/bookings/<?= (int) $order['booking_id'] ?>">#BK-<?= (int) $order['booking_id'] ?></a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-stats-strip">
                            <div class="stat-item">
                                <span class="stat-num"><?= (int) $order['item_count'] ?></span>
                                <span class="stat-desc">Món ăn</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-num"><?= (int) $order['served_count'] ?> / <?= (int) $order['item_count'] ?></span>
                                <span class="stat-desc">Đã lên bàn</span>
                            </div>
                            <div class="stat-item stat-price">
                                <span class="stat-num"><?= View::currency((string) $order['grand_total']) ?></span>
                                <span class="stat-desc">Tổng tiền</span>
                            </div>
                        </div>
                    </div>

                    <div class="order-summary-footer">
                        <a class="button button-small <?= $isLive ? 'button-live-track' : 'button-outline' ?>" href="/account/orders/<?= (int) $order['id'] ?>">
                            <span><?= $isLive ? '⚡ Theo dõi trực tiếp' : 'Xem chi tiết đơn' ?></span>
                            <span>→</span>
                        </a>
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <a class="text-button-subtle" href="/account/orders/<?= (int) $order['id'] ?>/invoice" target="_blank" title="Xem và tải hóa đơn điện tử">
                                <span>📄 Xem &amp; tải hóa đơn</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
