# REST API JakAsset BPAD

Dokumentasi REST API untuk semua fungsi panel admin JakAsset BPAD. Aturan bisnisnya **sama persis** dengan panel admin: validasi form, data turunan dari master, nomor asset otomatis, dan cek dependensi saat menghapus.

- **Base URL (lokal):** `http://localhost:8000/api/v1`
- **Format:** JSON (request & response), kecuali endpoint label yang mengembalikan file PDF.
- **Autentikasi:** Bearer token (Laravel Sanctum).
- **Bahasa pesan:** Indonesia.
- **Zona waktu:** WIB (`Asia/Jakarta`). Tanggal dikirim dalam format ISO 8601, contoh `2026-11-10T12:38:26+07:00`.

---

## Daftar Isi

1. [Langkah Cepat (step by step)](#1-langkah-cepat-step-by-step)
2. [Aturan Umum](#2-aturan-umum)
3. [Autentikasi](#3-autentikasi)
4. [Referensi (pilihan dropdown)](#4-referensi-pilihan-dropdown)
5. [Asset > Kendaraan Dinas](#5-asset--kendaraan-dinas)
6. [Pegawai](#6-pegawai)
7. [Master Data > Kendaraan](#7-master-data--kendaraan)
8. [Warna Kendaraan](#8-warna-kendaraan)
9. [Master Data > Instansi Pemilik](#9-master-data--instansi-pemilik)
10. [Perangkat Daerah (read-only)](#10-perangkat-daerah-read-only)
11. [Kode Status & Format Error](#11-kode-status--format-error)
12. [Ringkasan Semua Endpoint](#12-ringkasan-semua-endpoint)

---

## 1. Langkah Cepat (step by step)

Contoh di bawah memakai `curl`. Postman, Insomnia, atau aplikasi lain juga bisa: header dan body-nya sama.

### Langkah 1 — Jalankan aplikasi

```bash
php artisan serve
```

API dapat diakses di `http://localhost:8000/api/v1`.

### Langkah 2 — Login untuk mendapatkan token

Pakai akun panel admin (email & password sesuai `ADMIN_EMAIL` / `ADMIN_PASSWORD` di `.env`). Hanya akun berdomain `@jakasset-mail.com` yang diizinkan.

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"EMAIL_ADMIN","password":"PASSWORD_ADMIN","nama_perangkat":"Postman"}'
```

Respons:

```json
{
  "message": "Login berhasil.",
  "token": "1|AbCdEf...",
  "token_type": "Bearer",
  "user": { "id": 1, "name": "Administrator", "email": "admin@jakasset-mail.com" }
}
```

Simpan nilai `token`. Token hanya ditampilkan sekali.

### Langkah 3 — Kirim token di setiap request

Tambahkan header berikut di semua endpoint selain login:

```
Authorization: Bearer 1|AbCdEf...
```

Contoh, cek akun yang sedang login:

```bash
curl http://localhost:8000/api/v1/auth/me -H "Authorization: Bearer $TOKEN"
```

### Langkah 4 — Ambil data referensi & master

Sebelum menambah kendaraan dinas, ambil ID yang dibutuhkan:

```bash
curl http://localhost:8000/api/v1/referensi -H "Authorization: Bearer $TOKEN"          # pilihan kondisi, status, golongan
curl http://localhost:8000/api/v1/instansi -H "Authorization: Bearer $TOKEN"           # instansi_id
curl "http://localhost:8000/api/v1/pegawai?instansi_id=1" -H "Authorization: Bearer $TOKEN"   # pegawai_id (pemegang)
curl "http://localhost:8000/api/v1/master-kendaraan?merek=Toyota" -H "Authorization: Bearer $TOKEN"  # master_kendaraan_id
curl "http://localhost:8000/api/v1/warna-kendaraan?master_kendaraan_id=1" -H "Authorization: Bearer $TOKEN"  # warna_kendaraan_id
```

### Langkah 5 — Tambah kendaraan dinas

```bash
curl -X POST http://localhost:8000/api/v1/kendaraan-dinas \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"nomor_polisi":"B 1234 ABC","master_kendaraan_id":1,"warna_kendaraan_id":1,"instansi_id":1,"pegawai_id":1}'
```

Nomor Asset dibuat otomatis oleh sistem dan ada di respons (`data.nomor_asset`).

### Langkah 6 — Download label QR

```bash
curl -o label.pdf http://localhost:8000/api/v1/kendaraan-dinas/1/label -H "Authorization: Bearer $TOKEN"
```

### Langkah 7 — Logout

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout -H "Authorization: Bearer $TOKEN"
```

Token yang di-logout langsung tidak berlaku.

---

## 2. Aturan Umum

### Header

| Header | Nilai | Keterangan |
|--------|-------|------------|
| `Authorization` | `Bearer {token}` | Wajib di semua endpoint kecuali login |
| `Content-Type` | `application/json` | Untuk request yang membawa body (POST/PUT/PATCH) |
| `Accept` | `application/json` | Opsional. API selalu menjawab JSON walau header ini tidak dikirim |

### Tambah, ubah penuh, dan ubah sebagian

| Metode | Arti |
|--------|------|
| `POST /resource` | Tambah data. Semua field wajib harus dikirim. |
| `PUT /resource/{id}` | Ubah **penuh**. Semua field wajib harus dikirim. |
| `PATCH /resource/{id}` | Ubah **sebagian**. Cukup kirim field yang berubah; field lain memakai nilai lama. |

### Daftar data (index): paginasi, pencarian, pengurutan, filter

Semua endpoint daftar mendukung query parameter berikut:

| Parameter | Contoh | Keterangan |
|-----------|--------|------------|
| `page` | `page=2` | Halaman ke-n (default 1) |
| `per_page` | `per_page=50` | Jumlah data per halaman (default 15, maksimal 100) |
| `search` | `search=avanza` | Cari teks (kolom yang dicari tertulis di tiap endpoint) |
| `sort` | `sort=nama` / `sort=-nama` | Urutkan naik, atau turun dengan awalan `-` |
| filter | `instansi_id=1` | Filter khusus tiap endpoint |

Format respons daftar:

```json
{
  "data": [ { "...": "..." } ],
  "links": { "first": "...?page=1", "last": "...?page=3", "prev": null, "next": "...?page=2" },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 40, "from": 1, "to": 15, "path": "...", "links": [] }
}
```

Format respons satu data: `{ "data": { ... } }`. Saat tambah atau ubah, ditambah `"message"`.

### Enum

Kondisi dan status dikirim sebagai `value`, dan dikembalikan sebagai objek `{ "value", "label" }`:

| Field | Nilai (`value`) | Label |
|-------|-----------------|-------|
| `kondisi` | `baik`, `rusak_ringan`, `rusak_berat` | Baik, Rusak Ringan, Rusak Berat |
| `status` | `active`, `inactive`, `standby` | Active, Inactive, Standby |

### Hapus data & dependensi

Sama seperti alert di panel admin, data yang **masih dipakai data lain tidak bisa dihapus**:

- `DELETE /resource/{id}` mengembalikan **409 Conflict** berisi daftar data yang memakainya.
- `POST /resource/hapus-massal` menghapus data yang bebas, lalu **melewati** data yang masih dipakai.

Contoh respons 409:

```json
{
  "message": "Data tidak dapat dihapus karena masih digunakan oleh data lain. Hapus atau pindahkan data tersebut terlebih dahulu.",
  "dependensi": [
    { "data": "Kendaraan Dinas", "jumlah": 2, "contoh": ["B 1234 ABC", "B 5678 DEF"] },
    { "data": "Pegawai", "jumlah": 4, "contoh": ["Budi", "Siti", "Andi", "Rina"] }
  ]
}
```

Body hapus massal:

```json
{ "ids": [1, 2, 3] }
```

Respons hapus massal:

```json
{
  "message": "1 data dihapus, 1 data dilewati.",
  "dihapus": [2],
  "dilewati": [ { "id": 1, "nama": "Dinas Pendidikan", "dependensi": [ { "data": "Pegawai", "jumlah": 4, "contoh": ["Budi"] } ] } ],
  "tidak_ditemukan": [3]
}
```

Maksimal 500 ID per request.

---

## 3. Autentikasi

### `POST /auth/login` — Login, mendapatkan token

Tidak butuh token. Dibatasi 10 percobaan per menit.

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `email` | string | Ya | Email akun panel admin (`@jakasset-mail.com`) |
| `password` | string | Ya | Password akun |
| `nama_perangkat` | string | Tidak | Nama token, misalnya `Postman` atau `Aplikasi Android` (default `api`) |

| Status | Arti |
|--------|------|
| 200 | Berhasil, `token` dikembalikan |
| 422 | Email atau password salah |
| 403 | Akun bukan domain `@jakasset-mail.com` |
| 429 | Terlalu banyak percobaan |

### `GET /auth/me` — Akun yang sedang login

Respons: `{ "data": { "id", "name", "email" } }`.

### `POST /auth/logout` — Logout

Mencabut token yang sedang dipakai. Respons: `{ "message": "Logout berhasil. Token sudah tidak berlaku." }`.

---

## 4. Referensi (pilihan dropdown)

### `GET /referensi` — Pilihan Kondisi, Status, dan Golongan

Dipakai aplikasi klien untuk mengisi dropdown.

```json
{
  "data": {
    "kondisi": [ { "value": "baik", "label": "Baik" }, { "value": "rusak_ringan", "label": "Rusak Ringan" }, { "value": "rusak_berat", "label": "Rusak Berat" } ],
    "status": [ { "value": "active", "label": "Active" }, { "value": "inactive", "label": "Inactive" }, { "value": "standby", "label": "Standby" } ],
    "golongan": ["I/a", "I/b", "...", "IV/e"]
  }
}
```

---

## 5. Asset > Kendaraan Dinas

Data kendaraan dinas milik perangkat daerah. Merek, Tipe / Model, Tahun Produksi, **Kondisi, dan Status** dibaca dari Master Kendaraan, jadi tidak dikirim di request. **Nomor Asset** dibuat otomatis (`YYYY-mm-dd/KENDARAAN/0001`) dan tidak bisa diubah.

### `GET /kendaraan-dinas` — Daftar kendaraan dinas

| Parameter | Keterangan |
|-----------|------------|
| `search` | Cari di nomor polisi & nomor asset |
| `nomor_asset` | Cari tepat satu nomor asset (mis. hasil scan QR). Encode `/` menjadi `%2F` |
| `master_kendaraan_id`, `warna_kendaraan_id`, `instansi_id`, `pegawai_id` | Filter berdasarkan ID |
| `merek`, `kondisi`, `status` | Filter berdasarkan data master (mis. `status=active`) |
| `sort` | `nomor_asset`, `nomor_polisi`, `created_at` (default `-created_at`, terbaru dulu) |

Contoh mencari kendaraan dari hasil scan QR label:

```bash
curl "http://localhost:8000/api/v1/kendaraan-dinas?nomor_asset=2026-11-10%2FKENDARAAN%2F0001" -H "Authorization: Bearer $TOKEN"
```

Contoh isi satu data:

```json
{
  "id": 6,
  "nomor_asset": "2026-11-10/KENDARAAN/0006",
  "nomor_polisi": "B 4119 TKU",
  "master_kendaraan_id": 9,
  "merek": "Mitsubishi",
  "tipe_model": "Xpander Ultimate",
  "tahun_produksi": 2023,
  "kondisi": { "value": "baik", "label": "Baik" },
  "status": { "value": "standby", "label": "Standby" },
  "warna_kendaraan_id": 4,
  "warna": { "id": 4, "nama": "Abu-Abu", "kode_hex": "#808080" },
  "instansi_id": 1,
  "instansi": { "id": 1, "nama": "Dinas Pendidikan", "singkatan": "Disdik" },
  "pegawai_id": 3,
  "pemegang": { "id": 3, "nama": "Sri Wahyuni, S.E., M.M.", "nip": "197808242003122002" },
  "url_label": "http://localhost:8000/api/v1/kendaraan-dinas/6/label",
  "created_at": "2026-11-10T12:38:26+07:00",
  "updated_at": "2026-11-10T12:38:26+07:00"
}
```

### `POST /kendaraan-dinas` — Tambah kendaraan dinas

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `nomor_polisi` | string | Ya | Format `B 1234 ABC`, unik. Otomatis dirapikan ke huruf besar & spasi tunggal |
| `master_kendaraan_id` | integer | Ya | ID dari `GET /master-kendaraan` |
| `warna_kendaraan_id` | integer | Ya | Harus termasuk **Warna Tersedia** master terpilih (`GET /warna-kendaraan?master_kendaraan_id=`) |
| `instansi_id` | integer | Ya | Perangkat daerah pemilik |
| `pegawai_id` | integer | Ya | Pemegang, harus **pegawai dari instansi yang sama** (`GET /pegawai?instansi_id=`) |

`nomor_asset`, `kondisi`, dan `status` diabaikan bila dikirim. Respons: **201** dengan data lengkap.

### `GET /kendaraan-dinas/{id}` — Detail kendaraan dinas

### `PUT /kendaraan-dinas/{id}` / `PATCH /kendaraan-dinas/{id}` — Ubah kendaraan dinas

Field sama dengan tambah. Dengan `PATCH`, cukup kirim field yang berubah, misalnya ganti pemegang:

```json
{ "pegawai_id": 5 }
```

Jika `instansi_id` diganti, kirim juga `pegawai_id` dari instansi baru.

### `DELETE /kendaraan-dinas/{id}` — Hapus kendaraan dinas

### `POST /kendaraan-dinas/hapus-massal` — Hapus banyak kendaraan dinas

Body: `{ "ids": [1, 2, 3] }`.

### `GET /kendaraan-dinas/{id}/label` — Download label QR satu kendaraan (AV-003)

Mengembalikan file **PDF 90 × 45 mm**: QR berisi Nomor Asset dengan logo BPAD, lalu Merek, Tipe / Model, Tahun Produksi, Nomor Polisi, dan Nomor Asset. Nama file contohnya `label-2026-11-10-kendaraan-0001.pdf`.

```bash
curl -o label.pdf http://localhost:8000/api/v1/kendaraan-dinas/1/label -H "Authorization: Bearer $TOKEN"
```

### `POST /kendaraan-dinas/label` — Download label QR banyak kendaraan (batch)

Body: `{ "ids": [1, 2, 3] }`. Mengembalikan **PDF A4** berisi 10 label per halaman dengan garis potong. Nama file contohnya `label-kendaraan-dinas-20261110-130501.pdf`. Jika tidak ada ID yang ditemukan, respons 404.

```bash
curl -X POST -o label-batch.pdf http://localhost:8000/api/v1/kendaraan-dinas/label \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d '{"ids":[1,2,3]}'
```

---

## 6. Pegawai

Data pegawai. Setiap pegawai terdaftar di satu instansi dan bisa menjadi pemegang kendaraan dinas milik instansinya.

### `GET /pegawai` — Daftar pegawai

| Parameter | Keterangan |
|-----------|------------|
| `search` | Cari di nama, NIP, jabatan |
| `instansi_id` | Filter instansi (dipakai untuk pilihan pemegang kendaraan dinas) |
| `golongan` | Filter golongan, mis. `III/a` |
| `sort` | `nama` (default), `nip`, `jabatan`, `golongan`, `created_at` |

Contoh isi satu data:

```json
{
  "id": 3, "nama": "Sri Wahyuni, S.E., M.M.", "nip": "197808242003122002",
  "jabatan": "Kepala Subbagian Umum", "golongan": "III/d",
  "instansi_id": 1, "instansi": { "id": 1, "nama": "Dinas Pendidikan", "singkatan": "Disdik" },
  "jumlah_kendaraan_dinas": 2,
  "created_at": "...", "updated_at": "..."
}
```

### `POST /pegawai` — Tambah pegawai

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `nama` | string | Ya | Maks. 255 karakter |
| `nip` | string | Ya | Tepat 18 digit angka, unik |
| `jabatan` | string | Ya | Maks. 255 karakter |
| `golongan` | string | Ya | `I/a` s.d. `IV/e` (lihat `GET /referensi`) |
| `instansi_id` | integer | Ya | ID instansi |

### `GET /pegawai/{id}` — Detail pegawai

### `PUT /pegawai/{id}` / `PATCH /pegawai/{id}` — Ubah pegawai

Pegawai yang **masih memegang kendaraan dinas** tidak bisa dipindah ke instansi lain (422). Ganti pemegang kendaraannya terlebih dahulu.

### `DELETE /pegawai/{id}` — Hapus pegawai

Ditolak (409) jika pegawai masih menjadi pemegang kendaraan dinas.

### `POST /pegawai/hapus-massal` — Hapus banyak pegawai

---

## 7. Master Data > Kendaraan

Data acuan kendaraan: Merek, Tipe / Model, Tahun Produksi, Kondisi, Status, dan Warna Tersedia. Dipakai di Kendaraan Dinas.

### `GET /master-kendaraan` — Daftar master kendaraan

| Parameter | Keterangan |
|-----------|------------|
| `search` | Cari di merek & tipe / model |
| `merek`, `tipe_model`, `tahun_produksi` | Filter tepat (untuk pilihan berjenjang Merek → Tipe → Tahun) |
| `kondisi`, `status` | Filter enum, mis. `status=active` |
| `sort` | `merek` (default), `tipe_model`, `tahun_produksi`, `created_at` |

Contoh isi satu data:

```json
{
  "id": 1, "label": "Toyota Avanza 1.5 G (2022)",
  "merek": "Toyota", "tipe_model": "Avanza 1.5 G", "tahun_produksi": 2022,
  "kondisi": { "value": "baik", "label": "Baik" },
  "status": { "value": "active", "label": "Active" },
  "warna": [ { "id": 1, "nama": "Hitam", "kode_hex": "#000000" }, { "id": 2, "nama": "Putih", "kode_hex": "#FFFFFF" } ],
  "jumlah_kendaraan_dinas": 3,
  "created_at": "...", "updated_at": "..."
}
```

### `POST /master-kendaraan` — Tambah master kendaraan

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `merek` | string | Ya | Maks. 100 karakter |
| `tipe_model` | string | Ya | Maks. 100 karakter |
| `tahun_produksi` | integer | Ya | 1950 s.d. tahun depan. Kombinasi merek + tipe + tahun harus unik |
| `kondisi` | string | Ya | `baik`, `rusak_ringan`, `rusak_berat` |
| `status` | string | Ya | `active`, `inactive`, `standby` |
| `warna` | array of integer | Ya | Minimal 1 ID warna (Warna Tersedia), dari `GET /warna-kendaraan` |

```json
{ "merek": "Toyota", "tipe_model": "Avanza 1.5 G", "tahun_produksi": 2022, "kondisi": "baik", "status": "active", "warna": [1, 2] }
```

### `GET /master-kendaraan/{id}` — Detail master kendaraan

### `PUT /master-kendaraan/{id}` / `PATCH /master-kendaraan/{id}` — Ubah master kendaraan

- `warna` berisi **daftar lengkap** Warna Tersedia yang baru (menggantikan daftar lama). Dengan `PATCH`, jika `warna` tidak dikirim, daftar warna tidak berubah.
- Warna yang masih dipakai kendaraan dinas bertipe ini tidak bisa dikeluarkan (422, pesan menyebut nomor polisinya).
- Perubahan Kondisi/Status langsung tercermin di semua kendaraan dinas bertipe ini.

### `DELETE /master-kendaraan/{id}` — Hapus master kendaraan

Ditolak (409) jika masih dipakai kendaraan dinas.

### `POST /master-kendaraan/hapus-massal` — Hapus banyak master kendaraan

---

## 8. Warna Kendaraan

Daftar warna standar. Seperti di panel admin, warna tidak punya menu sendiri: warna dipilih sebagai Warna Tersedia di Master Kendaraan. API-nya hanya untuk melihat dan menambah warna.

### `GET /warna-kendaraan` — Daftar warna

| Parameter | Keterangan |
|-----------|------------|
| `master_kendaraan_id` | Hanya Warna Tersedia untuk master tersebut (pilihan warna saat menambah Kendaraan Dinas). Tanpa paginasi |
| `search` | Cari nama warna |
| `sort` | `nama` (default), `created_at` |

### `POST /warna-kendaraan` — Tambah warna baru

Sama dengan tombol **+ Tambah Warna Baru** di form Master Data > Kendaraan.

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `nama` | string | Ya | Maks. 50 karakter. Dirapikan otomatis ("biru  dongker" → "Biru Dongker"), dan tidak boleh dobel ("HITAM" = "Hitam") |
| `kode_hex` | string | Tidak | Format `#RRGGBB`, mis. `#1F2A44` |

Setelah ditambah, masukkan ID warna ke field `warna` master kendaraan agar bisa dipilih di Kendaraan Dinas.

---

## 9. Master Data > Instansi Pemilik

Instansi pemilik kendaraan (Dinas Pendidikan, Dinas Kesehatan, dst.). Data yang sama tampil sebagai Perangkat Daerah.

### `GET /instansi` — Daftar instansi

| Parameter | Keterangan |
|-----------|------------|
| `search` | Cari di nama & singkatan |
| `sort` | `nama` (default), `singkatan`, `created_at` |

Contoh isi satu data:

```json
{ "id": 1, "nama": "Dinas Pendidikan", "singkatan": "Disdik", "alamat": null, "jumlah_pegawai": 4, "jumlah_kendaraan_dinas": 3, "created_at": "...", "updated_at": "..." }
```

### `POST /instansi` — Tambah instansi

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `nama` | string | Ya | Unik, maks. 255 karakter |
| `singkatan` | string | Tidak | Maks. 50 karakter |
| `alamat` | string | Tidak | |

### `GET /instansi/{id}` — Detail instansi

### `PUT /instansi/{id}` / `PATCH /instansi/{id}` — Ubah instansi

### `DELETE /instansi/{id}` — Hapus instansi

Ditolak (409) jika masih punya kendaraan dinas atau pegawai.

### `POST /instansi/hapus-massal` — Hapus banyak instansi

---

## 10. Perangkat Daerah (read-only)

Menu Perangkat Daerah hanya untuk melihat data. Tambah, ubah, dan hapus dilakukan lewat endpoint `/instansi`. `POST`, `PUT`, `PATCH`, dan `DELETE` di endpoint ini menghasilkan **405**.

### `GET /perangkat-daerah` — Daftar perangkat daerah

Beserta jumlah pegawai dan kendaraan dinas. Parameter: `search`, `sort` (`nama`, `singkatan`).

### `GET /perangkat-daerah/{id}` — Detail perangkat daerah

### `GET /perangkat-daerah/{id}/kendaraan-dinas` — Kendaraan dinas milik perangkat daerah

Format data sama dengan `GET /kendaraan-dinas`. Parameter `sort`: `nomor_asset` (default), `nomor_polisi`, `created_at`.

### `GET /perangkat-daerah/{id}/pegawai` — Pegawai perangkat daerah

Format data sama dengan `GET /pegawai`. Parameter `sort`: `nama` (default), `nip`, `golongan`.

---

## 11. Kode Status & Format Error

| Status | Arti | Contoh `message` |
|--------|------|------------------|
| 200 | Berhasil | |
| 201 | Data berhasil ditambahkan | `Pegawai berhasil ditambahkan.` |
| 401 | Belum login / token salah / sudah logout | `Belum login atau token tidak valid. Login di POST /api/v1/auth/login.` |
| 403 | Akun tidak punya akses | `Akun ini tidak memiliki akses ke JakAsset BPAD.` |
| 404 | Data atau endpoint tidak ada | `Data tidak ditemukan.` / `Endpoint tidak ditemukan.` |
| 405 | Metode HTTP tidak didukung | `Metode HTTP tidak didukung untuk endpoint ini.` |
| 409 | Data masih dipakai, tidak bisa dihapus | lihat [Hapus data & dependensi](#hapus-data--dependensi) |
| 422 | Validasi gagal | `NIP harus 18 digit angka.` |
| 429 | Terlalu banyak request (login 10/menit, endpoint lain 120/menit) | `Terlalu banyak request. Coba lagi beberapa saat lagi.` |

Format error validasi (422): pesan per field ada di `errors`.

```json
{
  "message": "NIP harus 18 digit angka. (and 1 more error)",
  "errors": {
    "nip": ["NIP harus 18 digit angka."],
    "golongan": ["Golongan tidak valid. Pilihan: I/a, I/b, ..., IV/e."]
  }
}
```

---

## 12. Ringkasan Semua Endpoint

Semua path diawali `/api/v1`. 🔓 = tanpa token, selain itu wajib `Authorization: Bearer {token}`.

| Metode | Endpoint | Fungsi |
|--------|----------|--------|
| POST 🔓 | `/auth/login` | Login, mendapatkan token |
| GET | `/auth/me` | Akun yang sedang login |
| POST | `/auth/logout` | Logout, mencabut token |
| GET | `/referensi` | Pilihan kondisi, status, golongan |
| GET | `/kendaraan-dinas` | Daftar kendaraan dinas (cari, filter, urut) |
| POST | `/kendaraan-dinas` | Tambah kendaraan dinas (nomor asset otomatis) |
| GET | `/kendaraan-dinas/{id}` | Detail kendaraan dinas |
| PUT/PATCH | `/kendaraan-dinas/{id}` | Ubah kendaraan dinas |
| DELETE | `/kendaraan-dinas/{id}` | Hapus kendaraan dinas |
| POST | `/kendaraan-dinas/hapus-massal` | Hapus banyak kendaraan dinas |
| GET | `/kendaraan-dinas/{id}/label` | Download label QR satu kendaraan (PDF) |
| POST | `/kendaraan-dinas/label` | Download label QR banyak kendaraan (PDF A4) |
| GET | `/pegawai` | Daftar pegawai |
| POST | `/pegawai` | Tambah pegawai |
| GET | `/pegawai/{id}` | Detail pegawai |
| PUT/PATCH | `/pegawai/{id}` | Ubah pegawai |
| DELETE | `/pegawai/{id}` | Hapus pegawai (ditolak bila masih pemegang kendaraan) |
| POST | `/pegawai/hapus-massal` | Hapus banyak pegawai |
| GET | `/master-kendaraan` | Daftar master kendaraan |
| POST | `/master-kendaraan` | Tambah master kendaraan + Warna Tersedia |
| GET | `/master-kendaraan/{id}` | Detail master kendaraan |
| PUT/PATCH | `/master-kendaraan/{id}` | Ubah master kendaraan |
| DELETE | `/master-kendaraan/{id}` | Hapus master kendaraan (ditolak bila dipakai) |
| POST | `/master-kendaraan/hapus-massal` | Hapus banyak master kendaraan |
| GET | `/warna-kendaraan` | Daftar warna / Warna Tersedia suatu master |
| POST | `/warna-kendaraan` | Tambah warna baru |
| GET | `/instansi` | Daftar instansi pemilik |
| POST | `/instansi` | Tambah instansi |
| GET | `/instansi/{id}` | Detail instansi |
| PUT/PATCH | `/instansi/{id}` | Ubah instansi |
| DELETE | `/instansi/{id}` | Hapus instansi (ditolak bila punya kendaraan/pegawai) |
| POST | `/instansi/hapus-massal` | Hapus banyak instansi |
| GET | `/perangkat-daerah` | Daftar perangkat daerah (read-only) |
| GET | `/perangkat-daerah/{id}` | Detail perangkat daerah |
| GET | `/perangkat-daerah/{id}/kendaraan-dinas` | Kendaraan dinas milik perangkat daerah |
| GET | `/perangkat-daerah/{id}/pegawai` | Pegawai perangkat daerah |

---

## Catatan Teknis

- Kode API: `routes/api.php`, `app/Http/Controllers/Api/`, `app/Http/Requests/Api/`, `app/Http/Resources/`.
- Aturan validasi memakai fungsi yang sama dengan form panel admin, jadi aturan bisnis di API dan panel admin selalu sama.
- Token disimpan di tabel `personal_access_tokens` (Laravel Sanctum) dan tidak kedaluwarsa sampai logout. Untuk mencabut semua token seorang user, hapus barisnya di tabel tersebut.
- Test: `tests/Feature/ApiTest.php`.
