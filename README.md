# 🚀 Laravel + Vue.js Project

This is a Laravel and Vue.js application using **JWT (JSON Web Token)** for secure API authentication. Follow the steps below to set up your local development environment.

---

## 📋 Prerequisites

Before you begin, ensure you have:
* **PHP** (>= 8.2)
* **Composer**
* **Node.js** (>= 18.x) & **NPM**
* **Database** (MySQL, PostgreSQL, or SQLite)

---

## 🛠 Installation Guide
```bash
git clone <your-repository-url>
cd <your-project-folder>

Install the PHP backend packages and the JavaScript frontend packages:

# Install Laravel packages
composer install

# Install Vue.js packages
npm install

# Environment Setup
Create your local environment file and generate the application security key:

cp .env.example .env
php artisan key:generate

Action Required: Open the .env file and update your database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

# JWT Secret Key Generation
Since this project uses JWT for authentication, you must generate a unique secret key to sign your tokens:

# This will add JWT_SECRET to your .env file
php artisan jwt:secret
5. Database Migration

Create your database tables

# Storage Link
Link the storage directory to the public folder to handle file uploads:

php artisan storage:link

Running the Application
To run the project, you need to open two separate terminal windows:

Terminal 1: Laravel Backend

php artisan serve

Your app will be live at: https://www.google.com/search?q=http://127.0.0.1:8000

Terminal 2: Vite Frontend

npm run dev

This handles Vue Hot-Module Replacement.
