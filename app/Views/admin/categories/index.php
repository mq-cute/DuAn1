<?php
use App\Core\View;

$messages = [
    'saved' => 'Danh mục đã được lưu.',
    'deleted' => 'Danh mục đã được xóa.',
    'in-use' => 'Danh mục đang có món hoặc đang được sử dụng, vì vậy không thể xóa.',
    'invalid' => 'Yêu cầu không hợp lệ. Vui lòng thử lại.',
    'missing' => 'Danh mục không còn tồn tại.',
    'expired' => 'Biểu mẫu đã hết hạn. Vui lòng tải lại trang.',
];
?>
<section class="page-heading admin-heading">
    <p class="eyebrow">QUẢN TRỊ · THỰC ĐƠN</p>
    <h1>Quản lý danh mục</h1>
    <p class="muted">Tạo và sắp xếp danh mục hiển thị trên thực đơn khách hàng.</p>
    <a class="back-link" href="/admin/menu">← Quay lại quản lý món ăn</a>
</section>
<section class="admin-content">
    <?php if (isset($messages[$message])): ?><div class="alert alert-success" role="status"><?= View::escape($messages[$message]) ?></div><?php endif; ?>
    <?php if ($errors !== []): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($errors as $error): ?><p><?= View::escape($error) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="admin-grid">
        <section class="admin-card">
            <h2>Thêm danh mục</h2>
            <form action="/admin/menu/categories" method="post" class="form-stack">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                <label for="category-name">Tên danh mục</label>
                <input id="category-name" name="name" maxlength="150" required value="<?= View::escape($old['name'] ?? '') ?>">
                <label for="category-slug">Đường dẫn (slug)</label>
                <input id="category-slug" name="slug" maxlength="150" pattern="[a-z0-9]+(-[a-z0-9]+)*" required placeholder="mon-chinh" value="<?= View::escape($old['slug'] ?? '') ?>">
                <span class="field-hint">Chỉ dùng chữ thường không dấu, số và dấu gạch ngang.</span>
                <label for="category-description">Mô tả</label>
                <textarea id="category-description" name="description" rows="3"><?= View::escape($old['description'] ?? '') ?></textarea>
                <label for="category-sort">Thứ tự hiển thị</label>
                <input id="category-sort" type="number" name="sort_order" min="0" step="1" value="<?= View::escape($old['sort_order'] ?? '0') ?>">
                <label class="checkbox-label"><input type="checkbox" name="is_visible" value="1" <?= !isset($old['_submitted']) || isset($old['is_visible']) ? 'checked' : '' ?>> Hiển thị trên thực đơn</label>
                <button class="button" type="submit">Tạo danh mục</button>
            </form>
        </section>
        <section class="admin-card">
            <h2>Danh mục hiện có <span class="count-badge"><?= count($categories) ?></span></h2>
            <?php if ($categories === []): ?>
                <div class="empty-state compact"><p>Chưa có danh mục. Thêm danh mục đầu tiên để bắt đầu tạo món ăn.</p></div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Danh mục</th><th>Món</th><th>Hiển thị</th><th>Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong><?= View::escape($category['name']) ?></strong><span class="table-subtitle">/<?= View::escape($category['slug']) ?></span></td>
                                <td><?= (int) $category['dish_count'] ?></td>
                                <td><span class="status-pill <?= (bool) $category['is_visible'] ? 'status-on' : 'status-off' ?>"><?= (bool) $category['is_visible'] ? 'Đang hiện' : 'Đang ẩn' ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="text-link" href="/admin/menu/categories/<?= (int) $category['id'] ?>/edit">Sửa</a>
                                        <form action="/admin/menu/categories/<?= (int) $category['id'] ?>/visibility" method="post">
                                            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                            <button class="text-button" type="submit"><?= (bool) $category['is_visible'] ? 'Ẩn' : 'Hiện' ?></button>
                                        </form>
                                        <form action="/admin/menu/categories/<?= (int) $category['id'] ?>/delete" method="post" onsubmit="return confirm('Chỉ danh mục chưa có món mới được xóa. Tiếp tục?')">
                                            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                            <button class="text-button text-danger" type="submit" <?= (int) $category['dish_count'] > 0 ? 'disabled title="Danh mục đang có món"' : '' ?>>Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
