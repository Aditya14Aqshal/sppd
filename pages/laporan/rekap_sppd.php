<?php
$halaman='Rekap SPPD'; $sub_judul='Laporan / Rekap SPPD'; $aktif='lap_rekap';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$bulan  = $_GET['bulan']  ?? '0';
$tahun  = $_GET['tahun']  ?? date('Y');
$status = $_GET['status'] ?? '';

$where = ["YEAR(s.tgl_berangkat) = $tahun"];
if ($bulan !== '0') $where[] = "MONTH(s.tgl_berangkat) = $bulan";
if ($status) $where[] = "s.status = '".$conn->real_escape_string($status)."'";

$sql = "SELECT s.*,p.nama_lengkap,p.nip,t.nama_instansi,t.kota,jk.nama_kegiatan,a.kode_anggaran
        FROM tb_sppd s
        JOIN tb_pegawai p ON s.id_pengaju=p.id_pegawai
        JOIN tb_tujuan t ON s.id_tujuan=t.id_tujuan
        JOIN tb_jenis_kegiatan jk ON s.id_kegiatan=jk.id_kegiatan
        JOIN tb_anggaran a ON s.id_anggaran=a.id_anggaran
        WHERE ".implode(' AND ',$where)."
        ORDER BY s.tgl_pengajuan";
$result = $conn->query($sql);
$total  = $result->num_rows;

$status_badge=['draf'=>['gray','Draf'],'diajukan'=>['blue','Diajukan'],'diverifikasi'=>['amber','Diverifikasi'],'disetujui'=>['green','Disetujui'],'ditolak'=>['red','Ditolak'],'selesai'=>['green','Selesai']];
$bln_list=['0'=>'Semua Bulan','1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
?>

<div class="page-head">
  <div><h2>Rekap SPPD</h2><p>Laporan rekapitulasi seluruh SPPD berdasarkan periode</p></div>
  <button onclick="window.print()" class="btn btn-primary">&#128424; Cetak Laporan</button>
</div>

<!-- FILTER -->
<div class="card no-print">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="margin:0">
        <label class="form-label">Bulan</label>
        <select class="form-control" name="bulan">
          <?php foreach($bln_list as $v=>$l): ?>
          <option value="<?=$v?>" <?=$bulan==$v?'selected':''?>><?=$l?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Tahun</label>
        <input class="form-control" type="number" name="tahun" value="<?=$tahun?>" min="2020" max="2030" style="width:100px">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Status</label>
        <select class="form-control" name="status">
          <option value="">Semua Status</option>
          <?php foreach(['diajukan','diverifikasi','disetujui','ditolak','selesai'] as $s2): ?>
          <option value="<?=$s2?>" <?=$status===$s2?'selected':''?>><?=ucfirst($s2)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="align-self:flex-end">Filter</button>
      <a href="/sppd/pages/laporan/rekap_sppd.php" class="btn btn-secondary" style="align-self:flex-end">Reset</a>
    </form>
  </div>
</div>

<!-- HEADER CETAK -->
<div style="display:none" class="print-header">
  <div style="text-align:center;margin-bottom:20px;border-bottom:2px solid #000;padding-bottom:12px">
    <div style="font-size:14pt;font-weight:bold">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div style="font-size:12pt;font-weight:bold">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div style="font-size:10pt;margin-top:6px">Rekapitulasi SPPD — <?=$bln_list[$bulan]?> <?=$tahun?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">Rekap SPPD — <?=$bln_list[$bulan]?> <?=$tahun?> (<?=$total?> record)</div>
    <span class="badge badge-blue"><?=$tahun?></span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>No. SPPD</th><th>Nama Pegawai</th><th>NIP</th><th>Kegiatan</th><th>Tujuan</th><th>Tgl Berangkat</th><th>Tgl Kembali</th><th>Lama</th><th>Transportasi</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php $no=1; $result->data_seek(0); while($r=$result->fetch_assoc()):
          [$bc,$bl]=$status_badge[$r['status']]??['gray','—'];
          $dur=(strtotime($r['tgl_kembali'])-strtotime($r['tgl_berangkat']))/86400+1;
        ?>
        <tr>
          <td><?=$no++?></td>
          <td><?=$r['no_sppd']?htmlspecialchars($r['no_sppd']):'<em style="color:#9AA3B2">—</em>'?></td>
          <td><?=htmlspecialchars($r['nama_lengkap'])?></td>
          <td style="font-size:11px"><?=htmlspecialchars($r['nip'])?></td>
          <td><?=htmlspecialchars($r['nama_kegiatan'])?></td>
          <td><?=htmlspecialchars($r['nama_instansi'])?></td>
          <td><?=date('d/m/Y',strtotime($r['tgl_berangkat']))?></td>
          <td><?=date('d/m/Y',strtotime($r['tgl_kembali']))?></td>
          <td style="text-align:center"><?=$dur?> hr</td>
          <td>
            <?=htmlspecialchars($r['kendaraan'] ?? '—')?>
            <?php if(($r['transportasi_nama'] ?? '') !== '' || ($r['transportasi_no_tiket'] ?? '') !== '' || ($r['transportasi_biaya'] ?? '') !== ''): ?>
            <div style="font-size:11px;color:var(--gray-600);margin-top:2px">
              <?=htmlspecialchars($r['transportasi_nama'] ?? '-')?>
              <?php if(($r['transportasi_no_tiket'] ?? '') !== ''):?>&nbsp;&middot;&nbsp;No. <?=htmlspecialchars($r['transportasi_no_tiket'])?><?php endif;?>
              <?php if($r['transportasi_biaya']):?>&nbsp;&middot;&nbsp;Rp <?=number_format($r['transportasi_biaya'],0,',','.')?><?php endif;?>
            </div>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-<?=$bc?>"><?=$bl?></span></td>
        </tr>
        <?php endwhile; ?>
        <?php if($total===0): ?>
        <tr><td colspan="11" style="text-align:center;color:var(--gray-400);padding:28px">Tidak ada data untuk periode ini</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:12px 20px;border-top:1px solid var(--gray-100);font-size:13px;color:var(--gray-600)">
    Total: <strong><?=$total?></strong> SPPD &nbsp;|&nbsp;
    Disetujui: <strong><?=$conn->query("SELECT COUNT(*) c FROM tb_sppd WHERE status='disetujui' AND YEAR(tgl_berangkat)=$tahun")->fetch_assoc()['c']?></strong> &nbsp;|&nbsp;
    Selesai: <strong><?=$conn->query("SELECT COUNT(*) c FROM tb_sppd WHERE status='selesai' AND YEAR(tgl_berangkat)=$tahun")->fetch_assoc()['c']?></strong>
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
  .badge { border: 1px solid #ccc; }
}
</style>

<?php require_once '../../includes/footer.php'; ?>
