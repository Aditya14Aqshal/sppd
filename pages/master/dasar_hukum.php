<?php

$halaman   = 'Dasar Hukum';
$sub_judul = 'Master Data / Dasar Hukum';
$aktif     = 'm_dasar_hukum';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

// Auto-create table jika belum ada
$conn->query("CREATE TABLE IF NOT EXISTS tb_dasar_hukum (
    id_dasar INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    tahun YEAR DEFAULT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    urutan INT DEFAULT 0
) ENGINE=InnoDB");

$cek = $conn->query("SELECT COUNT(*) c FROM tb_dasar_hukum")->fetch_assoc();

if ($cek['c'] == 0) {
    $conn->query("
        INSERT INTO tb_dasar_hukum
            (judul, isi, tahun, status, urutan)
        VALUES
            (
                'Peraturan Menteri Keuangan Nomor 113/PMK.05/2012',
                'Perjalanan Dinas Dalam Negeri bagi Pejabat Negara, Pegawai Negeri, dan Pegawai Tidak Tetap',
                2012,
                'aktif',
                1
            ),
            (
                'Peraturan Menteri Keuangan Nomor 49/PMK.02/2023',
                'Standar Biaya Masukan Tahun Anggaran 2024',
                2023,
                'aktif',
                2
            ),
            (
                'DPA Badan Kesatuan Bangsa dan Politik Kota Kuala Kapuas',
                'Dokumen Pelaksanaan Anggaran Tahun 2026',
                2026,
                'aktif',
                3
            )
    ");
}

$msg  = '';
$err  = '';
$edit = null;

if (isset($_GET['edit'])) {
    $edit = $conn
        ->query("SELECT * FROM tb_dasar_hukum WHERE id_dasar=" . (int) $_GET['edit'])
        ->fetch_assoc();
}

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $conn->query("DELETE FROM tb_dasar_hukum WHERE id_dasar=$id")
        ? $msg = 'Dasar hukum dihapus.'
        : $err = 'Gagal: ' . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = (int) ($_POST['id_dasar'] ?? 0);
    $judul  = trim($_POST['judul']);
    $isi    = trim($_POST['isi']);
    $tahun  = (int) ($_POST['tahun'] ?? 0);
    $status = $_POST['status'] ?? 'aktif';

    if (!$judul || !$isi) {

        $err = 'Judul dan isi wajib diisi.';

    } else {

        if ($id) {

            $st = $conn->prepare("
                UPDATE tb_dasar_hukum
                SET
                    judul = ?,
                    isi = ?,
                    tahun = ?,
                    status = ?
                WHERE id_dasar = ?
            ");

            $st->bind_param(
                'ssisi',
                $judul,
                $isi,
                $tahun,
                $status,
                $id
            );

            $st->execute()
                ? $msg = 'Dasar hukum diperbarui.'
                : $err = 'Gagal: ' . $conn->error;

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_dasar_hukum
                    (judul, isi, tahun, status)
                VALUES
                    (?, ?, ?, ?)
            ");

            $st->bind_param(
                'ssis',
                $judul,
                $isi,
                $tahun,
                $status
            );

            $st->execute()
                ? $msg = 'Dasar hukum ditambahkan.'
                : $err = 'Gagal: ' . $conn->error;
        }
    }
}

$data = $conn->query("
    SELECT *
    FROM tb_dasar_hukum
    ORDER BY urutan, tahun DESC
");
?>

<div class="page-head">
    <div>
        <h2>Dasar Hukum / Regulasi</h2>
        <p>Referensi peraturan yang digunakan dalam surat perjalanan dinas</p>
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
            <?= $edit ? 'Edit' : 'Tambah' ?> Dasar Hukum
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <div class="form-grid">

                <div class="form-group full">
                    <label class="form-label">
                        Judul / Nama Peraturan
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="judul"
                        value="<?= htmlspecialchars($edit['judul'] ?? '') ?>"
                        placeholder="Contoh: Peraturan Menteri Keuangan Nomor ..."
                        required
                    >
                </div>

                <div class="form-group full">
                    <label class="form-label">
                        Isi / Keterangan
                        <span class="req">*</span>
                    </label>

                    <textarea
                        class="form-control"
                        name="isi"
                        rows="3"
                        placeholder="Deskripsi atau kutipan peraturan..."
                        required
                    ><?= htmlspecialchars($edit['isi'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tahun</label>

                    <input
                        class="form-control"
                        type="number"
                        name="tahun"
                        value="<?= $edit['tahun'] ?? date('Y') ?>"
                        min="2000"
                        max="2030"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>

                    <select class="form-control" name="status">
                        <option value="aktif" <?= ($edit['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>
                            Aktif
                        </option>

                        <option value="nonaktif" <?= ($edit['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>
                            Nonaktif
                        </option>
                    </select>
                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>
                <input
                    type="hidden"
                    name="id_dasar"
                    value="<?= $edit['id_dasar'] ?>"
                >

                <a href="/sppd/pages/master/dasar_hukum.php" class="btn btn-secondary">
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
            Daftar Dasar Hukum
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Judul / Peraturan</th>
                    <th>Isi</th>
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

                        <td>
                            <strong><?= htmlspecialchars($r['judul']) ?></strong>
                        </td>

                        <td style="max-width:300px;font-size:12px;color:var(--gray-600)">
                            <?= htmlspecialchars(mb_substr($r['isi'], 0, 100)) ?>
                            <?= mb_strlen($r['isi']) > 100 ? '...' : '' ?>
                        </td>

                        <td>
                            <?= $r['tahun'] ?: '—' ?>
                        </td>

                        <td>
                            <span class="badge badge-<?= $r['status'] === 'aktif' ? 'green' : 'gray' ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>

                        <td style="display:flex;gap:6px">

                            <a
                                href="?edit=<?= $r['id_dasar'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_dasar'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Hapus dasar hukum ini?')"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

                <?php if ($data->num_rows === 0) : ?>

                    <tr>

                        <td
                            colspan="6"
                            style="text-align:center;color:var(--gray-400);padding:24px"
                        >
                            Belum ada data dasar hukum
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>
