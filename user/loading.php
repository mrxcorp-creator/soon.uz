<?php
/**
 * Loading page after login (3-5 second animation with prefetch)
 */

require_once __DIR__ . '/../includes/functions.php';
requireUser();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yuklanmoqda...</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            overflow: hidden;
        }
        .loading-container {
            text-align: center;
        }
        .logo-animation {
            width: 120px;
            height: 120px;
            margin: 0 auto var(--space-8);
            position: relative;
        }
        .logo-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            animation: pulse 2s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            color: white;
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.3);
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }
        .loading-text {
            font-size: var(--text-xl);
            color: var(--text-secondary);
            margin-bottom: var(--space-4);
        }
        .loading-bar {
            width: 200px;
            height: 4px;
            background: var(--bg-secondary);
            border-radius: var(--radius-full);
            margin: 0 auto;
            overflow: hidden;
        }
        .loading-progress {
            height: 100%;
            background: var(--primary);
            width: 0;
            transition: width 0.1s linear;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="logo-animation">
            <div class="logo-circle">W</div>
        </div>
        <div class="loading-text">Yuklanmoqda...</div>
        <div class="loading-bar">
            <div class="loading-progress" id="progress"></div>
        </div>
    </div>

    <script>
        // Prefetch user data
        const prefetchPromises = [
            fetch('/user/api/profile.json').catch(() => {}),
            fetch('/user/api/applications.json').catch(() => {}),
            fetch('/user/api/notifications.json').catch(() => {})
        ];

        // Animate progress bar for 3-5 seconds
        let progress = 0;
        const progressBar = document.getElementById('progress');
        const duration = 3500 + Math.random() * 1500; // 3.5-5 seconds
        const startTime = Date.now();

        function animate() {
            const elapsed = Date.now() - startTime;
            progress = Math.min((elapsed / duration) * 100, 100);
            progressBar.style.width = progress + '%';

            if (progress < 100) {
                requestAnimationFrame(animate);
            } else {
                // Redirect to dashboard
                window.location.href = '/user/dashboard.php';
            }
        }

        animate();
    </script>
</body>
</html>
