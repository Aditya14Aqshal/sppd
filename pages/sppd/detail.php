<?php
ob_start();
$halaman   = 'Detail SPPD';
$sub_judul = 'SPPD / Detail';
$aktif     = 'sppd_list';
require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

$id   = (int)($_GET['id'] ?? 0);
$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT s.*, p.nama_lengkap, p.nip, p.unit_kerja, p.jenis_pegawai,
           j2.nama_jabatan, j2.golongan, j2.pangkat,
           t.nama_instansi, t.kota, t.provinsi, t.zona,
           jk.nama_kegiatan,
           a.kode_anggaran, a.nama_program,
           pp.nama_lengkap AS nama_pimpinan
    FROM tb_sppd s
    JOIN tb_pegawai p   ON s.id_pengaju  = p.id_pegawai
    JOIN tb_jabatan j2  ON p.id_jabatan  = j2.id_jabatan
    JOIN tb_tujuan  t   ON s.id_tujuan   = t.id_tujuan
    JOIN tb_jenis_kegiatan jk ON s.id_kegiatan = jk.id_kegiatan
    JOIN tb_anggaran a  ON s.id_anggaran = a.id_anggaran
    LEFT JOIN tb_pegawai pp ON s.id_pimpinan = pp.id_pegawai
    WHERE s.id_sppd = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$sppd = $stmt->get_result()->fetch_assoc();
if (!$sppd) { echo '<div class="alert alert-danger">Data tidak ditemukan.</div>'; require_once '../../includes/footer.php'; exit; }

// Anggota rombongan
$anggota = $conn->query("SELECT pg.nama_lengkap, pg.nip, jb.nama_jabatan, sa.peran FROM tb_sppd_anggota sa JOIN tb_pegawai pg ON sa.id_pegawai=pg.id_pegawai JOIN tb_jabatan jb ON pg.id_jabatan=jb.id_jabatan WHERE sa.id_sppd=$id");

// Proses approval pimpinan
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && $role==='pimpinan') {
    $stm2 = $conn->prepare("SELECT id_pegawai FROM tb_pengguna WHERE id_pengguna=?");
    $stm2->bind_param('i',$uid); $stm2->execute();
    $id_peg_pim = $stm2->get_result()->fetch_assoc()['id_pegawai'];
    $keputusan = $_POST['keputusan'];
    $catatan   = trim($_POST['catatan_tolak'] ?? '');
    if ($keputusan === 'setuju') {
        $conn->query("UPDATE tb_sppd SET status='disetujui', id_pimpinan=$id_peg_pim, tgl_persetujuan=NOW() WHERE id_sppd=$id");
        $msg = 'SPPD telah disetujui.';
    } else {
        $conn->query("UPDATE tb_sppd SET status='ditolak', catatan_tolak='".($conn->real_escape_string($catatan))."', id_pimpinan=$id_peg_pim WHERE id_sppd=$id");
        $msg = 'SPPD telah ditolak.';
    }
    header("Location: /sppd/pages/sppd/detail.php?id=$id&msg=".urlencode($msg));
    exit;
}

// Proses verifikasi admin
if ($_SERVER['REQUEST_METHOD']==='POST' && $role==='admin') {
    $action = $_POST['action'] ?? '';
    if ($action==='verifikasi') {
        $conn->query("UPDATE tb_sppd SET status='diverifikasi' WHERE id_sppd=$id");
    } elseif ($action==='terbitkan') {
        $no = trim($_POST['no_sppd']);
        // Jika tidak diisi, generate otomatis
        if (!$no) {
            $bln_romawi = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            $bulan = (int)date('m');
            $tahun = date('Y');
            $romawi = $bln_romawi[$bulan];
            // Cari nomor urut terakhir bulan ini
            $q = $conn->query("SELECT no_sppd FROM tb_sppd WHERE no_sppd LIKE '%/BKBP/$romawi/$tahun' ORDER BY id_sppd DESC LIMIT 1");
            $last = $q->fetch_assoc();
            if ($last && preg_match('/^(\d+)\//', $last['no_sppd'], $m)) {
                $next = (int)$m[1] + 1;
            } else {
                $next = 1;
            }
            $no = sprintf('%03d', $next) . "/BKBP/$romawi/$tahun";
        }
        $conn->query("UPDATE tb_sppd SET no_sppd='".($conn->real_escape_string($no))."' WHERE id_sppd=$id");
    }
    header("Location: /sppd/pages/sppd/detail.php?id=$id");
    exit;
}

