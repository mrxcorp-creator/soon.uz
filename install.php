<?php
require_once __DIR__ . '/config/init.php';
if (file_exists(__DIR__ . '/install.lock')) { die('<h1 style="text-align:center;padding:50px;">O\'rnatish allaqachon yakunlangan.</h1>'); }
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = []; $success = '';
$db_host = isset($_POST['db_host']) ? $_POST['db_host'] : (isset($_SESSION['db_host']) ? $_SESSION['db_host'] : 'localhost');
$db_name = isset($_POST['db_name']) ? $_POST['db_name'] : '';
$db_user = isset($_POST['db_user']) ? $_POST['db_user'] : '';
$db_pass = isset($_POST['db_pass']) ? $_POST['db_pass'] : '';
$admin_login = isset($_POST['admin_login']) ? $_POST['admin_login'] : '';
$admin_password = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
$admin_name = isset($_POST['admin_name']) ? $_POST['admin_name'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['db_host'] = $db_host; $_SESSION['db_name'] = $db_name;
    $_SESSION['db_user'] = $db_user; $_SESSION['db_pass'] = $db_pass;
}
?>
<!DOCTYPE html><html lang="uz"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WebHub.uz - O'rnatish</title>
<style>:root{--primary:#6366f1;--bg:#f8fafc;--text:#1e293b;--card-bg:rgba(255,255,255,0.8)}@media(prefers-color-scheme:dark){:root{--bg:#0f172a;--text:#f1f5f9;--card-bg:rgba(30,41,59,0.8)}}*{box-sizing:border-box;margin:0;padding:0}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}.installer{max-width:500px;width:100%;background:var(--card-bg);backdrop-filter:blur(20px);border:1px solid rgba(99,102,241,0.2);border-radius:16px;padding:40px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)}h1{font-size:28px;margin-bottom:10px;text-align:center}.form-group{margin-bottom:20px}label{display:block;margin-bottom:8px;font-weight:500}input{width:100%;padding:12px 16px;border:1px solid rgba(99,102,241,0.2);border-radius:8px;font-size:16px;background:var(--card-bg);color:var(--text)}input:focus{outline:none;border-color:var(--primary)}button{width:100%;padding:14px;background:var(--primary);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer}button:hover{background:#4f46e5}.error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:12px;border-radius:8px;margin-bottom:20px}.success{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;padding:12px;border-radius:8px;margin-bottom:20px}.progress-bar{height:4px;background:rgba(99,102,241,0.2);border-radius:2px;margin-bottom:30px}.progress-fill{height:100%;background:var(--primary);transition:width 0.3s}</style></head><body>
<div class="installer"><h1>WebHub.uz</h1><p style="text-align:center;margin-bottom:30px;opacity:0.7;">Saytni o'rnatish</p>
<div class="progress-bar"><div class="progress-fill" style="width:<?php echo ($step*20); ?>%"></div></div>
<?php if(!empty($errors)):?><div class="error"><?php foreach($errors as $e):?><div><?php echo htmlspecialchars($e);?></div><?php endforeach;?></div><?php endif;?>
<?php if($success):?><div class="success"><?php echo htmlspecialchars($success);?></div><?php endif;?>
<?php if($step===1):?>
<h2 style="margin-bottom:20px;">Server talablari</h2>
<?php $reqs=['PHP>=8.0'=>version_compare(PHP_VERSION,'8.0.0','>='),'PDO MySQL'=>extension_loaded('pdo_mysql'),'GD'=>extension_loaded('gd'),'FileInfo'=>extension_loaded('fileinfo'),'JSON'=>extension_loaded('json'),'Session'=>extension_loaded('session'),'Yozish ruxsati'=>is_writable(__DIR__)];$ok=true;foreach($reqs as $r=>$p):if(!$p)$ok=false;?>
<div style="padding:10px;margin-bottom:8px;border-radius:6px;background:<?php echo $p?'rgba(16,185,129,0.1)':'rgba(239,68,68,0.1)';?>;"><?php echo $p?'✓':'✗';?> <?php echo htmlspecialchars($r);?></div>
<?php endforeach;?>
<form method="get" style="margin-top:20px;"><input type="hidden" name="step" value="2"><button type="submit" <?php echo $ok?'':'disabled';?>>Davom etish</button></form>
<?php elseif($step===2):?>
<form method="post"><input type="hidden" name="step" value="3"><h2 style="margin-bottom:20px;">Ma'lumotlar bazasi</h2>
<div class="form-group"><label>MySQL Host</label><input type="text" name="db_host" value="<?php echo htmlspecialchars($db_host);?>" required></div>
<div class="form-group"><label>Database nomi</label><input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name);?>" required></div>
<div class="form-group"><label>Foydalanuvchi</label><input type="text" name="db_user" value="<?php echo htmlspecialchars($db_user);?>" required></div>
<div class="form-group"><label>Parol</label><input type="password" name="db_pass" value="<?php echo htmlspecialchars($db_pass);?>"></div>
<button type="submit">Ulanishni tekshirish</button></form>
<form method="get" style="margin-top:10px;"><input type="hidden" name="step" value="1"><button type="submit" style="background:transparent;color:var(--text);border:1px solid rgba(99,102,241,0.2);">Orqaga</button></form>
<?php elseif($step===3):
try{$dsn="mysql:host={$db_host};charset=utf8mb4";$pdo=new PDO($dsn,$db_user,$db_pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$db_name}`");
$sql="CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,google_id VARCHAR(255) UNIQUE,name VARCHAR(255) NOT NULL,email VARCHAR(255) NOT NULL,phone VARCHAR(20),avatar VARCHAR(500),status ENUM('active','blocked') DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS admins(id INT AUTO_INCREMENT PRIMARY KEY,login VARCHAR(100) UNIQUE NOT NULL,password_hash VARCHAR(255) NOT NULL,name VARCHAR(255),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS services(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255) NOT NULL,description TEXT,price DECIMAL(10,2) DEFAULT 0,features_json JSON,sort_order INT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS portfolio(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255) NOT NULL,description TEXT,image VARCHAR(500),client_name VARCHAR(255),link VARCHAR(500),category VARCHAR(100),sort_order INT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS blog_posts(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255) NOT NULL,body LONGTEXT,image VARCHAR(500),status ENUM('draft','published') DEFAULT 'draft',views INT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS applications(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,service_id INT,name VARCHAR(255),phone VARCHAR(20),description TEXT,status ENUM('new','in_review','approved','completed','cancelled') DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id)REFERENCES users(id)ON DELETE SET NULL,FOREIGN KEY(service_id)REFERENCES services(id)ON DELETE SET NULL)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS chat_threads(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id)REFERENCES users(id)ON DELETE CASCADE)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS chat_messages(id INT AUTO_INCREMENT PRIMARY KEY,thread_id INT NOT NULL,sender_type ENUM('admin','user') NOT NULL,sender_id INT,message TEXT,file_path VARCHAR(500),is_read BOOLEAN DEFAULT FALSE,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(thread_id)REFERENCES chat_threads(id)ON DELETE CASCADE)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS notifications(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,title VARCHAR(255),message TEXT,is_read BOOLEAN DEFAULT FALSE,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id)REFERENCES users(id)ON DELETE CASCADE)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS site_settings(id INT AUTO_INCREMENT PRIMARY KEY,setting_key VARCHAR(100) UNIQUE NOT NULL,setting_value TEXT,setting_type VARCHAR(50) DEFAULT 'text',updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS api_tokens(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,token VARCHAR(255) UNIQUE NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,expires_at TIMESTAMP,FOREIGN KEY(user_id)REFERENCES users(id)ON DELETE CASCADE)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS audit_log(id INT AUTO_INCREMENT PRIMARY KEY,admin_id INT,action VARCHAR(255),details TEXT,ip_address VARCHAR(45),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(admin_id)REFERENCES admins(id)ON DELETE SET NULL)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS homepage_stats(id INT AUTO_INCREMENT PRIMARY KEY,stat_key VARCHAR(100) UNIQUE NOT NULL,stat_value INT DEFAULT 0,auto_compute BOOLEAN DEFAULT TRUE,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS media_library(id INT AUTO_INCREMENT PRIMARY KEY,file_name VARCHAR(255) NOT NULL,file_path VARCHAR(500) NOT NULL,file_type VARCHAR(50),file_size INT,uploaded_by INT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(uploaded_by)REFERENCES admins(id)ON DELETE SET NULL)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$stmts=array_filter(array_map('trim',explode(';',$sql)));foreach($stmts as $s){if(!empty($s))$pdo->exec($s);}
$defaults=[['site_name','WebHub.uz','text'],['meta_description','IT xizmatlar','text'],['hero_title','Kelajak IT yechimlari','text'],['hero_subtitle','Biznesingizni raqamli dunyoda rivojlantiramiz','text'],['primary_color','#6366f1','color'],['projects_count','0','number'],['clients_count','0','number']];
$stmt=$pdo->prepare("INSERT IGNORE INTO site_settings(setting_key,setting_value,setting_type)VALUES(?,?,?)");foreach($defaults as $d)$stmt->execute($d);
$stats=[['projects_count',0,1],['clients_count',0,1]];
$stmt=$pdo->prepare("INSERT IGNORE INTO homepage_stats(stat_key,stat_value,auto_compute)VALUES(?,?,?)");foreach($stats as $s)$stmt->execute($s);
$svcs=[['Veb-sayt yaratish','Zamonaviy veb-saytlar',500000,'["Responsive dizayn","SEO optimizatsiya","Tez yuklanish"]'],['Telegram bot','Biznes uchun Telegram botlar',300000,'["Avtomatizatsiya","To\'lov tizimi","Admin panel"]'],['AI yechimlar','Sun\'iy intellekt yechimlari',1000000,'["Chatbotlar","Ma\'lumotlar tahlili","Avtomatlashtirish"]'],['Mobil ilova','iOS va Android ilovalar',800000,'["Native development","Cross-platform","UI/UX dizayn"]'];
$stmt=$pdo->prepare("INSERT INTO services(title,description,price,features_json,sort_order)VALUES(?,?,?,?,?)");foreach($svcs as $i=>$svc)$stmt->execute([...$svc,$i]);
$_SESSION['db_ok']=true;$success='Ma\'lumotlar bazasi yaratildi!';}catch(PDOException $e){$errors[]='Xatolik: '.$e->getMessage();}
if(empty($errors)):<form method="get"><input type="hidden" name="step" value="4"><button type="submit">Davom etish</button></form>
<?php else:?><form method="get"><input type="hidden" name="step" value="2"><button type="submit">Qayta urinish</button></form><?php endif;?>
<?php elseif($step===4):?>
<form method="post"><input type="hidden" name="step" value="5"><h2 style="margin-bottom:20px;">Admin hisobi</h2>
<div class="form-group"><label>Login</label><input type="text" name="admin_login" required minlength="3"></div>
<div class="form-group"><label>Ism</label><input type="text" name="admin_name"></div>
<div class="form-group"><label>Parol</label><input type="password" name="admin_password" required minlength="6"></div>
<button type="submit">Admin yaratish</button></form>
<form method="get" style="margin-top:10px;"><input type="hidden" name="step" value="3"><button type="submit" style="background:transparent;color:var(--text);border:1px solid rgba(99,102,241,0.2);">Orqaga</button></form>
<?php elseif($step===5):
if($_SERVER['REQUEST_METHOD']==='POST'&&!empty($admin_login)&&!empty($admin_password)){try{$dsn="mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";$pdo=new PDO($dsn,$db_user,$db_pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$hash=password_hash($admin_password,PASSWORD_BCRYPT);$stmt=$pdo->prepare("INSERT INTO admins(login,password_hash,name)VALUES(?,?,?)");$stmt->execute([$admin_login,$hash,$admin_name]);$_SESSION['admin_ok']=true;$success='Admin yaratildi!';}catch(PDOException $e){$errors[]='Xatolik: '.$e->getMessage();}}
if(isset($_SESSION['admin_ok'])):?><h2 style="margin-bottom:20px;">Yakunlash</h2><p style="margin-bottom:20px;font-size:14px;opacity:0.7;">O'rnatish tayyor.</p>
<form method="post"><button type="submit">Yakunlash</button></form>
<?php else:?><p>Kutilmoqda...</p><?php endif;?>
<?php else:
try{$dsn="mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";$pdo=new PDO($dsn,$db_user,$db_pass);
$cfg="<?php\ndefine('DB_HOST','".addslashes($db_host)."');\ndefine('DB_NAME','".addslashes($db_name)."');\ndefine('DB_USER','".addslashes($db_user)."');\ndefine('DB_PASS','".addslashes($db_pass)."');\ndefine('SITE_URL','http://'.\$_SERVER['HTTP_HOST']);\ndefine('UPLOAD_DIR',__DIR__.'/uploads');\ndefine('MAX_UPLOAD_SIZE',10485760);\nfunction getDB(){static \$pdo=null;if(\$pdo===null){\$dsn='mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';\$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];\$pdo=new PDO(\$dsn,DB_USER,DB_PASS,\$options);}return \$pdo;}\n";
file_put_contents(__DIR__.'/config/config.php',$cfg);file_put_contents(__DIR__.'/install.lock','installed '.date('Y-m-d H:i:s'));session_destroy();$success='<strong>O\'rnatish yakunlandi!</strong> Endi <a href="<?php echo BASE_URL; ?>/admin/login.php" style="color:var(--primary);">Admin panelga</a> kirishingiz mumkin.';}catch(Exception $e){$errors[]='Xatolik: '.$e->getMessage();}
?><div class="success" style="text-align:center;"><h3 style="margin-bottom:10px;">Tabriklaymiz!</h3><p><?php echo $success;?></p><p style="margin-top:20px;"><a href="<?php echo BASE_URL; ?>/" class="btn btn-primary" style="display:inline-block;padding:12px 24px;background:var(--primary);color:white;border-radius:8px;text-decoration:none;">Bosh sahifa</a></p></div>
<?php endif;?></div></body></html>
