# POS API

POS API adalah backend service untuk aplikasi Point of Sales (POS) yang dikembangkan menggunakan Laravel. API ini menyediakan berbagai endpoint penting seperti autentikasi (AUTH), manajemen user, kategori, dan produk. Saat ini pengembangan masih ongoing dan akan terus diperluas untuk mendukung transaksi penjualan, laporan, dan fitur lainnya.

## Tech Stack

-   **Framework:** Laravel
-   **Auth:** JWT
-   **Database:** MySQL
-   **Documentation**: L5 Swagger

## Demo

Belum tersedia (run locally)

## Screenshots

![API Documentation](demo/swagger.png)

## Run Locally

Clone the project

```bash
git clone https://github.com/tiedsandi/project_pos-api
```

Go to the project directory

```bash
cd project_pos-api
```

Install dependencies

```bash
composer install
```

Rename `.env.example` to `.env` and configure the `.env` file

```bash
cp .env.example .env
```

Run migrations

```bash
php artisan migrate
```

Seed the database (if needed)

```bash
php artisan db:seed
```

Generate app key

```bash
php artisan key:generate
```

Check available routes

```bash
php artisan route:list
```

Start the development server

```bash
php artisan serve
```

# Hi, I'm Fachran Sandi! 👋

## 🚀 About Me

Junior Developer dengan pengalaman profesional di Sinarmas Land serta berbagai proyek pribadi, termasuk mengembangkan website pribadi. Menguasai JavaScript, PHP, dan React.js, serta memiliki pemahaman di front-end maupun back-end development. Memegang sertifikasi BNSP di bidang pemrograman, siap berkontribusi dalam tim developer dan terus mempelajari teknologi baru.

## 🎓 Education

**Universitas Pembangunan Nasional Veteran Jakarta – Jakarta Selatan**
Sarjana Informatika, 3.80/4.00 (Agustus 2018 – Januari 2023)

-   Aktif sebagai anggota komite dalam berbagai kegiatan di tingkat fakultas dan universitas
-   Menjadi asisten dosen untuk mata kuliah Pengantar Basis Data pada tahun 2020

## 💼 Experience

**Sinarmas Land | PT Bumi Serpong Damai Tbk – Tanggerang**
OutSystems Developer – Kontrak (November 2023 – Agustus 2024)

-   Mengembangkan aplikasi berbasis platform low-code OutSystems dengan workflow React, untuk web dan mobile
-   Terlibat penuh dalam seluruh siklus pengembangan
-   Bertanggung jawab atas pemeliharaan aplikasi dan perbaikan bug

## 💻 Projects

-   **Aplikasi Kasir (Proyek BNSP):** Laravel, MySQL – login, manajemen produk, transaksi, laporan harian
-   **API Backend untuk Aplikasi Kasir:** Laravel, MySQL – autentikasi, validasi data, RESTful API
-   **Simple TodoList:** React.js, Redux – CRUD tugas, local storage

## 🛠 Skills

-   Bahasa: JavaScript, PHP
-   Framework & Library: React.js, Node.js, Next.js, Redux, Laravel
-   API: RESTful API, Laravel API
-   Tools: Git, MySQL, VS Code
-   Soft Skills: Problem-solving, teamwork, fast learner

## 🔗 Links

[![portfolio](https://img.shields.io/badge/my_portfolio-000?style=for-the-badge&logo=ko-fi&logoColor=white)](https://fachran-sandi.netlify.app/)
[![linkedin](https://img.shields.io/badge/linkedin-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/fachransandi/)
