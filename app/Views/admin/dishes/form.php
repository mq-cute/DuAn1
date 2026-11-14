<?php
use App\Core\View;

$isEditing = $dish !== null;
$value = static fn (string $key, mixed $default = ''): mixed => $old[$key] ?? ($dish[$key] ?? $default);
$isAvailable = isset($old['_submitted'])
    ? isset($old['is_available'])
    : ($dish === null || (bool) $dish['is_available']);
$isVisible = isset($old['_submitted'])
    ? isset($old['is_visible'])
    : ($dish === null || (bool) $dish['is_visible']);
?>
<section class="page-heading admin-heading">
    <p class="eyebrow">QUẢN TRỊ · MENU</p>
    <h1><?= $isEditing ? 'Sửa món ăn' : 'Thêm món ăn' ?></h1>
    <a class="back-link" href="/admin/menu">← Quay lại danh sách món ăn</a>
</section>
<section class="admin-content">
    <?php if ($errors !== []): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="admin-card form-card">
        <?php if ($categories === []): ?>
            <div class="empty-state"><h2>Chưa có danh mục</h2><p>Tạo danh mục trước khi thêm món.</p><a class="button" href="/admin/menu/categories">Tạo danh mục</a></div>
        <?php else: ?>
            <form action="<?= $isEditing ? '/admin/menu/dishes/' . (int) $dish['id'] : '/admin/menu/dishes' ?>" method="post" enctype="multipart/form-data" class="form-stack">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <label for="dish-name">Tên món</label>
                <input id="dish-name" name="name" maxlength="200" required value="<?= View::escape($value('name')) ?>">
                <label for="dish-slug">Đường dẫn (slug)</label>
                <input id="dish-slug" name="slug" maxlength="200" pattern="[a-z0-9]+(-[a-z0-9]+)*" required value="<?= View::escape($value('slug')) ?>">
                <label for="dish-category">Danh mục</label>
                <select id="dish-category" name="category_id" required>
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) $value('category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= View::escape($category['name']) ?><?= !(bool) $category['is_visible'] ? ' (đang ẩn)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="dish-description">Mô tả</label>
                <textarea id="dish-description" name="description" rows="5"><?= View::escape($value('description')) ?></textarea>
                <div class="form-row">
                    <div><label for="dish-price">Giá bán (₫)</label><input id="dish-price" name="price" type="number" min="0.01" max="9999999999.99" step="0.01" required value="<?= View::escape($value('price')) ?>"></div>
                    <div><label for="dish-time">Thời gian chế biến (phút)</label><input id="dish-time" name="preparation_time" type="number" min="1" max="1440" step="1" value="<?= View::escape($value('preparation_time')) ?>"></div>
                </div>
                <label for="dish-image">Ảnh món (JPEG, PNG hoặc WebP; tối đa 5 MB)</label>
                <input id="dish-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                <?php if ($isEditing && !empty($dish['image'])): ?>
                    <div class="current-image"><img src="<?= View::escape($dish['image']) ?>" alt="Ảnh hiện tại của <?= View::escape($dish['name']) ?>"><span class="field-hint">Để trống để giữ ảnh hiện tại.</span></div>
                <?php endif; ?>
                <label class="checkbox-label"><input type="checkbox" name="is_available" value="1" <?= $isAvailable ? 'checked' : '' ?>> Còn món</label>
                <label class="checkbox-label"><input type="checkbox" name="is_visible" value="1" <?= $isVisible ? 'checked' : '' ?>> Hiển thị trên thực đơn</label>
                <button class="button" type="submit"><?= $isEditing ? 'Lưu món ăn' : 'Tạo món ăn' ?></button>
            </form>
        <?php endif; ?>
    </div>
</section>
