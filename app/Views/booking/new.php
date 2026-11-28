<?php
use App\Core\View;

$rooms = $rooms ?? [];
$input = $input ?? [];
$items = $items ?? [];
$errors = $errors ?? [];
$hasMissingCartItems = $hasMissingCartItems ?? false;
$csrfToken = $csrfToken ?? '';
$today = $today ?? date('Y-m-d');

$canSubmit = $rooms !== []
    && !$hasMissingCartItems
    && count(array_filter($items, static fn (array $item): bool => !$item['is_orderable'])) === 0;
$preorderSubtotal = 0.0;
foreach ($items as $item) {
    if (!empty($item['is_orderable'])) {
        $preorderSubtotal += (float) $item['dish']['price'] * (int) $item['quantity'];
    }
}
?>
<section class="page-heading">
    <p class="eyebrow">RÉSERVATION · L'EXPÉRIENCE</p>
    <h1>Đặt bàn trực tuyến</h1>
    <p class="muted">Chọn thời gian, số khách và bàn mong muốn. Yêu cầu sẽ ở trạng thái chờ nhà hàng xác nhận.</p>
</section>
<section class="booking-shell">
    <?php if ($errors !== []): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($hasMissingCartItems): ?>
        <div class="alert alert-error" role="alert">Một số món trong giỏ không còn tồn tại. Vui lòng kiểm tra lại giỏ chọn món.</div>
    <?php endif; ?>

    <section class="booking-step">
        <div class="step-heading"><span>01</span><div><h2>Thời gian & số khách</h2><p>Thời lượng giữ bàn mặc định là 2 giờ.</p></div></div>
        <form action="/bookings/new" method="get" class="booking-search">
            <div class="form-row">
                <div>
                    <label for="booking-date">Ngày đến</label>
                    <input id="booking-date" type="date" name="date" min="<?= View::escape($today) ?>" required value="<?= View::escape($input['booking_date']) ?>">
                </div>
                <div>
                    <label for="booking-time">Giờ đến</label>
                    <input id="booking-time" type="time" name="time" required value="<?= View::escape($input['booking_time']) ?>">
                </div>
                <div>
                    <label for="guest-count">Số lượng khách</label>
                    <input id="guest-count" type="number" name="number_of_guests" min="1" max="100" step="1" required value="<?= (int) $input['number_of_guests'] ?>">
                </div>
            </div>
            <button class="button button-outline" type="submit">Tìm bàn còn trống</button>
        </form>
    </section>

    <?php if ($rooms !== []): ?>
        <form action="/bookings" method="post" class="booking-confirm-form">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <input type="hidden" name="booking_date" value="<?= View::escape($input['booking_date']) ?>">
            <input type="hidden" name="booking_time" value="<?= View::escape($input['booking_time']) ?>">
            <input type="hidden" name="number_of_guests" value="<?= (int) $input['number_of_guests'] ?>">
            <section class="booking-step">
                <div class="step-heading"><span>02</span><div><h2>Chọn một phòng và nhiều bàn</h2><p>Tổng sức chứa phải đạt số lượng khách. Bàn sẽ được kiểm tra lại ở máy chủ.</p></div></div>
                <input type="hidden" name="room_id" id="selected-room-id" value="<?= (int) $input['room_id'] ?>">
                <div class="available-table-grid">
                    <?php foreach ($rooms as $room): ?>
                        <?php $roomId = (int) $room['room_id']; ?>
                        <div class="available-table" data-room-id="<?= $roomId ?>">
                            <label>
                                <input type="radio" name="room_choice" value="<?= $roomId ?>" class="room-choice" <?= (int) $input['room_id'] === $roomId ? 'checked' : '' ?>>
                                <strong><?= View::escape($room['room_name']) ?></strong>
                                <span><?= View::escape($room['area_name']) ?></span>
                            </label>
                            <div class="room-table-options">
                                <?php foreach ($room['tables'] as $table): ?>
                                    <?php $tableId = (int) $table['id']; ?>
                                    <?php $tableFee = (float) ($table['table_fee'] ?? 0) > 0 ? (float) $table['table_fee'] : (!empty($table['is_vip']) ? 200000 : 100000); ?>
                                    <label class="available-table table-option" data-room-id="<?= $roomId ?>" data-capacity="<?= (int) $table['capacity'] ?>" data-fee="<?= $tableFee ?>">
                                        <input type="checkbox" name="table_ids[]" value="<?= $tableId ?>" class="table-choice" data-room-id="<?= $roomId ?>" <?= in_array($tableId, $input['table_ids'], true) ? 'checked' : '' ?>>
                                        <span class="available-table-body">
                                            <span class="table-code"><?= View::escape($table['code']) ?></span>
                                            <strong><?= View::escape($table['name']) ?></strong>
                                            <span class="capacity-label">Sức chứa <?= (int) $table['capacity'] ?> khách</span>
                                            <span class="capacity-label"><?= View::currency((int) round($tableFee)) ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="alert" id="multi-table-summary" role="status">
                    Tổng bàn: <strong id="selected-table-count">0</strong>
                    · Tổng sức chứa: <strong id="selected-capacity">0</strong>
                    · Tổng tiền bàn: <strong id="selected-table-fee">0 đ</strong>
                    · Cần thêm: <strong id="remaining-guests">0</strong> khách
                </div>
            </section>

            <section class="booking-step">
                <div class="step-heading"><span>03</span><div><h2>Thông tin người đặt</h2><p>Chúng tôi dùng thông tin này để liên hệ xác nhận.</p></div></div>
                <div class="form-row">
                    <div>
                        <label for="customer-name">Họ tên</label>
                        <input id="customer-name" name="customer_name" type="text" maxlength="150" required value="<?= View::escape($input['customer_name']) ?>">
                    </div>
                    <div>
                        <label for="customer-phone">Số điện thoại</label>
                        <input id="customer-phone" name="customer_phone" type="tel" maxlength="20" required value="<?= View::escape($input['customer_phone']) ?>" placeholder="090 123 4567">
                    </div>
                </div>
                <label for="booking-notes">Ghi chú chung</label>
                <textarea id="booking-notes" name="notes" rows="3" maxlength="5000" placeholder="Dị ứng, dịp đặc biệt hoặc yêu cầu khác..."><?= View::escape($input['notes']) ?></textarea>
            </section>

            <section class="booking-step">
                <div class="step-heading"><span>04</span><div><h2>Món đặt trước</h2><p>Giá được lấy lại từ CSDL và lưu thành snapshot khi xác nhận yêu cầu.</p></div></div>
                <?php if ($items === []): ?>
                    <p class="muted">Bạn chưa chọn món trước. Có thể đặt bàn mà không cần món.</p>
                    <a class="text-link" href="/menu">Xem thực đơn</a>
                <?php else: ?>
                    <div class="preorder-list">
                        <?php foreach ($items as $item): ?>
                            <?php $dish = $item['dish']; ?>
                            <div class="preorder-row">
                                <div>
                                    <strong><?= View::escape($dish['name']) ?></strong>
                                    <span><?= (int) $item['quantity'] ?> × <?= View::currency($dish['price']) ?></span>
                                </div>
                                <?php if ($item['is_orderable']): ?>
                                    <label for="dish-note-<?= (int) $dish['id'] ?>">Ghi chú món</label>
                                    <textarea id="dish-note-<?= (int) $dish['id'] ?>" name="dish_notes[<?= (int) $dish['id'] ?>]" rows="2" maxlength="2000" placeholder="Yêu cầu chế biến riêng..."><?= View::escape($item['note']) ?></textarea>
                                <?php else: ?>
                                    <span class="status-unavailable">Món hiện không thể đặt</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <div class="booking-submit">
                <p>Trạng thái ban đầu: <strong>Chờ xác nhận</strong>. Yêu cầu không tự động được xác nhận.</p>
                <div class="booking-actions">
                    <button class="button" type="submit" name="pay_deposit" value="1" <?= $canSubmit ? '' : 'disabled' ?>>Đặt cọc</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>

