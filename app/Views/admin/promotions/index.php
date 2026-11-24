<?php
use App\Core\View;

$combos = $combos ?? [];
$vouchers = $vouchers ?? [];
?>

<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-admin-navy text-gold border border-gold/30">
                    <span>QUẢN LÝ ƯU ĐÃI & MARKETING</span>
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-bold text-slate-900">
                Combo Set Tiệc & Mã Khuyến Mãi (Vouchers)
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Quản lý các gói setup tiệc độc bản (sinh nhật, kỷ niệm, đối tác) và các mã ưu đãi giảm giá kích cầu đặt bàn.
            </p>
        </div>

        <button type="button" onclick="document.getElementById('createVoucherModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-admin-navy text-gold text-xs font-bold hover:bg-slate-800 transition flex items-center gap-2 shadow-sm self-start">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>+ Tạo Voucher Mới</span>
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>Tạo Voucher mới thành công.</span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            <span><?= $error === 'code_exists' ? 'Mã voucher này đã tồn tại, vui lòng đặt mã khác!' : 'Dữ liệu không hợp lệ!' ?></span>
        </div>
    <?php endif; ?>

    <!-- Combo Setup Tiệc Độc Bản -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-headline font-bold text-lg text-slate-900">Danh Sách Gói Combo & Setup Tiệc Độc Bản</h3>
                <p class="text-xs text-slate-500">Các gói setup hoa tươi, bóng bay, bánh kem và menu tiệc cho khách đặt trước.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-gold/10 text-gold-dark font-bold text-xs"><?= count($combos) ?> Gói Hiện Có</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($combos as $cb): ?>
                <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50 flex flex-col justify-between space-y-3 hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="px-2 py-0.5 rounded bg-admin-navy text-gold text-[10px] font-bold uppercase tracking-wider">
                                <?= View::escape($cb['target_occasion'] ?? 'Setup Tiệc') ?>
                            </span>
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Hoạt động</span>
                        </div>
                        <h4 class="font-headline font-bold text-sm text-slate-900"><?= View::escape($cb['name']) ?></h4>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?= View::escape($cb['description'] ?? '') ?></p>
                        
                        <div class="mt-3 p-2.5 bg-white rounded-xl border border-slate-100 text-[11px] text-slate-600">
                            <strong class="text-slate-800 block mb-0.5">Bao gồm:</strong>
                            <p class="line-clamp-3 text-slate-500"><?= View::escape($cb['includes'] ?? 'Trang trí hoa, nến, bóng bay & quà tặng độc quyền.') ?></p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Giá trọn gói</span>
                            <strong class="font-bold text-sm text-gold-dark"><?= View::currency((string)(int)$cb['price']) ?></strong>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400">Tự động áp dụng</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Danh Sách Vouchers / Khuyến Mãi -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-headline font-bold text-lg text-slate-900">Danh Sách Mã Ưu Đãi & Khuyến Mãi (Vouchers)</h3>
                <p class="text-xs text-slate-500">Mã giảm giá trực tiếp theo phần trăm hoặc số tiền cố định khi thanh toán hoặc đặt bàn.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-bold text-xs"><?= count($vouchers) ?> Voucher Đang Chạy</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">MÃ CODE</th>
                        <th class="py-3 px-4">TIÊU ĐỀ ƯU ĐÃI</th>
                        <th class="py-3 px-4">MỨC GIẢM</th>
                        <th class="py-3 px-4">ĐƠN TỐI THIỂU</th>
                        <th class="py-3 px-4">HIỆU LỰC</th>
                        <th class="py-3 px-4">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($vouchers)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Chưa có mã khuyến mãi nào được tạo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vouchers as $vc): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-admin-navy text-sm">
                                    <span class="px-2.5 py-1 rounded-lg bg-gold/15 text-gold-dark border border-gold/30">
                                        <?= View::escape($vc['code']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <strong class="text-slate-900 block"><?= View::escape($vc['title']) ?></strong>
                                    <span class="text-slate-400 text-[11px]"><?= View::escape($vc['description'] ?? '') ?></span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-rose-600">
                                    <?= $vc['discount_type'] === 'percentage' ? ((int)$vc['discount_value'] . ' %') : View::currency((string)(int)$vc['discount_value']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700">
                                    <?= !empty($vc['min_order_amount']) ? View::currency((string)(int)$vc['min_order_amount']) : 'Không giới hạn' ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                    <?= date('d/m/Y', strtotime((string)$vc['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime((string)$vc['end_date'])) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200">
                                        &bull; Đang kích hoạt
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tạo Voucher Mới -->
<div id="createVoucherModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-headline font-bold text-base text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gold">local_activity</span>
                Tạo Mã Khuyến Mãi Mới
            </h3>
            <button onclick="document.getElementById('createVoucherModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="/admin/vouchers" method="POST" class="space-y-3.5">
            <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">MÃ CODE VOUCHER (*)</label>
                <input type="text" name="code" required placeholder="VD: VIP15, SUMMER2026, FOODIESFIRST" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono uppercase font-bold text-slate-900">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">TIÊU ĐỀ CHƯƠNG TRÌNH (*)</label>
                <input type="text" name="title" required placeholder="VD: Ưu Đãi 15% Cho Khách Đặt Bàn Lần Đầu" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">LOẠI GIẢM GIÁ</label>
                    <select name="discount_type" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                        <option value="percentage">Theo phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định (VNĐ)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">MỨC GIẢM (*)</label>
                    <input type="number" name="discount_value" required min="1" step="any" placeholder="VD: 15 hoặc 200000" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-rose-600">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">ĐƠN HÀNG TỐI THIỂU (VNĐ)</label>
                <input type="number" name="min_order_amount" min="0" step="100000" placeholder="0" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">MÔ TẢ CHI TIẾT ĐIỀU KIỆN ÁP DỤNG</label>
                <textarea name="description" rows="2" placeholder="Áp dụng cho hóa đơn từ 2.000.000 ₫ khi đặt bàn trước..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('createVoucherModal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-admin-navy text-gold rounded-xl font-bold hover:bg-slate-800 transition">Tạo Voucher</button>
            </div>
        </form>
    </div>
</div>
