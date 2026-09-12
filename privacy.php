<?php
/**
 * Privacy Policy Page
 */

require_once __DIR__ . '/../config/init.php';
$siteName = getSetting('site_name', 'WebHub.uz');
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxfiylik siyosati - <?php echo e($siteName); ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/variables.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/base.css">
</head>
<body>
    <nav class="glass" style="padding: var(--space-4) 0;">
        <div class="container flex justify-between items-center">
            <a href="<?php echo BASE_URL; ?>/" style="font-size: var(--text-xl); font-weight: 700; color: var(--primary);"><?php echo e($siteName); ?></a>
            <a href="<?php echo BASE_URL; ?>/" class="btn btn-sm btn-outline">Bosh sahifa</a>
        </div>
    </nav>

    <main class="section">
        <div class="container" style="max-width: 800px;">
            <h1 style="margin-bottom: var(--space-8);">Maxfiylik siyosati</h1>
            
            <article style="line-height: 1.8; color: var(--text-secondary);">
                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">1. Umumiy qoidalar</h2>
                <p><?php echo e($siteName); ?> ("Biz", "Kompaniya") foydalanuvchilarning shaxsiy ma'lumotlarini himoya qilishga jiddiy yondashadi. Ushbu maxfiylik siyosati saytimizdan foydalanganda qanday ma'lumotlarni to'playmiz va ulardan qanday foydalanamizligini tushuntiradi.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">2. To'planadigan ma'lumotlar</h2>
                <p>Biz quyidagi ma'lumotlarni to'plashimiz mumkin:</p>
                <ul style="margin: var(--space-4) 0; padding-left: var(--space-6);">
                    <li>Ism va familiya</li>
                    <li>Aloqa ma'lumotlari (telefon raqami, email)</li>
                    <li>Loyiha haqida ma'lumotlar</li>
                    <li>Texnik ma'lumotlar (IP adres, brauzer turi)</li>
                </ul>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">3. Ma'lumotlardan foydalanish</h2>
                <p>Sizning ma'lumotlaringiz quyidagi maqsadlarda ishlatiladi:</p>
                <ul style="margin: var(--space-4) 0; padding-left: var(--space-6);">
                    <li>Xizmatlar ko'rsatish va buyurtmalarni bajarish</li>
                    <li>Siz bilan aloqa o'rnatish</li>
                    <li>Xizmat sifatini yaxshilash</li>
                    <li>Xavfsizlik maqsadlari</li>
                </ul>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">4. Ma'lumotlarni himoya qilish</h2>
                <p>Biz sizning shaxsiy ma'lumotlaringizni himoya qilish uchun zamonaviy xavfsizlik choralarini qo'llaymiz. Ma'lumotlar xavfsiz serverlarda saqlanadi va ruxsatsiz kirishdan himoyalangan.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">5. Uchinchi tomonlar</h2>
                <p>Biz sizning shaxsiy ma'lumotlaringizni uchinchi tomonlarga sotmaymiz, ijaraga bermaymiz yoki boshqa tarzda tarqatmaymiz, faqat qonun talablari yoki xizmat ko'rsatish zarurati bo'lganda bundan mustasno.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">6. Cookie fayllar</h2>
                <p>Saytimiz ishlashini yaxshilash uchun cookie fayllardan foydalanamiz. Siz brauzer sozlamalari orqali cookie fayllarni bloklashingiz mumkin.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">7. O'zgartirishlar</h2>
                <p>Biz ushbu maxfiylik siyosatiga o'zgartirishlar kiritish huquqiga egamiz. O'zgarishlar saytda joylashtiriladi.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">8. Aloqa</h2>
                <p>Maxfiylik siyosati bo'yicha savollaringiz bo'lsa, biz bilan bog'laning.</p>

                <p style="margin-top: var(--space-8); color: var(--text-muted);">
                    Oxirgi yangilanish: <?php echo date('Y-m-d'); ?>
                </p>
            </article>
        </div>
    </main>

    <footer class="glass" style="padding: var(--space-8) 0; border-top: 1px solid var(--border-color);">
        <div class="container text-center" style="color: var(--text-muted);">
            &copy; <?php echo date('Y'); ?> <?php echo e($siteName); ?>. Barcha huquqlar himoyalangan.
        </div>
    </footer>
</body>
</html>
