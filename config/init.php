<?php
/**
 * WebHub.uz - Dinamik Konfiguratsiya
 * Har qanday domenda ishlashi uchun mo'ljallangan
 */

// Xatoliklarni yashirish (Production mode)
error_reporting(0);
ini_set('display_errors', 0);

// Sessiyani xavfsiz boshlash
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Dinamik Base URL aniqlash (HTTP yoki HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

// BASE_URL ni dinamik ravishda aniqlash
$baseUrl = $protocol . $host . $scriptName;

// Oxirgi slashni olib tashlash
define('BASE_URL', rtrim($baseUrl, '/'));

// Fayl yo'llari
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('ASSETS_URL', BASE_URL . '/assets');

// Database ulanish (config/db.php mavjud bo'lsa)
$dbConfigFile = CONFIG_PATH . '/db.php';
$pdo = null;

if (file_exists($dbConfigFile)) {
    try {
        require_once $dbConfigFile;
        // Agar DB ma'lumotlari massivda bo'lsa
        if (isset($db_config) && is_array($db_config)) {
            $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }
    } catch (PDOException $e) {
        // Agar DB xatosi bo'lsa va install.php bo'lmasa, xatolik sahifasiga yo'naltirish
        if (!strpos($_SERVER['REQUEST_URI'], 'install.php')) {
            http_response_code(500);
            include ROOT_PATH . '/500.php';
            exit;
        }
    }
}

// Global funksiyalarni yuklash
if (file_exists(INCLUDES_PATH . '/functions.php')) {
    require_once INCLUDES_PATH . '/functions.php';
}

// CSRF Token yaratish agar yo'q bo'lsa
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
