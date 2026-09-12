<?php
require_once __DIR__ . '/config/init.php';

$services = getServices(4);
$portfolio = getPortfolio(6);
$blogPosts = getBlogPosts(3);
$stats = getHomepageStats();
$flashMessage = getFlashMessage();
$siteName = getSetting('site_name', 'WebHub.uz');
$metaDescription = getSetting('meta_description', 'IT xizmatlar - veb-saytlar, Telegram botlar, AI yechimlar');
$heroTitle = getSetting('hero_title', 'Kelajak IT yechimlari');
$heroSubtitle = getSetting('hero_subtitle', 'Biznesingizni raqamli dunyoda rivojlantiramiz');
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($siteName); ?> - <?php echo e($heroTitle); ?></title>
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/variables.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/assets/css/components.css">
</head>
<body>
    <nav class="glass" style="position:fixed;top:0;left:0;right:0;z-index:var(--z-sticky);padding:var(--space-4) 0;">
        <div class="container flex justify-between items-center">
            <a href="<?php echo BASE_URL; ?>/" style="font-size:var(--text-xl);font-weight:700;color:var(--primary);"><?php echo e($siteName); ?></a>
            <ul class="flex gap-6" style="list-style:none;margin:0;padding:0;">
                <li><a href="#services" style="color:var(--text-secondary);">Xizmatlar</a></li>
                <li><a href="#portfolio" style="color:var(--text-secondary);">Portfolio</a></li>
                <li><a href="#blog" style="color:var(--text-secondary);">Blog</a></li>
                <li><a href="#contact" style="color:var(--text-secondary);">Aloqa</a></li>
                <li><button id="theme-toggle" class="btn btn-sm btn-secondary">🌙</button></li>
                <li><a href="<?php echo BASE_URL; ?>/user/login.php" class="btn btn-sm btn-primary">Kirish</a></li>
            </ul>
        </div>
    </nav>
    <section class="section" style="padding-top:calc(var(--space-16)+80px);min-height:100vh;display:flex;align-items:center;">
        <div class="container">
            <div style="max-width:800px;margin:0 auto;text-align:center;">
                <h1 class="scroll-animate" style="font-size:clamp(var(--text-3xl),5vw,var(--text-4xl));margin-bottom:var(--space-6);"><?php echo e($heroTitle); ?></h1>
                <p class="scroll-animate" style="font-size:var(--text-lg);color:var(--text-secondary);margin-bottom:var(--space-8);"><?php echo e($heroSubtitle); ?></p>
                <div class="scroll-animate flex gap-4 justify-center" style="flex-wrap:wrap;">
                    <a href="#contact" class="btn btn-lg btn-primary">Boshlash</a>
                    <a href="#services" class="btn btn-lg btn-outline">Xizmatlar</a>
                </div>
                <div class="scroll-animate grid grid-cols-2 gap-8" style="margin-top:var(--space-16);max-width:400px;margin-left:auto;margin-right:auto;">
                    <div class="text-center"><div style="font-size:var(--text-3xl);font-weight:700;color:var(--primary);" data-counter="<?php echo $stats['projects_count']??0; ?>"><?php echo ($stats['projects_count']??0); ?>+</div><div style="color:var(--text-muted);">Loyihalar</div></div>
                    <div class="text-center"><div style="font-size:var(--text-3xl);font-weight:700;color:var(--primary);" data-counter="<?php echo $stats['clients_count']??0; ?>"><?php echo ($stats['clients_count']??0); ?>+</div><div style="color:var(--text-muted);">Mijozlar</div></div>
                </div>
            </div>
        </div>
    </section>
    <section id="services" class="section" style="background:var(--bg-secondary);">
        <div class="container">
            <h2 class="text-center scroll-animate" style="margin-bottom:var(--space-4);">Xizmatlarimiz</h2>
            <p class="text-center scroll-animate" style="color:var(--text-secondary);margin-bottom:var(--space-12);max-width:600px;margin-left:auto;margin-right:auto;">Biznesingiz uchun professional IT yechimlar</p>
            <div class="grid grid-cols-1" style="gap:var(--space-6);">
                <?php foreach($services as $service): ?>
                <div class="card glass-card scroll-animate">
                    <h3 style="font-size:var(--text-xl);margin-bottom:var(--space-2);"><?php echo e($service['title']); ?></h3>
                    <p style="color:var(--text-secondary);margin-bottom:var(--space-4);"><?php echo e($service['description']); ?></p>
                    <div style="font-size:var(--text-2xl);font-weight:700;color:var(--primary);margin-bottom:var(--space-4);"><?php echo number_format($service['price'],0,'.',' '); ?> so'mdan</div>
                    <?php if($service['features_json']): ?><ul style="list-style:none;padding:0;margin-bottom:var(--space-6);"><?php foreach(json_decode($service['features_json'],true) as $feature): ?><li style="padding:var(--space-2) 0;color:var(--text-secondary);">✓ <?php echo e($feature); ?></li><?php endforeach; ?></ul><?php endif; ?>
                    <a href="#contact" class="btn btn-primary" onclick="selectService(<?php echo $service['id']; ?>)">Buyurtma berish</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section id="portfolio" class="section">
        <div class="container">
            <h2 class="text-center scroll-animate" style="margin-bottom:var(--space-4);">Portfolio</h2>
            <p class="text-center scroll-animate" style="color:var(--text-secondary);margin-bottom:var(--space-12);">Bajarilgan loyihalarimiz</p>
            <div class="grid grid-cols-1" style="gap:var(--space-6);">
                <?php if(empty($portfolio)): ?><div class="text-center scroll-animate" style="padding:var(--space-12);color:var(--text-muted);">Hozircha portfolio bo'sh.</div>
                <?php else: ?><?php foreach($portfolio as $item): ?>
                <div class="card glass-card scroll-animate">
                    <?php if($item['image']): ?><img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['title']); ?>" style="width:100%;height:200px;object-fit:cover;border-radius:var(--radius-lg);margin-bottom:var(--space-4);"><?php endif; ?>
                    <h3 style="margin-bottom:var(--space-2);"><?php echo e($item['title']); ?></h3>
                    <p style="color:var(--text-secondary);margin-bottom:var(--space-2);"><?php echo e($item['description']); ?></p>
                    <?php if($item['client_name']): ?><p style="font-size:var(--text-sm);color:var(--text-muted);margin-bottom:var(--space-4);">Mijoz: <?php echo e($item['client_name']); ?></p><?php endif; ?>
                    <?php if($item['link']): ?><a href="<?php echo e($item['link']); ?>" target="_blank" class="btn btn-sm btn-outline">Ko'rish</a><?php endif; ?>
                </div>
                <?php endforeach; ?><?php endif; ?>
            </div>
        </div>
    </section>
    <section class="section" style="background:var(--bg-secondary);">
        <div class="container">
            <h2 class="text-center scroll-animate" style="margin-bottom:var(--space-4);">Qanday ishlaymiz</h2>
            <p class="text-center scroll-animate" style="color:var(--text-secondary);margin-bottom:var(--space-12);">4 oddiy qadamda natijaga erishing</p>
            <div class="grid grid-cols-1" style="gap:var(--space-8);max-width:800px;margin:0 auto;">
                <?php $steps=[['1','Ariza qoldiring','Saytimizda ariza qoldiring'],['2','Muzokara','Ehtiyojlaringizni muhokama qilamiz'],['3','Ish boshlash','Shartnoma tuzamiz va loyihani boshlaymiz'],['4','Natija','Tayyor mahsulotni taqdim etamiz']]; foreach($steps as $i=>$step): ?>
                <div class="scroll-animate flex gap-6 items-center" style="transition-delay:<?php echo($i*100); ?>ms;">
                    <div style="width:60px;height:60px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-size:var(--text-xl);font-weight:700;flex-shrink:0;"><?php echo $step[0]; ?></div>
                    <div><h3 style="margin-bottom:var(--space-1);"><?php echo e($step[1]); ?></h3><p style="color:var(--text-secondary);margin:0;"><?php echo e($step[2]); ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section id="blog" class="section">
        <div class="container">
            <h2 class="text-center scroll-animate" style="margin-bottom:var(--space-4);">Blog</h2>
            <p class="text-center scroll-animate" style="color:var(--text-secondary);margin-bottom:var(--space-12);">So'nggi yangiliklar va maqolalar</p>
            <div class="grid grid-cols-1" style="gap:var(--space-6);">
                <?php if(empty($blogPosts)): ?><div class="text-center scroll-animate" style="padding:var(--space-12);color:var(--text-muted);">Hozircha yangiliklar yo'q.</div>
                <?php else: ?><?php foreach($blogPosts as $post): ?>
                <div class="card glass-card scroll-animate">
                    <?php if($post['image']): ?><img src="<?php echo e($post['image']); ?>" alt="<?php echo e($post['title']); ?>" style="width:100%;height:200px;object-fit:cover;border-radius:var(--radius-lg);margin-bottom:var(--space-4);"><?php endif; ?>
                    <h3 style="margin-bottom:var(--space-2);"><?php echo e($post['title']); ?></h3>
                    <p style="color:var(--text-secondary);margin-bottom:var(--space-4);"><?php echo e(mb_substr(strip_tags($post['body']),0,150)); ?>...</p>
                    <a href="<?php echo BASE_URL; ?>/blog/view.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline">Davomi</a>
                </div>
                <?php endforeach; ?><?php endif; ?>
            </div>
        </div>
    </section>
    <section id="contact" class="section" style="background:var(--bg-secondary);">
        <div class="container">
            <div style="max-width:600px;margin:0 auto;">
                <h2 class="text-center scroll-animate" style="margin-bottom:var(--space-4);">Bog'lanish</h2>
                <p class="text-center scroll-animate" style="color:var(--text-secondary);margin-bottom:var(--space-8);">Loyihangiz haqida gaplashamiz</p>
                <?php if($flashMessage['message']): ?><div class="scroll-animate" style="padding:var(--space-4);border-radius:var(--radius-lg);margin-bottom:var(--space-6);background:<?php echo $flashMessage['type']==='success'?'rgba(16,185,129,0.1)':'rgba(239,68,68,0.1)'; ?>;color:<?php echo $flashMessage['type']==='success'?'#10b981':'#ef4444'; ?>;"><?php echo e($flashMessage['message']); ?></div><?php endif; ?>
                <form class="card glass-card scroll-animate" data-ajax action="<?php echo BASE_URL; ?>/api/submit-application.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="service_id" id="selected-service" value="">
                    <div class="form-group" style="margin-bottom:var(--space-4);"><label for="name">Ism</label><input type="text" id="name" name="name" required placeholder="Ismingizni kiriting"></div>
                    <div class="form-group" style="margin-bottom:var(--space-4);"><label for="phone">Telefon</label><input type="tel" id="phone" name="phone" required placeholder="+998 90 123 45 67"></div>
                    <div class="form-group" style="margin-bottom:var(--space-4);"><label for="service_type">Xizmat turi</label><select id="service_type" name="service_type"><option value="">Tanlang</option><?php foreach(getServices() as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo e($s['title']); ?></option><?php endforeach; ?></select></div>
                    <div class="form-group" style="margin-bottom:var(--space-6);"><label for="message">Xabar</label><textarea id="message" name="message" rows="4" placeholder="Loyihangiz haqida qisqacha..."></textarea></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Yuborish</button>
                </form>
            </div>
        </div>
    </section>
    <footer class="glass" style="padding:var(--space-12) 0;border-top:1px solid var(--border-color);">
        <div class="container">
            <div class="grid grid-cols-1" style="gap:var(--space-8);">
                <div><h3 style="margin-bottom:var(--space-4);"><?php echo e($siteName); ?></h3><p style="color:var(--text-secondary);max-width:300px;"><?php echo e(getSetting('address','Farg\'ona, O\'zbekiston')); ?></p></div>
                <div><h4 style="margin-bottom:var(--space-4);">Aloqa</h4><ul style="list-style:none;padding:0;"><?php $phone=getSetting('phone','');$telegram=getSetting('telegram','');$instagram=getSetting('instagram',''); if($phone):?><li style="margin-bottom:var(--space-2);"><a href="tel:<?php echo e(str_replace([' ','-','(',')'],'',$phone)); ?>">📞 <?php echo e($phone); ?></a></li><?php endif; ?><?php if($telegram):?><li style="margin-bottom:var(--space-2);"><a href="https://t.me/<?php echo e(ltrim($telegram,'@')); ?>" target="_blank">✈️ Telegram</a></li><?php endif; ?><?php if($instagram):?><li style="margin-bottom:var(--space-2);"><a href="https://instagram.com/<?php echo e(ltrim($instagram,'@')); ?>" target="_blank">📷 Instagram</a></li><?php endif; ?></ul></div>
                <div><h4 style="margin-bottom:var(--space-4);">Hujjatlar</h4><ul style="list-style:none;padding:0;"><li style="margin-bottom:var(--space-2);"><a href="<?php echo BASE_URL; ?>/privacy.php">Maxfiylik siyosati</a></li><li style="margin-bottom:var(--space-2);"><a href="<?php echo BASE_URL; ?>/terms.php">Foydalanish shartlari</a></li></ul></div>
            </div>
            <div style="margin-top:var(--space-12);padding-top:var(--space-6);border-top:1px solid var(--border-color);text-align:center;color:var(--text-muted);">&copy; <?php echo date('Y'); ?> <?php echo e($siteName); ?>. Barcha huquqlar himoyalangan.</div>
        </div>
    </footer>
    <script src="<?php echo ASSETS_URL; ?>/assets/js/main.js"></script>
    <script>function selectService(id){document.getElementById('selected-service').value=id;const s=document.getElementById('service_type');if(s)s.value=id;}</script>
</body>
</html>
