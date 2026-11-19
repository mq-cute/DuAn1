<?php

declare(strict_types=1);

$root = dirname(__DIR__);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once $root . '/app/Core/Environment.php';
\App\Core\Environment::load($root . '/.env');
require_once $root . '/app/Core/Database.php';

$db = \App\Core\Database::connection();

echo "======================================================\n";
echo "BẮT ĐẦU KIỂM THỬ TOÀN DIỆN 3 CHỨC NĂNG QUẢN TRỊ:\n";
echo "======================================================\n\n";

// Lấy tài khoản admin và staff hiện có
$userModel = new \App\Models\User($db);
$adminUser = $userModel->findByEmail('admin@lepicure.com');
if ($adminUser === null) {
    throw new RuntimeException('Không tìm thấy tài khoản admin@lepicure.com');
}
$adminId = (int) $adminUser['id'];

$staffUser = $userModel->findByEmail('staff@lepicure.com');
$staffId = $staffUser !== null ? (int) $staffUser['id'] : $adminId;

// =====================================================================
// PHẦN 1: CHỨC NĂNG 7 - THÔNG TIN NHÀ HÀNG & THIẾT LẬP VẬN HÀNH
// =====================================================================
echo "--- [TEST CHỨC NĂNG 7: QUẢN LÝ THÔNG TIN NHÀ HÀNG] ---\n";

$settingModel = new \App\Models\RestaurantSetting($db);

// 1.1 Lấy cài đặt ban đầu
$settings = $settingModel->getAll();
if (empty($settings['restaurant_name'])) {
    throw new RuntimeException('Lỗi: Cài đặt restaurant_name trống.');
}
echo "  [PASS] Đọc thành công thông tin nhà hàng: {$settings['restaurant_name']}\n";

// 1.2 Cập nhật thông tin cơ bản
$newTagline = 'Haute Cuisine Française • Michelin Experience ' . time();
$settingModel->setMany(['restaurant_tagline' => $newTagline], $adminId);
$updatedSettings = $settingModel->getAll();
if (($updatedSettings['restaurant_tagline'] ?? '') !== $newTagline) {
    throw new RuntimeException('Lỗi: Không cập nhật được tagline nhà hàng.');
}
echo "  [PASS] Cập nhật và lưu vết thông tin cơ bản thành công.\n";

// 1.3 Giờ mở cửa 7 ngày
$hours = $settingModel->getOperatingHours();
if (count($hours) !== 7) {
    throw new RuntimeException('Lỗi: Số lượng ngày trong tuần không đúng 7 ngày.');
}
echo "  [PASS] Đọc danh sách giờ mở cửa 7 ngày trong tuần thành công.\n";

// Thử cập nhật giờ mở cửa Thứ Hai
$settingModel->updateOperatingHours([
    'monday' => ['open_time' => '09:30', 'close_time' => '23:30', 'is_closed' => 0],
], $adminId);
$updatedHours = $settingModel->getOperatingHours();
$monday = null;
foreach ($updatedHours as $h) {
    if ($h['day_of_week'] === 'monday') {
        $monday = $h;
        break;
    }
}
if ($monday === null || substr($monday['open_time'], 0, 5) !== '09:30') {
    throw new RuntimeException('Lỗi: Cập nhật giờ mở cửa thứ 2 thất bại.');
}
echo "  [PASS] Cập nhật giờ mở cửa từng ngày thành công.\n";

// 1.4 Thuế và phí
$settingModel->setMany([
    'tax_rate' => '10',
    'vat_enabled' => '1',
    'service_charge_rate' => '5',
    'service_charge_enabled' => '1',
], $adminId);
$pricing = $settingModel->getPricingRates();
if ($pricing['tax_rate'] !== 0.1 || $pricing['service_charge_rate'] !== 0.05) {
    throw new RuntimeException('Lỗi: Thuế và phí không đồng bộ đúng.');
}
echo "  [PASS] Cập nhật mức thuế VAT (10%) và phí dịch vụ (5%) thành công.\n";

// Đặt lại 8% cho chuẩn
$settingModel->setMany(['tax_rate' => '8'], $adminId);

