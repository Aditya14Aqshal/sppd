<?php
$halaman   = 'Persetujuan SPPD';
$sub_judul = 'SPPD / Persetujuan';
$aktif     = 'approval';
require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'pimpinan') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

$uid = $_SESSION['user_id'];
$stm = $conn->prepare("SELECT id_pegawai FROM tb_pengguna WHERE id_pengguna=?");
$stm->bind_param('i', $uid); $stm->execute();
$id_peg_pim = $stm->get_result()->fetch_assoc()['id_pegawai'] ?? 0;

// Proses approve/tolak
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sppd    = (int)$_POST['id_sppd'];
    $keputusan  = $_POST['keputusan'];
    $catatan    = trim($_POST['catatan_tolak'] ?? '');

    if ($keputusan === 'setuju') {
        $st = $conn->prepare("UPDATE tb_sppd SET status='disetujui', id_pimpinan=?, tgl_persetujuan=NOW() WHERE id_sppd=? AND status='diverifikasi'");
        $st->bind_param('ii', $id_peg_pim, $id_sppd);
        $st->execute();
        if ($st->affected_rows > 0) $msg = 'SPPD berhasil disetujui.';
    } elseif ($keputusan === 'tolak') {
        $st = $conn->prepare("UPDATE tb_sppd SET status='ditolak', catatan_tolak=?, id_pimpinan=? WHERE id_sppd=? AND status='diverifikasi'");
        $st->bind_param('sii', $catatan, $id_peg_pim, $id_sppd);
        $st->execute();
        if ($st->affected_rows > 0) $msg = 'SPPD ditolak.';
    }
}

// Ambil daftar SPPD yang menunggu persetujuan
$result = $conn->query("
    SELECT s.*, p.nama_lengkap, p.nip, p.unit_kerja,
           j.nama_jabatan, j.golongan,
           t.nama_instansi, t.kota,
           jk.nama_kegiatan,
           a.kode_anggaran, a.nama_program
    FROM tb_sppd s
    JOIN tb_pegawai p ON s.id_pengaju = p.id_pegawai
    JOIN tb_jabatan j ON p.id_jabatan = j.id_jabatan
    JOIN tb_tujuan t ON s.id_tujuan = t.id_tujuan
    JOIN tb_jenis_kegiatan jk ON s.id_kegiatan = jk.id_kegiatan
    JOIN tb_anggaran a ON s.id_anggaran = a.id_anggaran
    WHERE s.status = 'diverifikasi'
    ORDER BY s.tgl_pengajuan DESC
");

$status_badge = [
    'draf' => ['gray', 'Draf'], 'diajukan' => ['blue', 'Diajukan'],
    'diverifikasi' => ['amber', 'Diverifikasi'], 'disetujui' => ['green', 'Disetujui'],
    'ditolak' => ['red', 'Ditolak'], 'selesai' => ['green', 'Selesai'],
];
?>

<div class="page-head">
  <div>
    <h2>Persetujuan SPPD</h2>
    <p>Daftar SPPD yang menunggu keputusan pimpinan</p>
  </div>
</div>

<?php if ($msg): ?>
<div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Statistik -->
<div class="stats-grid" style="margin-bottom:16px">
  <div class="stat-card amber">
    <div class="stat-label">Menunggu Persetujuan</div>
    <div class="stat-value"><?= $result->num_rows ?></div>
    <div class="stat-sub">SPPD perlu keputusan</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">SPPD Menunggu Persetujuan (<?= $result->num_rows ?>)</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Pengaju</th>
          <th>NIP</th>
          <th>Jabatan</th>
          <th>Kegiatan</th>
          <th>Tujuan</th>
          <th>Berangkat</th>
          <th>Kembali</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; while ($row = $result->fetch_assoc()):
          [$bc, $bl] = $status_badge[$row['status']] ?? ['gray', '—'];
          $dur = (strtotime($row['tgl_kembali']) - strtotime($row['tgl_berangkat'])) / 86400 + 1;
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
          <td style="font-size:12px"><?= htmlspecialchars($row['nip']) ?></td>
          <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
          <td><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
          <td><?= htmlspecialchars($row['nama_instansi']) ?><br><small style="color:var(--gray-400)"><?= htmlspecialchars($row['kota']) ?></small></td>
          <td><?= date('d/m/Y', strtotime($row['tgl_berangkat'])) ?></td>
          <td><?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?> (<?= $dur ?> hr)</td>
          <td><span class="badge badge-<?= $bc ?>"><?= $bl ?></span></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <a href="/sppd/pages/sppd/detail.php?id=<?= $row['id_sppd'] ?>" class="btn btn-outline btn-sm">Detail</a>
              <form method="POST" style="display:inline" onsubmit="return confirm('Setujui SPPD ini?')">
                <input type="hidden" name="id_sppd" value="<?= $row['id_sppd'] ?>">
                <input type="hidden" name="keputusan" value="setuju">
                <button class="btn btn-success btn-sm">&#10003; Setujui</button>
              </form>
              <button class="btn btn-danger btn-sm" onclick="tolak(<?= $row['id_sppd'] ?>, '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')">&#10005; Tolak</button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="10" style="text-align:center;color:var(--gray-400);padding:32px">Tidak ada SPPD yang menunggu persetujuan</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tolak -->
<div id="modalTolak" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center">
  <div class="modal-box" style="background:var(--white);border-radius:var(--radius);padding:24px;width:480px;max-width:90vw;box-shadow:var(--shadow-md)">
    <h3 style="margin-bottom:4px;font-size:16px">Tolak SPPD</h3>
    <p style="font-size:13px;color:var(--gray-400);margin-bottom:16px">Isi alasan penolakan untuk SPPD atas nama <strong id="tolakNama"></strong></p>
    <form method="POST">
      <input type="hidden" name="id_sppd" id="tolakId">
      <input type="hidden" name="keputusan" value="tolak">
      <div class="form-group" style="margin-bottom:16px">
        <label class="form-label">Alasan Penolakan <span class="req">*</span></label>
        <textarea class="form-control" name="catatan_tolak" rows="4" required placeholder="Tuliskan alasan penolakan..."></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTolak').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-danger">&#10005; Tolak SPPD</button>
      </div>
    </form>
  </div>
</div>

<script>
function tolak(id, nama) {
  document.getElementById('tolakId').value = id;
  document.getElementById('tolakNama').textContent = nama;
  document.getElementById('modalTolak').style.display = 'flex';
}
// Tutup modal klik di luar
document.getElementById('modalTolak').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>

<?php require_once '../../includes/footer.php'; ?>
