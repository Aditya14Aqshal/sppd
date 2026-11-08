-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2026 at 09:42 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_sppd_bakesbangpol`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_anggaran`
--

CREATE TABLE `tb_anggaran` (
  `id_anggaran` int(11) NOT NULL,
  `kode_anggaran` varchar(30) NOT NULL,
  `nama_program` varchar(150) NOT NULL,
  `pagu` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tahun_anggaran` year(4) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_anggaran`
--

INSERT INTO `tb_anggaran` (`id_anggaran`, `kode_anggaran`, `nama_program`, `pagu`, `tahun_anggaran`, `status`) VALUES
(1, '1.05.01.2.01', 'Penguatan Ideologi Pancasila', 50000000.00, '2026', 'aktif'),
(2, '1.05.01.2.02', 'Pembinaan Wawasan Kebangsaan', 75000000.00, '2026', 'aktif'),
(3, '1.05.01.2.03', 'Koordinasi Bidang Politik', 30000000.00, '2026', 'aktif'),
(5, '1.05.01.2.06', 'Monitoring dan Evaluasi Program', 35000000.00, '2026', 'aktif'),
(6, '1.05.01.2.07', 'Koordinasi Forum Kewaspadaan Dini Masyarakat', 40000000.00, '2026', 'aktif'),
(7, '1.05.01.2.04', 'Pembinaan Organisasi Kemasyarakatan', 60000000.00, '2026', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `tb_biaya_perjalanan`
--

CREATE TABLE `tb_biaya_perjalanan` (
  `id_biaya` int(11) NOT NULL,
  `id_sppd` int(11) NOT NULL,
  `id_komponen` int(11) NOT NULL,
  `jumlah_hari` int(11) NOT NULL DEFAULT 1,
  `besaran_realisasi` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kwitansi_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_biaya_perjalanan`
--

INSERT INTO `tb_biaya_perjalanan` (`id_biaya`, `id_sppd`, `id_komponen`, `jumlah_hari`, `besaran_realisasi`, `kwitansi_file`) VALUES
(1, 25, 7, 1, 300000.00, ''),
(2, 21, 5, 1, 300000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `tb_dasar_hukum`
--

CREATE TABLE `tb_dasar_hukum` (
  `id_dasar` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `tahun` year(4) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_dasar_hukum`
--

INSERT INTO `tb_dasar_hukum` (`id_dasar`, `judul`, `isi`, `tahun`, `status`, `urutan`) VALUES
(1, 'Peraturan Menteri Keuangan Nomor 113/PMK.05/2012', 'Perjalanan Dinas Dalam Negeri bagi Pejabat Negara, Pegawai Negeri, dan Pegawai Tidak Tetap', '2012', 'aktif', 1),
(2, 'Peraturan Menteri Keuangan Nomor 49/PMK.02/2023', 'Standar Biaya Masukan Tahun Anggaran 2024', '2023', 'aktif', 2),
(3, 'DPA Badan Kesatuan Bangsa dan Politik Kota Kuala Kapuas', 'Dokumen Pelaksanaan Anggaran Tahun 2026', '2026', 'aktif', 3),
(5, 'Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah', 'Mengatur penyelenggaraan pemerintahan daerah dan kewenangan perangkat daerah.', '2014', 'aktif', 0),
(6, 'Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah', 'Pedoman pengelolaan keuangan daerah, termasuk pelaksanaan belanja perjalanan dinas.', '2019', 'aktif', 0),
(7, 'Peraturan Presiden Nomor 33 Tahun 2020 tentang Standar Harga Satuan Regional', 'Menjadi acuan standar biaya perjalanan dinas pemerintah daerah.', '2020', 'aktif', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tb_dokumen`
--

CREATE TABLE `tb_dokumen` (
  `id_dokumen` int(11) NOT NULL,
  `id_sppd` int(11) NOT NULL,
  `jenis_dokumen` varchar(50) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_jabatan`
--

CREATE TABLE `tb_jabatan` (
  `id_jabatan` int(11) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `golongan` varchar(10) NOT NULL COMMENT 'Contoh: III/c',
  `pangkat` varchar(50) NOT NULL COMMENT 'Contoh: Penata',
  `eselon` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jabatan`
--

INSERT INTO `tb_jabatan` (`id_jabatan`, `nama_jabatan`, `golongan`, `pangkat`, `eselon`) VALUES
(1, 'Kepala Badan', 'IV/b', 'Pembina Tingkat I', 'II/b'),
(2, 'Sekretaris Badan', 'IV/a', 'Pembina', 'III/a'),
(3, 'Kepala Bidang', 'III/d', 'Penata Tingkat I', 'III/b'),
(4, 'Analis Kebijakan Muda', 'III/c', 'Penata', NULL),
(5, 'Staff Administrasi', 'III/a', 'Penata Muda', NULL),
(7, 'Pranata Komputer', 'III/c', 'Penata', '-'),
(8, 'Pengelola Data', 'III/c', 'Penata', '-'),
(9, 'Staff Umum', 'III/b', 'Penata Muda Tingkat I', '-');

-- --------------------------------------------------------

--
-- Table structure for table `tb_jenis_kegiatan`
--

CREATE TABLE `tb_jenis_kegiatan` (
  `id_kegiatan` int(11) NOT NULL,
  `nama_kegiatan` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jenis_kegiatan`
--

INSERT INTO `tb_jenis_kegiatan` (`id_kegiatan`, `nama_kegiatan`, `keterangan`, `status`) VALUES
(1, 'Rapat Koordinasi', 'Mengikuti rapat koordinasi antar instansi', 'aktif'),
(2, 'Bimbingan Teknis', 'Mengikuti bimtek atau pelatihan', 'nonaktif'),
(3, 'Monitoring dan Evaluasi', 'Kegiatan monev ke lapangan', 'aktif'),
(4, 'Konsultasi', 'Konsultasi ke instansi vertikal', 'aktif'),
(5, 'Sosialisasi', 'Kegiatan sosialisasi program', 'aktif'),
(6, 'Pelatihan', 'Mengikuti pelatihan peningkatan kompetensi pegawai', 'aktif'),
(8, 'Workshop', 'Mengikuti workshop atau lokakarya', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `tb_komponen_biaya`
--

CREATE TABLE `tb_komponen_biaya` (
  `id_komponen` int(11) NOT NULL,
  `jenis_biaya` enum('uang_harian','transport','penginapan') NOT NULL,
  `golongan` varchar(10) NOT NULL,
  `zona` enum('lokal','luar_kota','luar_provinsi') NOT NULL,
  `besaran` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tahun_anggaran` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_komponen_biaya`
--

INSERT INTO `tb_komponen_biaya` (`id_komponen`, `jenis_biaya`, `golongan`, `zona`, `besaran`, `tahun_anggaran`) VALUES
(1, 'uang_harian', 'IV', 'luar_kota', 370000.00, '2026'),
(2, 'uang_harian', 'III', 'luar_kota', 330000.00, '2026'),
(3, 'uang_harian', 'II', 'luar_kota', 270000.00, '2026'),
(4, 'uang_harian', 'IV', 'luar_provinsi', 530000.00, '2026'),
(5, 'uang_harian', 'III', 'luar_provinsi', 480000.00, '2026'),
(6, 'penginapan', 'IV', 'luar_kota', 400000.00, '2026'),
(7, 'penginapan', 'III', 'luar_kota', 300000.00, '2026'),
(8, 'transport', 'IV', 'luar_kota', 150000.00, '2026'),
(9, 'transport', 'III', 'luar_kota', 100000.00, '2026');

-- --------------------------------------------------------

--
-- Table structure for table `tb_laporan_dinas`
--

CREATE TABLE `tb_laporan_dinas` (
  `id_laporan` int(11) NOT NULL,
  `id_sppd` int(11) NOT NULL,
  `tgl_aktual_berangkat` date NOT NULL,
  `tgl_aktual_kembali` date NOT NULL,
  `uraian_kegiatan` text NOT NULL,
  `kesimpulan` text DEFAULT NULL,
  `bukti_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_pasien`
--

CREATE TABLE `tb_pasien` (
  `no` int(11) NOT NULL,
  `Nama` varchar(255) NOT NULL,
  `Alamat` varchar(500) NOT NULL,
  `Dokter` varchar(255) NOT NULL,
  `Keluhan` varchar(255) NOT NULL,
  `rawat` enum('rawat','inap') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pasien`
--

INSERT INTO `tb_pasien` (`no`, `Nama`, `Alamat`, `Dokter`, `Keluhan`, `rawat`) VALUES
(3, 'Aditya', 'jl mawar', 'Dr. Yusuf', 'sakit kepala', 'inap'),
(5, 'Aditya', 'jl mawar', 'Dr. Yusuf', 'TBC', 'inap'),
(6, 'Fatih', 'Jl. Seroja', 'dr. Srie', 'Sakit Kepala', 'inap');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pegawai`
--

CREATE TABLE `tb_pegawai` (
  `id_pegawai` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `unit_kerja` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `jenis_pegawai` enum('PNS','PPPK','Kontrak','Honorer','Tenaga Ahli') DEFAULT 'PNS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pegawai`
--

INSERT INTO `tb_pegawai` (`id_pegawai`, `nip`, `nama_lengkap`, `id_jabatan`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `unit_kerja`, `no_hp`, `email`, `foto`, `status`, `jenis_pegawai`) VALUES
(1, '196801011990031001', 'Drs. Ahmad Fauzi, M.Si', 1, NULL, NULL, 'L', 'Pimpinan', NULL, NULL, NULL, 'aktif', 'PNS'),
(2, '197205152000031002', 'Siti Rahayu, S.Sos', 2, NULL, NULL, 'P', 'Sekretariat', NULL, NULL, NULL, 'aktif', 'PNS'),
(3, '198703121010031003', 'Budi Santoso, S.Sos', 4, NULL, NULL, 'L', 'Bidang Ketahanan Bangsa', NULL, NULL, NULL, 'aktif', 'PNS'),
(4, '199001201015031004', 'Dewi Lestari, A.Md', 5, NULL, NULL, 'P', 'Sekretariat', NULL, NULL, NULL, 'aktif', 'PNS'),
(5, '198503302003121002', 'Aditya', 5, NULL, NULL, 'L', 'Bidang Pemberdayaan Ormas dan Oks', '089522637778', 'aditya@gmail.com', NULL, 'aktif', 'PNS'),
(6, '198512082012041003', 'Roma', 4, NULL, NULL, 'L', 'Bidang Ketahanan Ekonomi Sosial Budaya', '085176549280', 'roma@gmail.com', NULL, 'aktif', 'PNS'),
(7, '198601252011021007', 'Runa', 5, NULL, NULL, 'P', 'Bidang Politik Dalam Negeri', '089685679115', 'runa@gmail.com', NULL, 'aktif', 'PNS');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pengguna`
--

CREATE TABLE `tb_pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai','pimpinan') NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pengguna`
--

INSERT INTO `tb_pengguna` (`id_pengguna`, `username`, `password`, `role`, `id_pegawai`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$BJVkGf3d7hdaF4qF13GoW.f3HO/FNmpnCD5eGQcqQFctgHUJDzMK.', 'admin', 4, 'aktif', '2026-05-18 01:23:56'),
(2, 'pimpinan', '$2y$10$gXe6SExXUpYPHgt/0pxfoOjB/89pzzwEXuy3lLwd5FjiYTH8NhJiC', 'pimpinan', 1, 'aktif', '2026-05-18 01:23:56'),
(3, 'budi', '$2y$10$BJVkGf3d7hdaF4qF13GoW.f3HO/FNmpnCD5eGQcqQFctgHUJDzMK.', 'pegawai', 3, 'aktif', '2026-05-18 01:23:56'),
(4, 'pegawai', '$2y$10$OU/SNsDA9GdU1NZfnP0qa.0ZseQ3iszt8x0eZtJdd9xa3HsZSc7wu', 'pegawai', 2, 'aktif', NULL),
(5, 'aditya', '$2y$10$kbcAx4/aGrxcDrfMMmKycuhi4fXDFekL3syyi9156xkmcufaqwj7.', 'pegawai', 5, 'aktif', NULL),
(6, 'roma', '$2y$10$7U.otDZ2s7s4J4BI7KYnau5yVSVJ5s0eyvn5uRx/uVuDBczDTDsiO', 'pegawai', 6, 'aktif', NULL),
(7, 'runa', '$2y$10$vOyE3U.FtPMWVp9q.5Es.e7ce8AdVP.UrqgivdIo0YUQ022nmXZQ2', 'pegawai', 7, 'aktif', NULL),
(8, 'fauzi', '$2y$10$i4ajuLmqmvqe3JQMxT/HEe0yVhnvql0Z0qzScAvXEw6OhfisYPzo2', 'pegawai', 1, 'aktif', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_sppd`
--

CREATE TABLE `tb_sppd` (
  `id_sppd` int(11) NOT NULL,
  `no_sppd` varchar(50) DEFAULT NULL,
  `id_pengaju` int(11) NOT NULL,
  `id_kegiatan` int(11) NOT NULL,
  `id_tujuan` int(11) NOT NULL,
  `id_anggaran` int(11) NOT NULL,
  `keperluan` text NOT NULL,
  `tgl_berangkat` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `kendaraan` varchar(50) DEFAULT NULL,
  `transportasi_nama` varchar(100) DEFAULT NULL,
  `transportasi_no_tiket` varchar(50) DEFAULT NULL,
  `transportasi_biaya` decimal(15,0) DEFAULT NULL,
  `status` enum('draf','diajukan','diverifikasi','disetujui','ditolak','selesai') NOT NULL DEFAULT 'draf',
  `catatan_tolak` text DEFAULT NULL,
  `tgl_pengajuan` datetime DEFAULT NULL,
  `tgl_persetujuan` datetime DEFAULT NULL,
  `id_pimpinan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_sppd`
--

INSERT INTO `tb_sppd` (`id_sppd`, `no_sppd`, `id_pengaju`, `id_kegiatan`, `id_tujuan`, `id_anggaran`, `keperluan`, `tgl_berangkat`, `tgl_kembali`, `kendaraan`, `transportasi_nama`, `transportasi_no_tiket`, `transportasi_biaya`, `status`, `catatan_tolak`, `tgl_pengajuan`, `tgl_persetujuan`, `id_pimpinan`) VALUES
(11, '094/001/BKBP/I/2026', 1, 1, 1, 1, 'Rapat Koordinasi Forkopimda', '2026-01-12', '2026-01-14', 'Kendaraan Dinas', NULL, NULL, NULL, 'selesai', NULL, NULL, '2026-01-11 09:00:00', 1),
(12, '094/002/BKBP/II/2026', 2, 3, 2, 2, 'Sosialisasi Program Ketahanan Bangsa', '2026-02-03', '2026-02-05', 'Kendaraan Dinas', NULL, NULL, NULL, 'selesai', NULL, NULL, '2026-02-02 10:00:00', 1),
(13, '094/003/BKBP/II/2026', 3, 4, 3, 3, 'Bimbingan Teknis Pengelolaan Konflik', '2026-02-17', '2026-02-20', 'Pesawat Terbang', 'Garuda Indonesia', 'GA-1234567890', 1850000, 'disetujui', NULL, NULL, '2026-02-15 08:30:00', 1),
(14, NULL, 4, 5, 4, 1, 'Monitoring dan Evaluasi Ormas', '2026-03-03', '2026-03-04', 'Kendaraan Dinas', NULL, NULL, NULL, 'disetujui', NULL, NULL, '2026-07-13 23:56:49', 1),
(15, NULL, 1, 1, 1, 2, 'Forum Diskusi Lintas Sektoral', '2026-03-16', '2026-03-16', 'Transportasi Umum', NULL, NULL, NULL, 'ditolak', 'Materi rapat belum lengkap, harap dilengkapi', NULL, '2026-03-14 13:00:00', 1),
(16, '094/004/BKBP/IV/2026', 2, 3, 2, 3, 'Rapat Persiapan Pemilu 2026', '2026-04-06', '2026-04-08', 'Kendaraan Dinas', NULL, NULL, NULL, 'disetujui', NULL, NULL, '2026-04-04 11:00:00', 1),
(17, NULL, 3, 4, 3, 1, 'Sosialisasi Wawasan Kebangsaan', '2026-04-20', '2026-04-22', 'Transportasi Umum', NULL, NULL, NULL, 'diajukan', NULL, NULL, NULL, 1),
(18, NULL, 4, 5, 4, 2, 'Bimtek Pengelolaan Anggaran Perjalanan Dinas', '2026-05-05', '2026-05-08', 'Pesawat Terbang', 'Citilink', 'CT-0987654321', 1250000, 'diverifikasi', NULL, NULL, NULL, NULL),
(19, '094/005/BKBP/V/2026', 1, 1, 1, 3, 'Rapat Evaluasi Program Semester I', '2026-05-19', '2026-05-20', 'Kendaraan Dinas', NULL, NULL, NULL, 'selesai', NULL, NULL, '2026-05-17 09:00:00', 1),
(20, '094/006/BKBP/VI/2026', 2, 3, 2, 1, 'Forum Koordinasi Pimpinan Daerah', '2026-06-09', '2026-06-11', 'Kendaraan Dinas', NULL, NULL, NULL, 'selesai', NULL, NULL, '2026-06-07 13:30:00', 1),
(21, NULL, 5, 5, 2, 3, '0', '2026-07-17', '2026-07-20', 'Kendaraan Pribadi', NULL, NULL, NULL, 'disetujui', NULL, NULL, '2026-07-14 00:25:26', 1),
(22, '001/BKBP/VII/2026', 6, 3, 4, 3, '0', '2026-07-20', '2026-07-24', 'Kendaraan Pribadi', NULL, NULL, NULL, 'disetujui', NULL, NULL, '2026-07-23 02:26:28', 1),
(23, NULL, 7, 3, 1, 3, '0', '2026-07-21', '2026-07-27', 'Kendaraan Dinas', NULL, NULL, NULL, 'disetujui', NULL, NULL, '2026-07-23 02:36:13', 1),
(24, NULL, 6, 5, 3, 2, '0', '2026-07-21', '2026-07-23', 'Kendaraan Pribadi', NULL, NULL, NULL, 'disetujui', NULL, NULL, '2026-07-23 02:51:12', 1),
(25, NULL, 7, 6, 3, 1, '0', '2026-07-13', '2026-07-18', 'Pesawat Terbang', 'Batik Air', 'ID-1122334455', 1450000, 'disetujui', NULL, NULL, '2026-07-23 03:31:33', 1),
(26, NULL, 1, 1, 6, 1, '0', '2026-01-12', '2026-01-14', 'Kendaraan Dinas', NULL, NULL, NULL, 'diajukan', NULL, NULL, NULL, NULL),
(27, NULL, 5, 4, 2, 3, '0', '2026-11-08', '2026-11-09', 'Kendaraan Dinas', NULL, NULL, NULL, 'diajukan', NULL, NULL, NULL, NULL),
(28, NULL, 5, 3, 4, 6, '0', '2026-11-08', '2026-11-09', 'Transportasi Umum', NULL, NULL, NULL, 'diajukan', NULL, NULL, NULL, NULL),
(29, NULL, 6, 4, 2, 5, '0', '2026-11-08', '2026-11-09', 'Kendaraan Pribadi', NULL, NULL, NULL, 'diajukan', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_sppd_anggota`
--

CREATE TABLE `tb_sppd_anggota` (
  `id_anggota` int(11) NOT NULL,
  `id_sppd` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `peran` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_sppd_anggota`
--

INSERT INTO `tb_sppd_anggota` (`id_anggota`, `id_sppd`, `id_pegawai`, `peran`) VALUES
(10, 12, 3, 'Peserta'),
(11, 13, 4, 'Peserta'),
(12, 13, 1, 'Narasumber'),
(13, 15, 2, 'Peserta'),
(14, 15, 3, 'Narasumber'),
(15, 16, 3, 'Peserta'),
(16, 18, 1, 'Peserta'),
(17, 19, 2, 'Peserta'),
(18, 19, 3, 'Narasumber'),
(19, 23, 5, 'Peserta');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tujuan`
--

CREATE TABLE `tb_tujuan` (
  `id_tujuan` int(11) NOT NULL,
  `nama_instansi` varchar(150) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(80) NOT NULL,
  `provinsi` varchar(80) NOT NULL DEFAULT '',
  `zona` enum('lokal','luar_kota','luar_provinsi') NOT NULL DEFAULT 'lokal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_tujuan`
--

INSERT INTO `tb_tujuan` (`id_tujuan`, `nama_instansi`, `alamat`, `kota`, `provinsi`, `zona`) VALUES
(1, 'Kantor Gubernur Kalimantan Tengah', NULL, 'Palangkaraya', 'Kalimantan Tengah', 'luar_kota'),
(2, 'Kantor Bupati Kapuas', NULL, 'Kuala Kapuas', 'Kalimantan Tengah', 'lokal'),
(3, 'Kementerian Dalam Negeri', NULL, 'Jakarta', 'DKI Jakarta', 'luar_provinsi'),
(4, 'Kantor DPRD Kota Kuala Kapuas', NULL, 'Kuala Kapuas', 'Kalimantan Tengah', 'lokal'),
(5, 'Pengadilan Negeri Kuala Kapuas', '', 'Kuala Kapuas', 'Kalimantan Tengah', 'luar_kota'),
(6, 'Kejaksaan Negeri Kapuas', '', 'Kuala Kapuas', 'Kalimantan Tengah', 'lokal');

-- --------------------------------------------------------

--
-- Table structure for table `tb_unit_kerja`
--

CREATE TABLE `tb_unit_kerja` (
  `id_unit` int(11) NOT NULL,
  `nama_unit` varchar(100) NOT NULL,
  `kode_unit` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_unit_kerja`
--

INSERT INTO `tb_unit_kerja` (`id_unit`, `nama_unit`, `kode_unit`) VALUES
(1, 'Sekretariat', 'SKR'),
(2, 'Bidang Politik Dalam Negeri', 'BPDN'),
(3, 'Bidang Ketahanan Ekonomi Sosial Budaya', 'BKESB'),
(4, 'Bidang Ideologi Wawasan Kebangsaan', 'BIWK'),
(5, 'Bidang Pemberdayaan Organisasi Kemasyarakatan', 'BPOM');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_anggaran`
--
ALTER TABLE `tb_anggaran`
  ADD PRIMARY KEY (`id_anggaran`);

--
-- Indexes for table `tb_biaya_perjalanan`
--
ALTER TABLE `tb_biaya_perjalanan`
  ADD PRIMARY KEY (`id_biaya`),
  ADD KEY `id_sppd` (`id_sppd`),
  ADD KEY `id_komponen` (`id_komponen`);

--
-- Indexes for table `tb_dasar_hukum`
--
ALTER TABLE `tb_dasar_hukum`
  ADD PRIMARY KEY (`id_dasar`);

--
-- Indexes for table `tb_dokumen`
--
ALTER TABLE `tb_dokumen`
  ADD PRIMARY KEY (`id_dokumen`),
  ADD KEY `id_sppd` (`id_sppd`);

--
-- Indexes for table `tb_jabatan`
--
ALTER TABLE `tb_jabatan`
  ADD PRIMARY KEY (`id_jabatan`);

--
-- Indexes for table `tb_jenis_kegiatan`
--
ALTER TABLE `tb_jenis_kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`);

--
-- Indexes for table `tb_komponen_biaya`
--
ALTER TABLE `tb_komponen_biaya`
  ADD PRIMARY KEY (`id_komponen`);

--
-- Indexes for table `tb_laporan_dinas`
--
ALTER TABLE `tb_laporan_dinas`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `id_sppd` (`id_sppd`);

--
-- Indexes for table `tb_pasien`
--
ALTER TABLE `tb_pasien`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `tb_pegawai`
--
ALTER TABLE `tb_pegawai`
  ADD PRIMARY KEY (`id_pegawai`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `id_jabatan` (`id_jabatan`);

--
-- Indexes for table `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tb_sppd`
--
ALTER TABLE `tb_sppd`
  ADD PRIMARY KEY (`id_sppd`),
  ADD KEY `id_pengaju` (`id_pengaju`),
  ADD KEY `id_kegiatan` (`id_kegiatan`),
  ADD KEY `id_tujuan` (`id_tujuan`),
  ADD KEY `id_anggaran` (`id_anggaran`),
  ADD KEY `id_pimpinan` (`id_pimpinan`);

--
-- Indexes for table `tb_sppd_anggota`
--
ALTER TABLE `tb_sppd_anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD KEY `id_sppd` (`id_sppd`),
  ADD KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tb_tujuan`
--
ALTER TABLE `tb_tujuan`
  ADD PRIMARY KEY (`id_tujuan`);

--
-- Indexes for table `tb_unit_kerja`
--
ALTER TABLE `tb_unit_kerja`
  ADD PRIMARY KEY (`id_unit`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_anggaran`
--
ALTER TABLE `tb_anggaran`
  MODIFY `id_anggaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_biaya_perjalanan`
--
ALTER TABLE `tb_biaya_perjalanan`
  MODIFY `id_biaya` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_dasar_hukum`
--
ALTER TABLE `tb_dasar_hukum`
  MODIFY `id_dasar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_dokumen`
--
ALTER TABLE `tb_dokumen`
  MODIFY `id_dokumen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_jabatan`
--
ALTER TABLE `tb_jabatan`
  MODIFY `id_jabatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_jenis_kegiatan`
--
ALTER TABLE `tb_jenis_kegiatan`
  MODIFY `id_kegiatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_komponen_biaya`
--
ALTER TABLE `tb_komponen_biaya`
  MODIFY `id_komponen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_laporan_dinas`
--
ALTER TABLE `tb_laporan_dinas`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_pasien`
--
ALTER TABLE `tb_pasien`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_pegawai`
--
ALTER TABLE `tb_pegawai`
  MODIFY `id_pegawai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tb_sppd`
--
ALTER TABLE `tb_sppd`
  MODIFY `id_sppd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tb_sppd_anggota`
--
ALTER TABLE `tb_sppd_anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tb_tujuan`
--
ALTER TABLE `tb_tujuan`
  MODIFY `id_tujuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_unit_kerja`
--
ALTER TABLE `tb_unit_kerja`
  MODIFY `id_unit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_biaya_perjalanan`
--
ALTER TABLE `tb_biaya_perjalanan`
  ADD CONSTRAINT `tb_biaya_perjalanan_ibfk_1` FOREIGN KEY (`id_sppd`) REFERENCES `tb_sppd` (`id_sppd`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_biaya_perjalanan_ibfk_2` FOREIGN KEY (`id_komponen`) REFERENCES `tb_komponen_biaya` (`id_komponen`);

--
-- Constraints for table `tb_dokumen`
--
ALTER TABLE `tb_dokumen`
  ADD CONSTRAINT `tb_dokumen_ibfk_1` FOREIGN KEY (`id_sppd`) REFERENCES `tb_sppd` (`id_sppd`) ON DELETE CASCADE;

--
-- Constraints for table `tb_laporan_dinas`
--
ALTER TABLE `tb_laporan_dinas`
  ADD CONSTRAINT `tb_laporan_dinas_ibfk_1` FOREIGN KEY (`id_sppd`) REFERENCES `tb_sppd` (`id_sppd`) ON DELETE CASCADE;

--
-- Constraints for table `tb_pegawai`
--
ALTER TABLE `tb_pegawai`
  ADD CONSTRAINT `tb_pegawai_ibfk_1` FOREIGN KEY (`id_jabatan`) REFERENCES `tb_jabatan` (`id_jabatan`);

--
-- Constraints for table `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  ADD CONSTRAINT `tb_pengguna_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `tb_pegawai` (`id_pegawai`);

--
-- Constraints for table `tb_sppd`
--
ALTER TABLE `tb_sppd`
  ADD CONSTRAINT `tb_sppd_ibfk_1` FOREIGN KEY (`id_pengaju`) REFERENCES `tb_pegawai` (`id_pegawai`),
  ADD CONSTRAINT `tb_sppd_ibfk_2` FOREIGN KEY (`id_kegiatan`) REFERENCES `tb_jenis_kegiatan` (`id_kegiatan`),
  ADD CONSTRAINT `tb_sppd_ibfk_3` FOREIGN KEY (`id_tujuan`) REFERENCES `tb_tujuan` (`id_tujuan`),
  ADD CONSTRAINT `tb_sppd_ibfk_4` FOREIGN KEY (`id_anggaran`) REFERENCES `tb_anggaran` (`id_anggaran`),
  ADD CONSTRAINT `tb_sppd_ibfk_5` FOREIGN KEY (`id_pimpinan`) REFERENCES `tb_pegawai` (`id_pegawai`);

--
-- Constraints for table `tb_sppd_anggota`
--
ALTER TABLE `tb_sppd_anggota`
  ADD CONSTRAINT `tb_sppd_anggota_ibfk_1` FOREIGN KEY (`id_sppd`) REFERENCES `tb_sppd` (`id_sppd`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_sppd_anggota_ibfk_2` FOREIGN KEY (`id_pegawai`) REFERENCES `tb_pegawai` (`id_pegawai`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
