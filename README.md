````md
<div align="center">

# ⚙️ VocaVault API

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![REST API](https://img.shields.io/badge/API-REST-009688)](https://api.vocavault.ahmedmagdy.cloud)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

### RESTful backend powering the VocaVault platform.

Built with **Laravel**, **PHP**, and **MySQL**.

[🌐 API URL](https://api.vocavault.ahmedmagdy.cloud) •
[🌍 Frontend](https://github.com/ahmedmagdyoff/vocavault-web) •
[📚 Main Project](https://github.com/ahmedmagdyoff/vocavault)

</div>

---

# 📖 About

VocaVault API is the backend service that powers the VocaVault platform.

It provides secure RESTful endpoints for authentication, vocabulary management, videos, categories, and word forms.

---

# ✨ Features

- 🔐 Authentication
- 📚 Vocabulary Management
- 🎥 Video Management
- 📝 Word Forms
- 🗂 Categories
- 🔗 REST API
- 🛡 Token Authentication
- 🚀 Automatic Deployment

---

# 🛠 Tech Stack

- Laravel 12
- PHP 8
- MySQL
- Laravel Sanctum

---

# 🚀 Getting Started

Clone the repository.

```bash
git clone https://github.com/ahmedmagdyoff/vocavault-api.git
```

Install dependencies.

```bash
composer install
```

Copy the environment file.

```bash
cp .env.example .env
```

Generate the application key.

```bash
php artisan key:generate
```

Run database migrations.

```bash
php artisan migrate
```

Start the development server.

```bash
php artisan serve
```

---

# 🌐 Environment Variables

Create and configure your `.env` file.

```env
APP_NAME=VocaVault
APP_ENV=local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vocavault
DB_USERNAME=root
DB_PASSWORD=
```

---

# 📁 Project Structure

```text
app/
bootstrap/
config/
database/
routes/
storage/
tests/
```

---

# 🚀 Deployment

Deployment is fully automated using **GitHub Actions**.

Every push to the `main` branch runs:

- ✅ Build Check
- 🚀 VPS Deployment
- ⚡ Composer Install
- 🗄 Database Migration
- ⚙ Laravel Optimization

---

# 📂 Related Repositories

| Repository        | Description      |
| ----------------- | ---------------- |
| **VocaVault**     | Main Project     |
| **VocaVault Web** | Next.js Frontend |

---

# 📄 License

This project is licensed under the **MIT License**.
````
