<?php
$halaman   = 'Dashboard';
$sub_judul = 'Beranda';
$aktif     = 'dashboard';
require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];

// Ambil id_pegawai dari session user
$stm = $conn->prepare("SELECT id_pegawai FROM tb_pengguna WHERE id_pengguna = ?");
$stm->bind_param('i', $uid); $stm->execute();
$id_peg = $stm->get_result()->fetch_assoc()['id_pegawai'] ?? 0;

// Statistik umum
$total_sppd   = $conn->query("SELECT COUNT(*) c FROM tb_sppd")->fetch_assoc()['c'];
$menunggu     = $conn->query("SELECT COUNT(*) c FROM tb_sppd WHERE status IN ('diajukan','diverifikasi')")->fetch_assoc()['c'];
$disetujui    = $conn->query("SELECT COUNT(*) c FROM tb_sppd WHERE status='disetujui'")->fetch_assoc()['c'];
$selesai      = $conn->query("SELECT COUNT(*) c FROM tb_sppd WHERE status='selesai'")->fetch_assoc()['c'];

// SPPD terbaru
$q_recent = "SELECT s.id_sppd, s.no_sppd, p.nama_lengkap, t.nama_instansi,
                     s.tgl_berangkat, s.tgl_kembali, s.status, s.tgl_pengajuan
             FROM tb_sppd s
             JOIN tb_pegawai p ON s.id_pengaju = p.id_pegawai
             JOIN tb_tujuan  t ON s.id_tujuan  = t.id_tujuan";
if ($role === 'pegawai') $q_recent .= " WHERE s.id_pengaju = $id_peg";
$q_recent .= " ORDER BY s.tgl_pengajuan DESC LIMIT 8";
$recent = $conn->query($q_recent);

$status_badge = [
  'draf'        => ['gray',  'Draf'],
  'diajukan'    => ['blue',  'Diajukan'],
  'diverifikasi'=> ['amber', 'Diverifikasi'],
  'disetujui'   => ['green', 'Disetujui'],
  'ditolak'     => ['red',   'Ditolak'],
  'selesai'     => ['green', 'Selesai'],
];
?>

<div class="page-head">
  <div>
    <h2>Dashboard</h2>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>. Berikut ringkasan sistem hari ini.</p>
  </div>
  <?php if ($role !== 'pimpinan'): ?>
  <a href="/sppd/pages/sppd/ajukan.php" class="btn btn-primary">+ Ajukan SPPD</a>
  <?php endif; ?>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label">Total SPPD</div>
    <div class="stat-value"><?= $total_sppd ?></div>
    <div class="stat-sub">Sepanjang tahun ini</div>
  </div>
  <div class="stat-card amber">
    <div class="stat-label">Menunggu</div>
    <div class="stat-value"><?= $menunggu ?></div>
    <div class="stat-sub">Perlu tindakan</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Disetujui</div>
    <div class="stat-value"><?= $disetujui ?></div>
    <div class="stat-sub">Siap dilaksanakan</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Selesai</div>
    <div class="stat-value"><?= $selesai ?></div>
    <div class="stat-sub">Laporan masuk</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">SPPD Terbaru</div>
    <a href="/sppd/pages/sppd/daftar.php" class="btn btn-secondary btn-sm">Lihat semua</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>No. SPPD</th>
          <th>Nama Pegawai</th>
          <th>Tujuan</th>
          <th>Tgl Berangkat</th>
          <th>Tgl Kembali</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $recent->fetch_assoc()):
          [$bc, $bl] = $status_badge[$row['status']] ?? ['gray','—'];
        ?>
        <tr>
          <td><?= $row['no_sppd'] ? htmlspecialchars($row['no_sppd']) : '<span style="color:var(--gray-400)">Belum diterbitkan</span>' ?></td>
          <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
          <td><?= htmlspecialchars($row['nama_instansi']) ?></td>
          <td><?= date('d/m/Y', strtotime($row['tgl_berangkat'])) ?></td>
          <td><?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?></td>
          <td><span class="badge badge-<?= $bc ?>"><?= $bl ?></span></td>
          <td>
            <a href="/sppd/pages/sppd/detail.php?id=<?= $row['id_sppd'] ?>" class="btn btn-outline btn-sm">Detail</a>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($recent->num_rows === 0): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray-400);padding:28px">Belum ada data SPPD</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
