# WebHub.uz - IT Xizmatlar Platformasi

🚀 **Har qanday domenda ishlaydigan professional IT xizmatlar platformasi**

## ✨ Xususiyatlar

### Foydalanuvchilar uchun:
- 🌐 Dinamik bosh sahifa (xizmatlar, portfolio, blog, aloqa)
- 👤 Google OAuth orqali kirish
- 📝 Ariza yuborish va holatini kuzatish
- 💬 Real-time chat (admin bilan)
- 🔔 Bildirishnomalar tizimi
- 📊 Shaxsiy kabinet
- 🌓 Light/Dark tema avtomatik

### Admin panel:
- 📈 Dashboard statistika
- 🛠 Xizmatlarni CRUD boshqarish
- 📋 Arizalarni ko'rish va holat o'zgartirish
- 🎨 Portfolio loyihalarni boshqarish
- ⚙️ Sayt sozlamalari
- 📝 Blog yuritish
- 💬 Foydalanuvchilar bilan chat

### Xavfsizlik:
- 🔒 CSRF himoya
- 🔐 bcrypt password hashing
- 🛡 SQL injection prevention (PDO)
- 🚫 XSS himoyasi
- 📁 Uploads papkasida PHP ishlamaydi
- 🌐 HTTPS majburiy (SSL mavjud bo'lsa)

## 🚀 Tezkor O'rnatish

### 1. Fayllarni yuklang
```bash
# Barcha fayllarni domainingizning root papkasiga yuklang
```

### 2. Database yarating
```sql
CREATE DATABASE webhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. O'rnatish wizardini ishga tushiring
```
https://sizning-domeningiz.com/install.php
```

### 4. Tayyor!
- Admin: `https://domeningiz.com/admin/login.php`
- Default: `admin@webhub.uz` / `admin123`

## 📁 Fayl Tuzilishi

```
/webhub
├── config/
│   ├── init.php          # Dinamik konfiguratsiya (har qanday domen)
│   ├── db.sample.php     # DB config namuna
│   └── .htaccess         # Config himoyasi
├── includes/
│   ├── functions.php     # Core funksiyalar
│   └── migration.php     # DB schema
├── admin/                # Admin panel
├── user/                 # Foydalanuvchi paneli
├── api/                  # REST API
├── assets/
│   ├── css/              # Styles (light/dark)
│   └── js/               # JavaScript
├── uploads/              # Fayl yuklashlar
├── .htaccess             # Apache security
├── index.php             # Bosh sahifa
├── install.php           # O'rnatish wizardi
└── INSTALL.md            # Batafsil qo'llanma
```

## 🔧 Texnik Talablar

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite
- PDO MySQL extension
- GD extension

## 🎨 Dizayn Xususiyatlari

- Glassmorphism effektlari
- Responsive (mobile-first)
- CSS Variables theming
- Scroll animations
- Gradient backgrounds

## 📊 Database Tables

1. `users` - Foydalanuvchilar
2. `services` - Xizmatlar
3. `projects` - Portfolio
4. `applications` - Arizalar
5. `messages` - Chat xabarlar
6. `blog_posts` - Blog
7. `settings` - Sayt sozlamalari
8. `audit_logs` - Xavfsizlik loglari

## 🔐 Xavfsizlik

### Avtomatik himoyalar:
- ✅ CSRF token har bir formda
- ✅ Prepared statements (SQL)
- ✅ Password hashing (bcrypt)
- ✅ Session security flags
- ✅ File upload validation
- ✅ Directory listing disabled
- ✅ PHP execution disabled in uploads

### Tavsiyalar:
1. SSL sertifikat o'rnating
2. Admin parolini o'zgartiring
3. Database user uchun murakkab parol
4. `.htaccess` ni production da sozlang

## 🌍 Har Qanday Domenda Ishlash

Platforma dinamik ravishda:
- Base URL ni avtomatik aniqlaydi
- HTTP/HTTPS ni farqlaydi
- Domen nomidan qat'i nazar ishlaydi
- Subdomenlarda ham ishlaydi

## 📝 Versiya

**v1.0.0** - Production ready release

## 📞 Yordam

Muammolar uchun `INSTALL.md` faylini o'qing.

---

**WebHub.uz** © 2024 - Professional IT xizmatlar platformasi
