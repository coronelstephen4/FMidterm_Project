<?php
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$applicationRoot = realpath(dirname(__DIR__));
$appBasePath = '';

if ($documentRoot !== false && $applicationRoot !== false) {
    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
    $applicationRoot = rtrim(str_replace('\\', '/', $applicationRoot), '/');
    if ($documentRoot === '') {
        $documentRoot = '/';
    }

    if (strcasecmp($applicationRoot, $documentRoot) === 0) {
        $appBasePath = '';
    } elseif (stripos($applicationRoot, rtrim($documentRoot, '/') . '/') === 0) {
        $appBasePath = '/' . trim(substr($applicationRoot, strlen($documentRoot)), '/');
    }
}

if ($appBasePath === '') {
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptParts = explode('/', trim($scriptName, '/'));
    $rootFolders = ['auth', 'farmers', 'farms', 'crops', 'plantings', 'harvests', 'reports', 'includes'];

    if ($scriptParts && !in_array($scriptParts[0], $rootFolders, true) && count($scriptParts) > 1) {
        $appBasePath = '/' . $scriptParts[0];
    }
}

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', $appBasePath);
}
