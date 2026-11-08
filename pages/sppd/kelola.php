<?php
$halaman='Kelola SPPD'; $sub_judul='SPPD / Anggota & Laporan'; $aktif='sppd_list';
require_once '../../config/koneksi.php'; require_once '../../includes/header.php';

$id   = (int)($_GET['id'] ?? 0);
$role = $_SESSION['role'];
$tab  = $_GET['tab'] ?? 'anggota';

// Cek SPPD ada
$sppd = $conn->query("SELECT s.*,p.nama_lengkap FROM tb_sppd s JOIN tb_pegawai p ON s.id_pengaju=p.id_pegawai WHERE s.id_sppd=$id")->fetch_assoc();
if (!$sppd) { echo '<div class="alert alert-danger">Data SPPD tidak ditemukan.</div>'; require_once '../../includes/footer.php'; exit; }

$msg=$err='';

/* ── ANGGOTA ── */
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='tambah_anggota') {
    $id_peg = (int)$_POST['id_pegawai'];
    $peran  = trim($_POST['peran']);
    $cek    = $conn->query("SELECT id_anggota FROM tb_sppd_anggota WHERE id_sppd=$id AND id_pegawai=$id_peg")->num_rows;
    if ($cek) { $err='Pegawai sudah ada dalam rombongan.'; }
    else {
        $st=$conn->prepare("INSERT INTO tb_sppd_anggota (id_sppd,id_pegawai,peran) VALUES (?,?,?)");
        $st->bind_param('iis',$id,$id_peg,$peran);
        $st->execute() ? $msg='Anggota berhasil ditambahkan.' : $err='Gagal: '.$conn->error;
    }
}
if (isset($_GET['hapus_anggota'])) {
    $conn->query("DELETE FROM tb_sppd_anggota WHERE id_anggota=".(int)$_GET['hapus_anggota']." AND id_sppd=$id");
    $msg='Anggota dihapus dari rombongan.';
}

/* ── LAPORAN HASIL ── */
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='simpan_laporan') {
    $tgl_b  = $_POST['tgl_aktual_berangkat'];
    $tgl_k  = $_POST['tgl_aktual_kembali'];
    $uraian = trim($_POST['uraian_kegiatan']);
    $kesim  = trim($_POST['kesimpulan']);
    $file   = '';
    if (!$uraian) { $err='Uraian kegiatan wajib diisi.'; }
    else {
        // Upload bukti
        if (!empty($_FILES['bukti_file']['name'])) {
            $ext = pathinfo($_FILES['bukti_file']['name'], PATHINFO_EXTENSION);
            $fname = 'bukti_'.$id.'_'.time().'.'.$ext;
            $dest  = '../../assets/uploads/'.$fname;
            if (move_uploaded_file($_FILES['bukti_file']['tmp_name'], $dest)) $file=$fname;
        }
        $ada = $conn->query("SELECT id_laporan FROM tb_laporan_dinas WHERE id_sppd=$id")->num_rows;
        if ($ada) {
            $st=$conn->prepare("UPDATE tb_laporan_dinas SET tgl_aktual_berangkat=?,tgl_aktual_kembali=?,uraian_kegiatan=?,kesimpulan=?,bukti_file=COALESCE(NULLIF(?,''),bukti_file) WHERE id_sppd=?");
            $st->bind_param('sssssi',$tgl_b,$tgl_k,$uraian,$kesim,$file,$id);
        } else {
            $st=$conn->prepare("INSERT INTO tb_laporan_dinas (id_sppd,tgl_aktual_berangkat,tgl_aktual_kembali,uraian_kegiatan,kesimpulan,bukti_file) VALUES (?,?,?,?,?,?)");
            $st->bind_param('isssss',$id,$tgl_b,$tgl_k,$uraian,$kesim,$file);
        }
        if ($st->execute()) {
            $conn->query("UPDATE tb_sppd SET status='selesai' WHERE id_sppd=$id");
            $msg='Laporan berhasil disimpan. Status SPPD diubah ke Selesai.';
        } else { $err='Gagal: '.$conn->error; }
    }
}

