<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Table;

final class TableController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $db = Database::connection();
        $tableModel = new Table($db);

        $selectedAreaId = isset($_GET['area_id']) && is_numeric($_GET['area_id']) ? (int) $_GET['area_id'] : null;
        $floorData = $tableModel->getFloorPlanData($selectedAreaId);

        $this->render('table/index', [
            'pageTitle' => 'Sơ đồ bàn & Quản lý chỗ ngồi — FOODIES',
            'currentUser' => $currentUser,
            'areas' => $floorData['areas'],
            'tablesByArea' => $floorData['tablesByArea'],
            'totalTables' => $floorData['totalTables'],
            'counts' => $floorData['counts'],
            'selectedAreaId' => $selectedAreaId,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
        ], 200, 'layouts/staff');
    }

    public function updateStatus(string $id): void
    {
        $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/tables?message=invalid_csrf');
        }

        $tableId = (int) $id;
        $newStatus = (string) ($_POST['status'] ?? '');

        $db = Database::connection();
        $tableModel = new Table($db);
        $success = $tableModel->updateStatus($tableId, $newStatus);

        $redirectUrl = isset($_POST['redirect']) && is_string($_POST['redirect']) && str_starts_with($_POST['redirect'], '/')
            ? $_POST['redirect']
            : '/tables';

        $separator = str_contains($redirectUrl, '?') ? '&' : '?';
        $this->redirect($redirectUrl . $separator . 'message=' . ($success ? 'status_updated' : 'update_failed'));
    }
}
