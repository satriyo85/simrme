# SIMRME Belajar

Sistem Rekam Medis Elektronik (RME) sederhana untuk pembelajaran mahasiswa D4 Manajemen Informasi Kesehatan.
Alurnya meniru puskesmas/rumah sakit, tetapi dibuat ringkas agar kode mudah dibaca dan dimodifikasi.

- **Back end:** PHP 8.x (native, tanpa framework) + PDO + MySQL/MariaDB
- **Front end:** Bootstrap 5 + Bootstrap Icons, responsif (menu geser di HP, tabel dapat digulir)
- **Seluruh data pasien adalah data fiktif.** Jangan dipakai untuk data pasien sungguhan.

## Instalasi (XAMPP / Laragon / server LAMP)

1. Salin folder `rme-belajar` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka phpMyAdmin, lalu **Import** file `database.sql`. Database `rme_belajar` dibuat otomatis beserta data awal.
   Lalu **Import** juga `wilayah.sql` (data Kode Wilayah Kemendagri, sekitar 4 MB, dipakai untuk alamat terstruktur).
   Jika batas unggah phpMyAdmin kecil, impor lewat terminal: `mysql -u root -p rme_belajar < wilayah.sql`.
   *Sudah memakai versi lama dan tidak ingin impor ulang?* Jalankan `update_metadata_pasien.sql` lalu `wilayah.sql`
   (data pasien tetap aman; lihat bagian Metadata pasien).
3. Jika perlu, sesuaikan `DB_USER` dan `DB_PASS` di `config.php` (bawaan XAMPP/Laragon: `root` dan kosong).
4. Buka `http://localhost/rme-belajar/` di browser.

Alamat dasar (`BASE_URL`) dideteksi otomatis, jadi folder boleh diberi nama lain.

> **Perlu internet:** Bootstrap, ikon, dan Chart.js dimuat dari CDN. Untuk lab tanpa internet, unduh file
> tersebut ke folder `assets/` lalu ubah tautan di `includes/header.php`, `includes/footer.php`, dan `dashboard.php`.

## Akun contoh

| Username      | Peran               | Hak akses utama                                             |
|---------------|---------------------|-------------------------------------------------------------|
| `admin`       | Administrator       | Semua menu, master data, pengguna, audit log                |
| `pendaftaran` | Petugas pendaftaran | Data pasien (identitas), pendaftaran kunjungan              |
| `perawat`     | Perawat             | Triage (tanda vital), melihat rekam medis                   |
| `dokter`      | Dokter              | Pemeriksaan SOAP, diagnosa ICD-10, resep, rekam medis       |
| `apoteker`    | Apoteker            | Farmasi (serah obat), master obat dan stok                  |

Setelah kelas berjalan, ubah `SHOW_DEMO_HINT` di `config.php` menjadi `false` agar daftar akun tidak tampil di halaman login.

## Alur pelayanan

```
Pendaftaran  ->  Triage  ->  Dokter (SOAP + ICD-10 + resep)  ->  Farmasi  ->  Selesai
 (menunggu)    (siap_periksa)         (farmasi)                              (selesai)
```

1. **Pendaftaran** mendaftarkan pasien baru (No. RM otomatis `RM-000001`, dst.) dan membuat kunjungan ke poli
   dengan nomor antrian otomatis per poli per hari (misal `UM-001`).
2. **Perawat** mengisi tanda vital (TD, nadi, suhu, RR, SpO2, BB, TB). IMT dihitung otomatis dan nilai abnormal diberi warna merah.
3. **Dokter** mengisi SOAP, memilih diagnosa ICD-10 (satu utama, sisanya sekunder), dan menulis resep.
   Jika tidak ada resep, kunjungan langsung selesai.
4. **Apoteker** memeriksa resep, lalu menyerahkan obat. Stok berkurang otomatis dan kunjungan ditutup.
5. Setelah selesai, rekam medis **terkunci** (tidak dapat diubah lagi).

## Konsep MIK yang dapat dibahas di kelas

- **Hak akses berbasis peran:** petugas pendaftaran tidak dapat melihat isi rekam medis, hanya identitas pasien.
- **Audit log:** setiap login, pembukaan rekam medis, dan perubahan data tercatat (menu Audit Log).
- **Pencegahan duplikasi:** NIK unik, dan pasien tidak dapat didaftarkan dua kali di poli yang sama pada hari yang sama.
- **Kelengkapan data:** validasi rentang tanda vital, diagnosa wajib, aturan pakai wajib.
- **Kodifikasi:** ICD-10 dengan pencarian cepat. Kode yang sudah dipakai tidak boleh dihapus.
- **Keselamatan pasien:** peringatan alergi tampil di layar triage, dokter, dan farmasi.
- **Integritas data:** transaksi database untuk pengurangan stok dan penyimpanan SOAP+resep.
- **Retensi/penguncian rekam medis** setelah kunjungan selesai.

## Metadata pasien (siap integrasi SatuSehat)

Formulir pasien memuat field yang lazim diwajibkan pada integrasi rekam medis nasional:

