<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /sppd/pages/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { die('Akses ditolak.'); }
require_once '../../config/koneksi.php';

$unit  = $_GET['unit'] ?? '';
$status = $_GET['status'] ?? '';

$where = ['1=1'];
if ($unit)   $where[] = "p.unit_kerja = '" . $conn->real_escape_string($unit) . "'";
if ($status) $where[] = "p.status = '" . $conn->real_escape_string($status) . "'";

$result = $conn->query("
    SELECT p.*, j.nama_jabatan, j.golongan, j.pangkat
    FROM tb_pegawai p
    JOIN tb_jabatan j ON p.id_jabatan = j.id_jabatan
    WHERE " . implode(' AND ', $where) . "
    ORDER BY p.nama_lengkap ASC
");

$units = $conn->query("SELECT DISTINCT unit_kerja FROM tb_pegawai WHERE unit_kerja IS NOT NULL AND unit_kerja != '' ORDER BY unit_kerja");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Data Pegawai</title>
<link rel="stylesheet" href="/sppd/assets/css/style.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Times New Roman', serif; font-size: 11pt; color: #000; background: white; }
  .no-print { font-family: var(--font); }
  .print-page { width: 210mm; min-height: 297mm; padding: 15mm 20mm; margin: auto; background: white; }
  .kop { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 16px; }
  .kop-title { font-size: 14pt; font-weight: bold; }
  .kop-sub { font-size: 11pt; }
  .kop-addr { font-size: 10pt; margin-top: 2px; }
  .doc-title { text-align: center; margin: 16px 0; font-size: 12pt; font-weight: bold; text-decoration: underline; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10pt; }
  th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
  th { background: #eee; font-weight: bold; text-align: center; }
  .ttd { margin-top: 40px; display: flex; justify-content: flex-end; }
  .ttd-box { text-align: center; width: 250px; }
  .ttd-box .space { height: 70px; }
  .ttd-box .nama { font-weight: bold; text-decoration: underline; }
  @media print {
    .no-print { display: none !important; }
    .print-page { margin: 0; padding: 15mm 20mm; }
  }
</style>
</head>
<body>
<div class="no-print" style="padding:14px 24px;background:#1B4F8A;display:flex;align-items:center;justify-content:space-between">
  <span style="color:white;font-size:14px;font-weight:600">Preview Cetak Data Pegawai</span>
  <div style="display:flex;gap:10px">
    <a href="/sppd/pages/master/pegawai.php" style="padding:7px 16px;background:rgba(255,255,255,.15);color:white;border-radius:6px;font-size:13px">&#8592; Kembali</a>
    <button onclick="window.print()" style="padding:7px 18px;background:#D4A017;color:white;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer">&#128424; Cetak / Simpan PDF</button>
  </div>
</div>

<form class="no-print" method="GET" style="padding:16px 24px;display:flex;gap:12px;align-items:flex-end;background:#f5f5f5;border-bottom:1px solid #ddd">
  <div style="display:flex;flex-direction:column;gap:4px">
    <label style="font-size:12px;font-weight:500">Unit Kerja</label>
    <select name="unit" style="padding:6px 12px;border:1px solid #ccc;border-radius:4px;font-size:13px">
      <option value="">Semua Unit</option>
      <?php $units->data_seek(0); while ($u = $units->fetch_assoc()): ?>
      <option value="<?= htmlspecialchars($u['unit_kerja']) ?>" <?= $unit === $u['unit_kerja'] ? 'selected' : '' ?>><?= htmlspecialchars($u['unit_kerja']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div style="display:flex;flex-direction:column;gap:4px">
    <label style="font-size:12px;font-weight:500">Status</label>
    <select name="status" style="padding:6px 12px;border:1px solid #ccc;border-radius:4px;font-size:13px">
      <option value="">Semua Status</option>
      <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
      <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Non-aktif</option>
    </select>
  </div>
  <button type="submit" style="padding:6px 18px;background:#1B4F8A;color:white;border:none;border-radius:4px;cursor:pointer;font-size:13px">Filter</button>
  <a href="cetak_pegawai.php" style="padding:6px 18px;background:#6c757d;color:white;border-radius:4px;font-size:13px">Reset</a>
</form>

<div class="print-page">
  <div class="kop">
    <div class="kop-title">PEMERINTAH KOTA KUALA KAPUAS</div>
    <div class="kop-sub">BADAN KESATUAN BANGSA DAN POLITIK</div>
    <div class="kop-addr">Jl. ........................ No. ..., Kuala Kapuas, Kalimantan Tengah</div>
  </div>

  <div class="doc-title">DATA PEGAWAI</div>
  <?php if ($unit): ?>
  <p style="margin-bottom:8px;font-size:10pt">Unit Kerja: <strong><?= htmlspecialchars($unit) ?></strong></p>
  <?php endif; ?>
  <?php if ($status): ?>
  <p style="margin-bottom:8px;font-size:10pt">Status: <strong><?= ucfirst($status) ?></strong></p>
  <?php endif; ?>
  <p style="margin-bottom:8px;font-size:10pt">Jumlah Pegawai: <strong><?= $result->num_rows ?> orang</strong></p>

  <table>
    <thead>
      <tr>
        <th style="width:30px">No</th>
        <th>NIP</th>
        <th>Nama Lengkap</th>
        <th>Jabatan</th>
        <th>Gol</th>
        <th>Jenis</th>
        <th>Unit Kerja</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while ($r = $result->fetch_assoc()): ?>
      <tr>
        <td style="text-align:center"><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['nip']) ?></td>
        <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
        <td><?= htmlspecialchars($r['nama_jabatan']) ?></td>
        <td style="text-align:center"><?= htmlspecialchars($r['golongan']) ?></td>
        <td style="text-align:center"><?= htmlspecialchars($r['jenis_pegawai'] ?? 'PNS') ?></td>
        <td><?= htmlspecialchars($r['unit_kerja'] ?? '-') ?></td>
        <td style="text-align:center"><?= ucfirst($r['status']) ?></td>
      </tr>
      <?php endwhile; ?>
      <?php if ($result->num_rows === 0): ?>
      <tr><td colspan="8" style="text-align:center;padding:20px">Tidak ada data pegawai</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="ttd">
    <div class="ttd-box">
      <p>Kuala Kapuas, ...........................</p>
      <p>Kepala Badan,</p>
      <div class="space"></div>
      <p class="nama">.............................................</p>
      <p>NIP. .............................................</p>
    </div>
  </div>
</div>
</body>
</html>
