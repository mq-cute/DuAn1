<?php

declare(strict_types=1);

namespace App\Models;

use RuntimeException;

final class User extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.name, u.email, u.password, u.status, r.name AS role
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function createCustomer(string $name, string $email, string $passwordHash): int
    {
        $roleStatement = $this->db->prepare(
            'SELECT id FROM roles WHERE name = :name LIMIT 1'
        );
        $roleStatement->execute(['name' => 'customer']);
        $roleId = $roleStatement->fetchColumn();

        if ($roleId === false) {
            throw new RuntimeException('Customer role is missing from the database.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (role_id, name, email, password, status)
             VALUES (:role_id, :name, :email, :password, :status)'
        );
        $statement->execute([
            'role_id' => (int) $roleId,
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'status' => 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }
}
