<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use PDO;
use RuntimeException;
use Throwable;

final class User extends BaseModel
{
    public const ROLES = [
        'admin' => 'Quản trị viên',
        'staff' => 'Nhân viên phục vụ',
        'kitchen' => 'Nhân viên bếp',
        'customer' => 'Khách hàng',
    ];

    public const STATUSES = [
        'active' => 'Hoạt động',
        'inactive' => 'Tạm khóa',
    ];

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.role_id, u.name, u.email, u.phone, u.avatar, u.password, u.status, u.created_at, r.name AS role, r.description AS role_description
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    public function getPasswordHash(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $val = $stmt->fetchColumn();

        return $val === false ? null : (string) $val;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.role_id, u.name, u.email, u.phone, u.avatar, u.status, u.created_at, u.updated_at,
                    r.name AS role, r.description AS role_description
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    public function allRoles(): array
    {
        $stmt = $this->db->query('SELECT id, name, description FROM roles ORDER BY id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleIdByName(string $roleName): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $roleName]);
        $val = $stmt->fetchColumn();

        return $val === false ? null : (int) $val;
    }

    public function countActiveAdmins(): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE r.name = "admin" AND u.status = "active"'
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function listUsers(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(u.name LIKE :search_name OR u.email LIKE :search_email OR u.phone LIKE :search_phone)';
            $searchPattern = '%' . trim((string) $filters['search']) . '%';
            $params['search_name'] = $searchPattern;
            $params['search_email'] = $searchPattern;
            $params['search_phone'] = $searchPattern;
        }

        if (!empty($filters['role'])) {
            $conditions[] = 'r.name = :role';
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'u.status = :status';
            $params['status'] = $filters['status'];
        }

        $whereClause = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT u.id, u.role_id, u.name, u.email, u.phone, u.avatar, u.status, u.created_at,
                       r.name AS role, r.description AS role_description
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                {$whereClause}
                ORDER BY u.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(u.name LIKE :search_name OR u.email LIKE :search_email OR u.phone LIKE :search_phone)';
            $searchPattern = '%' . trim((string) $filters['search']) . '%';
            $params['search_name'] = $searchPattern;
            $params['search_email'] = $searchPattern;
            $params['search_phone'] = $searchPattern;
        }

        if (!empty($filters['role'])) {
            $conditions[] = 'r.name = :role';
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'u.status = :status';
            $params['status'] = $filters['status'];
        }

        $whereClause = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function createCustomer(string $name, string $email, string $passwordHash): int
    {
        $roleId = $this->getRoleIdByName('customer');
        if ($roleId === null) {
            throw new RuntimeException('Customer role is missing from the database.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (role_id, name, email, password, status, created_at, updated_at)
             VALUES (:role_id, :name, :email, :password, "active", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $statement->execute([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createUser(
        string $name,
        string $email,
        string $passwordHash,
        string $roleName,
        ?string $phone = null,
        string $status = 'active',
        ?int $actorId = null
    ): int {
        // Kiểm tra email duy nhất
        if ($this->findByEmail($email) !== null) {
            throw new DomainException('Địa chỉ email này đã được sử dụng bởi một tài khoản khác.');
        }

        $roleId = $this->getRoleIdByName($roleName);
        if ($roleId === null) {
            throw new DomainException('Vai trò người dùng không hợp lệ.');
        }

        if (!array_key_exists($status, self::STATUSES)) {
            $status = 'active';
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (role_id, name, email, phone, password, status, created_at, updated_at)
                 VALUES (:role_id, :name, :email, :phone, :password, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'role_id' => $roleId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $passwordHash,
                'status' => $status,
            ]);

            $newUserId = (int) $this->db->lastInsertId();

            ActivityLog::log(
                $this->db,
                $actorId,
                'create_user',
                'users',
                $newUserId,
                [
                    'name' => $name,
                    'email' => $email,
                    'role' => $roleName,
                    'status' => $status,
                ]
            );

            $this->db->commit();
            return $newUserId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateUser(
        int $id,
        array $data,
        int $actorId
    ): bool {
        $user = $this->findById($id);
        if ($user === null) {
            throw new DomainException('Không tìm thấy tài khoản người dùng.');
        }

        // Kiểm tra email nếu thay đổi
        if (isset($data['email']) && strtolower($data['email']) !== strtolower($user['email'])) {
            $existing = $this->findByEmail($data['email']);
            if ($existing !== null && (int) $existing['id'] !== $id) {
                throw new DomainException('Địa chỉ email đã được sử dụng bởi tài khoản khác.');
            }
        }

        // Quy tắc: Không cho phép khóa hoặc hạ quyền Admin cuối cùng!
        $newRole = $data['role'] ?? $user['role'];
        $newStatus = $data['status'] ?? $user['status'];
        if ($user['role'] === 'admin') {
            $isDemoting = ($newRole !== 'admin');
            $isDeactivating = ($newStatus !== 'active');

            if ($isDemoting || $isDeactivating) {
                $activeAdmins = $this->countActiveAdmins();
                if ($activeAdmins <= 1) {
                    throw new DomainException('Không thể khóa tài khoản hoặc hạ quyền Quản trị viên (Admin) duy nhất của hệ thống.');
                }
            }
        }

        $roleId = (int) $user['role_id'];
        if (isset($data['role'])) {
            $targetRoleId = $this->getRoleIdByName($data['role']);
            if ($targetRoleId === null) {
                throw new DomainException('Vai trò người dùng không hợp lệ.');
            }
            $roleId = $targetRoleId;
        }

        $this->db->beginTransaction();
        try {
            $name = $data['name'] ?? $user['name'];
            $email = strtolower($data['email'] ?? $user['email']);
            $phone = array_key_exists('phone', $data) ? $data['phone'] : $user['phone'];
            $avatar = array_key_exists('avatar', $data) ? $data['avatar'] : $user['avatar'];
            $status = $newStatus;

            $stmt = $this->db->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     phone = :phone,
                     avatar = :avatar,
                     role_id = :role_id,
                     status = :status,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'avatar' => $avatar,
                'role_id' => $roleId,
                'status' => $status,
                'id' => $id,
            ]);

            $diff = [];
            foreach (['name', 'email', 'phone', 'status'] as $field) {
                if (($user[$field] ?? null) !== ($data[$field] ?? null)) {
                    $diff[$field] = ['old' => $user[$field] ?? null, 'new' => $data[$field] ?? null];
                }
            }
            if ($user['role'] !== $newRole) {
                $diff['role'] = ['old' => $user['role'], 'new' => $newRole];
            }

            ActivityLog::log(
                $this->db,
                $actorId,
                'update_user',
                'users',
                $id,
                ['diff' => $diff]
            );

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $stmt = $this->db->prepare('SELECT id, password FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false || !password_verify($oldPassword, $user['password'])) {
            throw new DomainException('Mật khẩu hiện tại không chính xác.');
        }

        if (strlen($newPassword) < 6) {
            throw new DomainException('Mật khẩu mới phải có tối thiểu 6 ký tự.');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $this->db->prepare('UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute(['password' => $hash, 'id' => $userId]);

        ActivityLog::log(
            $this->db,
            $userId,
            'change_password',
            'users',
            $userId,
            ['action' => 'User self-changed password']
        );

        return true;
    }

    public function adminResetPassword(int $userId, string $newPassword, int $actorId): bool
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new DomainException('Không tìm thấy tài khoản người dùng.');
        }

        if (strlen($newPassword) < 6) {
            throw new DomainException('Mật khẩu mới phải có tối thiểu 6 ký tự.');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $this->db->prepare('UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute(['password' => $hash, 'id' => $userId]);

        ActivityLog::log(
            $this->db,
            $actorId,
            'reset_password',
            'users',
            $userId,
            ['target_email' => $user['email']]
        );

        return true;
    }
}
