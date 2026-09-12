<?php
/**
 * Database Migration & Installation Script
 * Yaratilgan: WebHub.uz
 * Maqsad: Barcha zarur jadvallarni yaratish va boshlang'ich ma'lumotlarni kiritish
 */

require_once __DIR__ . '/../config/init.php';

// O'rnatish jarayoni faqat install.php orqali boshlanishi kerak, 
// lekin bu funksiya API orqali chaqiriladi.
if (!defined('INSTALL_MODE') && !isset($_SESSION['install_step'])) {
    // Xavfsizlik: To'g'ridan-to'g'ri kirishni cheklash (agar kerak bo'lsa)
    // Bu yerda biz functions.php dagi DB ulanishidan foydalanamiz.
}

function runMigration($pdo) {
    try {
        $pdo->beginTransaction();

        // 1. Foydalanuvchilar jadvali
        $sql_users = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20),
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'client', 'manager') DEFAULT 'client',
            avatar_path VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_email (email),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_users);

        // 2. Xizmat turlari (Services)
        $sql_services = "CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title_uz VARCHAR(150) NOT NULL,
            title_ru VARCHAR(150) DEFAULT NULL,
            title_en VARCHAR(150) DEFAULT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description_uz TEXT,
            price_from DECIMAL(10, 2) DEFAULT 0.00,
            icon_class VARCHAR(50) DEFAULT 'fa-layer-group',
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_services);

        // 3. Loyihalar / Portfolio
        $sql_projects = "CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            client_name VARCHAR(100),
            description TEXT,
            short_description VARCHAR(255),
            cover_image VARCHAR(255),
            gallery_images JSON,
            service_id INT,
            technologies JSON,
            project_url VARCHAR(255),
            github_url VARCHAR(255),
            views_count INT DEFAULT 0,
            is_featured TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
            INDEX idx_featured (is_featured),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_projects);

        // 4. Arizalar (Applications/Orders)
        $sql_applications = "CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            service_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            message TEXT,
            budget_min DECIMAL(10, 2),
            budget_max DECIMAL(10, 2),
            status ENUM('new', 'contacted', 'in_progress', 'completed', 'cancelled') DEFAULT 'new',
            admin_note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            INDEX idx_status (status),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_applications);

        // 5. Xabarlar (Chat)
        $sql_messages = "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            attachment_path VARCHAR(255) DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_app (application_id),
            INDEX idx_read (is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_messages);

        // 6. Blog Maqolalari
        $sql_blog = "CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title_uz VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            content_uz LONGTEXT NOT NULL,
            excerpt_uz VARCHAR(255),
            cover_image VARCHAR(255),
            author_id INT,
            views_count INT DEFAULT 0,
            is_published TINYINT(1) DEFAULT 0,
            published_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_published (is_published),
            INDEX idx_date (published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_blog);

        // 7. Sozlamalar (Settings) - Key/Value format
        $sql_settings = "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('string', 'boolean', 'json', 'text') DEFAULT 'string',
            description VARCHAR(255),
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_settings);

        // 8. Audit Logs (Xavfsizlik)
        $sql_logs = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50),
            entity_id INT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            details JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_action (action),
            INDEX idx_date (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql_logs);

        // --- Boshlang'ich Ma'lumotlar (Seed Data) ---

        // Default Admin (password: admin123 - o'zgartirilishi shart!)
        $defaultPass = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Super Admin', 'admin@webhub.uz', $defaultPass, 'admin']);

        // Xizmat turlari
        $servicesData = [
            ['Veb saytlar ishlab chiqish', 'web-development', 'fa-code', 500000],
            ['Mobil ilovalar', 'mobile-apps', 'fa-mobile-alt', 1000000],
            ['SEO va Marketing', 'seo-marketing', 'fa-chart-line', 300000],
            ['Grafik Dizayn', 'graphic-design', 'fa-paint-brush', 200000],
            ['Texnik Qo\'llab-quvvatlash', 'support', 'fa-headset', 150000]
        ];

        $stmtService = $pdo->prepare("INSERT IGNORE INTO services (title_uz, slug, icon_class, price_from, is_active) VALUES (?, ?, ?, ?, 1)");
        foreach ($servicesData as $svc) {
            $stmtService->execute($svc);
        }

        // Default Sozlamalar
        $settingsData = [
            ['site_title', 'WebHub.uz', 'string', 'Sayt nomi'],
            ['site_description', 'O\'zbekistondagi eng zamonaviy raqamli yechimlar provayderi', 'text', 'Sayt tavsifi'],
            ['contact_email', 'info@webhub.uz', 'string', 'Aloqa email'],
            ['contact_phone', '+998 90 123 45 67', 'string', 'Aloqa telefon'],
            ['maintenance_mode', '0', 'boolean', 'Texnik ishlar rejimi'],
            ['registration_allowed', '1', 'boolean', 'Ro\'yxatdan o\'tish ruxsati']
        ];

        $stmtSetting = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
        foreach ($settingsData as $set) {
            $stmtSetting->execute($set);
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Migration Error: " . $e->getMessage());
        return false;
    }
}

// Agar to'g'ridan-to'g'ri ochilsa, xato qaytarish (xavfsizlik)
if (!defined('INSTALL_MODE')) {
    http_response_code(403);
    echo "Ruxsat yo'q. Ushbu fayl faqat install.php orqali ishlatilishi kerak.";
    exit;
}
?>
