<?php
/**
 * Konfigurasi aplikasi SIMRME Belajar.
 * Sesuaikan DB_USER / DB_PASS dengan server MySQL Anda.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'rme_belajar');
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP/Laragon default: kosong

define('APP_NAME', 'SIM RME');
define('FASKES_NAME', 'STIKES RS BAPTIS');
define('SHOW_DEMO_HINT', true);   // tampilkan daftar akun contoh di halaman login (matikan setelah dikelas)

/**
 * Aturan pendaftaran terhadap jadwal praktik dokter:
 *  'ketat' = harus ada jadwal hari ini DAN pendaftaran belum melewati jam selesai praktik
 *  'hari'  = cukup ada jadwal hari ini, jam diabaikan (cocok untuk praktik di kelas sore/malam)
 * Kuota harian dokter selalu diperiksa pada kedua mode.
 */
define('JADWAL_MODE', 'hari');

/**
 * Impor ICD-10 dari WHO ICD-API (menu Master ICD-10 > Impor dari WHO).
 * Daftar gratis di https://icd.who.int/icdapi lalu salin Client ID dan Client Secret.
 * Biarkan kosong jika tidak dipakai. JANGAN membagikan Client Secret.
 */
define('WHO_ICD_CLIENT_ID', 'aaeefe24-abce-4f98-a6a1-3d8d58acf1d8_1f062aa3-4992-4142-bc6d-29744926f7b9');
define('WHO_ICD_CLIENT_SECRET', '11A0w6o751vr2Ym6amh1jPKh2G9nHwRMe30wDUVoLNc=');
define('WHO_ICD_RELEASE', '2019');   // rilis ICD-10 WHO
define('WHO_ICD_LANG', 'en');        // bahasa nama diagnosa dari WHO
define('WHO_ICD_BASE', 'https://id.who.int');
define('WHO_ICD_TOKEN_URL', 'https://icdaccessmanagement.who.int/connect/token');

date_default_timezone_set('Asia/Jakarta');

// BASE_URL dideteksi otomatis (aplikasi boleh ditaruh di subfolder htdocs, mis. /rme-belajar)
$__doc = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$__app = realpath(__DIR__);
$__base = '';
if ($__doc && $__app) {
    $__doc = str_replace('\\', '/', $__doc);
    $__app = str_replace('\\', '/', $__app);
    if (strpos($__app, $__doc) === 0) {
        $__base = rtrim(substr($__app, strlen($__doc)), '/');
    }
}
define('BASE_URL', $__base);
unset($__doc, $__app, $__base);
