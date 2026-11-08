<?php
$halaman='Rekap per Unit'; $sub_judul='Laporan / Per Unit Kerja'; $aktif='lap_unit';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$tahun = $_GET['tahun'] ?? date('Y');
$data  = $conn->query("
    SELECT p.unit_kerja, COUNT(s.id_sppd) AS total,
           SUM(IF(s.status IN ('disetujui','selesai'),1,0)) AS terealisasi,
           SUM(DATEDIFF(s.tgl_kembali,s.tgl_berangkat)+1) AS total_hari
    FROM tb_pegawai p LEFT JOIN tb_sppd s ON p.id_pegawai=s.id_pengaju AND YEAR(s.tgl_berangkat)=$tahun
    WHERE p.unit_kerja!='' GROUP BY p.unit_kerja ORDER BY total DESC");
$grand_total=0; $grand_hari=0;
?>
<div class="page-head">
  <div><h2>Rekap SPPD per Unit Kerja</h2><p>Jumlah perjalanan dinas per unit/bidang tahun <?=$tahun?></p></div>
  <button onclick="window.print()" class="btn btn-primary">&#128424; Cetak Laporan</button>
</div>
<div style="display:none" class="print-header">
  <div style="text-align:center;margin-bottom:20px;border-bottom:2px solid #000;padding-bottom:12px">
    <div style="font-size:14pt;font-weight:bold">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div style="font-size:12pt;font-weight:bold">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div style="font-size:10pt;margin-top:6px">Rekapitulasi SPPD per Unit Kerja — Tahun <?=$tahun?></div>
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
  <div class="card-header"><div class="card-title">SPPD per Unit Kerja Tahun <?=$tahun?></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>#</th><th>Unit Kerja / Bidang</th><th style="text-align:center">Jumlah SPPD</th><th style="text-align:center">Terealisasi</th><th style="text-align:center">Total Hari</th></tr></thead>
    <tbody>
      <?php $no=1; while($r=$data->fetch_assoc()): $grand_total+=$r['total']; $grand_hari+=$r['total_hari']; ?>
      <tr>
        <td><?=$no++?></td><td><?=htmlspecialchars($r['unit_kerja'])?></td>
        <td style="text-align:center"><?=$r['total']?></td>
        <td style="text-align:center"><?=$r['terealisasi']?></td>
        <td style="text-align:center"><?=$r['total_hari']?:0?> hr</td>
      </tr>
      <?php endwhile; if($data->num_rows===0): ?>
      <tr><td colspan="5" style="text-align:center;color:var(--gray-400);padding:24px">Belum ada data</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot><tr style="background:var(--gray-50);font-weight:600">
      <td colspan="2" style="text-align:right">TOTAL</td>
      <td style="text-align:center"><?=$grand_total?></td><td></td><td style="text-align:center"><?=$grand_hari?> hr</td>
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
