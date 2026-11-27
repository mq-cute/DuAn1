<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use App\Models\Dish;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

final class BookingController extends Controller
{
    public function createForm(): void
    {
        $user = $this->requireRole('customer');
        $date = $this->queryString('date');
        $time = $this->queryString('time');
        $guestCount = $this->integerQuery('number_of_guests');
        $searchRequested = isset($_GET['date']) || isset($_GET['time']) || isset($_GET['number_of_guests']);

        if ($date === '') {
            $date = date('Y-m-d');
        }
        if ($guestCount === null || $guestCount < 1 || $guestCount > 100) {
            $guestCount = 2;
        }

        $errors = [];
        $tables = [];
        if ($searchRequested) {
            $errors = $this->validateSlot($date, $time, $guestCount);
            if ($errors === []) {
                $tables = (new Booking(Database::connection()))->availableTables($date, $time, $guestCount);
                if ($tables === []) {
                    $errors[] = 'Không có bàn phù hợp trong khung giờ này. Vui lòng chọn giờ khác.';
                }
            }
        }

        $this->renderForm($user, [
            'booking_date' => $date,
            'booking_time' => $time,
            'number_of_guests' => $guestCount,
            'table_id' => $this->integerQuery('table_id'),
            'customer_name' => $user['name'],
            'customer_phone' => '',
            'notes' => '',
            'dish_notes' => [],
        ], $errors, $tables);
    }

    public function create(): void
    {
        $user = $this->requireRole('customer');
        if (!$this->verifyCsrf()) {
            $this->renderForm($user, $this->bookingInput($_POST, $user), [
                'Biểu mẫu đã hết hạn. Vui lòng kiểm tra lại thông tin và thử lại.',
            ], [], 422);
            return;
        }

        $input = $this->bookingInput($_POST, $user);
        $errors = $this->validateSlot(
            $input['booking_date'],
            $input['booking_time'],
            $input['number_of_guests']
        );
        $nameLength = preg_match_all('/./us', $input['customer_name']);
        if ($input['customer_name'] === '' || $nameLength === false || $nameLength > 150) {
            $errors[] = 'Tên người đặt là bắt buộc và tối đa 150 ký tự.';
        }
        if (preg_match('/\A[0-9+()\-\s]{7,20}\z/', $input['customer_phone']) !== 1) {
            $errors[] = 'Vui lòng nhập số điện thoại hợp lệ (7–20 ký tự).';
        }
        if (strlen($input['notes']) > 5000) {
            $errors[] = 'Ghi chú đặt bàn không được vượt quá 5000 ký tự.';
        }
        foreach ($input['dish_notes'] as $dishNote) {
            if (strlen($dishNote) > 2000) {
                $errors[] = 'Ghi chú cho từng món không được vượt quá 2000 ký tự.';
                break;
            }
        }
        if ($input['table_id'] < 1) {
            $errors[] = 'Vui lòng tìm và chọn một bàn còn trống.';
        }
        if ($this->unavailableCartDishes(Cart::contents()) !== []) {
            $errors[] = 'Một hoặc nhiều món trong giỏ đã hết hoặc bị ẩn. Hãy cập nhật giỏ trước khi đặt bàn.';
        }
        if ($errors !== []) {
            $this->renderForm($user, $input, $errors, $this->availableTablesFor($input), 422);
            return;
        }

        try {
            $bookingId = (new Booking(Database::connection()))->createReservation([
                'user_id' => $user['id'],
                'customer_name' => $input['customer_name'],
                'customer_phone' => $input['customer_phone'],
                'customer_email' => $user['email'],
                'booking_date' => $input['booking_date'],
                'booking_time' => $input['booking_time'],
                'number_of_guests' => $input['number_of_guests'],
                'table_id' => $input['table_id'],
                'pay_deposit' => isset($_POST['pay_deposit']),
                'notes' => $input['notes'] === '' ? null : $input['notes'],
            ], Cart::contents(), $input['dish_notes']);

            // Tạo thông báo nghiệp vụ thời gian thực
            try {
                $notifModel = new \App\Models\Notification(Database::connection());
                // 1. Broadcast thông báo cho Admin & Nhân viên
                $notifModel->create(
                    null,
                    'booking_new',
                    'Đơn Đặt Bàn Mới #' . $bookingId,
                    "Khách hàng {$input['customer_name']} vừa đặt bàn ngày {$input['booking_date']} lúc {$input['booking_time']} cho {$input['number_of_guests']} khách.",
                    [
                        'booking_id' => $bookingId,
                        'customer_name' => $input['customer_name'],
                        'booking_date' => $input['booking_date'],
                        'booking_time' => $input['booking_time'],
                        'guests' => $input['number_of_guests'],
                        'phone' => $input['customer_phone'],
                    ]
                );
                // 2. Thông báo riêng cho tài khoản khách hàng
                $notifModel->create(
                    (int) $user['id'],
                    'booking_created',
                    'Đặt Bàn Thành Công #' . $bookingId,
                    "Yêu cầu đặt bàn ngày {$input['booking_date']} lúc {$input['booking_time']} của quý khách đã được ghi nhận. Nhà hàng sẽ liên hệ xác nhận sớm nhất.",
                    [
                        'booking_id' => $bookingId,
                        'booking_date' => $input['booking_date'],
                        'booking_time' => $input['booking_time'],
                    ]
                );
            } catch (\Throwable) {
                // Tiếp tục luồng đặt bàn nếu thông báo gặp sự cố
            }
        } catch (DomainException $exception) {
            $message = match ($exception->getMessage()) {
                'table_unavailable' => 'Bàn vừa được đặt hoặc không còn phù hợp. Hãy chọn bàn khác.',
                'dish_unavailable' => 'Món trong giỏ vừa hết hoặc bị ẩn. Hãy cập nhật giỏ rồi thử lại.',
                default => 'Không thể tạo lượt đặt bàn. Vui lòng kiểm tra lại thông tin.',
            };
            $this->renderForm($user, $input, [$message], $this->availableTablesFor($input), 409);
            return;
        }

        Cart::clear();
        $this->redirect(isset($_POST['pay_deposit'])
            ? '/bookings/' . $bookingId . '/deposit'
            : '/account/bookings/' . $bookingId . '?message=created');
    }

