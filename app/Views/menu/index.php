<?php
use App\Core\View;

$categoryUrl = static function (string $slug, string $search, string $sort): string {
    $query = array_filter(['category' => $slug, 'q' => $search, 'sort' => $sort], static fn ($value) => $value !== '');
    return '/menu' . ($query === [] ? '' : '?' . http_build_query($query));
};
$message = match ($error) {
    'unavailable' => 'Món ăn không còn phục vụ hoặc đã bị ẩn.',
    'invalid' => 'Thông tin món ăn không hợp lệ.',
    default => '',
};
?>
<section class="page-heading">
    <p class="eyebrow">MENU · L'EXPÉRIENCE</p>
    <h1>Thực đơn</h1>
    <p class="muted">Món ăn và giá được lấy trực tiếp từ thực đơn hiện hành của nhà hàng.</p>
</section>
<section class="menu-shell">
    <?php if ($message !== ''): ?><div class="alert alert-error" role="alert"><?= View::escape($message) ?></div><?php endif; ?>
    <nav class="category-pills" aria-label="Danh mục thực đơn">
        <a class="<?= $selectedCategory === null && $search === '' ? 'is-selected' : '' ?>" href="<?= View::escape($categoryUrl('', $search, $sort)) ?>">Tất cả món</a>
        <?php foreach ($categories as $category): ?>
            <a class="<?= ($selectedCategory['slug'] ?? '') === $category['slug'] ? 'is-selected' : '' ?>" href="<?= View::escape($categoryUrl($category['slug'], $search, $sort)) ?>">
                <?= View::escape($category['name']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <form class="menu-filters" action="/menu" method="get">
        <?php if ($selectedCategory !== null): ?><input type="hidden" name="category" value="<?= View::escape($selectedCategory['slug']) ?>"><?php endif; ?>
        <label class="sr-only" for="menu-search">Tìm món ăn</label>
        <input id="menu-search" type="search" name="q" maxlength="100" value="<?= View::escape($search) ?>" placeholder="Tìm tên món ăn...">
        <label class="sr-only" for="menu-sort">Sắp xếp món ăn</label>
        <select id="menu-sort" name="sort">
            <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Gợi ý</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Tên món A–Z</option>
        </select>
        <button class="button button-small" type="submit">Lọc thực đơn</button>
    </form>

    <?php if ($selectedCategory !== null && $selectedCategory['description'] !== null): ?>
        <p class="category-description"><?= View::escape($selectedCategory['description']) ?></p>
    <?php endif; ?>

    <?php if ($dishes === []): ?>
        <div class="empty-state"><h2>Chưa có món phù hợp</h2><p>Thử đổi danh mục hoặc từ khóa tìm kiếm.</p><a href="/menu">Xem toàn bộ thực đơn</a></div>
    <?php else: ?>
        <div class="dish-grid">
            <?php foreach ($dishes as $dish): ?>
                <article class="dish-card">
                    <a class="dish-image" href="/menu/<?= rawurlencode($dish['slug']) ?>">
                        <?php if (!empty($dish['image'])): ?>
                            <img src="<?= View::escape($dish['image']) ?>" alt="<?= View::escape($dish['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="image-placeholder">FOODIES</span>
                        <?php endif; ?>
                    </a>
                    <div class="dish-card-body">
                        <a class="category-label" href="<?= View::escape($categoryUrl($dish['category_slug'], '', $sort)) ?>"><?= View::escape($dish['category_name']) ?></a>
                        <h2><a href="/menu/<?= rawurlencode($dish['slug']) ?>"><?= View::escape($dish['name']) ?></a></h2>
                        <p><?= View::escape(mb_strimwidth((string) ($dish['description'] ?? ''), 0, 130, '…', 'UTF-8')) ?></p>
                        <div class="dish-card-bottom">
                            <strong class="price"><?= View::currency($dish['price']) ?></strong>
                            <?php if ((bool) $dish['is_available']): ?>
                                <form action="/cart/items" method="post">
                                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                    <input type="hidden" name="dish_id" value="<?= (int) $dish['id'] ?>">
                                    <button class="button button-small" type="submit">Thêm món</button>
                                </form>
                            <?php else: ?>
                                <span class="status-unavailable">Tạm hết món</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
