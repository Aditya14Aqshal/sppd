<?php
$halaman   = 'Ajukan SPPD';
$sub_judul = 'Pengajuan SPPD';
$aktif     = 'sppd_ajukan';
require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

$uid = $_SESSION['user_id'];
$stm = $conn->prepare("SELECT p.* FROM tb_pegawai p JOIN tb_pengguna u ON p.id_pegawai = u.id_pegawai WHERE u.id_pengguna = ?");
$stm->bind_param('i', $uid); $stm->execute();
$pegawai = $stm->get_result()->fetch_assoc();

$jabatan   = $conn->query("SELECT nama_jabatan,golongan,pangkat FROM tb_jabatan WHERE id_jabatan = {$pegawai['id_jabatan']}")->fetch_assoc();
$tujuans   = $conn->query("SELECT *, zona AS zona_tujuan FROM tb_tujuan ORDER BY nama_instansi");
$kegiatans = $conn->query("SELECT * FROM tb_jenis_kegiatan WHERE status='aktif' ORDER BY nama_kegiatan");
$anggarans = $conn->query("SELECT * FROM tb_anggaran WHERE status='aktif' ORDER BY nama_program");

// Ambil golongan prefix (II, III, IV) dari jabatan pegawai
$golongan_pegawai = preg_replace('/[^A-Z]/', '', $jabatan['golongan'] ?? 'II');

// Ambil data komponen biaya standar
$komponen_biaya = $conn->query("SELECT * FROM tb_komponen_biaya ORDER BY jenis_biaya, golongan, zona");
$biaya_json = [];
while ($kb = $komponen_biaya->fetch_assoc()) {
    $biaya_json[] = [
        'jenis'   => $kb['jenis_biaya'],
        'golongan'=> $kb['golongan'],
        'zona'    => $kb['zona'],
        'besaran' => (int)$kb['besaran'],
    ];
}

$error = $sukses = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kegiatan  = (int)$_POST['id_kegiatan'];
    $id_tujuan    = (int)$_POST['id_tujuan'];
    $id_anggaran  = (int)$_POST['id_anggaran'];
    $keperluan    = trim($_POST['keperluan']);
    $tgl_berangkat= $_POST['tgl_berangkat'];
    $tgl_kembali  = $_POST['tgl_kembali'];
    $kendaraan       = trim($_POST['kendaraan']);
    $trans_nama      = trim($_POST['transportasi_nama'] ?? '');
    $trans_no_tiket  = trim($_POST['transportasi_no_tiket'] ?? '');
    $trans_biaya     = preg_replace('/[^0-9]/', '', $_POST['transportasi_biaya'] ?? '');
    $submit_type  = $_POST['submit_type'] ?? 'ajukan';
    $status       = ($submit_type === 'draf') ? 'draf' : 'diajukan';

    if (!$id_kegiatan || !$id_tujuan || !$id_anggaran || !$keperluan || !$tgl_berangkat || !$tgl_kembali) {
        $error = 'Semua field bertanda * wajib diisi.';
    } elseif ($tgl_kembali < $tgl_berangkat) {
        $error = 'Tanggal kembali tidak boleh sebelum tanggal berangkat.';
    } else {
        // Cek bentrok tanggal dengan SPPD lain milik pengaju yang sama
        // Dua rentang bertumpang tindih bila: berangkat_baru <= kembali_lama DAN kembali_baru >= berangkat_lama
        $cek = $conn->prepare("
            SELECT no_sppd, keperluan, tgl_berangkat, tgl_kembali
            FROM tb_sppd
            WHERE id_pengaju = ?
              AND status NOT IN ('draf','ditolak')
              AND ? <= tgl_kembali
              AND ? >= tgl_berangkat
            ORDER BY tgl_berangkat ASC
            LIMIT 1
        ");
        $cek->bind_param('iss', $pegawai['id_pegawai'], $tgl_berangkat, $tgl_kembali);
        $cek->execute();
        $bentrok = $cek->get_result()->fetch_assoc();

        if ($bentrok) {
            $tglB = date('d/m/Y', strtotime($bentrok['tgl_berangkat']));
            $tglK = date('d/m/Y', strtotime($bentrok['tgl_kembali']));
            $no   = $bentrok['no_sppd'] ? 'No. '.$bentrok['no_sppd'] : 'belum bernomor';
            $error = "Tanggal yang Anda pilih bentrok dengan SPPD yang sudah ada: "
                   . "$tglB s.d. $tglK ($no — ".htmlspecialchars($bentrok['keperluan'])."). "
                   . "Anda tidak dapat memiliki dua perjalanan dinas pada rentang tanggal yang sama. Silakan pilih tanggal lain.";
        } else {
        // Detail transportasi (berlaku untuk semua moda)
        $trans_nama_n     = $trans_nama     !== '' ? $trans_nama     : null;
        $trans_no_tiket_n = $trans_no_tiket !== '' ? $trans_no_tiket : null;
        $trans_biaya_n    = $trans_biaya    !== '' ? (float)$trans_biaya : null;
        $stmt = $conn->prepare("INSERT INTO tb_sppd (id_pengaju,id_kegiatan,id_tujuan,id_anggaran,keperluan,tgl_berangkat,tgl_kembali,kendaraan,status,transportasi_nama,transportasi_no_tiket,transportasi_biaya) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iiiisssssssd', $pegawai['id_pegawai'], $id_kegiatan, $id_tujuan, $id_anggaran, $keperluan, $tgl_berangkat, $tgl_kembali, $kendaraan, $status, $trans_nama_n, $trans_no_tiket_n, $trans_biaya_n);
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            if ($status === 'diajukan') {
                header("Location: /sppd/pages/sppd/detail.php?id=$new_id&msg=diajukan");
            } else {
                header("Location: /sppd/pages/sppd/detail.php?id=$new_id&msg=draf");
            }
            exit;
        } else {
            $error = 'Gagal menyimpan data. Silakan coba lagi.';
        }
        }
    }
}
?>

