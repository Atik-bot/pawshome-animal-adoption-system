<?php
include("../config/db.php");
// session already started in db.php

if (isset($_SESSION['user_id'])) {
    header("Location: /user/dashboard.php"); exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Note: CSRF not enforced on register — same InfinityFree JS reload issue.

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $chk = mysqli_prepare($conn, "SELECT UserID FROM Users WHERE Email = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, "s", $email);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);

        if (mysqli_stmt_num_rows($chk) > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = mysqli_prepare($conn,
                "INSERT INTO Users (UserName, Email, Password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($ins, "sss", $name, $email, $hash);

            if (mysqli_stmt_execute($ins)) {
                $success = 'Account created! You can now log in.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}

$page_title = 'Register';
include("../includes/header.php");
?>

<!-- Banner -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-center">
        <div class="text-5xl mb-3">🐾</div>
        <h1 class="text-3xl font-extrabold text-white">Create an Account</h1>
        <p class="text-white/75 text-sm mt-1">Join PawsHome and start your adoption journey</p>
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

        <?php if ($success): ?>
        <div class="rounded-2xl p-4 mb-5 text-sm flex items-center gap-3 bg-green-50 border border-green-200 text-green-700">
            <i class="fas fa-check-circle text-xl flex-shrink-0"></i>
            <span><?= htmlspecialchars($success) ?> <a href="/auth/login.php" class="font-bold underline">Login now →</a></span>
        </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Name -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Full Name</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Your name"
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
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
                           placeholder="Min. 6 characters"
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
                </div>
            </div>

            <!-- Confirm password -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Confirm Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="confirm" required
                           placeholder="Repeat password"
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
                </div>
            </div>

            <button type="submit"
                    class="w-full gradient-hero text-white font-bold py-3 rounded-xl text-sm shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400 dark:text-gray-500">
            Already have an account?
            <a href="/auth/login.php" class="text-brand-500 font-semibold hover:underline">Sign in</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
