<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use App\Models\Table;
use DateTimeImmutable;
use DomainException;
use PDO;

final class BookingAdminController extends Controller
{
    private const STATUSES = [
        'pending',
        'confirmed',
        'seated',
        'waiting_pending',
        'completed',
        'cancelled',
        'no_show',
    ];

    public function index(): void
    {
        $user = $this->requireRole('admin', 'staff');
        $db = Database::connection();
        $model = new Booking($db);

        // Kích hoạt tự động hủy các bàn đến muộn
        $model->autoReleaseLateBookings();

        $date = $this->queryString('date');
        if ($date !== '' && !$this->isValidDate($date)) {
            $date = '';
        }
        $status = $this->queryString('status');
        if ($status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = '';
        }

        $bookings = $model->allForStaff(
            $date === '' ? null : $date,
            $status === '' ? null : $status
        );
        $preorders = [];
        foreach ($model->preordersForBookings(array_column($bookings, 'id')) as $preorder) {
            $preorders[(int) $preorder['booking_id']][] = $preorder;
        }
        foreach ($bookings as &$booking) {
            $booking['preorders'] = $preorders[(int) $booking['id']] ?? [];
        }
        unset($booking);

        // Lấy danh sách bàn khả dụng để hỗ trợ đổi bàn
        $tableModel = new Table($db);
        $allTables = $tableModel->allWithAreas();

        // Lấy các khu vực để lọc
        $areas = $tableModel->allAreas();

        $layout = ($user['role'] ?? '') === 'admin' ? 'layouts/admin' : 'layouts/staff';

        $this->render('booking/admin-index', [
            'pageTitle' => 'Quản lý Đặt bàn & Tiếp nhận Khách — FOODIES Admin',
            'currentUser' => $user,
            'bookings' => $bookings,
            'allTables' => $allTables,
            'areas' => $areas,
            'date' => $date,
            'status' => $status,
            'statuses' => self::STATUSES,
            'csrfToken' => Csrf::token(),
            'message' => $this->queryString('message'),
        ], 200, $layout);
    }

    public function updateStatus(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        $status = $this->stringInput($_POST, 'status');
        $note = trim($this->stringInput($_POST, 'note'));

        if (
            $bookingId === false
            || $bookingId < 1
            || !$this->verifyCsrf()
            || !in_array($status, self::STATUSES, true)
            || strlen($note) > 2000
        ) {
            $this->redirect('/admin/bookings?message=invalid');
        }

        try {
            $db = Database::connection();
            $bookingStmt = $db->prepare('SELECT * FROM bookings WHERE id = :id');
            $bookingStmt->execute(['id' => $bookingId]);
            $bookingData = $bookingStmt->fetch(PDO::FETCH_ASSOC);

            (new Booking($db))->transitionStatus(
                $bookingId,
                $status,
                $user['id'],
                null,
                $note === '' ? null : $note
            );

            // Gửi thông báo tương ứng cho khách hàng & hệ thống
            if ($bookingData) {
                try {
                    $notifModel = new \App\Models\Notification($db);
                    $userId = isset($bookingData['user_id']) && $bookingData['user_id'] !== null ? (int)$bookingData['user_id'] : null;

                    if ($status === 'confirmed') {
                        if ($userId !== null) {
                            $notifModel->create(
                                $userId,
                                'booking_confirmed',
                                'Đặt Bàn Đã Được Xác Nhận',
                                "Đơn đặt bàn ngày {$bookingData['booking_date']} lúc {$bookingData['booking_time']} cho {$bookingData['number_of_guests']} khách đã được FOODIES xác nhận thành công.",
                                ['booking_id' => $bookingId, 'status' => 'confirmed']
                            );
                        }
                    } elseif ($status === 'cancelled') {
                        if ($userId !== null) {
                            $notifModel->create(
                                $userId,
                                'booking_cancelled',
                                'Đơn Đặt Bàn Đã Hủy',
                                "Đơn đặt bàn ngày {$bookingData['booking_date']} lúc {$bookingData['booking_time']} đã bị hủy" . ($note !== '' ? ": $note" : "."),
                                ['booking_id' => $bookingId, 'status' => 'cancelled', 'note' => $note]
                            );
                        }
                    }
                } catch (\Throwable) {
                }
            }
        } catch (DomainException $exception) {
            $message = match ($exception->getMessage()) {
                'booking_not_found' => 'missing',
                'table_busy' => 'table-busy',
                'booking_not_today' => 'not-today',
                default => 'invalid-transition',
            };
            $this->redirect('/admin/bookings?message=' . $message);
        }

        $this->redirect('/admin/bookings?message=' . ($status === 'confirmed' ? 'confirmed' : 'updated'));
    }

