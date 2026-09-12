<?php
/**
 * User Applications Page - View all applications
 */

require_once __DIR__ . '/../config/init.php';
requireUser();

$user = getCurrentUser();

try {
    $pdo = getDB();
    
    // Get all user applications
    $stmt = $pdo->prepare("
        SELECT a.*, s.title as service_title
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $applications = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $applications = [];
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arizalarim - WebHub.uz</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/variables.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/base.css">
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
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            margin-bottom: var(--space-4);
        }
        .status-badge {
            display: inline-block;
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            font-weight: 600;
        }
        .status-new { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .status-in_review { background: rgba(245,158,11,0.1); color: #f59e0b; }
        .status-approved { background: rgba(16,185,129,0.1); color: #10b981; }
        .status-completed { background: rgba(16,185,129,0.1); color: #10b981; }
        .status-cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-4);
            flex-wrap: wrap;
            gap: var(--space-3);
        }
    </style>
</head>
<body>
    <nav class="user-nav">
        <div class="user-nav-content">
            <a href="<?php echo BASE_URL; ?>/" style="font-size: var(--text-xl); font-weight: 700; color: var(--primary);">
                <?php echo e(getSetting('site_name', 'WebHub.uz')); ?>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>/user/dashboard.php">Bosh sahifa</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/services.php">Xizmatlar</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/applications.php" class="active">Arizalarim</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/chat.php">Chat</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/notifications.php">Bildirishnomalar</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/profile.php">Profil</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/logout.php" style="color: var(--error);">Chiqish</a></li>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
            <div>
                <h1 style="margin-bottom: var(--space-2);">Mening arizalarim</h1>
                <p style="color: var(--text-secondary);">Barcha yuborilgan arizalar ro'yxati</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/user/services.php" class="btn btn-primary">Yangi ariza</a>
        </div>
        
        <?php if (empty($applications)): ?>
        <div class="card" style="text-align: center; padding: var(--space-12);">
            <p style="color: var(--text-muted); margin-bottom: var(--space-6);">Hozircha arizalar yo'q</p>
            <a href="<?php echo BASE_URL; ?>/user/services.php" class="btn btn-primary">Xizmatlarni ko'rish</a>
        </div>
        <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <?php foreach ($applications as $app): ?>
            <div class="card">
                <div class="app-header">
                    <div>
                        <h3 style="margin-bottom: var(--space-1);">#<?php echo $app['id']; ?> - <?php echo e($app['service_title'] ?? 'Xizmat tanlanmagan'); ?></h3>
                        <p style="color: var(--text-muted); font-size: var(--text-sm);">
                            <?php echo date('d.m.Y H:i', strtotime($app['created_at'])); ?>
                        </p>
                    </div>
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
                </div>
                
                <?php if ($app['description']): ?>
                <div style="padding: var(--space-3); background: var(--bg-tertiary); border-radius: var(--radius-lg); margin-bottom: var(--space-4);">
                    <strong>Izoh:</strong>
                    <p style="margin: var(--space-2) 0 0 0; color: var(--text-secondary);"><?php echo e($app['description']); ?></p>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>/user/chat.php" class="btn btn-sm btn-outline">Chat</a>
                    <?php if ($app['status'] !== 'completed' && $app['status'] !== 'cancelled'): ?>
                    <button class="btn btn-sm btn-secondary" onclick="alert('Ariza holatini o\\'zgartirish uchun admin bilan bog\\'laning')">Bekor qilish</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
