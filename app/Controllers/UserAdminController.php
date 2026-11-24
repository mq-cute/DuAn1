<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\User;
use DomainException;
use Throwable;

final class UserAdminController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();
        $userModel = new User($db);

        $search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) && is_string($_GET['role']) ? trim($_GET['role']) : '';
        $status = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 15;

        $filters = [];
        if ($search !== '') {
            $filters['search'] = $search;
        }
        if ($role !== '' && array_key_exists($role, User::ROLES)) {
            $filters['role'] = $role;
        }
        if ($status !== '' && array_key_exists($status, User::STATUSES)) {
            $filters['status'] = $status;
        }

        $totalUsers = $userModel->countUsers($filters);
        $totalPages = max(1, (int) ceil($totalUsers / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $users = $userModel->listUsers($filters, $page, $perPage);
        $roles = $userModel->allRoles();

        $this->render('admin/users/index', [
            'pageTitle' => 'Quản Lý Tài Khoản & Phân Quyền — L\'Épicure',
            'currentUser' => $currentUser,
            'users' => $users,
            'roles' => $roles,
            'roleLabels' => User::ROLES,
            'statusLabels' => User::STATUSES,
            'totalUsers' => $totalUsers,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'selectedRole' => $role,
            'selectedStatus' => $status,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/admin');
    }

    public function create(): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/users?error=csrf_expired');
        }

        $name = trim($this->postString('name'));
        $email = strtolower(trim($this->postString('email')));
        $phone = trim($this->postString('phone'));
        $role = trim($this->postString('role'));
        $status = trim($this->postString('status'));
        $password = $this->postString('password');

        if ($name === '' || strlen($name) > 150) {
            $this->redirect('/admin/users?error=' . urlencode('Họ tên không được để trống và tối đa 150 ký tự.'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/admin/users?error=' . urlencode('Địa chỉ email không hợp lệ.'));
        }

        if (strlen($password) < 6) {
            $this->redirect('/admin/users?error=' . urlencode('Mật khẩu phải có độ dài tối thiểu 6 ký tự.'));
        }

        if (!array_key_exists($role, User::ROLES)) {
            $this->redirect('/admin/users?error=' . urlencode('Vai trò được chọn không hợp lệ.'));
        }

        $db = Database::connection();
        $userModel = new User($db);

        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $userModel->createUser(
                $name,
                $email,
                $passwordHash,
                $role,
                $phone !== '' ? $phone : null,
                $status !== '' ? $status : 'active',
                (int) $currentUser['id']
            );

            $this->redirect('/admin/users?message=create_success');
        } catch (DomainException $e) {
            $this->redirect('/admin/users?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/admin/users?error=create_failed');
        }
    }

    public function update(string $id): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/users?error=csrf_expired');
        }

        $userId = (int) $id;
        $name = trim($this->postString('name'));
        $email = strtolower(trim($this->postString('email')));
        $phone = trim($this->postString('phone'));
        $role = trim($this->postString('role'));
        $status = trim($this->postString('status'));

        if ($name === '' || strlen($name) > 150) {
            $this->redirect('/admin/users?error=' . urlencode('Họ tên không được để trống và tối đa 150 ký tự.'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/admin/users?error=' . urlencode('Địa chỉ email không hợp lệ.'));
        }

        if (!array_key_exists($role, User::ROLES)) {
            $this->redirect('/admin/users?error=' . urlencode('Vai trò người dùng không hợp lệ.'));
        }

        if (!array_key_exists($status, User::STATUSES)) {
            $status = 'active';
        }

        $db = Database::connection();
        $userModel = new User($db);

        try {
            $userModel->updateUser($userId, [
                'name' => $name,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'role' => $role,
                'status' => $status,
            ], (int) $currentUser['id']);

            $this->redirect('/admin/users?message=update_success');
        } catch (DomainException $e) {
            $this->redirect('/admin/users?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/admin/users?error=update_failed');
        }
    }

    public function resetPassword(string $id): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/users?error=csrf_expired');
        }

        $userId = (int) $id;
        $newPassword = $this->postString('new_password');

        if (strlen($newPassword) < 6) {
            $this->redirect('/admin/users?error=' . urlencode('Mật khẩu mới phải có tối thiểu 6 ký tự.'));
        }

        $db = Database::connection();
        $userModel = new User($db);

        try {
            $userModel->adminResetPassword($userId, $newPassword, (int) $currentUser['id']);
            $this->redirect('/admin/users?message=reset_password_success');
        } catch (DomainException $e) {
            $this->redirect('/admin/users?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/admin/users?error=reset_password_failed');
        }
    }

    public function logs(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();
        $logModel = new ActivityLog($db);

        $action = isset($_GET['action']) && is_string($_GET['action']) ? trim($_GET['action']) : '';
        $targetType = isset($_GET['target_type']) && is_string($_GET['target_type']) ? trim($_GET['target_type']) : '';
        $date = isset($_GET['date']) && is_string($_GET['date']) ? trim($_GET['date']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 20;

        $filters = [];
        if ($action !== '') {
            $filters['action'] = $action;
        }
        if ($targetType !== '') {
            $filters['target_type'] = $targetType;
        }
        if ($date !== '') {
            $filters['date'] = $date;
        }

        $totalLogs = $logModel->count($filters);
        $totalPages = max(1, (int) ceil($totalLogs / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $logs = $logModel->all($filters, $page, $perPage);

        $this->render('admin/users/logs', [
            'pageTitle' => 'Nhật Ký Hoạt Động Hệ Thống — L\'Épicure',
            'currentUser' => $currentUser,
            'logs' => $logs,
            'totalLogs' => $totalLogs,
            'page' => $page,
            'totalPages' => $totalPages,
            'action' => $action,
            'targetType' => $targetType,
            'date' => $date,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/admin');
    }

    private function postString(string $key): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    }
}
