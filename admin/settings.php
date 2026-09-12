<?php
/**
 * Admin - Sozlamalar
 * WebHub.uz
 */

require_once '../includes/functions.php';
requireAdmin();

$pdo = getDB();
$message = '';
$messageType = '';

// POST so'rovlarini qayta ishlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        die('CSRF token noto\'g\'ri');
    }

    try {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = str_replace('setting_', '', $key);
                
                // Boolean qiymatlarni konvertatsiya qilish
                if (isset($_POST['type_' . $settingKey]) && $_POST['type_' . $settingKey] === 'boolean') {
                    $value = isset($_POST[$key]) ? '1' : '0';
                }
                
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value=?, updated_at=CURRENT_TIMESTAMP");
                $stmt->execute([$settingKey, $value, $_POST['type_' . $settingKey] ?? 'string', $value]);
            }
        }
        
        logAudit($pdo, getCurrentUserId(), 'settings_updated', 'settings');
        $message = 'Sozlamalar saqlandi!';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Xatolik: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Barcha sozlamalarni olish
$stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_key");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row;
}

// Helper function
function getSettingValue($key, $default = '') {
    global $settings;
    return $settings[$key]['setting_value'] ?? $default;
}

function getSettingType($key, $default = 'string') {
    global $settings;
    return $settings[$key]['setting_type'] ?? $default;
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sozlamalar - WebHub Admin</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--bg-secondary); padding: 1.5rem; border-right: 1px solid var(--border-color); }
        .sidebar-nav a { display: block; padding: 0.75rem 1rem; color: var(--text-muted); text-decoration: none; border-radius: 6px; margin-bottom: 0.5rem; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: var(--primary); color: white; }
        .main-content { flex: 1; padding: 2rem; }
        .card { background: var(--bg-primary); border-radius: 8px; padding: 1.5rem; box-shadow: var(--shadow-md); margin-bottom: 1.5rem; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .setting-group { border-bottom: 1px solid var(--border-color); padding: 1rem 0; }
        .setting-group:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2 class="text-xl font-bold text-primary mb-6">WebHub.Admin</h2>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-home mr-2"></i> Bosh sahifa</a>
                <a href="services.php"><i class="fas fa-layer-group mr-2"></i> Xizmatlar</a>
                <a href="applications.php"><i class="fas fa-file-alt mr-2"></i> Arizalar</a>
                <a href="projects.php"><i class="fas fa-briefcase mr-2"></i> Loyihalar</a>
                <a href="#"><i class="fas fa-users mr-2"></i> Foydalanuvchilar</a>
                <a href="settings.php" class="active"><i class="fas fa-cog mr-2"></i> Sozlamalar</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Chiqish</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 class="text-2xl font-bold mb-6">Sayt sozlamalari</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" class="card">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="setting-group">
                    <h3 class="text-lg font-semibold mb-4">Asosiy ma'lumotlar</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label>Sayt nomi</label>
                            <input type="text" name="setting_site_title" class="form-control" value="<?= htmlspecialchars(getSettingValue('site_title', 'WebHub.uz')) ?>">
                            <input type="hidden" name="type_site_title" value="string">
                        </div>
                        
                        <div class="form-group">
                            <label>Sayt tavsifi (SEO)</label>
                            <input type="text" name="setting_site_description" class="form-control" value="<?= htmlspecialchars(getSettingValue('site_description', '')) ?>">
                            <input type="hidden" name="type_site_description" value="text">
                        </div>
                        
                        <div class="form-group">
                            <label>Aloqa Email</label>
                            <input type="email" name="setting_contact_email" class="form-control" value="<?= htmlspecialchars(getSettingValue('contact_email', 'info@webhub.uz')) ?>">
                            <input type="hidden" name="type_contact_email" value="string">
                        </div>
                        
                        <div class="form-group">
                            <label>Aloqa Telefon</label>
                            <input type="text" name="setting_contact_phone" class="form-control" value="<?= htmlspecialchars(getSettingValue('contact_phone', '+998 90 123 45 67')) ?>">
                            <input type="hidden" name="type_contact_phone" value="string">
                        </div>
                    </div>
                </div>

                <div class="setting-group">
                    <h3 class="text-lg font-semibold mb-4">Ijtimoiy tarmoqlar</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label><i class="fab fa-telegram text-primary"></i> Telegram</label>
                            <input type="url" name="setting_social_telegram" class="form-control" value="<?= htmlspecialchars(getSettingValue('social_telegram', '')) ?>" placeholder="https://t.me/username">
                            <input type="hidden" name="type_social_telegram" value="string">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-instagram text-danger"></i> Instagram</label>
                            <input type="url" name="setting_social_instagram" class="form-control" value="<?= htmlspecialchars(getSettingValue('social_instagram', '')) ?>" placeholder="https://instagram.com/username">
                            <input type="hidden" name="type_social_instagram" value="string">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-facebook text-primary"></i> Facebook</label>
                            <input type="url" name="setting_social_facebook" class="form-control" value="<?= htmlspecialchars(getSettingValue('social_facebook', '')) ?>" placeholder="https://facebook.com/page">
                            <input type="hidden" name="type_social_facebook" value="string">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-linkedin text-info"></i> LinkedIn</label>
                            <input type="url" name="setting_social_linkedin" class="form-control" value="<?= htmlspecialchars(getSettingValue('social_linkedin', '')) ?>" placeholder="https://linkedin.com/company">
                            <input type="hidden" name="type_social_linkedin" value="string">
                        </div>
                    </div>
                </div>

                <div class="setting-group">
                    <h3 class="text-lg font-semibold mb-4">Tizim sozlamalari</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" name="setting_maintenance_mode" value="1" <?= getSettingValue('maintenance_mode', '0') === '1' ? 'checked' : '' ?> class="mr-2">
                                Texnik ishlar rejimi
                            </label>
                            <input type="hidden" name="type_maintenance_mode" value="boolean">
                            <p class="text-xs text-muted mt-1">Faol bo'lsa, sayt faqat adminlar uchun ko'rinadi</p>
                        </div>
                        
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" name="setting_registration_allowed" value="1" <?= getSettingValue('registration_allowed', '1') === '1' ? 'checked' : '' ?> class="mr-2">
                                Ro'yxatdan o'tish ruxsati
                            </label>
                            <input type="hidden" name="type_registration_allowed" value="boolean">
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i> Saqlash</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
