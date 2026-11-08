<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /sppd/pages/auth/login.php'); exit; }
require_once '../../config/koneksi.php';
$id = (int)($_GET['id'] ?? 0);
$sppd=$conn->query("
    SELECT s.*,p.nama_lengkap,p.nip,j.nama_jabatan,j.golongan,j.pangkat,
           t.nama_instansi,t.kota,jk.nama_kegiatan,a.nama_program,a.kode_anggaran,
           pp.nama_lengkap AS nama_pimpinan, j2.nama_jabatan AS jabatan_pimpinan
    FROM tb_sppd s
    JOIN tb_pegawai p ON s.id_pengaju=p.id_pegawai
    JOIN tb_jabatan j ON p.id_jabatan=j.id_jabatan
    JOIN tb_tujuan t ON s.id_tujuan=t.id_tujuan
    JOIN tb_jenis_kegiatan jk ON s.id_kegiatan=jk.id_kegiatan
    JOIN tb_anggaran a ON s.id_anggaran=a.id_anggaran
    LEFT JOIN tb_pegawai pp ON s.id_pimpinan=pp.id_pegawai
    LEFT JOIN tb_jabatan j2 ON pp.id_jabatan=j2.id_jabatan
    WHERE s.id_sppd=$id
")->fetch_assoc();
if (!$sppd) die('Data tidak ditemukan.');

$biaya=$conn->query("SELECT bp.*,kb.jenis_biaya,kb.golongan,kb.zona FROM tb_biaya_perjalanan bp JOIN tb_komponen_biaya kb ON bp.id_komponen=kb.id_komponen WHERE bp.id_sppd=$id ORDER BY kb.jenis_biaya");
$total=$conn->query("SELECT SUM(besaran_realisasi*jumlah_hari) t FROM tb_biaya_perjalanan WHERE id_sppd=$id")->fetch_assoc()['t']??0;
$dur=(strtotime($sppd['tgl_kembali'])-strtotime($sppd['tgl_berangkat']))/86400+1;
$bln=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tgl_terbit=date('d').' '.$bln[(int)date('m')].' '.date('Y');
$jenis_label=['uang_harian'=>'Uang Harian','transport'=>'Biaya Transport','penginapan'=>'Biaya Penginapan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Rincian Biaya — <?=htmlspecialchars($sppd['no_sppd']??'')?></title>
<link rel="stylesheet" href="/sppd/assets/css/style.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',serif;font-size:11.5pt;background:white}
.print-page{width:210mm;min-height:297mm;padding:18mm 22mm 18mm 28mm;margin:auto;background:white}
.kop{display:flex;align-items:center;gap:16px;border-bottom:3px double #000;padding-bottom:8px;margin-bottom:14px}
.kop-logo{width:60px;height:60px;border:2px solid #000;display:flex;align-items:center;justify-content:center;font-size:8px;text-align:center;font-weight:bold}
.kop-text{flex:1;text-align:center}
.kop-title{font-size:12pt;font-weight:bold;line-height:1.3}
.kop-address{font-size:9pt;color:#333}
.doc-title{text-align:center;margin:14px 0 4px}
.doc-title h2{font-size:13pt;font-weight:bold;text-decoration:underline}
.doc-nomor{text-align:center;font-size:10.5pt;margin-bottom:14px}
.info-table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:10.5pt}
.info-table td{padding:2px 4px;vertical-align:top}
.info-table td:first-child{width:38%}
.info-table td:nth-child(2){width:3%;text-align:center}
.biaya-table{width:100%;border-collapse:collapse;font-size:10.5pt;margin-bottom:16px}
.biaya-table th{background:#ddd;border:1px solid #000;padding:5px 8px;text-align:center;font-size:10pt}
.biaya-table td{border:1px solid #000;padding:5px 8px;vertical-align:top}
.biaya-table .num{text-align:right}
.biaya-table .cen{text-align:center}
.total-row{background:#eee;font-weight:bold}
.ttd-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:30px;font-size:10.5pt}
.ttd-box{text-align:center}
.ttd-space{height:60px}
.ttd-nama{font-weight:bold;text-decoration:underline;font-size:10.5pt}
.no-print{font-family:var(--font)}
@media print{.no-print{display:none!important}.print-page{margin:0;padding:12mm 18mm 12mm 22mm}body{background:white}}
</style>
</head>
<body>
<div class="no-print" style="padding:12px 24px;background:#1B4F8A;display:flex;align-items:center;justify-content:space-between">
  <span style="color:white;font-size:14px;font-weight:600">Preview Rincian Biaya Perjalanan Dinas</span>
  <div style="display:flex;gap:10px">
    <a href="/sppd/pages/sppd/kelola.php?id=<?=$id?>&tab=biaya" style="padding:7px 14px;background:rgba(255,255,255,.15);color:white;border-radius:6px;font-size:13px">&#8592; Kembali</a>
    <button onclick="window.print()" style="padding:7px 18px;background:#D4A017;color:white;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer">&#128424; Cetak / PDF</button>
  </div>
</div>

<div class="print-page">
  <div class="kop">
    <div class="kop-logo">LOGO<br>PEMDA</div>
    <div class="kop-text">
      <div class="kop-title">PEMERINTAH KOTA KUALA KAPUAS<br>BADAN KESATUAN BANGSA DAN POLITIK</div>
      <div class="kop-address">Jl. .............. No. ..., Kuala Kapuas &nbsp;|&nbsp; Telp. ............... &nbsp;|&nbsp; Email: bakesbangpol@kualakpuas.go.id</div>
    </div>
  </div>

  <div class="doc-title"><h2>RINCIAN BIAYA PERJALANAN DINAS</h2></div>
  <div class="doc-nomor">Berdasarkan SPPD Nomor: <?=htmlspecialchars($sppd['no_sppd']??'—')?></div>

  <!-- INFO PERJALANAN -->
  <table class="info-table">
    <tr><td>Nama</td><td>:</td><td><strong><?=htmlspecialchars($sppd['nama_lengkap'])?></strong></td></tr>
    <tr><td>NIP</td><td>:</td><td><?=htmlspecialchars($sppd['nip'])?></td></tr>
    <tr><td>Pangkat / Golongan</td><td>:</td><td><?=htmlspecialchars($sppd['pangkat'].'/'.$sppd['golongan'])?></td></tr>
    <tr><td>Jabatan</td><td>:</td><td><?=htmlspecialchars($sppd['nama_jabatan'])?></td></tr>
    <tr><td>Kegiatan</td><td>:</td><td><?=htmlspecialchars($sppd['nama_kegiatan'])?></td></tr>
    <tr><td>Tujuan</td><td>:</td><td><?=htmlspecialchars($sppd['nama_instansi'].', '.$sppd['kota'])?></td></tr>
    <tr><td>Tanggal Berangkat</td><td>:</td><td><?=tgl_id($sppd['tgl_berangkat'],$bln)?></td></tr>
    <tr><td>Tanggal Kembali</td><td>:</td><td><?=tgl_id($sppd['tgl_kembali'],$bln)?> (<?=$dur?> hari)</td></tr>
    <tr><td>Sumber Anggaran</td><td>:</td><td>[<?=htmlspecialchars($sppd['kode_anggaran'])?>] <?=htmlspecialchars($sppd['nama_program'])?></td></tr>
  </table>

  <!-- TABEL BIAYA -->
  <table class="biaya-table">
    <thead>
      <tr>
        <th style="width:5%">No</th>
        <th>Uraian Biaya</th>
        <th style="width:10%">Gol.</th>
        <th style="width:12%">Jml Hari</th>
        <th style="width:20%">Satuan (Rp)</th>
        <th style="width:20%">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($b=$biaya->fetch_assoc()): $sub=$b['besaran_realisasi']*$b['jumlah_hari']; ?>
      <tr>
        <td class="cen"><?=$no++?></td>
        <td><?=$jenis_label[$b['jenis_biaya']]??$b['jenis_biaya']?></td>
        <td class="cen"><?=$b['golongan']?></td>
        <td class="cen"><?=$b['jumlah_hari']?></td>
        <td class="num"><?=number_format($b['besaran_realisasi'],0,',','.')?></td>
        <td class="num"><?=number_format($sub,0,',','.')?></td>
      </tr>
      <?php endwhile; if($biaya->num_rows===0): ?>
      <tr><td colspan="6" style="text-align:center;padding:16px">Belum ada rincian biaya</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="5" style="text-align:right;padding:7px 8px">JUMLAH TOTAL</td>
        <td class="num" style="padding:7px 8px">Rp <?=number_format($total,0,',','.')?></td>
      </tr>
      <tr>
        <td colspan="6" style="padding:7px 8px;font-size:10pt;font-style:italic">
          Terbilang: <strong><?=terbilang((int)$total)?> Rupiah</strong>
        </td>
      </tr>
    </tfoot>
  </table>

  <!-- TANDA TANGAN -->
  <div class="ttd-grid">
    <div class="ttd-box">
      <p>Yang Menerima,</p>
      <p style="font-size:9.5pt">(Pegawai Pelaksana)</p>
      <div class="ttd-space"></div>
      <p class="ttd-nama"><?=htmlspecialchars($sppd['nama_lengkap'])?></p>
      <p style="font-size:9.5pt">NIP. <?=htmlspecialchars($sppd['nip'])?></p>
    </div>
    <div class="ttd-box">
      <p>Kuala Kapuas, <?=$tgl_terbit?></p>
      <p>Mengetahui,</p>
      <p style="font-size:9.5pt"><?=htmlspecialchars($sppd['jabatan_pimpinan']??'Kepala Badan')?></p>
      <div class="ttd-space"></div>
      <p class="ttd-nama"><?=htmlspecialchars($sppd['nama_pimpinan']??'...............................')?></p>
      <p style="font-size:9.5pt">NIP. ......................................</p>
    </div>
  </div>
</div>
</body>
</html>
<?php
function tgl_id($d,$b){return date('d',strtotime($d)).' '.$b[(int)date('m',strtotime($d))].' '.date('Y',strtotime($d));}
function terbilang($n){
    $s=['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh','sebelas'];
    if($n<12)return $s[$n];
    if($n<20)return terbilang($n-10).' belas';
    if($n<100)return terbilang((int)($n/10)).' puluh '.terbilang($n%10);
    if($n<200)return 'seratus '.terbilang($n-100);
    if($n<1000)return terbilang((int)($n/100)).' ratus '.terbilang($n%100);
    if($n<2000)return 'seribu '.terbilang($n-1000);
    if($n<1000000)return terbilang((int)($n/1000)).' ribu '.terbilang($n%1000);
    if($n<1000000000)return terbilang((int)($n/1000000)).' juta '.terbilang($n%1000000);
    return terbilang((int)($n/1000000000)).' miliar '.terbilang($n%1000000000);
}
?>
