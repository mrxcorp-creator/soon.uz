<?php
/**
 * User Dashboard
 */

require_once __DIR__ . '/../includes/functions.php';
requireUser();

$user = getCurrentUser();

try {
    $pdo = getDB();
    
    // Get user's applications count
    $applicationsCount = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
    $applicationsCount->execute([$user['id']]);
    $applicationsCount = $applicationsCount->fetchColumn();
    
    // Get active applications
    $activeApplications = $pdo->prepare("
        SELECT a.*, s.title as service_title 
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        WHERE a.user_id = ? AND a.status IN ('new', 'in_review', 'approved')
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $activeApplications->execute([$user['id']]);
    $activeApplications = $activeApplications->fetchAll();
    
    // Get unread notifications count
    $unreadCount = getUnreadNotificationCount($user['id']);
    
    // Get recent notifications
    $notifications = getUserNotifications($user['id'], 5);
    
} catch (PDOException $e) {
    $applicationsCount = 0;
    $activeApplications = [];
    $unreadCount = 0;
    $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mening kabinetim - WebHub.uz</title>
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
        .nav-links a:hover {
            color: var(--primary);
        }
        .nav-links a.active {
            color: var(--primary);
            font-weight: 600;
        }
        .notification-badge {
            position: relative;
        }
        .badge-count {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            border-radius: var(--radius-full);
            background: #ef4444;
            color: white;
            font-size: var(--text-xs);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px 4px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
        }
        .stat-value {
            font-size: var(--text-3xl);
            font-weight: 700;
            color: var(--primary);
        }
        .stat-label {
            color: var(--text-muted);
            margin-top: var(--space-1);
        }
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
        }
        .status-badge {
            display: inline-block;
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
        }
        .status-new { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .status-in_review { background: rgba(245,158,11,0.1); color: #f59e0b; }
        .status-approved { background: rgba(16,185,129,0.1); color: #10b981; }
        .status-completed { background: rgba(16,185,129,0.1); color: #10b981; }
        .status-cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }
        .avatar {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-full);
            object-fit: cover;
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
                <li><a href="/user/dashboard.php" class="active">Bosh sahifa</a></li>
                <li><a href="/user/services.php">Xizmatlar</a></li>
                <li><a href="/user/applications.php">Arizalarim</a></li>
                <li><a href="/user/chat.php">Chat</a></li>
                <li class="notification-badge">
                    <a href="/user/notifications.php">Bildirishnomalar</a>
                    <?php if ($unreadCount > 0): ?>
                    <span class="badge-count"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </li>
                <li><a href="/user/profile.php">Profil</a></li>
                <li><a href="/user/logout.php" style="color: var(--error);">Chiqish</a></li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: var(--space-3);">
                <?php if ($user['avatar']): ?>
                <img src="<?php echo e($user['avatar']); ?>" alt="<?php echo e($user['name']); ?>" class="avatar">
                <?php endif; ?>
                <span style="color: var(--text-secondary);"><?php echo e($user['name']); ?></span>
            </div>
        </div>
    </nav>

    <div class="container section">
        <h1 style="margin-bottom: var(--space-2);">Xush kelibsiz, <?php echo e($user['name']); ?>!</h1>
        <p style="color: var(--text-secondary); margin-bottom: var(--space-8);">Mijozlar paneliga xush kelibsiz</p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $applicationsCount; ?></div>
                <div class="stat-label">Jami arizalar</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #3b82f6;"><?php echo count($activeApplications); ?></div>
                <div class="stat-label">Faol arizalar</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #10b981;"><?php echo $unreadCount; ?></div>
                <div class="stat-label">Yangi bildirishnomalar</div>
            </div>
        </div>
        
        <div class="grid grid-cols-1" style="gap: var(--space-6);">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                    <h2 style="margin: 0;">Faol arizalar</h2>
                    <a href="/user/applications.php" class="btn btn-sm btn-outline">Barchasini ko'rish</a>
                </div>
                
                <?php if (empty($activeApplications)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: var(--space-8);">
                    Hozircha faol arizalar yo'q
                </p>
                <?php else: ?>
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding: var(--space-2); color: var(--text-secondary);">ID</th>
                            <th style="text-align: left; padding: var(--space-2); color: var(--text-secondary);">Xizmat</th>
                            <th style="text-align: left; padding: var(--space-2); color: var(--text-secondary);">Holati</th>
                            <th style="text-align: left; padding: var(--space-2); color: var(--text-secondary);">Sana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeApplications as $app): ?>
                        <tr style="border-top: 1px solid var(--border-color);">
                            <td style="padding: var(--space-3);">#<?php echo $app['id']; ?></td>
                            <td style="padding: var(--space-3);"><?php echo e($app['service_title'] ?? 'Tanlanmagan'); ?></td>
                            <td style="padding: var(--space-3);">
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php 
                                    $statusLabels = [
                                        'new' => 'Yangi',
                                        'in_review' => "Ko'rib chiqilmoqda",
                                        'approved' => 'Tasdiqlandi',
                                        'completed' => 'Yakunlandi',
                                        'cancelled' => 'Bekor qilindi'
                                    ];
                                    echo $statusLabels[$app['status']] ?? $app['status'];
                                    ?>
                                </span>
                            </td>
                            <td style="padding: var(--space-3);"><?php echo date('d.m.Y', strtotime($app['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                    <h2 style="margin: 0;">So'nggi bildirishnomalar</h2>
                    <a href="/user/notifications.php" class="btn btn-sm btn-outline">Barchasini ko'rish</a>
                </div>
                
                <?php if (empty($notifications)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: var(--space-8);">
                    Yangi bildirishnomalar yo'q
                </p>
                <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    <?php foreach ($notifications as $notification): ?>
                    <div style="padding: var(--space-3); border-radius: var(--radius-lg); background: var(--bg-tertiary); <?php echo !$notification['is_read'] ? 'border-left: 3px solid var(--primary);' : ''; ?>">
                        <div style="font-weight: 600; margin-bottom: var(--space-1);"><?php echo e($notification['title']); ?></div>
                        <div style="color: var(--text-secondary); font-size: var(--text-sm);"><?php echo e($notification['message']); ?></div>
                        <div style="font-size: var(--text-xs); color: var(--text-muted); margin-top: var(--space-2);">
                            <?php echo date('d.m.Y H:i', strtotime($notification['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="margin-top: var(--space-8); text-align: center;">
            <a href="/user/services.php" class="btn btn-lg btn-primary">Yangi ariza yaratish</a>
        </div>
    </div>
</body>
</html>
