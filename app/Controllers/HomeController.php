<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Category;
use App\Models\ComboSetup;
use App\Models\Dish;
use App\Models\Table;
use App\Models\Voucher;

final class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::connection();
        $dishModel = new Dish($db);
        $categoryModel = new Category($db);
        $tableModel = new Table($db);
        $comboModel = new ComboSetup($db);
        $voucherModel = new Voucher($db);

        $selectedCategorySlug = isset($_GET['category']) && is_string($_GET['category']) ? trim($_GET['category']) : null;
        
        $featuredDishes = $dishModel->allVisible($selectedCategorySlug);
        $categories = $categoryModel->allVisible();
        $areas = $tableModel->allAreas();
        $combos = $comboModel->allActive();
        $vouchers = $voucherModel->allActive();

        $this->render('home/index', [
            'pageTitle' => "FOODIES — Nghệ Thuật Ẩm Thực Đương Đại",
            'currentUser' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'featuredDishes' => $featuredDishes,
            'categories' => $categories,
            'areas' => $areas,
            'combos' => $combos,
            'vouchers' => $vouchers,
            'selectedCategorySlug' => $selectedCategorySlug,
        ]);
    }

    public function account(): void
    {
        $this->render('account/index', [
            'pageTitle' => 'Tài khoản của bạn',
            'currentUser' => $this->requireAuthenticated(),
        ]);
    }
}
