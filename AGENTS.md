# AGENTS.md — Catatan Instruksi & Log Pengerjaan

File ini mencatat **instruksi dari pemilik proyek** dan **langkah yang dikerjakan AI agent** selama pengembangan JakAsset BPAD. Setiap sesi kerja baru wajib menambahkan entri di bagian Log.

## Aturan tetap untuk agent

1. Setiap instruksi baru dari pemilik proyek dicatat di bagian **Log Pengerjaan** (tanggal, instruksi, apa yang dikerjakan).
2. Setiap penambahan atau pengurangan teknis (package, versi, database, konfigurasi, fitur, AI/model) **wajib** diperbarui di `README.md`.
3. PHP yang dipakai adalah PHP dari Laravel Herd (`~/Library/Application Support/Herd/bin/php`, PHP 8.3). PHP Homebrew di `/usr/local/opt/php` rusak (library `icu4c` hilang), jadi jangan dipakai.
4. Database default: SQLite (`database/database.sqlite`). Jika berganti ke MySQL/PostgreSQL, perbarui `.env`, `.env.example`, dan `README.md`.
5. Akun admin dibuat lewat `AdminUserSeeder` dan nilainya dibaca dari `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`). Jangan menulis kredensial di file lain.
6. Gunakan bahasa Indonesia untuk dokumentasi dan teks antarmuka (`APP_LOCALE=id`).
7. Setiap tombol hapus wajib memakai `App\Filament\Support\DependencyGuard` (`deleteAction` / `deleteBulkAction`) dan setiap resource mendeklarasikan `DEPENDENCIES`. Jika menambah tabel yang mereferensikan data lain, tambahkan relasinya ke `DEPENDENCIES` resource induk dan pakai foreign key `restrictOnDelete`.
8. Menu Perangkat Daerah hanya untuk melihat data. Tambah/ubah/hapus data dilakukan di Master Data > Instansi Pemilik.
9. Jalankan `php artisan test` setelah perubahan dan pastikan semua lolos.
10. Repositori: https://github.com/diankur313/jakasset-bpad (branch `main`). Commit hanya bila diminta pemilik proyek; satu commit per tahap pekerjaan, pesan commit berbahasa Indonesia.
11. Prompt yang diawali keterangan **"Added Value"** wajib dicatat juga di `ADDED_VALUE.md` sebagai entri baru (AV-xxx): prompt asli, penjelasan, spesifikasi yang diterapkan, file yang terlibat, dan verifikasi.
12. Timezone aplikasi `Asia/Jakarta` (`APP_TIMEZONE`). Nomor asset dan tanggal lain mengikuti WIB.

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

### 2026-11-10 — Menu CRUD Asset, Pegawai, Master Data, Perangkat Daerah

**Instruksi:**
> Buat menu berikut yang memuat fungsi CRUD (perhatikan ketika penghapusan data akan berkaitan dengan dependency data lainnya, wajib memberi alert):
> 1. Asset (parent) > Kendaraan Dinas (child). Form add: Nomor Polisi, Merek, Tipe / Model, Tahun Produksi, Warna Kendaraan, Kondisi, Status.
> 2. Pegawai. Form add: Nama, NIP, Jabatan, Golongan.
> 3. Master Data:
>    - Kendaraan: Merek, Tipe / Model, Tahun Produksi, Kondisi (Baik, Rusak Ringan, Rusak Berat), Status (Active, Inactive, Standby). Dipakai di data transaksi poin 1.
>    - Data Instansi Pemilik Kendaraan (misal Dinas Pendidikan, Dinas Kesehatan, Dinas PUPR).
> 4. Perangkat Daerah: hanya view data, karena datanya dibuat di Data Instansi Pemilik Kendaraan dan dipakai sebagai data transaksi.
>
> Poin 4 dan poin 2 dipakai sebagai data transaksi di poin 1.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Migration: tabel `instansi`, `master_kendaraan` (unik merek+tipe+tahun), `pegawai` (NIP unik), `kendaraan_dinas` (FK ke master_kendaraan, instansi, pegawai; semua `restrictOnDelete`).
2. Model `Instansi`, `MasterKendaraan`, `Pegawai`, `KendaraanDinas` beserta relasinya. Enum `KondisiKendaraan` dan `StatusKendaraan` (label + warna badge).
3. `App\Filament\Support\DependencyGuard`: aksi hapus tunggal yang berubah menjadi alert "Data tidak dapat dihapus" (beserta contoh nomor polisi pemakai) jika data masih dipakai, dan aksi hapus massal yang melewati data terpakai lalu memberi notifikasi.
4. Resource Filament:
   - Asset > Kendaraan Dinas: Merek → Tipe / Model → Tahun Produksi berupa dropdown berjenjang dari Master Kendaraan. Kondisi & Status otomatis terisi dari master (bisa diubah). Ditambah Perangkat Daerah (wajib) dan Pemegang/Pegawai (opsional), karena poin 2 & 4 dipakai di transaksi ini.
   - Pegawai: NIP divalidasi 18 digit dan unik, Golongan berupa pilihan I/a s.d. IV/e.
   - Master Data > Kendaraan dan Master Data > Instansi Pemilik.
   - Perangkat Daerah: read-only di atas tabel `instansi` (tanpa create/edit/delete), halaman detail menampilkan daftar kendaraan dinas milik perangkat daerah tersebut.
