<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use DateTimeImmutable;
use DomainException;

final class BookingAdminController extends Controller
{
    private const STATUSES = [
        'pending',
        'confirmed',
        'seated',
        'completed',
        'cancelled',
        'no_show',
    ];

    public function index(): void
    {
        $user = $this->requireRole('admin', 'staff');
        $date = $this->queryString('date');
        if ($date !== '' && !$this->isValidDate($date)) {
            $date = '';
        }
        $status = $this->queryString('status');
        if ($status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = '';
        }

        $model = new Booking(Database::connection());
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

        $this->render('booking/admin-index', [
            'pageTitle' => 'Quản lý đặt bàn',
            'currentUser' => $user,
            'bookings' => $bookings,
            'date' => $date,
            'status' => $status,
            'statuses' => self::STATUSES,
            'csrfToken' => Csrf::token(),
            'message' => $this->queryString('message'),
        ]);
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
            (new Booking(Database::connection()))->transitionStatus(
                $bookingId,
                $status,
                $user['id'],
                null,
                $note === '' ? null : $note
            );
        } catch (DomainException $exception) {
            $message = match ($exception->getMessage()) {
                'booking_not_found' => 'missing',
                'table_busy' => 'table-busy',
                'booking_not_today' => 'not-today',
                default => 'invalid-transition',
            };
            $this->redirect('/admin/bookings?message=' . $message);
        }

        $this->redirect('/admin/bookings?message=updated');
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
