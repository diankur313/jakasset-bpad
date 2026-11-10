# JakAsset BPAD

Aplikasi panel admin berbasis **Laravel + Filament**.

> **Wajib:** setiap penambahan atau pengurangan teknis (package, versi, database, konfigurasi, fitur, AI/model) harus diperbarui di file ini dan dicatat di bagian [Riwayat Perubahan Teknis](#riwayat-perubahan-teknis). Instruksi kerja dicatat di [`AGENTS.md`](AGENTS.md). Fitur tambahan (prompt berawalan "Added Value") dicatat di [`ADDED_VALUE.md`](ADDED_VALUE.md).

**Repositori:** https://github.com/diankur313/jakasset-bpad (branch `main`)

---

## 1. AI yang Digunakan

| Item          | Keterangan                                  |
|---------------|---------------------------------------------|
| AI Agent      | Claude Code (Claude desktop app, tab Code)  |
| Penyedia      | Anthropic                                   |
| Model         | Claude Opus 5.5                             |
| Model ID      | `claude-opus-5-5`                           |
| Peran         | Scaffolding proyek, konfigurasi, modul CRUD, testing, dokumentasi |

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
| `APP_TIMEZONE`     | `Asia/Jakarta` (WIB)      |
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

## 4. Menu & Fitur

Urutan menu di sidebar (Filament selalu menaruh menu tanpa grup di atas grup):

| Menu | URL | Fungsi | Keterangan |
|------|-----|--------|------------|
| Pegawai | `/admin/pegawai` | CRUD | Form: Nama, NIP (18 digit, unik), Jabatan, Golongan (I/a s.d. IV/e) |
| Perangkat Daerah | `/admin/perangkat-daerah` | **Hanya lihat** | Sumber data: Master Data > Instansi Pemilik. Halaman detail menampilkan daftar kendaraan dinas milik perangkat daerah tersebut. |
| **Asset** > Kendaraan Dinas | `/admin/asset/kendaraan-dinas` | CRUD (data transaksi) | Nomor Asset dibuat otomatis di backend (tidak ada di form). Form: Nomor Polisi, Merek, Tipe / Model, Tahun Produksi, Warna Kendaraan (dropdown dari master), Kondisi, Status, Perangkat Daerah (pemilik), Pemegang (pegawai) |
| **Master Data** > Kendaraan | `/admin/master-data/kendaraan` | CRUD | Form: Merek, Tipe / Model, Tahun Produksi, Kondisi, Status. Kombinasi Merek + Tipe + Tahun harus unik. |
| **Master Data** > Warna Kendaraan | `/admin/master-data/warna-kendaraan` | CRUD | Form: Nama Warna (unik, otomatis dirapikan ke Title Case), Contoh Warna (kode hex, opsional) |
| **Master Data** > Instansi Pemilik | `/admin/master-data/instansi` | CRUD | Form: Nama Instansi (unik), Singkatan, Alamat |

### Pilihan (enum)
| Field | Pilihan | File |
|-------|---------|------|
| Kondisi | Baik, Rusak Ringan, Rusak Berat | `app/Enums/KondisiKendaraan.php` |
| Status (Added Value AV-002) | Active, Inactive, Standby | `app/Enums/StatusKendaraan.php` |
| Golongan | I/a, I/b, ..., IV/e | `App\Models\Pegawai::GOLONGAN` |

### Alur data transaksi Kendaraan Dinas
- **Merek → Tipe / Model → Tahun Produksi** adalah dropdown berjenjang dari Master Data > Kendaraan. Yang disimpan hanya `master_kendaraan_id`.
- Saat Tahun Produksi dipilih, **Kondisi** dan **Status** otomatis terisi dari master, tetapi masih bisa diubah per kendaraan.
- **Warna Kendaraan** (wajib) dipilih dari Master Data > Warna Kendaraan, bukan diketik bebas, supaya penulisan warna seragam.
- **Perangkat Daerah** (wajib) diambil dari tabel `instansi`. **Pemegang** (opsional) diambil dari Pegawai.
- Nomor polisi otomatis diubah ke huruf besar dan divalidasi formatnya (contoh `B 1234 ABC`).

### Nomor Asset (Added Value AV-001)
- Format **`YYYY-mm-dd/KENDARAAN/0001`**, dibuat otomatis di backend saat kendaraan dinas pertama kali disimpan, unik, dan tidak bisa diubah.
- Tidak ada di form tambah/ubah. Hanya ditampilkan di tabel, halaman detail, detail Perangkat Daerah, dan pencarian global.
- Tanggal = tanggal pencatatan (WIB). Running number 4 digit dihitung per jenis asset per tanggal (mulai `0001` tiap hari) dan tidak dipakai ulang walaupun datanya dihapus.
- Generator: `app/Support/NomorAsset.php`, penghitung di tabel `nomor_asset_sequences`. Detail lengkap di [`ADDED_VALUE.md`](ADDED_VALUE.md).

### Penghapusan data & dependensi
Semua tombol hapus memakai `app/Filament/Support/DependencyGuard.php`:
- **Hapus satu data:** jika data masih dipakai, modal berubah menjadi alert **"Data tidak dapat dihapus"** berisi jumlah dan contoh nomor polisi yang memakai data itu, dan tombol hapus disembunyikan. Jika tidak dipakai, muncul konfirmasi hapus biasa.
- **Hapus massal:** modal menyebut data mana yang masih dipakai dan akan dilewati. Data lainnya tetap dihapus, lalu muncul notifikasi "N data dihapus, M data dilewati".
- **Pengaman database:** foreign key `restrictOnDelete`, jadi data master yang masih dipakai juga tidak bisa terhapus lewat jalur lain.

| Data yang dihapus | Dicek terhadap |
|-------------------|----------------|
| Master Kendaraan | Kendaraan Dinas |
| Instansi Pemilik | Kendaraan Dinas |
| Pegawai | Kendaraan Dinas (sebagai pemegang) |
| Warna Kendaraan | Kendaraan Dinas |
| Kendaraan Dinas | Tidak ada (konfirmasi biasa) |

## 5. Struktur Database

```
instansi           id, nama (unik), singkatan, alamat, timestamps
master_kendaraan   id, merek, tipe_model, tahun_produksi, kondisi, status, timestamps
                   unik: (merek, tipe_model, tahun_produksi)
pegawai            id, nama, nip (unik, 18), jabatan, golongan, timestamps
warna_kendaraan    id, nama (unik), kode_hex (nullable), timestamps
kendaraan_dinas    id, nomor_asset (unik, otomatis), nomor_polisi (unik),
                   master_kendaraan_id → master_kendaraan,
                   warna_kendaraan_id → warna_kendaraan, kondisi, status, instansi_id → instansi,
                   pegawai_id → pegawai (nullable), timestamps
                   semua foreign key: ON DELETE RESTRICT
nomor_asset_sequences
                   id, jenis_asset, tanggal, nomor_terakhir, timestamps
                   unik: (jenis_asset, tanggal); penghitung running number nomor asset
```

### Seeder

| Seeder | Isi | Kapan dijalankan |
|--------|-----|------------------|
| `AdminUserSeeder` | Akun admin dari `ADMIN_*` di `.env` | Selalu |
| `InstansiSeeder` | Dinas Pendidikan, Dinas Kesehatan, Dinas Pekerjaan Umum dan Penataan Ruang (PUPR) | Selalu |
| `WarnaKendaraanSeeder` | 12 warna standar: Hitam, Putih, Silver, Abu-Abu, Merah, Merah Marun, Biru, Biru Dongker, Hijau, Kuning, Oranye, Coklat | Selalu |
| `PegawaiSeeder` | 10 pegawai dummy (NIP 18 digit, golongan II/a s.d. IV/b) | Hanya `APP_ENV=local` |
| `MasterKendaraanSeeder` | 10 master kendaraan dummy (Toyota, Mitsubishi, Suzuki, Daihatsu, Isuzu, Honda) dengan kondisi & status bervariasi | Hanya `APP_ENV=local` |

Semua seeder memakai `updateOrCreate` / `firstOrCreate`, jadi aman dijalankan berulang (`php artisan db:seed`). Data dummy master kendaraan sengaja memuat merek dengan beberapa tipe dan satu tipe dengan dua tahun (Toyota Avanza 2019 & 2022) untuk mencoba dropdown berjenjang di Kendaraan Dinas.

## 6. Struktur Penting

```
app/
├── Enums/                                  # KondisiKendaraan, StatusKendaraan
├── Filament/
│   ├── Resources/
│   │   ├── KendaraanDinas/                 # Asset > Kendaraan Dinas
│   │   ├── Pegawai/
│   │   ├── MasterKendaraan/                # Master Data > Kendaraan
│   │   ├── WarnaKendaraan/                 # Master Data > Warna Kendaraan
│   │   ├── Instansi/                       # Master Data > Instansi Pemilik
│   │   └── PerangkatDaerah/                # read-only, model Instansi
│   └── Support/DependencyGuard.php         # aksi hapus + cek dependensi
├── Support/NomorAsset.php                  # generator nomor asset (AV-001)
├── Models/                                 # User, Instansi, MasterKendaraan, WarnaKendaraan, Pegawai, KendaraanDinas
└── Providers/Filament/AdminPanelProvider.php
database/
├── database.sqlite
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    ├── AdminUserSeeder.php
    ├── InstansiSeeder.php
    ├── WarnaKendaraanSeeder.php
    ├── PegawaiSeeder.php                   # dummy, hanya local
    └── MasterKendaraanSeeder.php           # dummy, hanya local
tests/Feature/AssetCrudTest.php             # test CRUD & alert hapus
.claude/launch.json                         # konfigurasi dev server untuk Claude Code
AGENTS.md                                   # log instruksi & pengerjaan
ADDED_VALUE.md                              # catatan fitur Added Value
CLAUDE.md                                   # merujuk ke AGENTS.md (untuk Claude Code)
README.md                                   # spesifikasi teknis (file ini)
```

## 7. Instalasi & Menjalankan

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Lalu buka http://localhost:8000/admin dan login dengan akun default di atas.

Menjalankan test:

```bash
php artisan test
```

> Di mesin pengembang ini, gunakan PHP dari Herd:
> `export PATH="$HOME/Library/Application Support/Herd/bin:$PATH"`

## Riwayat Perubahan Teknis

| Tanggal    | Perubahan | Oleh |
|------------|-----------|------|
| 2026-11-10 | Inisialisasi proyek: Laravel 13.40.0, Filament 5.11.2, SQLite, panel `admin`, seeder akun admin, locale `id`. | Claude Code (Opus 5.5) |
| 2026-11-10 | Menu CRUD: Asset > Kendaraan Dinas, Pegawai, Master Data > Kendaraan & Instansi Pemilik; menu Perangkat Daerah (read-only). 4 tabel baru dengan FK `restrictOnDelete`, enum Kondisi/Status, `DependencyGuard` (alert hapus berdependensi), `InstansiSeeder`, 11 feature test. | Claude Code (Opus 5.5) |
| 2026-11-10 | Inisialisasi git (branch `main`) dan push ke GitHub `diankur313/jakasset-bpad`. History disusun per tahap pengerjaan. | Claude Code (Opus 5.5) |
| 2026-11-10 | Data dummy: `PegawaiSeeder` (10 pegawai) dan `MasterKendaraanSeeder` (10 master kendaraan), dipanggil dari `DatabaseSeeder` hanya saat `APP_ENV=local`. | Claude Code (Opus 5.5) |
| 2026-11-10 | **Added Value AV-001:** Nomor Asset otomatis di Kendaraan Dinas (`YYYY-mm-dd/KENDARAAN/0001`), tabel `nomor_asset_sequences`, `APP_TIMEZONE=Asia/Jakarta`, file `ADDED_VALUE.md`, 3 test baru (total 16). | Claude Code (Opus 5.5) |
| 2026-11-10 | AV-001 Revisi 1: field Nomor Asset dihapus dari form Kendaraan Dinas (tetap dibuat otomatis di backend, tampil di tabel & detail). | Claude Code (Opus 5.5) |
| 2026-11-10 | Field Status (Active, Inactive, Standby) dicatat sebagai Added Value AV-002 di `ADDED_VALUE.md`. Tidak ada perubahan kode. | Claude Code (Opus 5.5) |
| 2026-11-10 | Master Data > Warna Kendaraan (tabel `warna_kendaraan`, seeder 12 warna standar). Kolom teks `kendaraan_dinas.warna` diganti FK `warna_kendaraan_id` (`restrictOnDelete`), data lama dipindahkan otomatis oleh migration. 2 test baru (total 18). | Claude Code (Opus 5.5) |
