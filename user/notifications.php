<?php
/**
 * User Notifications Page
 */

require_once __DIR__ . '/../includes/functions.php';
requireUser();

$user = getCurrentUser();

try {
    $pdo = getDB();
    
    // Get all notifications
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll();
    
    // Mark all as read
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user['id']]);
    
} catch (PDOException $e) {
    $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirishnomalar - WebHub.uz</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body { padding-top: 80px; }
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
        .notification-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-5);
            margin-bottom: var(--space-4);
            transition: all var(--transition-fast);
        }
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-lg);
        }
        .notification-card.unread {
            border-left: 3px solid var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }
        .notification-title {
            font-weight: 600;
            margin-bottom: var(--space-2);
        }
        .notification-time {
            font-size: var(--text-xs);
            color: var(--text-muted);
            margin-top: var(--space-2);
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
                <li><a href="/user/chat.php">Chat</a></li>
                <li><a href="/user/notifications.php" class="active">Bildirishnomalar</a></li>
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

    <div class="container section">
        <h1 style="margin-bottom: var(--space-2);">Bildirishnomalar</h1>
        <p style="color: var(--text-secondary); margin-bottom: var(--space-8);">Admin tomonidan yuborilgan xabarlar</p>
        
        <?php if (empty($notifications)): ?>
        <div class="card" style="text-align: center; padding: var(--space-12);">
            <p style="color: var(--text-muted);">Yangi bildirishnomalar yo'q</p>
        </div>
        <?php else: ?>
        <div>
            <?php foreach ($notifications as $notification): ?>
            <div class="notification-card <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                <div class="notification-title"><?php echo e($notification['title']); ?></div>
                <div style="color: var(--text-secondary);"><?php echo e($notification['message']); ?></div>
                <div class="notification-time">
                    <?php echo date('d.m.Y H:i', strtotime($notification['created_at'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