if (isset($_GET['msg'])) $msg = htmlspecialchars($_GET['msg']);
$dur = (strtotime($sppd['tgl_kembali']) - strtotime($sppd['tgl_berangkat'])) / 86400 + 1;
$status_label = ['draf'=>'Draf','diajukan'=>'Diajukan','diverifikasi'=>'Diverifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak','selesai'=>'Selesai'];
$status_color = ['draf'=>'gray','diajukan'=>'blue','diverifikasi'=>'amber','disetujui'=>'green','ditolak'=>'red','selesai'=>'green'];
$trans_meta = [
  'Kendaraan Dinas'   => ['Keterangan / Jenis Kendaraan', 'No. Polisi',            'Biaya'],
  'Kendaraan Pribadi' => ['Keterangan / Jenis Kendaraan', 'No. Polisi',            'Biaya BBM / Tol'],
  'Transportasi Umum' => ['Nama PO / Operator',           'No. Tiket',             'Harga Tiket'],
  'Pesawat Terbang'   => ['Nama Maskapai',                'No. Tiket',             'Harga Tiket'],
  'Lainnya'           => ['Nama Moda Transportasi',       'No. Tiket / Keterangan','Biaya'],
];
$tm = $trans_meta[$sppd['kendaraan'] ?? ''] ?? ['Nama / Keterangan', 'No. Tiket', 'Biaya'];
?>

<div class="page-head">
  <div>
    <h2>Detail SPPD</h2>
    <p><?= $sppd['no_sppd'] ? 'No. '.$sppd['no_sppd'] : 'Belum diterbitkan' ?></p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="/sppd/pages/sppd/daftar.php" class="btn btn-secondary">&#8592; Kembali</a>
    <?php if ($role !== 'pimpinan'): ?>
    <a href="/sppd/pages/sppd/kelola.php?id=<?= $id ?>" class="btn btn-warning">&#9881; Kelola SPPD</a>
    <?php endif; ?>
    <?php if ($sppd['status']==='disetujui' && $role !== 'pegawai'): ?>
    <a href="/sppd/pages/laporan/cetak_sppd.php?id=<?= $id ?>" class="btn btn-primary" target="_blank">&#128424; Cetak SPPD</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($sppd['catatan_tolak'] && $sppd['status']==='ditolak'): ?>
<div class="alert alert-danger"><strong>Alasan penolakan:</strong> <?= htmlspecialchars($sppd['catatan_tolak']) ?></div>
<?php endif; ?>

<!-- STATUS BADGE -->
<div style="margin-bottom:16px">
  <span class="badge badge-<?= $status_color[$sppd['status']] ?>" style="font-size:13px;padding:6px 14px">
    Status: <?= $status_label[$sppd['status']] ?? $sppd['status'] ?>
  </span>
</div>

<!-- INFO PEGAWAI -->
<div class="card">
  <div class="card-header"><div class="card-title">Data Pegawai Pengaju</div></div>
  <div class="card-body">
    <div class="form-grid-3">
      <div class="form-group"><label class="form-label">NIP</label><input class="form-control" value="<?= htmlspecialchars($sppd['nip']) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Nama Lengkap</label><input class="form-control" value="<?= htmlspecialchars($sppd['nama_lengkap']) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Jabatan</label><input class="form-control" value="<?= htmlspecialchars($sppd['nama_jabatan']) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Golongan / Pangkat</label><input class="form-control" value="<?= htmlspecialchars($sppd['golongan'].'/'.$sppd['pangkat']) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Jenis Pegawai</label><input class="form-control" value="<?= htmlspecialchars($sppd['jenis_pegawai'] ?? 'PNS') ?>" disabled></div>
      <div class="form-group"><label class="form-label">Unit Kerja</label><input class="form-control" value="<?= htmlspecialchars($sppd['unit_kerja'] ?? '-') ?>" disabled></div>
    </div>
  </div>
</div>

<!-- DETAIL PERJALANAN -->
<div class="card">
  <div class="card-header"><div class="card-title">Detail Perjalanan Dinas</div></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Jenis Kegiatan</label><input class="form-control" value="<?= htmlspecialchars($sppd['nama_kegiatan']) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Tujuan / Instansi</label><input class="form-control" value="<?= htmlspecialchars($sppd['nama_instansi'].' — '.$sppd['kota']) ?>" disabled></div>
      <div class="form-group full"><label class="form-label">Keperluan</label><textarea class="form-control" rows="3" disabled><?= htmlspecialchars($sppd['keperluan']) ?></textarea></div>
      <div class="form-group"><label class="form-label">Tanggal Berangkat</label><input class="form-control" value="<?= date('d/m/Y', strtotime($sppd['tgl_berangkat'])) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Tanggal Kembali</label><input class="form-control" value="<?= date('d/m/Y', strtotime($sppd['tgl_kembali'])) ?> (<?= $dur ?> hari)" disabled></div>
      <div class="form-group"><label class="form-label">Kendaraan</label><input class="form-control" value="<?= htmlspecialchars($sppd['kendaraan'] ?? '-') ?>" disabled></div>
      <?php if (($sppd['transportasi_nama'] ?? '') !== '' || ($sppd['transportasi_no_tiket'] ?? '') !== '' || ($sppd['transportasi_biaya'] ?? '') !== ''): ?>
      <div class="form-group"><label class="form-label"><?= htmlspecialchars($tm[0]) ?></label><input class="form-control" value="<?= htmlspecialchars($sppd['transportasi_nama'] ?? '-') ?>" disabled></div>
      <div class="form-group"><label class="form-label"><?= htmlspecialchars($tm[1]) ?></label><input class="form-control" value="<?= ($sppd['transportasi_no_tiket'] ?? '') !== '' ? htmlspecialchars($sppd['transportasi_no_tiket']) : '-' ?>" disabled></div>
      <div class="form-group"><label class="form-label"><?= htmlspecialchars($tm[2]) ?></label><input class="form-control" value="<?= $sppd['transportasi_biaya'] ? 'Rp '.number_format($sppd['transportasi_biaya'],0,',','.') : '-' ?>" disabled></div>
      <?php endif; ?>
      <div class="form-group"><label class="form-label">Sumber Anggaran</label><input class="form-control" value="[<?= htmlspecialchars($sppd['kode_anggaran']) ?>] <?= htmlspecialchars($sppd['nama_program']) ?>" disabled></div>
    </div>
  </div>