<div class="page-head">
  <div>
    <h2>Ajukan SPPD Baru</h2>
    <p>Isi form berikut untuk mengajukan perjalanan dinas</p>
  </div>
  <a href="/sppd/pages/sppd/daftar.php" class="btn btn-secondary">&#8592; Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST">
  <!-- STEP INDICATOR -->
  <div class="steps">
    <div class="step done"><span class="step-num">1</span>Data Pengaju</div>
    <div class="step active"><span class="step-num">2</span>Detail Perjalanan</div>
    <div class="step"><span class="step-num">3</span>Anggaran</div>
    <div class="step"><span class="step-num">4</span>Konfirmasi</div>
  </div>

  <!-- DATA PENGAJU (auto-fill) -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Data Pengaju</div>
      <span class="badge badge-blue">Otomatis dari akun</span>
    </div>
    <div class="card-body">
      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label">NIP</label>
          <input class="form-control" value="<?= htmlspecialchars($pegawai['nip']) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input class="form-control" value="<?= htmlspecialchars($pegawai['nama_lengkap']) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Jabatan</label>
          <input class="form-control" value="<?= htmlspecialchars($jabatan['nama_jabatan'] ?? '-') ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Golongan / Pangkat</label>
          <input class="form-control" value="<?= htmlspecialchars(($jabatan['golongan']??'').'/'.($jabatan['pangkat']??'')) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Jenis Pegawai</label>
          <input class="form-control" value="<?= htmlspecialchars($pegawai['jenis_pegawai'] ?? 'PNS') ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label">Unit Kerja</label>
          <input class="form-control" value="<?= htmlspecialchars($pegawai['unit_kerja'] ?? '-') ?>" disabled>
        </div>
      </div>
    </div>
  </div>

  <!-- DETAIL PERJALANAN -->
  <div class="card">
    <div class="card-header"><div class="card-title">Detail Perjalanan Dinas</div></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Jenis Kegiatan <span class="req">*</span></label>
          <select class="form-control" name="id_kegiatan" required>
            <option value="">-- Pilih Jenis Kegiatan --</option>
            <?php $kegiatans->data_seek(0); while ($k = $kegiatans->fetch_assoc()): ?>
            <option value="<?= $k['id_kegiatan'] ?>" <?= (($_POST['id_kegiatan']??'')==$k['id_kegiatan'])?'selected':'' ?>>
              <?= htmlspecialchars($k['nama_kegiatan']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Tujuan / Instansi <span class="req">*</span></label>
          <select class="form-control" name="id_tujuan" required onchange="hitungEstimasi()">
            <option value="">-- Pilih Tujuan --</option>
            <?php $tujuans->data_seek(0); while ($t = $tujuans->fetch_assoc()): ?>
            <option value="<?= $t['id_tujuan'] ?>" data-zona="<?= htmlspecialchars($t['zona_tujuan']) ?>" <?= (($_POST['id_tujuan']??'')==$t['id_tujuan'])?'selected':'' ?>>
              <?= htmlspecialchars($t['nama_instansi']) ?> — <?= htmlspecialchars($t['kota']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group full">
          <label class="form-label">Keperluan / Maksud Perjalanan <span class="req">*</span></label>
          <textarea class="form-control" name="keperluan" rows="3" placeholder="Jelaskan tujuan dan keperluan perjalanan dinas..." required><?= htmlspecialchars($_POST['keperluan']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Berangkat <span class="req">*</span></label>
          <input class="form-control" type="date" name="tgl_berangkat" value="<?= $_POST['tgl_berangkat']??'' ?>" required onchange="hitungDurasi(); hitungEstimasi()">
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Kembali <span class="req">*</span></label>
          <input class="form-control" type="date" name="tgl_kembali" value="<?= $_POST['tgl_kembali']??'' ?>" required onchange="hitungDurasi(); hitungEstimasi()">
          <div class="form-hint" id="durasi-info"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Kendaraan yang Digunakan</label>
          <select class="form-control" name="kendaraan" id="select-kendaraan" onchange="toggleTransportasi()">
            <option value="">-- Pilih Kendaraan --</option>
            <option value="Kendaraan Dinas"    <?= (($_POST['kendaraan']??'')==='Kendaraan Dinas')?'selected':'' ?>>Kendaraan Dinas</option>
            <option value="Kendaraan Pribadi"  <?= (($_POST['kendaraan']??'')==='Kendaraan Pribadi')?'selected':'' ?>>Kendaraan Pribadi</option>
            <option value="Transportasi Umum"  <?= (($_POST['kendaraan']??'')==='Transportasi Umum')?'selected':'' ?>>Transportasi Umum</option>
            <option value="Pesawat Terbang"    <?= (($_POST['kendaraan']??'')==='Pesawat Terbang')?'selected':'' ?>>Pesawat Terbang</option>
            <option value="Lainnya"            <?= (($_POST['kendaraan']??'')==='Lainnya')?'selected':'' ?>>Lainnya</option>
          </select>
        </div>
        <div class="form-group full" id="grup-transportasi" style="display:none">
          <div style="border:1px dashed var(--gray-200);border-radius:var(--radius);padding:14px 16px;background:var(--gray-50)">
            <div style="font-size:13px;font-weight:600;color:var(--gray-600);margin-bottom:10px">&#128663; <span id="judul-transportasi">Detail Transportasi</span></div>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label"><span id="label-trans-nama">Nama</span> <span class="req" id="req-trans-nama">*</span></label>
                <input class="form-control" name="transportasi_nama" id="input-trans-nama" value="<?= htmlspecialchars($_POST['transportasi_nama']??'') ?>">
              </div>
              <div class="form-group">
                <label class="form-label"><span id="label-trans-tiket">No. Tiket</span> <span class="req" id="req-trans-tiket">*</span></label>
                <input class="form-control" name="transportasi_no_tiket" id="input-trans-tiket" value="<?= htmlspecialchars($_POST['transportasi_no_tiket']??'') ?>">
              </div>
              <div class="form-group">
                <label class="form-label"><span id="label-trans-biaya">Biaya (Rp)</span> <span class="req" id="req-trans-biaya">*</span></label>
                <input class="form-control" name="transportasi_biaya" id="input-trans-biaya" inputmode="numeric" value="<?= htmlspecialchars($_POST['transportasi_biaya']??'') ?>">
              </div>
            </div>
            <div class="form-hint" style="margin-top:6px" id="hint-transportasi"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ANGGARAN -->
  <div class="card">
    <div class="card-header"><div class="card-title">Sumber Anggaran</div></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group full">
          <label class="form-label">Pilih Sumber Anggaran <span class="req">*</span></label>
          <select class="form-control" name="id_anggaran" required>
            <option value="">-- Pilih Anggaran --</option>
            <?php $anggarans->data_seek(0); while ($a = $anggarans->fetch_assoc()): ?>
            <option value="<?= $a['id_anggaran'] ?>" <?= (($_POST['id_anggaran']??'')==$a['id_anggaran'])?'selected':'' ?>>
              [<?= htmlspecialchars($a['kode_anggaran']) ?>] <?= htmlspecialchars($a['nama_program']) ?> — Pagu: Rp <?= number_format($a['pagu'],0,',','.') ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- ESTIMASI BIAYA -->
  <div class="card" id="card-estimasi" style="display:none">
    <div class="card-header">
      <div class="card-title">Estimasi Biaya Perjalanan</div>
      <span class="badge badge-amber">Perhitungan Otomatis</span>
    </div>
    <div class="card-body">
      <div id="estimasi-info" style="margin-bottom:12px;font-size:13px;color:var(--gray-400)">
        Pilih tujuan dan tanggal berangkat/kembali untuk melihat estimasi.
      </div>
      <table id="tabel-estimasi" style="width:100%;border-collapse:collapse;display:none">
        <thead>
          <tr style="border-bottom:2px solid var(--gray-200)">
            <th style="text-align:left;padding:8px 12px;font-size:13px">Komponen Biaya</th>
            <th style="text-align:center;padding:8px 12px;font-size:13px">Standar/Hari</th>
            <th style="text-align:center;padding:8px 12px;font-size:13px">Hari</th>
            <th style="text-align:right;padding:8px 12px;font-size:13px">Subtotal</th>
          </tr>
        </thead>
        <tbody id="estimasi-body"></tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--gray-200);font-weight:600">
            <td colspan="3" style="text-align:right;padding:10px 12px;font-size:14px">Total Estimasi:</td>
            <td id="estimasi-total" style="text-align:right;padding:10px 12px;font-size:16px;color:var(--primary)">Rp 0</td>
          </tr>
        </tfoot>
      </table>
      <input type="hidden" name="estimasi_json" id="estimasi_json" value="">
    </div>
  </div>

  <input type="hidden" name="golongan_pegawai" value="<?= htmlspecialchars($golongan_pegawai) ?>">

  <div class="form-footer" style="background:white;border:1px solid var(--gray-200);border-radius:var(--radius);padding:16px 20px;display:flex;justify-content:flex-end;gap:10px;">
    <a href="/sppd/pages/sppd/daftar.php" class="btn btn-secondary">Batal</a>
    <button type="submit" name="submit_type" value="draf" class="btn btn-secondary">&#128190; Simpan Draf</button>
    <button type="submit" name="submit_type" value="ajukan" class="btn btn-primary">&#128640; Ajukan SPPD</button>
  </div>
</form>

<script>
const KOMPONEN_BIAYA = <?= json_encode($biaya_json) ?>;
const GOL_PEGAWAI = '<?= $golongan_pegawai ?>';

const TRANSPORTASI_CONFIG = {
  'Kendaraan Dinas': {
    judul: 'Detail Kendaraan Dinas',
    hint:  'Kendaraan milik instansi.',
    nama:  { label: 'Keterangan / Jenis Kendaraan', req: false, ph: 'Contoh: Toyota Innova' },
    tiket: { label: 'No. Polisi',                   req: false, ph: 'Contoh: KH 1234 AB' },
    biaya: { label: 'Biaya (Rp)',                   req: false, ph: 'Contoh: 300000' }
  },
  'Kendaraan Pribadi': {
    judul: 'Detail Kendaraan Pribadi',
    hint:  'Mobil/motor pribadi.',
    nama:  { label: 'Keterangan / Jenis Kendaraan', req: false, ph: 'Contoh: Honda Beat' },
    tiket: { label: 'No. Polisi',                   req: false, ph: 'Contoh: KH 5678 CD' },
    biaya: { label: 'Biaya BBM / Tol (Rp)',         req: false, ph: 'Contoh: 250000' }
  },
  'Transportasi Umum': {
    judul: 'Detail Transportasi Umum',
    hint:  'Contoh: bus/PO, angkutan umum, taksi.',
    nama:  { label: 'Nama PO / Operator',   req: true,  ph: 'Contoh: Bus Sumber Rejeki' },
    tiket: { label: 'No. Tiket',            req: false, ph: 'Kosongkan bila tidak ada' },
    biaya: { label: 'Harga Tiket (Rp)',     req: true,  ph: 'Contoh: 150000' }
  },
  'Pesawat Terbang': {
    judul: 'Detail Transportasi Udara',
    hint:  'Wajib: nama maskapai, no. tiket, dan harga tiket.',
    nama:  { label: 'Nama Maskapai',        req: true,  ph: 'Contoh: Garuda Indonesia' },
    tiket: { label: 'No. Tiket',            req: true,  ph: 'Contoh: GA-1234567890' },
    biaya: { label: 'Harga Tiket (Rp)',     req: true,  ph: 'Contoh: 1500000' }
  },
  'Lainnya': {
    judul: 'Detail Transportasi Lainnya',
    hint:  'Contoh: kapal/feri, sewa mobil, ojek online.',
    nama:  { label: 'Nama Moda Transportasi',   req: true,  ph: 'Contoh: Kapal Feri' },
    tiket: { label: 'No. Tiket / Keterangan',   req: false, ph: 'Opsional' },
    biaya: { label: 'Biaya (Rp)',               req: true,  ph: 'Contoh: 500000' }
  }
};

function toggleTransportasi() {
  const val   = document.getElementById('select-kendaraan').value;
  const grup  = document.getElementById('grup-transportasi');
  const cfg   = TRANSPORTASI_CONFIG[val];
  const aktif = !!cfg;
  grup.style.display = aktif ? 'block' : 'none';

  ['nama','tiket','biaya'].forEach(function(k){
    const inp = document.getElementById('input-trans-' + k);
    const lbl = document.getElementById('label-trans-' + k);
    const req = document.getElementById('req-trans-' + k);
    if (aktif) {
      lbl.textContent = cfg[k].label;
      inp.placeholder = cfg[k].ph || '';
      inp.required    = cfg[k].req;
      req.style.display = cfg[k].req ? 'inline' : 'none';
    } else {
      inp.required = false;
      req.style.display = 'none';
    }
  });
  document.getElementById('judul-transportasi').textContent = aktif ? cfg.judul : 'Detail Transportasi';
  document.getElementById('hint-transportasi').textContent  = aktif ? (cfg.hint || '') : '';
}
document.addEventListener('DOMContentLoaded', toggleTransportasi);

function hitungDurasi() {
  const b = document.querySelector('[name=tgl_berangkat]').value;
  const k = document.querySelector('[name=tgl_kembali]').value;
  if (b && k) {
    const diff = (new Date(k) - new Date(b)) / 86400000 + 1;
    const el = document.getElementById('durasi-info');
    if (diff < 1) { el.textContent = '⚠ Tanggal kembali tidak valid'; el.style.color = 'var(--danger)'; }
    else { el.textContent = '📅 Durasi perjalanan: ' + diff + ' hari'; el.style.color = 'var(--success)'; }
  }
}

function hitungEstimasi() {
  const selTujuan = document.querySelector('[name=id_tujuan]');
  const opt = selTujuan.options[selTujuan.selectedIndex];
  const zona = opt.getAttribute('data-zona');
  const tglB = document.querySelector('[name=tgl_berangkat]').value;
  const tglK = document.querySelector('[name=tgl_kembali]').value;
  const hari = (tglB && tglK) ? Math.max(1, (new Date(tglK) - new Date(tglB)) / 86400000 + 1) : 0;

  const card = document.getElementById('card-estimasi');
  const info = document.getElementById('estimasi-info');
  const tabel = document.getElementById('tabel-estimasi');
  const tbody = document.getElementById('estimasi-body');
  const totalEl = document.getElementById('estimasi-total');
  const jsonEl = document.getElementById('estimasi_json');

  if (!zona || !hari) {
    card.style.display = 'none';
    tabel.style.display = 'none';
    info.style.display = 'block';
    info.textContent = 'Pilih tujuan dan tanggal berangkat/kembali untuk melihat estimasi.';
    jsonEl.value = '';
    return;
  }

  card.style.display = 'block';
  info.style.display = 'none';
  tabel.style.display = 'table';

  const golShort = GOL_PEGAWAI;
  const items = [];
  let total = 0;

  const jenisLabel = {uang_harian:'Uang Harian', transport:'Transport', penginapan:'Penginapan'};

  // Filter komponen biaya sesuai golongan + zona pegawai
  KOMPONEN_BIAYA.forEach(kb => {
    if (kb.golongan !== golShort) return;
    if (kb.zona !== zona) return;

    let subtotal = 0;
    let h = 0;
    if (kb.jenis === 'uang_harian') {
      h = hari;
      subtotal = kb.besaran * hari;
    } else {
      h = 1;
      subtotal = kb.besaran;
    }

    items.push({jenis: kb.jenis, label: jenisLabel[kb.jenis] || kb.jenis, standar: kb.besaran, hari: h, subtotal: subtotal});
    total += subtotal;
  });

  tbody.innerHTML = '';
  if (items.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:16px;color:var(--gray-400);font-size:13px">Tidak ada komponen biaya untuk golongan ' + golShort + ' dan zona ' + zona + '</td></tr>';
    totalEl.textContent = 'Rp 0';
    jsonEl.value = '';
    return;
  }

  items.forEach(it => {
    const tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid var(--gray-100)';
    tr.innerHTML =
      '<td style="padding:8px 12px;font-size:13px">' + it.label + '</td>' +
      '<td style="text-align:center;padding:8px 12px;font-size:13px">Rp ' + it.standar.toLocaleString('id-ID') + '</td>' +
      '<td style="text-align:center;padding:8px 12px;font-size:13px">' + it.hari + '</td>' +
      '<td style="text-align:right;padding:8px 12px;font-size:13px;font-weight:500">Rp ' + it.subtotal.toLocaleString('id-ID') + '</td>';
    tbody.appendChild(tr);
  });

  totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
  jsonEl.value = JSON.stringify({golongan: golShort, zona: zona, hari: hari, items: items, total: total});
}
</script>

<?php require_once '../../includes/footer.php'; ?>
