<?php
use App\Core\View;

$messages = [
    'saved' => 'Danh mục đã được lưu thành công.',
    'deleted' => 'Danh mục đã được xóa thành công.',
    'in-use' => 'Danh mục đang có món hoặc đang được sử dụng, vì vậy không thể xóa.',
    'invalid' => 'Yêu cầu không hợp lệ. Vui lòng thử lại.',
    'missing' => 'Danh mục không còn tồn tại.',
    'expired' => 'Biểu mẫu đã hết hạn. Vui lòng tải lại trang.',
];
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>QUẢN TRỊ &bull; THỰC ĐƠN</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Quản Lý Danh Mục Món Ăn
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Tạo và sắp xếp cấu trúc danh mục hiển thị trên thực đơn khách hàng.
            </p>
        </div>

        <a href="/admin/menu" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm self-start">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Quay lại quản lý món ăn</span>
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($messages[$message])): ?>
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span><?= View::escape($messages[$message]) ?></span>
        </div>
    <?php endif; ?>

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

    <!-- Main Content: 2 Columns Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Thêm Danh Mục Mới (1 span) -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5 sticky top-24">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gold">add_circle</span>
                        <span>Thêm Danh Mục Mới</span>
                    </h3>
                </div>

                <form action="/admin/menu/categories" method="post" class="space-y-4 text-xs">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    
                    <div>
                        <label for="category-name" class="block font-bold text-slate-700 mb-1">TÊN DANH MỤC (*)</label>
                        <input id="category-name" name="name" maxlength="150" required 
                               placeholder="VD: Món Khai Vị, Bò & Thịt Thượng Hạng..." 
                               value="<?= View::escape($old['name'] ?? '') ?>"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 focus:border-gold transition">
                    </div>

                    <div>
                        <label for="category-slug" class="block font-bold text-slate-700 mb-1">ĐƯỜNG DẪN (SLUG) (*)</label>
                        <input id="category-slug" name="slug" maxlength="150" pattern="[a-z0-9]+(-[a-z0-9]+)*" required 
                               placeholder="mon-khai-vi" 
                               value="<?= View::escape($old['slug'] ?? '') ?>"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 focus:border-gold transition">
                        <span class="text-[10px] text-slate-400 mt-1 block">Chỉ dùng chữ thường không dấu, số và gạch ngang.</span>
                    </div>

                    <div>
                        <label for="category-sort" class="block font-bold text-slate-700 mb-1">THỨ TỰ HIỂN THỊ</label>
                        <input id="category-sort" type="number" name="sort_order" min="0" step="1" 
                               value="<?= View::escape($old['sort_order'] ?? '0') ?>"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 transition">
                    </div>

                    <div>
                        <label for="category-description" class="block font-bold text-slate-700 mb-1">MÔ TẢ CHI TIẾT</label>
                        <textarea id="category-description" name="description" rows="3" 
                                  placeholder="Mô tả phong vị ẩm thực đặc sắc của danh mục..."
                                  class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none text-slate-700 focus:bg-white focus:ring-2 focus:ring-gold/30 transition"><?= View::escape($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="category-visible" name="is_visible" value="1" 
                               <?= !isset($old['_submitted']) || isset($old['is_visible']) ? 'checked' : '' ?>
                               class="rounded border-slate-300 text-gold focus:ring-gold w-4 h-4">
                        <label for="category-visible" class="font-bold text-slate-800 select-none cursor-pointer">
                            Hiển thị ngay trên thực đơn
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition flex items-center justify-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Tạo Danh Mục</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng Danh Sách Danh Mục Hiện Có (2 spans) -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-headline font-bold text-base text-slate-900">Danh Sách Danh Mục Hiện Có</h3>
                        <p class="text-xs text-slate-500">Các nhóm ẩm thực được phân loại trong thực đơn nhà hàng.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-gold/15 text-gold-dark font-bold text-xs">
                        <?= count($categories) ?> Danh mục
                    </span>
                </div>

                <?php if (empty($categories)): ?>
                    <div class="py-12 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">category</span>
                        <p class="text-xs">Chưa có danh mục nào. Hãy thêm danh mục đầu tiên ở bên trái.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                                    <th class="py-3 px-4">TÊN DANH MỤC & SLUG</th>
                                    <th class="py-3 px-4 text-center">SỐ MÓN</th>
                                    <th class="py-3 px-4">TRẠNG THÁI</th>
                                    <th class="py-3 px-4 text-right">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($categories as $category): 
                                    $visible = (bool) $category['is_visible'];
                                    $dishCount = (int) $category['dish_count'];
                                ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3.5 px-4">
                                            <strong class="text-slate-900 text-sm block"><?= View::escape($category['name']) ?></strong>
                                            <span class="font-mono text-slate-400 text-[11px]">/<?= View::escape($category['slug']) ?></span>
                                            <?php if (!empty($category['description'])): ?>
                                                <p class="text-slate-500 text-[11px] mt-0.5 line-clamp-1 max-w-sm"><?= View::escape($category['description']) ?></p>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-bold font-mono">
                                                <?= $dishCount ?>
                                            </span>
                                        </td>

                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $visible ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                                                <span class="w-1.5 h-1.5 rounded-full <?= $visible ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                                <span><?= $visible ? 'Đang hiển thị' : 'Đang ẩn' ?></span>
                                            </span>
                                        </td>

                                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                            <a href="/admin/menu/categories/<?= (int) $category['id'] ?>/edit" 
                                               class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">edit</span>
                                                <span>Sửa</span>
                                            </a>

                                            <form action="/admin/menu/categories/<?= (int) $category['id'] ?>/visibility" method="post" class="inline">
                                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                                <button type="submit" class="px-2.5 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-lg text-xs transition">
                                                    <?= $visible ? 'Ẩn' : 'Hiện' ?>
                                                </button>
                                            </form>

                                            <form action="/admin/menu/categories/<?= (int) $category['id'] ?>/delete" method="post" class="inline"
                                                  onsubmit="return confirm('Chỉ danh mục chưa có món mới được xóa. Tiếp tục?')">
                                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                                <button type="submit" 
                                                        class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-xs transition disabled:opacity-40 disabled:cursor-not-allowed"
                                                        <?= $dishCount > 0 ? 'disabled title="Danh mục đang có món, không thể xóa"' : '' ?>>
                                                    Xóa
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
