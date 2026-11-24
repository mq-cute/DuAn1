<?php
use App\Core\View;

$messages = [
    'saved' => 'Món ăn đã được lưu thành công vào thực đơn.',
    'deleted' => 'Món ăn đã được xóa.',
    'invalid' => 'Yêu cầu không hợp lệ. Vui lòng thử lại.',
    'missing' => 'Món ăn không còn tồn tại.',
    'has-history' => 'Món ăn đã có trong lịch sử giao dịch. Hãy ẩn món thay vì xóa để bảo toàn dữ liệu.',
];

$totalDishes = count($dishes);
$availableCount = 0;
$outOfStockCount = 0;

foreach ($dishes as $d) {
    if (!empty($d['is_available'])) $availableCount++;
    else $outOfStockCount++;
}
?>

<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>THỰC ĐƠN & BẾP &bull; PHIÊN BẢN MÙA THU 2024</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Quản lý Thực đơn & Danh mục Món ăn
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Quản lý danh sách món, cấu hình giá niêm yết, đồng bộ tình trạng tồn kho bếp (KDS) và kích hoạt thuật toán gợi ý món thông minh (Smart Pairing).
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="/admin/menu/categories" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:border-gold hover:text-gold-dark text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">category</span>
                <span>Quản lý Danh mục</span>
            </a>
            <a href="/admin/combos" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:border-gold hover:text-gold-dark text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">stars</span>
                <span>Thiết lập Combo Tiệc</span>
            </a>
            <a href="/admin/menu/dishes/new" class="px-4 py-2 bg-gradient-to-r from-admin-navy to-slate-800 text-gold text-xs font-bold rounded-xl border border-gold/30 shadow-sm hover:shadow flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>Thêm Món Ăn Mới (+)</span>
            </a>
        </div>
    </div>

    <!-- Alert Flash -->
    <?php if (isset($messages[$message])): ?>
        <div class="p-4 rounded-xl text-xs font-semibold flex items-center gap-2 bg-emerald-50 text-emerald-900 border border-emerald-300">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span><?= View::escape($messages[$message]) ?></span>
        </div>
    <?php endif; ?>

    <!-- 5 Metrics Cards (Theo Ảnh số 4) -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TỔNG SỐ MÓN ĂN</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-slate-900"><?= $totalDishes ?></strong>
                <span class="text-slate-400 text-xs font-normal">/ <?= count($categories) ?> Danh mục</span>
            </div>
            <span class="text-[10px] text-slate-400 block">&bull; Cập nhật 4 phút trước</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">ĐANG PHỤC VỤ</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-emerald-700"><?= $availableCount ?></strong>
                <span class="text-emerald-700 text-xs font-bold"><?= $totalDishes > 0 ? round(($availableCount / $totalDishes) * 100, 1) : 0 ?>%</span>
            </div>
            <span class="text-[10px] text-emerald-600 font-semibold block">Sẵn sàng gọi món</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TẠM HẾT MÓN (STOCK-OUT)</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-rose-700"><?= sprintf('%02d', $outOfStockCount) ?></strong>
                <span class="text-rose-600 text-xs font-normal">Bếp cần bù</span>
            </div>
            <span class="text-[10px] text-rose-600 font-semibold block">&Delta; 2 nguyên liệu nhập khẩu trễ</span>
        </div>

        <div class="bg-admin-navy text-white p-4 rounded-2xl border border-slate-800 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gold-light block">MICHELIN SIGNATURE</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-gold">08</strong>
                <span class="text-slate-400 text-xs font-normal">Đặc sản VIP</span>
            </div>
            <span class="text-[10px] text-gold-light/80 block">Chiếm 54% tổng doanh số</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">COMBO TIỆC KÍCH HOẠT</span>
            <div class="flex items-baseline gap-2">
                <strong class="text-2xl font-headline font-bold text-slate-900">06</strong>
                <span class="text-slate-400 text-xs font-normal">Set Menu</span>
            </div>
            <span class="text-[10px] text-slate-500 block">Áp dụng 2 sảnh tiệc riêng</span>
        </div>
    </div>

    <!-- Category Pills & Filter Strip -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <!-- Category Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold custom-scroll">
            <a href="/admin/menu" class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $selectedCategoryId === null ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Tất cả (<?= $totalDishes ?>)
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/admin/menu?category=<?= (int)$cat['id'] ?>" class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= (int)$selectedCategoryId === (int)$cat['id'] ? 'bg-admin-navy text-gold font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= View::escape($cat['name']) ?> (<?= (int)($cat['dish_count'] ?? 0) ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Bar -->
        <form action="/admin/menu" method="GET" class="flex flex-col sm:flex-row items-center gap-3 text-xs">
            <?php if ($selectedCategoryId !== null): ?>
                <input type="hidden" name="category" value="<?= (int)$selectedCategoryId ?>">
            <?php endif; ?>
            <div class="relative flex-1 w-full">
                <input type="search" name="q" value="<?= View::escape($search) ?>" placeholder="Tìm tên món, mã SKU, nguyên liệu..." 
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white text-xs">
                <span class="material-symbols-outlined text-[18px] text-slate-400 absolute left-2.5 top-2">search</span>
            </div>
            <div class="flex items-center gap-2 text-slate-500 font-medium">
                <span>Bộ lọc nhanh:</span>
                <button type="button" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white">&starf; Món nổi bật</button>
                <button type="button" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white">Bán chạy</button>
                <button type="button" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white">Bếp khuyên dùng</button>
            </div>
        </form>
    </div>

    <!-- Dishes Table (Chuẩn theo Ảnh số 4) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                        <th class="py-3.5 px-4">MÓN ĂN & MÃ SKU</th>
                        <th class="py-3.5 px-4">DANH MỤC</th>
                        <th class="py-3.5 px-4">GIÁ NIÊM YẾT</th>
                        <th class="py-3.5 px-4">ĐẶC TÍNH & THẺ</th>
                        <th class="py-3.5 px-4">TÌNH TRẠNG KDS</th>
                        <th class="py-3.5 px-4">SMART PAIRING</th>
                        <th class="py-3.5 px-4 text-right">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($dishes)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">restaurant_menu</span>
                                <span class="font-semibold">Không tìm thấy món ăn nào trong danh mục này.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dishes as $d): 
                            $isAvail = !empty($d['is_available']);
                        ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Món ăn & Mã SKU -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200">
                                            <?php if (!empty($d['image'])): ?>
                                                <img src="<?= View::escape($d['image']) ?>" alt="" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center font-bold text-slate-400 font-headline">FDS</div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong class="text-slate-900 block font-bold text-sm"><?= View::escape($d['name']) ?></strong>
                                            <span class="text-[10px] text-slate-400 font-mono">SKU: FOOD-<?= sprintf('%04d', $d['id']) ?> &bull; Chuẩn bị: <?= (int)($d['preparation_time'] ?? 20) ?>p</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Danh mục -->
                                <td class="py-4 px-4 font-medium text-slate-700">
                                    <?= View::escape($d['category_name'] ?? 'Món chính') ?>
                                </td>

                                <!-- Giá niêm yết -->
                                <td class="py-4 px-4">
                                    <strong class="text-slate-900 block text-sm font-bold"><?= View::currency($d['price']) ?></strong>
                                    <span class="text-[10px] text-slate-400">Đã gồm VAT 8%</span>
                                </td>

                                <!-- Đặc tính & Thẻ -->
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            Signature
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600">
                                            Organic 100%
                                        </span>
                                    </div>
                                </td>

                                <!-- Tình trạng KDS (Còn món / Hết món toggle) -->
                                <td class="py-4 px-4">
                                    <form action="/admin/menu/dishes/<?= (int)$d['id'] ?>/availability" method="POST" class="inline">
                                        <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                        <button type="submit" class="flex items-center gap-2 group cursor-pointer" title="Nhấn để đổi trạng thái Còn món / Hết món">
                                            <div class="w-8 h-4 rounded-full transition <?= $isAvail ? 'bg-emerald-500' : 'bg-slate-300' ?> p-0.5 flex items-center <?= $isAvail ? 'justify-end' : 'justify-start' ?>">
                                                <div class="w-3 h-3 rounded-full bg-white shadow-sm"></div>
                                            </div>
                                            <span class="text-[11px] font-semibold <?= $isAvail ? 'text-emerald-700' : 'text-rose-600' ?>">
                                                <?= $isAvail ? 'Còn món' : 'Hết món' ?>
                                            </span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Smart Pairing -->
                                <td class="py-4 px-4 text-slate-600 text-[11px]">
                                    <span class="block text-gold-dark font-semibold">&bull; Rượu Bordeaux 2015</span>
                                    <span class="text-slate-400 text-[10px]">Rau củ nướng than</span>
                                </td>

                                <!-- Thao tác -->
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="/admin/menu/dishes/<?= (int)$d['id'] ?>/edit" 
                                           class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-gold hover:border-gold transition" title="Sửa món">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </a>
                                        <form action="/admin/menu/dishes/<?= (int)$d['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('Xóa món <?= addslashes(View::escape($d['name'])) ?>?')">
                                            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                            <button type="submit" class="p-1.5 rounded-lg border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-300 transition" title="Xóa món">
                                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
