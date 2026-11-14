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
    'updated' => 'Trạng thái đặt bàn đã được cập nhật.',
    'invalid' => 'Thông tin cập nhật không hợp lệ.',
    'missing' => 'Không tìm thấy lượt đặt bàn.',
    'table-busy' => 'Bàn đang được sử dụng, không thể xác nhận lượt này.',
    'not-today' => 'Chỉ có thể nhận khách vào đúng ngày đặt bàn.',
    'invalid-transition' => 'Không thể chuyển trạng thái theo quy trình hiện tại.',
];
$nextStates = [
    'pending' => ['confirmed' => 'Xác nhận', 'cancelled' => 'Từ chối'],
    'confirmed' => ['seated' => 'Nhận bàn', 'cancelled' => 'Hủy', 'no_show' => 'Đánh dấu không đến'],
    'seated' => ['completed' => 'Hoàn tất'],
    'completed' => [],
    'cancelled' => [],
    'no_show' => [],
];
?>
<section class="page-heading admin-heading">
    <p class="eyebrow">VẬN HÀNH · RÉSERVATION</p>
    <h1>Quản lý đặt bàn</h1>
    <p class="muted">Duyệt yêu cầu của khách và cập nhật trạng thái theo đúng luồng phục vụ.</p>
</section>
<section class="admin-content">
    <?php if (isset($messages[$message])): ?><div class="alert <?= $message === 'updated' ? 'alert-success' : 'alert-error' ?>" role="status"><?= View::escape($messages[$message]) ?></div><?php endif; ?>
    <div class="admin-card">
        <form action="/admin/bookings" method="get" class="menu-filters admin-filters">
            <label for="filter-date">Ngày</label>
            <input id="filter-date" type="date" name="date" value="<?= View::escape($date) ?>">
            <label class="sr-only" for="filter-status">Trạng thái</label>
            <select id="filter-status" name="status">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($statuses as $statusValue): ?>
                    <option value="<?= View::escape($statusValue) ?>" <?= $status === $statusValue ? 'selected' : '' ?>><?= View::escape($statusLabels[$statusValue]) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="button button-small" type="submit">Lọc</button>
        </form>
        <?php if ($bookings === []): ?>
            <div class="empty-state"><h2>Chưa có lượt đặt bàn</h2><p>Thử bỏ bộ lọc hoặc kiểm tra lại sau.</p></div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Ngày & giờ</th><th>Khách hàng</th><th>Bàn</th><th>Số khách</th><th>Món / ghi chú</th><th>Trạng thái & thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><strong><?= View::escape(date('d/m/Y', strtotime($booking['booking_date']))) ?></strong><span class="table-subtitle"><?= View::escape(substr((string) $booking['booking_time'], 0, 5)) ?></span></td>
                            <td><strong><?= View::escape($booking['customer_name']) ?></strong><span class="table-subtitle"><?= View::escape($booking['customer_phone']) ?></span></td>
                            <td><?= View::escape($booking['area_name'] ?? '') ?><span class="table-subtitle"><?= View::escape($booking['table_code'] ?? 'Chưa xếp bàn') ?></span></td>
                            <td><?= (int) $booking['number_of_guests'] ?></td>
                            <td>
                                <?php if ($booking['preorders'] === []): ?>
                                    <span class="table-subtitle">Không đặt món trước</span>
                                <?php else: ?>
                                    <?php foreach ($booking['preorders'] as $preorder): ?>
                                        <span class="table-subtitle"><strong><?= View::escape($preorder['dish_name']) ?> × <?= (int) $preorder['quantity'] ?></strong><?= !empty($preorder['note']) ? ' · ' . View::escape($preorder['note']) : '' ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($booking['notes'])): ?><span class="table-subtitle">Ghi chú: <?= View::escape($booking['notes']) ?></span><?php endif; ?>
                            </td>
                            <td>
                                <span class="status-pill status-<?= View::escape($booking['status']) ?>"><?= View::escape($statusLabels[$booking['status']] ?? $booking['status']) ?></span>
                                <?php foreach ($nextStates[$booking['status']] ?? [] as $nextStatus => $label): ?>
                                    <form action="/admin/bookings/<?= (int) $booking['id'] ?>/status" method="post" class="status-action-form">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <input type="hidden" name="status" value="<?= View::escape($nextStatus) ?>">
                                        <button class="text-button <?= in_array($nextStatus, ['cancelled', 'no_show'], true) ? 'text-danger' : '' ?>" type="submit"><?= View::escape($label) ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="field-hint">Hiển thị tối đa 100 lượt đặt bàn theo bộ lọc hiện tại.</p>
        <?php endif; ?>
    </div>
</section>
