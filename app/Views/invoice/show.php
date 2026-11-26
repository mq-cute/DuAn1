<?php

declare(strict_types=1);

use App\Core\View;

$paymentMethodLabels = [
    'cash' => 'Tiền mặt (Cash)',
    'card' => 'Thẻ thanh toán (Credit / Debit Card)',
    'transfer' => 'Chuyển khoản trực tiếp (Bank Transfer)',
    'ewallet' => 'Ví điện tử (E-Wallet)',
];

$statusConfig = [
    'draft' => ['label' => 'BẢN NHÁP', 'badge' => 'bg-stone-100 text-stone-700 border-stone-200'],
    'issued' => ['label' => 'ĐÃ PHÁT HÀNH', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
    'paid' => ['label' => 'ĐÃ THANH TOÁN', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'partially_refunded' => ['label' => 'HOÀN TIỀN MỘT PHẦN', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'refunded' => ['label' => 'ĐÃ HOÀN TIỀN TOÀN BỘ', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
    'cancelled' => ['label' => 'ĐÃ HỦY', 'badge' => 'bg-red-100 text-red-800 border-red-200'],
];

$message = $message ?? '';
$error = $error ?? '';
$csrfToken = $csrfToken ?? '';

$invStatus = $invoice['status'] ?? 'paid';
$stInfo = $statusConfig[$invStatus] ?? ['label' => strtoupper($invStatus), 'badge' => 'bg-stone-100 text-stone-700 border-stone-200'];
$remainingRefundable = (float) ($invoice['remaining_refundable'] ?? ($invoice['total'] ?? 0));
$totalRefunded = (float) ($invoice['total_refunded'] ?? 0);
$isCustomer = ($currentUser['role'] ?? '') === 'customer';
$isStaffOrAdmin = in_array($currentUser['role'] ?? '', ['staff', 'admin'], true);
$canRefund = $isStaffOrAdmin && in_array($invStatus, ['paid', 'partially_refunded'], true) && $remainingRefundable > 0;
$canEdit = $isStaffOrAdmin && !in_array($invStatus, ['cancelled', 'refunded'], true);
?>

<?php if ($isCustomer): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                "primary": "#870000",
                "primary-container": "#af140b",
                "primary-fixed": "#ffdad4",
                "on-primary": "#ffffff",
                "secondary": "#4a6633",
                "secondary-container": "#c8eaa9",
                "on-secondary": "#ffffff",
                "background": "#fff8f5",
                "surface": "#fff8f5",
                "surface-container-low": "#fff1e8",
                "on-surface": "#231a11",
                "on-surface-variant": "#5b403c",
                "outline": "#906f6a",
                "outline-variant": "#e4beb8",
                "tertiary-container": "#844800",
              },
              fontFamily: {
                "headline": ["Playfair Display", "Georgia", "serif"],
                "body": ["Plus Jakarta Sans", "Arial", "sans-serif"],
              }
            }
          }
        };
    </script>
    <style>
        .site-header { margin-bottom: 24px; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
    </style>
<?php endif; ?>

<style>
@media print {
    .no-print, header, footer, .site-header, .site-footer {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .print-area {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

<div class="max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
    <!-- Action Bar (Not shown in print) -->
    <div class="no-print flex flex-col sm:flex-row items-center justify-between pb-4 border-b border-outline-variant/30 gap-4">
        <div class="flex items-center space-x-3">
            <?php if ($isCustomer): ?>
                <a href="/account/orders/<?= (int) ($invoice['order_id'] ?? 0) ?>" class="p-2 rounded-xl border border-outline-variant/40 hover:bg-stone-100 text-on-surface-variant flex items-center justify-center" title="Quay lại chi tiết đơn hàng">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
            <?php else: ?>
                <a href="/admin/invoices" class="p-2 rounded-xl border border-outline-variant/40 hover:bg-stone-100 text-on-surface-variant flex items-center justify-center" title="Trở về danh sách hóa đơn">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
            <?php endif; ?>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase border <?= $stInfo['badge'] ?>">
                        <?= View::escape($stInfo['label']) ?>
                    </span>
                    <h1 class="text-lg font-headline font-bold text-on-surface">Hóa Đơn #<?= View::escape($invoice['invoice_number']) ?></h1>
                </div>
                <p class="text-xs text-on-surface-variant">Phát hành lúc <?= date('H:i:s - d/m/Y', strtotime($invoice['issued_at'])) ?></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <?php if ($canEdit): ?>
                <button type="button" onclick="document.getElementById('editModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl border border-outline-variant/40 text-on-surface text-xs font-semibold hover:border-primary hover:text-primary transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">edit_note</span>
                    <span>Sửa sai thông tin</span>
                </button>
            <?php endif; ?>

            <?php if ($canRefund): ?>
                <button type="button" onclick="document.getElementById('refundModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-purple-100 text-purple-900 border border-purple-300 text-xs font-bold hover:bg-purple-200 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">currency_exchange</span>
                    <span>Hoàn tiền</span>
                </button>
            <?php endif; ?>

            <a href="/invoices/<?= $invoice['id'] ?>/pdf" target="_blank" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 text-xs font-bold hover:from-amber-600 hover:to-amber-700 shadow-sm transition flex items-center gap-1.5" title="Xuất file PDF chính thức từ Backend máy chủ">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                <span>Xuất File PDF (Backend)</span>
            </a>

            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm" title="In trực tiếp trên trình duyệt">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>In Nhanh</span>
            </button>

            <?php if ($isCustomer): ?>
                <a href="/account/orders" class="px-3.5 py-2 rounded-xl border border-outline-variant/40 text-on-surface text-xs font-semibold hover:bg-stone-50 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                    <span>Đơn của tôi</span>
                </a>
            <?php endif; ?>

            <?php if ($isStaffOrAdmin): ?>
                <a href="/staff/pos?table_id=<?= (int) ($invoice['table_id'] ?? 0) ?>" class="px-3 py-2 rounded-xl border border-outline-variant/40 text-on-surface text-xs font-semibold hover:bg-stone-50 transition">
                    Thu ngân POS
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message === 'refund_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Ghi nhận hoàn tiền thành công! Phiếu hoàn tiền đã được lưu trữ an toàn.</span>
        </div>
    <?php elseif ($message === 'edit_success'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Cập nhật thông tin hóa đơn thành công. Dữ liệu thay đổi đã được lưu vết lịch sử.</span>
        </div>
    <?php elseif ($message === 'payment_confirmed'): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>Xác nhận thanh toán và phát hành hóa đơn thành công!</span>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span><?= View::escape($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Official Printable Invoice Area -->
    <div class="print-area bg-white rounded-3xl p-8 sm:p-12 border border-outline-variant/30 shadow-md space-y-8">
        <!-- Restaurant Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start border-b border-outline-variant/30 pb-6 gap-6">
            <div class="space-y-1">
                <div class="flex items-baseline space-x-3">
                    <span class="text-3xl font-headline font-bold text-primary tracking-tight">L'Épicure</span>
                    <span class="text-xs font-bold uppercase tracking-widest text-tertiary-container">Gastronomie</span>
                </div>
                <p class="text-xs text-stone-600 font-medium">Ẩm thực Pháp Đương đại &amp; Hầm rượu Thượng hạng</p>
                <p class="text-xs text-on-surface-variant mt-2">
                    Địa chỉ: Số 18 Đại Lộ Lê Duẩn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh<br/>
                    Hotline: (+84) 28 3822 8899 • MST: 0314892341
                </p>
            </div>

            <div class="sm:text-right space-y-1">
                <span class="text-xs uppercase tracking-wider text-outline font-bold">PHIẾU HÓA ĐƠN TÍNH TIỀN</span>
                <div class="text-xl font-headline font-bold text-primary">
                    <?= View::escape($invoice['invoice_number']) ?>
                </div>
                <p class="text-xs text-on-surface-variant">
                    Ngày: <strong><?= date('d/m/Y', strtotime($invoice['issued_at'])) ?></strong><br/>
                    Giờ: <strong><?= date('H:i', strtotime($invoice['issued_at'])) ?></strong>
                </p>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-surface-container-low/50 text-xs text-on-surface-variant border border-outline-variant/20">
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Bàn &amp; Khu vực:</span>
                <strong class="text-on-surface text-sm"><?= View::escape($invoice['table_code'] ?? 'Bàn') ?></strong>
                <span class="block text-[11px]"><?= View::escape($invoice['area_name'] ?? '') ?></span>
            </div>
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Thực khách:</span>
                <strong class="text-on-surface text-sm"><?= View::escape($invoice['customer_name'] ?: 'Khách tại bàn') ?></strong>
            </div>
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Thu ngân / Phục vụ:</span>
                <strong class="text-on-surface text-sm"><?= View::escape($invoice['cashier_name'] ?? $currentUser['name'] ?? 'Nhân viên') ?></strong>
            </div>
            <div>
                <span class="text-stone-400 block uppercase text-[10px] tracking-wider">Phương thức:</span>
                <strong class="text-secondary text-sm"><?= View::escape($paymentMethodLabels[$invoice['payment_method']] ?? $invoice['payment_method']) ?></strong>
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b-2 border-outline-variant/40 text-on-surface uppercase font-bold tracking-wider">
                        <th class="py-3 px-2 w-10 text-center">STT</th>
                        <th class="py-3 px-4">Tên món ăn &amp; Đồ uống</th>
                        <th class="py-3 px-3 text-center w-16">SL</th>
                        <th class="py-3 px-4 text-right w-32">Đơn giá</th>
                        <th class="py-3 px-4 text-right w-36">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    <?php $index = 1; ?>
                    <?php foreach ($invoice['items'] as $item): ?>
                        <tr>
                            <td class="py-3 px-2 text-center text-stone-400 font-medium"><?= $index++ ?></td>
                            <td class="py-3 px-4 font-semibold text-on-surface">
                                <?= View::escape($item['dish_name']) ?>
                                <?php if (!empty($item['note'])): ?>
                                    <span class="block text-[10px] text-stone-400 font-normal">Ghi chú: <?= View::escape($item['note']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-on-surface"><?= (int) $item['quantity'] ?></td>
                            <td class="py-3 px-4 text-right text-stone-600"><?= View::currency($item['unit_price']) ?></td>
                            <td class="py-3 px-4 text-right font-bold text-on-surface"><?= View::currency($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary Breakdown -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-outline-variant/30 gap-6">
            <div class="space-y-2 text-xs">
                <?php if ($totalRefunded > 0): ?>
                    <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">info</span>
                            <span>Trạng thái hoàn tiền:</span>
                        </div>
                        <p>Đã hoàn trả: <strong><?= View::currency((int) round($totalRefunded)) ?></strong></p>
                        <p>Số tiền còn lại có thể hoàn: <strong><?= View::currency((int) round($remainingRefundable)) ?></strong></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="w-full sm:w-80 space-y-2 text-xs text-on-surface-variant">
                <div class="flex justify-between">
                    <span>Tổng tiền món &amp; rượu:</span>
                    <strong class="text-on-surface"><?= View::currency($invoice['subtotal']) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Phí dịch vụ:</span>
                    <strong class="text-on-surface"><?= View::currency($invoice['service_charge']) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Thuế GTGT ẩm thực (VAT):</span>
                    <strong class="text-on-surface"><?= View::currency($invoice['tax']) ?></strong>
                </div>
                <?php if ((float) $invoice['discount'] > 0): ?>
                    <div class="flex justify-between text-secondary">
                        <span>Giảm giá / Ưu đãi:</span>
                        <strong>-<?= View::currency($invoice['discount']) ?></strong>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between pt-3 border-t-2 border-outline-variant/40 text-base font-headline font-bold text-on-surface">
                    <span>TỔNG THANH TOÁN:</span>
                    <span class="text-primary text-xl font-headline font-bold"><?= View::currency($invoice['total']) ?></span>
                </div>
            </div>
        </div>

        <!-- Footer Sign Off -->
        <div class="pt-8 border-t border-dashed border-outline-variant/40 text-center space-y-2">
            <p class="font-headline italic text-on-surface text-sm">
                Cảm ơn Quý khách đã trải nghiệm ẩm thực tại L'Épicure Gastronomie!
            </p>
            <p class="text-[11px] text-stone-400">
                Hóa đơn đã được lưu trữ trên hệ thống điện tử.<br/>
                Chúc Quý khách một ngày tuyệt vời và hẹn gặp lại!
            </p>
        </div>
    </div>

    <!-- Refund History Section (Chỉ hiển thị cho Staff / Admin nếu có hoàn tiền) -->
    <?php if ($isStaffOrAdmin && !empty($invoice['refunds'])): ?>
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/30 shadow-sm space-y-4 no-print">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2 text-purple-900 font-bold text-sm">
                    <span class="material-symbols-outlined text-[20px]">currency_exchange</span>
                    <span>Lịch Sử Hoàn Tiền Nội Bộ (<?= count($invoice['refunds']) ?> lượt)</span>
                </div>
                <span class="text-xs text-on-surface-variant font-semibold">
                    Tổng đã hoàn: <strong class="text-purple-700"><?= View::currency((int) round($totalRefunded)) ?></strong>
                </span>
            </div>
            <div class="divide-y divide-outline-variant/20">
                <?php foreach ($invoice['refunds'] as $rf): ?>
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div>
                            <div class="font-bold text-on-surface flex items-center gap-2">
                                <span class="text-purple-700 text-sm font-headline"><?= View::currency((int) round((float) $rf['amount'])) ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-100 text-purple-800 border border-purple-200">
                                    <?= View::escape($rf['status']) ?>
                                </span>
                                <span class="text-stone-400 font-normal">qua <?= View::escape($paymentMethodLabels[$rf['refund_method']] ?? $rf['refund_method']) ?></span>
                            </div>
                            <p class="text-stone-600 mt-0.5"><strong>Lý do:</strong> <?= View::escape($rf['reason']) ?></p>
                        </div>
                        <div class="sm:text-right text-stone-500 text-[11px]">
                            <div>Người duyệt: <strong><?= View::escape($rf['processor_name'] ?? 'Admin') ?></strong></div>
                            <div><?= date('H:i:s - d/m/Y', strtotime($rf['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit History Section (Chỉ hiển thị cho Staff / Admin nếu có sửa sai) -->
    <?php if ($isStaffOrAdmin && !empty($invoice['edit_histories'])): ?>
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/30 shadow-sm space-y-4 no-print">
            <div class="flex items-center gap-2 text-on-surface font-bold text-sm pb-3 border-b border-outline-variant/30">
                <span class="material-symbols-outlined text-[20px]">history</span>
                <span>Nhật Ký Chỉnh Sửa Sai Sót Hóa Đơn (<?= count($invoice['edit_histories']) ?> lượt)</span>
            </div>
            <div class="divide-y divide-outline-variant/20 text-xs">
                <?php foreach ($invoice['edit_histories'] as $eh): ?>
                    <?php
                    $old = json_decode($eh['old_data'], true) ?: [];
                    $new = json_decode($eh['new_data'], true) ?: [];
                    ?>
                    <div class="py-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-primary">Lý do: <?= View::escape($eh['reason']) ?></span>
                            <span class="text-[11px] text-stone-400">
                                Bởi <?= View::escape($eh['editor_name'] ?? 'Quản trị viên') ?> lúc <?= date('H:i:s - d/m/Y', strtotime($eh['created_at'])) ?>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-2.5 rounded-xl bg-stone-50 text-[11px] border border-stone-200">
                            <div>
                                <span class="block font-bold text-stone-500 uppercase">Dữ liệu trước sửa:</span>
                                <span>Khách: <?= View::escape($old['customer_name'] ?? '—') ?></span><br/>
                                <span>PTTT: <?= View::escape($paymentMethodLabels[$old['payment_method'] ?? ''] ?? ($old['payment_method'] ?? '—')) ?></span>
                            </div>
                            <div>
                                <span class="block font-bold text-emerald-700 uppercase">Dữ liệu sau sửa:</span>
                                <span>Khách: <?= View::escape($new['customer_name'] ?? '—') ?></span><br/>
                                <span>PTTT: <?= View::escape($paymentMethodLabels[$new['payment_method'] ?? ''] ?? ($new['payment_method'] ?? '—')) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Hoàn Tiền -->
<?php if ($canRefund): ?>
    <div id="refundModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-outline-variant/30">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-700 text-[24px]">currency_exchange</span>
                    <h3 class="text-base font-bold font-headline text-on-surface">Yêu Cầu Hoàn Tiền Nội Bộ</h3>
                </div>
                <button type="button" onclick="document.getElementById('refundModal').classList.add('hidden')" class="p-1 rounded-lg text-stone-400 hover:text-stone-700">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form action="/admin/invoices/<?= (int) $invoice['id'] ?>/refund" method="post" class="space-y-4 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 text-purple-900">
                    <p class="font-semibold">Hóa đơn: #<?= View::escape($invoice['invoice_number']) ?></p>
                    <p>Tổng tiền gốc: <strong><?= View::currency($invoice['total']) ?></strong></p>
                    <p>Số tiền tối đa có thể hoàn: <strong class="text-purple-700"><?= View::currency((int) round($remainingRefundable)) ?></strong></p>
                </div>

                <div>
                    <label for="refund-amount" class="block font-bold text-on-surface uppercase mb-1">Số tiền hoàn (VNĐ) *</label>
                    <input id="refund-amount" type="number" name="amount" min="1000" max="<?= (int) $remainingRefundable ?>" step="1000" value="<?= (int) $remainingRefundable ?>" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary font-bold">
                    <span class="text-[10px] text-stone-400">Không được vượt quá số tiền còn lại có thể hoàn.</span>
                </div>

                <div>
                    <label for="refund-method" class="block font-bold text-on-surface uppercase mb-1">Phương thức hoàn tiền *</label>
                    <select id="refund-method" name="refund_method" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                        <?php foreach ($paymentMethods as $mKey => $mLabel): ?>
                            <option value="<?= View::escape($mKey) ?>" <?= $invoice['payment_method'] === $mKey ? 'selected' : '' ?>>
                                <?= View::escape($mLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="refund-reason" class="block font-bold text-on-surface uppercase mb-1">Lý do hoàn tiền *</label>
                    <textarea id="refund-reason" name="reason" rows="3" required placeholder="Nhập lý do hoàn tiền (VD: Khách đổi món, lỗi tính trùng, dịch vụ không đạt chuẩn...)" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-outline-variant/30">
                    <button type="button" onclick="document.getElementById('refundModal').classList.add('hidden')" class="px-4 py-2 border border-outline-variant/40 rounded-xl font-semibold hover:bg-stone-50">
                        Hủy bỏ
                    </button>
                    <button type="submit" onclick="return confirm('Xác nhận hoàn tiền số tiền này cho hóa đơn?')" class="px-5 py-2 bg-purple-700 text-white rounded-xl font-bold hover:bg-purple-800 transition">
                        Xác nhận hoàn tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Sửa Sai Hóa Đơn -->
<?php if ($canEdit): ?>
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-outline-variant/30">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[24px]">edit_note</span>
                    <h3 class="text-base font-bold font-headline text-on-surface">Chỉnh Sửa Thông Tin Hóa Đơn</h3>
                </div>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="p-1 rounded-lg text-stone-400 hover:text-stone-700">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form action="/admin/invoices/<?= (int) $invoice['id'] ?>/edit" method="post" class="space-y-4 text-xs">
                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                <p class="text-stone-500">Mọi thao tác sửa đổi sẽ được ghi nhận vào nhật ký kiểm toán hệ thống cùng với tài khoản của bạn.</p>

                <div>
                    <label for="edit-customer-name" class="block font-bold text-on-surface uppercase mb-1">Tên khách hàng</label>
                    <input id="edit-customer-name" type="text" name="customer_name" value="<?= View::escape($invoice['customer_name'] ?: '') ?>" required class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label for="edit-payment-method" class="block font-bold text-on-surface uppercase mb-1">Phương thức thanh toán</label>
                    <select id="edit-payment-method" name="payment_method" class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary">
                        <?php foreach ($paymentMethods as $mKey => $mLabel): ?>
                            <option value="<?= View::escape($mKey) ?>" <?= $invoice['payment_method'] === $mKey ? 'selected' : '' ?>>
                                <?= View::escape($mLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="edit-reason" class="block font-bold text-on-surface uppercase mb-1">Lý do điều chỉnh *</label>
                    <textarea id="edit-reason" name="reason" rows="3" required placeholder="Bắt buộc nhập lý do điều chỉnh hóa đơn đã thanh toán..." class="w-full text-xs py-2 px-3 rounded-xl border border-outline-variant/40 focus:ring-1 focus:ring-primary"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-outline-variant/30">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 border border-outline-variant/40 rounded-xl font-semibold hover:bg-stone-50">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary-container transition">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
