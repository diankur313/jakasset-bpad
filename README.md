# JakAsset BPAD

Aplikasi panel admin berbasis **Laravel + Filament**.

> **Wajib:** setiap penambahan atau pengurangan teknis (package, versi, database, konfigurasi, fitur, AI/model) harus diperbarui di file ini dan dicatat di bagian [Riwayat Perubahan Teknis](#riwayat-perubahan-teknis). Instruksi kerja dicatat di [`AGENTS.md`](AGENTS.md).

---

## 1. AI yang Digunakan

| Item          | Keterangan                                  |
|---------------|---------------------------------------------|
| AI Agent      | Claude Code (Claude desktop app, tab Code)  |
| Penyedia      | Anthropic                                   |
| Model         | Claude Opus 5.5                             |
| Model ID      | `claude-opus-5-5`                           |
| Peran         | Scaffolding proyek, konfigurasi, dokumentasi |

## 2. Stack Teknologi

### Backend
| Komponen        | Versi     | Keterangan                    |
|-----------------|-----------|-------------------------------|
| PHP             | 8.3.9     | Dari Laravel Herd             |
| Laravel         | 13.40.0   | `laravel/framework`           |
| Filament        | 5.11.2    | Panel admin (`filament/filament`) |
| Livewire        | 4.4.8     | Dependensi Filament           |
| Composer        | 2.7.7     | Package manager PHP           |
| PHPUnit         | 12.5.43   | Testing                       |

### Frontend / Build
| Komponen            | Versi   |
|---------------------|---------|
| Node.js             | 24.x    |
| Vite                | ^8.0    |
| Tailwind CSS        | ^4.0    |
| laravel-vite-plugin | ^3.1    |

> Aset Filament (CSS/JS/font) dipublikasikan ke `public/` oleh `php artisan filament:install`, jadi panel admin tidak butuh `npm run build`.

### Database
| Item     | Nilai                                   |
|----------|-----------------------------------------|
| Engine   | SQLite                                  |
| File     | `database/database.sqlite`              |
| Session  | `database` driver                       |
| Cache    | `database` driver                       |
| Queue    | `database` driver                       |

## 3. Konfigurasi Aplikasi

| Variabel `.env`    | Nilai                     |
|--------------------|---------------------------|
| `APP_NAME`         | `"JakAsset BPAD"`         |
| `APP_LOCALE`       | `id` (Bahasa Indonesia)   |
| `APP_FAKER_LOCALE` | `id_ID`                   |
| `DB_CONNECTION`    | `sqlite`                  |
| `ADMIN_NAME`       | `Administrator`           |
| `ADMIN_EMAIL`      | `admin@jakasset-mail.com` |
| `ADMIN_PASSWORD`   | `jakadmin123!`            |

### Panel Admin (Filament)
- **Panel ID:** `admin`
- **URL:** `/admin` (login: `/admin/login`)
- **Provider:** `app/Providers/Filament/AdminPanelProvider.php`
- **Warna utama:** Amber
- **Hak akses:** `App\Models\User::canAccessPanel()` hanya mengizinkan email berdomain `@jakasset-mail.com`.

### Akun Default
| Email                     | Password       |
|---------------------------|----------------|
| admin@jakasset-mail.com   | jakadmin123!   |

Akun dibuat oleh `database/seeders/AdminUserSeeder.php` dari variabel `ADMIN_*` di `.env`. **Ganti password ini sebelum deploy ke production.**

## 4. Struktur Penting

```
app/
├── Models/User.php                         # implements FilamentUser
└── Providers/Filament/AdminPanelProvider.php
database/
├── database.sqlite
└── seeders/
    ├── DatabaseSeeder.php
    └── AdminUserSeeder.php
.claude/launch.json                         # konfigurasi dev server untuk Claude Code
AGENTS.md                                   # log instruksi & pengerjaan
CLAUDE.md                                   # merujuk ke AGENTS.md (untuk Claude Code)
README.md                                   # spesifikasi teknis (file ini)
```

## 5. Instalasi & Menjalankan

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Lalu buka http://localhost:8000/admin dan login dengan akun default di atas.

> Di mesin pengembang ini, gunakan PHP dari Herd:
> `export PATH="$HOME/Library/Application Support/Herd/bin:$PATH"`

## Riwayat Perubahan Teknis

| Tanggal    | Perubahan | Oleh |
|------------|-----------|------|
| 2026-11-10 | Inisialisasi proyek: Laravel 13.40.0, Filament 5.11.2, SQLite, panel `admin`, seeder akun admin, locale `id`. | Claude Code (Opus 5.5) |