    public function history(): void
    {
        $user = $this->requireRole('customer');
        $this->render('booking/history', [
            'pageTitle' => 'Lịch sử đặt bàn',
            'currentUser' => $user,
            'bookings' => (new Booking(Database::connection()))->allForCustomer($user['id']),
            'message' => $this->queryString('message'),
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->requireRole('customer');
        $bookingId = $this->validId($id);
        $model = new Booking(Database::connection());
        $booking = $bookingId === null ? null : $model->findForCustomer($bookingId, $user['id']);
        if ($booking === null) {
            $this->render('errors/404', [
                'pageTitle' => 'Không tìm thấy lượt đặt bàn',
                'currentUser' => $user,
            ], 404);
            return;
        }

        $this->render('booking/show', [
            'pageTitle' => 'Chi tiết đặt bàn',
            'currentUser' => $user,
            'booking' => $booking,
            'details' => $model->details($bookingId),
            'histories' => $model->histories($bookingId),
            'message' => $this->queryString('message'),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function deposit(string $id): void
    {
        $user = $this->requireRole('customer');
        $bookingId = $this->validId($id);
        $model = new Booking(Database::connection());
        $booking = $bookingId === null ? null : $model->findForCustomer($bookingId, (int) $user['id']);
        if ($booking === null || (float) $booking['deposit_amount'] <= 0) {
            $this->redirect('/account/bookings');
        }
        $qr = (new \App\Services\PaymentGatewayService())->generateVietQr($bookingId, (float) $booking['deposit_amount'], 'FDS DEP ' . $bookingId);
        $this->render('booking/deposit', ['pageTitle' => 'Đặt cọc đặt bàn', 'currentUser' => $user, 'booking' => $booking, 'vietQr' => $qr, 'csrfToken' => Csrf::token()]);
    }

    public function confirmDeposit(string $id): void
    {
        $user = $this->requireRole('customer');
        $bookingId = $this->validId($id);
        if ($bookingId === null || !$this->verifyCsrf()) {
            $this->redirect('/account/bookings?message=invalid');
        }
        $model = new Booking(Database::connection());
        $booking = $model->findForCustomer($bookingId, (int) $user['id']);
        if ($booking === null || (float) $booking['deposit_amount'] <= 0) {
            $this->redirect('/account/bookings');
        }
        $this->redirect('/account/bookings/' . $bookingId . '?message=deposit-pending');
    }

    public function cancel(string $id): void
    {
        $user = $this->requireRole('customer');
        $bookingId = $this->validId($id);
        if ($bookingId === null || !$this->verifyCsrf()) {
            $this->redirect('/account/bookings?message=invalid');
        }

        try {
            (new Booking(Database::connection()))->transitionStatus(
                $bookingId,
                'cancelled',
                $user['id'],
                $user['id'],
                'Khách hàng yêu cầu hủy đặt bàn.'
            );

            // Tạo thông báo khi khách hủy đặt bàn
            try {
                $notifModel = new \App\Models\Notification(Database::connection());
                $notifModel->create(
                    null,
                    'booking_cancelled',
                    'Khách Hàng Hủy Đặt Bàn #' . $bookingId,
                    "Khách hàng {$user['name']} đã hủy đơn đặt bàn #{$bookingId}.",
                    ['booking_id' => $bookingId]
                );
            } catch (\Throwable) {
            }
        } catch (DomainException $exception) {
            $message = $exception->getMessage() === 'invalid_transition' ? 'cannot-cancel' : 'missing';
            $this->redirect('/account/bookings/' . $bookingId . '?message=' . $message);
        }

        $this->redirect('/account/bookings/' . $bookingId . '?message=cancelled');
    }

    private function renderForm(
        array $user,
        array $input,
        array $errors,
        array $tables,
        int $status = 200
    ): void {
        $cart = Cart::contents();
        $db = Database::connection();
        $cartDishes = $cart === [] ? [] : (new Dish($db))->findManyByIds(array_keys($cart));
        $availableDishIds = array_map(static fn (array $dish): int => (int) $dish['id'], $cartDishes);
        $items = [];
        foreach ($cartDishes as $dish) {
            $dishId = (int) $dish['id'];
            $items[] = [
                'dish' => $dish,
                'quantity' => $cart[$dishId] ?? 0,
                'note' => $input['dish_notes'][$dishId] ?? '',
                'is_orderable' => (bool) $dish['is_visible']
                    && (bool) $dish['category_visible']
                    && (bool) $dish['is_available'],
            ];
        }

        $this->render('booking/new', [
            'pageTitle' => 'Đặt bàn trực tuyến',
            'currentUser' => $user,
            'input' => $input,
            'errors' => $errors,
            'tables' => $tables,
            'items' => $items,
            'hasMissingCartItems' => count($availableDishIds) !== count($cart),
            'csrfToken' => Csrf::token(),
            'today' => date('Y-m-d'),
        ], $status);
    }

    private function bookingInput(array $input, array $user): array
    {
        $date = $this->stringInput($input, 'booking_date');
        $time = $this->stringInput($input, 'booking_time');
        $guestCount = filter_var($input['number_of_guests'] ?? null, FILTER_VALIDATE_INT);
        $tableId = filter_var($input['table_id'] ?? null, FILTER_VALIDATE_INT);
        $dishNotes = $input['dish_notes'] ?? [];
        $notes = [];
        if (is_array($dishNotes)) {
            foreach (Cart::contents() as $dishId => $_quantity) {
                $value = $dishNotes[$dishId] ?? '';
                if (is_string($value)) {
                    $notes[$dishId] = trim($value);
                }
            }
        }

        return [
            'booking_date' => $date,
            'booking_time' => $time,
            'number_of_guests' => $guestCount !== false && $guestCount !== null ? $guestCount : 0,
            'table_id' => $tableId !== false && $tableId !== null ? $tableId : 0,
            'customer_name' => trim($this->stringInput($input, 'customer_name') ?: $user['name']),
            'customer_phone' => trim($this->stringInput($input, 'customer_phone')),
            'notes' => trim($this->stringInput($input, 'notes')),
            'dish_notes' => $notes,
        ];
    }

    private function validateSlot(string $date, string $time, int $guestCount): array
    {
        $errors = [];
        $timezone = new DateTimeZone(date_default_timezone_get());
        $dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (
            $dateValue === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $dateValue->format('Y-m-d') !== $date
        ) {
            $errors[] = 'Ngày đặt bàn không hợp lệ.';
        } elseif ($dateValue->format('Y-m-d') < date('Y-m-d')) {
            $errors[] = 'Không thể đặt bàn trong quá khứ.';
        }

        if (preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/', $time) !== 1) {
            $errors[] = 'Vui lòng chọn giờ đặt bàn hợp lệ.';
        }
        if ($guestCount < 1 || $guestCount > 100) {
            $errors[] = 'Số lượng khách phải từ 1 đến 100.';
        }

        if ($errors === []) {
            $bookingTime = DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i',
                $date . ' ' . $time,
                $timezone
            );
            if ($bookingTime === false || $bookingTime <= new DateTimeImmutable('now', $timezone)) {
                $errors[] = 'Không thể đặt bàn vào thời gian đã qua.';
            }
        }

        return $errors;
    }

    private function availableTablesFor(array $input): array
    {
        if ($this->validateSlot(
            $input['booking_date'],
            $input['booking_time'],
            $input['number_of_guests']
        ) !== []) {
            return [];
        }

        return (new Booking(Database::connection()))->availableTables(
            $input['booking_date'],
            $input['booking_time'],
            $input['number_of_guests']
        );
    }

    private function unavailableCartDishes(array $cart): array
    {
        if ($cart === []) {
            return [];
        }

        $dishes = (new Dish(Database::connection()))->findManyByIds(array_keys($cart));
        $orderable = [];
        foreach ($dishes as $dish) {
            if ((bool) $dish['is_visible'] && (bool) $dish['category_visible'] && (bool) $dish['is_available']) {
                $orderable[(int) $dish['id']] = true;
            }
        }

        return array_values(array_diff(array_keys($cart), array_keys($orderable)));
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

    private function integerQuery(string $key): ?int
    {
        $value = filter_var($_GET[$key] ?? null, FILTER_VALIDATE_INT);

        return $value === false ? null : $value;
    }

    private function validId(string $id): ?int
    {
        $value = filter_var($id, FILTER_VALIDATE_INT);

        return $value !== false && $value > 0 ? $value : null;
    }
}