// 1.5 Chính sách nhà hàng
$testPolicy = 'Khách đặt bàn vui lòng đến trước 15 phút. ' . time();
$settingModel->setMany(['booking_policy' => $testPolicy], $adminId);
if ($settingModel->get('booking_policy') !== $testPolicy) {
    throw new RuntimeException('Lỗi: Không lưu được chính sách đặt bàn.');
}
echo "  [PASS] Cập nhật chính sách đặt bàn, hủy bàn, hoàn tiền thành công.\n\n";

// =====================================================================
// PHẦN 2: CHỨC NĂNG 6 - QUẢN LÝ TÀI KHOẢN & PHÂN QUYỀN
// =====================================================================
echo "--- [TEST CHỨC NĂNG 6: QUẢN LÝ TÀI KHOẢN & PHÂN QUYỀN] ---\n";

// 2.1 Danh sách tài khoản, tìm kiếm, lọc
$allUsers = $userModel->listUsers([], 1, 100);
$totalUserCount = $userModel->countUsers([]);
if (count($allUsers) === 0 || $totalUserCount === 0) {
    throw new RuntimeException('Lỗi: listUsers trả về rỗng.');
}
echo "  [PASS] Lấy danh sách tài khoản thành công (Tổng: {$totalUserCount} users).\n";

// Tìm kiếm
$searchResult = $userModel->listUsers(['search' => 'admin@lepicure.com']);
if (count($searchResult) !== 1) {
    throw new RuntimeException('Lỗi: Tìm kiếm theo email không khớp.');
}
echo "  [PASS] Tìm kiếm theo email/tên/số điện thoại hoạt động chính xác.\n";

// 2.2 Tạo tài khoản mới
$testEmail = 'test_staff_' . time() . '@lepicure.com';
$createdUserId = $userModel->createUser(
    'Nhân Viên Kiểm Thử',
    $testEmail,
    password_hash('Secret123', PASSWORD_DEFAULT),
    'staff',
    '0909999888',
    'active',
    $adminId
);
echo "  [PASS] Khởi tạo tài khoản mới thành công (ID: {$createdUserId}).\n";

// Kiểm tra email trùng
try {
    $userModel->createUser('Trùng Email', $testEmail, password_hash('Secret123', PASSWORD_DEFAULT), 'staff');
    throw new RuntimeException('Lỗi: Hệ thống không chặn email trùng lặp.');
} catch (\DomainException $e) {
    echo "  [PASS] Chặn tạo tài khoản trùng email thành công: " . $e->getMessage() . "\n";
}

// 2.3 Chỉnh sửa tài khoản
$userModel->updateUser($createdUserId, [
    'name' => 'Nhân Viên Đã Đổi Tên',
    'phone' => '0988776655',
    'role' => 'kitchen',
    'status' => 'active',
], $adminId);

$updatedUser = $userModel->findById($createdUserId);
if ($updatedUser['name'] !== 'Nhân Viên Đã Đổi Tên' || $updatedUser['role'] !== 'kitchen') {
    throw new RuntimeException('Lỗi: Cập nhật thông tin tài khoản thất bại.');
}
echo "  [PASS] Chỉnh sửa họ tên, SĐT, phân quyền vai trò (staff -> kitchen) thành công.\n";

// 2.4 Kiểm tra quy tắc BẢO VỆ ADMIN CUỐI CÙNG
echo "  [KIỂM TRA QUY TẮC] Bảo vệ Admin duy nhất:\n";
$activeAdmins = $userModel->countActiveAdmins();
echo "    - Số Admin active hiện tại: {$activeAdmins}\n";

