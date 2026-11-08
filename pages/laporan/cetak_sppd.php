<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /sppd/pages/auth/login.php'); exit; }
require_once '../../config/koneksi.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("
    SELECT s.*, p.nama_lengkap, p.nip, p.unit_kerja,
           j.nama_jabatan, j.golongan, j.pangkat,
           t.nama_instansi, t.kota, t.provinsi,
           jk.nama_kegiatan,
           a.kode_anggaran, a.nama_program,
           pp.nama_lengkap AS nama_pimpinan, pp2.nama_jabatan AS jabatan_pimpinan
    FROM tb_sppd s
    JOIN tb_pegawai p   ON s.id_pengaju  = p.id_pegawai
    JOIN tb_jabatan j   ON p.id_jabatan  = j.id_jabatan
    JOIN tb_tujuan  t   ON s.id_tujuan   = t.id_tujuan
    JOIN tb_jenis_kegiatan jk ON s.id_kegiatan = jk.id_kegiatan
    JOIN tb_anggaran a  ON s.id_anggaran = a.id_anggaran
    LEFT JOIN tb_pegawai pe ON s.id_pimpinan = pe.id_pegawai
    LEFT JOIN tb_jabatan pp2 ON pe.id_jabatan = pp2.id_jabatan
    LEFT JOIN tb_pegawai pp  ON s.id_pimpinan = pp.id_pegawai
    WHERE s.id_sppd = ?
