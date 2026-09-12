<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../config/init.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrfToken)) {
        $error = 'Xavfsizlik tekshiruvi muvaffaqiyatsiz';
    } elseif (empty($login) || empty($password)) {
        $error = 'Login va parolni kiriting';
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ?");
            $stmt->execute([$login]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Regenerate session for security
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                
                logAdminAction($admin['id'], 'Admin login', 'Successful login');
                
                redirect('/admin/dashboard.php', 'Xush kelibsiz!', 'success');
            } else {
                $error = 'Login yoki parol noto\'g\'ri';
                logAdminAction(0, 'Failed login attempt', "Login: {$login}");
            }
        } catch (PDOException $e) {
            $error = 'Serverda xatolik yuz berdi';
            error_log('Admin login error: ' . $e->getMessage());
        }
    }
}

regenerateCSRFToken();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kirish</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/variables.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/base.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-6);
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            padding: var(--space-8);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            box-shadow: var(--card-shadow-lg);
        }
        .logo {
            text-align: center;
            margin-bottom: var(--space-8);
        }
        .logo a {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>/"><?php echo e(getSetting('site_name', 'WebHub.uz')); ?></a>
            <p style="color: var(--text-muted); margin-top: var(--space-2);">Admin Panel</p>
        </div>
        
        <?php if ($error): ?>
        <div style="padding: var(--space-3); border-radius: var(--radius-lg); margin-bottom: var(--space-6); background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444;">
            <?php echo e($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" required autofocus placeholder="Loginni kiriting">
            </div>
            
            <div class="form-group" style="margin-bottom: var(--space-6);">
                <label for="password">Parol</label>
                <input type="password" id="password" name="password" required placeholder="Parolni kiriting">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Kirish</button>
        </form>
        
        <p style="text-align: center; margin-top: var(--space-6); color: var(--text-muted);">
            <a href="<?php echo BASE_URL; ?>/">Bosh sahifaga qaytish</a>
        </p>
    </div>
</body>
</html>
