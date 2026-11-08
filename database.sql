CREATE DATABASE IF NOT EXISTS db_sppd_bakesbangpol;
USE db_sppd_bakesbangpol;

-- Jabatan / Golongan
CREATE TABLE tb_jabatan (
  id_jabatan   INT AUTO_INCREMENT PRIMARY KEY,
  nama_jabatan VARCHAR(100) NOT NULL,
  golongan     VARCHAR(10),
  pangkat      VARCHAR(50)
) ENGINE=InnoDB;

-- Pegawai
CREATE TABLE tb_pegawai (
  id_pegawai    INT AUTO_INCREMENT PRIMARY KEY,
  nip           VARCHAR(30)  NOT NULL UNIQUE,
  nama_lengkap  VARCHAR(100) NOT NULL,
  id_jabatan    INT          NOT NULL,
  unit_kerja    VARCHAR(100),
  status        ENUM('aktif','nonaktif') DEFAULT 'aktif',
  FOREIGN KEY (id_jabatan) REFERENCES tb_jabatan(id_jabatan)
) ENGINE=InnoDB;

-- Pengguna (login)
CREATE TABLE tb_pengguna (
  id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
  id_pegawai  INT          NOT NULL UNIQUE,
  username    VARCHAR(50)  NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  role        ENUM('admin','pegawai','pimpinan') NOT NULL DEFAULT 'pegawai',
  status      ENUM('aktif','nonaktif') DEFAULT 'aktif',
  FOREIGN KEY (id_pegawai) REFERENCES tb_pegawai(id_pegawai)
) ENGINE=InnoDB;

-- Tujuan / Instansi
CREATE TABLE tb_tujuan (
  id_tujuan     INT AUTO_INCREMENT PRIMARY KEY,
  nama_instansi VARCHAR(150) NOT NULL,
  kota          VARCHAR(100),
  provinsi      VARCHAR(100),
  zona          ENUM('lokal','luar_kota','luar_provinsi') DEFAULT 'luar_kota'
) ENGINE=InnoDB;

