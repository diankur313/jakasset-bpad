# ADDED_VALUE.md — Catatan Fitur Added Value

File ini mencatat setiap prompt dari pemilik proyek yang **diawali dengan keterangan "Added Value"**, yaitu fitur tambahan di luar spesifikasi dasar aplikasi. Untuk setiap prompt dicatat: prompt aslinya, penjelasan maksudnya, spesifikasi yang diterapkan, dan file yang terlibat.

Aturan: setiap prompt baru yang diawali "Added Value" wajib ditambahkan ke file ini sebagai entri baru (AV-002, AV-003, dan seterusnya), selain dicatat di `AGENTS.md` dan `README.md`.

---

## Daftar Added Value

| Kode | Tanggal | Menu | Fitur |
|------|---------|------|-------|
| AV-001 | 2026-11-10 | Asset > Kendaraan Dinas | Nomor Asset otomatis |
| AV-002 | 2026-11-10 | Master Data > Kendaraan, Asset > Kendaraan Dinas | Field Status (Active, Inactive, Standby) |
| AV-003 | 2026-11-10 | Asset > Kendaraan Dinas | Download label QR code (satuan & batch) |

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

1. **Kapan dibuat:** saat data kendaraan dinas pertama kali disimpan, sepenuhnya di backend. Field Nomor Asset **tidak ditampilkan di form** tambah maupun ubah (lihat Revisi 1).
2. **Tanggal:** tanggal pencatatan menurut waktu Jakarta (WIB). Timezone aplikasi diubah dari UTC ke `Asia/Jakarta` supaya data yang diinput antara pukul 00.00 dan 06.59 WIB tidak tercatat dengan tanggal hari sebelumnya.
3. **Jenis asset:** ditulis `KENDARAAN` (huruf besar, sesuai kebiasaan penulisan kode), disimpan sebagai konstanta `KendaraanDinas::JENIS_ASSET`. Generatornya dibuat umum, sehingga jenis aset lain nanti (misalnya `TANAH`, `GEDUNG`) cukup memakai jenis yang berbeda.
4. **Running number:**
   - 4 digit dengan nol di depan (`0001` s.d. `9999`).
   - **Dihitung per jenis asset per tanggal**, jadi mulai lagi dari `0001` setiap hari. Karena tanggal ikut menjadi bagian kode, nomornya tetap unik, dan batas 9999 berlaku per hari, bukan untuk seluruh aset.
   - **Tidak dipakai ulang**: jika kendaraan dengan nomor `0002` dihapus, kendaraan berikutnya tetap mendapat `0003`, supaya satu nomor asset selalu merujuk ke satu kendaraan saja.
5. **Unik dan aman dari input bersamaan:** penghitung disimpan di tabel `nomor_asset_sequences` dan dinaikkan dengan satu perintah `UPDATE ... + 1` yang atomik. Kolom `nomor_asset` juga diberi unique index sebagai pengaman terakhir.
6. **Tidak bisa diubah:** tidak ada di form, tidak termasuk `$fillable`, dan model mengembalikan nilai aslinya jika ada yang mencoba mengubahnya.
7. **Tampilan:** kolom pertama di tabel Kendaraan Dinas (bisa dicari, diurutkan, dan disalin), di halaman detail, di daftar kendaraan pada detail Perangkat Daerah, dan di pencarian global panel.
8. **Data lama:** migration mengisi nomor asset untuk kendaraan yang sudah ada berdasarkan tanggal `created_at`.

### File yang terlibat

| File | Perubahan |
|------|-----------|
| `app/Support/NomorAsset.php` | Generator nomor asset (baru) |
| `database/migrations/2026_11_10_100004_add_nomor_asset_to_kendaraan_dinas_table.php` | Kolom `nomor_asset` (unik), tabel `nomor_asset_sequences`, isi data lama (baru) |
| `app/Models/KendaraanDinas.php` | Konstanta `JENIS_ASSET`, hook `creating` (buat nomor) dan `updating` (kunci nomor) |
| `app/Filament/Resources/KendaraanDinas/Schemas/KendaraanDinasForm.php` | Awalnya field Nomor Asset terkunci; dihapus di Revisi 1 |
| `app/Filament/Resources/KendaraanDinas/Schemas/KendaraanDinasInfolist.php` | Nomor Asset di halaman detail |
| `app/Filament/Resources/KendaraanDinas/Tables/KendaraanDinasTable.php` | Kolom Nomor Asset |
| `app/Filament/Resources/KendaraanDinas/KendaraanDinasResource.php` | Pencarian global berdasarkan nomor polisi & nomor asset |
| `config/app.php`, `.env`, `.env.example` | `APP_TIMEZONE=Asia/Jakarta` |
| `tests/Feature/AssetCrudTest.php` | 3 test baru: urutan & reset harian, tanggal WIB, nomor tidak bisa diubah & tidak ada di form |

