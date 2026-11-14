<?php
use App\Core\View;
?>
<section class="page-heading admin-heading">
    <p class="eyebrow">QUẢN TRỊ · THỰC ĐƠN</p>
    <h1>Sửa danh mục</h1>
    <a class="back-link" href="/admin/menu/categories">← Quay lại danh sách danh mục</a>
</section>
<section class="admin-content">
    <?php if ($errors !== []): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="admin-card form-card">
        <form action="/admin/menu/categories/<?= (int) $category['id'] ?>" method="post" class="form-stack">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            <label for="category-name">Tên danh mục</label>
            <input id="category-name" name="name" maxlength="150" required value="<?= View::escape($category['name'] ?? '') ?>">
            <label for="category-slug">Đường dẫn (slug)</label>
            <input id="category-slug" name="slug" maxlength="150" pattern="[a-z0-9]+(-[a-z0-9]+)*" required value="<?= View::escape($category['slug'] ?? '') ?>">
            <label for="category-description">Mô tả</label>
            <textarea id="category-description" name="description" rows="4"><?= View::escape($category['description'] ?? '') ?></textarea>
            <label for="category-sort">Thứ tự hiển thị</label>
            <input id="category-sort" type="number" name="sort_order" min="0" step="1" value="<?= View::escape($category['sort_order'] ?? '0') ?>">
            <label class="checkbox-label"><input type="checkbox" name="is_visible" value="1" <?= !empty($category['is_visible']) ? 'checked' : '' ?>> Hiển thị trên thực đơn</label>
            <button class="button" type="submit">Lưu danh mục</button>
        </form>
    </div>
</section>
