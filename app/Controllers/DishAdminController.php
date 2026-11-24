<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\ImageUpload;
use App\Models\Category;
use App\Models\Dish;
use InvalidArgumentException;
use PDOException;
use Throwable;

final class DishAdminController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $db = Database::connection();
        $categoryId = filter_var($_GET['category'] ?? null, FILTER_VALIDATE_INT);
        $search = trim($this->stringInput($_GET, 'q'));

        $this->render('admin/dishes/index', [
            'pageTitle' => 'Quản lý Thực đơn & Danh mục Món ăn — FOODIES Admin',
            'currentUser' => Auth::user(),
            'categories' => (new Category($db))->allForAdmin(),
            'dishes' => (new Dish($db))->allForAdmin(
                $search === '' ? null : mb_substr($search, 0, 100, 'UTF-8'),
                $categoryId !== false && $categoryId > 0 ? $categoryId : null
            ),
            'search' => mb_substr($search, 0, 100, 'UTF-8'),
            'selectedCategoryId' => $categoryId !== false && $categoryId > 0 ? $categoryId : null,
            'csrfToken' => Csrf::token(),
            'message' => $this->stringInput($_GET, 'message'),
        ], 200, 'layouts/admin');
    }

    public function createForm(): void
    {
        $this->requireRole('admin');
        $this->renderForm(null, [], []);
    }

    public function editForm(string $id): void
    {
        $this->requireRole('admin');
        $dishId = $this->validId($id);
        $dish = $dishId === null ? null : (new Dish(Database::connection()))->findById($dishId);
        if ($dish === null) {
            $this->render('errors/404', [
                'pageTitle' => 'Không tìm thấy món ăn',
                'currentUser' => Auth::user(),
            ], 404);
            return;
        }

        $this->renderForm($dish, [], []);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->renderForm(null, ['Biểu mẫu đã hết hạn. Vui lòng tải lại trang và thử lại.'], $_POST, 422);
            return;
        }

        $input = $this->validateInput($_POST);
        if (isset($input['errors'])) {
            $this->renderForm(null, $input['errors'], $_POST, 422);
            return;
        }

        $dishes = new Dish(Database::connection());
        if (!$dishes->categoryExists($input['dish']['category_id'])) {
            $this->renderForm(null, ['Danh mục đã chọn không tồn tại.'], $_POST, 422);
            return;
        }
        if ($dishes->slugExists($input['dish']['slug'])) {
            $this->renderForm(null, ['Đường dẫn món ăn đã được sử dụng.'], $_POST, 422);
            return;
        }

        $uploadedImage = null;
        try {
            $uploadedImage = ImageUpload::upload($_FILES['image'] ?? null);
            $input['dish']['image'] = $uploadedImage;
            $dishes->create($input['dish']);
        } catch (InvalidArgumentException $exception) {
            $this->renderForm(null, [$exception->getMessage()], $_POST, 422);
            return;
        } catch (PDOException $exception) {
            ImageUpload::deleteManaged($uploadedImage);
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->renderForm(null, ['Không thể lưu món ăn. Hãy kiểm tra đường dẫn và danh mục đã chọn.'], $_POST, 422);
            return;
        } catch (Throwable $exception) {
            ImageUpload::deleteManaged($uploadedImage);
            throw $exception;
        }

        $this->redirect('/admin/menu?message=saved');
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        $dishId = $this->validId($id);
        if ($dishId === null || !$this->verifyCsrf()) {
            $this->redirect('/admin/menu?message=invalid');
        }

        $dishes = new Dish(Database::connection());
        $existing = $dishes->findById($dishId);
        if ($existing === null) {
            $this->redirect('/admin/menu?message=missing');
        }

        $input = $this->validateInput($_POST);
        if (isset($input['errors'])) {
            $this->renderForm($existing, $input['errors'], $_POST, 422);
            return;
        }
        if (!$dishes->categoryExists($input['dish']['category_id'])) {
            $this->renderForm($existing, ['Danh mục đã chọn không tồn tại.'], $_POST, 422);
            return;
        }
        if ($dishes->slugExists($input['dish']['slug'], $dishId)) {
            $this->renderForm($existing, ['Đường dẫn món ăn đã được sử dụng.'], $_POST, 422);
            return;
        }

        $uploadedImage = null;
        try {
            $uploadedImage = ImageUpload::upload($_FILES['image'] ?? null);
            $input['dish']['image'] = $uploadedImage ?? $existing['image'];
            $dishes->update($dishId, $input['dish']);
        } catch (InvalidArgumentException $exception) {
            $this->renderForm($existing, [$exception->getMessage()], $_POST, 422);
            return;
        } catch (PDOException $exception) {
            ImageUpload::deleteManaged($uploadedImage);
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->renderForm($existing, ['Không thể lưu món ăn. Hãy kiểm tra đường dẫn và danh mục đã chọn.'], $_POST, 422);
            return;
        } catch (Throwable $exception) {
            ImageUpload::deleteManaged($uploadedImage);
            throw $exception;
        }

        if ($uploadedImage !== null) {
            ImageUpload::deleteManaged($existing['image']);
        }
        $this->redirect('/admin/menu?message=saved');
    }

    public function toggleVisibility(string $id): void
    {
        $this->toggle($id, 'visible');
    }

    public function toggleAvailability(string $id): void
    {
        $this->toggle($id, 'available');
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $dishId = $this->validId($id);
        if ($dishId === null || !$this->verifyCsrf()) {
            $this->redirect('/admin/menu?message=invalid');
        }

        $dishes = new Dish(Database::connection());
        $dish = $dishes->findById($dishId);
        if ($dish === null) {
            $this->redirect('/admin/menu?message=missing');
        }

        try {
            $dishes->delete($dishId);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->redirect('/admin/menu?message=has-history');
        }

        ImageUpload::deleteManaged($dish['image']);
        $this->redirect('/admin/menu?message=deleted');
    }

    private function toggle(string $id, string $flag): void
    {
        $this->requireRole('admin');
        $dishId = $this->validId($id);
        if ($dishId === null || !$this->verifyCsrf()) {
            $this->redirect('/admin/menu?message=invalid');
        }

        $dishes = new Dish(Database::connection());
        $dish = $dishes->findById($dishId);
        if ($dish !== null) {
            $dishes->updateFlags(
                $dishId,
                $flag === 'visible' ? !(bool) $dish['is_visible'] : null,
                $flag === 'available' ? !(bool) $dish['is_available'] : null
            );
        }

        $this->redirect('/admin/menu?message=saved');
    }

    private function validateInput(array $input): array
    {
        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim($this->stringInput($input, 'name'));
        $slug = strtolower(trim($this->stringInput($input, 'slug')));
        $description = trim($this->stringInput($input, 'description'));
        $price = trim($this->stringInput($input, 'price'));
        $preparationTime = trim($this->stringInput($input, 'preparation_time'));
        $errors = [];

        if ($categoryId === false || $categoryId < 1) {
            $errors[] = 'Vui lòng chọn danh mục món ăn.';
        }
        $nameLength = preg_match_all('/./us', $name);
        if ($name === '' || $nameLength === false || $nameLength > 200) {
            $errors[] = 'Tên món ăn là bắt buộc và tối đa 200 ký tự.';
        }
        if (preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug) !== 1 || strlen($slug) > 200) {
            $errors[] = 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.';
        }
        if (!preg_match('/\A\d{1,10}(?:\.\d{1,2})?\z/', $price) || preg_match('/[1-9]/', $price) !== 1) {
            $errors[] = 'Giá món phải lớn hơn 0 và không vượt quá 10 chữ số phần nguyên.';
        }
        if (strlen($description) > 65535) {
            $errors[] = 'Mô tả món ăn quá dài.';
        }
        if ($preparationTime !== '' && (
            filter_var($preparationTime, FILTER_VALIDATE_INT) === false
            || (int) $preparationTime < 1
            || (int) $preparationTime > 1440
        )) {
            $errors[] = 'Thời gian chế biến phải từ 1 đến 1440 phút.';
        }

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '');
        $whole = ltrim($whole, '0');
        $price = ($whole === '' ? '0' : $whole) . '.' . str_pad($fraction, 2, '0');

        return ['dish' => [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description === '' ? null : $description,
            'price' => $price,
            'preparation_time' => $preparationTime === '' ? null : (int) $preparationTime,
            'is_available' => ($input['is_available'] ?? null) === '1' ? 1 : 0,
            'is_visible' => ($input['is_visible'] ?? null) === '1' ? 1 : 0,
        ]];
    }

    private function renderForm(?array $dish, array $errors, array $old, int $status = 200): void
    {
        if ($old !== []) {
            $old['_submitted'] = true;
        }
        $this->render('admin/dishes/form', [
            'pageTitle' => $dish === null ? 'Thêm món ăn' : 'Sửa món ăn',
            'currentUser' => Auth::user(),
            'dish' => $dish,
            'categories' => (new Category(Database::connection()))->allForAdmin(),
            'csrfToken' => Csrf::token(),
            'errors' => $errors,
            'old' => $old,
        ], $status, 'layouts/admin');
    }

    private function validId(string $id): ?int
    {
        $value = filter_var($id, FILTER_VALIDATE_INT);

        return $value !== false && $value > 0 ? $value : null;
    }

    private function stringInput(array $input, string $key): string
    {
        $value = $input[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
