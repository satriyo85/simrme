<?php
require __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('dashboard.php');
}

$error = '';
if (is_post()) {
    csrf_verify();

    // Pembatasan percobaan login sederhana (5x per 5 menit per sesi)
    $_SESSION['login_try'] = array_filter($_SESSION['login_try'] ?? [], fn($t) => $t > time() - 300);
    if (count($_SESSION['login_try']) >= 5) {
        $error = 'Terlalu banyak percobaan. Coba lagi beberapa menit lagi.';
    } else {
        $username = post('username');
        $password = $_POST['password'] ?? '';
        $user = q_one('SELECT * FROM users WHERE username = ? AND aktif = 1', [$username]);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);       // cegah session fixation
            $_SESSION['user'] = [
                'id' => (int)$user['id'], 'username' => $user['username'],
                'nama' => $user['nama'], 'role' => $user['role'],
            ];
            $_SESSION['login_try'] = [];
            audit('LOGIN', 'Berhasil masuk');
            redirect('dashboard.php');
        }
        $_SESSION['login_try'][] = time();
        $error = 'Nama pengguna atau kata sandi salah.';
        audit('LOGIN_GAGAL', 'Username: ' . $username);
    }
}
$judul = 'Masuk';
include __DIR__ . '/includes/header.php';
?>
<div class="login-wrap">
  <div class="login-card">
    <div class="card shadow-lg">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <img src="LOGO.png" alt="Logo STIKES" class="login-logo">
          <h4 class="mt-2 mb-0"><?= e(APP_NAME) ?></h4>
          <div class="text-muted small"><?= e(FASKES_NAME) ?></div>
        </div>
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Nama pengguna</label>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Kata sandi</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
        </form>
        <hr>
        <div class="text-center text-muted small">
          &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &middot; <?= e(FASKES_NAME) ?><br>
          <span class="opacity-85">Aplikasi pembelajaran, Untuk Seluruh Mahasiswa.</span>
        </div>
       </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
