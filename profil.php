<?php
require __DIR__ . '/includes/bootstrap.php';
$u = require_login();

if (is_post()) {
    csrf_verify();
    $lama = $_POST['lama'] ?? '';
    $baru = $_POST['baru'] ?? '';
    $ulang = $_POST['ulang'] ?? '';
    $row = q_one('SELECT password FROM users WHERE id = ?', [$u['id']]);

    if (!$row || !password_verify($lama, $row['password'])) {
        flash_set('danger', 'Kata sandi lama salah.');
    } elseif (strlen($baru) < 8) {
        flash_set('danger', 'Kata sandi baru minimal 8 karakter.');
    } elseif ($baru !== $ulang) {
        flash_set('danger', 'Konfirmasi kata sandi tidak sama.');
    } else {
        q_exec('UPDATE users SET password = ? WHERE id = ?', [password_hash($baru, PASSWORD_DEFAULT), $u['id']]);
        audit('GANTI_SANDI', 'Pengguna mengganti kata sandi sendiri');
        flash_set('success', 'Kata sandi berhasil diganti.');
        redirect('dashboard.php');
    }
    redirect('profil.php');
}

$judul = 'Ganti Kata Sandi';
include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title"><i class="bi bi-key"></i> Ganti Kata Sandi</h1>
<div class="card" style="max-width:480px">
  <div class="card-body">
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Kata sandi lama</label>
        <input type="password" name="lama" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Kata sandi baru (min. 8 karakter)</label>
        <input type="password" name="baru" class="form-control" minlength="8" required></div>
      <div class="mb-3"><label class="form-label">Ulangi kata sandi baru</label>
        <input type="password" name="ulang" class="form-control" minlength="8" required></div>
      <button class="btn btn-primary" type="submit">Simpan</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
