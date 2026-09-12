<?php
/**
 * Terms of Service Page
 */

require_once __DIR__ . '/includes/functions.php';
$siteName = getSetting('site_name', 'WebHub.uz');
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foydalanish shartlari - <?php echo e($siteName); ?></title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/base.css">
</head>
<body>
    <nav class="glass" style="padding: var(--space-4) 0;">
        <div class="container flex justify-between items-center">
            <a href="/" style="font-size: var(--text-xl); font-weight: 700; color: var(--primary);"><?php echo e($siteName); ?></a>
            <a href="/" class="btn btn-sm btn-outline">Bosh sahifa</a>
        </div>
    </nav>

    <main class="section">
        <div class="container" style="max-width: 800px;">
            <h1 style="margin-bottom: var(--space-8);">Foydalanish shartlari</h1>
            
            <article style="line-height: 1.8; color: var(--text-secondary);">
                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">1. Qabul qilish</h2>
                <p><?php echo e($siteName); ?> saytidan foydalanish orqali siz ushbu foydalanish shartlarini to'liq qabul qilgan hisoblanasiz.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">2. Xizmatlar</h2>
                <p>Sayt quyidagi xizmatlarni taqdim etadi:</p>
                <ul style="margin: var(--space-4) 0; padding-left: var(--space-6);">
                    <li>IT xizmatlar haqida ma'lumot</li>
                    <li>Buyurtma berish imkoniyati</li>
                    <li>Mijozlar bilan aloqa</li>
                    <li>Blog va yangiliklar</li>
                </ul>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">3. Foydalanuvchi majburiyatlari</h2>
                <p>Foydalanuvchi quyidagilarni bajarmasligi kerak:</p>
                <ul style="margin: var(--space-4) 0; padding-left: var(--space-6);">
                    <li>Noto'g'ri yoki aldama ma'lumotlarni taqdim etish</li>
                    <li>Sayt ishini buzishga urinish</li>
                    <li>Boshqa foydalanuvchilarga zarar yetkazish</li>
                    <li>Noqonuniy maqsadlarda foydalanish</li>
                </ul>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">4. Intellektual mulk</h2>
                <p>Saytdagi barcha kontent (matnlar, rasmlar, logotiplar) mualliflik huquqi bilan himoyalangan va ruxsatsiz ishlatilishi mumkin emas.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">5. Javobgarlikni cheklash</h2>
                <p><?php echo e($siteName); ?> sayt ma'lumotlarining aniqligi uchun javobgar emas va xizmatlarni "bor holatda" taqdim etadi.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">6. Xizmatlarni o'zgartirish</h2>
                <p>Biz istalgan vaqtda xizmatlarni o'zgartirish yoki to'xtatib qo'yish huquqiga egamiz.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">7. Shartlarni o'zgartirish</h2>
                <p>Biz ushbu shartlarga o'zgartirishlar kiritish huquqiga egamiz. O'zgarishlar saytda joylashtiriladi.</p>

                <h2 style="color: var(--text-primary); margin-top: var(--space-8);">8. Aloqa</h2>
                <p>Savollaringiz bo'lsa, biz bilan bog'laning.</p>

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
