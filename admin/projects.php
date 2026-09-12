<?php
/**
 * Admin - Loyihalarni (Portfolio) boshqarish
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

    if (isset($_POST['save_project'])) {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title']);
        $slug = generateSlug($_POST['slug'] ?: $title);
        $client_name = trim($_POST['client_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $service_id = (int)($_POST['service_id'] ?? 0);
        $project_url = trim($_POST['project_url'] ?? '');
        $github_url = trim($_POST['github_url'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Texnologiyalar (JSON)
        $technologies = [];
        if (!empty($_POST['technologies'])) {
            $technologies = array_map('trim', explode(',', $_POST['technologies']));
        }
        
        // Rasm yuklash
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_image = uploadFile($_FILES['cover_image'], 'projects');
        }

        try {
            if ($id > 0) {
                // Yangilash
                if ($cover_image) {
                    // Eski rasmni o'chirish
                    $stmt = $pdo->prepare("SELECT cover_image FROM projects WHERE id=?");
                    $stmt->execute([$id]);
                    $old = $stmt->fetch();
                    if ($old && $old['cover_image'] && file_exists(__DIR__ . '/../uploads/' . $old['cover_image'])) {
                        unlink(__DIR__ . '/../uploads/' . $old['cover_image']);
                    }
                }
                
                $sql = "UPDATE projects SET title=?, slug=?, client_name=?, description=?, short_description=?, service_id=?, project_url=?, github_url=?, is_featured=?, is_active=?";
                $params = [$title, $slug, $client_name, $description, $short_description, $service_id, $project_url, $github_url, $is_featured, $is_active];
                
                if ($cover_image) {
                    $sql .= ", cover_image=?";
                    $params[] = $cover_image;
                }
                $sql .= " WHERE id=?";
                $params[] = $id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // Texnologiyalarni yangilash
                if (!empty($technologies)) {
                    $stmt = $pdo->prepare("UPDATE projects SET technologies=? WHERE id=?");
                    $stmt->execute([json_encode($technologies), $id]);
                }
                
                logAudit($pdo, getCurrentUserId(), 'project_updated', 'projects', $id);
                $message = 'Loyiha muvaffaqiyatli yangilandi!';
            } else {
                // Yangi yaratish
                $stmt = $pdo->prepare("INSERT INTO projects (title, slug, client_name, description, short_description, cover_image, service_id, technologies, project_url, github_url, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title, $slug, $client_name, $description, $short_description, 
                    $cover_image, $service_id, json_encode($technologies), 
                    $project_url, $github_url, $is_featured, $is_active
                ]);
                logAudit($pdo, getCurrentUserId(), 'project_created', 'projects', $pdo->lastInsertId());
                $message = 'Yangi loyiha qo\'shildi!';
            }
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik: ' . $e->getMessage();
            $messageType = 'error';
        }
    }

    if (isset($_POST['delete_project']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        // Rasmni o'chirish
        $stmt = $pdo->prepare("SELECT cover_image FROM projects WHERE id=?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if ($project && $project['cover_image'] && file_exists(__DIR__ . '/../uploads/' . $project['cover_image'])) {
            unlink(__DIR__ . '/../uploads/' . $project['cover_image']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id=?");
        $stmt->execute([$id]);
        logAudit($pdo, getCurrentUserId(), 'project_deleted', 'projects', $id);
        $message = 'Loyiha o\'chirildi!';
        $messageType = 'success';
    }
}

// Ro'yxatni olish
$projects = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT p.*, s.title_uz as service_name FROM projects p LEFT JOIN services s ON p.service_id = s.id ORDER BY p.is_featured DESC, p.created_at DESC");
    $projects = $stmt->fetchAll();
}

// Tahrirlash uchun ma'lumot olish
$editProject = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->execute([(int)$_GET['id']]);
    $editProject = $stmt->fetch();
}

// Xizmatlarni olish (select uchun)
$servicesStmt = $pdo->query("SELECT id, title_uz FROM services WHERE is_active=1 ORDER BY title_uz");
$services = $servicesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyihalar - WebHub Admin</title>
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
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .project-thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
        .tech-tag { display: inline-block; padding: 0.2rem 0.5rem; background: var(--bg-secondary); border-radius: 4px; font-size: 0.75rem; margin-right: 0.25rem; }
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
                <a href="projects.php" class="active"><i class="fas fa-briefcase mr-2"></i> Loyihalar</a>
                <a href="#"><i class="fas fa-users mr-2"></i> Foydalanuvchilar</a>
                <a href="#"><i class="fas fa-cog mr-2"></i> Sozlamalar</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Chiqish</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Loyihalarni boshqarish</h1>
                <?php if ($action !== 'create' && !$editProject): ?>
                    <a href="?action=create" class="btn btn-primary"><i class="fas fa-plus mr-2"></i> Yangi loyiha</a>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Create/Edit Form -->
            <?php if ($action === 'create' || $editProject): ?>
                <div class="card">
                    <h2 class="text-lg font-semibold mb-4"><?= $editProject ? 'Tahrirlash' : 'Yangi loyiha' ?></h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $editProject['id'] ?? '' ?>">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Nomi</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editProject['title'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug (URL)</label>
                                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editProject['slug'] ?? '') ?>" placeholder="my-project">
                            </div>
                            <div class="form-group">
                                <label>Mijoz nomi</label>
                                <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($editProject['client_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Xizmat turi</label>
                                <select name="service_id" class="form-control">
                                    <option value="">Tanlang...</option>
                                    <?php foreach ($services as $svc): ?>
                                        <option value="<?= $svc['id'] ?>" <?= ($editProject['service_id'] ?? 0) == $svc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($svc['title_uz']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Muhokama (qisqa)</label>
                                <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($editProject['short_description'] ?? '') ?>" maxlength="255">
                            </div>
                            <div class="form-group">
                                <label>Muqova rasm</label>
                                <?php if ($editProject && $editProject['cover_image']): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/<?= htmlspecialchars($editProject['cover_image']) ?>" alt="Cover" class="project-thumb">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                            </div>
                            <div class="form-group col-span-2">
                                <label>Tavsif (to'liq)</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($editProject['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Texnologiyalar (vergul bilan ajrating)</label>
                                <input type="text" name="technologies" class="form-control" value="<?= htmlspecialchars(implode(', ', json_decode($editProject['technologies'] ?? '[]', true) ?: [])) ?>" placeholder="PHP, Laravel, Vue.js">
                            </div>
                            <div class="form-group">
                                <label>Loyiha URL</label>
                                <input type="url" name="project_url" class="form-control" value="<?= htmlspecialchars($editProject['project_url'] ?? '') ?>" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>GitHub URL</label>
                                <input type="url" name="github_url" class="form-control" value="<?= htmlspecialchars($editProject['github_url'] ?? '') ?>" placeholder="https://github.com/...">
                            </div>
                            <div class="form-group flex items-center mt-8">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1" <?= ($editProject['is_featured'] ?? 0) ? 'checked' : '' ?> class="mr-2">
                                <label for="is_featured">Tanlangan (Featured)</label>
                            </div>
                            <div class="form-group flex items-center mt-8">
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($editProject['is_active'] ?? 1) ? 'checked' : '' ?> class="mr-2">
                                <label for="is_active">Faol</label>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex gap-2">
                            <button type="submit" name="save_project" class="btn btn-primary">Saqlash</button>
                            <a href="projects.php" class="btn btn-outline">Bekor qilish</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Projects List -->
            <?php if ($action === 'list' && !$editProject): ?>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Rasm</th>
                                <th>Nomi</th>
                                <th>Mijoz</th>
                                <th>Xizmat</th>
                                <th>Texnologiyalar</th>
                                <th>Holat</th>
                                <th>Amallar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $proj): ?>
                            <tr>
                                <td><?= $proj['id'] ?></td>
                                <td>
                                    <?php if ($proj['cover_image']): ?>
                                        <img src="../uploads/<?= htmlspecialchars($proj['cover_image']) ?>" alt="<?= htmlspecialchars($proj['title']) ?>" class="project-thumb">
                                    <?php else: ?>
                                        <div class="project-thumb bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-medium"><?= htmlspecialchars($proj['title']) ?></div>
                                    <?php if ($proj['is_featured']): ?>
                                        <span class="badge badge-warning text-xs mt-1">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($proj['client_name'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($proj['service_name'] ?: '-') ?></td>
                                <td>
                                    <?php 
                                    $techs = json_decode($proj['technologies'] ?? '[]', true) ?: [];
                                    foreach (array_slice($techs, 0, 3) as $tech): 
                                    ?>
                                        <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($techs) > 3): ?>
                                        <span class="tech-tag">+<?= count($techs) - 3 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $proj['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $proj['is_active'] ? 'Faol' : 'Nofaol' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?action=edit&id=<?= $proj['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('O\'chirishni xohlaysizmi?')">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $proj['id'] ?>">
                                        <button type="submit" name="delete_project" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (empty($projects)): ?>
                        <p class="text-muted text-center py-8">Loyihalar topilmadi</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
