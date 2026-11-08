<?php

$halaman   = 'Master Pegawai';
$sub_judul = 'Master Data / Pegawai';
$aktif     = 'm_pegawai';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

$msg = '';
$err = '';

// DELETE
if (isset($_GET['hapus'])) {

    $hid = (int) $_GET['hapus'];

    $conn->query("DELETE FROM tb_pegawai WHERE id_pegawai=$hid");

    $msg = 'Data pegawai berhasil dihapus.';
}

// SAVE (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_peg = (int) ($_POST['id_pegawai'] ?? 0);
    $nip    = trim($_POST['nip']);
    $nama   = trim($_POST['nama_lengkap']);
    $id_jbt = (int) $_POST['id_jabatan'];
    $jk     = $_POST['jenis_kelamin'];
    $unit   = trim($_POST['unit_kerja']);
    $hp     = trim($_POST['no_hp']);
    $email  = trim($_POST['email']);
    $status = $_POST['status'];
    $jenis  = $_POST['jenis_pegawai'] ?? 'PNS';

    if (!$nip || !$nama || !$id_jbt) {

        $err = 'NIP, Nama, dan Jabatan wajib diisi.';

    } else {

        if ($id_peg) {

            $st = $conn->prepare("
                UPDATE tb_pegawai
                SET
                    nip             = ?,
                    nama_lengkap    = ?,
                    id_jabatan      = ?,
                    jenis_kelamin   = ?,
                    unit_kerja      = ?,
                    no_hp           = ?,
                    email           = ?,
                    status          = ?,
                    jenis_pegawai   = ?
                WHERE id_pegawai = ?
            ");

            $st->bind_param(
                'ssississsi',
                $nip,
                $nama,
                $id_jbt,
                $jk,
                $unit,
                $hp,
                $email,
                $status,
                $jenis,
                $id_peg
            );

        } else {

            $st = $conn->prepare("
                INSERT INTO tb_pegawai
                (
                    nip,
                    nama_lengkap,
                    id_jabatan,
                    jenis_kelamin,
                    unit_kerja,
                    no_hp,
                    email,
                    status,
                    jenis_pegawai
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $st->bind_param(
                'ssissssss',
                $nip,
                $nama,
                $id_jbt,
                $jk,
                $unit,
                $hp,
                $email,
                $status,
                $jenis
            );
        }

        $st->execute()
            ? $msg = 'Data berhasil disimpan.'
            : $err = 'Gagal menyimpan: ' . $conn->error;
    }
}

// EDIT
$edit = null;

if (isset($_GET['edit'])) {

    $eid = (int) $_GET['edit'];

    $edit = $conn->query("
        SELECT *
        FROM tb_pegawai
        WHERE id_pegawai = $eid
    ")->fetch_assoc();
}

$jabatans = $conn->query("
    SELECT *
    FROM tb_jabatan
    ORDER BY nama_jabatan
");

$unit_list = $conn->query("
    SELECT *
    FROM tb_unit_kerja
    ORDER BY nama_unit
");

$pegawais = $conn->query("
    SELECT
        p.*,
        j.nama_jabatan,
        j.golongan
    FROM tb_pegawai p
    JOIN tb_jabatan j
        ON p.id_jabatan = j.id_jabatan
    ORDER BY p.nama_lengkap
");

?>

<div class="page-head">
    <div>
        <h2>Master Data Pegawai</h2>
        <p>Kelola data induk seluruh pegawai</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="alert alert-danger"><?= $err ?></div>
<?php endif; ?>

<!-- FORM TAMBAH / EDIT -->

<div class="card">

    <div class="card-header">
        <div class="card-title">
            <?= $edit ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru' ?>
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id_pegawai"
                value="<?= $edit['id_pegawai'] ?? 0 ?>"
            >

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">
                        NIP <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nip"
                        value="<?= htmlspecialchars($edit['nip'] ?? $_POST['nip'] ?? '') ?>"
                        placeholder="18 digit NIP"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Nama Lengkap <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="nama_lengkap"
                        value="<?= htmlspecialchars($edit['nama_lengkap'] ?? $_POST['nama_lengkap'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Jabatan <span class="req">*</span>
                    </label>

                    <select class="form-control" name="id_jabatan" required>

                        <option value="">-- Pilih Jabatan --</option>

                        <?php
                        $jabatans->data_seek(0);
                        while ($j = $jabatans->fetch_assoc()):
                        ?>

                            <option
                                value="<?= $j['id_jabatan'] ?>"
                                <?= ($edit['id_jabatan'] ?? $_POST['id_jabatan'] ?? '') == $j['id_jabatan'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($j['nama_jabatan']) ?>
                                (<?= $j['golongan'] ?>)
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>

                    <select class="form-control" name="jenis_kelamin">
                        <option value="L" <?= ($edit['jenis_kelamin'] ?? 'L') === 'L' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="P" <?= ($edit['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Kerja / Bidang</label>

                    <select class="form-control" name="unit_kerja">

                        <option value="">-- Pilih Unit Kerja --</option>

                        <?php
                        $unit_list->data_seek(0);
                        while ($u = $unit_list->fetch_assoc()):
                        ?>

                            <option
                                value="<?= htmlspecialchars($u['nama_unit']) ?>"
                                <?= ($edit['unit_kerja'] ?? '') === $u['nama_unit'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($u['nama_unit']) ?>
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">No. HP</label>

                    <input
                        class="form-control"
                        name="no_hp"
                        value="<?= htmlspecialchars($edit['no_hp'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>

                    <input
                        class="form-control"
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($edit['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>

                    <select class="form-control" name="status">

                        <option value="aktif" <?= ($edit['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>
                            Aktif
                        </option>

                        <option value="nonaktif" <?= ($edit['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>
                            Non-aktif
                        </option>

                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Pegawai</label>

                    <select class="form-control" name="jenis_pegawai">

                        <?php foreach (['PNS', 'PPPK', 'Kontrak', 'Honorer', 'Tenaga Ahli'] as $jp): ?>

                            <option
                                value="<?= $jp ?>"
                                <?= ($edit['jenis_pegawai'] ?? 'PNS') === $jp ? 'selected' : '' ?>
                            >
                                <?= $jp ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit): ?>

                <a href="/sppd/pages/master/pegawai.php" class="btn btn-secondary">
                    Batal Edit
                </a>

            <?php endif; ?>

            <button type="submit" class="btn btn-primary">
                &#128190; Simpan Data
            </button>

        </div>

    </form>

</div>

// TABEL DATA 
<div class="card">

    <div class="card-header">

        <div class="card-title">
            Daftar Pegawai (<?= $pegawais->num_rows ?> orang)
        </div>

        <a
            href="/sppd/pages/laporan/cetak_pegawai.php"
            class="btn btn-secondary btn-sm"
            target="_blank"
        >
            &#128424; Cetak
        </a>

    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>NIP</th>
                    <th>Nama Lengkap</th>
                    <th>Jabatan</th>
                    <th>Gol.</th>
                    <th>Jenis</th>
                    <th>Unit Kerja</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $no = 1;
                $pegawais->data_seek(0);

                while ($r = $pegawais->fetch_assoc()):
                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= htmlspecialchars($r['nip']) ?></td>

                        <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>

                        <td><?= htmlspecialchars($r['nama_jabatan']) ?></td>

                        <td><?= htmlspecialchars($r['golongan']) ?></td>

                        <td>
                            <span class="badge badge-blue">
                                <?= htmlspecialchars($r['jenis_pegawai'] ?? 'PNS') ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($r['unit_kerja'] ?? '-') ?></td>

                        <td>
                            <span class="badge <?= $r['status'] === 'aktif' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>

                        <td style="display:flex; gap:6px;">

                            <a
                                href="?edit=<?= $r['id_pegawai'] ?>"
                                class="btn btn-outline btn-sm"
                            >
                                Edit
                            </a>

                            <a
                                href="?hapus=<?= $r['id_pegawai'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Hapus pegawai ini?')"
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