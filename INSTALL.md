# WebHub.uz - O'rnatish Qo'llanmasi

## Har qanday domenda ishga tushirish

### 1-qadam: Fayllarni serverga yuklash
```bash
# Barcha fayllarni hosting/serverga yuklang
# Domainingizning root papkasiga (public_html yoki www)
```

### 2-qadam: Database yaratish
```sql
CREATE DATABASE webhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'webhub_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON webhub_db.* TO 'webhub_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3-qadam: Konfiguratsiya
```bash
# config/db.sample.php ni config/db.php ga nusxalang
cp config/db.sample.php config/db.php

# db.php faylini tahrirlang va database ma'lumotlarini kiriting
```

### 4-qadam: Browserda ochish
```
https://sizning-domeningiz.com/install.php
```

O'rnatish wizardi orqali o'ting:
1. Server tekshiruvi
2. Database ma'lumotlari
3. Admin account yaratish
4. Yakunlash

### 5-qadam: Tugallash
- Admin panelga kiring: `https://sizning-domeningiz.com/admin/login.php`
- Default admin: `admin@webhub.uz` / `admin123` (agar install paytida o'zgartirmagan bo'lsangiz)

## Xavfsizlik sozlamalari

### .htaccess avtomatik sozlamalar
- XSS himoyasi
- Clickjacking himoyasi
- Directory listing o'chirilgan
- Uploads papkasida PHP ishlamaydi
- Gzip compression yoqilgan
- Browser caching sozlangan

### Tavsiya etiladi:
1. SSL sertifikat o'rnating (Let's Encrypt)
2. HTTPS ni majburiy qiling (.htaccess da)
3. Database user uchun murakkab parol o'rnating
4. Admin email va parolni o'zgartiring

## Muammolarni hal qilish

### "Database ulanish xatosi"
- db.php faylidagi ma'lumotlarni tekshiring
- Database mavjudligini tekshiring
- User ruxsatlarini tekshiring

### "404 Not Found"
- .htaccess fayli mavjudligini tekshiring
- mod_rewrite yoqilganligini tekshiring
- Fayl ruxsatlarini tekshiring (755 papkalar, 644 fayllar)

### "500 Internal Server Error"
- PHP error log ni tekshiring
- PHP versiyasi (7.4+ kerak)
- Required extensionlar mavjudligini tekshiring

## Texnik talablar

- PHP 7.4 yoki undan yuqori
- MySQL 5.7+ yoki MariaDB 10.3+
- Apache with mod_rewrite
- PDO MySQL extension
- OpenSSL extension
- GD yoki Imagick extension

## Fayl ruxsatlari

```bash
chmod 755 .
chmod 755 config/
chmod 755 includes/
chmod 755 uploads/
chmod 644 *.php
chmod 644 .htaccess
chmod 644 config/.htaccess
chmod 644 uploads/.htaccess
```

## Yordam

Muammolar yuzaga kelsa:
1. install.php ni qayta oching
2. Database ni tozalang va qayta o'rnating
3. Error loglarni tekshiring
