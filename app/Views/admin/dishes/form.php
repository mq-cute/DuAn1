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

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>QUẢN TRỊ &bull; THỰC ĐƠN</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                <?= $isEditing ? 'Chỉnh Sửa Món Ăn' : 'Thêm Món Ăn Mới' ?>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Cấu hình thông tin mỹ vị, giá niêm yết, thời gian chế biến và hình ảnh giới thiệu.
            </p>
        </div>

        <a href="/admin/menu" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm self-start">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Quay lại danh sách món</span>
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

    <!-- Main Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <?php if ($categories === []): ?>
            <div class="py-12 text-center text-slate-400 space-y-3">
                <span class="material-symbols-outlined text-4xl block text-amber-500">warning</span>
                <h3 class="font-headline font-bold text-lg text-slate-900">Chưa có danh mục ẩm thực</h3>
                <p class="text-xs max-w-sm mx-auto">Vui lòng tạo ít nhất một danh mục ẩm thực trước khi thực hiện thêm món ăn mới.</p>
                <a href="/admin/menu/categories" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-admin-navy text-gold rounded-xl font-bold text-xs hover:bg-slate-800 transition">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Tạo danh mục ngay</span>
                </a>
            </div>
        <?php else: ?>
            <form action="<?= $isEditing ? '/admin/menu/dishes/' . (int) $dish['id'] : '/admin/menu/dishes' ?>" 
                  method="post" enctype="multipart/form-data" class="space-y-6 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <!-- Nhóm 1: Thông tin cơ bản -->
                <div class="space-y-4">
                    <h3 class="font-headline font-bold text-sm text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gold text-[18px]">restaurant_menu</span>
                        <span>Thông Tin Định Danh Món Ăn</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="dish-name" class="block font-bold text-slate-700 mb-1">TÊN MÓN ĂN (*)</label>
                            <input id="dish-name" name="name" maxlength="200" required 
                                   placeholder="VD: Thăn Lưng Bò Wagyu A5 Miyazaki..."
                                   value="<?= View::escape($value('name')) ?>"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 focus:border-gold transition">
                        </div>

                        <div>
                            <label for="dish-slug" class="block font-bold text-slate-700 mb-1">ĐƯỜNG DẪN (SLUG) (*)</label>
                            <input id="dish-slug" name="slug" maxlength="200" pattern="[a-z0-9]+(-[a-z0-9]+)*" required 
                                   placeholder="than-lung-bo-wagyu-a5"
                                   value="<?= View::escape($value('slug')) ?>"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 focus:border-gold transition">
                            <span class="text-[10px] text-slate-400 mt-1 block">Chỉ dùng chữ thường không dấu, số và gạch ngang.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="dish-category" class="block font-bold text-slate-700 mb-1">DANH MỤC (*)</label>
                            <select id="dish-category" name="category_id" required 
                                    class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 transition">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (string) $value('category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                                        <?= View::escape($category['name']) ?><?= !(bool) $category['is_visible'] ? ' (đang ẩn)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="dish-price" class="block font-bold text-slate-700 mb-1">GIÁ BÁN NIÊM YẾT (VNĐ) (*)</label>
                            <input id="dish-price" name="price" type="number" min="0.01" max="9999999999.99" step="any" required 
                                   placeholder="VD: 2450000"
                                   value="<?= View::escape($value('price')) ?>"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold text-gold-dark focus:bg-white focus:ring-2 focus:ring-gold/30 transition">
                        </div>

                        <div>
                            <label for="dish-time" class="block font-bold text-slate-700 mb-1">THỜI GIAN CHẾ BIẾN (PHÚT)</label>
                            <input id="dish-time" name="preparation_time" type="number" min="1" max="1440" step="1" 
                                   placeholder="25"
                                   value="<?= View::escape($value('preparation_time', 20)) ?>"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-gold/30 transition">
                        </div>
                    </div>
                </div>

                <!-- Nhóm 2: Mô tả phong vị & Ẩm thực -->
                <div class="space-y-4">
                    <h3 class="font-headline font-bold text-sm text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gold text-[18px]">description</span>
                        <span>Mô Tả Phong Vị & Nguyên Liệu Thượng Hạng</span>
                    </h3>

                    <div>
                        <label for="dish-description" class="block font-bold text-slate-700 mb-1">MÔ TẢ MÓN ĂN</label>
                        <textarea id="dish-description" name="description" rows="4" 
                                  placeholder="Mô tả nguồn gốc nguyên liệu nhập khẩu, kỹ thuật chế biến Sous-vide hoặc nướng than hoa Binchotan..."
                                  class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl resize-none text-slate-700 focus:bg-white focus:ring-2 focus:ring-gold/30 transition leading-relaxed"><?= View::escape($value('description')) ?></textarea>
                    </div>
                </div>

                <!-- Nhóm 3: Hình ảnh & Trạng thái -->
                <div class="space-y-4">
                    <h3 class="font-headline font-bold text-sm text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gold text-[18px]">image</span>
                        <span>Hình Ảnh Trưng Bày & Trạng Thái Phục Vụ</span>
                    </h3>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <label for="dish-image" class="block font-bold text-slate-700">TẢI LÊN ẢNH MÓN ĂN (JPEG, PNG hoặc WebP; tối đa 5 MB)</label>
                        <input id="dish-image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-admin-navy file:text-gold hover:file:bg-slate-800 file:cursor-pointer cursor-pointer">

                        <?php if ($isEditing && !empty($dish['image'])): ?>
                            <div class="flex items-center gap-4 pt-2 border-t border-slate-200">
                                <img src="<?= View::escape($dish['image']) ?>" alt="<?= View::escape($dish['name']) ?>" 
                                     class="w-20 h-20 rounded-xl object-cover border border-slate-300 shadow-sm">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Ảnh hiện tại của món ăn</span>
                                    <span class="text-[11px] text-slate-400">Nếu không muốn thay đổi ảnh, hãy để trống ô chọn file phía trên.</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="dish-available" name="is_available" value="1" <?= $isAvailable ? 'checked' : '' ?>
                                   class="rounded border-slate-300 text-gold focus:ring-gold w-4 h-4">
                            <label for="dish-available" class="font-bold text-slate-800 select-none cursor-pointer">
                                Còn món (Sẵn sàng chế biến tại bếp KDS)
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="dish-visible" name="is_visible" value="1" <?= $isVisible ? 'checked' : '' ?>
                                   class="rounded border-slate-300 text-gold focus:ring-gold w-4 h-4">
                            <label for="dish-visible" class="font-bold text-slate-800 select-none cursor-pointer">
                                Hiển thị công khai trên thực đơn
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Nút Submit -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="/admin/menu" class="px-5 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">
                        Hủy
                    </a>
                    <button type="submit" class="px-7 py-2.5 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span><?= $isEditing ? 'Lưu Thay Đổi Món Ăn' : 'Tạo Món Ăn' ?></span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