    public function receiveDeposit(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        if ($bookingId === false || $bookingId < 1 || !$this->verifyCsrf()) {
            $this->redirect('/admin/bookings?message=invalid');
        }

        try {
            (new Booking(Database::connection()))->markDepositReceived($bookingId, (int) $user['id']);
            $this->redirect('/admin/bookings?message=deposit-received');
        } catch (DomainException) {
            $this->redirect('/admin/bookings?message=deposit-invalid');
        }
    }

    public function recordLateContact(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        $note = trim($this->stringInput($_POST, 'late_contact_note'));
        if (
            $bookingId === false
            || $bookingId < 1
            || !$this->verifyCsrf()
            || $note === ''
            || mb_strlen($note) > 2000
        ) {
            $this->redirect('/admin/bookings?message=invalid');
        }

        try {
            (new Booking(Database::connection()))->recordLateContact(
                (int)$bookingId,
                (int)$user['id'],
                $note
            );
            $this->redirect('/admin/bookings?message=late-contact-recorded');
        } catch (DomainException) {
            $this->redirect('/admin/bookings?message=invalid');
        }
    }

    public function reviewForPos(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        if ($bookingId === false || $bookingId < 1) {
            $this->redirect('/admin/bookings?message=missing');
        }

        $model = new Booking(Database::connection());
        $booking = $model->findForStaff($bookingId);
        if ($booking === null || empty($booking['table_id'])) {
            $this->redirect('/admin/bookings?message=table-not-assigned');
        }
        if (!in_array($booking['status'], ['pending', 'confirmed'], true)) {
            $this->redirect('/admin/bookings?message=invalid-transition');
        }

        $details = $model->details($bookingId);
        $foodSubtotal = 0.0;
        foreach ($details as $item) {
            $foodSubtotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }
        $tableFee = (float) ($booking['table_fee'] ?? 0);
        $setupFee = (float) ($booking['combo_price'] ?? 0);
        $subtotal = $foodSubtotal + $tableFee + $setupFee;
        $serviceCharge = round($subtotal * 0.05);
        $tax = round(($subtotal + $serviceCharge) * 0.08);

        $layout = ($user['role'] ?? '') === 'admin' ? 'layouts/admin' : 'layouts/staff';
        $this->render('booking/confirm-pos', [
            'pageTitle' => 'Xác nhận đặt bàn vào POS',
            'currentUser' => $user,
            'booking' => $booking,
            'bookedTables' => $model->tablesForBooking($bookingId),
            'details' => $details,
            'foodSubtotal' => $foodSubtotal,
            'tableFee' => $tableFee,
            'setupFee' => $setupFee,
            'subtotal' => $subtotal,
            'serviceCharge' => $serviceCharge,
            'tax' => $tax,
            'estimatedTotal' => $subtotal + $serviceCharge + $tax,
            'csrfToken' => Csrf::token(),
        ], 200, $layout);
    }

