<?php
// ============================================================
//  PawsHome — InfinityFree Database Configuration
// ============================================================

define('DB_HOST', 'sql102.infinityfree.com');
define('DB_USER', 'if0_42124879');
define('DB_PASS', 'lm10strikes');
define('DB_NAME', 'if0_42124879_pawshome');

// ── Session bootstrap ────────────────────────────────────────────────────────
// InfinityFree denies writes to /php_sessions — store sessions inside htdocs/tmp/
$session_dir = __DIR__ . '/../tmp/sessions';
if (!is_dir($session_dir)) {
    @mkdir($session_dir, 0755, true);
}
if (is_writable($session_dir)) {
    session_save_path($session_dir);
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Database connection ──────────────────────────────────────────────────────
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    error_log('DB connection failed: ' . mysqli_connect_error());
    die('<h2 style="font-family:sans-serif;color:#c0392b;padding:2rem">
         Database connection failed. Please try again later.
         </h2>');
}

mysqli_set_charset($conn, 'utf8mb4');

// ── CSRF helpers ─────────────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    $token_post    = $_POST['csrf_token']    ?? '';
    $token_session = $_SESSION['csrf_token'] ?? '';

    if (empty($token_session) || !hash_equals($token_session, $token_post)) {
        http_response_code(403);
        die('<h2 style="font-family:sans-serif;color:#c0392b;padding:2rem">
             403 — Invalid or missing CSRF token. Please go back and try again.
             </h2>');
    }
    // Rotate token after successful verification
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
