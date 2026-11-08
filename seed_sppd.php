<?php
require_once 'config/koneksi.php';

echo "<h3>🌱 Seeding 10 Data SPPD + Anggota + Biaya</h3>";

// Ambil referensi data yang ada
$pegawais   = $conn->query("SELECT id_pegawai, nama_lengkap FROM tb_pegawai");
$tujuans    = $conn->query("SELECT id_tujuan, nama_instansi, kota FROM tb_tujuan");
$kegiatans  = $conn->query("SELECT id_kegiatan, nama_kegiatan FROM tb_jenis_kegiatan WHERE status='aktif'");
$anggarans  = $conn->query("SELECT id_anggaran, kode_anggaran FROM tb_anggaran WHERE status='aktif'");
$jabatans   = $conn->query("SELECT id_jabatan, golongan FROM tb_jabatan");

$peg = []; while ($r = $pegawais->fetch_assoc()) $peg[] = $r;
$tuj = []; while ($r = $tujuans->fetch_assoc()) $tuj[] = $r;
$keg = []; while ($r = $kegiatans->fetch_assoc()) $keg[] = $r;
$ang = []; while ($r = $anggarans->fetch_assoc()) $ang[] = $r;
$jab = []; while ($r = $jabatans->fetch_assoc()) $jab[] = $r;

if (count($peg) < 2 || count($tuj) < 1 || count($keg) < 1 || count($ang) < 1) {
    die("❌ Data referensi tidak mencukupi. Minimal 2 pegawai, 1 tujuan, 1 kegiatan, 1 anggaran.");
}

$statuses = ['draf', 'diajukan', 'diverifikasi', 'disetujui', 'ditolak', 'selesai'];
$kendaraans = ['Kendaraan Dinas', 'Kendaraan Pribadi', 'Transportasi Umum', 'Pesawat Terbang'];

// Hapus data SPPD lama (kaskade ke tb_sppd_anggota, tb_biaya_perjalanan, tb_laporan_dinas)
$conn->query("DELETE FROM tb_laporan_dinas");
$conn->query("DELETE FROM tb_biaya_perjalanan");
$conn->query("DELETE FROM tb_sppd_anggota");
$conn->query("DELETE FROM tb_sppd");
echo "✅ Data SPPD lama dibersihkan<br>";

// Data SPPD sample (10 records spanning Jan-Jun 2026)
$sppd_data = [
    // [id_pengaju, id_kegiatan, id_tujuan, id_anggaran, keperluan, tgl_berangkat, tgl_kembali, kendaraan, status, no_sppd, id_pimpinan, tgl_persetujuan, catatan_tolak]
    [0,0,0,0, 'Rapat Koordinasi Forkopimda',                '2026-01-12','2026-01-14','Kendaraan Dinas',    'selesai',    '094/001/BKBP/I/2026',    1, '2026-01-11 09:00:00', null],
    [0,0,0,0, 'Sosialisasi Program Ketahanan Bangsa',        '2026-02-03','2026-02-05','Kendaraan Dinas',    'selesai',    '094/002/BKBP/II/2026',   1, '2026-02-02 10:00:00', null],
    [0,0,0,0, 'Bimbingan Teknis Pengelolaan Konflik',        '2026-02-17','2026-02-20','Pesawat Terbang',    'disetujui',  '094/003/BKBP/II/2026',   1, '2026-02-15 08:30:00', null],
    [0,0,0,0, 'Monitoring dan Evaluasi Ormas',               '2026-03-03','2026-03-04','Kendaraan Dinas',    'diverifikasi',null,                     null, null,                null],
    [0,0,0,0, 'Forum Diskusi Lintas Sektoral',               '2026-03-16','2026-03-16','Transportasi Umum',  'ditolak',    null,                      1, '2026-03-14 13:00:00', 'Materi rapat belum lengkap, harap dilengkapi'],
    [0,0,0,0, 'Rapat Persiapan Pemilu 2026',                 '2026-04-06','2026-04-08','Kendaraan Dinas',    'disetujui',  '094/004/BKBP/IV/2026',   1, '2026-04-04 11:00:00', null],
    [0,0,0,0, 'Sosialisasi Wawasan Kebangsaan',              '2026-04-20','2026-04-22','Transportasi Umum',  'diajukan',   null,                     1, null,                null],
    [0,0,0,0, 'Bimtek Pengelolaan Anggaran Perjalanan Dinas','2026-05-05','2026-05-08','Pesawat Terbang',    'diverifikasi',null,                     null, null,                null],
    [0,0,0,0, 'Rapat Evaluasi Program Semester I',           '2026-05-19','2026-05-20','Kendaraan Dinas',    'selesai',    '094/005/BKBP/V/2026',    1, '2026-05-17 09:00:00', null],
    [0,0,0,0, 'Forum Koordinasi Pimpinan Daerah',            '2026-06-09','2026-06-11','Kendaraan Dinas',    'selesai',    '094/006/BKBP/VI/2026',   1, '2026-06-07 13:30:00', null],
];

