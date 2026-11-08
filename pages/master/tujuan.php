<?php
$halaman   = 'Master Tujuan';
$sub_judul = 'Master Data / Tujuan';
$aktif     = 'm_tujuan';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

$msg = '';
$err = '';

if (isset($_GET['hapus'])) {
    $conn->query("DELETE FROM tb_tujuan WHERE id_tujuan=" . (int) $_GET['hapus']);
    $msg = 'Dihapus.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id    = (int) ($_POST['id'] ?? 0);
    $nama  = trim($_POST['nama_instansi']);
    $alamat = trim($_POST['alamat']);
    $kota  = trim($_POST['kota']);
    $prov  = trim($_POST['provinsi']);
    $zona  = $_POST['zona'];

    if (!$nama || !$kota) {
        $err = 'Nama instansi dan kota wajib diisi.';
    } else {

        if ($id) {
            $st = $conn->prepare("
                UPDATE tb_tujuan
                SET
                    nama_instansi = ?,
                    alamat = ?,
                    kota = ?,
                    provinsi = ?,
                    zona = ?
                WHERE id_tujuan = ?
            ");

            $st->bind_param(
                'sssssi',
                $nama,
                $alamat,
                $kota,
                $prov,
                $zona,
                $id
            );

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_tujuan
                    (nama_instansi, alamat, kota, provinsi, zona)
                VALUES
                    (?, ?, ?, ?, ?)
            ");

            $st->bind_param(
                'sssss',
                $nama,
                $alamat,
                $kota,
                $prov,
                $zona
            );
        }

        $st->execute()
            ? $msg = 'Disimpan.'
            : $err = 'Gagal.';
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $edit = $conn
        ->query("SELECT * FROM tb_tujuan WHERE id_tujuan=" . (int) $_GET['edit'])
        ->fetch_assoc();
}

$data = $conn->query("SELECT * FROM tb_tujuan ORDER BY nama_instansi");
?>

<div class="page-head">
    <div>
        <h2>Master Data Tujuan / Instansi</h2>
        <p>Daftar instansi tujuan perjalanan dinas</p>
    </div>
</div>

<?php if ($msg) : ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if ($err) : ?>
    <div class="alert alert-danger"><?= $err ?></div>
<?php endif; ?>

<div class="card">

    <div class="card-header">
        <div class="card-title">
            <?= $edit ? 'Edit' : 'Tambah' ?> Tujuan
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id"
                value="<?= $edit['id_tujuan'] ?? 0 ?>"
            >

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">
                        Nama Instansi <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nama_instansi"
                        value="<?= htmlspecialchars($edit['nama_instansi'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Kota <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="kota"
                        value="<?= htmlspecialchars($edit['kota'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Provinsi</label>

                    <input
                        class="form-control"
                        name="provinsi"
                        value="<?= htmlspecialchars($edit['provinsi'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Zona</label>

                    <select class="form-control" name="zona">

                        <?php foreach (
                            [
                                'lokal' => 'Lokal',
                                'luar_kota' => 'Luar Kota',
                                'luar_provinsi' => 'Luar Provinsi'
                            ] as $v => $l
                        ) : ?>

                            <option
                                value="<?= $v ?>"
                                <?= ($edit['zona'] ?? 'lokal') === $v ? 'selected' : '' ?>
                            >
                                <?= $l ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group full">
                    <label class="form-label">Alamat</label>

                    <textarea
                        class="form-control"
                        name="alamat"
                        rows="2"
                    ><?= htmlspecialchars($edit['alamat'] ?? '') ?></textarea>
                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>
                <a href="/sppd/pages/master/tujuan.php" class="btn btn-secondary">
                    Batal
                </a>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">
                Simpan
            </button>

        </div>

    </form>

</div>

<div class="card">

    <div class="card-header">
        <div class="card-title">
            Daftar Tujuan
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Instansi</th>
                    <th>Kota</th>
                    <th>Provinsi</th>
                    <th>Zona</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $no = 1;

                while ($r = $data->fetch_assoc()) :

                    $zona_label = [
                        'lokal' => 'Lokal',
                        'luar_kota' => 'Luar Kota',
                        'luar_provinsi' => 'Luar Provinsi'
                    ];
                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= htmlspecialchars($r['nama_instansi']) ?></td>

                        <td><?= htmlspecialchars($r['kota']) ?></td>

                        <td><?= htmlspecialchars($r['provinsi'] ?? '-') ?></td>

                        <td>
                            <span class="badge badge-blue">
                                <?= $zona_label[$r['zona']] ?? $r['zona'] ?>
                            </span>
                        </td>

                        <td style="display:flex; gap:6px;">

                            <a
                                href="?edit=<?= $r['id_tujuan'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_tujuan'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Hapus?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>