<script>
(() => {
    const guestCount = <?= (int) $input['number_of_guests'] ?>;
    const roomChoices = document.querySelectorAll('.room-choice');
    const tableChoices = document.querySelectorAll('.table-choice');
    const roomInput = document.getElementById('selected-room-id');
    const submitButton = document.querySelector('.booking-submit button[type="submit"]');

    function refreshSelection() {
        const selectedRoom = roomInput.value;
        let count = 0;
        let capacity = 0;
        let fee = 0;
        tableChoices.forEach((choice) => {
            if (choice.dataset.roomId !== selectedRoom) {
                choice.checked = false;
            } else if (choice.checked) {
                count++;
                capacity += Number(choice.closest('[data-capacity]').dataset.capacity || 0);
                fee += Number(choice.closest('[data-fee]').dataset.fee || 0);
            }
        });
        document.getElementById('selected-table-count').textContent = String(count);
        document.getElementById('selected-capacity').textContent = String(capacity);
        document.getElementById('selected-table-fee').textContent = new Intl.NumberFormat('vi-VN').format(fee) + ' đ';
        document.getElementById('remaining-guests').textContent = String(Math.max(0, guestCount - capacity));
        if (submitButton) {
            submitButton.disabled = count === 0 || capacity < guestCount;
        }
    }

    roomChoices.forEach((choice) => choice.addEventListener('change', () => {
        roomInput.value = choice.value;
        refreshSelection();
    }));
    tableChoices.forEach((choice) => choice.addEventListener('change', refreshSelection));
    refreshSelection();
})();
</script>