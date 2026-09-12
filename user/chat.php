<?php
/**
 * User Chat Page - Real-time chat with admin
 */

require_once __DIR__ . '/../includes/functions.php';
requireUser();

$user = getCurrentUser();
$chatThread = getOrCreateChatThread($user['id']);

// Get messages
$messages = getChatMessages($chatThread['id'], 100);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - WebHub.uz</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body { padding-top: 80px; min-height: 100vh; display: flex; flex-direction: column; }
        .user-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 var(--space-6);
            z-index: var(--z-sticky);
        }
        .user-nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .nav-links {
            display: flex;
            gap: var(--space-6);
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-links a {
            color: var(--text-secondary);
            transition: color var(--transition-fast);
        }
        .nav-links a:hover, .nav-links a.active {
            color: var(--primary);
        }
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            padding: var(--space-6);
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: var(--space-4);
            margin-bottom: var(--space-4);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            min-height: 400px;
            max-height: 60vh;
        }
        .message {
            display: flex;
            margin-bottom: var(--space-4);
            gap: var(--space-3);
        }
        .message.user {
            flex-direction: row-reverse;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
        }
        .message.admin .message-avatar {
            background: var(--success);
        }
        .message-content {
            max-width: 70%;
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-xl);
            background: var(--bg-tertiary);
        }
        .message.user .message-content {
            background: var(--primary);
            color: white;
        }
        .message-meta {
            font-size: var(--text-xs);
            color: var(--text-muted);
            margin-top: var(--space-1);
        }
        .message.user .message-meta {
            text-align: right;
        }
        .chat-input {
            display: flex;
            gap: var(--space-3);
            padding: var(--space-4);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
        }
        .chat-input input {
            flex: 1;
            border: none;
            background: transparent;
        }
        .chat-input input:focus {
            outline: none;
        }
        .typing-indicator {
            display: none;
            padding: var(--space-2) var(--space-4);
            color: var(--text-muted);
            font-size: var(--text-sm);
        }
        .file-attachment {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            background: rgba(99, 102, 241, 0.1);
            border-radius: var(--radius-lg);
            margin-top: var(--space-2);
        }
        .file-attachment a {
            color: var(--primary);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="user-nav">
        <div class="user-nav-content">
            <a href="/" style="font-size: var(--text-xl); font-weight: 700; color: var(--primary);">
                <?php echo e(getSetting('site_name', 'WebHub.uz')); ?>
            </a>
            
            <ul class="nav-links">
                <li><a href="/user/dashboard.php">Bosh sahifa</a></li>
                <li><a href="/user/services.php">Xizmatlar</a></li>
                <li><a href="/user/applications.php">Arizalarim</a></li>
                <li><a href="/user/chat.php" class="active">Chat</a></li>
                <li><a href="/user/notifications.php">Bildirishnomalar</a></li>
                <li><a href="/user/profile.php">Profil</a></li>
                <li><a href="/user/logout.php" style="color: var(--error);">Chiqish</a></li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <?php if ($user['avatar']): ?>
                <img src="<?php echo e($user['avatar']); ?>" alt="<?php echo e($user['name']); ?>" style="width:32px;height:32px;border-radius:50%;">
                <?php endif; ?>
                <span style="color: var(--text-secondary);"><?php echo e($user['name']); ?></span>
            </div>
        </div>
    </nav>

    <div class="chat-container">
        <h1 style="margin-bottom: var(--space-4);">Admin bilan chat</h1>
        
        <div class="chat-messages" id="chat-messages">
            <?php if (empty($messages)): ?>
            <div style="text-align: center; padding: var(--space-12); color: var(--text-muted);">
                <p>Hozircha xabarlar yo'q</p>
                <p style="font-size: var(--text-sm); margin-top: var(--space-2);">Admin bilan bog'lanish uchun xabar yozing</p>
            </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['sender_type'] === 'user' ? 'user' : 'admin'; ?>">
                    <div class="message-avatar">
                        <?php echo $msg['sender_type'] === 'user' ? mb_substr($user['name'], 0, 1) : 'A'; ?>
                    </div>
                    <div>
                        <div class="message-content">
                            <?php echo nl2br(e($msg['message'])); ?>
                            <?php if ($msg['file_path']): ?>
                            <div class="file-attachment">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                </svg>
                                <a href="<?php echo e($msg['file_path']); ?>" target="_blank">Faylni yuklab olish</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="message-meta"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="typing-indicator" id="typing-indicator">Admin yozmoqda...</div>
        
        <form class="chat-input" id="chat-form">
            <input type="hidden" name="thread_id" value="<?php echo $chatThread['id']; ?>">
            <input type="text" id="message-input" name="message" placeholder="Xabar yozing..." autocomplete="off" required>
            <button type="submit" class="btn btn-primary" id="send-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>

    <script>
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const threadId = document.querySelector('[name="thread_id"]').value;
        
        // Scroll to bottom
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        scrollToBottom();
        
        // Send message
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            sendBtn.disabled = true;
            messageInput.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('thread_id', threadId);
                formData.append('message', message);
                formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
                
                const response = await fetch('/api/send-message.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageInput.value = '';
                    // Reload messages
                    loadMessages();
                } else {
                    alert(result.error || 'Xatolik yuz berdi');
                }
            } catch (error) {
                alert('Ulanishda xatolik');
            } finally {
                sendBtn.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
            }
        });
        
        // Load messages
        async function loadMessages() {
            try {
                const response = await fetch('/api/get-messages.php?thread_id=' + threadId);
                const messages = await response.json();
                
                let html = '';
                if (messages.length === 0) {
                    html = '<div style="text-align: center; padding: var(--space-12); color: var(--text-muted);"><p>Hozircha xabarlar yo\'q</p></div>';
                } else {
                    messages.forEach(msg => {
                        const isUser = msg.sender_type === 'user';
                        html += `
                            <div class="message ${isUser ? 'user' : 'admin'}">
                                <div class="message-avatar">${isUser ? '<?php echo mb_substr($user['name'], 0, 1); ?>' : 'A'}</div>
                                <div>
                                    <div class="message-content">
                                        ${msg.message.replace(/\n/g, '<br>')}
                                        ${msg.file_path ? `<div class="file-attachment"><a href="${msg.file_path}" target="_blank">Faylni yuklab olish</a></div>` : ''}
                                    </div>
                                    <div class="message-meta">${new Date(msg.created_at).toLocaleTimeString('uz-UZ', {hour: '2-digit', minute:'2-digit'})}</div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                chatMessages.innerHTML = html;
                scrollToBottom();
            } catch (error) {
                console.error('Failed to load messages:', error);
            }
        }
        
        // Poll for new messages every 3 seconds
        setInterval(loadMessages, 3000);
    </script>
</body>
</html>