5. Nav group `Asset` dan `Master Data` di `AdminPanelProvider`. Catatan: Filament selalu menaruh menu tanpa grup (Pegawai, Perangkat Daerah) di atas grup.
6. `InstansiSeeder`: Dinas Pendidikan, Dinas Kesehatan, Dinas Pekerjaan Umum dan Penataan Ruang.
7. `tests/Feature/AssetCrudTest.php`: 11 test (halaman terbuka, create/edit, validasi, dropdown berjenjang, alert hapus tunggal & massal, Perangkat Daerah read-only). `php artisan test`: 13 test lolos.
8. Cek di browser: alert hapus master kendaraan yang dipakai, form edit kendaraan dinas, dan detail perangkat daerah tampil benar. Data contoh untuk pengecekan sudah dihapus lagi.

### 2026-11-10 — Commit dan push ke GitHub

**Instruksi:**
> Commit dan push ke repo, sertakan history dari awal pembuatan project. https://github.com/diankur313/jakasset-bpad.git

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. `git init -b main`. File yang di-ignore (`.env`, `vendor/`, `database/database.sqlite`, aset Filament di `public/`) tidak ikut di-commit.
2. History disusun per tahap pengerjaan:
   - Commit 1, inisialisasi project: kondisi setelah tahap pertama. `AGENTS.md`, `README.md`, `DatabaseSeeder.php`, dan `AdminPanelProvider.php` memakai versi sebelum menu CRUD ditambahkan.
   - Commit 2, menu CRUD: semua file dari tahap kedua.
   - Commit 3: catatan repositori di `AGENTS.md` dan `README.md`.
3. Remote `origin` = `https://github.com/diankur313/jakasset-bpad.git`, lalu push branch `main`.

### 2026-11-10 — Data dummy Pegawai dan Master Kendaraan

**Instruksi:**
> Buat data dummy 10 untuk pegawai dan master data - kendaraan.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. `database/seeders/PegawaiSeeder.php`: 10 pegawai dengan nama, NIP 18 digit (format tanggal lahir + TMT + jenis kelamin + nomor urut), jabatan, dan golongan yang sesuai jabatannya.
2. `database/seeders/MasterKendaraanSeeder.php`: 10 master kendaraan dinas yang umum (Innova Zenix, Avanza 2019 & 2022, Hilux, Pajero Sport, Xpander, Ertiga, Gran Max, Elf ambulans, Vario 125) dengan kondisi dan status bervariasi.
3. Keduanya dipanggil dari `DatabaseSeeder` hanya saat `APP_ENV=local`, supaya data dummy tidak masuk ke production. Memakai `updateOrCreate`, jadi aman dijalankan berulang.
4. `php artisan db:seed` dijalankan dua kali: Pegawai 10 baris, tidak ada data dobel. Master Kendaraan berisi 11 baris karena sudah ada 1 data sebelumnya (Toyota Innova Zenix 2.0 G, 2025) yang bukan dari seeder dan tidak diubah. `php artisan test`: 13 test lolos.
5. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.

### 2026-11-10 — Added Value AV-001: Nomor Asset otomatis Kendaraan Dinas

**Instruksi:**
> Added Value:
> Pada menu asset - kendaran dinas, tambahkan field nomor asset yang auto tergenerate untuk indentifikasi tiap kendaraan. kode ini bersifat unik dengan format YYYY-mm-dd/jenis asset (dalam hal ini kendaraan)/4 digit running number.
>
> Buat 1 file added value, isinya adalah penjelasan prompt yang gw awalin dengan keterangan added value. Masukin juga data di atas yang udah gw flag added value ke file added value yang baru.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. `app/Support/NomorAsset.php`: generator `YYYY-mm-dd/JENIS/0001`. Running number per jenis asset per tanggal, disimpan di tabel `nomor_asset_sequences`, dinaikkan secara atomik, dan tidak dipakai ulang.
2. Migration `2026_11_10_100004`: kolom `kendaraan_dinas.nomor_asset` (unik) dan tabel `nomor_asset_sequences`, lalu mengisi nomor asset untuk data lama berdasarkan `created_at`.
3. `KendaraanDinas`: konstanta `JENIS_ASSET = 'KENDARAAN'`, hook `creating` membuat nomor asset, hook `updating` mencegah nomor diubah. `nomor_asset` tidak masuk `$fillable`.
4. Filament: field Nomor Asset terkunci di form, tampil di detail, menjadi kolom pertama tabel (juga di detail Perangkat Daerah), dan bisa dicari lewat pencarian global.
5. Timezone aplikasi diubah dari UTC ke `Asia/Jakarta` (`config/app.php` membaca `APP_TIMEZONE`), supaya tanggal di nomor asset sesuai WIB.
6. Membuat `ADDED_VALUE.md` berisi entri AV-001 (prompt asli, penjelasan, spesifikasi, file, verifikasi).
7. 3 test baru di `AssetCrudTest`. `php artisan test`: 16 test lolos. Dicek di browser: kolom dan field terkunci tampil benar. Data dan counter yang dipakai untuk pengecekan sudah dihapus lagi.
8. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.