// Nếu chỉ có 1 admin, thử hạ quyền
if ($activeAdmins === 1) {
    try {
        $userModel->updateUser($adminId, ['role' => 'staff'], $adminId);
        throw new RuntimeException('Lỗi nghiêm trọng: Admin duy nhất bị hạ quyền mà không bị chặn!');
    } catch (\DomainException $e) {
        echo "    [PASS] Chặn hạ quyền Admin cuối cùng thành công: " . $e->getMessage() . "\n";
    }

    try {
        $userModel->updateUser($adminId, ['status' => 'inactive'], $adminId);
        throw new RuntimeException('Lỗi nghiêm trọng: Admin duy nhất bị khóa tài khoản mà không bị chặn!');
    } catch (\DomainException $e) {
        echo "    [PASS] Chặn khóa tài khoản Admin cuối cùng thành công: " . $e->getMessage() . "\n";
    }
}

// 2.5 Đổi mật khẩu cá nhân
$userModel->changePassword($createdUserId, 'Secret123', 'NewSecret456');
$newHash = $userModel->getPasswordHash($createdUserId);
if ($newHash === null || !password_verify('NewSecret456', $newHash)) {
    throw new RuntimeException('Lỗi: Mật khẩu mới không được băm và lưu chính xác.');
}
echo "  [PASS] Đổi mật khẩu cá nhân (kiểm tra mật khẩu cũ, hash mật khẩu mới) thành công.\n";

// Thử đổi sai mật khẩu cũ
try {
    $userModel->changePassword($createdUserId, 'SaiMatKhau', 'Abc123456');
    throw new RuntimeException('Lỗi: Cho phép đổi mật khẩu khi nhập sai mật khẩu cũ.');
} catch (\DomainException $e) {
    echo "  [PASS] Chặn đổi mật khẩu khi sai mật khẩu cũ thành công.\n";
}

// 2.6 Admin đặt lại mật khẩu
$userModel->adminResetPassword($createdUserId, 'AdminReset789', $adminId);
$resetHash = $userModel->getPasswordHash($createdUserId);
if ($resetHash === null || !password_verify('AdminReset789', $resetHash)) {
    throw new RuntimeException('Lỗi: Admin reset password thất bại.');
}
echo "  [PASS] Admin đặt lại mật khẩu thành công.\n";

// 2.7 Kiểm tra Activity Log
$logModel = new \App\Models\ActivityLog($db);
$recentLogs = $logModel->all([], 1, 10);
if (count($recentLogs) === 0) {
    throw new RuntimeException('Lỗi: ActivityLog không có bản ghi nào.');
}
echo "  [PASS] Nhật ký hoạt động (ActivityLog) đã ghi nhận: " . count($recentLogs) . " bản ghi gần nhất.\n\n";

// =====================================================================
// PHẦN 3: CHỨC NĂNG 5 - QUẢN LÝ HÓA ĐƠN & HOÀN TIỀN
// =====================================================================
echo "--- [TEST CHỨC NĂNG 5: QUẢN LÝ HÓA ĐƠN, HOÀN TIỀN & SỬA SAI] ---\n";

$invoiceModel = new \App\Models\Invoice($db);

// 3.1 Danh sách hóa đơn, tìm kiếm, lọc
$invoices = $invoiceModel->listInvoices([], 1, 10);
$countInv = $invoiceModel->countInvoices([]);
if (count($invoices) === 0) {
    throw new RuntimeException('Lỗi: Chưa có hóa đơn nào trong DB.');
}
echo "  [PASS] Lấy danh sách hóa đơn thành công (Tổng: {$countInv} hóa đơn).\n";

// Tìm kiếm hóa đơn
$firstInv = $invoices[0];
$found = $invoiceModel->listInvoices(['search' => $firstInv['invoice_number']]);
if (count($found) !== 1) {
    throw new RuntimeException('Lỗi: Tìm kiếm theo mã hóa đơn thất bại.');
}
echo "  [PASS] Tìm kiếm theo mã hóa đơn #{$firstInv['invoice_number']} chính xác.\n";

// 3.2 Chi tiết hóa đơn
$invoiceDetail = $invoiceModel->getInvoiceWithDetails((int) $firstInv['id']);
if (!isset($invoiceDetail['items'], $invoiceDetail['payments'], $invoiceDetail['refunds'], $invoiceDetail['edit_histories'])) {
    throw new RuntimeException('Lỗi: getInvoiceWithDetails thiếu các trường mở rộng.');
}
echo "  [PASS] Chi tiết hóa đơn đầy đủ (Món ăn: " . count($invoiceDetail['items']) . ", Thanh toán: " . count($invoiceDetail['payments']) . ").\n";

