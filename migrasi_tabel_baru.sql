-- ============================================================
-- MIGRASI: Tabel baru & kolom tambahan
-- ============================================================
USE db_sppd_bakesbangpol;

-- 0. Tambah kolom jenis_pegawai di tb_pegawai
-- ALTER TABLE tb_pegawai ADD COLUMN jenis_pegawai ENUM('PNS','PPPK','Kontrak','Honorer','Tenaga Ahli') DEFAULT 'PNS' AFTER status;

-- 1. Unit Kerja / Bidang
CREATE TABLE IF NOT EXISTS tb_unit_kerja (
  id_unit   INT AUTO_INCREMENT PRIMARY KEY,
  nama_unit VARCHAR(100) NOT NULL,
  kode_unit VARCHAR(20)  DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO tb_unit_kerja (nama_unit, kode_unit) VALUES
('Sekretariat',              'SKR'),
('Bidang Politik Dalam Negeri', 'BPDN'),
('Bidang Ketahanan Ekonomi Sosial Budaya', 'BKESB'),
('Bidang Ideologi Wawasan Kebangsaan',     'BIWK'),
('Bidang Pemberdayaan Ormas dan Oks',      'BPOO');

-- 2. Dasar Hukum / Regulasi
CREATE TABLE IF NOT EXISTS tb_dasar_hukum (
  id_dasar INT AUTO_INCREMENT PRIMARY KEY,
  judul    VARCHAR(200) NOT NULL,
  isi      TEXT         NOT NULL,
  tahun    YEAR         DEFAULT NULL,
  status   ENUM('aktif','nonaktif') DEFAULT 'aktif',
  urutan   INT          DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO tb_dasar_hukum (judul, isi, tahun, status, urutan) VALUES
('Peraturan Menteri Keuangan Nomor 113/PMK.05/2012', 'Perjalanan Dinas Dalam Negeri bagi Pejabat Negara, Pegawai Negeri, dan Pegawai Tidak Tetap', 2012, 'aktif', 1),
('Peraturan Menteri Keuangan Nomor 49/PMK.02/2023', 'Standar Biaya Masukan Tahun Anggaran 2024', 2023, 'aktif', 2),
('DPA Badan Kesatuan Bangsa dan Politik Kota Kuala Kapuas', 'Dokumen Pelaksanaan Anggaran Badan Kesatuan Bangsa dan Politik Kota Kuala Kapuas Tahun Anggaran 2026', 2026, 'aktif', 3);
