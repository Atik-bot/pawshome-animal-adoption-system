<?php
// session already started in db.php; guard against double-start if header is
// somehow included without db.php (e.g. during direct testing)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title).' — PawsHome' : 'PawsHome — Find Your New Best Friend' ?></title>
    <meta name="description" content="PawsHome — Find and adopt animals in need. Browse dogs, cats, birds and more available for adoption.">

    <!-- Anti-flash: apply saved theme before any rendering occurs -->
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter','ui-sans-serif','system-ui'] },
                    colors: {
                        brand: { 50:'#fff7ed',100:'#ffedd5',200:'#fed7aa',400:'#fb923c',500:'#f97316',600:'#ea580c',700:'#c2410c' }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .card-hover { transition: transform .22s ease, box-shadow .22s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,.10); }
        .gradient-hero { background: linear-gradient(135deg,#ff6b35 0%,#f7931e 55%,#ffcd3c 100%); }
        .gradient-side { background: linear-gradient(160deg,#ff6b35 0%,#f97316 45%,#fb923c 100%); }
        .paw-bg { background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Ccircle cx='10' cy='10' r='4'/%3E%3Ccircle cx='20' cy='7' r='3'/%3E%3Ccircle cx='28' cy='10' r='3'/%3E%3Cellipse cx='19' cy='20' rx='7' ry='9'/%3E%3C/g%3E%3C/svg%3E"); }
        .gradient-hero.paw-bg { background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Ccircle cx='10' cy='10' r='4'/%3E%3Ccircle cx='20' cy='7' r='3'/%3E%3Ccircle cx='28' cy='10' r='3'/%3E%3Cellipse cx='19' cy='20' rx='7' ry='9'/%3E%3C/g%3E%3C/svg%3E"), linear-gradient(135deg,#ff6b35 0%,#f7931e 55%,#ffcd3c 100%); }
        .gradient-side.paw-bg { background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Ccircle cx='10' cy='10' r='4'/%3E%3Ccircle cx='20' cy='7' r='3'/%3E%3Ccircle cx='28' cy='10' r='3'/%3E%3Cellipse cx='19' cy='20' rx='7' ry='9'/%3E%3C/g%3E%3C/svg%3E"), linear-gradient(160deg,#ff6b35 0%,#f97316 45%,#fb923c 100%); }
        select { -webkit-appearance:none; appearance:none; }
        .line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .animate-fade-up { animation: fadeUp .45s ease both; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-200">

<!-- ===== NAVBAR ===== -->
<nav class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="/index.php" class="flex items-center gap-2 group">
                <div class="w-9 h-9 gradient-hero rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <span class="text-lg">🐾</span>
                </div>
                <span class="text-lg font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">Paws<span class="text-brand-500">Home</span></span>
            </a>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-1">
                <a href="/index.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-gray-800 transition-all">
                    <i class="fas fa-home mr-1.5 opacity-70"></i>Home
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/user/dashboard.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-gray-800 transition-all">
                        <i class="fas fa-th-large mr-1.5 opacity-70"></i>Dashboard
                    </a>
                    <a href="/user/add_pet.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-gray-800 transition-all">
                        <i class="fas fa-plus-circle mr-1.5 opacity-70"></i>List a Pet
                    </a>
                    <!-- Avatar dropdown -->
                    <div class="relative group ml-2">
                        <button class="flex items-center gap-2 pl-2 pr-3 py-1.5 bg-brand-50 dark:bg-gray-800 border border-brand-200 dark:border-gray-700 text-brand-600 dark:text-brand-400 rounded-full text-sm font-semibold hover:bg-brand-100 dark:hover:bg-gray-700 transition-all">
                            <div class="w-6 h-6 gradient-hero text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">
                                <?= strtoupper(substr(htmlspecialchars($_SESSION['user_name']),0,1)) ?>
                            </div>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                            <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                        </button>
                        <div class="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 translate-y-1 group-hover:translate-y-0">
                            <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700">
                                <p class="text-[11px] text-gray-400 uppercase tracking-wide">Signed in as</p>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            </div>
                            <a href="/user/my_requests.php" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-gray-700 hover:text-brand-500 transition-all">
                                <i class="fas fa-heart w-4 text-brand-400 text-xs"></i>My Requests
                            </a>
                            <a href="/auth/logout.php" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-b-2xl transition-all">
                                <i class="fas fa-sign-out-alt w-4 text-xs"></i>Logout
                            </a>
                        </div>
                    </div>
                <?php elseif (isset($_SESSION['admin_id'])): ?>
                    <a href="/admin/dashboard.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-gray-800 transition-all">
                        <i class="fas fa-shield-alt mr-1.5 opacity-70"></i>Admin Panel
                    </a>
                    <a href="/admin/logout.php" class="px-3 py-2 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all">
                        <i class="fas fa-sign-out-alt mr-1.5"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="/auth/register.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-gray-800 transition-all">
                        <i class="fas fa-user-plus mr-1.5 opacity-70"></i>Register
                    </a>
                    <a href="/auth/login.php" class="ml-1 gradient-hero text-white px-5 py-2 rounded-full text-sm font-bold shadow-sm hover:shadow-md transition-all active:scale-95">
                        Login
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right side: theme toggle + hamburger -->
            <div class="flex items-center gap-1">
                <button id="theme-toggle"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        title="Toggle dark mode">
                    <i class="fas fa-moon dark:hidden text-base"></i>
                    <i class="fas fa-sun hidden dark:block text-base text-yellow-400"></i>
                </button>
                <button id="mob-btn" class="md:hidden p-2 text-gray-500 dark:text-gray-400 hover:text-brand-500 rounded-lg transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mob-menu" class="hidden md:hidden border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3 space-y-1">
        <a href="/index.php" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-gray-800 hover:text-brand-500 transition-all">
            <i class="fas fa-home w-5 text-brand-400"></i>Home
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/user/dashboard.php"   class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-gray-800 hover:text-brand-500 transition-all"><i class="fas fa-th-large w-5 text-brand-400"></i>Dashboard</a>
            <a href="/user/add_pet.php"     class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-gray-800 hover:text-brand-500 transition-all"><i class="fas fa-plus-circle w-5 text-brand-400"></i>List a Pet</a>
            <a href="/user/my_requests.php" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-gray-800 hover:text-brand-500 transition-all"><i class="fas fa-heart w-5 text-brand-400"></i>My Requests</a>
            <a href="/auth/logout.php"      class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all"><i class="fas fa-sign-out-alt w-5"></i>Logout</a>
        <?php elseif (isset($_SESSION['admin_id'])): ?>
            <a href="/admin/dashboard.php"  class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-gray-800 hover:text-brand-500 transition-all"><i class="fas fa-shield-alt w-5 text-brand-400"></i>Admin Panel</a>
            <a href="/admin/logout.php"     class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all"><i class="fas fa-sign-out-alt w-5"></i>Logout</a>
        <?php else: ?>
            <a href="/auth/register.php" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-gray-800 hover:text-brand-500 transition-all"><i class="fas fa-user-plus w-5 text-brand-400"></i>Register</a>
            <a href="/auth/login.php"    class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-bold gradient-hero text-white transition-all"><i class="fas fa-sign-in-alt w-5"></i>Login</a>
        <?php endif; ?>
    </div>
</nav>
<script>
document.getElementById('mob-btn').addEventListener('click',()=>{
    document.getElementById('mob-menu').classList.toggle('hidden');
});
document.getElementById('theme-toggle').addEventListener('click', function(){
    var html = document.documentElement;
    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
});
</script>
<main class="flex-1">
