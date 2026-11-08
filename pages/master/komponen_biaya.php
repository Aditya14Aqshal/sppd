<?php

$halaman   = 'Komponen Biaya';
$sub_judul = 'Master Data / Komponen Biaya';
$aktif     = 'm_biaya';

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

    $conn->query(
        "DELETE FROM tb_komponen_biaya WHERE id_komponen=" . (int) $_GET['hapus']
    );

    $msg = 'Data berhasil dihapus.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id    = (int) ($_POST['id'] ?? 0);
    $jenis = $_POST['jenis_biaya'];
    $gol   = trim($_POST['golongan']);
    $zona  = $_POST['zona'];

    $bes = (float) str_replace(
        ['.', 'Rp ', 'Rp', ' '],
        '',
        $_POST['besaran']
    );

    $tahun = $_POST['tahun_anggaran'];

    if (!$jenis || !$gol || !$zona) {

        $err = 'Semua field wajib diisi.';

    } else {

        if ($id) {

            $st = $conn->prepare("
                UPDATE tb_komponen_biaya
                SET
                    jenis_biaya=?,
                    golongan=?,
                    zona=?,
                    besaran=?,
                    tahun_anggaran=?
                WHERE id_komponen=?
            ");

            $st->bind_param(
                'sssdii',
                $jenis,
                $gol,
                $zona,
                $bes,
                $tahun,
                $id
            );

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_komponen_biaya
                    (
                        jenis_biaya,
                        golongan,
                        zona,
                        besaran,
                        tahun_anggaran
                    )
                VALUES
                    (?, ?, ?, ?, ?)
            ");

            $st->bind_param(
                'sssdi',
                $jenis,
                $gol,
                $zona,
                $bes,
                $tahun
            );
        }

        $st->execute()
            ? $msg = 'Data berhasil disimpan.'
            : $err = 'Gagal: ' . $conn->error;
    }
}

$edit = null;

if (isset($_GET['edit'])) {

    $edit = $conn
        ->query(
            "SELECT * FROM tb_komponen_biaya WHERE id_komponen=" . (int) $_GET['edit']
        )
        ->fetch_assoc();
}

$data = $conn->query("
    SELECT *
    FROM tb_komponen_biaya
    ORDER BY
        tahun_anggaran DESC,
        jenis_biaya,
        golongan,
        zona
");

$zona_label = [
    'lokal'           => 'Lokal',
    'luar_kota'       => 'Luar Kota',
    'luar_provinsi'   => 'Luar Provinsi'
];

$jenis_label = [
    'uang_harian' => 'Uang Harian',
    'transport'   => 'Transport',
    'penginapan'  => 'Penginapan'
];

?>

<div class="page-head">
    <div>
        <h2>Master Komponen Biaya Perjalanan</h2>
        <p>Standar biaya per jenis, golongan, dan zona wilayah</p>
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
            <?= $edit ? 'Edit' : 'Tambah' ?> Komponen Biaya
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id"
                value="<?= $edit['id_komponen'] ?? 0 ?>"
            >

            <div class="form-grid">

                <div class="form-group">

                    <label class="form-label">
                        Jenis Biaya
                        <span class="req">*</span>
                    </label>

                    <select
                        class="form-control"
                        name="jenis_biaya"
                        required
                    >

                        <option value="">-- Pilih Jenis --</option>

                        <?php foreach ($jenis_label as $v => $l) : ?>

                            <option
                                value="<?= $v ?>"
                                <?= ($edit['jenis_biaya'] ?? '') === $v ? 'selected' : '' ?>
                            >
                                <?= $l ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Golongan
                        <span class="req">*</span>
                    </label>

                    <select
                        class="form-control"
                        name="golongan"
                        required
                    >

                        <option value="">-- Pilih Golongan --</option>

                        <?php foreach (['I', 'II', 'III', 'IV'] as $g) : ?>

                            <option
                                value="<?= $g ?>"
                                <?= ($edit['golongan'] ?? '') === $g ? 'selected' : '' ?>
                            >
                                Golongan <?= $g ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Zona Wilayah
                        <span class="req">*</span>
                    </label>

                    <select
                        class="form-control"
                        name="zona"
                        required
                    >

                        <option value="">-- Pilih Zona --</option>

                        <?php foreach ($zona_label as $v => $l) : ?>

                            <option
                                value="<?= $v ?>"
                                <?= ($edit['zona'] ?? '') === $v ? 'selected' : '' ?>
                            >
                                <?= $l ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

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
                        Besaran Biaya (Rp)
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="besaran"
                        value="<?= number_format($edit['besaran'] ?? 0, 0, ',', '.') ?>"
                        placeholder="Contoh: 350000"
                        required
                    >

                    <div class="form-hint">
                        Masukkan angka saja tanpa titik/koma, contoh: 350000
                    </div>

                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>
                <a
                    href="/sppd/pages/master/komponen_biaya.php"
                    class="btn btn-secondary"
                >
                    Batal
                </a>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">
                &#128190; Simpan
            </button>

        </div>

    </form>

</div>

<div class="card">

    <div class="card-header">
        <div class="card-title">
            Daftar Komponen Biaya
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Jenis Biaya</th>
                    <th>Golongan</th>
                    <th>Zona</th>
                    <th style="text-align:right">Besaran (Rp)</th>
                    <th>Tahun</th>
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

                        <td>
                            <span class="badge badge-blue">
                                <?= $jenis_label[$r['jenis_biaya']] ?? $r['jenis_biaya'] ?>
                            </span>
                        </td>

                        <td>
                            Golongan <?= htmlspecialchars($r['golongan']) ?>
                        </td>

                        <td>
                            <?= $zona_label[$r['zona']] ?? $r['zona'] ?>
                        </td>

                        <td style="text-align:right;font-weight:500">
                            Rp <?= number_format($r['besaran'], 0, ',', '.') ?>
                        </td>

                        <td>
                            <?= $r['tahun_anggaran'] ?>
                        </td>

                        <td style="display:flex;gap:5px">

                            <a
                                href="?edit=<?= $r['id_komponen'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_komponen'] ?>"
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