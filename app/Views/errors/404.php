<section class="status-page">
    <p class="status-page__code">404</p>
    <h1>Không tìm thấy trang</h1>
    <p>Đường dẫn này chưa có hoặc không tồn tại.</p>
    <?php
    $returnUrl = $homeUrl ?? '/';
    $returnLabel = match ($currentUser['role'] ?? '') {
        'kitchen' => 'Quay lại màn hình bếp',
        'staff', 'admin' => 'Quay lại POS',
        default => 'Về trang chủ',
    };
    ?>
    <a class="status-page__action" href="<?= \App\Core\View::escape($returnUrl) ?>"><?= \App\Core\View::escape($returnLabel) ?></a>
</section>