</div>

<!-- ANGGOTA ROMBONGAN -->
<?php if ($anggota->num_rows > 0): ?>
<div class="card">
  <div class="card-header"><div class="card-title">Anggota Rombongan</div></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Peran</th></tr></thead>
      <tbody>
        <?php $no=1; while ($a = $anggota->fetch_assoc()): ?>
        <tr><td><?=$no++?></td><td><?=htmlspecialchars($a['nip'])?></td><td><?=htmlspecialchars($a['nama_lengkap'])?></td><td><?=htmlspecialchars($a['nama_jabatan'])?></td><td><?=htmlspecialchars($a['peran']??'-')?></td></tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- TOMBOL ADMIN: VERIFIKASI & TERBITKAN -->
<?php if ($role==='admin'): ?>
<div class="card">
  <div class="card-header"><div class="card-title">Tindakan Admin</div></div>
  <div class="card-body">
    <?php if ($sppd['status']==='diajukan'): ?>
    <form method="POST" style="display:inline">
      <input type="hidden" name="action" value="verifikasi">
      <button class="btn btn-warning">&#10003; Verifikasi & Teruskan ke Pimpinan</button>
    </form>
    <?php elseif ($sppd['status']==='disetujui' && !$sppd['no_sppd']):
        // Generate nomor otomatis
        $bln_romawi = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $bulan = (int)date('m');
        $tahun = date('Y');
        $romawi = $bln_romawi[$bulan];
        $q = $conn->query("SELECT no_sppd FROM tb_sppd WHERE no_sppd LIKE '%/BKBP/$romawi/$tahun' ORDER BY id_sppd DESC LIMIT 1");
        $last = $q->fetch_assoc();
        if ($last && preg_match('/^(\d+)\//', $last['no_sppd'], $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $auto_no = sprintf('%03d', $next) . "/BKBP/$romawi/$tahun";
    ?>
    <form method="POST" style="display:flex;gap:12px;align-items:flex-end">
      <input type="hidden" name="action" value="terbitkan">
      <div class="form-group" style="flex:1;margin:0">
        <label class="form-label">Nomor SPPD <span class="req">*</span></label>
        <input class="form-control" name="no_sppd" value="<?= $auto_no ?>" placeholder="Contoh: 094/BKBP/IV/2026">
        <div class="form-hint">Kosongkan untuk menggunakan nomor otomatis</div>
      </div>
      <button class="btn btn-success">Terbitkan Nomor SPPD</button>
    </form>
    <?php else: ?>
    <p style="color:var(--gray-400);font-size:13px">Tidak ada tindakan yang tersedia untuk status saat ini.</p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- FORM PERSETUJUAN PIMPINAN -->
<?php if ($role==='pimpinan' && in_array($sppd['status'],['diverifikasi'])): ?>
<div class="card">
  <div class="card-header"><div class="card-title">Keputusan Pimpinan</div></div>
  <div class="card-body">
    <form method="POST">
      <div class="form-group" style="margin-bottom:16px">
        <label class="form-label">Catatan / Alasan (wajib diisi jika menolak)</label>
        <textarea class="form-control" name="catatan_tolak" rows="3" placeholder="Tuliskan catatan atau alasan penolakan..."></textarea>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" name="keputusan" value="setuju" class="btn btn-success">&#10003; Setujui SPPD</button>
        <button type="submit" name="keputusan" value="tolak" class="btn btn-danger">&#10005; Tolak SPPD</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- INFO PERSETUJUAN -->
<?php if ($sppd['nama_pimpinan']): ?>
<div class="card">
  <div class="card-header"><div class="card-title">Informasi Persetujuan</div></div>
  <div class="card-body">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Disetujui Oleh</label><input class="form-control" value="<?= htmlspecialchars($sppd['nama_pimpinan']) ?>" disabled></div>
      <div class="form-group"><label class="form-label">Tanggal Persetujuan</label><input class="form-control" value="<?= $sppd['tgl_persetujuan'] ? date('d/m/Y H:i', strtotime($sppd['tgl_persetujuan'])) : '-' ?>" disabled></div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php ob_end_flush(); require_once '../../includes/footer.php'; ?>
