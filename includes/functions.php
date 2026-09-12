<?php
/**
 * WebHub.uz - Core Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get database connection
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        require_once __DIR__ . '/../config/config.php';
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token
 */
function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect with message
 */
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    $message = $_SESSION['flash_message'] ?? null;
    $type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return ['message' => $message, 'type' => $type];
}

/**
 * Check if user is logged in (Google OAuth)
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Get current admin
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Require admin authentication
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin/login.php');
    }
}

/**
 * Require user authentication
 */
function requireUser() {
    if (!isLoggedIn()) {
        redirect('/user/login.php');
    }
}

/**
 * Get site setting by key
 */
function getSetting($key, $default = '') {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Update site setting
 */
function updateSetting($key, $value) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all services
 */
function getServices($limit = null) {
    try {
        $pdo = getDB();
        $sql = "SELECT * FROM services ORDER BY sort_order ASC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get service by ID
 */
function getServiceById($id) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get portfolio items
 */
function getPortfolio($limit = null) {
    try {
        $pdo = getDB();
        $sql = "SELECT * FROM portfolio ORDER BY sort_order ASC, created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get blog posts
 */
function getBlogPosts($limit = null, $status = 'published') {
    try {
        $pdo = getDB();
        $sql = "SELECT * FROM blog_posts WHERE status = ? ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get homepage stats
 */
function getHomepageStats() {
    try {
        $pdo = getDB();
        $stats = [];
        
        // Get stored stats
        $stmt = $pdo->query("SELECT stat_key, stat_value, auto_compute FROM homepage_stats");
        while ($row = $stmt->fetch()) {
            if ($row['auto_compute']) {
                // Compute from database
                switch ($row['stat_key']) {
                    case 'projects_count':
                        $countStmt = $pdo->query("SELECT COUNT(*) FROM portfolio");
                        $stats[$row['stat_key']] = $countStmt->fetchColumn();
                        break;
                    case 'clients_count':
                        $countStmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM applications WHERE user_id IS NOT NULL");
                        $stats[$row['stat_key']] = $countStmt->fetchColumn();
                        break;
                    default:
                        $stats[$row['stat_key']] = $row['stat_value'];
                }
            } else {
                $stats[$row['stat_key']] = $row['stat_value'];
            }
        }
        
        return $stats;
    } catch (PDOException $e) {
        return ['projects_count' => 0, 'clients_count' => 0];
    }
}

/**
 * Create application
 */
function createApplication($data) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO applications (user_id, service_id, name, phone, description, status)
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['service_id'] ?? null,
            $data['name'] ?? '',
            $data['phone'] ?? '',
            $data['description'] ?? ''
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get or create chat thread for user
 */
function getOrCreateChatThread($userId) {
    try {
        $pdo = getDB();
        
        // Check existing thread
        $stmt = $pdo->prepare("SELECT * FROM chat_threads WHERE user_id = ?");
        $stmt->execute([$userId]);
        $thread = $stmt->fetch();
        
        if (!$thread) {
            // Create new thread
            $stmt = $pdo->prepare("INSERT INTO chat_threads (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            
            $threadId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM chat_threads WHERE id = ?");
            $stmt->execute([$threadId]);
            $thread = $stmt->fetch();
        }
        
        return $thread;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Send chat message
 */
function sendChatMessage($threadId, $senderType, $senderId, $message, $filePath = null) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (thread_id, sender_type, sender_id, message, file_path)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$threadId, $senderType, $senderId, $message, $filePath]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get chat messages
 */
function getChatMessages($threadId, $limit = 50) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM chat_messages 
            WHERE thread_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$threadId, (int)$limit]);
        return array_reverse($stmt->fetchAll());
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Create notification
 */
function createNotification($userId, $title, $message) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $message]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 20) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, (int)$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Mark notification as read
 */
function markNotificationRead($notificationId) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
        $stmt->execute([$notificationId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Log admin action
 */
function logAdminAction($adminId, $action, $details = '') {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO audit_log (admin_id, action, details, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$adminId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $maxSize = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Fayl yuklashda xatolik'];
    }
    
    $maxSize = $maxSize ?? defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10485760;
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'Fayl hajmi juda katta'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri fayl turi'];
    }
    
    return ['valid' => true];
}

/**
 * Upload file securely
 */
function uploadFile($file, $subDir = 'images') {
    $validation = validateFileUpload($file);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }
    
    $uploadDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../uploads';
    $targetDir = $uploadDir . '/' . $subDir;
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($extension);
    $targetPath = $targetDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => '/uploads/' . $subDir . '/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Faylni saqlashda xatolik'];
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Rate limiting
 */
function checkRateLimit($identifier, $maxRequests = 60, $windowSeconds = 60) {
    $key = 'rate_limit_' . md5($identifier);
    $now = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    
    if ($now > $_SESSION[$key]['reset']) {
        $_SESSION[$key] = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    
    $_SESSION[$key]['count']++;
    
    if ($_SESSION[$key]['count'] > $maxRequests) {
        return false;
    }
    
    return true;
}
