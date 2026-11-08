<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /sppd/pages/auth/login.php');
    exit;
}
$role      = $_SESSION['role']      ?? '';
$nama      = $_SESSION['nama']      ?? 'Pengguna';
$inisial   = strtoupper(substr($nama, 0, 1));
$halaman   = $halaman   ?? 'Dashboard';
$sub_judul = $sub_judul ?? '';
$aktif     = $aktif     ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($halaman) ?> — SPPD Bakesbangpol</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-wrap">
      <div class="logo-icon">B</div>
      <div class="logo-text">
        <div class="logo-title">SPPD Bakesbangpol</div>
        <div class="logo-sub">Kota Kuala Kapuas</div>
      </div>
    </div>
  </div>

  <nav class="sidebar-menu">
    <div class="menu-section">Utama</div>
    <a class="menu-item <?= $aktif==='dashboard' ? 'active':'' ?>" href="/sppd/pages/dashboard/">
      <span class="mi-icon">&#9632;</span> Dashboard
    </a>

    <?php if ($role === 'pegawai' || $role === 'admin'): ?>
    <div class="menu-section">SPPD</div>
    <a class="menu-item <?= $aktif==='sppd_ajukan' ? 'active':'' ?>" href="/sppd/pages/sppd/ajukan.php">
      <span class="mi-icon">&#43;</span> Ajukan SPPD
    </a>
    <a class="menu-item <?= $aktif==='sppd_list' ? 'active':'' ?>" href="/sppd/pages/sppd/daftar.php">
      <span class="mi-icon">&#9776;</span> Daftar SPPD
    </a>
    <?php endif; ?>

    <?php if ($role === 'pimpinan'): ?>
    <div class="menu-section">Persetujuan</div>
    <a class="menu-item <?= $aktif==='approval' ? 'active':'' ?>" href="/sppd/pages/sppd/approval.php">
      <span class="mi-icon">&#10003;</span> Persetujuan SPPD
    </a>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
    <div class="menu-section">Master Data</div>
    <a class="menu-item <?= $aktif==='m_pegawai' ? 'active':'' ?>" href="/sppd/pages/master/pegawai.php">
      <span class="mi-icon">&#128100;</span> Pegawai
    </a>
    <a class="menu-item <?= $aktif==='m_jabatan' ? 'active':'' ?>" href="/sppd/pages/master/jabatan.php">
      <span class="mi-icon">&#127970;</span> Jabatan & Golongan
    </a>
    <a class="menu-item <?= $aktif==='m_tujuan' ? 'active':'' ?>" href="/sppd/pages/master/tujuan.php">
      <span class="mi-icon">&#128205;</span> Tujuan / Instansi
    </a>
    <a class="menu-item <?= $aktif==='m_kegiatan' ? 'active':'' ?>" href="/sppd/pages/master/kegiatan.php">
      <span class="mi-icon">&#128203;</span> Jenis Kegiatan
    </a>
    <a class="menu-item <?= $aktif==='m_anggaran' ? 'active':'' ?>" href="/sppd/pages/master/anggaran.php">
      <span class="mi-icon">&#128181;</span> Sumber Anggaran
    </a>
    <a class="menu-item <?= $aktif==='m_biaya' ? 'active':'' ?>" href="/sppd/pages/master/komponen_biaya.php">
      <span class="mi-icon">&#128200;</span> Komponen Biaya
    </a>
    <a class="menu-item <?= $aktif==='m_pengguna' ? 'active':'' ?>" href="/sppd/pages/master/pengguna.php">
      <span class="mi-icon">&#128275;</span> Kelola Pengguna
    </a>
    <a class="menu-item <?= $aktif==='m_unit_kerja' ? 'active':'' ?>" href="/sppd/pages/master/unit_kerja.php">
      <span class="mi-icon">&#128195;</span> Unit Kerja / Bidang
    </a>
    <a class="menu-item <?= $aktif==='m_dasar_hukum' ? 'active':'' ?>" href="/sppd/pages/master/dasar_hukum.php">
      <span class="mi-icon">&#128214;</span> Dasar Hukum
    </a>
    <a class="menu-item <?= $aktif==='m_pasien' ? 'active':'' ?>" href="/sppd/pages/master/pasien.php">
      <span class="mi-icon">&#128214;</span> Daftar Pasien
    </a>
    <?php endif; ?>

    <?php if ($role !== 'pegawai'): ?>
    <div class="menu-section">Laporan</div>
    <a class="menu-item <?= $aktif==='lap_rekap' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_sppd.php">
      <span class="mi-icon">&#128196;</span> Rekap SPPD
    </a>
    <a class="menu-item <?= $aktif==='lap_bulan' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_bulan.php">
      <span class="mi-icon">&#128197;</span> Rekap per Bulan
    </a>
    <a class="menu-item <?= $aktif==='lap_pegawai' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_pegawai.php">
      <span class="mi-icon">&#128200;</span> Rekap per Pegawai
    </a>
    <a class="menu-item <?= $aktif==='lap_unit' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_unit.php">
      <span class="mi-icon">&#127970;</span> Rekap per Unit Kerja
    </a>
    <a class="menu-item <?= $aktif==='lap_kegiatan' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_kegiatan.php">
      <span class="mi-icon">&#128203;</span> Rekap per Kegiatan
    </a>
    <a class="menu-item <?= $aktif==='lap_tujuan' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_tujuan.php">
      <span class="mi-icon">&#128205;</span> Rekap per Tujuan
    </a>
    <a class="menu-item <?= $aktif==='lap_status' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_status.php">
      <span class="mi-icon">&#128202;</span> Status SPPD
    </a>
    <?php if ($role === 'admin' || $role === 'pimpinan'): ?>
    <a class="menu-item <?= $aktif==='lap_anggaran' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_anggaran.php">
      <span class="mi-icon">&#128184;</span> Rekap Anggaran
    </a>
    <?php endif; ?>
    <a class="menu-item <?= $aktif==='lap_pegawai_aktif' ? 'active':'' ?>" href="/sppd/pages/laporan/rekap_pegawai_aktif.php">
      <span class="mi-icon">&#128170;</span> Pegawai Teraktif
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    v1.0 &copy; 2026 Bakesbangpol
  </div>
</aside>

<div class="topbar">
  <div class="topbar-left">
    <div class="page-title"><?= htmlspecialchars($halaman) ?></div>
    <?php if ($sub_judul): ?>
    <div class="breadcrumb">Beranda / <span><?= htmlspecialchars($sub_judul) ?></span></div>
    <?php endif; ?>
  </div>
  <div class="topbar-right">
    <div class="user-info">
      <div class="user-avatar"><?= $inisial ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($nama) ?></div>
        <div class="user-role"><?= ucfirst($role) ?></div>
      </div>
    </div>
    <a href="/sppd/pages/auth/logout.php" class="btn-logout">Keluar</a>
  </div>
</div>

<div class="main">
  <div class="content">
