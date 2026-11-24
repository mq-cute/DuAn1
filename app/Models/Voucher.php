<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Voucher extends BaseModel
{
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM vouchers 
             WHERE is_active = 1 
               AND start_date <= NOW() 
               AND end_date >= NOW() 
             ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vouchers 
             WHERE code = :code 
               AND is_active = 1 
               AND start_date <= NOW() 
               AND end_date >= NOW() 
             LIMIT 1"
        );
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
}
