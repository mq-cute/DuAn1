<?php
use App\Core\View;
?>

<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>QUẢN TRỊ &bull; THỰC ĐƠN</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Chỉnh Sửa Danh Mục
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Cập nhật thông tin và thứ tự hiển thị của danh mục.
            </p>
        </div>

        <a href="/admin/menu/categories" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm self-start">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Quay lại danh sách</span>
        </a>
    </div>

    <!-- Alert Errors -->
    <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-xs font-semibold space-y-1">
            <?php foreach ($errors as $error): ?>
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">error</span>
                    <span><?= View::escape($error) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Form Edit Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
        <form action="/admin/menu/categories/<?= (int) $category['id'] ?>" method="post" class="space-y-4 text-xs">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div>
                <label for="category-name" class="block font-bold text-slate-700 mb-1">TÊN DANH MỤC (*)</label>
                <input id="category-name" name="name" maxlength="150" required 
                       value="<?= View::escape($category['name'] ?? '') ?>"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 focus:border-gold transition">
            </div>

            <div>
                <label for="category-slug" class="block font-bold text-slate-700 mb-1">ĐƯỜNG DẪN (SLUG) (*)</label>
                <input id="category-slug" name="slug" maxlength="150" pattern="[a-z0-9]+(-[a-z0-9]+)*" required 
                       value="<?= View::escape($category['slug'] ?? '') ?>"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 focus:border-gold transition">
            </div>

            <div>
                <label for="category-sort" class="block font-bold text-slate-700 mb-1">THỨ TỰ HIỂN THỊ</label>
                <input id="category-sort" type="number" name="sort_order" min="0" step="1" 
                       value="<?= View::escape($category['sort_order'] ?? '0') ?>"
                       class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 transition">
            </div>

            <div>
                <label for="category-description" class="block font-bold text-slate-700 mb-1">MÔ TẢ CHI TIẾT</label>
                <textarea id="category-description" name="description" rows="4" 
                          class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none text-slate-700 focus:bg-white focus:ring-2 focus:ring-gold/30 transition"><?= View::escape($category['description'] ?? '') ?></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="category-visible" name="is_visible" value="1" 
                       <?= !empty($category['is_visible']) ? 'checked' : '' ?>
                       class="rounded border-slate-300 text-gold focus:ring-gold w-4 h-4">
                <label for="category-visible" class="font-bold text-slate-800 select-none cursor-pointer">
                    Hiển thị trên thực đơn khách hàng
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="/admin/menu/categories" class="px-5 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2.5 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Lưu Danh Mục</span>
                </button>
            </div>
        </form>
    </div>
</div>