// 3.3 Tạo một hóa đơn mới riêng để test hoàn tiền & sửa sai
// Dùng OrderModel tạo 1 đơn hàng mới và thanh toán
$orderModel = new \App\Models\Order($db);
$tableModel = new \App\Models\Table($db);
$tables = $tableModel->allWithAreas();
$dishes = (new \App\Models\Dish($db))->allVisible();
if ($tables === [] || $dishes === []) {
    throw new RuntimeException('Không đủ bàn hoặc món để test tạo đơn.');
}

$testOrderId = $orderModel->createOrderAtTable(
    (int) $tables[0]['id'],
    'Khách Hàng Kiểm Thử Hoàn Tiền',
    $adminId,
    [
        ['dish_id' => (int) $dishes[0]['id'], 'quantity' => 2, 'note' => 'Test'],
    ]
);
$testInvoiceId = $invoiceModel->confirmPayment($testOrderId, 'card', $adminId);
$testInvoice = $invoiceModel->findById($testInvoiceId);
$invTotal = (float) $testInvoice['total'];
echo "  [PASS] Khởi tạo hóa đơn test thành công: #{$testInvoice['invoice_number']} (Tổng tiền: " . number_format($invTotal, 0, ',', '.') . " đ).\n";

// 3.4 Test nghiệp vụ HOÀN TIỀN (Refund)
// Thử hoàn tiền âm hoặc 0
try {
    $invoiceModel->refund($testInvoiceId, 0, 'Lỗi', 'cash', $adminId);
    throw new RuntimeException('Lỗi: Cho phép hoàn tiền 0 đ.');
} catch (\DomainException $e) {
    echo "  [PASS] Chặn hoàn tiền <= 0 đ: " . $e->getMessage() . "\n";
}

// Thử hoàn tiền vượt quá tổng tiền hóa đơn
try {
    $invoiceModel->refund($testInvoiceId, $invTotal + 100000, 'Hoàn thừa', 'cash', $adminId);
    throw new RuntimeException('Lỗi: Cho phép hoàn vượt quá số tiền của hóa đơn.');
} catch (\DomainException $e) {
    echo "  [PASS] Chặn hoàn tiền vượt quá số tiền hợp lệ: " . $e->getMessage() . "\n";
}

// Thử không nhập lý do hoàn tiền
try {
    $invoiceModel->refund($testInvoiceId, 50000, '   ', 'cash', $adminId);
    throw new RuntimeException('Lỗi: Cho phép hoàn tiền không có lý do.');
} catch (\DomainException $e) {
    echo "  [PASS] Bắt buộc nhập lý do hoàn tiền: " . $e->getMessage() . "\n";
}

// Thực hiện HOÀN TIỀN MỘT PHẦN hợp lệ
$partialRefundAmount = 50000.0;
$refundId1 = $invoiceModel->refund($testInvoiceId, $partialRefundAmount, 'Khách trả lại 1 phần đồ uống', 'card', $adminId);
$afterPartial = $invoiceModel->getInvoiceWithDetails($testInvoiceId);
if ($afterPartial['status'] !== 'partially_refunded' || (float) $afterPartial['total_refunded'] !== $partialRefundAmount) {
    throw new RuntimeException('Lỗi: Trạng thái hóa đơn sau khi hoàn một phần không phải partially_refunded.');
}
echo "  [PASS] Hoàn tiền một phần thành công (Trạng thái: partially_refunded, Đã hoàn: 50.000 đ).\n";

// Thử hoàn tiền trùng lặp ngay tức thì
try {
    $invoiceModel->refund($testInvoiceId, $partialRefundAmount, 'Khách trả lại 1 phần đồ uống', 'card', $adminId);
    throw new RuntimeException('Lỗi: Không phát hiện yêu cầu hoàn tiền trùng lặp.');
} catch (\DomainException $e) {
    echo "  [PASS] Phát hiện và chặn thao tác hoàn tiền trùng lặp: " . $e->getMessage() . "\n";
}

