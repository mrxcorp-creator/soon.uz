<?php
/**
 * API: Submit Application
 * Handles contact form submissions
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Noto\'g\'ri so\'rov usuli'], 405);
}

// Rate limiting
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($clientIp . '_application', 5, 300)) {
    jsonResponse(['success' => false, 'error' => 'Juda ko\'p urinishlar. Iltimos, 5 daqiqa kuting.'], 429);
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    jsonResponse(['success' => false, 'error' => 'Xavfsizlik tekshiruvi muvaffaqiyatsiz'], 403);
}

// Get and validate input
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$serviceId = (int)($_POST['service_id'] ?? $_POST['service_type'] ?? 0);
$message = trim($_POST['message'] ?? '');

$errors = [];

if (empty($name)) {
    $errors[] = 'Ismni kiriting';
}

if (empty($phone)) {
    $errors[] = 'Telefon raqamni kiriting';
} elseif (!preg_match('/^\+?[0-9\s\-()]{10,20}$/', $phone)) {
    $errors[] = 'Noto\'g\'ri telefon raqam';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'error' => implode('. ', $errors)], 400);
}

try {
    $pdo = getDB();
    
    // Check if user is logged in
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    // Create application
    $stmt = $pdo->prepare("
        INSERT INTO applications (user_id, service_id, name, phone, description, status)
        VALUES (?, ?, ?, ?, ?, 'new')
    ");
    $stmt->execute([$userId, $serviceId ?: null, $name, $phone, $message]);
    
    $applicationId = $pdo->lastInsertId();
    
    // If user is logged in, create notification
    if ($userId) {
        createNotification($userId, 'Ariza qabul qilindi', 'Sizning arizangiz qabul qilindi. Tez orada menejerimiz siz bilan bog\'lanadi.');
    }
    
    // Log the action
    logAdminAction(null, 'New application submitted', "Application ID: {$applicationId}, Name: {$name}, Phone: {$phone}");
    
    jsonResponse([
        'success' => true,
        'message' => 'Arizangiz qabul qilindi! Menejerimiz tez orada siz bilan bog\'lanadi.',
        'application_id' => $applicationId
    ]);
    
} catch (PDOException $e) {
    error_log('Application submit error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Serverda xatolik yuz berdi. Iltimos, qayta urinib ko\'ring.'], 500);
}
