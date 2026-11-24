<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ComboSetup extends BaseModel
{
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM combo_setups WHERE is_active = 1 ORDER BY is_featured DESC, id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM combo_setups WHERE slug = :slug AND is_active = 1 LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
}
