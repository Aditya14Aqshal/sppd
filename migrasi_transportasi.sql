-- ============================================================
-- MIGRASI: Detail transportasi pada SPPD
-- Revisi: rekapitulasi perjalanan dinas menampilkan transportasi
-- beserta detailnya (nama/operator/moda, no. tiket, biaya).
-- Kolom berlaku untuk semua moda:
--   - Pesawat Terbang  : Nama Maskapai, No. Tiket, Harga Tiket
--   - Transportasi Umum: Nama PO/Operator, No. Tiket, Harga Tiket
--   - Kendaraan Dinas  : Keterangan Kendaraan, No. Polisi, Biaya
--   - Kendaraan Pribadi: Keterangan Kendaraan, No. Polisi, Biaya BBM/Tol
--   - Lainnya          : Nama Moda Transportasi, No. Tiket/Keterangan, Biaya
-- ============================================================
USE db_sppd_bakesbangpol;

ALTER TABLE tb_sppd
  ADD COLUMN transportasi_nama     VARCHAR(100)  DEFAULT NULL AFTER kendaraan,
  ADD COLUMN transportasi_no_tiket VARCHAR(50)   DEFAULT NULL AFTER transportasi_nama,
  ADD COLUMN transportasi_biaya    DECIMAL(15,0) DEFAULT NULL AFTER transportasi_no_tiket;
