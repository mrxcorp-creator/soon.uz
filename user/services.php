<?php
/**
 * User Services Page - Browse and order services
 */

require_once __DIR__ . '/../config/init.php';
requireUser();

$user = getCurrentUser();
$services = getServices();

// Get user's chat thread
$chatThread = getOrCreateChatThread($user['id']);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xizmatlar - WebHub.uz</title>
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
        .service-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            transition: all var(--transition-base);
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-lg);
        }
        .price {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--primary);
            margin: var(--space-4) 0;
        }
        .features-list {
            list-style: none;
            padding: 0;
            margin: var(--space-4) 0;
        }
        .features-list li {
            padding: var(--space-2) 0;
            color: var(--text-secondary);
        }
        .features-list li::before {
            content: '✓';
            color: var(--success);
            font-weight: 700;
            margin-right: var(--space-2);
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: var(--z-modal);
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            max-width: 500px;
            width: 90%;
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            max-height: 90vh;
            overflow-y: auto;
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
                <li><a href="<?php echo BASE_URL; ?>/user/services.php" class="active">Xizmatlar</a></li>
                <li><a href="<?php echo BASE_URL; ?>/user/applications.php">Arizalarim</a></li>
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
        <h1 style="margin-bottom: var(--space-2);">Xizmatlarimiz</h1>
        <p style="color: var(--text-secondary); margin-bottom: var(--space-8);">Biznesingiz uchun professional IT yechimlar tanlang</p>
        
        <div class="grid grid-cols-1" style="gap: var(--space-6);">
            <?php foreach ($services as $service): ?>
            <div class="service-card">
                <h3 style="font-size: var(--text-xl); margin-bottom: var(--space-2);"><?php echo e($service['title']); ?></h3>
                <p style="color: var(--text-secondary); margin-bottom: var(--space-4);"><?php echo e($service['description']); ?></p>
                <div class="price"><?php echo number_format($service['price'], 0, '.', ' '); ?> so'mdan</div>
                
                <?php if ($service['features_json']): ?>
                <ul class="features-list">
                    <?php foreach (json_decode($service['features_json'], true) as $feature): ?>
                    <li><?php echo e($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <button class="btn btn-primary" onclick="openOrderModal(<?php echo $service['id']; ?>, '<?php echo e($service['title']); ?>')">Buyurtma berish</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal-overlay" id="order-modal">
        <div class="modal">
            <h2 style="margin-bottom: var(--space-4);">Ariza yaratish</h2>
            <form method="POST" action="<?php echo BASE_URL; ?>/api/submit-application.php" data-ajax>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="service_id" id="modal-service-id">
                
                <div style="padding: var(--space-3); background: var(--bg-tertiary); border-radius: var(--radius-lg); margin-bottom: var(--space-4);">
                    <strong>Tanlangan xizmat:</strong> <span id="modal-service-name"></span>
                </div>
                
                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label for="phone">Telefon raqam</label>
                    <input type="tel" id="phone" name="phone" required placeholder="+998 90 123 45 67" value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: var(--space-6);">
                    <label for="message">Loyiha haqida ma'lumot</label>
                    <textarea id="message" name="message" rows="4" placeholder="Loyihangiz haqida qisqacha ma'lumot bering..."></textarea>
                </div>
                
                <div style="display: flex; gap: var(--space-3);">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Yuborish</button>
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openOrderModal(serviceId, serviceName) {
            document.getElementById('modal-service-id').value = serviceId;
            document.getElementById('modal-service-name').textContent = serviceName;
            document.getElementById('order-modal').classList.add('active');
        }
        
        function closeOrderModal() {
            document.getElementById('order-modal').classList.remove('active');
        }
        
        // Close modal on overlay click
        document.getElementById('order-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>
</body>
</html>