    public function confirmForPos(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        if ($bookingId === false || $bookingId < 1 || !$this->verifyCsrf()) {
            $this->redirect('/admin/bookings?message=invalid');
        }

        try {
            $result = (new Booking(Database::connection()))->checkinAndOpenTable(
                $bookingId,
                (int) $user['id']
            );
            $this->redirect(
                '/staff/pos?table_id=' . $result['table_id']
                . '&order_id=' . $result['order_id'] . '&message=booking_seated'
            );
        } catch (DomainException $exception) {
            $message = match ($exception->getMessage()) {
                'booking_not_found' => 'missing',
                'table_not_assigned' => 'table-not-assigned',
                default => 'cannot-open',
            };
            $this->redirect('/admin/bookings?message=' . $message);
        }
    }

    // MỞ BÀN TỪ ĐƠN ĐẶT TRƯỚC (NHẬN KHÁCH & TẠO ORDER ĐẦY ĐỦ TIỀN BÀN + SETUP + MÓN)
    public function openTable(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);

        if ($bookingId === false || $bookingId < 1 || !$this->verifyCsrf()) {
            $this->redirect('/admin/bookings?message=invalid');
        }

        try {
            $bookingModel = new Booking(Database::connection());
            $result = $bookingModel->checkinAndOpenTable($bookingId, (int)$user['id']);

            try {
                $notifModel = new \App\Models\Notification(Database::connection());
                $notifModel->create(
                    null,
                    'table_opened',
                    'Đã Mở Bàn Cho Đơn #' . $bookingId,
                    "Bàn đã được tiếp nhận và chuyển sang trạng thái đang phục vụ.",
                    ['booking_id' => $bookingId]
                );
            } catch (\Throwable) {
            }

            $this->redirect('/admin/bookings?message=table-opened');
        } catch (DomainException $e) {
            $msg = match ($e->getMessage()) {
                'table_not_assigned' => 'table-not-assigned',
                'booking_not_found' => 'missing',
                default => 'cannot-open',
            };
            $this->redirect('/admin/bookings?message=' . $msg);
        }
    }

    // ĐỔI BÀN CHO KHÁCH (KHI BÀN GẶP SỰ CỐ HOẶC KHÁCH ĐỔI Ý)
    public function changeTable(string $id): void
    {
        $user = $this->requireRole('admin', 'staff');
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        $newTableIds = [];
        if (isset($_POST['new_table_ids']) && is_array($_POST['new_table_ids'])) {
            foreach ($_POST['new_table_ids'] as $value) {
                $validated = filter_var($value, FILTER_VALIDATE_INT);
                if ($validated !== false && $validated > 0) {
                    $newTableIds[] = (int)$validated;
                }
            }
        } elseif (isset($_POST['new_table_id'])) {
            $validated = filter_var($_POST['new_table_id'], FILTER_VALIDATE_INT);
            if ($validated !== false && $validated > 0) {
                $newTableIds[] = (int)$validated;
            }
        }
        $reason = trim($this->stringInput($_POST, 'reason'));

        if ($bookingId === false || $newTableIds === [] || $reason === '' || !$this->verifyCsrf()) {
            $this->redirect('/admin/bookings?message=invalid');
        }

        try {
            $bookingModel = new Booking(Database::connection());
            $bookingModel->replaceBookingTables(
                $bookingId,
                $newTableIds,
                (int)$user['id'],
                $reason,
                trim($this->stringInput($_POST, 'note'))
            );
            $this->redirect('/admin/bookings?message=table-changed');
        } catch (DomainException $e) {
            $msg = match ($e->getMessage()) {
                'table_busy' => 'table-busy',
                'booking_not_found' => 'missing',
                'insufficient_capacity' => 'insufficient-capacity',
                default => 'cannot-change',
            };
            $this->redirect('/admin/bookings?message=' . $msg);
        }
    }

    private function isValidDate(string $date): bool
    {
        $value = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        return $value !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $value->format('Y-m-d') === $date;
    }

    private function stringInput(array $input, string $key): string
    {
        $value = $input[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function queryString(string $key): string
    {
        return $this->stringInput($_GET, $key);
    }
}
