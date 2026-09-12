<?php
/**
 * User Profile Page
 */

require_once __DIR__ . '/../includes/functions.php';
requireUser();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrfToken)) {
        $error = 'Xavfsizlik tekshiruvi muvaffaqiyatsiz';
    } else {
        $phone = trim($_POST['phone'] ?? '');
        $name = trim($_POST['name'] ?? '');
        
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $user['id']]);
            
            $_SESSION['user_name'] = $name;
            $success = 'Profil yangilandi';
            
            // Refresh user data
            $user = getCurrentUser();
            
        } catch (PDOException $e) {
            $error = 'Ma\\'lumotlarni saqlashda xatolik';
            error_log('Profile update error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - WebHub.uz</title>
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
        .profile-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            max-width: 600px;
        }
        .avatar-large {
            width: 100px;
            height: 100px;
            border-radius: var(--radius-full);
            object-fit: cover;
            margin-bottom: var(--space-4);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: var(--space-3) 0;
            border-bottom: 1px solid var(--border-color);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: var(--text-muted);
            font-size: var(--text-sm);
        }
        .info-value {
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
                <li><a href="/user/chat.php">Chat</a></li>
                <li><a href="/user/notifications.php">Bildirishnomalar</a></li>
                <li><a href="/user/profile.php" class="active">Profil</a></li>
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
        <h1 style="margin-bottom: var(--space-8);">Mening profilim</h1>
        
        <?php if ($error): ?>
        <div style="padding: var(--space-4); border-radius: var(--radius-lg); margin-bottom: var(--space-6); background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444;">
            <?php echo e($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div style="padding: var(--space-4); border-radius: var(--radius-lg); margin-bottom: var(--space-6); background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981;">
            <?php echo e($success); ?>
        </div>
        <?php endif; ?>
        
        <div class="profile-card">
            <div style="text-align: center; margin-bottom: var(--space-8);">
                <?php if ($user['avatar']): ?>
                <img src="<?php echo e($user['avatar']); ?>" alt="<?php echo e($user['name']); ?>" class="avatar-large">
                <?php else: ?>
                <div style="width:100px;height:100px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:var(--text-3xl);font-weight:700;color:white;margin:0 auto var(--space-4);">
                    <?php echo mb_substr($user['name'], 0, 1); ?>
                </div>
                <?php endif; ?>
                <h2 style="margin-bottom: var(--space-2);"><?php echo e($user['name']); ?></h2>
                <p style="color: var(--text-muted);">Google orqali ro'yxatdan o'tgan</p>
            </div>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label for="name">Ism</label>
                    <input type="text" id="name" name="name" required value="<?php echo e($user['name']); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo e($user['email']); ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                    <p style="font-size: var(--text-xs); color: var(--text-muted); margin-top: var(--space-1);">Email Google hisobidan olinadi va o'zgartirilmaydi</p>
                </div>
                
                <div class="form-group" style="margin-bottom: var(--space-6);">
                    <label for="phone">Telefon raqam</label>
                    <input type="tel" id="phone" name="phone" placeholder="+998 90 123 45 67" value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Saqlash</button>
            </form>
            
            <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--border-color);">
                <h3 style="margin-bottom: var(--space-4);">Hisob ma'lumotlari</h3>
                <div class="info-row">
                    <span class="info-label">Ro'yxatdan o'tgan sana</span>
                    <span class="info-value"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Holati</span>
                    <span class="info-value" style="color: <?php echo $user['status'] === 'active' ? 'var(--success)' : 'var(--error)'; ?>;">
                        <?php echo $user['status'] === 'active' ? 'Faol' : 'Bloklangan'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