### Verifikasi

- `php artisan test`: 16 test lolos (105 assertion).
- Browser: kolom Nomor Asset tampil di tabel (`2026-11-10/KENDARAAN/0001`). Data dan counter yang dipakai untuk pengecekan sudah dihapus lagi.

### Revisi 1 — Nomor Asset tidak ditampilkan di form (2026-11-10)

**Prompt:**
> Field tidak perlu ditampilkan di form / user nggak perlu tahu, jadikan dia berjalan di backend aja.

**Penjelasan:** Sebelumnya form tambah dan ubah Kendaraan Dinas menampilkan field Nomor Asset yang terkunci dengan placeholder "Dibuat otomatis saat disimpan". Field itu tidak berguna bagi pengguna saat mengisi form, jadi dihapus. Pembuatan nomor tetap berjalan otomatis di backend lewat hook `KendaraanDinas::creating`.

**Perubahan:**
- Field `nomor_asset` dihapus dari `KendaraanDinasForm.php` (form tambah & ubah).
- Nomor Asset **tetap ditampilkan** di tabel Kendaraan Dinas, halaman detail, daftar kendaraan di detail Perangkat Daerah, dan pencarian global, karena fungsinya sebagai identitas kendaraan.
- Test `test_nomor_asset_tidak_bisa_diubah` sekarang memastikan field tidak ada di form tambah maupun ubah.

**Verifikasi:** `php artisan test`: 16 test lolos. Di browser, halaman tambah Kendaraan Dinas hanya menampilkan "Nomor Polisi", tanpa Nomor Asset.

---

## AV-002 — Field Status Kendaraan (Active, Inactive, Standby)

**Tanggal:** 2026-11-10
**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

### Prompt asli

> Pada form Status yang berisi Active, Inactive, Standby catat itu sebagai added value.

### Penjelasan prompt

Field **Status** dengan pilihan **Active / Inactive / Standby** ditetapkan sebagai fitur Added Value. Fitur ini sudah dibuat pada tahap menu CRUD (berasal dari instruksi Master Data > Kendaraan), lalu sekarang dicatat secara resmi di file ini. **Tidak ada perubahan kode pada entri ini.**

Status menunjukkan ketersediaan operasional kendaraan, terpisah dari **Kondisi** (Baik / Rusak Ringan / Rusak Berat) yang menunjukkan keadaan fisiknya.

| Status | Arti | Warna badge |
|--------|------|-------------|
| Active | Kendaraan sedang dipakai untuk operasional | Hijau (`success`) |
| Inactive | Kendaraan tidak dipakai / tidak dioperasikan | Abu-abu (`gray`) |
| Standby | Kendaraan siap pakai tetapi sedang tidak ditugaskan | Biru (`info`) |

### Spesifikasi yang diterapkan

1. **Enum** `App\Enums\StatusKendaraan` dengan nilai tersimpan `active`, `inactive`, `standby`. Label tampilan dan warna badge diatur di enum ini (`HasLabel`, `HasColor`).
2. **Master Data > Kendaraan:** field Status wajib diisi, berupa dropdown, dengan default **Active**.
3. **Asset > Kendaraan Dinas:** Status **tidak bisa dipilih** dan tidak disimpan di tabel `kendaraan_dinas`; nilainya selalu dibaca dari Master Kendaraan dan ditampilkan terkunci di form (lihat Revisi 1).
4. **Tabel:** Status tampil sebagai badge berwarna, bisa diurutkan, dan ada filter Status di tabel Master Kendaraan dan Kendaraan Dinas.
5. **Detail:** Status tampil sebagai badge di halaman detail kedua menu.
6. **Database:** kolom `status` (string 20) hanya di tabel `master_kendaraan`, di-cast ke enum di model. (Kolom `kendaraan_dinas.status` dihapus di Revisi 1.)