");
$stmt->bind_param('i',$id); $stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
if (!$s) die('Data tidak ditemukan.');
$anggota = $conn->query("SELECT pg.nama_lengkap,pg.nip,jb.nama_jabatan,jb.golongan,sa.peran FROM tb_sppd_anggota sa JOIN tb_pegawai pg ON sa.id_pegawai=pg.id_pegawai JOIN tb_jabatan jb ON pg.id_jabatan=jb.id_jabatan WHERE sa.id_sppd=$id");
$dur = (strtotime($s['tgl_kembali']) - strtotime($s['tgl_berangkat'])) / 86400 + 1;
$trans_meta = [
  'Kendaraan Dinas'   => ['Keterangan / Jenis Kendaraan', 'No. Polisi',            'Biaya'],
  'Kendaraan Pribadi' => ['Keterangan / Jenis Kendaraan', 'No. Polisi',            'Biaya BBM / Tol'],
  'Transportasi Umum' => ['Nama PO / Operator',           'No. Tiket',             'Harga Tiket'],
  'Pesawat Terbang'   => ['Nama Maskapai',                'No. Tiket',             'Harga Tiket'],
  'Lainnya'           => ['Nama Moda Transportasi',       'No. Tiket / Keterangan','Biaya'],
];
$tm = $trans_meta[$s['kendaraan'] ?? ''] ?? ['Nama / Keterangan', 'No. Tiket', 'Biaya'];
$bln_id = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tgl_terbit = $s['tgl_persetujuan'] ? date('d', strtotime($s['tgl_persetujuan'])).' '.$bln_id[(int)date('m',strtotime($s['tgl_persetujuan']))].' '.date('Y',strtotime($s['tgl_persetujuan'])) : date('d').' '.$bln_id[(int)date('m')].' '.date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak SPPD — <?= htmlspecialchars($s['no_sppd'] ?? '') ?></title>
<link rel="stylesheet" href="/sppd/assets/css/style.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; background: white; }
  .no-print { font-family: var(--font); }
  .print-page { width: 210mm; min-height: 297mm; padding: 20mm 25mm 20mm 30mm; margin: auto; background: white; }
  .kop { display: flex; align-items: center; gap: 20px; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 16px; }
  .kop-logo { width: 70px; height: 70px; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center; font-weight: bold; }
  .kop-text { flex: 1; text-align: center; }
  .kop-title { font-size: 14pt; font-weight: bold; line-height: 1.3; }
  .kop-sub { font-size: 11pt; }
  .kop-address { font-size: 10pt; color: #333; }
  .doc-title { text-align: center; margin: 20px 0 6px; }
  .doc-title h2 { font-size: 14pt; font-weight: bold; text-decoration: underline; letter-spacing: 2px; }
  .doc-nomor { text-align: center; font-size: 11pt; margin-bottom: 20px; }
  .dasar { margin-bottom: 16px; font-size: 11pt; }
  .dasar p { margin-bottom: 4px; }
  .isi-table { width: 100%; border-collapse: collapse; margin: 12px 0 20px; font-size: 11pt; }
  .isi-table td { padding: 4px 8px; vertical-align: top; }
  .isi-table td:first-child { width: 35%; white-space: nowrap; }
  .isi-table td:nth-child(2) { width: 5%; text-align: center; }
  .cap-table { width: 100%; margin-top: 24px; font-size: 11pt; }
  .cap-table td { padding: 6px 12px; border: 1px solid #000; vertical-align: top; text-align: center; }
  .cap-table th { padding: 6px 12px; border: 1px solid #000; background: #eee; font-size: 10pt; }
  .ttd-section { margin-top: 24px; }
  .ttd-row { display: flex; justify-content: flex-end; }
  .ttd-box { text-align: center; width: 250px; }
  .ttd-box .ttd-space { height: 70px; }
  .ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; }
  .ttd-box .ttd-nip { font-size: 10pt; }
  .section-title { font-weight: bold; font-size: 11pt; margin: 16px 0 8px; border-bottom: 1px solid #000; padding-bottom: 4px; }
  @media print {
    .no-print { display: none !important; }
    .print-page { margin: 0; padding: 15mm 20mm 15mm 25mm; }
    body { background: white; }
  }
</style>
</head>
<body>
<div class="no-print" style="padding:14px 24px;background:#1B4F8A;display:flex;align-items:center;justify-content:space-between">
  <span style="color:white;font-size:14px;font-weight:600">Preview Cetak SPPD</span>
  <div style="display:flex;gap:10px">
    <a href="/sppd/pages/sppd/detail.php?id=<?=$id?>" style="padding:7px 16px;background:rgba(255,255,255,.15);color:white;border-radius:6px;font-size:13px">&#8592; Kembali</a>
    <button onclick="window.print()" style="padding:7px 18px;background:#D4A017;color:white;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer">&#128424; Cetak / Simpan PDF</button>
  </div>
</div>

<div class="print-page">
  <!-- KOP SURAT -->
  <div class="kop">
    <div class="kop-logo">LOGO<br>PEMDA</div>
    <div class="kop-text">
      <div class="kop-title">PEMERINTAH KOTA KUALA KAPUAS<br>BADAN KESATUAN BANGSA DAN POLITIK</div>
      <div class="kop-address">Jl. ........................ No. ..., Kuala Kapuas, Kalimantan Tengah<br>Telp. ........... Fax. ........... Email: bakesbangpol@kualakpaus.go.id</div>
    </div>
  </div>

  <!-- JUDUL -->
  <div class="doc-title"><h2>SURAT PERINTAH PERJALANAN DINAS</h2></div>
  <div class="doc-nomor">Nomor: <?= htmlspecialchars($s['no_sppd'] ?? '......../BKBP/...../'.date('Y')) ?></div>

  <!-- DASAR -->
  <div class="dasar">
    <p><strong>Dasar :</strong></p>
    <p style="margin-left:20px">1. Peraturan Menteri Keuangan Nomor 113/PMK.05/2012 tentang Perjalanan Dinas Dalam Negeri</p>
    <p style="margin-left:20px">2. DPA Badan Kesatuan Bangsa dan Politik Kota Kuala Kapuas Tahun Anggaran <?= date('Y') ?></p>
  </div>

  <!-- ISI SPPD -->
  <p><strong>Memerintahkan kepada :</strong></p>
  <table class="isi-table">
    <tr><td>Nama</td><td>:</td><td><strong><?= htmlspecialchars($s['nama_lengkap']) ?></strong></td></tr>
    <tr><td>NIP</td><td>:</td><td><?= htmlspecialchars($s['nip']) ?></td></tr>
    <tr><td>Jabatan</td><td>:</td><td><?= htmlspecialchars($s['nama_jabatan']) ?></td></tr>
    <tr><td>Golongan / Pangkat</td><td>:</td><td><?= htmlspecialchars($s['golongan'].'/'.$s['pangkat']) ?></td></tr>
    <tr><td>Unit Kerja</td><td>:</td><td><?= htmlspecialchars($s['unit_kerja'] ?? '-') ?></td></tr>
    <tr><td>Untuk Keperluan</td><td>:</td><td><?= htmlspecialchars($s['keperluan']) ?></td></tr>
    <tr><td>Berangkat Dari</td><td>:</td><td>Kuala Kapuas</td></tr>
    <tr><td>Ke</td><td>:</td><td><?= htmlspecialchars($s['nama_instansi'].', '.$s['kota'].', '.$s['provinsi']) ?></td></tr>
    <tr><td>Lamanya Perjalanan</td><td>:</td><td><?= $dur ?> (<?= terbilang($dur) ?>) hari</td></tr>
    <tr><td>Tanggal Berangkat</td><td>:</td><td><?= tgl_indo($s['tgl_berangkat']) ?></td></tr>
    <tr><td>Tanggal Kembali</td><td>:</td><td><?= tgl_indo($s['tgl_kembali']) ?></td></tr>
    <tr><td>Kendaraan</td><td>:</td><td><?= htmlspecialchars($s['kendaraan'] ?? 'Kendaraan Dinas') ?></td></tr>
    <?php if (($s['transportasi_nama'] ?? '') !== '' || ($s['transportasi_no_tiket'] ?? '') !== '' || ($s['transportasi_biaya'] ?? '') !== ''): ?>
    <tr><td><?= htmlspecialchars($tm[0]) ?></td><td>:</td><td><?= htmlspecialchars($s['transportasi_nama'] ?? '-') ?></td></tr>
    <tr><td><?= htmlspecialchars($tm[1]) ?></td><td>:</td><td><?= ($s['transportasi_no_tiket'] ?? '') !== '' ? htmlspecialchars($s['transportasi_no_tiket']) : '-' ?></td></tr>
    <tr><td><?= htmlspecialchars($tm[2]) ?></td><td>:</td><td><?= $s['transportasi_biaya'] ? 'Rp '.number_format($s['transportasi_biaya'],0,',','.') : '-' ?></td></tr>
    <?php endif; ?>
    <tr><td>Sumber Dana</td><td>:</td><td>[<?= htmlspecialchars($s['kode_anggaran']) ?>] <?= htmlspecialchars($s['nama_program']) ?></td></tr>
  </table>

  <?php if ($anggota->num_rows > 0): ?>
  <div class="section-title">Anggota Rombongan</div>
  <table class="cap-table">
    <thead><tr><th>No</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Gol.</th><th>Peran</th></tr></thead>
    <tbody><?php $no=1; while($a=$anggota->fetch_assoc()): ?>
    <tr><td><?=$no++?></td><td style="text-align:left"><?=htmlspecialchars($a['nama_lengkap'])?></td><td><?=htmlspecialchars($a['nip'])?></td><td style="text-align:left"><?=htmlspecialchars($a['nama_jabatan'])?></td><td><?=htmlspecialchars($a['golongan'])?></td><td><?=htmlspecialchars($a['peran']??'-')?></td></tr>
    <?php endwhile; ?></tbody>
  </table>
  <?php endif; ?>

  <!-- CAP INSTANSI -->
  <div class="section-title" style="margin-top:20px">Tanda Berangkat / Tiba (Diisi oleh Instansi Tujuan)</div>
  <table class="cap-table">
    <thead><tr><th>Keterangan</th><th>Berangkat</th><th>Tiba di Tempat Tujuan</th><th>Meninggalkan Tujuan</th><th>Tiba Kembali</th></tr></thead>
    <tbody>
      <tr><td>Tanggal</td><td style="height:40px"></td><td></td><td></td><td></td></tr>
      <tr><td>Pejabat yang Berwenang</td><td style="height:60px"></td><td></td><td></td><td></td></tr>
      <tr><td>Cap Dinas</td><td style="height:60px"></td><td></td><td></td><td></td></tr>
    </tbody>
  </table>

  <!-- TTD PIMPINAN -->
  <div class="ttd-section">
    <div class="ttd-row">
      <div class="ttd-box">
        <p>Kuala Kapuas, <?= $tgl_terbit ?></p>
        <p><?= htmlspecialchars($s['jabatan_pimpinan'] ?? 'Kepala Badan') ?>,</p>
        <div class="ttd-space"></div>
        <p class="ttd-nama"><?= htmlspecialchars($s['nama_pimpinan'] ?? '...................................') ?></p>
        <p class="ttd-nip">NIP. <?= $s['id_pimpinan'] ? '...........................' : '...........................' ?></p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php
function tgl_indo($d) {
    $bln=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return date('d',strtotime($d)).' '.$bln[(int)date('m',strtotime($d))].' '.date('Y',strtotime($d));
}
function terbilang($n) {
    $kata=['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh'];
    return $n<=10 ? ($kata[$n]??$n) : $n;
}
?>
