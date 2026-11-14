<?php
use App\Core\View;

$message = match ($error) {
    'unavailable' => 'Món ăn hiện không thể thêm vào giỏ.',
    'invalid' => 'Thông tin số lượng không hợp lệ.',
    default => '',
};
?>
<section class="dish-detail">
    <div class="detail-image">
        <?php if (!empty($dish['image'])): ?>
            <img src="<?= View::escape($dish['image']) ?>" alt="<?= View::escape($dish['name']) ?>">
        <?php else: ?>
            <span class="image-placeholder">L'ÉPICURE</span>
        <?php endif; ?>
    </div>
    <div class="detail-copy">
        <a class="category-label" href="/menu?category=<?= rawurlencode($dish['category_slug']) ?>"><?= View::escape($dish['category_name']) ?></a>
        <h1><?= View::escape($dish['name']) ?></h1>
        <p class="price detail-price"><?= View::currency($dish['price']) ?></p>
        <?php if ($message !== ''): ?><div class="alert alert-error" role="alert"><?= View::escape($message) ?></div><?php endif; ?>
        <div class="dish-description">
            <?= nl2br(View::escape($dish['description'] ?? 'Nhà hàng đang cập nhật mô tả cho món ăn này.')) ?>
        </div>
        <?php if ($dish['preparation_time'] !== null): ?>
            <p class="muted">Thời gian chế biến dự kiến: <?= (int) $dish['preparation_time'] ?> phút</p>
        <?php endif; ?>
        <?php if ((bool) $dish['is_available']): ?>
            <form action="/cart/items" method="post" class="add-to-cart-form">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <input type="hidden" name="dish_id" value="<?= (int) $dish['id'] ?>">
                <label for="quantity">Số lượng</label>
                <input id="quantity" name="quantity" type="number" min="1" max="99" value="1" required>
                <button class="button" type="submit">Thêm vào giỏ chọn món</button>
            </form>
        <?php else: ?>
            <p class="status-unavailable">Món hiện tạm hết. Vui lòng quay lại sau.</p>
        <?php endif; ?>
        <a class="back-link" href="/menu">← Quay lại thực đơn</a>
    </div>
</section>
