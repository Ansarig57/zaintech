<?php
// ===== EARNHUB ENVIRONMENT CONFIGURATION =====
// Copy this file to your server and update the values according to your setup

// ===== DATABASE CONFIGURATION =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'earnhub');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

// ===== SITE CONFIGURATION =====
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'Earnhub');
define('SITE_EMAIL', 'noreply@yourdomain.com');

// ===== ADMIN CONFIGURATION =====
// Admin login credentials (username: admin, password: 123ZAIN)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$8K1p/a0dLRZFnFpBImxOdOvs9S0gF1KjM2.Z2/8q9jK5qBGYvQOJG'); // 123ZAIN hashed

// ===== SECURITY CONFIGURATION =====
define('JWT_SECRET', 'your-super-secret-jwt-key-change-this-in-production');
define('SESSION_LIFETIME', 86400); // 24 hours
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// ===== EMAIL CONFIGURATION =====
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');

// ===== PAYMENT CONFIGURATION =====
define('POINTS_TO_PKR_RATE', 1000); // 1000 points = 1 PKR
define('MIN_WITHDRAWAL_PKR', 200);
define('WITHDRAWAL_FEE_PERCENT', 2); // 2% fee

// ===== GAME CONFIGURATION =====
define('SPIN_MIN_REWARD', 10);
define('SPIN_MAX_REWARD', 500);
define('SPIN_DAILY_LIMIT', 5);
define('WATCH_AD_REWARD', 5);
define('WATCH_AD_DURATION', 30);
define('WATCH_DAILY_LIMIT', 20);
define('DAILY_LOGIN_BONUS', 50);
define('STREAK_BONUS_DAYS', 7);
define('STREAK_BONUS_POINTS', 5000);

// ===== REFERRAL CONFIGURATION =====
define('REFERRAL_SIGNUP_BONUS', 100);
define('REFERRAL_COMMISSION_RATE', 10); // 10%

// ===== JACKPOT CONFIGURATION =====
define('JACKPOT_ENTRY_THRESHOLD', 1000); // Weekly points required
define('JACKPOT_PRIZE_AMOUNT', 10000); // PKR

// ===== FILE UPLOAD CONFIGURATION =====
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('AVATAR_UPLOAD_PATH', 'uploads/avatars/');

// ===== API CONFIGURATION =====
define('API_RATE_LIMIT', 100); // requests per minute
define('API_VERSION', 'v1');

// ===== DEVELOPMENT/PRODUCTION =====
define('ENVIRONMENT', 'production'); // 'development' or 'production'
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', 'logs/error.log');

// ===== TIMEZONE =====
date_default_timezone_set('Asia/Karachi');

// ===== ERROR REPORTING =====
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// ===== SESSION CONFIGURATION =====
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// ===== SECURITY HEADERS =====
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
?>