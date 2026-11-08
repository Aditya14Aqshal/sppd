<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /sppd/pages/auth/login.php'); exit; }
require_once '../../config/koneksi.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("
    SELECT s.*,p.nama_lengkap,p.nip,p.unit_kerja,
           j.nama_jabatan,j.golongan,j.pangkat,
           t.nama_instansi,t.kota,t.provinsi,
           jk.nama_kegiatan,
           pp.nama_lengkap AS nama_pimpinan,
           j2.nama_jabatan AS jabatan_pimpinan,
           j2.nip AS nip_pimpinan
    FROM tb_sppd s
    JOIN tb_pegawai p   ON s.id_pengaju=p.id_pegawai
    JOIN tb_jabatan j   ON p.id_jabatan=j.id_jabatan
    JOIN tb_tujuan  t   ON s.id_tujuan=t.id_tujuan
    JOIN tb_jenis_kegiatan jk ON s.id_kegiatan=jk.id_kegiatan
    LEFT JOIN tb_pegawai pp ON s.id_pimpinan=pp.id_pegawai
    LEFT JOIN tb_jabatan j2 ON pp.id_jabatan=j2.id_jabatan
    WHERE s.id_sppd=?
");
$stmt->bind_param('i',$id); $stmt->execute();
$s=$stmt->get_result()->fetch_assoc();
if (!$s) die('Data tidak ditemukan.');

