<section class="status-page">
    <p class="status-page__code">403</p>
    <h1>Bạn không có quyền truy cập</h1>
    <p>Tài khoản hiện tại không được phép thực hiện thao tác này.</p>
    <?php
    $role = $currentUser['role'] ?? '';
    $returnUrl = match ($role) {
        'kitchen' => '/kitchen',
        'staff', 'admin' => '/staff/pos',
        default => '/',
    };
    $returnLabel = $role === 'kitchen' ? 'Quay lại màn hình bếp' : (in_array($role, ['staff', 'admin'], true) ? 'Quay lại POS' : 'Về trang chủ');
    ?>
    <a class="status-page__action status-page__action--outline" href="<?= \App\Core\View::escape($returnUrl) ?>"><?= \App\Core\View::escape($returnLabel) ?></a>
</section>
