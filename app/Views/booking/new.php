<?php
use App\Core\View;

$canSubmit = $tables !== []
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
            <input type="hidden" name="voucher" value="<?= View::escape($input['voucher_code'] ?? '') ?>">
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

    <?php if ($tables !== []): ?>
        <form action="/bookings" method="post" class="booking-confirm-form">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <input type="hidden" name="booking_date" value="<?= View::escape($input['booking_date']) ?>">
            <input type="hidden" name="booking_time" value="<?= View::escape($input['booking_time']) ?>">
            <input type="hidden" name="number_of_guests" value="<?= (int) $input['number_of_guests'] ?>">
            <section class="booking-step">
                <div class="step-heading"><span>02</span><div><h2>Chọn bàn</h2><p>Bàn sẽ được kiểm tra lại ở máy chủ khi bạn xác nhận.</p></div></div>
                <div class="available-table-grid">
                    <?php foreach ($tables as $table): ?>
                        <label class="available-table">
                            <input type="radio" name="table_id" value="<?= (int) $table['id'] ?>" required <?= (string) $input['table_id'] === (string) $table['id'] ? 'checked' : '' ?>>
                            <span class="available-table-body">
                                <span class="table-code"><?= View::escape($table['code']) ?></span>
                                <strong><?= View::escape($table['name']) ?></strong>
                                <span><?= View::escape($table['area_name']) ?></span>
                                <span class="capacity-label">Tối đa <?= (int) $table['capacity'] ?> khách</span>
                                <?php $tableFee = (float) ($table['table_fee'] ?? 0) > 0 ? (float) $table['table_fee'] : (!empty($table['is_vip']) ? 200000 : 100000); ?>
                                <?php $depositAmount = (int) round(($tableFee + $preorderSubtotal) * 0.25); ?>
                                <span class="capacity-label">Đặt cọc 25% (bàn + món): <?= View::currency($depositAmount) ?><?= (float) ($table['table_fee'] ?? 0) <= 0 ? ' · phí bàn mặc định' : '' ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
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
                <div class="form-row">
                    <div>
                        <label for="booking-voucher">Mã voucher (không trừ vào tiền cọc)</label>
                        <input id="booking-voucher" name="voucher_code" type="text" maxlength="50" value="<?= View::escape($input['voucher_code'] ?? '') ?>" placeholder="VD: FOODIESFIRST15" autocomplete="off">
                        <p class="muted">Voucher hợp lệ sẽ giảm trên phần món ăn khi xuất hóa đơn thanh toán.</p>
                    </div>
                </div>
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
