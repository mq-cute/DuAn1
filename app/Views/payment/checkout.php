<?php
use App\Core\View;

$order = $order ?? [];
$items = $order['items'] ?? [];
$totalAmount = (float)($totalAmount ?? 0);
$vietQr = $vietQr ?? [];
$paymentError = (string)($_GET['error'] ?? '');
$isCustomer = (bool)($isCustomer ?? false);
?>
<?php if ($isCustomer): ?>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<?php endif; ?>

<div class="max-w-5xl mx-auto space-y-8 py-6">
    <?php if ($paymentError === 'merchant_not_configured'): ?>
        <div role="alert" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            VNPAY Sandbox chưa được cấu hình. Hãy nhập mã merchant và khóa bí mật Sandbox do VNPAY cấp vào file .env.
        </div>
    <?php elseif ($paymentError === 'csrf_expired'): ?>
        <div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            Phiên thanh toán đã hết hạn. Vui lòng thử lại.
        </div>
    <?php endif; ?>
    <!-- Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-500/10 text-amber-800 border border-amber-300">
            <span class="material-symbols-outlined text-[16px]">lock</span>
            <span>CỔNG THANH TOÁN ĐIỆN TỬ BẢO MẬT SSL 256-BIT</span>
        </div>
        <h1 class="text-3xl lg:text-4xl font-headline font-bold text-slate-900">
            Thanh Toán Hóa Đơn Ẩm Thực
        </h1>
        <p class="text-sm text-slate-500 max-w-xl mx-auto">
            Quý khách có thể lựa chọn thanh toán tức thì qua Cổng trực tuyến VNPAY hoặc Quét mã VietQR chuyển khoản 24/7.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Cột Trái: Tóm tắt Đơn Hàng (5 spans) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">CHI TIẾT ĐƠN HÀNG</span>
                    <h3 class="text-xl font-headline font-bold text-slate-900">
                        Đơn #<?= (int)($order['id'] ?? 0) ?>
                    </h3>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Bàn phục vụ:</span>
                    <strong class="text-gold-dark font-bold text-base"><?= View::escape($order['table_code'] ?? 'VIP-01') ?></strong>
                </div>
            </div>

            <!-- Metadata info -->
            <div class="space-y-2 text-xs text-slate-600 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex justify-between">
                    <span class="text-slate-400">Khách hàng:</span>
                    <strong class="text-slate-900"><?= View::escape($order['customer_name'] ?? 'Quý khách') ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Thời gian gọi món:</span>
                    <span><?= date('H:i, d/m/Y', strtotime((string)($order['created_at'] ?? 'now'))) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Trạng thái bếp:</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 font-bold uppercase text-[10px]">
                        <?= View::escape($order['status'] ?? 'served') ?>
                    </span>
                </div>
            </div>

            <!-- List of items -->
            <div class="space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">THỰC ĐƠN ĐÃ GỌI</span>
                <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-1 text-xs">
                    <?php foreach ($items as $item): ?>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <div>
                                <strong class="text-slate-800 block"><?= View::escape($item['dish_name']) ?></strong>
                                <span class="text-slate-400 text-[11px]"><?= (int)$item['quantity'] ?> x <?= View::currency((string)(int)$item['unit_price']) ?></span>
                            </div>
                            <span class="font-bold text-slate-900"><?= View::currency((string)(int)$item['subtotal']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Financial Total -->
            <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Phí dịch vụ (5%) & VAT (8%):</span>
                    <span>Đã bao gồm</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
                    <span class="text-sm font-bold text-slate-900 uppercase">TỔNG THANH TOÁN:</span>
                    <strong class="text-2xl font-headline font-bold text-gold-dark"><?= View::currency((string)(int)$totalAmount) ?></strong>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Lựa chọn Cổng Thanh Toán (7 spans) -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-sm space-y-6">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">PHƯƠNG THỨC THANH TOÁN</span>
                <h3 class="text-xl font-headline font-bold text-slate-900">
                    Chọn Cổng Thanh Toán Trực Tuyến
                </h3>
            </div>

            <!-- Payment Tabs Navigation -->
            <div class="flex rounded-2xl bg-slate-100 p-1.5 gap-1.5 text-xs font-bold">
                <button type="button" onclick="switchTab('vnpay')" id="tabBtnVnpay" class="flex-1 py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 bg-white text-slate-900 shadow-sm">
                    <span class="material-symbols-outlined text-[18px] text-blue-600">credit_card</span>
                    <span>Cổng VNPAY (ATM/Visa/QR)</span>
                </button>
                <button type="button" onclick="switchTab('vietqr')" id="tabBtnVietqr" class="flex-1 py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">qr_code_2</span>
                    <span>Chuyển Khoản VietQR 24/7</span>
                </button>
            </div>

            <!-- Tab 1: VNPAY Gateway Content -->
            <div id="tabContentVnpay" class="space-y-6">
                <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/80 text-xs text-blue-900 space-y-2">
                    <div class="font-bold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-blue-600">verified_user</span>
                        <span>Cổng Thanh Toán Quốc Gia VNPAY</span>
                    </div>
                    <p class="text-slate-600 text-[11px] leading-relaxed">
                        Hỗ trợ thanh toán bảo mật tức thì qua ứng dụng Mobile Banking của 40+ ngân hàng (Vietcombank, BIDV, Techcombank, MB, ACB...), quét VNPAY-QR hoặc thẻ quốc tế Visa, MasterCard, JCB.
                    </p>
                </div>

                <!-- Logo banks strip -->
                <div class="flex items-center justify-center gap-4 py-2 border-y border-slate-100 flex-wrap text-slate-400 text-xs">
                    <span class="font-bold text-slate-700">VNPAY-QR</span> &bull;
                    <span class="font-bold text-slate-700">Thẻ ATM Nội Địa</span> &bull;
                    <span class="font-bold text-slate-700">Visa / Mastercard</span> &bull;
                    <span class="font-bold text-slate-700">Apple Pay / Google Pay</span>
                </div>

                <!-- Action Forms -->
                <form method="POST" action="/payment/process/<?= (int)($order['id'] ?? 0) ?>" class="space-y-3">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">

                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-blue-700 to-blue-900 text-white font-bold text-sm hover:from-blue-800 hover:to-blue-950 shadow-lg shadow-blue-900/10 transition flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <span>Chuyển đến Cổng VNPAY (Thanh toán <?= View::currency((string)(int)$totalAmount) ?>)</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>

                </form>
            </div>

            <!-- Tab 2: VietQR Dynamic Code Content -->
            <div id="tabContentVietqr" class="space-y-6 hidden">
                <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 text-xs text-emerald-900 space-y-1">
                    <strong class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">qr_code_scanner</span>
                        <span>Mã VietQR Tự Động Điền Số Tiền & Nội Dung</span>
                    </strong>
                    <p class="text-slate-600 text-[11px]">
                        Mở bất kỳ ứng dụng ngân hàng nào trên điện thoại &rarr; Chọn Quét QR &rarr; Kiểm tra thông tin và bấm Xác nhận chuyển khoản.
                    </p>
                </div>

                <!-- QR Display & Bank Info -->
                <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-48 h-48 bg-white p-2 rounded-2xl shadow-sm border border-slate-200 flex-shrink-0 flex items-center justify-center">
                        <img src="<?= View::escape($vietQr['qr_url'] ?? '') ?>" alt="VietQR Code" class="w-full h-full object-contain rounded-xl">
                    </div>

                    <div class="space-y-3 text-xs w-full">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">NGÂN HÀNG THỤ HƯỞNG</span>
                            <strong class="text-slate-900 text-sm">MBBank (Ngân hàng TMCP Quân Đội)</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">SỐ TÀI KHOẢN</span>
                            <div class="flex items-center gap-2">
                                <strong class="text-gold-dark text-base tracking-wider font-mono font-bold"><?= View::escape($vietQr['account_no'] ?? '0314892341') ?></strong>
                                <button type="button" onclick="navigator.clipboard.writeText('<?= View::escape($vietQr['account_no'] ?? '0314892341') ?>'); alert('Đã sao chép số tài khoản!');" class="p-1 rounded hover:bg-slate-200 text-slate-500" title="Sao chép">
                                    <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">CHỦ TÀI KHOẢN</span>
                            <strong class="text-slate-800 uppercase"><?= View::escape($vietQr['account_name'] ?? 'LEPICURE GASTRONOMIE') ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">NỘI DUNG CHUYỂN KHOẢN DUY NHẤT</span>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-900 font-bold font-mono rounded-lg text-xs"><?= View::escape($vietQr['syntax'] ?? '') ?></span>
                                <button type="button" onclick="navigator.clipboard.writeText('<?= View::escape($vietQr['syntax'] ?? '') ?>'); alert('Đã sao chép cú pháp chuyển khoản!');" class="p-1 rounded hover:bg-slate-200 text-slate-500" title="Sao chép cú pháp">
                                    <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Form -->
                <form method="POST" action="/payment/vietqr/confirm/<?= (int)($order['id'] ?? 0) ?>">
                    <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold text-sm hover:from-emerald-700 hover:to-emerald-800 shadow-lg shadow-emerald-900/10 transition flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">done_all</span>
                        <span>Tôi Đã Chuyển Khoản Thành Công</span>
                    </button>
                </form>
            </div>

            <!-- Footer Return Link -->
            <div class="text-center pt-2">
                <a href="/account/orders" class="text-xs text-slate-500 hover:text-slate-800 font-semibold inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Quay lại danh sách đơn hàng</span>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
function switchTab(tab) {
    const vnpayContent = document.getElementById('tabContentVnpay');
    const vietqrContent = document.getElementById('tabContentVietqr');
    const tabBtnVnpay = document.getElementById('tabBtnVnpay');
    const tabBtnVietqr = document.getElementById('tabBtnVietqr');

    if (tab === 'vnpay') {
        vnpayContent.classList.remove('hidden');
        vietqrContent.classList.add('hidden');
        tabBtnVnpay.className = 'flex-1 py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 bg-white text-slate-900 shadow-sm';
        tabBtnVietqr.className = 'flex-1 py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900';
    } else {
        vnpayContent.classList.add('hidden');
        vietqrContent.classList.remove('hidden');
        tabBtnVietqr.className = 'flex-1 py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 bg-white text-slate-900 shadow-sm';
        tabBtnVnpay.className = 'flex-1 py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900';
    }
}
</script>
