<?php

$halaman   = 'Master Anggaran';
$sub_judul = 'Master Data / Anggaran';
$aktif     = 'm_anggaran';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

$msg = '';
$err = '';


// GET : Hapus


if (isset($_GET['hapus'])) {

    $conn->query(
        "DELETE FROM tb_anggaran WHERE id_anggaran=" . (int) $_GET['hapus']
    );

    $msg = 'Dihapus.';
}


// POST : Simpan / Edit


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id    = (int) ($_POST['id'] ?? 0);
    $kode  = trim($_POST['kode_anggaran']);
    $nama  = trim($_POST['nama_program']);

    $pagu = str_replace(
        ['.', 'Rp ', 'Rp', ' '],
        '',
        $_POST['pagu']
    );

    $tahun  = $_POST['tahun_anggaran'];
    $status = $_POST['status'];

    if (!$kode || !$nama) {

        $err = 'Kode dan nama program wajib diisi.';

    } else {

        if ($id) {

            $st = $conn->prepare("
                UPDATE tb_anggaran
                SET
                    kode_anggaran   = ?,
                    nama_program    = ?,
                    pagu            = ?,
                    tahun_anggaran = ?,
                    status          = ?
                WHERE id_anggaran = ?
            ");

            $st->bind_param(
                'ssdisi',
                $kode,
                $nama,
                $pagu,
                $tahun,
                $status,
                $id
            );

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_anggaran
                    (
                        kode_anggaran,
                        nama_program,
                        pagu,
                        tahun_anggaran,
                        status
                    )
                VALUES
                    (?, ?, ?, ?, ?)
            ");

            $st->bind_param(
                'ssdis',
                $kode,
                $nama,
                $pagu,
                $tahun,
                $status
            );
        }

        $st->execute()
            ? $msg = 'Disimpan.'
            : $err = 'Gagal: ' . $conn->error;
    }
}


// Data Edit


$edit = null;

if (isset($_GET['edit'])) {

    $edit = $conn
        ->query(
            "SELECT * FROM tb_anggaran WHERE id_anggaran=" . (int) $_GET['edit']
        )
        ->fetch_assoc();
}


// Data Tabel


$data = $conn->query("
    SELECT *
    FROM tb_anggaran
    ORDER BY tahun_anggaran DESC, nama_program
");

?>

<div class="page-head">
    <div>
        <h2>Master Sumber Anggaran</h2>
        <p>Referensi kode MAK dan pagu anggaran</p>
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
            <?= $edit ? 'Edit' : 'Tambah' ?> Anggaran
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id"
                value="<?= $edit['id_anggaran'] ?? 0 ?>"
            >

            <div class="form-grid">

                <div class="form-group">

                    <label class="form-label">
                        Kode Anggaran (MAK)
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="kode_anggaran"
                        value="<?= htmlspecialchars($edit['kode_anggaran'] ?? '') ?>"
                        placeholder="1.05.01.2.01"
                        required
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Tahun Anggaran
                    </label>

                    <input
                        class="form-control"
                        type="number"
                        name="tahun_anggaran"
                        value="<?= $edit['tahun_anggaran'] ?? date('Y') ?>"
                        min="2020"
                        max="2030"
                    >

                </div>

                <div class="form-group full">

                    <label class="form-label">
                        Nama Program / Kegiatan
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nama_program"
                        value="<?= htmlspecialchars($edit['nama_program'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Pagu Anggaran (Rp)
                    </label>

                    <input
                        class="form-control"
                        name="pagu"
                        value="<?= number_format($edit['pagu'] ?? 0, 0, ',', '.') ?>"
                        placeholder="0"
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Status
                    </label>

                    <select class="form-control" name="status">

                        <option
                            value="aktif"
                            <?= ($edit['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>
                        >
                            Aktif
                        </option>

                        <option
                            value="nonaktif"
                            <?= ($edit['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>
                        >
                            Non-aktif
                        </option>

                    </select>

                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>

                <a
                    href="/sppd/pages/master/anggaran.php"
                    class="btn btn-secondary"
                >
                    Batal
                </a>

            <?php endif; ?>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Simpan
            </button>

        </div>

    </form>

</div>

<div class="card">

    <div class="card-header">
        <div class="card-title">
            Daftar Anggaran
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Program</th>
                    <th>Pagu (Rp)</th>
                    <th>Tahun</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

                <?php
                $no = 1;

                while ($r = $data->fetch_assoc()) :
                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= htmlspecialchars($r['kode_anggaran']) ?></td>

                        <td><?= htmlspecialchars($r['nama_program']) ?></td>

                        <td style="text-align:right;">
                            <?= number_format($r['pagu'], 0, ',', '.') ?>
                        </td>

                        <td><?= $r['tahun_anggaran'] ?></td>

                        <td>
                            <span class="badge <?= $r['status'] === 'aktif' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>

                        <td style="display:flex; gap:6px;">

                            <a
                                href="?edit=<?= $r['id_anggaran'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_anggaran'] ?>"
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