-- Jenis Kegiatan
CREATE TABLE tb_jenis_kegiatan (
  id_kegiatan   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kegiatan VARCHAR(150) NOT NULL,
  status        ENUM('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB;

-- Sumber Anggaran
CREATE TABLE tb_anggaran (
  id_anggaran   INT AUTO_INCREMENT PRIMARY KEY,
  kode_anggaran VARCHAR(50)  NOT NULL UNIQUE,
  nama_program  VARCHAR(150) NOT NULL,
  pagu          DECIMAL(15,0) DEFAULT 0,
  status        ENUM('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB;

-- SPPD
CREATE TABLE tb_sppd (
  id_sppd          INT AUTO_INCREMENT PRIMARY KEY,
  id_pengaju       INT          NOT NULL,
  id_kegiatan      INT          NOT NULL,
  id_tujuan        INT          NOT NULL,
  id_anggaran      INT          NOT NULL,
  id_pimpinan      INT          DEFAULT NULL,
  no_sppd          VARCHAR(50)  DEFAULT NULL,
  keperluan        TEXT         NOT NULL,
  tgl_berangkat    DATE         NOT NULL,
  tgl_kembali      DATE         NOT NULL,
  kendaraan        VARCHAR(50)  DEFAULT NULL,
  transportasi_nama     VARCHAR(100)  DEFAULT NULL,
  transportasi_no_tiket VARCHAR(50)   DEFAULT NULL,
  transportasi_biaya    DECIMAL(15,0) DEFAULT NULL,
  status           ENUM('draf','diajukan','diverifikasi','disetujui','ditolak','selesai') DEFAULT 'draf',
  catatan_tolak    TEXT         DEFAULT NULL,
  tgl_pengajuan    DATETIME     DEFAULT CURRENT_TIMESTAMP,
  tgl_persetujuan  DATETIME     DEFAULT NULL,
  FOREIGN KEY (id_pengaju)  REFERENCES tb_pegawai(id_pegawai),
  FOREIGN KEY (id_kegiatan) REFERENCES tb_jenis_kegiatan(id_kegiatan),
  FOREIGN KEY (id_tujuan)   REFERENCES tb_tujuan(id_tujuan),
  FOREIGN KEY (id_anggaran) REFERENCES tb_anggaran(id_anggaran),
  FOREIGN KEY (id_pimpinan) REFERENCES tb_pegawai(id_pegawai)
) ENGINE=InnoDB;

-- Anggota rombongan SPPD
CREATE TABLE tb_sppd_anggota (
  id_anggota INT AUTO_INCREMENT PRIMARY KEY,
  id_sppd    INT NOT NULL,
  id_pegawai INT NOT NULL,
  peran      VARCHAR(50) DEFAULT NULL,
  FOREIGN KEY (id_sppd)    REFERENCES tb_sppd(id_sppd) ON DELETE CASCADE,
  FOREIGN KEY (id_pegawai) REFERENCES tb_pegawai(id_pegawai)
) ENGINE=InnoDB;

-- Komponen Biaya (standar)
CREATE TABLE tb_komponen_biaya (
  id_komponen INT AUTO_INCREMENT PRIMARY KEY,
  jenis_biaya ENUM('uang_harian','transport','penginapan') NOT NULL,
  golongan    VARCHAR(5)  NOT NULL,
  zona        ENUM('lokal','luar_kota','luar_provinsi') NOT NULL,
  besaran     DECIMAL(12,0) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Biaya realisasi perjalanan
CREATE TABLE tb_biaya_perjalanan (
  id_biaya          INT AUTO_INCREMENT PRIMARY KEY,
  id_sppd           INT          NOT NULL,
  id_komponen       INT          NOT NULL,
  jumlah_hari       INT          NOT NULL DEFAULT 1,
  besaran_realisasi DECIMAL(12,0) NOT NULL DEFAULT 0,
  kwitansi_file     VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (id_sppd)     REFERENCES tb_sppd(id_sppd) ON DELETE CASCADE,
  FOREIGN KEY (id_komponen) REFERENCES tb_komponen_biaya(id_komponen)
) ENGINE=InnoDB;

-- Laporan hasil perjalanan dinas
CREATE TABLE tb_laporan_dinas (
  id_laporan            INT AUTO_INCREMENT PRIMARY KEY,
  id_sppd               INT          NOT NULL UNIQUE,
  tgl_aktual_berangkat  DATE         DEFAULT NULL,
  tgl_aktual_kembali    DATE         DEFAULT NULL,
  uraian_kegiatan       TEXT         DEFAULT NULL,
  kesimpulan            TEXT         DEFAULT NULL,
  bukti_file            VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (id_sppd) REFERENCES tb_sppd(id_sppd) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DATA AWAL (SEEDER)
-- ============================================================

-- Jabatan
INSERT INTO tb_jabatan (nama_jabatan, golongan, pangkat) VALUES
('Kepala Badan', 'IV/c', 'Pembina Utama Muda'),
('Sekretaris', 'IV/a', 'Pembina'),
('Kepala Bidang', 'III/d', 'Penata Tk. I'),
('Kepala Sub Bidang', 'III/c', 'Penata'),
('Staf Administrasi', 'II/d', 'Pengatur Tk. I'),
('Staf Umum', 'II/c', 'Pengatur');

-- Pegawai
INSERT INTO tb_pegawai (nip, nama_lengkap, id_jabatan, unit_kerja) VALUES
('197501011998031001', 'Drs. H. Ahmad Yani, M.Si',        1, 'Bakesbangpol'),
('198002122005021002', 'Siti Rahmawati, S.Sos, M.AP',     2, 'Bakesbangpol'),
('198503102008011003', 'Bambang Supriyadi, S.IP',         3, 'Bakesbangpol'),
('199001152009021001', 'Dian Puspita, S.E',               4, 'Bakesbangpol'),
('199205102011011002', 'Rudi Hartono',                    5, 'Bakesbangpol'),
('199308122012021001', 'Fitriani Nurul, A.Md',            6, 'Bakesbangpol');

-- Pengguna (password: 123456)
INSERT INTO tb_pengguna (id_pegawai, username, password, role, status) VALUES
(1, 'pimpinan', '$2y$10$BwwG6twtLdlZfqnDJ1h5yOrneiqApsSVaB3D4etyifoxSP6iVO0.a', 'pimpinan', 'aktif'),
(2, 'admin',    '$2y$10$BwwG6twtLdlZfqnDJ1h5yOrneiqApsSVaB3D4etyifoxSP6iVO0.a', 'admin',    'aktif'),
(3, 'pegawai',  '$2y$10$BwwG6twtLdlZfqnDJ1h5yOrneiqApsSVaB3D4etyifoxSP6iVO0.a', 'pegawai',  'aktif');

-- Tujuan
INSERT INTO tb_tujuan (nama_instansi, kota, provinsi, zona) VALUES
('Kantor Gubernur Kalimantan Tengah', 'Palangka Raya', 'Kalimantan Tengah', 'luar_kota'),
('Kementerian Dalam Negeri',           'Jakarta',       'DKI Jakarta',       'luar_provinsi'),
('Kantor Bupati Kapuas',              'Kuala Kapuas',  'Kalimantan Tengah', 'lokal'),
('Pemerintah Kota Banjarmasin',       'Banjarmasin',   'Kalimantan Selatan','luar_provinsi');

-- Jenis Kegiatan
INSERT INTO tb_jenis_kegiatan (nama_kegiatan, status) VALUES
('Rapat Koordinasi',            'aktif'),
('Sosialisasi Program',         'aktif'),
('Bimbingan Teknis',            'aktif'),
('Monitoring dan Evaluasi',     'aktif'),
('Forum Diskusi',               'aktif');

-- Anggaran
INSERT INTO tb_anggaran (kode_anggaran, nama_program, pagu, status) VALUES
('BKBP-01-2026', 'Program Pelayanan Administrasi Perkantoran', 250000000, 'aktif'),
('BKBP-02-2026', 'Program Peningkatan Kapasitas SDM',          180000000, 'aktif');

-- Komponen Biaya standar
INSERT INTO tb_komponen_biaya (jenis_biaya, golongan, zona, besaran) VALUES
('uang_harian', 'IV',   'lokal',        180000),
('uang_harian', 'IV',   'luar_kota',    250000),
('uang_harian', 'IV',   'luar_provinsi', 370000),
('uang_harian', 'III',  'lokal',        150000),
('uang_harian', 'III',  'luar_kota',    200000),
('uang_harian', 'III',  'luar_provinsi', 300000),
('uang_harian', 'II',   'lokal',        120000),
('uang_harian', 'II',   'luar_kota',    170000),
('uang_harian', 'II',   'luar_provinsi', 250000),
('transport',   'IV',   'luar_kota',    350000),
('transport',   'IV',   'luar_provinsi', 500000),
('transport',   'III',  'luar_kota',    300000),
('transport',   'III',  'luar_provinsi', 450000),
('transport',   'II',   'luar_kota',    250000),
('transport',   'II',   'luar_provinsi', 400000),
('penginapan',  'IV',   'luar_kota',    400000),
('penginapan',  'IV',   'luar_provinsi', 500000),
('penginapan',  'III',  'luar_kota',    300000),
('penginapan',  'III',  'luar_provinsi', 400000),
('penginapan',  'II',   'luar_kota',    250000),
('penginapan',  'II',   'luar_provinsi', 350000);
