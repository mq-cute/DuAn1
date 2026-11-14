<?php

declare(strict_types=1);

namespace App\Core;

final class Cart
{
    public static function contents(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart)) {
            return [];
        }

        $valid = [];
        foreach ($cart as $id => $quantity) {
            $dishId = filter_var($id, FILTER_VALIDATE_INT);
            $count = filter_var($quantity, FILTER_VALIDATE_INT);
            if ($dishId !== false && $dishId > 0 && $count !== false && $count > 0) {
                $valid[$dishId] = min(99, $count);
            }
        }

        return $valid;
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }
}
