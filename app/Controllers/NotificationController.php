<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireAuthenticated();
        $db = Database::connection();
        $notifModel = new Notification($db);

        $notifications = $notifModel->forUser((int) $currentUser['id']);
        $unreadCount = $notifModel->unreadCount((int) $currentUser['id']);

        $this->render('account/notifications', [
            'pageTitle' => 'Thông Báo Của Tôi — L\'Épicure Gastronomie',
            'currentUser' => $currentUser,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
        ], 200, 'layouts/guest');
    }

    public function markRead(string $id): void
    {
        $currentUser = $this->requireAuthenticated();
        if (!$this->verifyCsrf()) {
            $this->redirect('/account/notifications?error=csrf_expired');
        }

        $notifId = (int) $id;
        $db = Database::connection();
        $notifModel = new Notification($db);
        $notifModel->markAsRead($notifId, (int) $currentUser['id']);

        $redirect = isset($_POST['redirect']) && is_string($_POST['redirect']) ? $_POST['redirect'] : '/account/notifications';
        $this->redirect($redirect);
    }

    public function markAllRead(): void
    {
        $currentUser = $this->requireAuthenticated();
        if (!$this->verifyCsrf()) {
            $this->redirect('/account/notifications?error=csrf_expired');
        }

        $db = Database::connection();
        $notifModel = new Notification($db);
        $notifModel->markAllAsRead((int) $currentUser['id']);

        $this->redirect('/account/notifications?message=all_marked_read');
    }

    public function unreadCountJson(): void
    {
        $currentUser = $this->requireAuthenticated();
        $db = Database::connection();
        $notifModel = new Notification($db);
        $count = $notifModel->unreadCount((int) $currentUser['id']);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['unread_count' => $count], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
