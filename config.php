<?php
// Set timezone
date_default_timezone_set('Asia/Riyadh');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pointshift_pos');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/pharmacy');
define('SITE_NAME', 'نظام الصيدلية | Pharmacy System');

// RTL Support
define('RTL_ENABLED', true);
define('DEFAULT_LANG', 'ar');

// Session configuration
session_start();

// Autoload classes
spl_autoload_register(function ($className) {
    $directories = [
        'classes/',
        'controllers/',
        'helpers/'
    ];
    
    foreach ($directories as $dir) {
        $file = __DIR__ . '/' . $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper functions for backward compatibility
function isLoggedIn() {
    return User::isLoggedIn();
}

function isAdmin() {
    return User::isAdmin();
}

function requireLogin() {
    User::requireLogin();
}

function requireAdmin() {
    User::requireAdmin();
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function formatCurrency($amount) {
    return Layout::formatCurrency($amount);
}
?>
