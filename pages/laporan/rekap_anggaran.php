<?php
$halaman='Rekap Anggaran'; $sub_judul='Laporan / Rekap Anggaran'; $aktif='lap_anggaran';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';
if (!in_array($_SESSION['role'],['admin','pimpinan'])){echo '<div class="alert alert-danger">Akses ditolak.</div>';require_once '../../includes/footer.php';exit;}

$tahun = $_GET['tahun'] ?? date('Y');
$data  = $conn->query("
    SELECT a.kode_anggaran, a.nama_program, a.pagu,
           COUNT(s.id_sppd) AS jumlah_sppd,
           SUM(DATEDIFF(s.tgl_kembali,s.tgl_berangkat)+1) AS total_hari,
           SUM(bp.besaran_realisasi * bp.jumlah_hari) AS total_realisasi
    FROM tb_anggaran a
    LEFT JOIN tb_sppd s ON a.id_anggaran=s.id_anggaran AND YEAR(s.tgl_berangkat)=$tahun AND s.status IN ('disetujui','selesai')
    LEFT JOIN tb_biaya_perjalanan bp ON s.id_sppd=bp.id_sppd
    GROUP BY a.id_anggaran
    ORDER BY a.kode_anggaran
");
?>

<div class="page-head">
  <div><h2>Rekap Realisasi Anggaran</h2><p>Penyerapan anggaran perjalanan dinas per MAK/program</p></div>
  <button onclick="window.print()" class="btn btn-primary no-print">&#128424; Cetak</button>
</div>

<div style="display:none" class="print-header">
  <div style="text-align:center;margin-bottom:20px;border-bottom:2px solid #000;padding-bottom:12px">
    <div style="font-size:14pt;font-weight:bold">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div style="font-size:12pt;font-weight:bold">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div style="font-size:10pt;margin-top:6px">Rekapitulasi Realisasi SPPD — Tahun <?=$tahun?></div>
  </div>
</div>

<div class="card no-print">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
      <div class="form-group" style="margin:0">
        <label class="form-label">Tahun Anggaran</label>
        <input class="form-control" type="number" name="tahun" value="<?=$tahun?>" min="2020" max="2030" style="width:120px">
      </div>
      <button type="submit" class="btn btn-primary" style="align-self:flex-end">Tampilkan</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Realisasi Anggaran Tahun <?=$tahun?></div></div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Kode MAK</th><th>Nama Program/Kegiatan</th><th style="text-align:right">Pagu (Rp)</th><th style="text-align:right">Realisasi (Rp)</th><th style="text-align:right">Sisa (Rp)</th><th>Jml SPPD</th><th>Total Hari</th><th>%</th></tr>
      </thead>
      <tbody>
        <?php $no=1; $total_pagu=0; $total_real=0;
        while($r=$data->fetch_assoc()):
          $real   = (float)($r['total_realisasi'] ?? 0);
          $sisa   = (float)$r['pagu'] - $real;
          $pct    = $r['pagu'] > 0 ? round($real / $r['pagu'] * 100, 1) : 0;
          $total_pagu += $r['pagu']; $total_real += $real;
        ?>
        <tr>
          <td><?=$no++?></td>
          <td><code style="font-size:11px"><?=htmlspecialchars($r['kode_anggaran'])?></code></td>
          <td><?=htmlspecialchars($r['nama_program'])?></td>
          <td style="text-align:right"><?=number_format($r['pagu'],0,',','.')?></td>
          <td style="text-align:right"><?=number_format($real,0,',','.')?></td>
          <td style="text-align:right;color:<?=$sisa<0?'var(--danger)':'var(--success)'?>"><?=number_format($sisa,0,',','.')?></td>
          <td style="text-align:center"><?=$r['jumlah_sppd']??0?></td>
          <td style="text-align:center"><?=$r['total_hari']??0?></td>
          <td>
            <div style="display:flex;align-items:center;gap:6px">
              <div style="flex:1;height:6px;background:var(--gray-100);border-radius:99px;overflow:hidden">
                <div style="width:<?=min($pct,100)?>%;height:100%;background:<?=$pct>90?'var(--danger)':($pct>70?'var(--accent)':'var(--success)')?>"></div>
              </div>
              <span style="font-size:11px;white-space:nowrap"><?=$pct?>%</span>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr style="background:var(--gray-50);font-weight:600">
          <td colspan="3" style="padding:10px 14px;text-align:right">TOTAL</td>
          <td style="padding:10px 14px;text-align:right"><?=number_format($total_pagu,0,',','.')?></td>
          <td style="padding:10px 14px;text-align:right"><?=number_format($total_real,0,',','.')?></td>
          <td style="padding:10px 14px;text-align:right;color:<?=($total_pagu-$total_real)<0?'var(--danger)':'var(--success)'?>">
            <?=number_format($total_pagu-$total_real,0,',','.')?>
          </td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<style>@media print{
  .no-print{display:none!important}
  .sidebar,.topbar,.page-head button{display:none!important}
  .main{margin:0;padding:0}
  .content{padding:0}
  .card{box-shadow:none}
  .print-header{
        display:block !important;
    }
  }</style>

<?php require_once '../../includes/footer.php'; ?>
