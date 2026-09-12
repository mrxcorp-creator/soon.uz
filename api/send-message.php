<?php
/**
 * API: Send Chat Message
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Noto\'g\'ri so\'rov usuli'], 405);
}

// Require user authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Kirish talab etiladi'], 401);
}

// Rate limiting
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($clientIp . '_chat_' . $_SESSION['user_id'], 30, 60)) {
    jsonResponse(['success' => false, 'error' => 'Juda ko\'p xabarlar. Iltimos, kutib turing.'], 429);
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    jsonResponse(['success' => false, 'error' => 'Xavfsizlik tekshiruvi muvaffaqiyatsiz'], 403);
}

// Get and validate input
$threadId = (int)($_POST['thread_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($threadId <= 0) {
    jsonResponse(['success' => false, 'error' => 'Thread ID noto\'g\'ri'], 400);
}

if (empty($message)) {
    jsonResponse(['success' => false, 'error' => 'Xabar matni bo\'sh'], 400);
}

try {
    $pdo = getDB();
    
    // Verify thread belongs to user
    $stmt = $pdo->prepare("SELECT * FROM chat_threads WHERE id = ? AND user_id = ?");
    $stmt->execute([$threadId, $_SESSION['user_id']]);
    $thread = $stmt->fetch();
    
    if (!$thread) {
        jsonResponse(['success' => false, 'error' => 'Chat topilmadi'], 404);
    }
    
    // Handle file upload if present
    $filePath = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['file'], 'documents');
        if ($uploadResult['success']) {
            $filePath = $uploadResult['path'];
        }
    }
    
    // Send message
    $messageId = sendChatMessage($threadId, 'user', $_SESSION['user_id'], $message, $filePath);
    
    // Create notification for admin
    $admins = $pdo->query("SELECT id FROM admins")->fetchAll();
    foreach ($admins as $admin) {
        createNotification($admin['id'], 'Yangi xabar', 'Foydalanuvchidan yangi xabar keldi');
    }
    
    jsonResponse([
        'success' => true,
        'message_id' => $messageId
    ]);
    
} catch (PDOException $e) {
    error_log('Send message error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Serverda xatolik yuz berdi'], 500);
}
