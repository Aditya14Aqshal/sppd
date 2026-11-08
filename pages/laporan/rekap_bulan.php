<?php
$halaman='Rekap per Bulan'; $sub_judul='Laporan / Per Bulan'; $aktif='lap_bulan';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$tahun = $_GET['tahun'] ?? date('Y');
$data  = $conn->query("
    SELECT MONTH(tgl_berangkat) AS bulan,
           COUNT(*) AS total,
           SUM(IF(status='selesai',1,0)) AS selesai,
           SUM(IF(status='disetujui',1,0)) AS disetujui,
           SUM(IF(status='ditolak',1,0)) AS ditolak
    FROM tb_sppd WHERE YEAR(tgl_berangkat)=$tahun
    GROUP BY MONTH(tgl_berangkat) ORDER BY bulan");
$bln=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
?>
<div class="page-head">
  <div><h2>Rekap SPPD per Bulan</h2><p>Distribusi SPPD setiap bulan di tahun <?=$tahun?></p></div>
  <button onclick="window.print()" class="btn btn-primary">&#128424; Cetak Laporan</button>
</div>
<div class="card no-print">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
      <div class="form-group" style="margin:0"><label class="form-label">Tahun</label>
        <input class="form-control" type="number" name="tahun" value="<?=$tahun?>" min="2020" max="2030" style="width:100px"></div>
      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
  </div>
</div>
<div style="display:none" class="print-header">
  <div style="text-align:center;margin-bottom:20px;border-bottom:2px solid #000;padding-bottom:12px">
    <div style="font-size:14pt;font-weight:bold">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div style="font-size:12pt;font-weight:bold">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div style="font-size:10pt;margin-top:6px">Rekapitulasi SPPD per Bulan — Tahun <?=$tahun?></div>
  </div>
</div>
<div class="card">
  <div class="card-header"><div class="card-title">Distribusi Bulanan SPPD Tahun <?=$tahun?></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Bulan</th><th style="text-align:center">Total</th><th style="text-align:center">Selesai</th><th style="text-align:center">Disetujui</th><th style="text-align:center">Ditolak</th></tr></thead>
    <tbody>
      <?php $tot=0; $sel=0; $dis=0; $ditolak=0;
      $map=[]; while($r=$data->fetch_assoc()) $map[$r['bulan']]=$r;
      for($i=1;$i<=12;$i++): $r=$map[$i]??null; $tot+=$r['total']??0; $sel+=$r['selesai']??0; $dis+=$r['disetujui']??0; $ditolak+=$r['ditolak']??0; ?>
      <tr>
        <td><strong><?=$bln[$i-1]?></strong></td>
        <td style="text-align:center"><?=$r?$r['total']:0?></td>
        <td style="text-align:center"><?=$r?$r['selesai']:0?></td>
        <td style="text-align:center"><?=$r?$r['disetujui']:0?></td>
        <td style="text-align:center"><?=$r?$r['ditolak']:0?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
    <tfoot><tr style="background:var(--gray-50);font-weight:600">
      <td>TOTAL</td><td style="text-align:center"><?=$tot?></td><td style="text-align:center"><?=$sel?></td><td style="text-align:center"><?=$dis?></td><td style="text-align:center"><?=$ditolak?></td>
    </tr></tfoot>
  </table></div>
</div>
<style>
@media print {
  .no-print { display: none !important; }
  .print-header { display: block !important; }
  .sidebar, .topbar, .page-head button { display: none !important; }
  .main { margin: 0; padding: 0; }
  .content { padding: 0; }
  .card { box-shadow: none; }
}
</style>
<?php require_once '../../includes/footer.php'; ?>
