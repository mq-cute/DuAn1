<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use PDOException;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::user() !== null) {
            $this->redirect('/account');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Đăng nhập',
            'currentUser' => null,
            'csrfToken' => Csrf::token(),
            'errors' => [],
            'old' => [],
        ]);
    }

    public function login(): void
    {
        Session::start();
        if (!$this->verifyCsrf()) {
            $this->renderLogin(['Biểu mẫu đã hết hạn. Vui lòng tải lại trang và thử lại.'], []);
            return;
        }

        $email = strtolower(trim($this->postString('email')));
        $password = $this->postString('password');
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Vui lòng nhập địa chỉ email hợp lệ.';
        }
        if ($password === '' || strlen($password) > 4096) {
            $errors[] = 'Vui lòng nhập mật khẩu.';
        }
        if ($errors !== []) {
            $this->renderLogin($errors, ['email' => $email]);
            return;
        }

        $user = (new User(Database::connection()))->findByEmail($email);
        if (
            $user === null
            || $user['status'] !== 'active'
            || !password_verify($password, $user['password'])
        ) {
            $this->renderLogin(['Email hoặc mật khẩu không chính xác.'], ['email' => $email]);
            return;
        }

        Auth::login($user);
        $this->redirect('/account');
    }

    public function showRegister(): void
    {
        if (Auth::user() !== null) {
            $this->redirect('/account');
        }

        $this->render('auth/register', [
            'pageTitle' => 'Tạo tài khoản',
            'currentUser' => null,
            'csrfToken' => Csrf::token(),
            'errors' => [],
            'old' => [],
        ]);
    }

    public function register(): void
    {
        Session::start();
        if (!$this->verifyCsrf()) {
            $this->renderRegister(['Biểu mẫu đã hết hạn. Vui lòng tải lại trang và thử lại.'], []);
            return;
        }

        $name = trim($this->postString('name'));
        $email = strtolower(trim($this->postString('email')));
        $password = $this->postString('password');
        $passwordConfirmation = $this->postString('password_confirmation');
        $errors = [];

        $nameLength = preg_match_all('/./us', $name, $characters);
        if ($name === '' || $nameLength === false || $nameLength > 150) {
            $errors[] = 'Họ tên là bắt buộc và không được vượt quá 150 ký tự.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Vui lòng nhập địa chỉ email hợp lệ.';
        }
        if (strlen($password) < 12 || strlen($password) > 4096) {
            $errors[] = 'Mật khẩu cần có ít nhất 12 ký tự.';
        }
        if (!hash_equals($password, $passwordConfirmation)) {
            $errors[] = 'Xác nhận mật khẩu không khớp.';
        }

        if ($errors !== []) {
            $this->renderRegister($errors, ['name' => $name, 'email' => $email]);
            return;
        }

        $users = new User(Database::connection());
        if ($users->findByEmail($email) !== null) {
            $this->renderRegister(['Email này đã được sử dụng.'], ['name' => $name, 'email' => $email]);
            return;
        }

        try {
            $userId = $users->createCustomer($name, $email, password_hash($password, PASSWORD_DEFAULT));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderRegister(['Email này đã được sử dụng.'], ['name' => $name, 'email' => $email]);
            return;
        }

        Auth::login([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'customer',
        ]);
        $this->redirect('/account');
    }

    public function logout(): void
    {
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            echo 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
            return;
        }

        Auth::logout();
        $this->redirect('/');
    }

    private function renderLogin(array $errors, array $old): void
    {
        $this->render('auth/login', [
            'pageTitle' => 'Đăng nhập',
            'currentUser' => null,
            'csrfToken' => Csrf::token(),
            'errors' => $errors,
            'old' => $old,
        ], 422);
    }

    private function renderRegister(array $errors, array $old): void
    {
        $this->render('auth/register', [
            'pageTitle' => 'Tạo tài khoản',
            'currentUser' => null,
            'csrfToken' => Csrf::token(),
            'errors' => $errors,
            'old' => $old,
        ], 422);
    }

    private function postString(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
