<?php
/**
 * API: Get Chat Messages
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require user authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Kirish talab etiladi'], 401);
}

// Get thread ID
$threadId = (int)($_GET['thread_id'] ?? 0);

if ($threadId <= 0) {
    jsonResponse(['success' => false, 'error' => 'Thread ID noto\'g\'ri'], 400);
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
    
    // Get messages
    $messages = getChatMessages($threadId, 100);
    
    // Mark messages as read
    $stmt = $pdo->prepare("UPDATE chat_messages SET is_read = TRUE WHERE thread_id = ? AND sender_type = 'admin'");
    $stmt->execute([$threadId]);
    
    jsonResponse($messages);
    
} catch (PDOException $e) {
    error_log('Get messages error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Serverda xatolik yuz berdi'], 500);
}
