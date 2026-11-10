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
8. Warna kendaraan tidak punya menu sendiri; diatur lewat field Warna Tersedia di form Master Data > Kendaraan. Menu Perangkat Daerah hanya untuk melihat data. Tambah/ubah/hapus data dilakukan di Master Data > Instansi Pemilik.
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

### 2026-11-10 — AV-001 Revisi 1: Nomor Asset tidak ditampilkan di form

**Instruksi:**
> Field tidak perlu ditampilkan di form / user nggak perlu tahu, jadikan dia berjalan di backend aja. (dengan screenshot field Nomor Asset di form)

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Field `nomor_asset` dihapus dari `KendaraanDinasForm.php`. Pembuatan nomor tetap otomatis lewat `KendaraanDinas::creating`.
2. Nomor Asset tetap tampil di tabel, halaman detail, detail Perangkat Daerah, dan pencarian global.
3. Test diperbarui: field dipastikan tidak ada di form tambah & ubah. `php artisan test`: 16 test lolos. Dicek di browser: halaman tambah tidak lagi menampilkan Nomor Asset.
4. `ADDED_VALUE.md` diperbarui: spesifikasi AV-001 disesuaikan dan entri "Revisi 1" ditambahkan.
5. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.

### 2026-11-10 — Added Value AV-002: Field Status kendaraan

**Instruksi:**
> Pada form Status yang berisi Active, Inactive, Standby catat itu sebagai added value.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Field Status (enum `StatusKendaraan`: Active, Inactive, Standby) sudah ada sejak tahap menu CRUD di form Master Data > Kendaraan dan Asset > Kendaraan Dinas. Tidak ada perubahan kode.
2. `ADDED_VALUE.md`: entri AV-002 berisi prompt asli, arti tiap status, spesifikasi, file yang terlibat, dan verifikasi. Tabel daftar Added Value diperbarui.
3. `README.md`: baris Status di tabel enum ditandai AV-002, riwayat perubahan ditambah.
4. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.

### 2026-11-10 — Warna Kendaraan menjadi master data

**Instruksi:**
> Warna kendaraan masukin sebagai entitas dari data master kendaraan, supaya data lebih terjaga kerapihan dan standarisasinya.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Migration `2026_11_10_100005`: tabel `warna_kendaraan` (nama unik, kode_hex opsional). Kolom teks `kendaraan_dinas.warna` diganti FK `warna_kendaraan_id` (`restrictOnDelete`). Isian teks lama dipindahkan ke master (dirapikan ke Title Case) lalu kolom lama dihapus. Migration bisa di-rollback.
2. Model `WarnaKendaraan` (mutator merapikan nama ke Title Case dengan spasi tunggal), relasi `KendaraanDinas::warnaKendaraan()`.
3. Menu baru Master Data > Warna Kendaraan (CRUD, color picker, contoh warna di tabel, hapus memakai `DependencyGuard`). Urutan Master Data: Kendaraan, Warna Kendaraan, Instansi Pemilik.
4. Kendaraan Dinas: Warna Kendaraan sekarang dropdown dari master (wajib), ditambah filter Warna di tabel.
5. Validasi unik nama warna membandingkan nama yang sudah dirapikan, jadi "hitam", "HITAM", dan "Hitam" dianggap sama. (Saat test, ditemukan bug: validasi unik bawaan berjalan sebelum nama dirapikan; sudah diperbaiki.)
6. `WarnaKendaraanSeeder` (selalu dijalankan): 12 warna standar.
7. 2 test baru (nama warna dirapikan & unik, hapus warna yang dipakai diblokir). `php artisan test`: 18 test lolos. Dicek di browser: menu warna dan dropdown di form Kendaraan Dinas tampil benar.
8. Bukan prompt "Added Value", jadi tidak dicatat di `ADDED_VALUE.md`. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.

### 2026-11-10 — Warna Kendaraan digabung ke form Master Data > Kendaraan

