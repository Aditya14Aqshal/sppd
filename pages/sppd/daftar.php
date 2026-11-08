<?php
$halaman   = 'Daftar SPPD';
$sub_judul = 'SPPD';
$aktif     = 'sppd_list';
require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];
$stm  = $conn->prepare("SELECT id_pegawai FROM tb_pengguna WHERE id_pengguna=?");
$stm->bind_param('i',$uid); $stm->execute();
$id_peg = $stm->get_result()->fetch_assoc()['id_pegawai'] ?? 0;

$filter_status = $_GET['status'] ?? '';
$filter_cari   = trim($_GET['cari'] ?? '');

$where = ['1=1'];
if ($role === 'pegawai') $where[] = "s.id_pengaju = " . (int)$id_peg;
if ($filter_status) $where[] = "s.status = '".$conn->real_escape_string($filter_status)."'";
if ($filter_cari)   $where[] = "(p.nama_lengkap LIKE '%".($conn->real_escape_string($filter_cari))."%' OR s.no_sppd LIKE '%".($conn->real_escape_string($filter_cari))."%' OR t.nama_instansi LIKE '%".($conn->real_escape_string($filter_cari))."%')";

$sql = "SELECT s.*, p.nama_lengkap, t.nama_instansi, j.nama_kegiatan
        FROM tb_sppd s
        JOIN tb_pegawai p ON s.id_pengaju = p.id_pegawai
        JOIN tb_tujuan t ON s.id_tujuan = t.id_tujuan
        JOIN tb_jenis_kegiatan j ON s.id_kegiatan = j.id_kegiatan
        WHERE ".implode(' AND ',$where)."
        ORDER BY s.tgl_pengajuan DESC";
$result = $conn->query($sql);

$status_badge = [
  'draf'         => ['gray',  'Draf'],
  'diajukan'     => ['blue',  'Diajukan'],
  'diverifikasi' => ['amber', 'Diverifikasi'],
  'disetujui'    => ['green', 'Disetujui'],
  'ditolak'      => ['red',   'Ditolak'],
  'selesai'      => ['green', 'Selesai'],
];
?>

<div class="page-head">
  <div>
    <h2>Daftar SPPD</h2>
    <p>Semua data surat perintah perjalanan dinas</p>
  </div>
  <?php if ($role !== 'pimpinan'): ?>
  <a href="/sppd/pages/sppd/ajukan.php" class="btn btn-primary">+ Ajukan SPPD</a>
  <?php endif; ?>
</div>

<!-- FILTER -->
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="flex:2;min-width:200px;margin:0">
        <label class="form-label">Cari</label>
        <input class="form-control" name="cari" placeholder="Nama, no. SPPD, instansi..." value="<?= htmlspecialchars($filter_cari) ?>">
      </div>
      <div class="form-group" style="flex:1;min-width:160px;margin:0">
        <label class="form-label">Status</label>
        <select class="form-control" name="status">
          <option value="">Semua Status</option>
          <?php foreach (['draf','diajukan','diverifikasi','disetujui','ditolak','selesai'] as $s): ?>
          <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="align-self:flex-end">&#128269; Filter</button>
      <a href="/sppd/pages/sppd/daftar.php" class="btn btn-secondary" style="align-self:flex-end">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">Data SPPD (<?= $result->num_rows ?> record)</div>
    <?php if ($role !== 'pegawai'): ?>
    <a href="/sppd/pages/laporan/rekap_sppd.php" class="btn btn-secondary btn-sm">&#128196; Cetak Rekap</a>
    <?php endif; ?>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>No. SPPD</th>
          <th>Nama Pegawai</th>
          <th>Kegiatan</th>
          <th>Tujuan</th>
          <th>Tgl Berangkat</th>
          <th>Tgl Kembali</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; while ($row = $result->fetch_assoc()):
          [$bc,$bl] = $status_badge[$row['status']] ?? ['gray','—'];
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $row['no_sppd'] ? htmlspecialchars($row['no_sppd']) : '<em style="color:var(--gray-400)">—</em>' ?></td>
          <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
          <td><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
          <td><?= htmlspecialchars($row['nama_instansi']) ?></td>
          <td><?= date('d/m/Y', strtotime($row['tgl_berangkat'])) ?></td>
          <td><?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?></td>
          <td><span class="badge badge-<?= $bc ?>"><?= $bl ?></span></td>
          <td style="display:flex;gap:6px;flex-wrap:wrap">
            <a href="/sppd/pages/sppd/detail.php?id=<?= $row['id_sppd'] ?>" class="btn btn-outline btn-sm">Detail</a>
            <?php if ($role !== 'pimpinan'): ?>
            <a href="/sppd/pages/sppd/kelola.php?id=<?= $row['id_sppd'] ?>" class="btn btn-outline btn-sm">Kelola</a>
            <?php endif; ?>
            <?php if ($row['status']==='disetujui' && ($role==='admin')): ?>
            <a href="/sppd/pages/laporan/cetak_sppd.php?id=<?= $row['id_sppd'] ?>" class="btn btn-secondary btn-sm" target="_blank">Cetak</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="9" style="text-align:center;color:var(--gray-400);padding:32px">Tidak ada data yang sesuai filter</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
