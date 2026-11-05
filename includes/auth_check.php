<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header('Location: /FMidterm_Project/auth/login.php');
        exit;
    }
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin(): void {
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        exit('You do not have permission to perform this action.');
    }
}
