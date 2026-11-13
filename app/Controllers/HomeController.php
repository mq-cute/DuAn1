<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'pageTitle' => "L'Épicure — Nhà hàng & đặt bàn",
            'currentUser' => Auth::user(),
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
