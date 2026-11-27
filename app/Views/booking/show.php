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
    'created' => ['alert-success', 'Yêu cầu đặt bàn đã được gửi và đang chờ nhà hàng xác nhận.'],
    'cancelled' => ['alert-success', 'Đặt bàn đã được hủy.'],
    'deposit-pending' => ['alert-success', 'Yêu cầu đặt bàn đã được gửi. Trạng thái hiện tại: Chờ xác nhận bàn.'],
    'cannot-cancel' => ['alert-error', 'Trạng thái hiện tại không cho phép hủy đặt bàn.'],
];
?>
<section class="page-heading">
    <p class="eyebrow">TÀI KHOẢN · CHI TIẾT</p>
    <h1>Đặt bàn #<?= (int) $booking['id'] ?></h1>
    <a class="back-link" href="/account/bookings">← Quay lại lịch sử đặt bàn</a>
</section>
<section class="booking-detail-shell">
    <?php if (isset($messages[$message])): ?>
        <div class="alert <?= View::escape($messages[$message][0]) ?>" role="status"><?= View::escape($messages[$message][1]) ?></div>
    <?php endif; ?>
    <div class="booking-detail-grid">
        <article class="booking-step">
            <div class="history-card-top">
                <h2>Thông tin đặt bàn</h2>
                <span class="status-pill status-<?= View::escape($booking['status']) ?>"><?= $booking['status'] === 'pending' ? 'Chờ xác nhận bàn' : View::escape($statusLabels[$booking['status']] ?? $booking['status']) ?></span>
            </div>
            <dl class="booking-data">
                <div><dt>Thời gian</dt><dd><?= View::escape(date('d/m/Y', strtotime($booking['booking_date']))) ?> · <?= View::escape(substr((string) $booking['booking_time'], 0, 5)) ?></dd></div>
                <div><dt>Số khách</dt><dd><?= (int) $booking['number_of_guests'] ?></dd></div>
                <div><dt>Khu vực & bàn</dt><dd><?= View::escape($booking['area_name'] ?? 'Chưa xếp') ?> · <?= View::escape($booking['table_code'] ?? 'Chưa xếp') ?></dd></div>
                <?php if ((float) ($booking['deposit_amount'] ?? 0) > 0): ?><div><dt>Tiền đặt cọc</dt><dd><?= View::currency($booking['deposit_amount']) ?></dd></div><?php endif; ?>
                <div><dt>Người đặt</dt><dd><?= View::escape($booking['customer_name']) ?></dd></div>
                <div><dt>Số điện thoại</dt><dd><?= View::escape($booking['customer_phone']) ?></dd></div>
                <div><dt>Ghi chú</dt><dd><?= nl2br(View::escape($booking['notes'] ?? 'Không có')) ?></dd></div>
            </dl>
            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                <form action="/account/bookings/<?= (int) $booking['id'] ?>/cancel" method="post" onsubmit="return confirm('Bạn có chắc muốn hủy lượt đặt bàn này? Tiền đặt cọc đã thanh toán sẽ không được hoàn lại.')">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    <button class="button button-outline" type="submit">Hủy đặt bàn</button>
                </form>
            <?php endif; ?>
        </article>
        <article class="booking-step">
            <h2>Món đặt trước</h2>
            <?php if ($details === []): ?>
                <p class="muted">Không có món đặt trước.</p>
            <?php else: ?>
                <ul class="booking-dish-list">
                    <?php foreach ($details as $detail): ?>
                        <li><div><strong><?= View::escape($detail['dish_name']) ?></strong><span><?= (int) $detail['quantity'] ?> × <?= View::currency($detail['unit_price']) ?></span><?php if ($detail['note'] !== null && $detail['note'] !== ''): ?><small><?= View::escape($detail['note']) ?></small><?php endif; ?></div><strong><?= View::currencyFromCents((int) round((float) $detail['unit_price'] * 100) * (int) $detail['quantity']) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
        <article class="booking-step">
            <h2>Lịch sử trạng thái</h2>
            <ol class="status-history">
                <?php foreach ($histories as $history): ?>
                    <li>
                        <span class="history-dot"></span>
                        <div><strong><?= View::escape($statusLabels[$history['new_status']] ?? $history['new_status']) ?></strong><span><?= View::escape(date('d/m/Y H:i', strtotime($history['created_at']))) ?></span><?php if (!empty($history['changed_by_name'])): ?><small><?= View::escape($history['changed_by_name']) ?></small><?php endif; ?><?php if (!empty($history['note'])): ?><small><?= View::escape($history['note']) ?></small><?php endif; ?></div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </article>
    </div>
</section>