| Field | Aturan |
|-------|--------|
| **Nama ibu kandung** | **Wajib.** Dipakai untuk validasi berlapis bersama NIK dan pencegahan data ganda |
| **Alamat jalan / RT / RW** | Wajib, satu kolom teks bebas |
| **Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan/Desa** | Wajib. Dropdown bertingkat dengan pencarian (ketik untuk menyaring), memakai Kode Wilayah Kemendagri. Hanya kode kelurahan yang dikirim, tiga tingkat di atasnya diturunkan otomatis oleh server sehingga selalu konsisten |
| **Pekerjaan** | Dropdown dari tabel `ref_pekerjaan` (15 kelompok makro), bukan teks bebas, sehingga mudah dikelompokkan di laporan |
| **NIK** | 16 digit, unik. Dicocokkan dengan tanggal lahir dan jenis kelamin (struktur NIK: 6 digit wilayah + DDMMYY, tanggal +40 untuk perempuan). Jika tidak cocok data tetap tersimpan, tetapi muncul peringatan |

**Pencegahan data ganda:** pendaftaran ditolak jika NIK sudah ada, atau jika kombinasi *nama + tanggal lahir + nama ibu kandung*
sama dengan pasien lain. Pasien lama yang belum punya nama ibu atau alamat terstruktur ditandai **Belum lengkap** di daftar pasien
dan baru dapat disimpan setelah dilengkapi.

Catatan data: `wilayah.sql` bersumber dari repositori [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) (lisensi MIT,
38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, 83.762 kelurahan/desa). Untuk keperluan resmi, cocokkan dengan rujukan terbaru
Kemendagri atau Kemenkes. Daftar pekerjaan di `ref_pekerjaan` adalah daftar makro yang umum dipakai, dapat Anda ganti/tambah
(mis. disesuaikan dengan value set yang dipakai instansi Anda).

## Impor ICD-10 dari WHO ICD-API (opsional)

Menu **Master ICD-10 > Impor dari WHO** mengambil kode dan nama diagnosa langsung dari WHO, lalu menyimpannya ke tabel `icd10` lokal
(pencarian dokter tetap memakai data lokal, jadi cepat dan tetap jalan saat internet putus).

1. Daftar gratis di <https://icd.who.int/icdapi>, lalu salin **Client ID** dan **Client Secret** ke `config.php`
   (`WHO_ICD_CLIENT_ID`, `WHO_ICD_CLIENT_SECRET`). Rahasiakan Client Secret.
2. Buka halaman impor, ketik kode atau kategori, misalnya `J06` (menampilkan J06.0, J06.8, J06.9) atau rentang `J00-J06`.
3. Centang kode yang diinginkan. Nama dari WHO berbahasa Inggris dan **dapat diedit/diterjemahkan** sebelum diimpor.

Batasan: API ICD-10 WHO hanya mendukung penelusuran **berdasarkan kode**, bukan pencarian nama penyakit (pencarian teks hanya ada di ICD-11).
Server perlu ekstensi PHP cURL dan akses internet keluar. Panggilan ke WHO dilakukan dari server, sehingga Client Secret tidak terlihat di browser.

## Struktur folder

```
rme-belajar/
├── database.sql          skema + data awal (akun, poli, 47 ICD-10, 31 obat, 15 pekerjaan, 8 pasien fiktif)
├── wilayah.sql           data Kode Wilayah Kemendagri (provinsi sampai kelurahan/desa)
├── update_metadata_pasien.sql   migrasi database lama ke metadata pasien baru
├── config.php            koneksi database & pengaturan aplikasi
├── index.php · login.php · logout.php · dashboard.php · profil.php
├── includes/             bootstrap, db (PDO), functions (CSRF, RBAC, audit), header, footer
├── pasien/               daftar, form, detail + riwayat rekam medis (dapat dicetak)
├── kunjungan/            pendaftaran kunjungan dan antrian
├── triage/               tanda vital
├── pemeriksaan/          SOAP, diagnosa ICD-10, resep
├── farmasi/              serah obat + pengurangan stok
├── master/               obat, ICD-10, poli
├── admin/                pengguna, audit log
├── api/                  endpoint JSON pencarian (pasien, ICD-10, obat)
└── assets/               css dan js
```

## Keamanan yang sudah diterapkan

Prepared statement (anti SQL injection), `password_hash` bcrypt, token CSRF di semua formulir POST, escape output
dengan `e()` (anti XSS), `session_regenerate_id` saat login, pembatasan percobaan login, dan pemeriksaan peran di server
pada setiap halaman dan endpoint API.

Untuk produksi sungguhan masih perlu: HTTPS, enkripsi data sensitif, kebijakan sandi yang lebih ketat, pencadangan,
serta kepatuhan pada regulasi RME dan perlindungan data pribadi. Aplikasi ini sengaja disederhanakan untuk pembelajaran.

## Ide tugas / pengembangan untuk mahasiswa

1. Tambah modul **laboratorium** (permintaan dan hasil lab) atau **rujukan**.
2. Tambah cetak **resume medis** dan **surat keterangan sakit**.
3. Laporan 10 besar penyakit per bulan (SQL `GROUP BY` pada diagnosa) dan ekspor ke Excel/CSV.
4. Tambah pemeriksaan gigi (odontogram) untuk Poli Gigi atau form KIA khusus.
5. Hubungkan dengan standar interoperabilitas **HL7 FHIR** (mis. endpoint JSON `Patient` dan `Encounter`).
6. Tambah **interaksi obat** dan pengecekan alergi otomatis saat dokter memilih obat.
7. Uji keamanan: temukan dan perbaiki celah yang sengaja dibuat (latihan *code review*).
