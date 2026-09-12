<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$admin = getCurrentAdmin();

// Get stats
try {
    $pdo = getDB();
    
    $totalApplications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    $newApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'new'")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalProjects = $pdo->query("SELECT COUNT(*) FROM portfolio")->fetchColumn();
    
    // Recent applications
    $recentApps = $pdo->query("
        SELECT a.*, s.title as service_title, u.name as user_name
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ")->fetchAll();
    
} catch (PDOException $e) {
    $recentApps = [];
    $totalApplications = 0;
    $newApplications = 0;
    $totalUsers = 0;
    $totalProjects = 0;
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Boshqaruv</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body { padding-top: 80px; }
        .admin-nav {
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
        .admin-nav-content {
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
        .table-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: var(--space-4);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background: var(--bg-tertiary);
            font-weight: 600;
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }
        tr:hover {
            background: var(--bg-tertiary);
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
    </style>
</head>
<body>
    <nav class="admin-nav">
        <div class="admin-nav-content">
            <a href="/" style="font-size: var(--text-xl); font-weight: 700; color: var(--primary);">
                <?php echo e(getSetting('site_name', 'WebHub.uz')); ?> Admin
            </a>
            
            <ul class="nav-links">
                <li><a href="/admin/dashboard.php">Bosh sahifa</a></li>
                <li><a href="/admin/applications.php">Arizalar</a></li>
                <li><a href="/admin/services.php">Xizmatlar</a></li>
                <li><a href="/admin/portfolio.php">Portfolio</a></li>
                <li><a href="/admin/blog.php">Blog</a></li>
                <li><a href="/admin/users.php">Foydalanuvchilar</a></li>
                <li><a href="/admin/settings.php">Sozlamalar</a></li>
                <li><a href="/admin/logout.php" style="color: var(--error);">Chiqish</a></li>
            </ul>
            
            <div style="color: var(--text-secondary);">
                <?php echo e($admin['name'] ?? $admin['login']); ?>
            </div>
        </div>
    </nav>

    <div class="container section">
        <h1 style="margin-bottom: var(--space-8);">Boshqaruv paneli</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalApplications; ?></div>
                <div class="stat-label">Jami arizalar</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #3b82f6;"><?php echo $newApplications; ?></div>
                <div class="stat-label">Yangi arizalar</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #10b981;"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Foydalanuvchilar</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #f59e0b;"><?php echo $totalProjects; ?></div>
                <div class="stat-label">Loyihalar</div>
            </div>
        </div>
        
        <h2 style="margin-bottom: var(--space-4);">So'nggi arizalar</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ism</th>
                        <th>Telefon</th>
                        <th>Xizmat</th>
                        <th>Holati</th>
                        <th>Sana</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentApps)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: var(--space-8); color: var(--text-muted);">
                            Hozircha arizalar yo'q
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentApps as $app): ?>
                        <tr>
                            <td>#<?php echo $app['id']; ?></td>
                            <td><?php echo e($app['name']); ?></td>
                            <td><?php echo e($app['phone']); ?></td>
                            <td><?php echo e($app['service_title'] ?? 'Tanlanmagan'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php 
                                    $statusLabels = [
                                        'new' => 'Yangi',
                                        'in_review' => 'Ko\'rib chiqilmoqda',
                                        'approved' => 'Tasdiqlandi',
                                        'completed' => 'Yakunlandi',
                                        'cancelled' => 'Bekor qilindi'
                                    ];
                                    echo $statusLabels[$app['status']] ?? $app['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($app['created_at'])); ?></td>
                            <td>
                                <a href="/admin/application-view.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline">Ko'rish</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