$anggota=$conn->query("SELECT pg.nama_lengkap,pg.nip,jb.nama_jabatan,jb.golongan,sa.peran FROM tb_sppd_anggota sa JOIN tb_pegawai pg ON sa.id_pegawai=pg.id_pegawai JOIN tb_jabatan jb ON pg.id_jabatan=jb.id_jabatan WHERE sa.id_sppd=$id");
$bln=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tgl_terbit=$s['tgl_persetujuan']?date('d',strtotime($s['tgl_persetujuan'])).' '.$bln[(int)date('m',strtotime($s['tgl_persetujuan']))].' '.date('Y',strtotime($s['tgl_persetujuan'])):date('d').' '.$bln[(int)date('m')].' '.date('Y');
$no_st=str_replace('SPPD','ST',$s['no_sppd']??'......../BKBP/ST/...../'.date('Y'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Surat Tugas — <?=htmlspecialchars($s['no_sppd']??'')?></title>
<link rel="stylesheet" href="/sppd/assets/css/style.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',serif;font-size:12pt;background:white}
.print-page{width:210mm;min-height:297mm;padding:20mm 25mm 20mm 30mm;margin:auto;background:white}
.kop{display:flex;align-items:center;gap:18px;border-bottom:3px solid #000;padding-bottom:10px;margin-bottom:16px}
.kop-logo{width:65px;height:65px;border:2px solid #000;display:flex;align-items:center;justify-content:center;font-size:9px;text-align:center;font-weight:bold}
.kop-text{flex:1;text-align:center}
.kop-title{font-size:13pt;font-weight:bold;line-height:1.3}
.kop-sub{font-size:11pt}
.kop-address{font-size:9.5pt;color:#333}
.doc-title{text-align:center;margin:18px 0 4px}
.doc-title h2{font-size:14pt;font-weight:bold;text-decoration:underline;letter-spacing:1px}
.doc-nomor{text-align:center;font-size:11pt;margin-bottom:18px}
.dasar p{font-size:11pt;margin-bottom:4px}
.isi{margin:14px 0;font-size:11pt}
.isi p{margin-bottom:6px}
.pejabat-table{width:100%;border-collapse:collapse;margin:10px 0 16px;font-size:11pt}
.pejabat-table td{padding:3px 6px;vertical-align:top}
.pejabat-table td:first-child{width:40%}
.pejabat-table td:nth-child(2){width:4%;text-align:center}
.peserta-table{width:100%;border-collapse:collapse;margin:10px 0;font-size:11pt}
.peserta-table th,.peserta-table td{border:1px solid #000;padding:5px 8px;vertical-align:top}
.peserta-table th{background:#eee;text-align:center;font-size:10pt}
.ttd-row{display:flex;justify-content:flex-end;margin-top:30px}
.ttd-box{text-align:center;width:240px;font-size:11pt}
.ttd-space{height:70px}
.ttd-nama{font-weight:bold;text-decoration:underline}
.ttd-nip{font-size:10pt}
.no-print{font-family:var(--font)}
@media print{.no-print{display:none!important}.print-page{margin:0;padding:15mm 20mm 15mm 25mm}body{background:white}}
</style>
</head>
<body>
<div class="no-print" style="padding:12px 24px;background:#1B4F8A;display:flex;align-items:center;justify-content:space-between">
  <span style="color:white;font-size:14px;font-weight:600">Preview Surat Tugas</span>
  <div style="display:flex;gap:10px">
    <a href="/sppd/pages/sppd/detail.php?id=<?=$id?>" style="padding:7px 14px;background:rgba(255,255,255,.15);color:white;border-radius:6px;font-size:13px">&#8592; Kembali</a>
    <button onclick="window.print()" style="padding:7px 18px;background:#D4A017;color:white;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer">&#128424; Cetak / PDF</button>
  </div>
</div>

<div class="print-page">
  <div class="kop">
    <div class="kop-logo">LOGO<br>PEMDA</div>
    <div class="kop-text">
      <div class="kop-title">PEMERINTAH KOTA KUALA KAPUAS<br>BADAN KESATUAN BANGSA DAN POLITIK</div>
      <div class="kop-address">Jl. ........................... No. ..., Kuala Kapuas, Kalimantan Tengah<br>Telp. ............... Email: bakesbangpol@kualakpuas.go.id</div>
    </div>
  </div>

  <div class="doc-title"><h2>SURAT TUGAS</h2></div>
  <div class="doc-nomor">Nomor: <?=htmlspecialchars($no_st)?></div>

  <div class="dasar">
    <p><strong>Dasar:</strong></p>
    <p style="margin-left:20px">1. Program Kerja Badan Kesatuan Bangsa dan Politik Kota Kuala Kapuas Tahun <?=date('Y')?></p>
    <p style="margin-left:20px">2. SPPD Nomor: <?=htmlspecialchars($s['no_sppd']??'—')?> Tanggal <?=$tgl_terbit?></p>
  </div>

  <div class="isi" style="margin-top:16px">
    <p><strong><?=$s['jabatan_pimpinan']??'Kepala Badan Kesatuan Bangsa dan Politik'?> Kota Kuala Kapuas</strong>, menugaskan kepada:</p>
  </div>

  <table class="pejabat-table">
    <tr><td>Nama</td><td>:</td><td><strong><?=htmlspecialchars($s['nama_lengkap'])?></strong></td></tr>
    <tr><td>NIP</td><td>:</td><td><?=htmlspecialchars($s['nip'])?></td></tr>
    <tr><td>Pangkat / Golongan</td><td>:</td><td><?=htmlspecialchars($s['pangkat'].'/'.$s['golongan'])?></td></tr>
    <tr><td>Jabatan</td><td>:</td><td><?=htmlspecialchars($s['nama_jabatan'])?></td></tr>
    <tr><td>Unit Kerja</td><td>:</td><td><?=htmlspecialchars($s['unit_kerja']??'-')?></td></tr>
  </table>

  <?php if($anggota->num_rows>0): ?>
  <p style="font-size:11pt;margin-bottom:8px">Beserta anggota:</p>
  <table class="peserta-table">
    <thead><tr><th>No</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Gol.</th></tr></thead>
    <tbody><?php $no=1;while($a=$anggota->fetch_assoc()):?>
    <tr><td style="text-align:center"><?=$no++?></td><td><?=htmlspecialchars($a['nama_lengkap'])?></td><td><?=htmlspecialchars($a['nip'])?></td><td><?=htmlspecialchars($a['nama_jabatan'])?></td><td style="text-align:center"><?=htmlspecialchars($a['golongan'])?></td></tr>
    <?php endwhile;?></tbody>
  </table>
  <?php endif; ?>

  <div class="isi">
    <p>Untuk melaksanakan <strong><?=htmlspecialchars($s['nama_kegiatan'])?></strong> pada:</p>
    <table class="pejabat-table" style="margin-top:8px">
      <tr><td>Hari / Tanggal</td><td>:</td><td><?=date('l',strtotime($s['tgl_berangkat']))?>, <?=tgl_indo($s['tgl_berangkat'])?> s/d <?=tgl_indo($s['tgl_kembali'])?></td></tr>
      <tr><td>Tempat</td><td>:</td><td><?=htmlspecialchars($s['nama_instansi'].', '.$s['kota'].', '.$s['provinsi'])?></td></tr>
      <tr><td>Keperluan</td><td>:</td><td><?=htmlspecialchars($s['keperluan'])?></td></tr>
    </table>
  </div>

  <p style="font-size:11pt;margin-top:14px">Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>

  <div class="ttd-row">
    <div class="ttd-box">
      <p>Kuala Kapuas, <?=$tgl_terbit?></p>
      <p><?=$s['jabatan_pimpinan']??'Kepala Badan'?>,</p>
      <div class="ttd-space"></div>
      <p class="ttd-nama"><?=htmlspecialchars($s['nama_pimpinan']??'......................................')?></p>
      <p class="ttd-nip">NIP. ......................................</p>
    </div>
  </div>
</div>
</body>
</html>
<?php
function tgl_indo($d){$b=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];return date('d',strtotime($d)).' '.$b[(int)date('m',strtotime($d))].' '.date('Y',strtotime($d));}
?>
