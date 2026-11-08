<?php
$halaman='Rekap per Pegawai'; $sub_judul='Laporan / Rekap Pegawai'; $aktif='lap_pegawai';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$tahun   = $_GET['tahun']     ?? date('Y');
$id_peg  = (int)($_GET['id_pegawai'] ?? 0);
$pegawais= $conn->query("SELECT id_pegawai,nama_lengkap,nip FROM tb_pegawai ORDER BY nama_lengkap");

$result = null; $pegawai_info = null;
if ($id_peg) {
    $pegawai_info = $conn->query("SELECT p.*,j.nama_jabatan,j.golongan FROM tb_pegawai p JOIN tb_jabatan j ON p.id_jabatan=j.id_jabatan WHERE p.id_pegawai=$id_peg")->fetch_assoc();
    $sql = "SELECT s.*,t.nama_instansi,t.kota,jk.nama_kegiatan,a.nama_program,
                   DATEDIFF(s.tgl_kembali,s.tgl_berangkat)+1 AS lama
            FROM tb_sppd s
            JOIN tb_tujuan t ON s.id_tujuan=t.id_tujuan
            JOIN tb_jenis_kegiatan jk ON s.id_kegiatan=jk.id_kegiatan
            JOIN tb_anggaran a ON s.id_anggaran=a.id_anggaran
            WHERE s.id_pengaju=$id_peg AND YEAR(s.tgl_berangkat)=$tahun
            ORDER BY s.tgl_berangkat";
    $result = $conn->query($sql);
}
$status_badge=['draf'=>['gray','Draf'],'diajukan'=>['blue','Diajukan'],'diverifikasi'=>['amber','Diverifikasi'],'disetujui'=>['green','Disetujui'],'ditolak'=>['red','Ditolak'],'selesai'=>['green','Selesai']];
?>

<div class="page-head">
  <div><h2>Rekap SPPD per Pegawai</h2><p>Riwayat perjalanan dinas per individu pegawai</p></div>
  <?php if($result): ?><button onclick="window.print()" class="btn btn-primary no-print">&#128424; Cetak</button><?php endif; ?>
</div>

<div class="card no-print">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div class="form-group" style="flex:2;margin:0">
        <label class="form-label">Pilih Pegawai</label>
        <select class="form-control" name="id_pegawai">
          <option value="">-- Pilih Pegawai --</option>
          <?php while($pg=$pegawais->fetch_assoc()): ?>
          <option value="<?=$pg['id_pegawai']?>" <?=$id_peg==$pg['id_pegawai']?'selected':''?>><?=htmlspecialchars($pg['nama_lengkap'])?> (<?=htmlspecialchars($pg['nip'])?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Tahun</label>
        <input class="form-control" type="number" name="tahun" value="<?=$tahun?>" min="2020" max="2030" style="width:100px">
      </div>
      <button type="submit" class="btn btn-primary" style="align-self:flex-end">Tampilkan</button>
    </form>
  </div>
</div>

<?php if($result && $pegawai_info): ?>
<div class="card">
  <div class="card-header"><div class="card-title">Info Pegawai</div></div>
  <div class="card-body">
    <div class="form-grid-3">
      <div class="form-group"><label class="form-label">NIP</label><input class="form-control" value="<?=htmlspecialchars($pegawai_info['nip'])?>" disabled></div>
      <div class="form-group"><label class="form-label">Nama</label><input class="form-control" value="<?=htmlspecialchars($pegawai_info['nama_lengkap'])?>" disabled></div>
      <div class="form-group"><label class="form-label">Jabatan</label><input class="form-control" value="<?=htmlspecialchars($pegawai_info['nama_jabatan'])?> (<?=htmlspecialchars($pegawai_info['golongan'])?>)" disabled></div>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-header">
    <div class="card-title">Riwayat SPPD Tahun <?=$tahun?> (<?=$result->num_rows?> perjalanan)</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>No. SPPD</th><th>Kegiatan</th><th>Tujuan</th><th>Tgl Berangkat</th><th>Tgl Kembali</th><th>Lama</th><th>Status</th></tr></thead>
      <tbody>
        <?php $no=1; $total_hari=0; while($r=$result->fetch_assoc()):
          [$bc,$bl]=$status_badge[$r['status']]??['gray','—'];
          $total_hari+=$r['lama'];
        ?>
        <tr>
          <td><?=$no++?></td>
          <td><?=$r['no_sppd']?htmlspecialchars($r['no_sppd']):'<em style="color:#9AA3B2">—</em>'?></td>
          <td><?=htmlspecialchars($r['nama_kegiatan'])?></td>
          <td><?=htmlspecialchars($r['nama_instansi'].', '.$r['kota'])?></td>
          <td><?=date('d/m/Y',strtotime($r['tgl_berangkat']))?></td>
          <td><?=date('d/m/Y',strtotime($r['tgl_kembali']))?></td>
          <td style="text-align:center"><?=$r['lama']?> hr</td>
          <td><span class="badge badge-<?=$bc?>"><?=$bl?></span></td>
        </tr>
        <?php endwhile; if($result->num_rows===0): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--gray-400);padding:24px">Tidak ada data perjalanan dinas untuk tahun ini</td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr style="background:var(--gray-50);font-weight:600">
          <td colspan="6" style="text-align:right;padding:10px 14px">Total Hari Perjalanan:</td>
          <td style="text-align:center;padding:10px 14px"><?=$total_hari?> hr</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php elseif(!$id_peg): ?>
<div class="card"><div class="card-body" style="text-align:center;color:var(--gray-400);padding:40px">
  <p style="font-size:15px;margin-bottom:8px">Pilih pegawai terlebih dahulu</p>
  <p style="font-size:13px">Gunakan filter di atas untuk memilih pegawai dan tahun</p>
</div></div>
<?php endif; ?>

<style>@media print{.no-print{display:none!important}.sidebar,.topbar,.page-head button{display:none!important}.main{margin:0;padding:0}.content{padding:0}.card{box-shadow:none}}</style>

<?php require_once '../../includes/footer.php'; ?>
