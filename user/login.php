<?php
/**
 * User Login - Google OAuth
 */

require_once __DIR__ . '/../config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/user/dashboard.php');
}

// Handle OAuth callback
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    try {
        // Exchange code for token
        $clientId = getSetting('google_client_id', '');
        $clientSecret = getSetting('google_client_secret', '');
        $redirectUri = SITE_URL . '/user/login.php';
        
        $tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'code' => $code,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code'
                ])
            ]
        ]));
        
        $tokenData = json_decode($tokenResponse, true);
        
        if (!isset($tokenData['access_token'])) {
            throw new Exception('Google token olishda xatolik');
        }
        
        // Get user info from Google
        $userInfo = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $tokenData['access_token']);
        $userData = json_decode($userInfo, true);
        
        if (!isset($userData['id'])) {
            throw new Exception('Google user ma\\'lumotlarini olishda xatolik');
        }
        
        // Find or create user
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->execute([$userData['id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Create new user
            $stmt = $pdo->prepare("
                INSERT INTO users (google_id, name, email, avatar, status)
                VALUES (?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $userData['id'],
                $userData['name'],
                $userData['email'],
                $userData['picture'] ?? ''
            ]);
            
            $userId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } elseif ($user['status'] === 'blocked') {
            redirect('/', 'Sizning hisobingiz bloklangan', 'error');
        }
        
        // Set session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        
        // Create chat thread for user
        getOrCreateChatThread($user['id']);
        
        redirect('/user/loading.php', 'Muvaffaqiyatli kirish!', 'success');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log('OAuth error: ' . $e->getMessage());
    }
}

$error = $error ?? '';
$googleClientId = getSetting('google_client_id', '');
$redirectUri = SITE_URL . '/user/login.php';
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $googleClientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => generateCSRFToken()
]);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish - WebHub.uz</title>
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
            max-width: 420px;
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
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-3);
            width: 100%;
            padding: var(--space-3) var(--space-4);
            background: white;
            color: #1f1f1f;
            border: 1px solid #dadce0;
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .google-btn:hover {
            background: #f8f9fa;
            border-color: #d2e3fc;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: var(--space-6) 0;
            color: var(--text-muted);
            font-size: var(--text-sm);
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }
        .divider span {
            padding: 0 var(--space-4);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>/"><?php echo e(getSetting('site_name', 'WebHub.uz')); ?></a>
            <p style="color: var(--text-muted); margin-top: var(--space-2);">Mijozlar paneli</p>
        </div>
        
        <?php if ($error): ?>
        <div style="padding: var(--space-3); border-radius: var(--radius-lg); margin-bottom: var(--space-6); background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444;">
            <?php echo e($error); ?>
        </div>
        <?php endif; ?>
        
        <a href="<?php echo $authUrl; ?>" class="google-btn">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.125 8.125H10V11.875H14.6875C14.0625 14.875 11.5625 16.875 8.125 16.875C4.0625 16.875 0.9375 13.75 0.9375 10C0.9375 6.25 4.0625 3.125 8.125 3.125C10.3125 3.125 11.875 4.0625 12.8125 4.9375L15.6875 2.0625C13.4375 0 10.9375 -0.9375 8.125 -0.9375C3.125 -0.9375 -0.9375 3.125 -0.9375 10C-0.9375 16.875 3.125 20.9375 8.125 20.9375C13.4375 20.9375 17.8125 17.1875 17.8125 10.625C17.8125 9.6875 17.8125 8.75 18.125 8.125Z" fill="#4285F4"/>
            </svg>
            Google orqali kirish
        </a>
        
        <div class="divider">
            <span>yoki</span>
        </div>
        
        <p style="text-align: center; color: var(--text-secondary); font-size: var(--text-sm);">
            Google hisobi orqali tez va oson kiring
        </p>
        
        <p style="text-align: center; margin-top: var(--space-6); color: var(--text-muted); font-size: var(--text-sm);">
            <a href="<?php echo BASE_URL; ?>/">Bosh sahifaga qaytish</a>
        </p>
    </div>
</body>
</html>
