<?php
require __DIR__ . '/includes/bootstrap.php';
$u = require_login();

$today = date('Y-m-d');

$totalPasien = (int)q_val('SELECT COUNT(*) FROM pasien');
$stat = ['menunggu' => 0, 'siap_periksa' => 0, 'farmasi' => 0, 'selesai' => 0, 'batal' => 0];
foreach (q_all('SELECT status, COUNT(*) n FROM kunjungan WHERE tgl_kunjungan = ? GROUP BY status', [$today]) as $r) {
    $stat[$r['status']] = (int)$r['n'];
}
$totalHariIni = array_sum($stat);

// Grafik kunjungan 7 hari terakhir
$labels = [];
$data = [];
$map = [];
foreach (q_all('SELECT tgl_kunjungan d, COUNT(*) n FROM kunjungan
                WHERE tgl_kunjungan >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status <> "batal"
                GROUP BY tgl_kunjungan') as $r) {
    $map[$r['d']] = (int)$r['n'];
}
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d/m', strtotime($d));
    $data[] = $map[$d] ?? 0;
}

// Diagnosa terbanyak (30 hari) hanya untuk peran klinis
$topDx = [];
if (can_view_clinical()) {
    $topDx = q_all('SELECT i.kode, i.nama, COUNT(*) n
                    FROM pemeriksaan_diagnosa d
                    JOIN icd10 i ON i.id = d.icd_id
                    JOIN pemeriksaan p ON p.id = d.pemeriksaan_id
                    JOIN kunjungan k ON k.id = p.kunjungan_id
                    WHERE k.tgl_kunjungan >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY i.id ORDER BY n DESC LIMIT 5');
}

$antrian = q_all('SELECT k.id, k.no_antrian, k.status, p.nama, p.no_rm, po.nama poli
                  FROM kunjungan k
                  JOIN pasien p ON p.id = k.pasien_id
                  JOIN poli po ON po.id = k.poli_id
                  WHERE k.tgl_kunjungan = ? AND k.status IN ("menunggu","siap_periksa","farmasi")
                  ORDER BY k.id LIMIT 10', [$today]);

$judul = 'Dashboard';
$menu_aktif = 'dashboard';
include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Selamat datang, <?= e($u['nama']) ?></h1>
<p class="text-muted mb-4"><?= e(ROLE_LABEL[$u['role']]) ?> &middot; <?= e(tgl_id($today)) ?></p>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl"><div class="card stat-card"><div class="card-body">
    <div class="num"><?= $totalPasien ?></div><div class="lbl">Total pasien terdaftar</div></div></div></div>
  <div class="col-6 col-md-4 col-xl"><div class="card stat-card warn"><div class="card-body">
    <div class="num"><?= $stat['menunggu'] ?></div><div class="lbl">Menunggu triage</div></div></div></div>
  <div class="col-6 col-md-4 col-xl"><div class="card stat-card info"><div class="card-body">
    <div class="num"><?= $stat['siap_periksa'] ?></div><div class="lbl">Menunggu dokter</div></div></div></div>
  <div class="col-6 col-md-4 col-xl"><div class="card stat-card prim"><div class="card-body">
    <div class="num"><?= $stat['farmasi'] ?></div><div class="lbl">Menunggu obat</div></div></div></div>
  <div class="col-6 col-md-4 col-xl"><div class="card stat-card ok"><div class="card-body">
    <div class="num"><?= $stat['selesai'] ?></div><div class="lbl">Selesai hari ini</div></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header">Kunjungan 7 hari terakhir</div>
      <div class="card-body"><canvas id="grafik" height="130"></canvas></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header">Antrian aktif hari ini (<?= $totalHariIni ?> kunjungan)</div>
      <div class="list-group list-group-flush">
        <?php if (!$antrian): ?>
          <div class="list-group-item text-muted">Tidak ada antrian aktif.</div>
        <?php endif; ?>
        <?php foreach ($antrian as $a): ?>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <b><?= e($a['no_antrian']) ?></b> &middot; <?= e($a['nama']) ?>
              <div class="small text-muted"><?= e($a['no_rm']) ?> &middot; <?= e($a['poli']) ?></div>
            </div>
            <?= status_badge($a['status']) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($topDx): ?>
<div class="card mt-3">
  <div class="card-header">5 diagnosa terbanyak (30 hari terakhir)</div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>ICD-10</th><th>Diagnosa</th><th class="text-end">Jumlah</th></tr></thead>
      <tbody>
      <?php foreach ($topDx as $d): ?>
        <tr><td><code><?= e($d['kode']) ?></code></td><td><?= e($d['nama']) ?></td><td class="text-end"><?= (int)$d['n'] ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php
$extra_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById("grafik"), {
  type: "bar",
  data: { labels: ' . json_encode($labels) . ',
          datasets: [{ label: "Kunjungan", data: ' . json_encode($data) . ', backgroundColor: "#0f766e", borderRadius: 4 }] },
  options: { plugins: { legend: { display: false } },
             scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>';
include __DIR__ . '/includes/footer.php';
