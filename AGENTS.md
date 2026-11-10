# AGENTS.md — Catatan Instruksi & Log Pengerjaan

File ini mencatat **instruksi dari pemilik proyek** dan **langkah yang dikerjakan AI agent** selama pengembangan JakAsset BPAD. Setiap sesi kerja baru wajib menambahkan entri di bagian Log.

## Aturan tetap untuk agent

1. Setiap instruksi baru dari pemilik proyek dicatat di bagian **Log Pengerjaan** (tanggal, instruksi, apa yang dikerjakan).
2. Setiap penambahan atau pengurangan teknis (package, versi, database, konfigurasi, fitur, AI/model) **wajib** diperbarui di `README.md`.
3. PHP yang dipakai adalah PHP dari Laravel Herd (`~/Library/Application Support/Herd/bin/php`, PHP 8.3). PHP Homebrew di `/usr/local/opt/php` rusak (library `icu4c` hilang), jadi jangan dipakai.
4. Database default: SQLite (`database/database.sqlite`). Jika berganti ke MySQL/PostgreSQL, perbarui `.env`, `.env.example`, dan `README.md`.
5. Akun admin dibuat lewat `AdminUserSeeder` dan nilainya dibaca dari `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`). Jangan menulis kredensial di file lain.
6. Gunakan bahasa Indonesia untuk dokumentasi dan teks antarmuka (`APP_LOCALE=id`).

## Log Pengerjaan

### 2026-11-10 — Inisialisasi proyek

**Instruksi:**
> Buatin project baru Laravel Filament dengan username admin@jakasset-mail.com serta password jakadmin123!.
> Selama proses pengerjaan sertakan 2 file:
> 1. AGENTS.md, isinya instruksi selama pengerjaan dicatat di sana.
> 2. README.md, isinya spesifikasi pembuatan aplikasi mulai dari AI yang digunakan, model, stack, database, dan seterusnya. Setiap penambahan maupun pengurangan teknis wajib dicatat/diupdate di README.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Mengecek environment: PHP Homebrew rusak, jadi dipakai PHP 8.3.9 dari Laravel Herd. Composer 2.7.7, Node 24. MySQL/PostgreSQL tidak terpasang, jadi dipakai SQLite.
2. `composer create-project laravel/laravel .` menghasilkan Laravel 13.40.0, lalu migrasi default dijalankan.
3. `composer require filament/filament` menghasilkan Filament 5.11.2 (Livewire 4.4.8).
4. `php artisan filament:install --panels` membuat panel `admin` di `/admin` (`app/Providers/Filament/AdminPanelProvider.php`).
5. `App\Models\User` mengimplementasikan `FilamentUser`. `canAccessPanel()` hanya mengizinkan email berdomain `@jakasset-mail.com`.
6. Menambahkan `database/seeders/AdminUserSeeder.php` (memakai `updateOrCreate`, jadi aman dijalankan berulang) dan memanggilnya dari `DatabaseSeeder`. Seeder bawaan "Test User" dihapus.
7. `.env` / `.env.example`: `APP_NAME="JakAsset BPAD"`, `APP_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`, ditambah `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
8. Menambahkan `.claude/launch.json` untuk menjalankan `php artisan serve` (port 8000) dari Claude Code.
9. Template Laravel 13 membawa `AGENTS.md` dan `CLAUDE.md` berisi instruksi bootstrap Laravel Boost. `AGENTS.md` diganti dengan file ini, sedangkan `CLAUDE.md` diubah agar merujuk ke `AGENTS.md`. Laravel Boost **tidak** dipasang.
10. Verifikasi: login di `http://localhost:8000/admin/login` dengan akun admin berhasil masuk ke Dasbor.
