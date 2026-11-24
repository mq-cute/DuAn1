<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= \App\Core\View::escape($pageTitle ?? "L'Épicure Gastronomie — Quản lý Vận hành & Phục vụ") ?></title>
    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                "primary": "#870000",
                "primary-container": "#af140b",
                "primary-fixed": "#ffdad4",
                "on-primary": "#ffffff",
                "secondary": "#4a6633",
                "secondary-container": "#c8eaa9",
                "on-secondary": "#ffffff",
                "background": "#fff8f5",
                "surface": "#fff8f5",
                "surface-container-lowest": "#ffffff",
                "surface-container-low": "#fff1e8",
                "surface-container": "#feeadc",
                "surface-container-high": "#f8e5d6",
                "surface-container-highest": "#f2dfd1",
                "on-surface": "#231a11",
                "on-surface-variant": "#5b403c",
                "outline": "#906f6a",
                "outline-variant": "#e4beb8",
                "tertiary": "#633500",
                "tertiary-container": "#844800",
                "tertiary-fixed": "#ffdcc1",
                "on-tertiary-fixed": "#2e1500",
                "error": "#ba1a1a",
                "error-container": "#ffdad6",
                "inverse-surface": "#392e25",
                "inverse-on-surface": "#ffeee1",
              },
              fontFamily: {
                "headline": ["Playfair Display", "Georgia", "serif"],
                "body": ["Plus Jakarta Sans", "Arial", "sans-serif"],
              }
            }
          }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: rgba(242, 223, 209, 0.4);
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(144, 111, 106, 0.35);
            border-radius: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(135, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body antialiased min-h-screen flex flex-col selection:bg-primary selection:text-on-primary">
    <!-- TOP APP BAR -->
    <header class="bg-surface/95 backdrop-blur-md sticky top-0 z-50 border-b border-outline-variant/30 shadow-[0_2px_8px_-2px_rgba(43,33,24,0.06)]">
        <div class="flex justify-between items-center w-full px-6 md:px-10 max-w-[1536px] mx-auto h-20">
            <!-- Brand -->
            <div class="flex items-center space-x-6">
                <a href="/staff/pos" class="flex items-baseline space-x-3 text-decoration-none">
                    <span class="text-2xl lg:text-3xl font-headline font-bold text-primary tracking-tight">L'Épicure</span>
                    <span class="text-[11px] font-bold text-tertiary-container tracking-widest border-l border-outline-variant/50 pl-3 uppercase">Phục vụ bàn</span>
                </a>
                <div class="hidden lg:flex items-center bg-surface-container-low px-3.5 py-1.5 rounded-full border border-outline-variant/30 space-x-2">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                    <span class="text-xs font-semibold text-secondary uppercase tracking-wider">KDS Real-time</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold text-on-surface-variant">
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
                <a href="/kitchen" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/kitchen') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">soup_kitchen</span>
                    <span>Màn hình bếp</span>
                </a>
                <a href="/invoices" class="hover:text-primary transition-colors flex items-center gap-1.5 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/invoices') && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/invoices') ? 'text-primary border-b-2 border-primary pb-0.5' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">receipt</span>
                    <span>Hóa đơn</span>
                </a>
            </nav>

            <!-- Staff Shift & Status -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="flex items-center justify-end space-x-1.5">
                        <span class="text-xs font-semibold text-on-surface"><?= \App\Core\View::escape($currentUser['name'] ?? 'Nhân viên phục vụ') ?></span>
                        <span class="text-[10px] font-bold uppercase bg-tertiary-fixed text-on-tertiary-fixed px-2 py-0.5 rounded-full">
                            <?= ($currentUser['role'] ?? '') === 'admin' ? 'Quản trị viên' : 'Phục vụ bàn' ?>
                        </span>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">ID: #ST-<?= sprintf('%04d', $currentUser['id'] ?? 1) ?></p>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="/staff/pos" class="bg-primary text-on-primary text-xs font-bold px-3.5 py-2 rounded-xl hover:bg-primary-container transition flex items-center space-x-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        <span class="hidden md:inline">Mở bàn mới</span>
                    </a>
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

    <main class="flex-1 flex flex-col">
        <?= $content ?>
    </main>

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
</body>
</html>
