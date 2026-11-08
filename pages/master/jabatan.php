<?php

$halaman   = 'Master Jabatan';
$sub_judul = 'Master Data / Jabatan';
$aktif     = 'm_jabatan';

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
    $conn->query("DELETE FROM tb_jabatan WHERE id_jabatan=" . (int) $_GET['hapus']);
    $msg = 'Data berhasil dihapus.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id       = (int) ($_POST['id'] ?? 0);
    $nama     = trim($_POST['nama_jabatan']);
    $gol      = trim($_POST['golongan']);
    $pangkat  = trim($_POST['pangkat']);
    $eselon   = trim($_POST['eselon']);

    if (!$nama || !$gol) {

        $err = 'Nama jabatan dan golongan wajib diisi.';

    } else {

        if ($id) {

            $st = $conn->prepare("
                UPDATE tb_jabatan
                SET
                    nama_jabatan = ?,
                    golongan     = ?,
                    pangkat      = ?,
                    eselon       = ?
                WHERE id_jabatan = ?
            ");

            $st->bind_param(
                'ssssi',
                $nama,
                $gol,
                $pangkat,
                $eselon,
                $id
            );

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_jabatan
                (
                    nama_jabatan,
                    golongan,
                    pangkat,
                    eselon
                )
                VALUES (?, ?, ?, ?)
            ");

            $st->bind_param(
                'ssss',
                $nama,
                $gol,
                $pangkat,
                $eselon
            );
        }

        $st->execute()
            ? $msg = 'Data berhasil disimpan.'
            : $err = 'Gagal: ' . $conn->error;
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $edit = $conn->query(
        "SELECT * FROM tb_jabatan WHERE id_jabatan=" . (int) $_GET['edit']
    )->fetch_assoc();
}

$data = $conn->query("
    SELECT *
    FROM tb_jabatan
    ORDER BY golongan, nama_jabatan
");
?>

<div class="page-head">
    <div>
        <h2>Master Data Jabatan & Golongan</h2>
        <p>Referensi jabatan yang berlaku di instansi</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="alert alert-danger"><?= $err ?></div>
<?php endif; ?>

<div class="card">

    <div class="card-header">
        <div class="card-title">
            <?= $edit ? 'Edit' : 'Tambah' ?> Jabatan
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id"
                value="<?= $edit['id_jabatan'] ?? 0 ?>"
            >

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">
                        Nama Jabatan <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nama_jabatan"
                        value="<?= htmlspecialchars($edit['nama_jabatan'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Golongan <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="golongan"
                        value="<?= htmlspecialchars($edit['golongan'] ?? '') ?>"
                        placeholder="Contoh: III/c"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Pangkat</label>

                    <input
                        class="form-control"
                        name="pangkat"
                        value="<?= htmlspecialchars($edit['pangkat'] ?? '') ?>"
                        placeholder="Contoh: Penata"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Eselon</label>

                    <input
                        class="form-control"
                        name="eselon"
                        value="<?= htmlspecialchars($edit['eselon'] ?? '') ?>"
                        placeholder="Contoh: III/b"
                    >
                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit): ?>
                <a href="/sppd/pages/master/jabatan.php" class="btn btn-secondary">
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
            Daftar Jabatan
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Jabatan</th>
                    <th>Golongan</th>
                    <th>Pangkat</th>
                    <th>Eselon</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $no = 1;

                while ($r = $data->fetch_assoc()):
                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= htmlspecialchars($r['nama_jabatan']) ?></td>

                        <td><?= htmlspecialchars($r['golongan']) ?></td>

                        <td><?= htmlspecialchars($r['pangkat'] ?? '-') ?></td>

                        <td><?= htmlspecialchars($r['eselon'] ?? '-') ?></td>

                        <td style="display:flex; gap:6px;">

                            <a
                                href="?edit=<?= $r['id_jabatan'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_jabatan'] ?>"
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