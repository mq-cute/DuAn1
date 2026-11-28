<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use App\Models\User;
use DomainException;
use Throwable;

final class AccountController extends Controller
{
    public function profile(): void
    {
        $sessionUser = $this->requireAuthenticated();
        $db = Database::connection();
        $userModel = new User($db);

        $user = $userModel->findById((int) $sessionUser['id']);
        if ($user === null) {
            Auth::logout();
            $this->redirect('/login');
        }

        $bookingModel = new Booking($db);
        $bookings = $bookingModel->allForCustomer((int) $user['id']);

        $this->render('account/index', [
            'pageTitle' => 'Tài Khoản & Hồ Sơ Cá Nhân — FOODIES',
            'currentUser' => $user,
            'bookings' => $bookings,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/guest');
    }

    public function updateProfile(): void
    {
        $sessionUser = $this->requireAuthenticated();
        if (!$this->verifyCsrf()) {
            $this->redirect('/account?error=csrf_expired');
        }

        $name = trim($this->postString('name'));
        $phone = trim($this->postString('phone'));

        if ($name === '' || strlen($name) > 150) {
            $this->redirect('/account?error=' . urlencode('Họ tên không được để trống và tối đa 150 ký tự.'));
        }

        $db = Database::connection();
        $userModel = new User($db);

        try {
            $userModel->updateUser((int) $sessionUser['id'], [
                'name' => $name,
                'phone' => $phone !== '' ? $phone : null,
            ], (int) $sessionUser['id']);

            // Cập nhật session user
            $_SESSION['user']['name'] = $name;

            $this->redirect('/account?message=profile_updated');
        } catch (DomainException $e) {
            $this->redirect('/account?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/account?error=update_failed');
        }
    }

    public function changePassword(): void
    {
        $sessionUser = $this->requireAuthenticated();
        if (!$this->verifyCsrf()) {
            $this->redirect('/account?error=csrf_expired');
        }

        $oldPassword = $this->postString('old_password');
        $newPassword = $this->postString('new_password');
        $confirmPassword = $this->postString('confirm_password');

        if ($oldPassword === '') {
            $this->redirect('/account?error=' . urlencode('Vui lòng nhập mật khẩu hiện tại.'));
        }

        if (strlen($newPassword) < 6) {
            $this->redirect('/account?error=' . urlencode('Mật khẩu mới phải có tối thiểu 6 ký tự.'));
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirect('/account?error=' . urlencode('Xác nhận mật khẩu mới không khớp.'));
        }

        $db = Database::connection();
        $userModel = new User($db);

        try {
            $userModel->changePassword((int) $sessionUser['id'], $oldPassword, $newPassword);
            $this->redirect('/account?message=password_changed');
        } catch (DomainException $e) {
            $this->redirect('/account?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/account?error=change_password_failed');
        }
    }

    private function postString(string $key): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    }
}
