<?php
use App\Core\View;

$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'seated' => 'Đã nhận bàn',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
    'no_show' => 'Không đến',
];
$messages = [
    'created' => 'Yêu cầu đặt bàn đã được gửi và đang chờ nhà hàng xác nhận.',
    'cancelled' => 'Đặt bàn đã được hủy.',
    'cannot-cancel' => 'Trạng thái hiện tại không cho phép hủy đặt bàn.',
    'missing' => 'Không tìm thấy lượt đặt bàn.',
    'invalid' => 'Yêu cầu không hợp lệ.',
];
?>
<section class="page-heading">
    <p class="eyebrow">TÀI KHOẢN · RÉSERVATION</p>
    <h1>Lịch sử đặt bàn</h1>
    <p class="muted">Theo dõi trạng thái và thông tin các lượt đặt bàn của bạn.</p>
    <a class="button button-small" href="/bookings/new">Đặt bàn mới</a>
</section>
<section class="booking-list-shell">
    <?php if (isset($messages[$message ?? ''])): ?><div class="alert <?= $message === 'created' || $message === 'cancelled' ? 'alert-success' : 'alert-error' ?>" role="status"><?= View::escape($messages[$message]) ?></div><?php endif; ?>
    <?php if ($bookings === []): ?>
        <div class="empty-state"><h2>Bạn chưa có lượt đặt bàn</h2><p>Chọn ngày và khu vực phù hợp để gửi yêu cầu đến nhà hàng.</p><a class="button" href="/bookings/new">Đặt bàn ngay</a></div>
    <?php else: ?>
        <div class="booking-history-grid">
            <?php foreach ($bookings as $booking): ?>
                <article class="booking-history-card">
                    <div class="history-card-top">
                        <span class="booking-reference">Đặt bàn #<?= (int) $booking['id'] ?></span>
                        <span class="status-pill status-<?= View::escape($booking['status']) ?>"><?= View::escape($statusLabels[$booking['status']] ?? $booking['status']) ?></span>
                    </div>
                    <h2><?= View::escape($booking['area_name'] ?? 'Nhà hàng') ?> · <?= View::escape($booking['table_code'] ?? 'Chưa xếp bàn') ?></h2>
                    <p><?= View::escape(date('d/m/Y', strtotime($booking['booking_date']))) ?> · <?= View::escape(substr((string) $booking['booking_time'], 0, 5)) ?></p>
                    <p><?= (int) $booking['number_of_guests'] ?> khách · <?= View::escape($booking['table_name'] ?? '') ?></p>
                    <a class="text-link" href="/account/bookings/<?= (int) $booking['id'] ?>">Xem chi tiết →</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
