<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  </a>
</p>

<h1 align="center">🛒 E-Commerce PKL</h1>

<p align="center">
  Proyek e-commerce berbasis Laravel · Dikerjakan dalam rangka Praktik Kerja Lapangan (PKL)
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red?logo=laravel" />
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?logo=php" />
  <img src="https://img.shields.io/badge/Database-MySQL-orange?logo=mysql" />
  <img src="https://img.shields.io/badge/Status-In%20Development-yellow" />
</p>

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Tech Stack](#-tech-stack)
- [Struktur Branch](#-struktur-branch)
- [Setup Awal (Pertama Kali)](#-setup-awal-pertama-kali)
- [Workflow Git Sehari-hari](#-workflow-git-sehari-hari)
- [Fitur yang Dikerjakan](#-fitur-yang-dikerjakan)
- [Konvensi Commit](#-konvensi-commit)
- [Struktur Folder Penting](#-struktur-folder-penting)
- [Tim](#-tim)

---

## 📌 Tentang Proyek

Aplikasi e-commerce yang dibangun menggunakan **Laravel** sebagai backend framework. Proyek ini dikerjakan oleh 2 developer dengan repo backend terpisah, menggunakan **Git Feature Branch Workflow** agar pengembangan lebih rapi dan tidak saling konflik.

---

## 🛠 Tech Stack

| Layer      | Teknologi         |
|------------|-------------------|
| Backend    | Laravel 11.x      |
| Language   | PHP 8.2+          |
| Database   | MySQL             |
| Auth       | Laravel Sanctum   |
| Version Control | Git + GitHub |

---

## 🌿 Struktur Branch

```
main          ← Production (stable, jangan langsung push)
└── develop   ← Gabungan development (merge dari feature branches)
    ├── feature/database   ← Schema & migrations
    ├── feature/products   ← Manajemen produk
    ├── feature/orders     ← Manajemen pesanan
    └── feature/wallet     ← Fitur dompet / pembayaran
```

> ⚠️ **Aturan:** Jangan pernah push langsung ke `main` atau `develop`. Selalu buat `feature/*` branch dulu.

---

## 🚀 Setup Awal (Pertama Kali)

### 1. Clone Repo

```bash
git clone https://github.com/username/nama-repo.git
cd nama-repo
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Lalu edit `.env` sesuai konfigurasi lokal kamu:

```env
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeding Database

```bash
php artisan migrate --seed
```

### 5. Jalankan Server

```bash
php artisan serve
```

Aplikasi berjalan di: `http://localhost:8000`

---

## 🔄 Workflow Git Sehari-hari

### Mulai Mengerjakan Fitur

```bash
# 1. Pindah ke develop dan ambil update terbaru
git checkout develop
git pull

# 2. Buat branch fitur baru dari develop
git checkout -b feature/nama-fitur
```

### Simpan & Push Pekerjaan

```bash
# 3. Setelah selesai mengerjakan sesuatu
git add .
git commit -m "feat(nama-fitur): deskripsi singkat apa yang dibuat"
git push -u origin feature/nama-fitur
```

### Setelah Fitur Selesai & Stabil

```bash
# 4. Merge ke develop (via Pull Request atau langsung)
git checkout develop
git pull
git merge feature/nama-fitur
git push
```

> 💡 **Tips:** Sebaiknya buat **Pull Request** di GitHub agar bisa di-review dulu sebelum merge ke `develop`.

---

## ✅ Fitur yang Dikerjakan

| Branch               | Fitur                              | Status        |
|----------------------|------------------------------------|---------------|
| `feature/database`   | Schema & migrasi tabel             | 🔧 In Progress |
| `feature/products`   | CRUD produk, kategori, stok        | ⏳ Planned     |
| `feature/orders`     | Keranjang, checkout, order history | ⏳ Planned     |
| `feature/wallet`     | Saldo, top-up, transaksi           | ⏳ Planned     |

---

## 📝 Konvensi Commit

Gunakan format **Conventional Commits** supaya histori Git mudah dibaca:

```
<type>(<scope>): <deskripsi singkat>
```

| Type       | Kapan Digunakan                          |
|------------|------------------------------------------|
| `feat`     | Menambah fitur baru                      |
| `fix`      | Memperbaiki bug                          |
| `refactor` | Refactor kode tanpa mengubah fungsional  |
| `chore`    | Update dependency, config, dll           |
| `docs`     | Update dokumentasi / README              |
| `style`    | Formatting, typo (bukan perubahan logic) |
| `test`     | Menambah atau mengubah unit test         |

**Contoh commit yang baik:**

```bash
git commit -m "feat(products): add product listing with pagination"
git commit -m "fix(orders): fix total price calculation on checkout"
git commit -m "chore: update composer dependencies"
```

---

## 📁 Struktur Folder Penting

```
app/
├── Http/
│   ├── Controllers/     ← Semua controller
│   └── Requests/        ← Form request validation
├── Models/              ← Eloquent models
└── Services/            ← Business logic (opsional)

database/
├── migrations/          ← Skema database
└── seeders/             ← Data dummy

routes/
├── api.php              ← API routes (jika pakai API)
└── web.php              ← Web routes

resources/
└── views/               ← Blade templates
```

---

## 👥 Tim

| Nama        | Role                  | GitHub          |
|-------------|-----------------------|-----------------|
| Developer 1 | Backend / Database    | @username1      |
| Developer 2 | Backend / Feature     | @username2      |

---

## 🔒 Catatan Penting

- File `.env` **jangan di-push** ke GitHub (sudah ada di `.gitignore`)
- Setiap mulai kerja selalu `git pull` dulu dari `develop`
- Kalau ada konflik, selesaikan dulu sebelum push
- Kalau ragu, tanya ke anggota tim dulu sebelum merge ke `develop`

---

<p align="center">Made with ❤️ for PKL</p>

$merchant->id; 869e3adb-3b07-42a0-9453-59219f50dd3f
$product->id; 627672c8-1d75-4083-9b0c-3a06a9326d18
$slot?->id; a94e8dbf-3ab9-4a74-8809-340124e4323b