/* ── BIAYA REALISASI ── */
if ($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='simpan_biaya') {
    $id_komp = (int)$_POST['id_komponen'];
    $hari    = (int)$_POST['jumlah_hari'];
    $bes     = (float)str_replace(['.','Rp',' '],'',$_POST['besaran_realisasi']);
    $file    = '';
    if (!$id_komp||!$hari||!$bes) { $err='Semua field biaya wajib diisi.'; }
    else {
        if (!empty($_FILES['kwitansi_file']['name'])) {
            $ext   = pathinfo($_FILES['kwitansi_file']['name'], PATHINFO_EXTENSION);
            $fname = 'kwi_'.$id.'_'.time().'.'.$ext;
            move_uploaded_file($_FILES['kwitansi_file']['tmp_name'], '../../assets/uploads/'.$fname);
            $file=$fname;
        }
        $st=$conn->prepare("INSERT INTO tb_biaya_perjalanan (id_sppd,id_komponen,jumlah_hari,besaran_realisasi,kwitansi_file) VALUES (?,?,?,?,?)");
        $st->bind_param('iiids',$id,$id_komp,$hari,$bes,$file);
        $st->execute() ? $msg='Biaya berhasil ditambahkan.' : $err='Gagal: '.$conn->error;
    }
}
if (isset($_GET['hapus_biaya'])) {
    $conn->query("DELETE FROM tb_biaya_perjalanan WHERE id_biaya=".(int)$_GET['hapus_biaya']." AND id_sppd=$id");
    $msg='Item biaya dihapus.';
}

// Data pendukung
$pegawais   = $conn->query("SELECT id_pegawai,nama_lengkap,nip FROM tb_pegawai WHERE status='aktif' ORDER BY nama_lengkap");
$anggota    = $conn->query("SELECT sa.*,pg.nama_lengkap,pg.nip,jb.nama_jabatan FROM tb_sppd_anggota sa JOIN tb_pegawai pg ON sa.id_pegawai=pg.id_pegawai JOIN tb_jabatan jb ON pg.id_jabatan=jb.id_jabatan WHERE sa.id_sppd=$id");
$laporan    = $conn->query("SELECT * FROM tb_laporan_dinas WHERE id_sppd=$id")->fetch_assoc();
$komponen   = $conn->query("SELECT * FROM tb_komponen_biaya ORDER BY jenis_biaya,golongan");
$biaya_list = $conn->query("SELECT bp.*,kb.jenis_biaya,kb.golongan,kb.zona FROM tb_biaya_perjalanan bp JOIN tb_komponen_biaya kb ON bp.id_komponen=kb.id_komponen WHERE bp.id_sppd=$id");
$total_biaya= $conn->query("SELECT SUM(besaran_realisasi*jumlah_hari) total FROM tb_biaya_perjalanan WHERE id_sppd=$id")->fetch_assoc()['total']??0;
$zona_label =['lokal'=>'Lokal','luar_kota'=>'Luar Kota','luar_provinsi'=>'Luar Provinsi'];
$jenis_label=['uang_harian'=>'Uang Harian','transport'=>'Transport','penginapan'=>'Penginapan'];
?>

<div class="page-head">
  <div>
    <h2>Kelola SPPD — <?=htmlspecialchars($sppd['nama_lengkap'])?></h2>
    <p><?=$sppd['no_sppd']?'No. '.htmlspecialchars($sppd['no_sppd']):'Belum diterbitkan'?> &nbsp;|&nbsp; Status: <strong><?=ucfirst($sppd['status'])?></strong></p>
  </div>
  <a href="/sppd/pages/sppd/detail.php?id=<?=$id?>" class="btn btn-secondary">&#8592; Kembali ke Detail</a>
</div>

<?php if($msg):?><div class="alert alert-success"><?=$msg?></div><?php endif;?>
<?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>

<!-- TAB -->
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid var(--gray-200)">
  <?php foreach(['anggota'=>'&#128101; Anggota Rombongan','laporan'=>'&#128203; Laporan Hasil Dinas','biaya'=>'&#128181; Rincian Biaya'] as $t=>$l): ?>
  <a href="?id=<?=$id?>&tab=<?=$t?>" style="padding:10px 20px;font-size:13px;font-weight:<?=$tab===$t?'600':'400'?>;color:<?=$tab===$t?'var(--primary)':'var(--gray-600)'?>;border-bottom:<?=$tab===$t?'2px solid var(--primary)':'2px solid transparent'?>;margin-bottom:-2px;text-decoration:none"><?=$l?></a>
  <?php endforeach; ?>
