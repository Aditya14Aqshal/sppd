<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /sppd/pages/dashboard/');
    exit;
}
require_once '../../config/koneksi.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $stmt = $conn->prepare("
            SELECT u.id_pengguna, u.password, u.role, u.status, p.nama_lengkap
            FROM tb_pengguna u
            JOIN tb_pegawai p ON u.id_pegawai = p.id_pegawai
            WHERE u.username = ?
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res && $res['status'] === 'aktif' && password_verify($password, $res['password'])) {
            $_SESSION['user_id'] = $res['id_pengguna'];
            $_SESSION['role']    = $res['role'];
            $_SESSION['nama']    = $res['nama_lengkap'];
            header('Location: /sppd/pages/dashboard/');
            exit;
        } else {
            $error = 'Username atau password salah, atau akun tidak aktif.';
        }
    } else {
        $error = 'Username dan password wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — SPPD Bakesbangpol</title>
  <link rel="stylesheet" href="/sppd/assets/css/style.css">
  <style>
    body { background: var(--gray-50); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .login-wrap { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; width: 100%; }
    .login-left {
      background: var(--primary-dk);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 48px; position: relative; overflow: hidden;
    }
    .login-left::before {
      content: ''; position: absolute; inset: 0;
      background: repeating-linear-gradient(45deg, rgba(255,255,255,.02) 0, rgba(255,255,255,.02) 1px, transparent 1px, transparent 40px);
    }
    .left-logo {
      width: 72px; height: 72px; border-radius: 18px;
      background: var(--accent); display: flex; align-items: center; justify-content: center;
      font-size: 32px; font-weight: 700; color: white; margin-bottom: 24px;
      position: relative; z-index: 1;
    }
    .left-title {
      font-family: 'Lora', serif; font-size: 26px; color: white;
      text-align: center; line-height: 1.35; margin-bottom: 12px;
      position: relative; z-index: 1;
    }
    .left-sub {
      font-size: 13px; color: rgba(255,255,255,.5);
      text-align: center; line-height: 1.6; position: relative; z-index: 1;
    }
    .left-divider {
      width: 48px; height: 2px; background: var(--accent);
      margin: 20px auto; position: relative; z-index: 1;
    }
    .left-features { margin-top: 36px; display: flex; flex-direction: column; gap: 14px; position: relative; z-index: 1; width: 100%; max-width: 280px; }
    .feat-item { display: flex; align-items: center; gap: 12px; }
    .feat-icon { width: 34px; height: 34px; border-radius: 8px; background: rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; font-size: 14px; color: var(--accent); }
    .feat-text { font-size: 12px; color: rgba(255,255,255,.65); line-height: 1.4; }
    .feat-text strong { display: block; color: white; font-size: 13px; }
    .login-right {
      display: flex; align-items: center; justify-content: center;
      padding: 48px; background: white;
    }
    .login-box { width: 100%; max-width: 380px; }
    .login-heading { font-size: 22px; font-weight: 600; color: var(--gray-800); margin-bottom: 6px; }
    .login-sub { font-size: 13px; color: var(--gray-400); margin-bottom: 32px; }
    .field-wrap { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
    .field-label { font-size: 12px; font-weight: 500; color: var(--gray-600); }
    .field-input {
      height: 44px; border: 1.5px solid var(--gray-200); border-radius: var(--radius);
      padding: 0 14px; font-size: 14px; color: var(--gray-800);
      transition: border-color .15s;
    }
    .field-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(27,79,138,.08); }
    .btn-login {
      width: 100%; height: 46px; background: var(--primary); color: white;
      border: none; border-radius: var(--radius); font-size: 15px; font-weight: 600;
      cursor: pointer; margin-top: 8px; transition: background .15s;
    }
    .btn-login:hover { background: var(--primary-dk); }
    .login-footer { text-align: center; font-size: 12px; color: var(--gray-400); margin-top: 32px; }
    .alert-danger { margin-bottom: 20px; }
    @media (max-width: 768px) {
      .login-wrap { grid-template-columns: 1fr; }
      .login-left { display: none; }
    }
  </style>
</head>
<body>
<div class="login-wrap">
  <div class="login-left">
    <div class="left-logo">B</div>
    <div class="left-title">Sistem Informasi<br>SPPD Bakesbangpol</div>
    <div class="left-divider"></div>
    <div class="left-sub">Badan Kesatuan Bangsa dan Politik<br>Kota Kuala Kapuas</div>
    <div class="left-features">
      <div class="feat-item">
        <div class="feat-icon">&#128196;</div>
        <div class="feat-text"><strong>Pengajuan Digital</strong>Ajukan SPPD kapan saja secara online</div>
      </div>
      <div class="feat-item">
        <div class="feat-icon">&#10003;</div>
        <div class="feat-text"><strong>Persetujuan Cepat</strong>Notifikasi langsung ke pimpinan</div>
      </div>
      <div class="feat-item">
        <div class="feat-icon">&#128200;</div>
        <div class="feat-text"><strong>Laporan Otomatis</strong>Rekap dan cetak laporan real-time</div>
      </div>
    </div>
  </div>

  <div class="login-right">
    <div class="login-box">
      <div class="login-heading">Selamat datang</div>
      <div class="login-sub">Masukkan kredensial Anda untuk melanjutkan</div>
      <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="field-wrap">
          <label class="field-label">Username</label>
          <input class="field-input" type="text" name="username" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
        </div>
        <div class="field-wrap">
          <label class="field-label">Password</label>
          <input class="field-input" type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn-login">Masuk ke Sistem</button>
      </form>
      <div class="login-footer">
        &copy; 2026 Bakesbangpol Kota Kuala Kapuas<br>Sistem Informasi SPPD v1.0
      </div>
    </div>
  </div>
</div>
</body>
</html>
