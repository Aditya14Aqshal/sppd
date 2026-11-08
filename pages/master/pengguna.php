<?php

$halaman   = 'Kelola Pengguna';
$sub_judul = 'Master Data / Pengguna';
$aktif     = 'm_pengguna';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/footer.php';
    exit;
}

$msg = '';
$err = '';

// HAPUS
if (isset($_GET['hapus'])) {

    $hid = (int) $_GET['hapus'];

    if ($hid == $_SESSION['user_id']) {

        $err = 'Tidak bisa menghapus akun sendiri.';

    } else {

        $conn->query("DELETE FROM tb_pengguna WHERE id_pengguna=$hid");
        $msg = 'Akun berhasil dihapus';
    }
}

// RESET PASSWORD
if (isset($_GET['reset'])) {

    $rid  = (int) $_GET['reset'];
    $hash = password_hash('Password123', $PASSWORD_DEFAULT ?? PASSWORD_DEFAULT);

    $st = $conn->prepare("
        UPDATE tb_pengguna
        SET password=?
        WHERE id_pengguna=?
    ");

    $st->bind_param('si', $hash, $rid);
    $st->execute();

    $msg = 'Password berhasil direset ke: Password123';
}

// TOGGLE STATUS
if (isset($_GET['toggle'])) {

    $tid = (int) $_GET['toggle'];

    $cur = $conn
        ->query("SELECT status FROM tb_pengguna WHERE id_pengguna=$tid")
        ->fetch_assoc()['status'];

    $new = $cur === 'aktif'
        ? 'nonaktif'
        : 'aktif';

    $conn->query("
        UPDATE tb_pengguna
        SET status='$new'
        WHERE id_pengguna=$tid
    ");

    $msg = "Status akun berhasil diubah ke: $new";
}

// SIMPAN AKUN BARU / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_u     = (int) ($_POST['id_pengguna'] ?? 0);
    $username = trim($_POST['username']);
    $role     = $_POST['role'];
    $id_peg   = (int) $_POST['id_pegawai'];
    $status   = $_POST['status'];
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$role || !$id_peg) {

        $err = 'Username, role, dan pegawai wajib diisi.';

    } else {

        if ($id_u) {

            // Edit - password opsional
            if ($password) {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $st = $conn->prepare("
                    UPDATE tb_pengguna
                    SET
                        username=?,
                        role=?,
                        id_pegawai=?,
                        status=?,
                        password=?
                    WHERE id_pengguna=?
                ");

                $st->bind_param(
                    'ssissi',
                    $username,
                    $role,
                    $id_peg,
                    $status,
                    $hash,
                    $id_u
                );

            } else {

                $st = $conn->prepare("
                    UPDATE tb_pengguna
                    SET
                        username=?,
                        role=?,
                        id_pegawai=?,
                        status=?
                    WHERE id_pengguna=?
                ");

                $st->bind_param(
                    'ssisi',
                    $username,
                    $role,
                    $id_peg,
                    $status,
                    $id_u
                );
            }

        } else {

            if (!$password) {

                $err = 'Password wajib diisi untuk akun baru.';

            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $st = $conn->prepare("
                    INSERT INTO tb_pengguna
                        (
                            username,
                            password,
                            role,
                            id_pegawai,
                            status
                        )
                    VALUES
                        (?, ?, ?, ?, ?)
                ");

                $st->bind_param(
                    'sssis',
                    $username,
                    $hash,
                    $role,
                    $id_peg,
                    $status
                );
            }
        }

        if (!$err) {

            isset($st) && $st->execute()
                ? $msg = 'Akun berhasil disimpan.'
                : $err = 'Gagal: ' . $conn->error;
        }
    }
}

$edit = null;

if (isset($_GET['edit'])) {

    $edit = $conn
        ->query("
            SELECT *
            FROM tb_pengguna
            WHERE id_pengguna=" . (int) $_GET['edit'])
        ->fetch_assoc();
}

$pegawais = $conn->query("
    SELECT
        id_pegawai,
        nama_lengkap,
        nip
    FROM tb_pegawai
    WHERE status='aktif'
    ORDER BY nama_lengkap
");

$users = $conn->query("
    SELECT
        u.*,
        p.nama_lengkap,
        p.nip
    FROM tb_pengguna u
    JOIN tb_pegawai p
        ON u.id_pegawai=p.id_pegawai
    ORDER BY
        u.role,
        p.nama_lengkap
");
?>

<div class="page-head">
    <div>
        <h2>Kelola Pengguna</h2>
        <p>Manajemen akun login sistem SPPD</p>
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
            <?= $edit ? 'Edit Akun' : 'Tambah Akun Baru' ?>
        </div>
    </div>

    <form method="POST">

        <div class="card-body">

            <input
                type="hidden"
                name="id_pengguna"
                value="<?= $edit['id_pengguna'] ?? 0 ?>"
            >

            <div class="form-grid">
                <div class="form-group">

                    <label class="form-label">
                        Pegawai
                        <span class="req">*</span>
                    </label>

                    <select
                        class="form-control"
                        name="id_pegawai"
                        required
                    >

                        <option value="">-- Pilih Pegawai --</option>

                        <?php
                        $pegawais->data_seek(0);

                        while ($pg = $pegawais->fetch_assoc()) :
                        ?>

                            <option
                                value="<?= $pg['id_pegawai'] ?>"
                                <?= ($edit['id_pegawai'] ?? '') == $pg['id_pegawai'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($pg['nama_lengkap']) ?>
                                (<?= htmlspecialchars($pg['nip']) ?>)
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Username
                        <span class="req">*</span>
                    </label>

                    <input
                        class="form-control"
                        name="username"
                        value="<?= htmlspecialchars($edit['username'] ?? '') ?>"
                        placeholder="Contoh: budi.santoso"
                        required
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Role / Hak Akses
                        <span class="req">*</span>
                    </label>

                    <select
                        class="form-control"
                        name="role"
                        required
                    >

                        <option value="">-- Pilih Role --</option>

                        <option
                            value="pegawai"
                            <?= ($edit['role'] ?? '') === 'pegawai' ? 'selected' : '' ?>
                        >
                            Pegawai
                        </option>

                        <option
                            value="admin"
                            <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>
                        >
                            Admin
                        </option>

                        <option
                            value="pimpinan"
                            <?= ($edit['role'] ?? '') === 'pimpinan' ? 'selected' : '' ?>
                        >
                            Pimpinan
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Password
                        <?= $edit ? '(kosongkan jika tidak diubah)' : '<span class="req">*</span>' ?>
                    </label>

                    <input
                        class="form-control"
                        type="password"
                        name="password"
                        placeholder="<?= $edit ? 'Biarkan kosong jika tidak ganti' : 'Minimal 8 karakter' ?>"
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        class="form-control"
                        name="status"
                    >

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

            <div
                class="alert alert-warning"
                style="margin-top:12px;margin-bottom:0"
            >
                &#9888; Password default untuk akun baru sebaiknya diberitahukan langsung kepada pegawai dan diminta segera diganti.
            </div>

        </div>

        <div class="form-footer">

            <?php if ($edit) : ?>

                <a
                    href="/sppd/pages/master/pengguna.php"
                    class="btn btn-secondary"
                >
                    Batal Edit
                </a>

            <?php endif; ?>

            <button
                type="submit"
                class="btn btn-primary"
            >
                &#128190; Simpan Akun
            </button>

        </div>

    </form>

</div>

<div class="card">

    <div class="card-header">

        <div class="card-title">
            Daftar Akun Pengguna
        </div>

    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Nama Pegawai</th>
                    <th>NIP</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

                <?php
                $no = 1;

                while ($r = $users->fetch_assoc()) :

                    $role_badge = [
                        'admin'     => 'badge-red',
                        'pegawai'   => 'badge-blue',
                        'pimpinan'  => 'badge-amber'
                    ];
                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td>
                            <strong><?= htmlspecialchars($r['username']) ?></strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($r['nama_lengkap']) ?>
                        </td>

                        <td style="font-size:11px">
                            <?= htmlspecialchars($r['nip']) ?>
                        </td>

                        <td>
                            <span class="badge <?= $role_badge[$r['role']] ?? 'badge-gray' ?>">
                                <?= ucfirst($r['role']) ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= $r['status'] === 'aktif' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>

                        <td>

                            <div style="display:flex;gap:5px;flex-wrap:wrap">

                                <a
                                    href="?edit=<?= $r['id_pengguna'] ?>"
                                    class="btn btn-outline btn-sm"
                                >
                                    Edit
                                </a>

                                <a
                                    href="?reset=<?= $r['id_pengguna'] ?>"
                                    class="btn btn-secondary btn-sm"
                                    onclick="return confirm('Reset password ke Password123?')"
                                >
                                    Reset PW
                                </a>

                                <a
                                    href="?toggle=<?= $r['id_pengguna'] ?>"
                                    class="btn btn-<?= $r['status'] === 'aktif' ? 'warning' : 'success' ?> btn-sm"
                                >
                                    <?= $r['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </a>

                                <?php if ($r['id_pengguna'] != $_SESSION['user_id']) : ?>

                                    <a
                                        href="?hapus=<?= $r['id_pengguna'] ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus akun ini?')"
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

<?php require_once '../../includes/footer.php'; ?>