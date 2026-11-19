<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class RestaurantSetting extends BaseModel
{
    private static ?array $cachedSettings = null;

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT `key`, `value`, `group_name` FROM restaurant_settings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }

        self::$cachedSettings = $result;
        return $result;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (self::$cachedSettings === null) {
            $this->getAll();
        }

        return self::$cachedSettings[$key] ?? $default;
    }

    public function setMany(array $settings, int $actorId): bool
    {
        $this->db->beginTransaction();
        try {
            $oldSettings = $this->getAll();
            $changed = [];

            $stmt = $this->db->prepare(
                'INSERT INTO restaurant_settings (`key`, `value`, `group_name`, `updated_at`)
                 VALUES (:key, :value, :group_name, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = CURRENT_TIMESTAMP'
            );

            foreach ($settings as $key => $val) {
                $group = 'general';
                if (str_starts_with($key, 'tax_') || str_starts_with($key, 'vat_') || str_starts_with($key, 'service_charge_')) {
                    $group = 'pricing';
                } elseif (str_ends_with($key, '_policy') || $key === 'general_terms') {
                    $group = 'policies';
                }

                $stmt->execute([
                    'key' => (string) $key,
                    'value' => (string) $val,
                    'group_name' => $group,
                ]);

                if (($oldSettings[$key] ?? null) !== (string) $val) {
                    $changed[$key] = [
                        'old' => $oldSettings[$key] ?? null,
                        'new' => (string) $val,
                    ];
                }
            }

            if ($changed !== []) {
                ActivityLog::log(
                    $this->db,
                    $actorId,
                    'update_restaurant_settings',
                    'restaurant_settings',
                    null,
                    ['changed_keys' => array_keys($changed), 'diff' => $changed]
                );
            }

            $this->db->commit();
            self::$cachedSettings = null; // Clear cache
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getOperatingHours(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM restaurant_operating_hours ORDER BY FIELD(day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday")'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOperatingHours(array $hoursData, int $actorId): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE restaurant_operating_hours
                 SET open_time = :open_time,
                     close_time = :close_time,
                     is_closed = :is_closed,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE day_of_week = :day_of_week'
            );

            $oldHours = $this->getOperatingHours();
            $summary = [];

            foreach ($hoursData as $day => $config) {
                $openTime = !empty($config['open_time']) ? (string) $config['open_time'] : '10:00:00';
                $closeTime = !empty($config['close_time']) ? (string) $config['close_time'] : '23:00:00';
                $isClosed = !empty($config['is_closed']) ? 1 : 0;

                // Chuẩn hóa định dạng time nếu chỉ gửi H:i
                if (strlen($openTime) === 5) {
                    $openTime .= ':00';
                }
                if (strlen($closeTime) === 5) {
                    $closeTime .= ':00';
                }

                $stmt->execute([
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'is_closed' => $isClosed,
                    'day_of_week' => $day,
                ]);

                $summary[$day] = [
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'is_closed' => $isClosed,
                ];
            }

            ActivityLog::log(
                $this->db,
                $actorId,
                'update_operating_hours',
                'restaurant_operating_hours',
                null,
                ['schedule' => $summary]
            );

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPricingRates(): array
    {
        $all = $this->getAll();

        $vatEnabled = (bool) ($all['vat_enabled'] ?? true);
        $taxRatePercent = isset($all['tax_rate']) ? (float) $all['tax_rate'] : 8.0;
        $serviceChargeEnabled = (bool) ($all['service_charge_enabled'] ?? true);
        $serviceChargeRatePercent = isset($all['service_charge_rate']) ? (float) $all['service_charge_rate'] : 5.0;

        return [
            'vat_enabled' => $vatEnabled,
            'tax_rate_percent' => $taxRatePercent,
            'tax_rate' => $vatEnabled ? ($taxRatePercent / 100.0) : 0.0,
            'service_charge_enabled' => $serviceChargeEnabled,
            'service_charge_rate_percent' => $serviceChargeRatePercent,
            'service_charge_rate' => $serviceChargeEnabled ? ($serviceChargeRatePercent / 100.0) : 0.0,
        ];
    }
}
