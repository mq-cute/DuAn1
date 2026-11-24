<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= \App\Core\View::escape($pageTitle ?? "FOODIES Haute Cuisine — Hệ Thống Quản Trị Trung Tâm") ?></title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                "admin-navy": "#121820",
                "admin-sidebar": "#16202c",
                "admin-hover": "#1e2b3a",
                "gold": "#c5a059",
                "gold-light": "#e6ca85",
                "gold-dark": "#9a7632",
                "primary": "#870000",
                "primary-action": "#af140b",
                "surface": "#f8f9fa",
                "surface-card": "#ffffff",
                "line": "#e2e8f0",
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
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 22;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: rgba(226, 232, 240, 0.4);
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.5);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#1e293b] font-body antialiased min-h-screen flex selection:bg-gold selection:text-admin-navy">
    
    <!-- ================= SIDEBAR (FOODIES ADMIN) ================= -->
    <aside class="w-64 bg-admin-sidebar text-slate-300 flex flex-col shrink-0 min-h-screen border-r border-slate-800 select-none sticky top-0 h-screen overflow-y-auto custom-scroll">
        <!-- Logo / Brand Header -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <a href="/admin/dashboard" class="flex items-center gap-3 text-decoration-none">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-gold to-gold-dark text-admin-navy flex items-center justify-center font-bold text-lg shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">restaurant</span>
                </div>
                <div>
                    <span class="font-headline font-bold text-white tracking-wider text-base block leading-tight">FOODIES</span>
                    <span class="text-[9px] text-gold tracking-[0.2em] font-semibold block uppercase">Haute Cuisine</span>
                </div>
            </a>
            <span class="text-[9px] bg-slate-800 text-gold px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">ADMIN</span>
        </div>

        <!-- Navigation Menu Groups -->
        <div class="flex-1 py-4 px-3 space-y-6">
            
            <!-- NHÓM 1: TỔNG QUAN -->
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">TỔNG QUAN</span>
                <nav class="space-y-1">
                    <a href="/admin/dashboard" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">dashboard</span>
                        <span>Tổng quan</span>
                    </a>
                </nav>
            </div>

            <!-- NHÓM 2: VẬN HÀNH & ĐẶT CHỖ -->
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">VẬN HÀNH & ĐẶT CHỖ</span>
                <nav class="space-y-1">
                    <a href="/admin/bookings" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/bookings') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">event_seat</span>
                        <span>Quản lý Đặt bàn</span>
                    </a>
                    <a href="/admin/tables" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/tables') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">table_restaurant</span>
                        <span>Sơ đồ Bàn ăn</span>
                    </a>
                    <a href="/admin/setups" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/setups') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">celebration</span>
                        <span>Yêu cầu Setup riêng</span>
                    </a>
                </nav>
            </div>

            <!-- NHÓM 3: THỰC ĐƠN & BẾP -->
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">THỰC ĐƠN & BẾP</span>
                <nav class="space-y-1">
                    <a href="/admin/menu" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/menu') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">menu_book</span>
                        <span>Quản lý Món ăn</span>
                    </a>
                    <a href="/admin/combos" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/combos') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">local_offer</span>
                        <span>Combo & Khuyến mãi</span>
                    </a>
                </nav>
            </div>

            <!-- NHÓM 4: BÁN HÀNG & TÀI CHÍNH -->
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">BÁN HÀNG & TÀI CHÍNH</span>
                <nav class="space-y-1">
                    <a href="/admin/invoices" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/invoices') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">receipt_long</span>
                        <span>Đơn hàng & Hóa đơn</span>
                    </a>
                    <a href="/admin/reports" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/reports') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">monitoring</span>
                        <span>Doanh thu & Báo cáo</span>
                    </a>
                </nav>
            </div>

            <!-- NHÓM 5: HỆ THỐNG -->
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">HỆ THỐNG</span>
                <nav class="space-y-1">
                    <a href="/admin/users" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">manage_accounts</span>
                        <span>Nhân viên & Phân quyền</span>
                    </a>
                    <a href="/admin/restaurant" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/restaurant') ? 'bg-gold text-admin-navy font-bold shadow-sm' : 'hover:bg-admin-hover hover:text-white text-slate-300' ?>">
                        <span class="material-symbols-outlined text-[19px]">tune</span>
                        <span>Cấu hình Nhà hàng</span>
                    </a>
                </nav>
            </div>

        </div>

        <!-- System Version Info Footer -->
        <div class="p-3 border-t border-slate-800 text-[11px] text-slate-500 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Hệ thống sẵn sàng</span>
            </span>
            <span class="font-mono text-slate-600">v2.4</span>
        </div>
    </aside>

    <!-- ================= MAIN CONTENT WRAPPER ================= -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        
        <!-- TOP NAVBAR -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between gap-4 sticky top-0 z-40 shadow-sm">
            <!-- Left Info Pills -->
            <div class="flex items-center gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800">Foodies</span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-slate-500">Chi nhánh Lê Duẩn, Q.1</span>
                </div>
                <div class="h-4 w-px bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">Đồng bộ thời gian thực (KDS & Quầy)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
            </div>

            <!-- Right Controls: Search, Notifications, User -->
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="relative hidden md:block w-64">
                    <input type="text" placeholder="Tìm mã bàn, SĐT, số đơn..." 
                           class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold text-slate-700">
                    <span class="material-symbols-outlined text-[18px] text-slate-400 absolute left-2.5 top-2">search</span>
                </div>

                <!-- Notifications Button -->
                <a href="/account/notifications" class="relative p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-red-600 text-white text-[9px] font-bold flex items-center justify-center">5</span>
                </a>

                <!-- User Profile Dropdown -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-admin-navy text-gold flex items-center justify-center font-bold text-xs">
                        <?= mb_substr(\App\Core\View::escape($currentUser['name'] ?? 'Admin'), 0, 1) ?>
                    </div>
                    <div class="text-left hidden sm:block">
                        <span class="font-bold text-xs text-slate-800 block leading-tight"><?= \App\Core\View::escape($currentUser['name'] ?? 'Vũ Hoàng Quân') ?></span>
                        <span class="text-[10px] text-slate-400 block flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                            <span>Đang trực tuyến</span>
                        </span>
                    </div>
                    <form action="/logout" method="post" class="ml-2 inline">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Core\View::escape($csrfToken ?? \App\Core\Csrf::token()) ?>">
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 transition" title="Đăng xuất">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 p-6 lg:p-8">
            <?= $content ?>
        </main>

    </div>

</body>
</html>
