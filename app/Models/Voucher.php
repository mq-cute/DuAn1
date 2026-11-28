<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use PDO;

final class Voucher extends BaseModel
{
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM vouchers
             WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW()
               AND (usage_limit IS NULL OR used_count < usage_limit)
             ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForCustomer(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM vouchers WHERE is_active = 1
             ORDER BY end_date DESC, id ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM vouchers WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function validateForBill(string $code, float $eligibleAmount, bool $lock = false): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            throw new DomainException('voucher_required');
        }
        $sql = 'SELECT * FROM vouchers WHERE code = :code LIMIT 1' . ($lock ? ' FOR UPDATE' : '');
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($voucher === false || !(bool) $voucher['is_active']) {
            throw new DomainException('voucher_invalid');
        }
        $now = time();
        if (strtotime((string) $voucher['start_date']) > $now || strtotime((string) $voucher['end_date']) < $now) {
            throw new DomainException('voucher_expired');
        }
        if ($voucher['usage_limit'] !== null && (int) $voucher['used_count'] >= (int) $voucher['usage_limit']) {
            throw new DomainException('voucher_unavailable');
        }
        if ($eligibleAmount < (float) ($voucher['min_order_amount'] ?? 0)) {
            throw new DomainException('voucher_minimum');
        }
        return $voucher;
    }

    public static function calculateDiscount(?array $voucher, float $eligibleFoodAmount): float
    {
        if ($voucher === null || $eligibleFoodAmount <= 0 || $eligibleFoodAmount < (float) ($voucher['min_order_amount'] ?? 0)) {
            return 0.0;
        }
        $discount = ($voucher['discount_type'] ?? '') === 'percentage'
            ? $eligibleFoodAmount * (float) $voucher['discount_value'] / 100
            : (float) $voucher['discount_value'];
        if (($voucher['max_discount_amount'] ?? null) !== null) {
            $discount = min($discount, (float) $voucher['max_discount_amount']);
        }
        return round(min(max(0.0, $discount), $eligibleFoodAmount), 2);
    }
}
