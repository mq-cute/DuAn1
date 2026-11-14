<?php
use App\Core\View;

$message = match ($error) {
    'unavailable' => 'Món đã ngừng phục vụ hoặc bị ẩn nên không thể cập nhật số lượng.',
    'invalid' => 'Số lượng không hợp lệ.',
    default => '',
};
?>
<section class="page-heading">
    <p class="eyebrow">YOUR SELECTION</p>
    <h1>Giỏ chọn món</h1>
    <p class="muted">Các món được lưu tạm trong phiên trình duyệt này; chưa phải đơn gọi món hay đặt bàn.</p>
</section>
<section class="cart-shell">
    <?php if ($message !== ''): ?><div class="alert alert-error" role="alert"><?= View::escape($message) ?></div><?php endif; ?>
    <?php if ($items === []): ?>
        <div class="empty-state"><h2>Giỏ món đang trống</h2><p>Chọn món yêu thích từ thực đơn để lưu vào danh sách.</p><a class="button" href="/menu">Khám phá thực đơn</a></div>
    <?php else: ?>
        <div class="cart-list">
            <?php foreach ($items as $item): ?>
                <?php $dish = $item['dish']; ?>
                <article class="cart-item">
                    <div class="cart-thumb">
                        <?php if (!empty($dish['image'])): ?><img src="<?= View::escape($dish['image']) ?>" alt="">
                        <?php else: ?><span>L'É</span><?php endif; ?>
                    </div>
                    <div class="cart-item-info">
                        <a href="/menu/<?= rawurlencode($dish['slug']) ?>"><?= View::escape($dish['name']) ?></a>
                        <span class="muted"><?= View::escape($dish['category_name']) ?></span>
                        <?php if (!$item['is_orderable']): ?><span class="status-unavailable">Món hiện không phục vụ</span><?php endif; ?>
                    </div>
                    <div class="cart-item-price"><?= View::currency($dish['price']) ?></div>
                    <?php if ($item['is_orderable']): ?>
                        <form class="cart-quantity" action="/cart/items/<?= (int) $dish['id'] ?>" method="post">
                            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                            <label class="sr-only" for="quantity-<?= (int) $dish['id'] ?>">Số lượng <?= View::escape($dish['name']) ?></label>
                            <input id="quantity-<?= (int) $dish['id'] ?>" name="quantity" type="number" min="0" max="99" value="<?= (int) $item['quantity'] ?>" required>
                            <button class="button button-small button-outline" type="submit">Cập nhật</button>
                        </form>
                    <?php endif; ?>
                    <form action="/cart/items/<?= (int) $dish['id'] ?>/remove" method="post">
                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                        <button class="text-button" type="submit">Bỏ món</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
        <aside class="cart-summary">
            <span>Tạm tính danh sách</span>
            <strong><?= View::currencyFromCents($subtotalCents) ?></strong>
            <p>Giá được đọc lại từ thực đơn hiện hành. Giỏ món chỉ được lưu vào đặt bàn khi bạn gửi yêu cầu.</p>
            <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                <a class="button" href="/bookings/new">Tiếp tục đặt bàn</a>
            <?php else: ?>
                <a class="button" href="/login">Đăng nhập để đặt bàn</a>
            <?php endif; ?>
            <a class="button button-outline" href="/menu">Tiếp tục chọn món</a>
        </aside>
    <?php endif; ?>
</section>
