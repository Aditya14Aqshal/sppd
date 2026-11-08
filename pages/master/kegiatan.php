<?php
ob_start();

$halaman   = 'Master Jenis Kegiatan';
$sub_judul = 'Master Data / Jenis Kegiatan';
$aktif     = 'm_kegiatan';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

$msg = '';
$err = '';


// URL Message
if (isset($_GET['msg'])) {

    $url_msg = $_GET['msg'];

    if ($url_msg === 'tambah') {

        $msg = 'Data berhasil ditambahkan.';

    } elseif ($url_msg === 'edit') {

        $msg = 'Data berhasil disimpan.';

    } elseif ($url_msg === 'hapus') {

        $msg = 'Data berhasil dihapus.';

    } elseif ($url_msg === 'toggle') {

        $val = $_GET['val'] ?? '';
        $label = $val === 'aktif' ? 'Aktif' : 'Non-aktif';
        $msg = "Status diubah ke: $label";
    }
}


// POST : Simpan / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id   = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_kegiatan']);
    $ket  = trim($_POST['keterangan']);
    $stat = $_POST['status'] ?? 'aktif';

    if (!$nama) {

        $err = 'Nama kegiatan wajib diisi.';

    } else {

        if ($id) {

            $st = $conn->prepare("
                UPDATE tb_jenis_kegiatan
                SET
                    nama_kegiatan = ?,
                    keterangan    = ?,
                    status        = ?
                WHERE id_kegiatan = ?
            ");

            $st->bind_param(
                'sssi',
                $nama,
                $ket,
                $stat,
                $id
            );

            if ($st->execute()) {

                header("Location: kegiatan.php?msg=edit");
                exit;

            } else {

                $err = 'Gagal: ' . $conn->error;
            }

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_jenis_kegiatan
                    (nama_kegiatan, keterangan, status)
                VALUES
                    (?, ?, ?)
            ");

            $st->bind_param(
                'sss',
                $nama,
                $ket,
                $stat
            );

            if ($st->execute()) {

                header("Location: kegiatan.php?msg=tambah");
                exit;

            } else {

                $err = 'Gagal: ' . $conn->error;
            }
        }
    }
}

// GET : Hapus
if (isset($_GET['hapus'])) {

    $hid = (int) $_GET['hapus'];

    $cek = $conn
        ->query("SELECT COUNT(*) c FROM tb_sppd WHERE id_kegiatan=$hid")
        ->fetch_assoc()['c'];

    if ($cek > 0) {

        $err = 'Tidak bisa dihapus, sudah digunakan di ' . $cek . ' SPPD.';

    } else {

        $conn->query("DELETE FROM tb_jenis_kegiatan WHERE id_kegiatan=$hid");
        header("Location: kegiatan.php?msg=hapus");
        exit;
    }
}


// GET : Toggle Status
if (isset($_GET['toggle'])) {

    $tid = (int) $_GET['toggle'];

    $cur = $conn
        ->query("SELECT status FROM tb_jenis_kegiatan WHERE id_kegiatan=$tid")
        ->fetch_assoc()['status'];

    $new = $cur === 'aktif'
        ? 'nonaktif'
        : 'aktif';

    $conn->query("
        UPDATE tb_jenis_kegiatan
        SET status='$new'
        WHERE id_kegiatan=$tid
    ");

    header("Location: kegiatan.php?msg=toggle&val=" . urlencode($new));
    exit;
}


// Data Edit
$edit = null;

$edit_id = isset($_GET['edit'])
    ? (int) $_GET['edit']
    : 0;

if ($edit_id) {

    $edit = $conn
        ->query("SELECT * FROM tb_jenis_kegiatan WHERE id_kegiatan=$edit_id")
        ->fetch_assoc();
}


// Data Tabel
$data = $conn->query("
    SELECT
        k.*,
        (
            SELECT COUNT(*)
            FROM tb_sppd
            WHERE id_kegiatan = k.id_kegiatan
        ) AS total_pakai
    FROM tb_jenis_kegiatan k
    ORDER BY k.nama_kegiatan
");

?>

<div class="page-head">
    <div>
        <h2>Master Jenis Kegiatan Dinas</h2>
        <p>Kategori kegiatan yang menjadi dasar penerbitan SPPD</p>
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
            <?= $edit ? 'Edit' : 'Tambah' ?> Jenis Kegiatan
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id"
                value="<?= $edit['id_kegiatan'] ?? 0 ?>"
            >

            <div class="form-grid">

                <div class="form-group">

                    <label class="form-label">
                        Nama Jenis Kegiatan
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nama_kegiatan"
                        value="<?= htmlspecialchars($edit['nama_kegiatan'] ?? '') ?>"
                        placeholder="Contoh: Rapat Koordinasi"
                        required
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

                <div class="form-group full">

                    <label class="form-label">
                        Keterangan / Deskripsi
                    </label>

                    <textarea
                        class="form-control"
                        name="keterangan"
                        rows="2"
                        placeholder="Deskripsi singkat jenis kegiatan..."
                    ><?= htmlspecialchars($edit['keterangan'] ?? '') ?></textarea>

                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>
                <a
                    href="/sppd/pages/master/kegiatan.php"
                    class="btn btn-secondary"
                >
                    Batal
                </a>
            <?php endif; ?>

            <button
                type="submit"
                class="btn btn-primary"
            >
                &#128190; Simpan
            </button>

        </div>

    </form>

</div>

<div class="card">

    <div class="card-header">
        <div class="card-title">
            Daftar Jenis Kegiatan (<?= $data->num_rows ?> data)
        </div>
    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>#</th>
                    <th>Nama Kegiatan</th>
                    <th>Keterangan</th>
                    <th>Dipakai</th>
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
                            <strong><?= htmlspecialchars($r['nama_kegiatan']) ?></strong>
                        </td>

                        <td style="color:var(--gray-600); font-size:12px;">
                            <?= htmlspecialchars($r['keterangan'] ?? '-') ?>
                        </td>

                        <td style="text-align:center;">
                            <span class="badge badge-blue">
                                <?= $r['total_pakai'] ?> SPPD
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= $r['status'] === 'aktif' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>

                        <td>

                            <div style="display:flex; gap:5px;">

                                <a
                                    href="?edit=<?= $r['id_kegiatan'] ?>"
                                    class="btn btn-outline btn-sm"
                                >
                                    Edit
                                </a>

                                <a
                                    href="?toggle=<?= $r['id_kegiatan'] ?>"
                                    class="btn btn-<?= $r['status'] === 'aktif' ? 'warning' : 'success' ?> btn-sm"
                                >
                                    <?= $r['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </a>

                                <?php if ($r['total_pakai'] == 0) : ?>

                                    <a
                                        href="?hapus=<?= $r['id_kegiatan'] ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus?')"
                                    >
                                        Hapus
                                    </a>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<?php ob_end_flush(); require_once '../../includes/footer.php'; ?>