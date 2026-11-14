<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Category;
use PDOException;

final class CategoryAdminController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $this->render('admin/categories/index', [
            'pageTitle' => 'Quản lý danh mục',
            'currentUser' => Auth::user(),
            'categories' => (new Category(Database::connection()))->allForAdmin(),
            'csrfToken' => Csrf::token(),
            'errors' => [],
            'old' => [],
            'message' => $this->stringInput($_GET, 'message'),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $categoryId = $this->validId($id);
        $category = $categoryId === null
            ? null
            : (new Category(Database::connection()))->findById($categoryId);
        if ($category === null) {
            $this->render('errors/404', [
                'pageTitle' => 'Không tìm thấy danh mục',
                'currentUser' => Auth::user(),
            ], 404);
            return;
        }

        $this->render('admin/categories/edit', [
            'pageTitle' => 'Sửa danh mục',
            'currentUser' => Auth::user(),
            'category' => $category,
            'csrfToken' => Csrf::token(),
            'errors' => [],
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/menu/categories?message=expired');
        }

        $input = $this->validateInput($_POST);
        if (isset($input['errors'])) {
            $this->renderCategoryList($input['errors'], $_POST, 422);
            return;
        }

        $categories = new Category(Database::connection());
        if ($categories->slugExists($input['category']['slug'])) {
            $this->renderCategoryList(['Đường dẫn danh mục đã được sử dụng.'], $_POST, 422);
            return;
        }

        try {
            $categories->create($input['category']);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->renderCategoryList(['Tên đường dẫn đã tồn tại; vui lòng chọn đường dẫn khác.'], $_POST, 422);
            return;
        }

        $this->redirect('/admin/menu/categories?message=saved');
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        $categoryId = $this->validId($id);
        if ($categoryId === null || !$this->verifyCsrf()) {
            $this->redirect('/admin/menu/categories?message=invalid');
        }

        $categories = new Category(Database::connection());
        $existing = $categories->findById($categoryId);
        if ($existing === null) {
            $this->redirect('/admin/menu/categories?message=missing');
        }

        $input = $this->validateInput($_POST);
        if (isset($input['errors'])) {
            $this->render('admin/categories/edit', [
                'pageTitle' => 'Sửa danh mục',
                'currentUser' => Auth::user(),
                'category' => array_merge($existing, $_POST, [
                    'is_visible' => isset($_POST['is_visible']),
                ]),
                'csrfToken' => Csrf::token(),
                'errors' => $input['errors'],
            ], 422);
            return;
        }

        if ($categories->slugExists($input['category']['slug'], $categoryId)) {
            $this->render('admin/categories/edit', [
                'pageTitle' => 'Sửa danh mục',
                'currentUser' => Auth::user(),
                'category' => array_merge($existing, $_POST, [
                    'is_visible' => isset($_POST['is_visible']),
                ]),
                'csrfToken' => Csrf::token(),
                'errors' => ['Đường dẫn danh mục đã được sử dụng.'],
            ], 422);
            return;
        }

        try {
            $categories->update($categoryId, $input['category']);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->render('admin/categories/edit', [
                'pageTitle' => 'Sửa danh mục',
                'currentUser' => Auth::user(),
                'category' => array_merge($existing, $_POST),
                'csrfToken' => Csrf::token(),
                'errors' => ['Tên đường dẫn đã tồn tại; vui lòng chọn đường dẫn khác.'],
            ], 422);
            return;
        }

        $this->redirect('/admin/menu/categories?message=saved');
    }

    public function toggle(string $id): void
    {
        $this->requireRole('admin');
        $categoryId = $this->validId($id);
        if ($categoryId === null || !$this->verifyCsrf()) {
            $this->redirect('/admin/menu/categories?message=invalid');
        }

        $categories = new Category(Database::connection());
        $category = $categories->findById($categoryId);
        if ($category !== null) {
            $categories->updateVisibility($categoryId, !(bool) $category['is_visible']);
        }

        $this->redirect('/admin/menu/categories?message=saved');
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $categoryId = $this->validId($id);
        if ($categoryId === null || !$this->verifyCsrf()) {
            $this->redirect('/admin/menu/categories?message=invalid');
        }

        $categories = new Category(Database::connection());
        $category = $categories->findById($categoryId);
        if ($category === null) {
            $this->redirect('/admin/menu/categories?message=missing');
        }
        if ((int) $category['dish_count'] > 0) {
            $this->redirect('/admin/menu/categories?message=in-use');
        }

        try {
            $categories->delete($categoryId);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->redirect('/admin/menu/categories?message=in-use');
        }

        $this->redirect('/admin/menu/categories?message=deleted');
    }

    private function validateInput(array $input): array
    {
        $name = trim($this->stringInput($input, 'name'));
        $slug = strtolower(trim($this->stringInput($input, 'slug')));
        $description = trim($this->stringInput($input, 'description'));
        $sortOrder = filter_var($input['sort_order'] ?? '0', FILTER_VALIDATE_INT);
        $errors = [];

        $nameLength = preg_match_all('/./us', $name);
        if ($name === '' || $nameLength === false || $nameLength > 150) {
            $errors[] = 'Tên danh mục là bắt buộc và tối đa 150 ký tự.';
        }
        if (preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug) !== 1 || strlen($slug) > 150) {
            $errors[] = 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.';
        }
        if ($sortOrder === false || $sortOrder < 0 || $sortOrder > 2147483647) {
            $errors[] = 'Thứ tự sắp xếp phải là số nguyên không âm.';
        }
        if (strlen($description) > 65535) {
            $errors[] = 'Mô tả danh mục quá dài.';
        }

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        return ['category' => [
            'name' => $name,
            'slug' => $slug,
            'description' => $description === '' ? null : $description,
            'sort_order' => $sortOrder,
            'is_visible' => ($input['is_visible'] ?? null) === '1' ? 1 : 0,
        ]];
    }

    private function renderCategoryList(array $errors, array $old, int $status): void
    {
        $old['_submitted'] = true;
        $this->render('admin/categories/index', [
            'pageTitle' => 'Quản lý danh mục',
            'currentUser' => Auth::user(),
            'categories' => (new Category(Database::connection()))->allForAdmin(),
            'csrfToken' => Csrf::token(),
            'errors' => $errors,
            'old' => $old,
            'message' => '',
        ], $status);
    }

    private function stringInput(array $input, string $key): string
    {
        $value = $input[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function validId(string $id): ?int
    {
        $value = filter_var($id, FILTER_VALIDATE_INT);

        return $value !== false && $value > 0 ? $value : null;
    }
}
