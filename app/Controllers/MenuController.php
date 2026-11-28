<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Models\Category;
use App\Models\Dish;

final class MenuController extends Controller
{
    public function index(): void
    {
        $categorySlug = trim($this->queryString('category'));
        $search = trim($this->queryString('q'));
        $sort = $this->queryString('sort') ?: 'featured';
        if (strlen($categorySlug) > 160) {
            $categorySlug = '';
        }
        if (strlen($search) > 100) {
            $search = mb_substr($search, 0, 100, 'UTF-8');
        }
        if (!in_array($sort, ['featured', 'price_asc', 'price_desc', 'name'], true)) {
            $sort = 'featured';
        }

        $db = Database::connection();
        $categories = new Category($db);
        $selectedCategory = $categorySlug === '' ? null : $categories->findBySlug($categorySlug);
        $this->render('menu/index', [
            'pageTitle' => 'Thực đơn | FOODIES',
            'currentUser' => Auth::user(),
            'categories' => $categories->allVisible(),
            'selectedCategory' => $selectedCategory,
            'dishes' => (new Dish($db))->allVisible(
                $selectedCategory['slug'] ?? null,
                $search === '' ? null : $search,
                $sort
            ),
            'search' => $search,
            'sort' => $sort,
            'csrfToken' => Csrf::token(),
            'error' => $this->queryString('error'),
        ]);
    }

    public function show(string $slug): void
    {
        if (preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug) !== 1) {
            $this->render('errors/404', [
                'pageTitle' => 'Không tìm thấy món ăn',
                'currentUser' => Auth::user(),
            ], 404);
            return;
        }

        $db = Database::connection();
        $dish = (new Dish($db))->findVisibleBySlug($slug);
        if ($dish === null) {
            $this->render('errors/404', [
                'pageTitle' => 'Không tìm thấy món ăn',
                'currentUser' => Auth::user(),
            ], 404);
            return;
        }

        $this->render('menu/show', [
            'pageTitle' => $dish['name'] . ' | FOODIES',
            'currentUser' => Auth::user(),
            'dish' => $dish,
            'csrfToken' => Csrf::token(),
            'error' => $this->queryString('error'),
        ]);
    }

    public function cart(): void
    {
        Session::start();
        $cart = Cart::contents();
        $dishes = $cart === [] ? [] : (new Dish(Database::connection()))->findManyByIds(array_keys($cart));
        $foundIds = array_fill_keys(array_map(static fn (array $dish): int => (int) $dish['id'], $dishes), true);
        foreach (array_keys($cart) as $dishId) {
            if (!isset($foundIds[$dishId])) {
                unset($cart[$dishId]);
            }
        }
        $_SESSION['cart'] = $cart;

        $items = [];
        $subtotalCents = 0;
        foreach ($dishes as $dish) {
            $quantity = $cart[(int) $dish['id']] ?? 0;
            if ($quantity < 1) {
                continue;
            }

            $unitPriceCents = $this->priceToCents((string) $dish['price']);
            $subtotalCents += $unitPriceCents * $quantity;
            $items[] = [
                'dish' => $dish,
                'quantity' => $quantity,
                'is_orderable' => (bool) $dish['is_visible']
                    && (bool) $dish['category_visible']
                    && (bool) $dish['is_available'],
            ];
        }

        $this->render('menu/cart', [
            'pageTitle' => 'Giỏ chọn món | FOODIES',
            'currentUser' => Auth::user(),
            'items' => $items,
            'subtotalCents' => $subtotalCents,
            'csrfToken' => Csrf::token(),
            'error' => $this->queryString('error'),
        ]);
    }

    public function addToCart(): void
    {
        Session::start();
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            echo 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
            return;
        }

        $dishId = filter_var($_POST['dish_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
        if ($dishId === false || $dishId < 1 || $quantity === false || $quantity < 1 || $quantity > 99) {
            $this->redirect('/menu?error=invalid');
        }

        $dish = (new Dish(Database::connection()))->findOrderableById($dishId);
        if ($dish === null) {
            $this->redirect('/menu?error=unavailable');
        }

        $cart = Cart::contents();
        $cart[$dishId] = min(99, ($cart[$dishId] ?? 0) + $quantity);
        $_SESSION['cart'] = $cart;
        $this->redirect('/cart');
    }

    public function updateCart(string $id): void
    {
        Session::start();
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            echo 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
            return;
        }

        $dishId = filter_var($id, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($dishId === false || $dishId < 1 || $quantity === false || $quantity < 0 || $quantity > 99) {
            $this->redirect('/cart?error=invalid');
        }

        $cart = Cart::contents();
        if ($quantity === 0) {
            unset($cart[$dishId]);
            $_SESSION['cart'] = $cart;
            $this->redirect('/cart');
        }

        $dish = (new Dish(Database::connection()))->findOrderableById($dishId);
        if ($dish === null) {
            $this->redirect('/cart?error=unavailable');
        }

        $cart[$dishId] = $quantity;
        $_SESSION['cart'] = $cart;
        $this->redirect('/cart');
    }

    public function removeFromCart(string $id): void
    {
        Session::start();
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            echo 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
            return;
        }

        $dishId = filter_var($id, FILTER_VALIDATE_INT);
        if ($dishId === false || $dishId < 1) {
            $this->redirect('/cart?error=invalid');
        }

        $cart = Cart::contents();
        unset($cart[$dishId]);
        $_SESSION['cart'] = $cart;
        $this->redirect('/cart');
    }

    private function queryString(string $key): string
    {
        $value = $_GET[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function priceToCents(string $price): int
    {
        if (preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', $price, $matches) !== 1) {
            return 0;
        }

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '0', 2, '0');
    }
}
