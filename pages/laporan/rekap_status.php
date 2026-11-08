<?php
$halaman='Rekap Status'; $sub_judul='Laporan / Status SPPD'; $aktif='lap_status';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$tahun = $_GET['tahun'] ?? date('Y');
$data  = $conn->query("
    SELECT status, COUNT(*) AS total
    FROM tb_sppd WHERE YEAR(tgl_berangkat)=$tahun
    GROUP BY status ORDER BY FIELD(status,'draf','diajukan','diverifikasi','disetujui','ditolak','selesai')");
$label=['draf'=>'Draf','diajukan'=>'Diajukan','diverifikasi'=>'Diverifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak','selesai'=>'Selesai'];
$warna=['draf'=>'gray','diajukan'=>'blue','diverifikasi'=>'amber','disetujui'=>'green','ditolak'=>'red','selesai'=>'green'];
$grand=0;
?>
<div class="page-head">
  <div><h2>Rekap Status SPPD</h2><p>Distribusi status seluruh SPPD tahun <?=$tahun?></p></div>
  <button onclick="window.print()" class="btn btn-primary">&#128424; Cetak Laporan</button>
</div>
<div style="display:none" class="print-header">
  <div style="text-align:center;margin-bottom:20px;border-bottom:2px solid #000;padding-bottom:12px">
    <div style="font-size:14pt;font-weight:bold">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div style="font-size:12pt;font-weight:bold">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div style="font-size:10pt;margin-top:6px">Distribusi Status SPPD — Tahun <?=$tahun?></div>
  </div>
</div>
<div class="card">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
      <div class="form-group" style="margin:0"><label class="form-label">Tahun</label>
        <input class="form-control" type="number" name="tahun" value="<?=$tahun?>" min="2020" max="2030" style="width:100px"></div>
      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Distribusi Status SPPD Tahun <?=$tahun?></div></div>
  <div class="card-body">
    <?php $map=[]; while($r=$data->fetch_assoc()){$map[$r['status']]=$r['total'];$grand+=$r['total'];} ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
      <?php foreach($label as $k=>$l): $tot=$map[$k]??0; $pct=$grand>0?round($tot/$grand*100):0; ?>
      <div style="background:var(--gray-50);border-radius:var(--radius);padding:16px;text-align:center">
        <div style="font-size:11px;color:var(--gray-400);margin-bottom:4px"><?=$l?></div>
        <div style="font-size:28px;font-weight:600;color:var(--gray-800)"><?=$tot?></div>
        <div style="font-size:12px;color:var(--gray-400)"><?=$pct?>%</div>
        <div style="margin-top:8px;height:4px;background:var(--gray-200);border-radius:99px;overflow:hidden">
          <div style="width:<?=$pct?>%;height:100%;background:var(--<?=$warna[$k]?>);border-radius:99px"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
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
