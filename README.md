# 💬 ChatApp (Laravel + Reverb)

Real-time chat application built with Laravel, Livewire, and Laravel Reverb.

🌍 Live Demo

🚀 Try it here:
👉 https://chat-app-main-npv2h5.free.laravel.cloud
---

## 🚀 Features

* 🔥 Real-time messaging using **Laravel Reverb**
* ⚡ Livewire + Blade UI
* 💬 Chat rooms support
* 📡 Broadcasting with WebSockets
* 🗄️ MySQL database

---

## 🧰 Requirements

Before you start, make sure you have:

* PHP >= 8.2
* Composer
* Node.js & NPM
* MySQL / MariaDB
* Laravel CLI

---

## 📥 Installation

### 1. Clone the repository

```bash
git clone https://github.com/minamaherwanis/ChatApp.git
cd ChatApp
```

---

### 2. Install dependencies

```bash
composer install
npm install
```

---

### 3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

---

### 4. Configure database

افتح ملف `.env` وعدل:

```env
DB_DATABASE=chatapp
DB_USERNAME=root
DB_PASSWORD=
```

وبعدين:

```bash
php artisan migrate
```

---

### 5. Storage link (important)

```bash
php artisan storage:link
```

---

## ⚡ Setup Laravel Reverb

### 6. Install Reverb (لو مش موجود)

```bash
php artisan install:broadcasting
```

---

### 7. Run Reverb server

```bash
php artisan reverb:start
```

---

### 8. Frontend build

```bash
npm run dev
```

---

### 9. Run Laravel server

```bash
php artisan serve
```

---

## 🌐 Access the app

افتح:

```
http://127.0.0.1:8000
```

---

## 📡 Notes about Reverb

* تأكد إن:

```env
BROADCAST_DRIVER=reverb
```

* ولو شغال Local:

```env
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

---

## 🐞 Troubleshooting

### الصور مش شغالة؟

```bash
php artisan storage:link
```

---

### Realtime مش شغال؟

* تأكد إن `reverb:start` شغال
* تأكد إن Echo متعرف في `app.js`
* تأكد من إعدادات `.env`

---

## 📌 Deployment Notes

لو هترفع على Cloud:

* شغل Reverb كـ process دائم (Supervisor)
* افتح port الخاص بـ WebSocket
* استخدم HTTPS + WSS

---

## 👨‍💻 Author

* Mena Maher
<p align="left">
  <a href="https://www.instagram.com/mina_maher_wanis/" target="_blank">
    <img src="https://img.shields.io/badge/Instagram-%23E4405F.svg?style=for-the-badge&logo=Instagram&logoColor=white"/>
  </a>

  <a href="https://minamaherwanis.github.io/Portfolio" target="_blank">
    <img src="https://img.shields.io/badge/Portfolio-%23000000.svg?style=for-the-badge&logo=About.me&logoColor=white"/>
  </a>

  <a href="https://www.linkedin.com/in/mina-maher-b91369199/" target="_blank">
    <img src="https://img.shields.io/badge/LinkedIn-%230077B5.svg?style=for-the-badge&logo=linkedin&logoColor=white"/>
  </a>
</p>

---

## ⭐ Support

لو المشروع عجبك اعمله Star ⭐ على GitHub