</div>

<?php if ($tab==='anggota'): ?>
<!-- ══ TAB ANGGOTA ══ -->
<div class="card">
  <div class="card-header"><div class="card-title">Tambah Anggota Rombongan</div></div>
  <form method="POST">
    <input type="hidden" name="action" value="tambah_anggota">
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Pilih Pegawai <span class="req">*</span></label>
          <select class="form-control" name="id_pegawai" required>
            <option value="">-- Pilih Pegawai --</option>
            <?php $pegawais->data_seek(0); while($pg=$pegawais->fetch_assoc()): ?>
            <option value="<?=$pg['id_pegawai']?>"><?=htmlspecialchars($pg['nama_lengkap'])?> (<?=htmlspecialchars($pg['nip'])?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Peran dalam Kegiatan</label>
          <input class="form-control" name="peran" placeholder="Contoh: Peserta, Narasumber, Moderator">
        </div>
      </div>
    </div>
    <div class="form-footer"><button type="submit" class="btn btn-primary">+ Tambah Anggota</button></div>
  </form>
</div>
<div class="card">
  <div class="card-header"><div class="card-title">Daftar Anggota (<?=$anggota->num_rows?> orang)</div></div>
  <div class="table-wrap"><table>
    <thead><tr><th>#</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Peran</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php $no=1; while($a=$anggota->fetch_assoc()): ?>
      <tr><td><?=$no++?></td><td><?=htmlspecialchars($a['nip'])?></td><td><?=htmlspecialchars($a['nama_lengkap'])?></td><td><?=htmlspecialchars($a['nama_jabatan'])?></td><td><?=htmlspecialchars($a['peran']??'-')?></td>
      <td><a href="?id=<?=$id?>&tab=anggota&hapus_anggota=<?=$a['id_anggota']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus anggota ini?')">Hapus</a></td></tr>
      <?php endwhile; if($anggota->num_rows===0): ?>
      <tr><td colspan="6" style="text-align:center;color:var(--gray-400);padding:24px">Belum ada anggota ditambahkan</td></tr>
      <?php endif; ?>
    </tbody>
  </table></div>
</div>

<?php elseif($tab==='laporan'): ?>
<!-- ══ TAB LAPORAN HASIL ══ -->
<div class="card">
  <div class="card-header">
    <div class="card-title">Form Laporan Hasil Perjalanan Dinas</div>
    <?php if($laporan):?><span class="badge badge-green">Sudah diisi</span><?php endif;?>
  </div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="simpan_laporan">
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Tanggal Berangkat Aktual <span class="req">*</span></label>
          <input class="form-control" type="date" name="tgl_aktual_berangkat" value="<?=$laporan['tgl_aktual_berangkat']??$sppd['tgl_berangkat']?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Kembali Aktual <span class="req">*</span></label>
          <input class="form-control" type="date" name="tgl_aktual_kembali" value="<?=$laporan['tgl_aktual_kembali']??$sppd['tgl_kembali']?>" required>
        </div>
        <div class="form-group full">
          <label class="form-label">Uraian Hasil Kegiatan <span class="req">*</span></label>
          <textarea class="form-control" name="uraian_kegiatan" rows="5" placeholder="Jelaskan secara rinci kegiatan yang dilaksanakan selama perjalanan dinas..." required><?=htmlspecialchars($laporan['uraian_kegiatan']??'')?></textarea>
        </div>
        <div class="form-group full">
          <label class="form-label">Kesimpulan & Tindak Lanjut</label>
          <textarea class="form-control" name="kesimpulan" rows="3" placeholder="Tuliskan kesimpulan dan rencana tindak lanjut..."><?=htmlspecialchars($laporan['kesimpulan']??'')?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Upload Bukti Kehadiran (Foto/Scan)</label>
          <input class="form-control" type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf">
          <div class="form-hint">Format: JPG, PNG, PDF. Maks. 2MB</div>
          <?php if($laporan && $laporan['bukti_file']):?>
          <div style="margin-top:6px;font-size:12px;color:var(--success)">&#10003; File sudah diupload: <?=htmlspecialchars($laporan['bukti_file'])?></div>
          <?php endif;?>
        </div>
      </div>
    </div>
    <div class="form-footer">
      <div style="font-size:12px;color:var(--gray-400)">&#9888; Setelah menyimpan laporan, status SPPD akan berubah menjadi <strong>Selesai</strong></div>
      <button type="submit" class="btn btn-primary">&#128190; Simpan Laporan</button>
    </div>
  </form>
</div>

<?php elseif($tab==='biaya'): ?>
<!-- ══ TAB BIAYA REALISASI ══ -->
<div class="card">
  <div class="card-header"><div class="card-title">Tambah Rincian Biaya Realisasi</div></div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="simpan_biaya">
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Komponen Biaya <span class="req">*</span></label>
          <select class="form-control" name="id_komponen" required>
            <option value="">-- Pilih Komponen --</option>
            <?php $komponen->data_seek(0); while($k=$komponen->fetch_assoc()): ?>
            <option value="<?=$k['id_komponen']?>">
              <?=$jenis_label[$k['jenis_biaya']]?> — Gol.<?=$k['golongan']?> — <?=$zona_label[$k['zona']]?> (Rp <?=number_format($k['besaran'],0,',','.')?>)
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Jumlah Hari <span class="req">*</span></label>
          <input class="form-control" type="number" name="jumlah_hari" min="1" value="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Besaran Realisasi (Rp) <span class="req">*</span></label>
          <input class="form-control" name="besaran_realisasi" placeholder="Masukkan angka, contoh: 350000" required>
        </div>
        <div class="form-group">
          <label class="form-label">Upload Kwitansi / Bukti Bayar</label>
          <input class="form-control" type="file" name="kwitansi_file" accept=".jpg,.jpeg,.png,.pdf">
          <div class="form-hint">Format: JPG, PNG, PDF. Maks. 2MB</div>
        </div>
      </div>
    </div>
    <div class="form-footer"><button type="submit" class="btn btn-primary">+ Tambah Biaya</button></div>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">Rincian Biaya (<?=$biaya_list->num_rows?> item)</div>
    <?php if($role !== 'pegawai'):?>
    <a href="/sppd/pages/laporan/cetak_biaya.php?id=<?=$id?>" class="btn btn-secondary btn-sm" target="_blank">&#128424; Cetak Rincian</a>
    <?php endif;?>
  </div>
  <div class="table-wrap"><table>
    <thead><tr><th>#</th><th>Komponen</th><th>Golongan</th><th>Zona</th><th style="text-align:center">Hari</th><th style="text-align:right">Besaran (Rp)</th><th style="text-align:right">Total (Rp)</th><th>Kwitansi</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php $no=1; while($b=$biaya_list->fetch_assoc()): $tot=$b['besaran_realisasi']*$b['jumlah_hari']; ?>
      <tr>
        <td><?=$no++?></td>
        <td><span class="badge badge-blue"><?=$jenis_label[$b['jenis_biaya']]??$b['jenis_biaya']?></span></td>
        <td>Gol. <?=htmlspecialchars($b['golongan'])?></td>
        <td><?=$zona_label[$b['zona']]??$b['zona']?></td>
        <td style="text-align:center"><?=$b['jumlah_hari']?></td>
        <td style="text-align:right"><?=number_format($b['besaran_realisasi'],0,',','.')?></td>
        <td style="text-align:right;font-weight:600"><?=number_format($tot,0,',','.')?></td>
        <td><?=$b['kwitansi_file']?'<span class="badge badge-green">Ada</span>':'<span class="badge badge-gray">—</span>'?></td>
        <td><a href="?id=<?=$id?>&tab=biaya&hapus_biaya=<?=$b['id_biaya']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item ini?')">Hapus</a></td>
      </tr>
      <?php endwhile; if($biaya_list->num_rows===0): ?>
      <tr><td colspan="9" style="text-align:center;color:var(--gray-400);padding:24px">Belum ada rincian biaya</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr style="background:var(--gray-50);font-weight:700">
        <td colspan="6" style="padding:12px 14px;text-align:right">TOTAL BIAYA REALISASI:</td>
        <td style="padding:12px 14px;text-align:right;color:var(--primary);font-size:15px">Rp <?=number_format($total_biaya,0,',','.')?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
  </table></div>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
