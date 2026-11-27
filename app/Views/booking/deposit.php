<?php use App\Core\View; ?>
<section class="deposit-page">
  <div class="deposit-card">
    <p class="eyebrow">THANH TOÁN · ĐẶT CỌC BÀN</p>
    <h1>Hoàn tất tiền đặt cọc</h1>
    <p class="muted">Đặt bàn #<?= (int) $booking['id'] ?> · <?= View::escape($booking['table_code'] ?? '') ?></p>
    <div class="deposit-total"><?= View::currency($booking['deposit_amount']) ?></div>
    <p class="muted">Quét mã bằng ứng dụng ngân hàng, sau đó xác nhận để gửi yêu cầu đặt bàn. Nhà hàng sẽ kiểm tra và xác nhận đặt cọc.</p>
    <img class="deposit-qr" src="<?= View::escape($vietQr['qr_url']) ?>" alt="Mã VietQR đặt cọc">
    <p><strong><?= View::escape($vietQr['account_name']) ?></strong><br>Số tài khoản: <?= View::escape($vietQr['account_no']) ?><br>Nội dung: <code><?= View::escape($vietQr['syntax']) ?></code></p>
    <form action="/bookings/<?= (int) $booking['id'] ?>/deposit/confirm" method="post">
      <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
      <button class="button" type="submit">Tôi đã chuyển khoản</button>
    </form>
    <a class="text-link" href="/account/bookings/<?= (int) $booking['id'] ?>">Quay lại chi tiết đặt bàn</a>
  </div>
</section>