### File yang terlibat

| File | Peran |
|------|-------|
| `app/Enums/StatusKendaraan.php` | Definisi pilihan, label, dan warna |
| `app/Models/MasterKendaraan.php`, `app/Models/KendaraanDinas.php` | Cast kolom `status` ke enum |
| `app/Filament/Resources/MasterKendaraan/Schemas/MasterKendaraanForm.php` | Dropdown Status (default Active) |
| `app/Filament/Resources/KendaraanDinas/Schemas/KendaraanDinasForm.php` | Dropdown Status, terisi otomatis dari master |
| `app/Filament/Resources/MasterKendaraan/Tables/MasterKendaraanTable.php`, `app/Filament/Resources/KendaraanDinas/Tables/KendaraanDinasTable.php` | Badge & filter Status |
| `database/migrations/2026_11_10_100001_create_master_kendaraan_table.php`, `database/migrations/2026_11_10_100003_create_kendaraan_dinas_table.php` | Kolom `status` |
| `database/seeders/MasterKendaraanSeeder.php` | Data dummy dengan status bervariasi |
| `tests/Feature/AssetCrudTest.php` | Status terisi otomatis dari master (`test_tambah_kendaraan_dinas_dengan_pilihan_berjenjang`) |

### Verifikasi

Fitur sudah diuji sejak tahap menu CRUD: `php artisan test` lolos 16 test, termasuk pengecekan Status yang otomatis terisi dari master. Tampilan badge Status sudah dicek di browser pada tabel Master Kendaraan.

### Revisi 1 — Status Kendaraan Dinas mengikuti master (2026-11-10)

**Prompt:**
> Kondisi dan status pada field form http://localhost:8000/admin/asset/kendaraan-dinas/create tidak boleh dipilih manual, karena nilainya sudah di-state di master kendaraan, untuk mencegah redundant data.

**Penjelasan:** Sebelumnya Status (dan Kondisi) di Kendaraan Dinas terisi otomatis dari master tetapi masih bisa diubah, dan disimpan ulang di tabel `kendaraan_dinas`. Akibatnya nilai yang sama tersimpan dua kali dan bisa tidak sinkron. Sekarang Kendaraan Dinas tidak menyimpan Status/Kondisi sendiri; nilainya selalu diambil dari Master Kendaraan.

**Perubahan:**
- Migration `2026_11_10_100008`: kolom `kondisi` dan `status` dihapus dari `kendaraan_dinas` (sebelum dihapus, ke-3 data yang ada dicek dan nilainya sama dengan master, jadi tidak ada data yang hilang).
- Form Kendaraan Dinas: Kondisi & Status tampil terkunci dengan keterangan "Mengikuti Master Data > Kendaraan", terisi saat Tahun Produksi dipilih, dan tidak ikut disimpan.
- Tabel, detail, dan filter Kondisi/Status Kendaraan Dinas membaca dari relasi `masterKendaraan`.
- Akibatnya: mengubah Kondisi/Status di Master Kendaraan langsung berlaku untuk semua kendaraan dinas bertipe itu.

**Verifikasi:** `php artisan test`: 26 test lolos, termasuk test baru `test_kondisi_status_kendaraan_dinas_tidak_bisa_dipilih_dan_mengikuti_master`. Dicek di browser pada form ubah kendaraan dinas.

---

## AV-003 — Download Label QR Code Kendaraan Dinas

**Tanggal:** 2026-11-10
**Dikerjakan oleh:** Claude Code (model Claude Opus 5.5, `claude-opus-5-5`)

### Prompt asli

> Di menu http://localhost:8000/admin/asset/kendaraan-dinas tambahin action download label per data, kemudian sistem mengeksport label yang berisikan QR code yang berasal dari ID asset yang di-encode ke bentuk QR. Untuk style-nya di tengah QR ada logo BPAD (terlampir), kemudian di samping QR ada keterangan merek dan jenis mobil, kemudian tahun pembelian. Berikan fungsi juga untuk batch download berbentuk ceklis all.
> Tulis fitur ini sebagai added value.

