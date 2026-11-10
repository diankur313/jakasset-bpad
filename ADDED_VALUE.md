# ADDED_VALUE.md — Catatan Fitur Added Value

File ini mencatat setiap prompt dari pemilik proyek yang **diawali dengan keterangan "Added Value"**, yaitu fitur tambahan di luar spesifikasi dasar aplikasi. Untuk setiap prompt dicatat: prompt aslinya, penjelasan maksudnya, spesifikasi yang diterapkan, dan file yang terlibat.

Aturan: setiap prompt baru yang diawali "Added Value" wajib ditambahkan ke file ini sebagai entri baru (AV-002, AV-003, dan seterusnya), selain dicatat di `AGENTS.md` dan `README.md`.

---

## Daftar Added Value

| Kode | Tanggal | Menu | Fitur |
|------|---------|------|-------|
| AV-001 | 2026-11-10 | Asset > Kendaraan Dinas | Nomor Asset otomatis |

---

## AV-001 — Nomor Asset Otomatis untuk Kendaraan Dinas

**Tanggal:** 2026-11-10
**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

### Prompt asli

> Added Value:
> Pada menu asset - kendaran dinas, tambahkan field nomor asset yang auto tergenerate untuk indentifikasi tiap kendaraan. kode ini bersifat unik dengan format
> YYYY-mm-dd/jenis asset (dalam hal ini kendaraan)/4 digit running number

### Penjelasan prompt

Setiap kendaraan dinas butuh **kode identitas aset** yang:
- **Dibuat otomatis** oleh sistem, bukan diketik pengguna, sehingga tidak ada salah ketik atau nomor ganda.
- **Unik**: tidak ada dua kendaraan dengan nomor asset yang sama.
- **Bisa dibaca**: dari kodenya langsung terlihat kapan aset dicatat, jenis asetnya apa, dan urutannya.

Format yang diminta terdiri dari tiga bagian yang dipisah garis miring:

| Bagian | Isi | Contoh |
|--------|-----|--------|
| `YYYY-mm-dd` | Tanggal aset dicatat ke sistem | `2026-11-10` |
| Jenis asset | Untuk menu ini: kendaraan | `KENDARAAN` |
| Running number | Nomor urut 4 digit | `0001` |

Contoh hasil: **`2026-11-10/KENDARAAN/0001`**

### Spesifikasi yang diterapkan

1. **Kapan dibuat:** saat data kendaraan dinas pertama kali disimpan. Di form tambah, field Nomor Asset tampil terkunci dengan keterangan "Dibuat otomatis saat disimpan".
2. **Tanggal:** tanggal pencatatan menurut waktu Jakarta (WIB). Timezone aplikasi diubah dari UTC ke `Asia/Jakarta` supaya data yang diinput antara pukul 00.00 dan 06.59 WIB tidak tercatat dengan tanggal hari sebelumnya.
3. **Jenis asset:** ditulis `KENDARAAN` (huruf besar, sesuai kebiasaan penulisan kode), disimpan sebagai konstanta `KendaraanDinas::JENIS_ASSET`. Generatornya dibuat umum, sehingga jenis aset lain nanti (misalnya `TANAH`, `GEDUNG`) cukup memakai jenis yang berbeda.
4. **Running number:**
   - 4 digit dengan nol di depan (`0001` s.d. `9999`).
   - **Dihitung per jenis asset per tanggal**, jadi mulai lagi dari `0001` setiap hari. Karena tanggal ikut menjadi bagian kode, nomornya tetap unik, dan batas 9999 berlaku per hari, bukan untuk seluruh aset.
   - **Tidak dipakai ulang**: jika kendaraan dengan nomor `0002` dihapus, kendaraan berikutnya tetap mendapat `0003`, supaya satu nomor asset selalu merujuk ke satu kendaraan saja.
5. **Unik dan aman dari input bersamaan:** penghitung disimpan di tabel `nomor_asset_sequences` dan dinaikkan dengan satu perintah `UPDATE ... + 1` yang atomik. Kolom `nomor_asset` juga diberi unique index sebagai pengaman terakhir.
6. **Tidak bisa diubah:** field terkunci di form edit, tidak termasuk `$fillable`, dan model mengembalikan nilai aslinya jika ada yang mencoba mengubahnya.
7. **Tampilan:** kolom pertama di tabel Kendaraan Dinas (bisa dicari, diurutkan, dan disalin), di halaman detail, di daftar kendaraan pada detail Perangkat Daerah, dan di pencarian global panel.
8. **Data lama:** migration mengisi nomor asset untuk kendaraan yang sudah ada berdasarkan tanggal `created_at`.

### File yang terlibat

| File | Perubahan |
|------|-----------|
| `app/Support/NomorAsset.php` | Generator nomor asset (baru) |
| `database/migrations/2026_11_10_100004_add_nomor_asset_to_kendaraan_dinas_table.php` | Kolom `nomor_asset` (unik), tabel `nomor_asset_sequences`, isi data lama (baru) |
| `app/Models/KendaraanDinas.php` | Konstanta `JENIS_ASSET`, hook `creating` (buat nomor) dan `updating` (kunci nomor) |
| `app/Filament/Resources/KendaraanDinas/Schemas/KendaraanDinasForm.php` | Field Nomor Asset (terkunci) |
| `app/Filament/Resources/KendaraanDinas/Schemas/KendaraanDinasInfolist.php` | Nomor Asset di halaman detail |
| `app/Filament/Resources/KendaraanDinas/Tables/KendaraanDinasTable.php` | Kolom Nomor Asset |
| `app/Filament/Resources/KendaraanDinas/KendaraanDinasResource.php` | Pencarian global berdasarkan nomor polisi & nomor asset |
| `config/app.php`, `.env`, `.env.example` | `APP_TIMEZONE=Asia/Jakarta` |
| `tests/Feature/AssetCrudTest.php` | 3 test baru: urutan & reset harian, tanggal WIB, nomor tidak bisa diubah |

### Verifikasi

- `php artisan test`: 16 test lolos (105 assertion).
- Browser: kolom Nomor Asset tampil di tabel (`2026-11-10/KENDARAAN/0001`) dan terkunci di form edit. Data dan counter yang dipakai untuk pengecekan sudah dihapus lagi.
