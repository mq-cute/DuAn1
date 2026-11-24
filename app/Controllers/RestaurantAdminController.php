<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\RestaurantSetting;
use DomainException;
use Throwable;

final class RestaurantAdminController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireRole('admin');
        $db = Database::connection();
        $settingModel = new RestaurantSetting($db);

        $settings = $settingModel->getAll();
        $operatingHours = $settingModel->getOperatingHours();
        $pricing = $settingModel->getPricingRates();

        $activeTab = isset($_GET['tab']) && is_string($_GET['tab']) ? trim($_GET['tab']) : 'general';

        $this->render('admin/restaurant/index', [
            'pageTitle' => 'Quản Lý Thông Tin Nhà Hàng & Thiết Lập — FOODIES Admin',
            'currentUser' => $currentUser,
            'settings' => $settings,
            'operatingHours' => $operatingHours,
            'pricing' => $pricing,
            'activeTab' => $activeTab,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/admin');
    }

    public function updateGeneral(): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/restaurant?tab=general&error=csrf_expired');
        }

        $restaurantName = trim($this->postString('restaurant_name'));
        $tagline = trim($this->postString('restaurant_tagline'));
        $address = trim($this->postString('restaurant_address'));
        $phone = trim($this->postString('restaurant_phone'));
        $email = strtolower(trim($this->postString('restaurant_email')));
        $taxNumber = trim($this->postString('restaurant_tax_number'));
        $description = trim($this->postString('restaurant_description'));
        $logo = trim($this->postString('restaurant_logo'));
        $coverImage = trim($this->postString('restaurant_cover_image'));

        if ($restaurantName === '') {
            $this->redirect('/admin/restaurant?tab=general&error=' . urlencode('Tên nhà hàng không được để trống.'));
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/admin/restaurant?tab=general&error=' . urlencode('Địa chỉ email nhà hàng không hợp lệ.'));
        }

        $db = Database::connection();
        $settingModel = new RestaurantSetting($db);

        try {
            $settingModel->setMany([
                'restaurant_name' => $restaurantName,
                'restaurant_tagline' => $tagline,
                'restaurant_address' => $address,
                'restaurant_phone' => $phone,
                'restaurant_email' => $email,
                'restaurant_tax_number' => $taxNumber,
                'restaurant_description' => $description,
                'restaurant_logo' => $logo,
                'restaurant_cover_image' => $coverImage,
            ], (int) $currentUser['id']);

            $this->redirect('/admin/restaurant?tab=general&message=general_updated');
        } catch (Throwable $e) {
            $this->redirect('/admin/restaurant?tab=general&error=update_failed');
        }
    }

    public function updateHours(): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/restaurant?tab=hours&error=csrf_expired');
        }

        $rawHours = $_POST['hours'] ?? [];
        if (!is_array($rawHours)) {
            $this->redirect('/admin/restaurant?tab=hours&error=' . urlencode('Dữ liệu giờ mở cửa không hợp lệ.'));
        }

        $db = Database::connection();
        $settingModel = new RestaurantSetting($db);

        try {
            $settingModel->updateOperatingHours($rawHours, (int) $currentUser['id']);
            $this->redirect('/admin/restaurant?tab=hours&message=hours_updated');
        } catch (Throwable $e) {
            $this->redirect('/admin/restaurant?tab=hours&error=update_failed');
        }
    }

    public function updatePricing(): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/restaurant?tab=pricing&error=csrf_expired');
        }

        $taxRate = isset($_POST['tax_rate']) && is_numeric($_POST['tax_rate']) ? (float) $_POST['tax_rate'] : 8.0;
        $serviceChargeRate = isset($_POST['service_charge_rate']) && is_numeric($_POST['service_charge_rate']) ? (float) $_POST['service_charge_rate'] : 5.0;
        $vatEnabled = !empty($_POST['vat_enabled']) ? '1' : '0';
        $serviceChargeEnabled = !empty($_POST['service_charge_enabled']) ? '1' : '0';

        if ($taxRate < 0 || $taxRate > 100) {
            $this->redirect('/admin/restaurant?tab=pricing&error=' . urlencode('Tỷ lệ thuế VAT phải nằm trong khoảng từ 0% đến 100%.'));
        }

        if ($serviceChargeRate < 0 || $serviceChargeRate > 100) {
            $this->redirect('/admin/restaurant?tab=pricing&error=' . urlencode('Tỷ lệ phí dịch vụ phải nằm trong khoảng từ 0% đến 100%.'));
        }

        $db = Database::connection();
        $settingModel = new RestaurantSetting($db);

        try {
            $settingModel->setMany([
                'tax_rate' => (string) $taxRate,
                'vat_enabled' => $vatEnabled,
                'service_charge_rate' => (string) $serviceChargeRate,
                'service_charge_enabled' => $serviceChargeEnabled,
            ], (int) $currentUser['id']);

            $this->redirect('/admin/restaurant?tab=pricing&message=pricing_updated');
        } catch (Throwable $e) {
            $this->redirect('/admin/restaurant?tab=pricing&error=update_failed');
        }
    }

    public function updatePolicies(): void
    {
        $currentUser = $this->requireRole('admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/restaurant?tab=policies&error=csrf_expired');
        }

        $bookingPolicy = trim($this->postString('booking_policy'));
        $cancellationPolicy = trim($this->postString('cancellation_policy'));
        $refundPolicy = trim($this->postString('refund_policy'));
        $generalTerms = trim($this->postString('general_terms'));

        $db = Database::connection();
        $settingModel = new RestaurantSetting($db);

        try {
            $settingModel->setMany([
                'booking_policy' => $bookingPolicy,
                'cancellation_policy' => $cancellationPolicy,
                'refund_policy' => $refundPolicy,
                'general_terms' => $generalTerms,
            ], (int) $currentUser['id']);

            $this->redirect('/admin/restaurant?tab=policies&message=policies_updated');
        } catch (Throwable $e) {
            $this->redirect('/admin/restaurant?tab=policies&error=update_failed');
        }
    }

    private function postString(string $key): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    }
}
