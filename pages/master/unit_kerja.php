<?php

$halaman   = 'Unit Kerja';
$sub_judul = 'Master Data / Unit Kerja';
$aktif     = 'm_unit_kerja';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

// Auto-create table jika belum ada
$conn->query("
    CREATE TABLE IF NOT EXISTS tb_unit_kerja (
        id_unit INT AUTO_INCREMENT PRIMARY KEY,
        nama_unit VARCHAR(100) NOT NULL,
        kode_unit VARCHAR(20) DEFAULT NULL
    ) ENGINE=InnoDB
");

// Seed data awal jika tabel kosong
$cek = $conn->query("SELECT COUNT(*) c FROM tb_unit_kerja")->fetch_assoc();

if ($cek['c'] == 0) {

    $conn->query("
        INSERT INTO tb_unit_kerja (nama_unit, kode_unit)
        VALUES
            ('Sekretariat', 'SKR'),
            ('Bidang Politik Dalam Negeri', 'BPDN'),
            ('Bidang Ketahanan Ekonomi Sosial Budaya', 'BKESB'),
            ('Bidang Ideologi Wawasan Kebangsaan', 'BIWK'),
            ('Bidang Pemberdayaan Ormas dan Oks', 'BPOO')
    ");
}

// Proses form
$msg  = '';
$err  = '';
$edit = null;

if (isset($_GET['edit'])) {

    $edit = $conn
        ->query("SELECT * FROM tb_unit_kerja WHERE id_unit=" . (int) $_GET['edit'])
        ->fetch_assoc();
}

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $conn->query("DELETE FROM tb_unit_kerja WHERE id_unit=$id")
        ? $msg = 'Unit kerja dihapus.'
        : $err = 'Gagal: ' . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id   = (int) ($_POST['id_unit'] ?? 0);
    $nama = trim($_POST['nama_unit']);
    $kode = trim($_POST['kode_unit']);

    if (!$nama) {

        $err = 'Nama unit kerja wajib diisi.';

    } else {

        if ($id) {

            $st = $conn->prepare("
                UPDATE tb_unit_kerja
                SET
                    nama_unit=?,
                    kode_unit=?
                WHERE id_unit=?
            ");

            $st->bind_param(
                'ssi',
                $nama,
                $kode,
                $id
            );

            $st->execute()
                ? $msg = 'Unit kerja diperbarui.'
                : $err = 'Gagal: ' . $conn->error;

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_unit_kerja
                    (nama_unit, kode_unit)
                VALUES
                    (?, ?)
            ");

            $st->bind_param(
                'ss',
                $nama,
                $kode
            );

            $st->execute()
                ? $msg = 'Unit kerja ditambahkan.'
                : $err = 'Gagal: ' . $conn->error;
        }
    }
}

$data = $conn->query("
    SELECT *
    FROM tb_unit_kerja
    ORDER BY nama_unit
");
?>

<div class="page-head">
    <div>
        <h2>Unit Kerja / Bidang</h2>
        <p>Kelola daftar unit kerja dan bidang</p>
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
            <?= $edit ? 'Edit' : 'Tambah' ?> Unit Kerja
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <div class="form-grid">

                <div class="form-group">

                    <label class="form-label">
                        Nama Unit / Bidang
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nama_unit"
                        value="<?= htmlspecialchars($edit['nama_unit'] ?? '') ?>"
                        placeholder="Contoh: Sekretariat"
                        required
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Kode Unit
                    </label>

                    <input
                        class="form-control"
                        name="kode_unit"
                        value="<?= htmlspecialchars($edit['kode_unit'] ?? '') ?>"
                        placeholder="Contoh: SKR"
                    >

                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>

                <input
                    type="hidden"
                    name="id_unit"
                    value="<?= $edit['id_unit'] ?>"
                >

                <a
                    href="/sppd/pages/master/unit_kerja.php"
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
            Daftar Unit Kerja
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>#</th>
                    <th>Nama Unit / Bidang</th>
                    <th>Kode</th>
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
                            <?= htmlspecialchars($r['nama_unit']) ?>
                        </td>

                        <td>
                            <code><?= htmlspecialchars($r['kode_unit'] ?? '—') ?></code>
                        </td>

                        <td style="display:flex;gap:6px">

                            <a
                                href="?edit=<?= $r['id_unit'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_unit'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Hapus unit kerja ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

                <?php if ($data->num_rows === 0) : ?>

                    <tr>

                        <td
                            colspan="4"
                            style="text-align:center;color:var(--gray-400);padding:24px"
                        >
                            Belum ada data unit kerja
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>
