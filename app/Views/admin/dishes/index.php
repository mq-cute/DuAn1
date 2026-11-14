<?php
use App\Core\View;

$messages = [
    'saved' => 'Món ăn đã được lưu.',
    'deleted' => 'Món ăn đã được xóa.',
    'invalid' => 'Yêu cầu không hợp lệ. Vui lòng thử lại.',
    'missing' => 'Món ăn không còn tồn tại.',
    'has-history' => 'Món ăn đã có trong lịch sử giao dịch. Hãy ẩn món thay vì xóa để bảo toàn dữ liệu.',
];
?>
<section class="page-heading admin-heading">
    <p class="eyebrow">QUẢN TRỊ · MENU</p>
    <h1>Quản lý thực đơn</h1>
    <p class="muted">Danh mục và món ăn được lưu trực tiếp trong cơ sở dữ liệu.</p>
    <div class="hero-actions">
        <a class="button" href="/admin/menu/dishes/new">+ Thêm món ăn</a>
        <a class="button button-outline" href="/admin/menu/categories">Quản lý danh mục</a>
    </div>
</section>
<section class="admin-content">
    <?php if (isset($messages[$message])): ?><div class="alert alert-success" role="status"><?= View::escape($messages[$message]) ?></div><?php endif; ?>
    <div class="admin-card">
        <form action="/admin/menu" method="get" class="menu-filters admin-filters">
            <label class="sr-only" for="admin-search">Tìm món</label>
            <input id="admin-search" type="search" name="q" maxlength="100" value="<?= View::escape($search) ?>" placeholder="Tìm tên món hoặc slug...">
            <label class="sr-only" for="admin-category">Lọc theo danh mục</label>
            <select id="admin-category" name="category">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (string) $selectedCategoryId === (string) $category['id'] ? 'selected' : '' ?>><?= View::escape($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="button button-small" type="submit">Lọc</button>
        </form>
        <?php if ($dishes === []): ?>
            <div class="empty-state"><h2>Chưa có món ăn</h2><p>Thêm danh mục trước, sau đó tạo món ăn đầu tiên.</p></div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table dish-admin-table">
                    <thead><tr><th>Món ăn</th><th>Danh mục</th><th>Giá</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($dishes as $dish): ?>
                        <tr>
                            <td>
                                <div class="table-dish">
                                    <?php if (!empty($dish['image'])): ?><img src="<?= View::escape($dish['image']) ?>" alt="" loading="lazy"><?php else: ?><span class="table-dish-placeholder">L'É</span><?php endif; ?>
                                    <span><strong><?= View::escape($dish['name']) ?></strong><span class="table-subtitle">/<?= View::escape($dish['slug']) ?></span></span>
                                </div>
                            </td>
                            <td><?= View::escape($dish['category_name']) ?></td>
                            <td class="price"><?= View::currency($dish['price']) ?></td>
                            <td>
                                <span class="status-pill <?= (bool) $dish['is_visible'] ? 'status-on' : 'status-off' ?>"><?= (bool) $dish['is_visible'] ? 'Đang hiện' : 'Đang ẩn' ?></span>
                                <span class="status-pill <?= (bool) $dish['is_available'] ? 'status-on' : 'status-warn' ?>"><?= (bool) $dish['is_available'] ? 'Còn món' : 'Hết món' ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a class="text-link" href="/admin/menu/dishes/<?= (int) $dish['id'] ?>/edit">Sửa</a>
                                    <form action="/admin/menu/dishes/<?= (int) $dish['id'] ?>/visibility" method="post">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <button class="text-button" type="submit"><?= (bool) $dish['is_visible'] ? 'Ẩn' : 'Hiện' ?></button>
                                    </form>
                                    <form action="/admin/menu/dishes/<?= (int) $dish['id'] ?>/availability" method="post">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <button class="text-button" type="submit"><?= (bool) $dish['is_available'] ? 'Đánh dấu hết' : 'Đánh dấu còn' ?></button>
                                    </form>
                                    <form action="/admin/menu/dishes/<?= (int) $dish['id'] ?>/delete" method="post" onsubmit="return confirm('Xóa món này? Món đã có giao dịch sẽ không thể xóa.')">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <button class="text-button text-danger" type="submit">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