$no_sppd_map = [
    ['094/001/BKBP/I/2026', '094/002/BKBP/II/2026', '094/003/BKBP/II/2026', null, null,
     '094/004/BKBP/IV/2026', null, null, '094/005/BKBP/V/2026', '094/006/BKBP/VI/2026']
];

$inserted_ids = [];
$stmt = $conn->prepare("INSERT INTO tb_sppd 
    (id_pengaju, id_kegiatan, id_tujuan, id_anggaran, keperluan, tgl_berangkat, tgl_kembali, kendaraan, status, no_sppd, id_pimpinan, tgl_persetujuan, catatan_tolak, transportasi_nama, transportasi_no_tiket, transportasi_biaya) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$i = 0;
foreach ($sppd_data as $d) {
    // Rotasi pegawai, kegiatan, tujuan, anggaran
    $d[0] = $peg[$i % count($peg)]['id_pegawai'];
    $d[1] = $keg[$i % count($keg)]['id_kegiatan'];
    $d[2] = $tuj[$i % count($tuj)]['id_tujuan'];
    $d[3] = $ang[$i % count($ang)]['id_anggaran'];

    // Detail transportasi sesuai moda
    $trans_nama = $trans_no_tiket = $trans_biaya = null;
    switch ($d[7]) {
        case 'Pesawat Terbang':
            $trans_nama     = ['Garuda Indonesia','Citilink','Batik Air','Lion Air'][$i % 4];
            $trans_no_tiket = 'GA-'.str_pad((string)(1000000 + $i * 137), 9, '0', STR_PAD_LEFT);
            $trans_biaya    = [1850000, 1250000, 1450000, 990000][$i % 4];
            break;
        case 'Transportasi Umum':
            $trans_nama     = ['Bus Sumber Rejeki','PO Haryanto','Taksi Koperasi'][$i % 3];
            $trans_no_tiket = 'TU-'.str_pad((string)(5000 + $i * 21), 6, '0', STR_PAD_LEFT);
            $trans_biaya    = [150000, 220000, 90000][$i % 3];
            break;
        case 'Kendaraan Pribadi':
            $trans_biaya    = [250000, 300000][$i % 2];
            break;
    }

    $stmt->bind_param('iiiissssssissssd',
        $d[0], $d[1], $d[2], $d[3],
        $d[4], $d[5], $d[6], $d[7],
        $d[8], $d[9], $d[10], $d[11], $d[12],
        $trans_nama, $trans_no_tiket, $trans_biaya
    );
    $stmt->execute();
    $id = $conn->insert_id;
    $inserted_ids[] = $id;

    $no = $d[9] ? $d[9] : '—';
    echo "✅ SPPD #$id: {$d[4]} (status: {$d[8]}, no: $no)<br>";

    // Tambah anggota rombongan (1-2 orang) untuk SPPD tertentu
    if ($i % 3 !== 0) { // skip beberapa
        $jml_anggota = ($i % 2 === 0) ? 2 : 1;
        for ($a = 0; $a < $jml_anggota; $a++) {
            $id_peg_anggota = $peg[($i + $a + 1) % count($peg)]['id_pegawai'];
            // Jangan tambahkan pengaju sebagai anggota
            if ($id_peg_anggota === $d[0]) continue;
            $peran = ['Peserta', 'Narasumber', 'Moderator', 'Pendamping'][$a % 4];
            $conn->query("INSERT INTO tb_sppd_anggota (id_sppd, id_pegawai, peran) VALUES ($id, $id_peg_anggota, '$peran')");
        }
    }

    // Tambah biaya untuk SPPD yang disetujui/selesai
    if (in_array($d[8], ['disetujui', 'selesai'])) {
        // Ambil komponen biaya berdasarkan golongan
        $gol = $jab[0]['golongan']; // default
        foreach ($jab as $j) {
            $gol = $j['golongan'];
            break;
        }
        $komponen = $conn->query("SELECT id_komponen, jenis_biaya, besaran FROM tb_komponen_biaya WHERE golongan='$gol' AND zona='luar_kota'");
        while ($k = $komponen->fetch_assoc()) {
            $hari  = (int)((strtotime($d[6]) - strtotime($d[5])) / 86400) + 1;
            $total = $k['besaran'];
            $conn->query("INSERT INTO tb_biaya_perjalanan (id_sppd, id_komponen, jumlah_hari, besaran_realisasi) VALUES ($id, {$k['id_komponen']}, $hari, $total)");
        }
    }

    $i++;
}

echo "<hr><h4>✅ " . count($inserted_ids) . " SPPD berhasil dibuat</h4>";

// Hapus file fix_login.php dan test_login.php
@unlink(__DIR__ . '/fix_login.php');
@unlink(__DIR__ . '/test_login.php');
