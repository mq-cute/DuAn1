<?php

declare(strict_types=1);

use App\Core\View;

$typeIcons = [
    'order_created' => 'receipt_long',
    'order_confirmed' => 'task_alt',
    'order_preparing' => 'skillet',
    'order_ready' => 'room_service',
    'order_served' => 'restaurant',
    'order_completed' => 'verified',
    'order_cancelled' => 'cancel',
    'booking_new' => 'calendar_month',
    'booking_confirmed' => 'event_available',
    'booking_cancelled' => 'event_busy',
];
?>

<section class="page-heading">
    <div class="heading-with-actions">
        <div>
            <p class="eyebrow">NOTIFICATIONS · FOODIES</p>
            <h1>Thông báo của tôi</h1>
            <p class="muted">Cập nhật nhanh chóng về trạng thái đặt bàn, tiếp nhận đơn gọi món và tiến độ từ nhà bếp.</p>
        </div>
        <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <form action="/account/notifications/read-all" method="post" class="inline-form">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <button type="submit" class="button button-small button-outline">
                    Đánh dấu tất cả đã đọc (<?= (int) $unreadCount ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="notifications-shell">
    <?php if ($message === 'all_marked_read'): ?>
        <div class="alert alert-success" role="status">Đã đánh dấu tất cả thông báo là đã đọc.</div>
    <?php endif; ?>

    <?php if ($notifications === []): ?>
        <div class="empty-state">
            <h2>Chưa có thông báo nào</h2>
            <p>Hệ thống sẽ gửi thông báo đến bạn mỗi khi đơn đặt bàn được xác nhận, hoặc khi đầu bếp bắt đầu chế biến và hoàn thành món ăn.</p>
            <div class="empty-actions">
                <a class="button" href="/menu">Khám phá thực đơn</a>
                <a class="button button-outline" href="/bookings/new">Đặt bàn trực tuyến</a>
            </div>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notif): ?>
                <?php
                $isUnread = !(bool) $notif['is_read'];
                $data = is_array($notif['data']) ? $notif['data'] : [];
                $orderId = $data['order_id'] ?? null;
                $bookingId = $data['booking_id'] ?? null;
                ?>
                <article class="notification-item <?= $isUnread ? 'is-unread' : 'is-read' ?>">
                    <div class="notif-indicator">
                        <?php if ($isUnread): ?>
                            <span class="unread-dot" title="Chưa đọc"></span>
                        <?php else: ?>
                            <span class="read-dot" title="Đã đọc">✓</span>
                        <?php endif; ?>
                    </div>

                    <div class="notif-body">
                        <div class="notif-header">
                            <h2 class="notif-title"><?= View::escape($notif['title']) ?></h2>
                            <time class="notif-time"><?= date('H:i · d/m/Y', strtotime($notif['created_at'])) ?></time>
                        </div>

                        <p class="notif-message"><?= View::escape($notif['message']) ?></p>

                        <div class="notif-actions">
                            <?php if ($orderId !== null): ?>
                                <?php
                                $userRole = $currentUser['role'] ?? 'customer';
                                $orderTrackUrl = match ($userRole) {
                                    'staff', 'admin' => '/staff/orders/' . (int) $orderId,
                                    'kitchen' => '/kitchen',
                                    default => '/account/orders/' . (int) $orderId,
                                };
                                ?>
                                <a class="button button-small button-outline" href="<?= $orderTrackUrl ?>">
                                    Theo dõi đơn #<?= (int) $orderId ?> →
                                </a>
                            <?php elseif ($bookingId !== null): ?>
                                <a class="button button-small button-outline" href="/account/bookings/<?= (int) $bookingId ?>">
                                    Chi tiết đặt bàn #<?= (int) $bookingId ?> →
                                </a>
                            <?php endif; ?>

                            <?php if ($isUnread): ?>
                                <form action="/account/notifications/<?= (int) $notif['id'] ?>/read" method="post" class="inline-form">
                                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                    <button type="submit" class="text-button text-muted">Đánh dấu đã đọc</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
