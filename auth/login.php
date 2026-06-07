<?php
include("../config/db.php");
// session already started in db.php

if (isset($_SESSION['user_id'])) {
    header("Location: /user/dashboard.php"); exit;
}
if (isset($_SESSION['admin_id'])) {
    header("Location: /admin/dashboard.php"); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Note: CSRF not enforced on login — InfinityFree's browser security system
    // reloads the page via JS which invalidates session tokens before submission.

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role']          ?? 'user';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        if ($role === 'admin') {
            $stmt = mysqli_prepare($conn, "SELECT AdminID, AdminName, Password FROM Admin WHERE Email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($row && password_verify($password, $row['Password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']   = $row['AdminID'];
                $_SESSION['admin_name'] = $row['AdminName'];
                header("Location: /admin/dashboard.php"); exit;
            } else {
                $error = 'Invalid admin credentials.';
            }
        } else {
            $stmt = mysqli_prepare($conn, "SELECT UserID, UserName, Password FROM Users WHERE Email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($row && password_verify($password, $row['Password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $row['UserID'];
                $_SESSION['user_name'] = $row['UserName'];
                header("Location: /user/dashboard.php"); exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

$page_title = 'Login';
include("../includes/header.php");
?>

<!-- Banner -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-center">
        <div class="text-5xl mb-3">🐾</div>
        <h1 class="text-3xl font-extrabold text-white">Welcome Back</h1>
        <p class="text-white/75 text-sm mt-1">Sign in to continue to PawsHome</p>
    </div>
</div>

<div class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 animate-fade-up">

        <?php if ($error): ?>
        <div class="rounded-2xl p-4 mb-5 text-sm flex items-center gap-3 bg-red-50 border border-red-200 text-red-700">
            <i class="fas fa-exclamation-circle text-xl flex-shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Role selector -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Login as</label>
                <div class="flex gap-3">
                    <label class="flex-1 flex items-center justify-center gap-2 border-2 border-gray-200 dark:border-gray-600 rounded-xl py-2.5 text-sm font-medium cursor-pointer has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-all">
                        <input type="radio" name="role" value="user" class="sr-only" checked>
                        <i class="fas fa-user text-brand-400"></i> User
                    </label>
                    <label class="flex-1 flex items-center justify-center gap-2 border-2 border-gray-200 dark:border-gray-600 rounded-xl py-2.5 text-sm font-medium cursor-pointer has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-all">
                        <input type="radio" name="role" value="admin" class="sr-only">
                        <i class="fas fa-shield-alt text-brand-400"></i> Admin
                    </label>
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Email</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="you@example.com"
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
                </div>
            </div>

            <button type="submit"
                    class="w-full gradient-hero text-white font-bold py-3 rounded-xl text-sm shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400 dark:text-gray-500">
            Don't have an account?
            <a href="/auth/register.php" class="text-brand-500 font-semibold hover:underline">Register here</a>
        </p>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
