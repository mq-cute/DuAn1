<?php
$appCssPath = dirname(__DIR__, 3) . '/public/assets/css/app.css';
$statusCssPath = dirname(__DIR__, 3) . '/public/assets/css/status-pages.css';
$appCssVer = is_file($appCssPath) ? (string) filemtime($appCssPath) : '1';
$statusCssVer = is_file($statusCssPath) ? (string) filemtime($statusCssPath) : '1';
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= \App\Core\View::escape($pageTitle ?? "L'Épicure Gastronomie — Quản lý Vận hành & Phục vụ") ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $appCssVer ?>"/>
    <link rel="stylesheet" href="/assets/css/status-pages.css?v=<?= $statusCssVer ?>"/>
</head>
<body class="staff-shell bg-background text-on-surface font-body antialiased min-h-screen flex flex-col selection:bg-primary selection:text-on-primary">
    <!-- TOP APP BAR -->
    <header class="bg-surface/95 backdrop-blur-md sticky top-0 z-50 border-b border-outline-variant/30 shadow-[0_2px_8px_-2px_rgba(43,33,24,0.06)]">
        <div class="flex justify-between items-center w-full px-4 md:px-6 2xl:px-10 max-w-[1536px] mx-auto min-h-20 gap-3">
            <!-- Brand -->
            <div class="flex items-center space-x-3 2xl:space-x-6 shrink-0">
                <a href="<?= ($currentUser['role'] ?? '') === 'kitchen' ? '/kitchen' : ((($currentUser['role'] ?? '') === 'admin') ? '/admin/dashboard' : '/staff/pos') ?>" class="flex items-baseline space-x-3 text-decoration-none">
                    <span class="text-2xl lg:text-3xl font-headline font-bold text-primary tracking-tight">L'Épicure</span>
                    <span class="text-[11px] font-bold text-tertiary-container tracking-widest border-l border-outline-variant/50 pl-3 uppercase"><?= ($currentUser['role'] ?? '') === 'kitchen' ? 'Nhà bếp' : 'Phục vụ bàn' ?></span>
                </a>
                <div class="hidden lg:flex items-center bg-surface-container-low px-3.5 py-1.5 rounded-full border border-outline-variant/30 space-x-2">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                    <span class="text-xs font-semibold text-secondary uppercase tracking-wider">KDS Real-time</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <?php if (($currentUser['role'] ?? '') === 'staff'): ?>
            <nav class="hidden 2xl:flex items-center space-x-3 text-base font-semibold text-on-surface-variant whitespace-nowrap shrink-0">
                <a href="/tables" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/tables') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">table_restaurant</span>
                    <span>Sơ đồ bàn</span>
                </a>
                <a href="/staff/pos" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/staff/pos') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">room_service</span>
                    <span>Phục vụ tại bàn</span>
                </a>
                <a href="/staff/orders" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/staff/orders') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    <span>Quản lý gọi món</span>
                </a>
                <?php if (($currentUser['role'] ?? '') === 'kitchen'): ?>
                <a href="/kitchen" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/kitchen') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">soup_kitchen</span>
                    <span>Màn hình bếp</span>
                </a>
                <?php endif; ?>
                <a href="/invoices" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/invoices') && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/invoices') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">receipt</span>
                    <span>Hóa đơn</span>
                </a>
            </nav>
            <?php endif; ?>

            <!-- Staff Shift & Status -->
            <div class="flex items-center space-x-3 2xl:space-x-4 shrink-0">
                <div class="text-right hidden sm:block">
                    <div class="flex items-center justify-end space-x-1.5">
                        <span class="text-xs font-semibold text-on-surface"><?= \App\Core\View::escape($currentUser['name'] ?? 'Nhân viên phục vụ') ?></span>
                        <span class="text-[10px] font-bold uppercase bg-tertiary-fixed text-on-tertiary-fixed px-2 py-0.5 rounded-full">
                            <?= ($currentUser['role'] ?? '') === 'admin' ? 'Quản trị viên' : ((($currentUser['role'] ?? '') === 'kitchen') ? 'Nhân viên bếp' : 'Phục vụ bàn') ?>
                        </span>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">ID: #ST-<?= sprintf('%04d', $currentUser['id'] ?? 1) ?></p>
                </div>

                <div class="flex items-center space-x-2">
                    <?php if (($currentUser['role'] ?? '') === 'staff'): ?>
                    <a href="/account/notifications" class="relative p-2 rounded-xl border border-outline-variant/40 text-on-surface-variant hover:text-primary hover:border-primary transition" title="Thông báo hệ thống">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span id="staffNotifBadge" class="hidden absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-red-600 text-white text-[9px] font-bold flex items-center justify-center">0</span>
                    </a>
                    <?php endif; ?>
                    <?php if (($currentUser['role'] ?? '') === 'staff'): ?>
                    <a href="/staff/pos" class="bg-primary text-on-primary text-xs font-bold px-3.5 py-2 rounded-xl hover:bg-primary-container transition flex items-center space-x-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        <span class="hidden md:inline">Mở bàn mới</span>
                    </a>
                    <?php endif; ?>
                    <form action="/logout" method="post" class="inline m-0">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Core\View::escape(\App\Core\Csrf::token()) ?>">
                        <button type="submit" class="p-2 rounded-xl border border-outline-variant/40 text-on-surface-variant hover:text-primary hover:border-primary transition" title="Đăng xuất">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex flex-col">
        <?= $content ?>
    </div>

    <!-- IMMUTABLE BRAND FOOTER -->
    <footer class="bg-inverse-surface mt-12 border-t border-outline-variant/20">
        <div class="w-full max-w-7xl mx-auto px-6 md:px-12 py-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-surface-container-highest">
            <div class="flex items-center space-x-3">
                <span class="font-headline text-lg font-semibold text-inverse-on-surface">L'Épicure Gastronomie</span>
                <span>•</span>
                <span>Hệ thống Quản lý Vận hành Bàn & Gọi món POS</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Ca trực hiện hành: <?= date('d/m/Y') ?></span>
                <span class="text-secondary font-semibold">● Hệ thống hoạt động bình thường</span>
            </div>
        </div>
    </footer>

    <?php if (($currentUser['role'] ?? '') === 'staff'): ?>
    <script>
    (function() {
        function checkStaffNotifs() {
            fetch('/api/notifications/poll')
                .then(r => r.json())
                .then(d => {
                    const badge = document.getElementById('staffNotifBadge');
                    if (badge && d && d.unread_count > 0) {
                        badge.textContent = d.unread_count > 99 ? '99+' : d.unread_count;
                        badge.classList.remove('hidden');
                    } else if (badge) {
                        badge.classList.add('hidden');
                    }
                })
                .catch(() => {});
        }
        checkStaffNotifs();
        setInterval(checkStaffNotifs, 8000);
    })();
    </script>
    <?php endif; ?>
</body>
</html>
