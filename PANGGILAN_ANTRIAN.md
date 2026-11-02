# Sistem Antrean Rumah Sakit (Loket Pendaftaran & Poli)

## Alur Pelayanan Antrean Standar Rumah Sakit

```
[Kiosk Tiket / Pintu Masuk]
   ├── A: BPJS           (A-001, A-002, ...)
   ├── B: Umum           (B-001, B-002, ...)
   └── C: Lansia / Anak  (C-001, C-002, ...)
          ↓
[Layar TV Pendaftaran: display.php]
   Petugas Loket Pendaftaran memanggil nomor antrean tiket (TANPA nama pasien)
   Suara: "Nomor antrian... A 001... silakan menuju Loket 1"
          ↓
[Meja Loket Pendaftaran: kunjungan/daftar.php]
   Petugas melayani pasien, mencatat identitas & MEMILIH POLI TUJUAN.
   Pasien memperoleh Nomor Antrean Poli sesuai kode huruf poli (mis. UM-001, GI-001, AN-001).
          ↓
[Ruang Tunggu Poli & Layar TV Poli: display_poli.php]
   PERAWAT memanggil nomor antrean poli + nama pasien ke Poli / Triage.
   Suara: "Nomor antrian... U M 001, atas nama Budi Santoso... silakan menuju Poli Umum"
          ↓
[Triage / Pengkajian Tanda Vital oleh Perawat]
   Status berubah menjadi "siap_periksa", pasien diperiksa Dokter.
```

## Pembagian Peran dan Tampilan Layar

| Tahap | Petugas Pemanggil | Lokasi / Halaman | Format Panggilan Suara & Layar | Layar TV |
|-------|-------------------|-------------------|---------------------------------|----------|
| **1. Ambil Tiket Loket** | Pasien / Mandiri | Menu **Kiosk Tiket** (`kunjungan/tiket.php`) | Tiket Kategori A (BPJS), B (Umum), C (Lansia/Anak) | - |
| **2. Panggil ke Loket** | **Petugas Pendaftaran** | Menu **Pendaftaran** (`kunjungan/index.php`) | **Hanya nomor antrean** (mis. `A-001`) menuju Loket tertentu, **tanpa nama pasien** | `kunjungan/display.php` |
| **3. Pendaftaran Poli** | **Petugas Pendaftaran** | Menu **Daftarkan Kunjungan** (`kunjungan/daftar.php`) | Memilih Poli tujuan & terbit nomor antrean poli (mis. `UM-001`, `GI-001`, `AN-001`) | - |
| **4. Panggil ke Poli** | **Perawat** | Menu **Triage (Perawat)** (`triage/index.php`) | **Nomor antrean poli + NAMA PASIEN** menuju Poli tujuan | `kunjungan/display_poli.php` |
| **5. Pemeriksaan Dokter** | Dokter | Menu **Pemeriksaan Dokter** (`pemeriksaan/index.php`) | Pemeriksaan klinis SOAP, diagnosa ICD-10, resep | - |

## Catatan Teknis
- Suara bel dan pemanggilan menggunakan **Web Audio API** (chime nada 3 nada) dan **Web Speech API** bahasa Indonesia.
- Layar TV monitor antrean bersifat publik (dapat dibuka di layar TV monitor ruang tunggu tanpa login).
- Pemanggilan tercatat di **Audit Log** (`ANTRIAN_PANGGIL_LOKET`, `ANTRIAN_PANGGIL_POLI`).
