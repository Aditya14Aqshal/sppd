<?php
$halaman='Pegawai Teraktif'; $sub_judul='Laporan / Pegawai Teraktif'; $aktif='lap_pegawai_aktif';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$tahun = $_GET['tahun'] ?? date('Y');
$data  = $conn->query("
    SELECT p.nama_lengkap, p.nip, j.nama_jabatan, j.golongan, p.unit_kerja,
           COUNT(s.id_sppd) AS total_sppd,
           SUM(DATEDIFF(s.tgl_kembali,s.tgl_berangkat)+1) AS total_hari,
           SUM(IF(s.status='selesai',1,0)) AS selesai
    FROM tb_pegawai p
    JOIN tb_jabatan j ON p.id_jabatan=j.id_jabatan
    LEFT JOIN tb_sppd s ON p.id_pegawai=s.id_pengaju AND YEAR(s.tgl_berangkat)=$tahun
    GROUP BY p.id_pegawai
    HAVING total_sppd>0
    ORDER BY total_sppd DESC, total_hari DESC");
$grand_sppd=0; $grand_hari=0;
?>
<div class="page-head">
  <div><h2>Pegawai Teraktif Perjalanan Dinas</h2><p>Peringkat pegawai berdasarkan frekuensi dinas tahun <?=$tahun?></p></div>
  <button onclick="window.print()" class="btn btn-primary">&#128424; Cetak Laporan</button>
</div>
<div style="display:none" class="print-header">
  <div style="text-align:center;margin-bottom:20px;border-bottom:2px solid #000;padding-bottom:12px">
    <div style="font-size:14pt;font-weight:bold">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div style="font-size:12pt;font-weight:bold">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div style="font-size:10pt;margin-top:6px">Pegawai Teraktif Perjalanan Dinas — Tahun <?=$tahun?></div>
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
  <div class="card-header"><div class="card-title">Peringkat Pegawai Tahun <?=$tahun?></div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>#</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Unit Kerja</th><th style="text-align:center">Jml SPPD</th><th style="text-align:center">Total Hari</th><th style="text-align:center">Selesai</th></tr></thead>
    <tbody>
      <?php $no=1; while($r=$data->fetch_assoc()): $grand_sppd+=$r['total_sppd']; $grand_hari+=$r['total_hari']; ?>
      <tr>
        <td style="font-size:15px;font-weight:600;color:var(--gray-400)">#<?=$no++?></td>
        <td style="font-size:11px"><?=htmlspecialchars($r['nip'])?></td>
        <td><strong><?=htmlspecialchars($r['nama_lengkap'])?></strong></td>
        <td style="font-size:12px"><?=htmlspecialchars($r['nama_jabatan'])?></td>
        <td><?=htmlspecialchars($r['unit_kerja'])?></td>
        <td style="text-align:center;font-weight:600"><?=$r['total_sppd']?></td>
        <td style="text-align:center"><?=$r['total_hari']?> hr</td>
        <td style="text-align:center"><?=$r['selesai']?></td>
      </tr>
      <?php endwhile; if($data->num_rows===0): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--gray-400);padding:24px">Belum ada data</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot><tr style="background:var(--gray-50);font-weight:600">
      <td colspan="5" style="text-align:right">TOTAL</td>
      <td style="text-align:center"><?=$grand_sppd?></td><td style="text-align:center"><?=$grand_hari?> hr</td><td></td>
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
