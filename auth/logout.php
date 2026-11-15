<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/app_path.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the Log out button to end your session.');
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request. Refresh the page and try again.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

session_destroy();

header('Location: ' . APP_BASE_PATH . '/auth/login.php');
exit;
