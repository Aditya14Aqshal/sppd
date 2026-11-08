<?php

$halaman   = 'Master Pasien';
$sub_judul = 'Master Data / Pasien';
$aktif     = 'm_pasien';

require_once '../../config/koneksi.php';
require_once '../../includes/header.php';




if ($_SESSION['role'] !== 'admin') {

    echo '<div class="alert alert-danger">Akses ditolak.</div>';

    require_once '../../includes/footer.php';

    exit;
}


$msg = '';
$err = '';


/* =========================================================
   DELETE
   ========================================================= */

if (isset($_GET['hapus'])) {

    $no = (int) $_GET['hapus'];

    $st = $conn->prepare("
        DELETE FROM tb_pasien
        WHERE no = ?
    ");

    $st->bind_param('i', $no);

    if ($st->execute()) {

        $msg = 'Data pasien berhasil dihapus.';

    } else {

        $err = 'Gagal menghapus data: ' . $st->error;

    }
}


/* =========================================================
   SAVE / UPDATE
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $edit_no = isset($_GET['no'])
        ? (int) $_GET['no']
        : 0;


    $nama = trim($_POST['nama'] ?? '');

    $alamat = trim($_POST['alamat'] ?? '');

    $dokter = trim($_POST['dokter'] ?? '');

    $keluhan = trim($_POST['keluhan'] ?? '');

    $rawat = $_POST['rawat'] ?? '';


    /* =====================================================
       VALIDASI
       ===================================================== */

    if (
        $nama === '' ||
        $alamat === '' ||
        $dokter === '' ||
        $keluhan === '' ||
        $rawat === ''
    ) {

        $err = 'Nama, alamat, dokter, keluhan, dan rawat wajib diisi.';

    } elseif (!in_array($rawat, ['inap', 'jalan'], true)) {

        $err = 'Jenis rawat tidak valid.';

    } else {


        /* =================================================
           UPDATE DATA
           ================================================= */

        if ($edit_no > 0) {

            $st = $conn->prepare("

                UPDATE tb_pasien

                SET
                    nama = ?,
                    alamat = ?,
                    dokter = ?,
                    keluhan = ?,
                    rawat = ?

                WHERE no = ?

            ");

            $st->bind_param(
                'sssssi',
                $nama,
                $alamat,
                $dokter,
                $keluhan,
                $rawat,
                $edit_no
            );


            if ($st->execute()) {

                $msg = 'Data pasien berhasil diperbarui.';

                /*
                 * Setelah berhasil edit,
                 * hapus mode edit dari URL.
                 */

                header('Location: pasien.php?msg=updated');

                exit;

            } else {

                $err = 'Gagal memperbarui data: ' . $st->error;

            }


        /* =================================================
           INSERT DATA
           ================================================= */

        } else {

            $st = $conn->prepare("

                INSERT INTO tb_pasien
                (
                    nama,
                    alamat,
                    dokter,
                    keluhan,
                    rawat
                )

                VALUES (?, ?, ?, ?, ?)

            ");

            $st->bind_param(
                'sssss',
                $nama,
                $alamat,
                $dokter,
                $keluhan,
                $rawat
            );


            if ($st->execute()) {

                $msg = 'Data pasien berhasil disimpan.';

            } else {

                $err = 'Gagal menyimpan data: ' . $st->error;

            }
        }
    }
}


/* =========================================================
   PESAN SETELAH UPDATE
   ========================================================= */

if (isset($_GET['msg'])) {

    if ($_GET['msg'] === 'updated') {

        $msg = 'Data pasien berhasil diperbarui.';

    }

}



   //AMBIL DATA UNTUK EDIT


$edit = null;

if (isset($_GET['edit'])) {

    $eid = (int) $_GET['edit'];

    $st = $conn->prepare("

        SELECT
            no,
            nama,
            alamat,
            dokter,
            keluhan,
            rawat

        FROM tb_pasien

        WHERE no = ?

    ");

    $st->bind_param('i', $eid);

    $st->execute();

    $result = $st->get_result();

    $edit = $result->fetch_assoc();


    /*
     * Jika nomor tidak ditemukan
     */

    if (!$edit) {

        $err = 'Data pasien tidak ditemukan.';

    }
}


/* =========================================================
   DATA PASIEN
   ========================================================= */

$pasiens = $conn->query("

    SELECT
        no,
        nama,
        alamat,
        dokter,
        keluhan,
        rawat

    FROM tb_pasien

    ORDER BY no DESC

");

?>

<!-- =======================================================
     PAGE HEADER
     ======================================================= -->

<div class="page-head">

```
<div>

    <h2>Master Pasien</h2>

    <p>Kelola data pasien</p>

</div>
```

</div>

<!-- =======================================================
     ALERT SUCCESS
     ======================================================= -->

<?php if ($msg): ?>

```
<div class="alert alert-success">

    <?= htmlspecialchars($msg) ?>

</div>
```

<?php endif; ?>

<!-- =======================================================
     ALERT ERROR
     ======================================================= -->

<?php if ($err): ?>

```
<div class="alert alert-danger">

    <?= htmlspecialchars($err) ?>

</div>
```

<?php endif; ?>

<!-- =======================================================
     FORM PASIEN
     ======================================================= -->

<div class="card">

```
<div class="card-header">

    <div class="card-title">

        <?= $edit
            ? 'Edit Data Pasien'
            : 'Tambah Pasien Baru'
        ?>

    </div>

</div>




<form
    method="POST"
    action="<?= $edit
        ? '?edit=' . (int)$edit['no']
        : ''
    ?>"
>

    <div class="card-body">

        <div class="form-grid">


            <!-- =================================================
                 NAMA
                 ================================================= -->

            <div class="form-group">

                <label class="form-label">

                    Nama
                    <span class="req">*</span>

                </label>


                <input
                    type="text"
                    class="form-control"
                    name="nama"
                    value="<?= htmlspecialchars(
                        $edit['nama']
                        ?? $_POST['nama']
                        ?? ''
                    ) ?>"
                    placeholder="Masukkan nama pasien"
                    required
                >

            </div>


            <!-- =================================================
                 ALAMAT
                 ================================================= -->

            <div class="form-group">

                <label class="form-label">

                    Alamat
                    <span class="req">*</span>

                </label>


                <input
                    type="text"
                    class="form-control"
                    name="alamat"
                    value="<?= htmlspecialchars(
                        $edit['alamat']
                        ?? $_POST['alamat']
                        ?? ''
                    ) ?>"
                    placeholder="Masukkan alamat pasien"
                    required
                >

            </div>


            <!-- =================================================
                 DOKTER
                 ================================================= -->

            <div class="form-group">

                <label class="form-label">

                    Dokter
                    <span class="req">*</span>

                </label>


                <input
                    type="text"
                    class="form-control"
                    name="dokter"
                    value="<?= htmlspecialchars(
                        $edit['dokter']
                        ?? $_POST['dokter']
                        ?? ''
                    ) ?>"
                    placeholder="Masukkan nama dokter"
                    required
                >

            </div>


            <!-- =================================================
                 KELUHAN
                 ================================================= -->

            <div class="form-group">

                <label class="form-label">

                    Keluhan
                    <span class="req">*</span>

                </label>


                <input
                    type="text"
                    class="form-control"
                    name="keluhan"
                    value="<?= htmlspecialchars(
                        $edit['keluhan']
                        ?? $_POST['keluhan']
                        ?? ''
                    ) ?>"
                    placeholder="Masukkan keluhan pasien"
                    required
                >

            </div>


            <!-- =================================================
                 RAWAT
                 ENUM('inap','jalan')
                 ================================================= -->

            <div class="form-group">

                <label class="form-label">

                    Rawat
                    <span class="req">*</span>

                </label>


                <select
                    class="form-control"
                    name="rawat"
                    required
                >

                    <option value="">
                        -- Pilih Rawat --
                    </option>


                    <option
                        value="inap"
                        <?= (
                            $edit['rawat']
                            ?? $_POST['rawat']
                            ?? ''
                        ) === 'inap'
                            ? 'selected'
                            : ''
                        ?>
                    >

                        Inap

                    </option>


                    <option
                        value="jalan"
                        <?= (
                            $edit['rawat']
                            ?? $_POST['rawat']
                            ?? ''
                        ) === 'jalan'
                            ? 'selected'
                            : ''
                        ?>
                    >

                        Jalan

                    </option>

                </select>

            </div>


        </div>

    </div>


    <!-- =======================================================
         FOOTER FORM
         ======================================================= -->

    <div class="form-footer">


        <?php if ($edit): ?>

            <a
                href="pasien.php"
                class="btn btn-secondary"
            >

                Batal Edit

            </a>

        <?php endif; ?>


        <button
            type="submit"
            class="btn btn-primary"
        >

            &#128190; Simpan Data

        </button>


    </div>

</form>
```

</div>

<!-- =======================================================
     TABEL DATA PASIEN
     ======================================================= -->

<div class="card">

```
<div class="card-header">


    <div class="card-title">

        Daftar Pasien
        (<?= $pasiens->num_rows ?> orang)

    </div>


</div>


<div class="table-wrap">


    <table>


        <thead>

            <tr>

                <th>No</th>

                <th>Nama</th>

                <th>Alamat</th>

                <th>Dokter</th>

                <th>Keluhan</th>

                <th>Rawat</th>

                <th>Aksi</th>

            </tr>

        </thead>


        <tbody>


            <?php

            $no_urut = 1;

            while ($r = $pasiens->fetch_assoc()):

            ?>


                <tr>


                    <td>

                        <?= $no_urut++ ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $r['nama']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $r['alamat']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $r['dokter']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $r['keluhan']
                        ) ?>

                    </td>


                    <td>

                        <span class="badge badge-blue">

                            <?= htmlspecialchars(
                                ucfirst($r['rawat'])
                            ) ?>

                        </span>

                    </td>


                    <td style="display:flex; gap:6px;">


                        <!-- EDIT -->

                        <a
                            href="?edit=<?= (int)$r['no'] ?>"
                            class="btn btn-outline btn-sm"
                        >

                            Edit

                        </a>


                        <!-- HAPUS -->

                        <a
                            href="?hapus=<?= (int)$r['no'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Hapus pasien ini?')"
                        >

                            Hapus

                        </a>


                    </td>


                </tr>


            <?php endwhile; ?>


        </tbody>


    </table>


</div>
```

</div>

<?php

require_once '../../includes/footer.php';

?>
