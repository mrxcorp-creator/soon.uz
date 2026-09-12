# 🚀 WebHub.uz - O'rnatish va Joylashtirish Qo'llanmasi

## ✅ 403 Forbidden Xatosini Bartaraf Etish

Agar siz `403 Forbidden` xatosini ko'rsangiz, quyidagi amallarni bajaring:

### 1. .htaccess Fayllari Tekshiruvi

Barcha `.htaccess` fayllari yaratilgan va to'g'ri sozlangan:

```
/.htaccess              # Asosiy konfiguratsiya (LiteSpeed & Apache)
/admin/.htaccess        # Admin panel himoyasi
/api/.htaccess          # API endpoint himoyasi
/assets/.htaccess       # Statik fayllar himoyasi
/config/.htaccess       # Konfiguratsiya papkasi to'liq bloklangan
/includes/.htaccess     # Include fayllar himoyasi
/uploads/.htaccess      # Uploads papkasida PHP ishlamaydi
/uploads/images/.htaccess   # Faqat rasmlar ruxsat etilgan
/uploads/documents/.htaccess # Faqat hujjatlar ruxsat etilgan
/user/.htaccess         # User panel himoyasi
```

### 2. Server Talablari

- **PHP**: 7.4 yoki undan yuqori
- **MySQL**: 5.7 yoki undan yuqori (yoki MariaDB 10.2+)
- **Apache**: mod_rewrite bilan (yoki LiteSpeed)
- **PHP Extensions**: PDO, pdo_mysql, json, mbstring

### 3. Fayl Ruxsatlari (Permissions)

Serverga yuklagandan keyin quyidagi ruxsatlarni o'rnating:

```bash
# Papkalar uchun
chmod 755 /workspace
chmod 755 /workspace/admin
chmod 755 /workspace/api
chmod 755 /workspace/assets
chmod 755 /workspace/config
chmod 755 /workspace/includes
chmod 755 /workspace/uploads
chmod 755 /workspace/uploads/images
chmod 755 /workspace/uploads/documents
chmod 755 /workspace/user

# Fayllar uchun
chmod 644 /workspace/*.php
chmod 644 /workspace/.htaccess
chmod 644 /workspace/*/.htaccess

# Config papkasi (o'rnatishdan keyin)
chmod 755 /workspace/config
chmod 644 /workspace/config/config.php

# Uploads papkasi (yozish ruxsati kerak)
chmod 755 /workspace/uploads
chmod 755 /workspace/uploads/images
chmod 755 /workspace/uploads/documents
```

### 4. O'rnatish Bosqichlari

#### 4.1. Fayllarni Serverga Yuklash

```bash
# Barcha fayllarni hosting serveriga yuklang
# FTP, SFTP yoki cPanel File Manager orqali
```

#### 4.2. Ma'lumotlar Bazasini Yaratish

cPanel yoki phpMyAdmin orqali:

1. Yangi MySQL database yarating
2. Database foydalanuvchisini yarating
3. Foydalanuvchiga database ustidan to'liq huquqlar bering

#### 4.3. O'rnatish Wizardini Ishga Tushirish

Brauzerda oching:
```
https://domeningiz.com/install.php
```

O'rnatish wizardi 4 bosqichdan iborat:
1. **Server Tekshiruvi** - PHP versiyasi, extensionlar, ruxsatlar
2. **Database Sozlamalari** - Host, database nomi, foydalanuvchi, parol
3. **Admin Yaratish** - Admin email va parol
4. **Yakunlash** - Tayyor!

#### 4.4. O'rnatishdan Keyin

```bash
# Config faylini himoya qilish
chmod 644 /workspace/config/config.php

# Uploads papkasiga yozish ruxsati
chmod 755 /workspace/uploads
chmod 755 /workspace/uploads/images
chmod 755 /workspace/uploads/documents
```

### 5. Har Qanday Domenda Ishlatish

Platforma avtomatik ravishda har qanday domenda ishlaydi:

- `https://webhub.uz`
- `https://example.com`
- `https://subdomain.domeningiz.com`
- `http://localhost` (local development)

**BASE_URL** avtomatik aniqlanadi va qo'lda sozlash talab qilinmaydi.

### 6. Admin Panelga Kirish

O'rnatishdan keyin:

```
URL: https://domeningiz.com/admin/login.php
Email: (o'rnatishda kiritgan emailingiz)
Parol: (o'rnatishda kiritgan parolingiz)
```

### 7. Muammolarni Bartaraf Etish

#### 403 Forbidden Xatosi

**Sabab**: .htaccess noto'g'ri yoki server ruxsatlari muammosi

**Yechim**:
1. `.htaccess` fayllari mavjudligini tekshiring
2. Fayl ruxsatlarini to'g'rilang (644 fayllar, 755 papkalar)
3. Apache/LiteSpeed da mod_rewrite yoqilganligini tekshiring
4. Server loglarini tekshiring (`error_log`)

#### 500 Internal Server Error

**Sabab**: PHP xatoligi yoki .htaccess sintaksis xatosi

**Yechim**:
1. `error_log` faylini tekshiring
2. PHP versiyasini tekshiring (7.4+)
3. .htaccess fayllarini vaqtincha o'chirib test qiling

#### Database Ulanish Xatosi

**Sabab**: Noto'g'ri database ma'lumotlari

**Yechim**:
1. `config/config.php` faylini tekshiring
2. Database nomi, foydalanuvchi va parolni tasdiqlang
3. Database foydalanuvchisiga huquqlar berilganligini tekshiring

### 8. Xavfsizlik Maslahatlari

1. **SSL Sertifikati** o'rnating (HTTPS)
2. **Database prefix** ni o'zgartiring (default: `webhub_`)
3. **Admin email** ni darhol o'zgartiring
4. **Muntazam backup** qiling
5. **PHP versiyasini** yangilab boring

### 9. Texnik Ko'mak

Muammolar yuzaga kelsa:

1. Server error loglarini tekshiring
2. Browser console ni tekshiring
3. Network tab da requestlarni ko'ring
4. README.md va INSTALL.md ni qayta o'qing

---

**WebHub.uz** - Har qanday domenda ishlaydigan xavfsiz va professional platforma! 🎉