### Penjelasan prompt

Setiap kendaraan dinas perlu **label fisik** yang bisa dicetak dan ditempel di kendaraan. Label memuat **QR code** berisi ID asset, sehingga petugas cukup memindai QR untuk mengenali kendaraan. Label bisa diunduh **per kendaraan**, atau **sekaligus banyak** dengan mencentang data (termasuk centang semua).

Keputusan yang dikonfirmasi ke pemilik proyek saat pengerjaan:
- **Tahun di label:** data belum punya "tahun pembelian", jadi label memakai **Tahun Produksi** dari Master Kendaraan dan diberi judul "Tahun Produksi". Tidak ada field baru.
- **Format file:** **PDF siap cetak**.

### Spesifikasi yang diterapkan

1. **Isi QR:** Nomor Asset (ID asset, contoh `2026-11-10/KENDARAAN/0001`).
2. **Logo BPAD di tengah QR:** file `resources/images/logo-bpad.png` (logo terlampir). QR memakai error correction level **High** (bisa memulihkan ±30% data) dan modul QR di belakang logo dikosongkan, jadi QR tetap terbaca. Sudah diuji: QR berlogo berhasil dibaca kembali menjadi nomor asset yang benar.
3. **Teks di samping QR:** judul "Kendaraan Dinas · BPAD", **Merek** (besar), **Tipe / Model** (jenis mobil), **Tahun Produksi**, ditambah Nomor Polisi dan Nomor Asset dalam huruf kecil agar label tetap bisa dibaca tanpa memindai.
4. **Download satuan:** tombol **Label** (ikon QR) di setiap baris tabel Kendaraan Dinas, dan tombol **Download Label** di halaman detail. Hasil: PDF berukuran 1 label **90 × 45 mm**, nama file `label-2026-11-10-kendaraan-0001.pdf`.
5. **Download batch:** centang beberapa baris atau centang semua (checkbox di header tabel), lalu klik tombol **Download Label** yang muncul di atas tabel. Hasil: PDF **A4** berisi **10 label per halaman** (2 kolom × 5 baris) dengan garis potong putus-putus, nama file `label-kendaraan-dinas-YYYYMMDD-HHMMSS.pdf`.
6. **Ukuran file kecil:** hanya huruf yang dipakai yang ditanam ke PDF (±35 KB untuk 1 label, ±105 KB untuk 6 label).

### File yang terlibat

| File | Peran |
|------|-------|
| `app/Support/LabelAsset.php` | Membuat QR berlogo dan PDF label (satuan / lembar A4) |
| `resources/views/pdf/label-kendaraan.blade.php` | Layout PDF (ukuran label, grid A4, garis potong) |
| `resources/views/pdf/partials/label-kendaraan-isi.blade.php` | Isi satu label (QR + teks) |
| `resources/images/logo-bpad.png` | Logo BPAD di tengah QR |
| `app/Filament/Resources/KendaraanDinas/Actions/DownloadLabelAction.php` | Aksi download satuan & batch |
| `app/Filament/Resources/KendaraanDinas/Tables/KendaraanDinasTable.php` | Tombol Label per baris & Download Label untuk data tercentang |
| `app/Filament/Resources/KendaraanDinas/Pages/ViewKendaraanDinas.php` | Tombol Download Label di halaman detail |
| `composer.json` | Package baru: `endroid/qr-code`, `barryvdh/laravel-dompdf`; dev: `khanamiryan/qrcode-detector-decoder` |
| `tests/Feature/AssetCrudTest.php` | 3 test baru |

### Verifikasi

- `php artisan test`: 29 test lolos, termasuk 3 test baru: QR berlogo terbaca kembali sebagai nomor asset, download satuan menghasilkan PDF dengan nama file yang benar, dan download batch 11 kendaraan menghasilkan PDF 2 halaman.
- PDF dicek secara visual (dirender ke gambar): layout QR + logo + teks sesuai, 90 × 45 mm, lembar A4 rapi.
- Browser: centang semua di tabel Kendaraan Dinas memunculkan tombol Download Label, dan klik tombol mengirim PDF tanpa error.
- Saat test ditemukan dan diperbaiki 1 bug: download satuan error karena data dikirim sebagai koleksi biasa, bukan koleksi Eloquent.