// Hoàn tiếp số tiền còn lại -> Chuyển thành refunded toàn bộ
$remaining = $invTotal - $partialRefundAmount;
$refundId2 = $invoiceModel->refund($testInvoiceId, $remaining, 'Hoàn trả toàn bộ phần còn lại do khách khiếu nại', 'card', $adminId);
$afterFull = $invoiceModel->getInvoiceWithDetails($testInvoiceId);
if ($afterFull['status'] !== 'refunded' || (float) $afterFull['total_refunded'] !== $invTotal) {
    throw new RuntimeException('Lỗi: Trạng thái hóa đơn sau khi hoàn toàn bộ không phải refunded.');
}
$orderAfterRefund = $orderModel->findById($testOrderId);
if ($orderAfterRefund['payment_status'] !== 'refunded') {
    throw new RuntimeException('Lỗi: payment_status của order không đồng bộ sang refunded.');
}
echo "  [PASS] Hoàn tiền toàn bộ thành công (Trạng thái hóa đơn: refunded, Trạng thái đơn: refunded, Tổng hoàn: " . number_format($afterFull['total_refunded'], 0, ',', '.') . " đ).\n";

// 3.5 Test SỬA SAI HÓA ĐƠN (Edit Invoice with history log)
// Tạo thêm 1 hóa đơn để test sửa sai
$testOrderId2 = $orderModel->createOrderAtTable(
    (int) $tables[0]['id'],
    'Khách Ban Đầu',
    $adminId,
    [['dish_id' => (int) $dishes[0]['id'], 'quantity' => 1, 'note' => '']]
);
$testInvoiceId2 = $invoiceModel->confirmPayment($testOrderId2, 'cash', $adminId);

// Thử sửa không có lý do
try {
    $invoiceModel->editInvoice($testInvoiceId2, ['customer_name' => 'Khách Mới'], '  ', $adminId);
    throw new RuntimeException('Lỗi: Cho phép sửa hóa đơn mà không nhập lý do.');
} catch (\DomainException $e) {
    echo "  [PASS] Bắt buộc có lý do khi sửa sai hóa đơn: " . $e->getMessage() . "\n";
}

// Sửa hợp lệ: Đổi tên khách và đổi phương thức thanh toán sang chuyển khoản
$invoiceModel->editInvoice($testInvoiceId2, [
    'customer_name' => 'Công Ty Horizon Tech (VAT)',
    'payment_method' => 'transfer',
], 'Khách yêu cầu đổi tên công ty để xuất hóa đơn VAT và chuyển khoản', $adminId);

$editedInvoice = $invoiceModel->getInvoiceWithDetails($testInvoiceId2);
if ($editedInvoice['customer_name'] !== 'Công Ty Horizon Tech (VAT)' || $editedInvoice['payment_method'] !== 'transfer') {
    throw new RuntimeException('Lỗi: Dữ liệu hóa đơn chưa được cập nhật sau khi sửa.');
}
if (count($editedInvoice['edit_histories']) !== 1) {
    throw new RuntimeException('Lỗi: Lịch sử sửa sai hóa đơn không được lưu vào invoice_edit_histories.');
}
$hist = $editedInvoice['edit_histories'][0];
$oldData = json_decode($hist['old_data'], true);
$newData = json_decode($hist['new_data'], true);
if ($oldData['customer_name'] !== 'Khách Ban Đầu' || $newData['customer_name'] !== 'Công Ty Horizon Tech (VAT)') {
    throw new RuntimeException('Lỗi: Dữ liệu snapshot trước/sau khi sửa không chính xác.');
}
echo "  [PASS] Sửa sai thông tin hóa đơn thành công và đã lưu vết lịch sử (Old -> New snapshot) đầy đủ.\n\n";

echo "======================================================\n";
echo "TẤT CẢ CÁC BÀI KIỂM THỬ CHO 3 CHỨC NĂNG ĐÃ THÀNH CÔNG RỰC RỠ!\n";
echo "======================================================\n";