**Instruksi:**
> Jadiin satu menu Master - Warna Kendaraan ke dalam form yang berada pada menu http://localhost:8000/admin/master-data/kendaraan. Agar menu menjadi lebih compact dan memudahkan user. Tidak perlu membuat menu master warna berdiri sendiri.

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Menu Master Data > Warna Kendaraan dihapus (`app/Filament/Resources/WarnaKendaraan/`). Tabel `warna_kendaraan` tetap dipakai sebagai daftar warna standar.
2. Migration `2026_11_10_100006`: pivot `master_kendaraan_warna`. Kombinasi master + warna yang sudah dipakai kendaraan dinas otomatis dimasukkan.
3. Form Master Data > Kendaraan: field **Warna Tersedia** (pilih banyak, wajib), dengan tombol + untuk menambah warna baru (nama dirapikan & dicek dobel). Warna yang masih dipakai kendaraan dinas bertipe itu tidak bisa dikeluarkan; form menampilkan pesan berisi nomor polisi pemakainya. Warna Tersedia juga tampil di tabel & detail master.
4. Form Kendaraan Dinas: pilihan warna hanya dari Warna Tersedia master terpilih (divalidasi), dikosongkan saat merek/tipe/tahun berubah, dan terisi otomatis jika master hanya punya satu warna.
5. `MasterKendaraanSeeder` memberi Warna Tersedia untuk 10 master dummy (`syncWithoutDetaching`, tidak menghapus warna yang diatur pengguna). Master "Toyota Innova Zenix 2.0 G (2025)" milik pengguna belum punya warna.
6. Test: test menu warna diganti dengan 4 test baru (simpan Warna Tersedia, tambah warna baru via tombol +, warna terpakai tidak bisa dikeluarkan, warna Kendaraan Dinas harus dari master). `php artisan test`: 20 test lolos. Dicek di browser: menu warna hilang, field Warna Tersedia dan modal tambah warna tampil benar.
7. Catatan: `php artisan db:seed` membuat sesi login admin berakhir, karena `AdminUserSeeder` meng-hash ulang password.
8. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.

### 2026-11-10 — Relasi Pegawai ke Instansi, Pemegang wajib

**Instruksi:**
> Pegawai memiliki relasi dengan instansi pemilik, hal ini penting dilakukan untuk menjaga kualitas data yang baik / menghindari redundant. Kemudian field pegawai pada form yang berada di menu http://localhost:8000/admin/asset/kendaraan-dinas sifatnya mandatory. (dengan screenshot section Kepemilikan & Pemegang)

**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

1. Migration `2026_11_10_100007`: `pegawai.instansi_id` (FK ke `instansi`, `restrictOnDelete`). Nullable di database agar data lama valid; pegawai yang sudah jadi pemegang otomatis diisi instansi kendaraannya.
2. Model: `Pegawai::instansi()`, `Instansi::pegawai()`.
3. Form Pegawai: field Instansi (wajib). Pegawai yang masih memegang kendaraan dinas instansi lain tidak bisa dipindah instansi. Tabel Pegawai: kolom & filter Instansi.
4. Form Kendaraan Dinas: Pemegang wajib, pilihan hanya pegawai dari Perangkat Daerah pemilik (divalidasi dengan `in`), dikosongkan saat perangkat daerah diganti.
5. Hapus Instansi Pemilik juga dicek terhadap Pegawai (`InstansiResource::DEPENDENCIES`).
6. Detail Perangkat Daerah: daftar pegawai (read-only) dan jumlah pegawai.
7. Perbaikan tampilan yang ditemukan saat cek browser: Filament menyembunyikan tanda * pada field yang dikunci, jadi field turunan (Tipe/Model, Tahun, Warna, Pemegang) tidak lagi dikunci, dan placeholder-nya dibuat statis. Kolom Instansi di tabel Pegawai dibatasi satu baris + tooltip.
8. `PegawaiSeeder`: 10 pegawai dummy dibagi ke Disdik (4), Dinkes (3), PUPR (3).
9. 5 test baru (pegawai wajib punya instansi, pemegang wajib & harus dari instansi pemilik, ganti perangkat daerah mengosongkan pemegang, pegawai pemegang tidak bisa pindah instansi, hapus instansi yang punya pegawai diblokir). `php artisan test`: 25 test lolos. Dicek di browser.
10. Di-commit dan di-push ke `main` atas permintaan pemilik proyek.
