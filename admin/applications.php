<?php
/**
 * Admin - Arizalarni boshqarish
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

    if (isset($_POST['update_status'])) {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'];
        $note = trim($_POST['admin_note'] ?? '');

        try {
            $stmt = $pdo->prepare("UPDATE applications SET status=?, admin_note=? WHERE id=?");
            $stmt->execute([$status, $note, $id]);
            logAudit($pdo, getCurrentUserId(), 'application_status_updated', 'applications', $id);
            
            // Mijozga xabar yuborish (ixtiyoriy)
            if (isset($_POST['notify_client']) && !empty($note)) {
                $app = getApplicationById($pdo, $id);
                if ($app) {
                    sendMessage($pdo, getCurrentUserId(), $app['user_id'] ?? null, $note, $id);
                }
            }
            
            $message = 'Ariza holati yangilandi!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Xatolik: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Filtrlar
$filterStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

// Arizalarni olish
$where = ['1=1'];
$params = [];

if ($filterStatus !== 'all') {
    $where[] = "a.status = ?";
    $params[] = $filterStatus;
}

if ($searchQuery) {
    $where[] = "(a.name LIKE ? OR a.email LIKE ? OR s.title_uz LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql = "SELECT a.*, s.title_uz as service_name, u.full_name as user_name 
        FROM applications a 
        LEFT JOIN services s ON a.service_id = s.id 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Bitta arizani ko'rish
$selectedApp = null;
if ($action === 'view' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT a.*, s.title_uz as service_name, u.full_name as user_name, u.email as user_email, u.phone as user_phone 
                          FROM applications a 
                          LEFT JOIN services s ON a.service_id = s.id 
                          LEFT JOIN users u ON a.user_id = u.id 
                          WHERE a.id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $selectedApp = $stmt->fetch();
    
    if ($selectedApp) {
        // Xabarlarni olish
        $msgStmt = $pdo->prepare("SELECT m.*, sender.full_name as sender_name, receiver.full_name as receiver_name 
                                 FROM messages m 
                                 LEFT JOIN users sender ON m.sender_id = sender.id 
                                 LEFT JOIN users receiver ON m.receiver_id = receiver.id 
                                 WHERE m.application_id = ? 
                                 ORDER BY m.created_at ASC");
        $msgStmt->execute([(int)$_GET['id']]);
        $messages = $msgStmt->fetchAll();
    }
}

// Status ranglari
$statusColors = [
    'new' => 'bg-blue-100 text-blue-800',
    'contacted' => 'bg-yellow-100 text-yellow-800',
    'in_progress' => 'bg-purple-100 text-purple-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];

$statusLabels = [
    'new' => 'Yangi',
    'contacted' => 'Aloqa o\'rnatildi',
    'in_progress' => 'Jarayonda',
    'completed' => 'Yakunlandi',
    'cancelled' => 'Bekor qilindi'
];
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arizalar - WebHub Admin</title>
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
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .chat-box { max-height: 400px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; background: var(--bg-secondary); }
        .chat-message { margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; background: var(--bg-primary); }
        .chat-message.mine { background: var(--primary); color: white; margin-left: 2rem; }
        .chat-message.theirs { margin-right: 2rem; }
        .chat-meta { font-size: 0.75rem; opacity: 0.7; margin-top: 0.25rem; }
        .filter-bar { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
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
                <a href="applications.php" class="active"><i class="fas fa-file-alt mr-2"></i> Arizalar</a>
                <a href="#"><i class="fas fa-briefcase mr-2"></i> Loyihalar</a>
                <a href="#"><i class="fas fa-users mr-2"></i> Foydalanuvchilar</a>
                <a href="#"><i class="fas fa-cog mr-2"></i> Sozlamalar</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Chiqish</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($action === 'view' && $selectedApp): ?>
                <!-- View Single Application -->
                <div class="mb-6">
                    <a href="applications.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-2"></i> Orqaga</a>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <div class="card">
                            <h2 class="text-lg font-semibold mb-4">Ariza ma'lumotlari</h2>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-muted">Mijoz</dt>
                                    <dd class="font-medium"><?= htmlspecialchars($selectedApp['name']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-muted">Email</dt>
                                    <dd><a href="mailto:<?= htmlspecialchars($selectedApp['email']) ?>" class="text-primary"><?= htmlspecialchars($selectedApp['email']) ?></a></dd>
                                </div>
                                <?php if ($selectedApp['phone']): ?>
                                <div>
                                    <dt class="text-sm text-muted">Telefon</dt>
                                    <dd><?= htmlspecialchars($selectedApp['phone']) ?></dd>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <dt class="text-sm text-muted">Xizmat</dt>
                                    <dd><?= htmlspecialchars($selectedApp['service_name']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-muted">Byudjet</dt>
                                    <dd>
                                        <?php if ($selectedApp['budget_min'] || $selectedApp['budget_max']): ?>
                                            <?= number_format($selectedApp['budget_min'], 0, ',', ' ') ?> - <?= number_format($selectedApp['budget_max'], 0, ',', ' ') ?> so'm
                                        <?php else: ?>
                                            <span class="text-muted">Ko'rsatilmagan</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-muted">Holat</dt>
                                    <dd><span class="badge <?= $statusColors[$selectedApp['status']] ?>"><?= $statusLabels[$selectedApp['status']] ?></span></dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-muted">Xabar</dt>
                                    <dd class="text-sm"><?= nl2br(htmlspecialchars($selectedApp['message'])) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-muted">Yuborilgan</dt>
                                    <dd><?= date('d.m.Y H:i', strtotime($selectedApp['created_at'])) ?></dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Update Status Form -->
                        <div class="card">
                            <h2 class="text-lg font-semibold mb-4">Holatni yangilash</h2>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $selectedApp['id'] ?>">
                                
                                <div class="form-group">
                                    <label>Yangi holat</label>
                                    <select name="status" class="form-control">
                                        <?php foreach ($statusLabels as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $selectedApp['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Izoh (admin uchun)</label>
                                    <textarea name="admin_note" class="form-control" rows="3"><?= htmlspecialchars($selectedApp['admin_note'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="notify_client" value="1" class="mr-2">
                                        Mijozga xabar yuborish (izoh matni bilan)
                                    </label>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-primary">Saqlash</button>
                            </form>
                        </div>
                    </div>

                    <div>
                        <!-- Chat History -->
                        <div class="card">
                            <h2 class="text-lg font-semibold mb-4">Yozishmalar</h2>
                            <div class="chat-box">
                                <?php if (empty($messages)): ?>
                                    <p class="text-muted text-center py-4">Hali xabarlar yo'q</p>
                                <?php else: ?>
                                    <?php foreach ($messages as $msg): ?>
                                        <div class="chat-message <?= $msg['sender_id'] == getCurrentUserId() ? 'mine' : 'theirs' ?>">
                                            <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                            <div class="chat-meta">
                                                <?= $msg['sender_name'] ?> • <?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Send Message Form -->
                            <form method="POST" action="../api/send-message.php" class="mt-4">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="application_id" value="<?= $selectedApp['id'] ?>">
                                <div class="flex gap-2">
                                    <input type="text" name="message" class="form-control" placeholder="Xabar yozing..." required>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Applications List -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Arizalar</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="filter-bar">
                    <div class="flex gap-2">
                        <a href="?status=all" class="btn btn-sm <?= $filterStatus === 'all' ? 'btn-primary' : 'btn-outline' ?>">Barchasi</a>
                        <a href="?status=new" class="btn btn-sm <?= $filterStatus === 'new' ? 'btn-primary' : 'btn-outline' ?>">Yangi</a>
                        <a href="?status=in_progress" class="btn btn-sm <?= $filterStatus === 'in_progress' ? 'btn-primary' : 'btn-outline' ?>">Jarayonda</a>
                        <a href="?status=completed" class="btn btn-sm <?= $filterStatus === 'completed' ? 'btn-primary' : 'btn-outline' ?>">Yakunlandi</a>
                    </div>
                    
                    <form method="GET" class="flex gap-2">
                        <input type="text" name="q" class="form-control" placeholder="Qidiruv..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mijoz</th>
                                <th>Xizmat</th>
                                <th>Aloqa</th>
                                <th>Byudjet</th>
                                <th>Holat</th>
                                <th>Sana</th>
                                <th>Amallar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>#<?= $app['id'] ?></td>
                                <td>
                                    <div class="font-medium"><?= htmlspecialchars($app['name']) ?></div>
                                    <?php if ($app['user_name']): ?>
                                        <div class="text-xs text-muted"><?= htmlspecialchars($app['user_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($app['service_name']) ?></td>
                                <td>
                                    <div class="text-sm"><?= htmlspecialchars($app['email']) ?></div>
                                    <?php if ($app['phone']): ?>
                                        <div class="text-xs text-muted"><?= htmlspecialchars($app['phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($app['budget_min'] || $app['budget_max']): ?>
                                        <div class="text-sm"><?= number_format($app['budget_min'], 0, ',', ' ') ?> - <?= number_format($app['budget_max'], 0, ',', ' ') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusColors[$app['status']] ?>"><?= $statusLabels[$app['status']] ?></span></td>
                                <td><?= date('d.m.Y', strtotime($app['created_at'])) ?></td>
                                <td>
                                    <a href="?action=view&id=<?= $app['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye mr-1"></i> Ko'rish</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (empty($applications)): ?>
                        <p class="text-muted text-center py-8">Arizalar topilmadi</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
