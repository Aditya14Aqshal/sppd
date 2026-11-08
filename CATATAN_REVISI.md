# Catatan Revisi — Sistem Informasi SPPD Bakesbangpol

Dokumen ini mencatat revisi yang dikerjakan pada project skripsi.
Terakhir diperbarui: 8 November 2026.

---

## Ringkasan Revisi Penguji

### Revisi 1 — Sembunyikan menu & tombol cetak untuk role Pegawai
**Permintaan:** untuk role `pegawai`, sembunyikan menu Laporan di sidebar dan tombol cetak.

**Perubahan:**
- `includes/header.php` — seluruh section **Laporan** di sidebar hanya tampil untuk `admin` dan `pimpinan`.
- `pages/sppd/daftar.php` — tombol **Cetak Rekap** disembunyikan untuk `pegawai`.
- `pages/sppd/detail.php` — tombol **Cetak SPPD** disembunyikan untuk `pegawai`.
- `pages/sppd/kelola.php` — tombol **Cetak Rincian** disembunyikan untuk `pegawai`.

**Catatan:** ini hanya menyembunyikan tampilan. Halaman laporan (`pages/laporan/*.php`)
belum memblokir akses langsung via URL.

---

### Revisi 2 — Cegah tanggal perjalanan yang bertumpang tindih
**Permintaan:** user tidak boleh mengajukan dua SPPD dengan rentang
tanggal berangkat–kembali yang sama/overlap.

**Perubahan:** `pages/sppd/ajukan.php`
- Sebelum menyimpan, sistem mencari SPPD lain milik pengaju yang sama dengan
  rentang tanggal overlap:
  `berangkat_baru <= kembali_lama AND kembali_baru >= berangkat_lama`.
- Status yang **diabaikan** (tidak dianggap memakai tanggal): `draf` dan `ditolak`.
- Bila bentrok → muncul pesan merah di atas form, menyebutkan SPPD yang bentrok.
- Berlaku untuk tombol **Ajukan SPPD** maupun **Simpan Draf**.

---

### Revisi 3 — Detail transportasi pada rekapitulasi perjalanan dinas
**Permintaan penguji:** "Lengkapi data rekapitulasi perjalanan dinas dengan
menambahkan transportasi yang digunakan, apabila menggunakan transportasi udara
sertakan nama maskapainya serta harga dan no tiket."

**Dikembangkan lebih lanjut:** detail juga ditambahkan untuk moda lain
(atas permintaan pemilik project).

**Kolom baru di tabel `tb_sppd`** (berlaku untuk semua moda):

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `transportasi_nama` | VARCHAR(100) NULL | nama maskapai / PO / jenis kendaraan / moda |
| `transportasi_no_tiket` | VARCHAR(50) NULL | no. tiket / no. polisi / keterangan |
| `transportasi_biaya` | DECIMAL(15,0) NULL | harga / biaya |

**Label field menyesuaikan moda:**

| Moda | Field 1 | Field 2 | Field 3 |
|------|---------|---------|---------|
| Pesawat Terbang | Nama Maskapai* | No. Tiket* | Harga Tiket* |
| Transportasi Umum | Nama PO/Operator* | No. Tiket | Harga Tiket* |
| Kendaraan Dinas | Keterangan/Jenis | No. Polisi | Biaya |
| Kendaraan Pribadi | Keterangan/Jenis | No. Polisi | Biaya BBM/Tol |
| Lainnya (opsi baru) | Nama Moda* | No. Tiket/Keterangan | Biaya* |

\* = wajib diisi.

**File yang diubah:**
- `pages/sppd/ajukan.php` — input dinamis (panel muncul sesuai moda) + JS.
- `pages/sppd/detail.php` — tampilan detail menyesuaikan moda.
- `pages/laporan/rekap_sppd.php` — kolom **Transportasi** (ikut tercetak).
- `pages/laporan/cetak_sppd.php` — baris detail di dokumen SPPD.
- `seed_sppd.php` — data contoh untuk berbagai moda.
- `database.sql` — skema diperbarui untuk instalasi baru.

---

## Database

**File migrasi:** `migrasi_transportasi.sql`
```sql
ALTER TABLE tb_sppd
  ADD COLUMN transportasi_nama     VARCHAR(100)  DEFAULT NULL AFTER kendaraan,
  ADD COLUMN transportasi_no_tiket VARCHAR(50)   DEFAULT NULL AFTER transportasi_nama,
  ADD COLUMN transportasi_biaya    DECIMAL(15,0) DEFAULT NULL AFTER transportasi_no_tiket;
```
Catatan: migrasi ini **sudah dijalankan** pada database aktif `db_sppd_bakesbangpol`.

---

## Koreksi Data / Demo
- 3 SPPD contoh ber-moda Pesawat Terbang diisi contoh data
  (Garuda Indonesia, Citilink, Batik Air) agar rekap tampil lengkap.

---

## Verifikasi
- Semua file yang diubah lolos `php -l` (tidak ada error sintaks).
- Panjang tipe `bind_param` dicek sesuai jumlah parameter.
- Uji INSERT nyata (transaksi + rollback) berhasil menyimpan detail
  `Lainnya / Kapal Feri / FR-001 / 500000`.

---

## To-Do / Belum Selesai
- [ ] Perbarui dokumen **ERD** (`erd.html`, `erd.mmd`) dan **class diagram**
      (`class_diagram.html`) agar mencerminkan kolom `transportasi_*` dan opsi "Lainnya".
- [ ] (Opsional) Tambah pengecekan role di setiap halaman `pages/laporan/*.php`
      agar `pegawai` benar-benar tidak bisa akses via URL langsung.

---

## Isu Lama yang Ditemukan (belum diperbaiki)
Beberapa hal ini ditemukan saat analisis awal dan **belum** dikerjakan:
- Skema `database.sql` tidak sinkron dengan kode (kolom `jenis_kelamin`, `no_hp`,
  `email`, `jenis_pegawai` di `tb_pegawai`; tabel `tb_unit_kerja`, `tb_dasar_hukum`).
- Halaman `pages/master/pasien.php` + tabel `tb_pasien` adalah sisa project lain —
  sebaiknya dihapus.
- `pages/sppd/detail.php` — approve/tolak pimpinan tidak memeriksa `status='diverifikasi'`.
- Fungsi `terbilang()` di `pages/laporan/cetak_sppd.php` hanya benar untuk 1–10.
- Belum ada CSRF token; validasi upload file masih minim.
