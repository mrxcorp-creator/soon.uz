<?php
/**
 * Admin - Xizmatlarni boshqarish
 * WebHub.uz
 */

require_once '../includes/functions.php';
requireAdmin();

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

// POST so'rovlarini qayta ishlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        die('CSRF token noto\'g\'ri');
    }

    if (isset($_POST['save_service'])) {
        $id = (int)($_POST['id'] ?? 0);
        $title_uz = trim($_POST['title_uz']);
        $slug = generateSlug($_POST['slug'] ?: $title_uz);
        $price = (float)($_POST['price'] ?? 0);
        $icon = $_POST['icon'] ?: 'fa-layer-group';
        $sort = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            if ($id > 0) {
                // Yangilash
                $stmt = $pdo->prepare("UPDATE services SET title_uz=?, slug=?, price_from=?, icon_class=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$title_uz, $slug, $price, $icon, $sort, $is_active, $id]);
                logAudit($pdo, getCurrentUserId(), 'service_updated', 'services', $id);
                $message = 'Xizmat muvaffaqiyatli yangilandi!';
            } else {
                // Yangi yaratish
                $stmt = $pdo->prepare("INSERT INTO services (title_uz, slug, price_from, icon_class, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title_uz, $slug, $price, $icon, $sort, $is_active]);
                logAudit($pdo, getCurrentUserId(), 'service_created', 'services', $pdo->lastInsertId());
                $message = 'Yangi xizmat qo\'shildi!';
            }
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik: ' . $e->getMessage();
            $messageType = 'error';
        }
    }

    if (isset($_POST['delete_service']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id=?");
        $stmt->execute([$id]);
        logAudit($pdo, getCurrentUserId(), 'service_deleted', 'services', $id);
        $message = 'Xizmat o\'chirildi!';
        $messageType = 'success';
    }
}

// Ro'yxatni olish
$services = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id DESC");
    $services = $stmt->fetchAll();
}

// Tahrirlash uchun ma'lumot olish
$editService = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id=?");
    $stmt->execute([(int)$_GET['id']]);
    $editService = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xizmatlar - WebHub Admin</title>
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
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { font-weight: 600; color: var(--text-muted); }
        .badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .badge-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2 class="text-xl font-bold text-primary mb-6">WebHub.Admin</h2>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-home mr-2"></i> Bosh sahifa</a>
                <a href="services.php" class="active"><i class="fas fa-layer-group mr-2"></i> Xizmatlar</a>
                <a href="#"><i class="fas fa-briefcase mr-2"></i> Loyihalar</a>
                <a href="#"><i class="fas fa-file-alt mr-2"></i> Arizalar</a>
                <a href="#"><i class="fas fa-users mr-2"></i> Foydalanuvchilar</a>
                <a href="#"><i class="fas fa-cog mr-2"></i> Sozlamalar</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Chiqish</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Xizmatlarni boshqarish</h1>
                <?php if ($action !== 'create' && !$editService): ?>
                    <a href="?action=create" class="btn btn-primary"><i class="fas fa-plus mr-2"></i> Yangi xizmat</a>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Create/Edit Form -->
            <?php if ($action === 'create' || $editService): ?>
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4"><?= $editService ? 'Tahrirlash' : 'Yangi xizmat' ?></h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $editService['id'] ?? '' ?>">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Nomi (O'zbekcha)</label>
                                <input type="text" name="title_uz" class="form-control" value="<?= htmlspecialchars($editService['title_uz'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug (URL)</label>
                                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editService['slug'] ?? '') ?>" placeholder="web-development">
                            </div>
                            <div class="form-group">
                                <label>Narx (so'mdan)</label>
                                <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($editService['price_from'] ?? '') ?>" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Icon klass</label>
                                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($editService['icon_class'] ?? 'fa-layer-group') ?>" placeholder="fa-code">
                            </div>
                            <div class="form-group">
                                <label>Tartib raqami</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editService['sort_order'] ?? '0') ?>">
                            </div>
                            <div class="form-group flex items-center mt-8">
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($editService['is_active'] ?? 1) ? 'checked' : '' ?> class="mr-2">
                                <label for="is_active">Faol</label>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex gap-2">
                            <button type="submit" name="save_service" class="btn btn-primary">Saqlash</button>
                            <a href="services.php" class="btn btn-outline">Bekor qilish</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Services List -->
            <?php if ($action === 'list' && !$editService): ?>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nomi</th>
                                <th>Slug</th>
                                <th>Narx</th>
                                <th>Icon</th>
                                <th>Tartib</th>
                                <th>Holat</th>
                                <th>Amallar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $svc): ?>
                            <tr>
                                <td><?= $svc['id'] ?></td>
                                <td><?= htmlspecialchars($svc['title_uz']) ?></td>
                                <td><code><?= htmlspecialchars($svc['slug']) ?></code></td>
                                <td><?= number_format($svc['price_from'], 0, ',', ' ') ?> so'm</td>
                                <td><i class="fas <?= htmlspecialchars($svc['icon_class']) ?>"></i></td>
                                <td><?= $svc['sort_order'] ?></td>
                                <td>
                                    <span class="badge <?= $svc['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $svc['is_active'] ? 'Faol' : 'Nofaol' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?action=edit&id=<?= $svc['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('O\'chirishni xohlaysizmi?')">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                        <button type="submit" name="delete_service" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
