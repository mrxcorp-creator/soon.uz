# WebHub.uz - Raqamli Yechimlar Platformasi

WebHub.uz - O'zbekistondagi zamonaviy veb-studiya va raqamli xizmatlar platformasi.

## 🚀 Xususiyatlar

### Foydalanuvchilar uchun:
- Zamonaviy va responsive dizayn (Light/Dark tema)
- Xizmatlar ko'rinishi va buyurtma berish
- Portfolio (loyihalar) galereyasi
- Blog maqolalari
- Onlayn ariza yuborish
- Real-time chat qo'llab-quvvatlash
- Shaxsiy kabinet

### Admin panel:
- Dashboard (statistika)
- Xizmatlarni boshqarish (CRUD)
- Arizalarni ko'rish va holatini o'zgartirish
- Loyihalarni (portfolio) boshqarish
- Foydalanuvchilarni boshqarish
- Sozlamalar (sayt, ijtimoiy tarmoqlar)
- Audit loglari

## 🛠 Texnologiyalar

- **Backend**: PHP 7.4+ (PDO, native)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Styling**: Custom CSS variables (Light/Dark theme)
- **Icons**: Font Awesome 6
- **Security**: CSRF protection, password hashing (bcrypt), SQL injection prevention

## 📁 Fayl tuzilmasi

```
webhub/
├── admin/                  # Admin panel sahifalari
│   ├── dashboard.php
│   ├── services.php
│   ├── applications.php
│   ├── projects.php
│   ├── settings.php
│   ├── login.php
│   └── logout.php
├── api/                    # API endpointlari
│   ├── submit-application.php
│   ├── send-message.php
│   └── get-messages.php
├── assets/
│   ├── css/
│   │   ├── variables.css   # CSS variables (tema)
│   │   ├── base.css        # Base styles
│   │   └── components.css  # Component styles
│   └── js/
│       └── main.js         # Asosiy JavaScript
├── config/                 # Konfiguratsiya fayllari
│   └── db.php              # DB ulanish (install dan keyin)
├── includes/
│   ├── functions.php       # Core funksiyalar
│   └── migration.php       # Database migration
├── uploads/                # Yuklangan fayllar
│   ├── projects/
│   └── avatars/
├── index.php               # Bosh sahifa
├── install.php             # O'rnatish wizardi
├── privacy.php             # Maxfiylik siyosati
├── terms.php               # Foydalanish shartlari
├── .htaccess               # Apache security rules
└── README.md
```

## ⚙️ O'rnatish

### 1. Server talablari
- PHP 7.4 yoki undan yuqori
- MySQL 5.7 yoki MariaDB 10.3+
- Apache with mod_rewrite (yoki Nginx)
- PDO MySQL extension

### 2. O'rnatish jarayoni

1. Fayllarni serverga yuklang
2. Brauzerda `http://saytingiz.com/install.php` ochiladi
3. Bosqichma-bosqich ko'rsatmalarga amal qiling:
   - Server talablarini tekshirish
   - Ma'lumotlar bazasi sozlamalari
   - Admin foydalanuvchi yaratish
   - Yakunlash

### 3. Default admin ma'lumotlari
- **Email**: admin@webhub.uz
- **Parol**: admin123 (birinchi kirishdan keyin o'zgartiring!)

## 🔒 Xavfsizlik

- CSRF token himoyasi barcha formalar uchun
- Password hashing (bcrypt)
- SQL injection prevention (PDO prepared statements)
- XSS prevention (htmlspecialchars)
- File upload validation
- Session management
- Audit logging

## 📊 Database jadvallari

1. **users** - Foydalanuvchilar (admin, client, manager)
2. **services** - Xizmat turlari
3. **projects** - Portfolio loyihalar
4. **applications** - Mijoz arizalari
5. **messages** - Chat xabarlari
6. **blog_posts** - Blog maqolalari
7. **settings** - Sayt sozlamalari (key/value)
8. **audit_logs** - Xavfsizlik jurnali

## 🌐 Til

Platforma UI to'liq **O'zbek tilida**. 
Texnik nomlar va kodlar ingliz tilida saqlangan.

## 📝 Litsenziya

Proprietary - WebHub.uz

## 🤝 Aloqa

- Website: https://webhub.uz
- Email: info@webhub.uz
- Telegram: @webhub_uz

---
**WebHub.uz** © 2024 - Raqamli yechimlar studiyasi
