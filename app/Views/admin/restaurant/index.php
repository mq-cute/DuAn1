<?php

declare(strict_types=1);

use App\Core\View;

$activeTab = $activeTab ?? 'general';
?>

<div class="max-w-[1536px] w-full mx-auto p-4 md:p-6 lg:p-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary">THIẾT LẬP HỆ THỐNG</span>
                <span class="text-stone-300">•</span>
                <span class="text-xs text-on-surface-variant">L'Épicure Gastronomie</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-bold text-on-surface mt-1">Thông Tin Nhà Hàng &amp; Thiết Lập Vận Hành</h1>
            <p class="text-sm text-on-surface-variant">Cấu hình định danh thương hiệu, khung giờ phục vụ, chính sách đặt bàn, thuế VAT và phí dịch vụ.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/" target="_blank" class="px-4 py-2.5 rounded-xl border border-outline-variant/40 bg-white text-on-surface text-xs font-semibold hover:border-primary transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                <span>Xem trang chủ</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message === 'general_updated'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Cập nhật thông tin cơ bản của nhà hàng thành công!</span>
        </div>
    <?php elseif ($message === 'hours_updated'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Cập nhật lịch giờ mở cửa 7 ngày trong tuần thành công!</span>
        </div>
    <?php elseif ($message === 'pricing_updated'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Cập nhật cấu hình thuế VAT và phí dịch vụ thành công!</span>
        </div>
    <?php elseif ($message === 'policies_updated'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Cập nhật các chính sách nhà hàng thành công!</span>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span><?= View::escape($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Horizontal Tabs -->
    <div class="flex items-center space-x-2 border-b border-outline-variant/30 overflow-x-auto pb-px">
        <a href="/admin/restaurant?tab=general" class="px-5 py-3 text-xs font-bold rounded-t-xl transition-all border-b-2 flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'general' ? 'border-primary text-primary bg-white shadow-sm' : 'border-transparent text-on-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined text-[18px]">storefront</span>
            <span>1. Thông tin cơ bản</span>
        </a>
        <a href="/admin/restaurant?tab=hours" class="px-5 py-3 text-xs font-bold rounded-t-xl transition-all border-b-2 flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'hours' ? 'border-primary text-primary bg-white shadow-sm' : 'border-transparent text-on-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined text-[18px]">schedule</span>
            <span>2. Giờ hoạt động</span>
        </a>
        <a href="/admin/restaurant?tab=pricing" class="px-5 py-3 text-xs font-bold rounded-t-xl transition-all border-b-2 flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'pricing' ? 'border-primary text-primary bg-white shadow-sm' : 'border-transparent text-on-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined text-[18px]">percent</span>
            <span>3. Thuế &amp; Phí</span>
        </a>
        <a href="/admin/restaurant?tab=policies" class="px-5 py-3 text-xs font-bold rounded-t-xl transition-all border-b-2 flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'policies' ? 'border-primary text-primary bg-white shadow-sm' : 'border-transparent text-on-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined text-[18px]">policy</span>
            <span>4. Chính sách &amp; Quy định</span>
        </a>
    </div>

    <!-- TAB 1: THÔNG TIN CƠ BẢN -->
    <?php if ($activeTab === 'general'): ?>
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-outline-variant/30 shadow-sm max-w-4xl">
            <div class="pb-6 border-b border-outline-variant/30">
                <h2 class="text-xl font-headline font-bold text-on-surface">Định Danh &amp; Thông Tin Nhà Hàng</h2>
                <p class="text-xs text-on-surface-variant mt-1">Thông tin này được hiển thị công khai trên tiêu đề, chân trang và hóa đơn tính tiền.</p>
            </div>

            <form action="/admin/restaurant/general" method="post" class="mt-6 space-y-5 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="restaurant_name" class="block font-bold text-on-surface uppercase mb-1">Tên nhà hàng *</label>
                        <input id="restaurant_name" type="text" name="restaurant_name" value="<?= View::escape($settings['restaurant_name'] ?? "L'Épicure Gastronomie") ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary font-semibold">
                    </div>

                    <div>
                        <label for="restaurant_tagline" class="block font-bold text-on-surface uppercase mb-1">Khẩu hiệu / Slogan</label>
                        <input id="restaurant_tagline" type="text" name="restaurant_tagline" value="<?= View::escape($settings['restaurant_tagline'] ?? '') ?>" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="restaurant_address" class="block font-bold text-on-surface uppercase mb-1">Địa chỉ trụ sở *</label>
                    <input id="restaurant_address" type="text" name="restaurant_address" value="<?= View::escape($settings['restaurant_address'] ?? '') ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="restaurant_phone" class="block font-bold text-on-surface uppercase mb-1">Số điện thoại / Hotline *</label>
                        <input id="restaurant_phone" type="text" name="restaurant_phone" value="<?= View::escape($settings['restaurant_phone'] ?? '') ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    </div>

                    <div>
                        <label for="restaurant_email" class="block font-bold text-on-surface uppercase mb-1">Địa chỉ Email *</label>
                        <input id="restaurant_email" type="email" name="restaurant_email" value="<?= View::escape($settings['restaurant_email'] ?? '') ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    </div>

                    <div>
                        <label for="restaurant_tax_number" class="block font-bold text-on-surface uppercase mb-1">Mã số thuế (MST)</label>
                        <input id="restaurant_tax_number" type="text" name="restaurant_tax_number" value="<?= View::escape($settings['restaurant_tax_number'] ?? '') ?>" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="restaurant_description" class="block font-bold text-on-surface uppercase mb-1">Mô tả giới thiệu nhà hàng</label>
                    <textarea id="restaurant_description" name="restaurant_description" rows="4" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"><?= View::escape($settings['restaurant_description'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="restaurant_logo" class="block font-bold text-on-surface uppercase mb-1">Đường dẫn Logo</label>
                        <input id="restaurant_logo" type="text" name="restaurant_logo" value="<?= View::escape($settings['restaurant_logo'] ?? '/assets/images/logo.png') ?>" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    </div>

                    <div>
                        <label for="restaurant_cover_image" class="block font-bold text-on-surface uppercase mb-1">Đường dẫn Ảnh bìa / Banner</label>
                        <input id="restaurant_cover_image" type="text" name="restaurant_cover_image" value="<?= View::escape($settings['restaurant_cover_image'] ?? '/assets/images/banner.jpg') ?>" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                    </div>
                </div>

                <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-xl hover:bg-primary-container transition shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Lưu thông tin cơ bản</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- TAB 2: GIỜ HOẠT ĐỘNG -->
    <?php if ($activeTab === 'hours'): ?>
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-outline-variant/30 shadow-sm max-w-4xl">
            <div class="pb-6 border-b border-outline-variant/30">
                <h2 class="text-xl font-headline font-bold text-on-surface">Lịch Giờ Mở Cửa &amp; Ngày Nghỉ Trong Tuần</h2>
                <p class="text-xs text-on-surface-variant mt-1">Cấu hình khung giờ nhận khách và đánh dấu những ngày tạm ngưng phục vụ.</p>
            </div>

            <form action="/admin/restaurant/hours" method="post" class="mt-6 space-y-6 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div class="divide-y divide-outline-variant/20 border border-outline-variant/20 rounded-2xl overflow-hidden">
                    <?php foreach ($operatingHours as $h): ?>
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-surface-container-low/30 transition-colors">
                            <div class="w-36">
                                <span class="font-bold text-on-surface text-sm"><?= View::escape($h['day_name']) ?></span>
                                <span class="block text-[11px] text-stone-400 capitalize">(<?= View::escape($h['day_of_week']) ?>)</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-stone-500 font-medium">Mở cửa:</span>
                                    <input type="time" name="hours[<?= View::escape($h['day_of_week']) ?>][open_time]" value="<?= substr((string) $h['open_time'], 0, 5) ?>" class="text-xs py-1.5 px-2.5 rounded-lg border border-outline-variant/40 focus:ring-1 focus:ring-primary font-mono font-bold">
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="text-stone-500 font-medium">Đóng cửa:</span>
                                    <input type="time" name="hours[<?= View::escape($h['day_of_week']) ?>][close_time]" value="<?= substr((string) $h['close_time'], 0, 5) ?>" class="text-xs py-1.5 px-2.5 rounded-lg border border-outline-variant/40 focus:ring-1 focus:ring-primary font-mono font-bold">
                                </div>

                                <label class="flex items-center gap-1.5 cursor-pointer text-stone-700 font-semibold ml-auto">
                                    <input type="checkbox" name="hours[<?= View::escape($h['day_of_week']) ?>][is_closed]" value="1" <?= (bool) $h['is_closed'] ? 'checked' : '' ?> class="rounded border-outline-variant/40 text-red-600 focus:ring-red-500">
                                    <span class="<?= (bool) $h['is_closed'] ? 'text-red-700 font-bold' : '' ?>">Tạm nghỉ</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-xl hover:bg-primary-container transition shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Lưu lịch hoạt động</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- TAB 3: THUẾ & PHÍ -->
    <?php if ($activeTab === 'pricing'): ?>
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-outline-variant/30 shadow-sm max-w-4xl">
            <div class="pb-6 border-b border-outline-variant/30">
                <h2 class="text-xl font-headline font-bold text-on-surface">Thuế Giá Trị Gia Tăng (VAT) &amp; Phí Dịch Vụ</h2>
                <p class="text-xs text-on-surface-variant mt-1">Các giá trị này tự động kết nối vào công thức tính tiền của Đơn gọi món và Hóa đơn thanh toán.</p>
            </div>

            <form action="/admin/restaurant/pricing" method="post" class="mt-6 space-y-6 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <!-- Khối VAT -->
                <div class="p-6 rounded-2xl bg-surface-container-low/40 border border-outline-variant/30 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[22px]">receipt_long</span>
                            <div>
                                <h3 class="font-bold text-on-surface text-sm">Thuế Giá Trị Gia Tăng (VAT)</h3>
                                <p class="text-stone-500">Áp dụng trực tiếp trên tổng giá trị các món ăn và thức uống.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer font-bold">
                            <input type="checkbox" name="vat_enabled" value="1" <?= $pricing['vat_enabled'] ? 'checked' : '' ?> class="rounded border-outline-variant/40 text-primary focus:ring-primary w-4 h-4">
                            <span>Bật tính thuế VAT</span>
                        </label>
                    </div>

                    <div class="w-full sm:w-64">
                        <label for="tax_rate" class="block font-bold text-on-surface uppercase mb-1">Mức thuế VAT (%)</label>
                        <div class="relative">
                            <input id="tax_rate" type="number" name="tax_rate" min="0" max="100" step="0.5" value="<?= $pricing['tax_rate_percent'] ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary font-bold">
                            <span class="absolute right-3 top-2.5 text-stone-500 font-bold">%</span>
                        </div>
                    </div>
                </div>

                <!-- Khối Service Charge -->
                <div class="p-6 rounded-2xl bg-surface-container-low/40 border border-outline-variant/30 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary text-[22px]">room_service</span>
                            <div>
                                <h3 class="font-bold text-on-surface text-sm">Phí Dịch Vụ Nhà Hàng (Service Charge)</h3>
                                <p class="text-stone-500">Phí phục vụ tiệc cao cấp và dịch vụ Sommelier tại bàn.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer font-bold">
                            <input type="checkbox" name="service_charge_enabled" value="1" <?= $pricing['service_charge_enabled'] ? 'checked' : '' ?> class="rounded border-outline-variant/40 text-secondary focus:ring-secondary w-4 h-4">
                            <span>Bật phí dịch vụ</span>
                        </label>
                    </div>

                    <div class="w-full sm:w-64">
                        <label for="service_charge_rate" class="block font-bold text-on-surface uppercase mb-1">Mức phí dịch vụ (%)</label>
                        <div class="relative">
                            <input id="service_charge_rate" type="number" name="service_charge_rate" min="0" max="100" step="0.5" value="<?= $pricing['service_charge_rate_percent'] ?>" required class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-secondary font-bold">
                            <span class="absolute right-3 top-2.5 text-stone-500 font-bold">%</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-2">
                    <span class="material-symbols-outlined text-[18px] text-amber-700">info</span>
                    <div>
                        <strong>Công thức tính tự động:</strong> Tổng thanh toán = Tổng tiền món + (Tổng tiền món × Phí dịch vụ%) + (Tổng tiền món × Thuế VAT%) - Giảm giá.
                    </div>
                </div>

                <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-xl hover:bg-primary-container transition shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Lưu thiết lập thuế &amp; phí</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- TAB 4: CHÍNH SÁCH NHÀ HÀNG -->
    <?php if ($activeTab === 'policies'): ?>
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-outline-variant/30 shadow-sm max-w-4xl">
            <div class="pb-6 border-b border-outline-variant/30">
                <h2 class="text-xl font-headline font-bold text-on-surface">Chính Sách Nhà Hàng &amp; Quy Định</h2>
                <p class="text-xs text-on-surface-variant mt-1">Quy định đặt bàn, thời gian giữ chỗ, chính sách hủy tiệc và hoàn tiền.</p>
            </div>

            <form action="/admin/restaurant/policies" method="post" class="mt-6 space-y-5 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div>
                    <label for="booking_policy" class="block font-bold text-on-surface uppercase mb-1">Chính sách đặt bàn</label>
                    <textarea id="booking_policy" name="booking_policy" rows="4" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"><?= View::escape($settings['booking_policy'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="cancellation_policy" class="block font-bold text-on-surface uppercase mb-1">Chính sách hủy đặt bàn</label>
                    <textarea id="cancellation_policy" name="cancellation_policy" rows="4" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"><?= View::escape($settings['cancellation_policy'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="refund_policy" class="block font-bold text-on-surface uppercase mb-1">Chính sách hoàn tiền</label>
                    <textarea id="refund_policy" name="refund_policy" rows="4" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"><?= View::escape($settings['refund_policy'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="general_terms" class="block font-bold text-on-surface uppercase mb-1">Các quy định chung khác</label>
                    <textarea id="general_terms" name="general_terms" rows="3" class="w-full text-xs py-2.5 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"><?= View::escape($settings['general_terms'] ?? '') ?></textarea>
                </div>

                <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-xl hover:bg-primary-container transition shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Lưu chính sách</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
