-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: sipa
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `arsip`
--

DROP TABLE IF EXISTS `arsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arsip` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_pengolah_id` bigint unsigned NOT NULL,
  `unit_pengolah_asal` varchar(150) DEFAULT NULL,
  `kode_internal` varchar(60) NOT NULL,
  `kode_klasifikasi` varchar(100) DEFAULT NULL,
  `uraian_berkas` text NOT NULL,
  `uraian_informasi` text,
  `tanggal_surat` date DEFAULT NULL,
  `tahun` smallint NOT NULL,
  `bulan` smallint DEFAULT NULL,
  `tingkat_perkembangan` varchar(255) DEFAULT NULL,
  `jra_aktif` smallint DEFAULT NULL,
  `jra_inaktif` smallint DEFAULT NULL,
  `jra_keterangan` varchar(100) DEFAULT NULL,
  `lokasi_simpan` varchar(255) DEFAULT NULL,
  `teks_label` text,
  `klasifikasi_keamanan` varchar(100) NOT NULL DEFAULT 'Biasa/Terbuka',
  `dikecualikan` tinyint(1) NOT NULL DEFAULT '0',
  `status_ketersediaan` varchar(30) NOT NULL DEFAULT 'Tersedia',
  `skor_integritas_terakhir` tinyint unsigned DEFAULT NULL,
  `catatan_kondisi_terakhir` varchar(255) DEFAULT NULL,
  `terakhir_diperiksa_pada` timestamp NULL DEFAULT NULL,
  `kode_qr` varchar(50) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `arsip_kode_internal_unique` (`kode_internal`),
  UNIQUE KEY `arsip_kode_qr_unique` (`kode_qr`),
  KEY `arsip_unit_pengolah_id_tahun_bulan_index` (`unit_pengolah_id`,`tahun`,`bulan`),
  KEY `arsip_kode_klasifikasi_index` (`kode_klasifikasi`),
  KEY `arsip_status_ketersediaan_index` (`status_ketersediaan`),
  CONSTRAINT `arsip_unit_pengolah_id_foreign` FOREIGN KEY (`unit_pengolah_id`) REFERENCES `unit_pengolah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arsip`
--

LOCK TABLES `arsip` WRITE;
/*!40000 ALTER TABLE `arsip` DISABLE KEYS */;
INSERT INTO `arsip` VALUES (4,1,'Bidang Pengelolaan Arsip','ARS-2026-07-0001','700.1.1.3','Rencana Kinerja Tahunan','RENJA Kantor Kearsipan Dan Dokumen Kabupaten Cirebon Tahun 2014 (2 Berkas)','2026-07-14',2026,7,'tanda tangan dan cap asli dan teregalisir',NULL,NULL,NULL,'Boks 4','Rencana Kinerja Tahunan Juli 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(5,1,'Bidang Pengelolaan Arsip','ARS-2026-11-0002','700.1.1.3','Rencana Kinerja Tahunan','Rencana Kerja (RENJA) Kantor Kearsipan Dan Dokumen Kabupaten Cirebon Tahun 2015 (1 Berkas)','2026-11-11',2026,11,'tanda tangan asli',NULL,NULL,NULL,'Boks 4','Rencana Kinerja Tahunan November 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 04:09:47'),(6,1,'Bidang Pengelolaan Arsip','ARS-2026-11-0003','700.1.1.3','Rencana Kinerja Tahunan','Rencana Kerja (RENJA) Kantor Kearsipan Dan Dokumen Kabupaten Cirebon Tahun 2015 (1 Berkas)','2026-11-11',2026,11,'tanda tangan asli',NULL,NULL,NULL,'Boks 4','Rencana Kinerja Tahunan November 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(7,1,'Bidang Pengelolaan Arsip','ARS-2016-00-0001','700.1.1.3','Rencana Kinerja Tahunan','Rencana Kerja (RENJA) Kantor Kearsipan Dan Dokumen Kabupaten Cirebon Tahun 2016 (1 Berkas)',NULL,2016,NULL,'tanda tangan asli',NULL,NULL,NULL,'Boks 4','Rencana Kinerja Tahunan  2016','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(8,1,'Bidang Pengelolaan Arsip','ARS-2009-00-0001','900.1.3.10','Laporan Keuangan','Laporan Keuangan Pemerintah Kabupaten Cirebon 1.24.01 Kantor Kearsipan Dan Dokumen Kabupaten Cirebon Kabupaten Cirebon Untuk Periode Yang Berakhir 31 Desember 2008 Tahun Anggaran 2008',NULL,2009,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 1','Laporan Keuangan  2009','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(9,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0001','900.1.3.10','Laporan Keuangan','Laporan Keuangan 2.18.01 Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon Tahun Anggaran 2018',NULL,2018,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 1','Laporan Keuangan  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(10,1,'Bidang Pengelolaan Arsip','ARS-2007-00-0001','900.1.7.1','Surat Penetapan Pemimpin Proyek/Bagian Proyek, Bendahara, atas Penggunaan Anggaran Kegiatan Pembantuan, termasuk Specimen Tanda Tangan','Surat Pengesahan Laporan Pertanggung Jawaban Bendahara Pengeluaran (SPJ-UP/GU)',NULL,2007,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 1','Surat Penetapan Pemimpin Proyek/Bagian Proyek, Bendahara, atas Penggunaan Anggaran Kegiatan Pembantuan, termasuk Specimen Tanda Tangan  2007','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(11,1,'Bidang Pengelolaan Arsip','ARS-2014-00-0001','900.1.6.4','Laporan Realisasi Anggaran dan Neraca  Bulanan  /Triwulanan /Semesteran','Anggaran Kas Per Bulan Kantor Kearsipan Dan Dokumentahun Anggaran 2014',NULL,2014,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 1','Laporan Realisasi Anggaran dan Neraca  Bulanan  /Triwulanan /Semesteran  2014','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(12,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0002','900.1.6.4','Laporan Realisasi Anggaran dan Neraca  Bulanan  /Triwulanan /Semesteran','Anggaran Kas Per Bulan Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon Tahun Anggaran 2018 (4 Berkas)',NULL,2018,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 1','Laporan Realisasi Anggaran dan Neraca  Bulanan  /Triwulanan /Semesteran  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(13,1,'Bidang Pengelolaan Arsip','ARS-2017-00-0001','900.1.6.4','Laporan Realisasi Anggaran dan Neraca  Bulanan  /Triwulanan /Semesteran','Anggaran Kas Per Bulan Dinas Kearsipan Dan Perpustakaan Tahun Anggaran 2017 (2 Berkas)',NULL,2017,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 1','Laporan Realisasi Anggaran dan Neraca  Bulanan  /Triwulanan /Semesteran  2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(14,1,'Bidang Pengelolaan Arsip','ARS-2020-00-0001','900.1.1.4','Anggaran Pendapatan dan Belanja Daerah Perubahan (RAPBD-P)','Rencana Kerja Perubahan Anggaran Satuan Kerja Perangkat Daerah (Rkpa-Skpd) Tahun Anggaran 2020 (3 Berkas)',NULL,2020,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 2','Anggaran Pendapatan dan Belanja Daerah Perubahan (RAPBD-P)  2020','Biasa/Terbuka',0,'Dipinjam',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-12 06:59:33'),(15,1,'Bidang Pengelolaan Arsip','ARS-2017-00-0002','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2017 (3 Berkas)',NULL,2017,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)  2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(16,1,'Bidang Pengelolaan Arsip','ARS-2019-00-0001','900.1.1.4','Anggaran Pendapatan dan Belanja Daerah Perubahan (RAPBD-P)','Rencana Kerja Perubahan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2019 (3 Berkas)',NULL,2019,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 2','Anggaran Pendapatan dan Belanja Daerah Perubahan (RAPBD-P)  2019','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 04:09:47'),(17,1,'Bidang Pengelolaan Arsip','ARS-2020-00-0002','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Dan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2020',NULL,2020,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)  2020','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 06:58:27'),(18,1,'Bidang Pengelolaan Arsip','ARS-2020-11-0003','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Dan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2021','2020-11-12',2020,11,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD) November 2020','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(19,1,'Bidang Pengelolaan Arsip','ARS-2011-11-0001','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Dan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2012 1.24.01 Kantor Kearsipan Dan Dokumen (4 Berkas)','2011-11-30',2011,11,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD) November 2011','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(20,1,'Bidang Pengelolaan Arsip','ARS-2013-11-0001','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Dan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2014 1.24.01 Kantor Kearsipan Dan Dokumen (1 Berkas)','2013-11-07',2013,11,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD) November 2013','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(21,1,'Bidang Pengelolaan Arsip','ARS-2013-11-0002','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Dan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2013 1.24.01 Kantor Kearsipan Dan Dokumen (1 Berkas)','2013-11-28',2013,11,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD) November 2013','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(22,1,'Bidang Pengelolaan Arsip','ARS-2015-00-0001','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Dan Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2015 1.24.01 Kantor Kearsipan Dan Dokumen (3 Berkas)',NULL,2015,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)  2015','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(23,1,'Bidang Pengelolaan Arsip','ARS-2016-00-0002','900.1.1.2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)','Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKA-SKPD) Tahun Anggaran 2016 1.24.01 - Kantor Kearsipan Dan Dokumen (1 Berkas)',NULL,2016,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 2','Penyusunan Rencana Kerja Anggaran Satuan Kerja Perangkat Daerah (RKASKPD)  2016','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(24,1,'Bidang Pengelolaan Arsip','ARS-2009-00-0002','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Dokumen Pemilihan, Nomor: 602.1/001-DIPA-PAN-INDUSTRI/2009, Pemilihan Penyedia Barang, Metoda Pelelangan Umum Dengan Pascakualifikasi Metoda Penyampaian Penawaran Dua Sampul, Metoda Evaluasi Sistem Gugur Kontrak Berdasarkan Bentuk Turn Key (Terima Jadi) Pekerjaan Pengadaan Mesin/Peralatan Produksi Industri Kecil Dan Menengah Tahun Anggaran 2009',NULL,2009,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2009','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(25,1,'Bidang Pengelolaan Arsip','ARS-2012-00-0001','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor 602.21/ 03-APBD.KKD.FUMIGASI/2012, Tanggal: 15 Maret 2012, Kegiatan Pemeliharaan Rutin/Berkala Sarana Pengolahan Dan Penyimpanan Arsip Pekerjaan Belanja Peralatan Kebersihan Dan Bahan Pembersih Lokasi Kabupaten Cirebon, Biaya Rp. 18.788.000,00 Waktu Pelaksanaan Tujuh), Hari Kalender Tgl. Mulai Tge 15 Maret 2012 S.D Tgl 21 Maret 2012, Pt: Portal Bhakti Yudha Jl.Kusama Indah V Blok Sampiran Rt. 09/04 Ds. Setu Kulon Kec. Weru Kab. Cirebon',NULL,2012,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2012','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(26,1,'Bidang Pengelolaan Arsip','ARS-2014-00-0002','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor : 602.21/17-APBD-PA-Kearsipan & Dok/2014, Tanggal 18 Agustus 2014, Kegiatan Pemeliharaan Rutin/Berkala Gedung Kantor Pekerjaan Belanja Pemeliharaan Gedung/Kantor (Pengecatan Gedung Kantor, Rehabilitasi Kamar Mandi 3 Unit, Dan Pemasangan Plafond Kantor), Lokasi Kabupaten Cirebon, Biaya Rp. 44.223.000,00 Waktu Pelaksanaan 30 (Tiga Puluh), Mulai Tgl. 18 Agustus 2014 S.D Tgl 16 September 2014, Cv. Dua Putri Jl. Cideng Jaya Rt.04/04 Desa Kertawinangun Kec. Kedawung Kab. Cirebon',NULL,2014,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2014','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(27,1,'Bidang Pengelolaan Arsip','ARS-2015-11-0002','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Pemerintah Kabupaten Cirebon, Kantor Kearsipan Dan Dokumen, Jl. Sunan Kalijaga No.1, Tlp. (0231)8330595 Sumber, Surat Perintah Kerja No: 602.21/41/APBD.PPK.KKD/2015, Kegiatan: Rehabilitasi Sedang/Berat Gedung Kantor, Pekerjaan: Belanja Pemeliharaan Gedung Kantor (Pengecatan Gedung), Biaya: Rp. 19.710.000 (Sembilan Belas Juta Tujuh Ratus Sepuluh Ribu Rupiah), Pelaksana Cv. Cipta Andi Laksana Komplek Cempaka Arum Blok.D No. 336 Kecamatan Talun Kabupaten Cirebon Jangka Waktu, Pelaksanaan: 15(Lima Belas) Hari Kalender 24 November 2015 S.D. 09 Desember 2015, Tahun Anggaran 2015 (5 Berkas)','2015-11-24',2015,11,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya November 2015','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(28,1,'Bidang Pengelolaan Arsip','ARS-2017-00-0003','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor 602.21/180-APBD-PPK-Sekrt/2017, Tanggal: 22 September 2017, Kegiatan Penyediaan Perlengkapan Dan Peralatan Kantor, Pekerjaan Pengadaan Perlengkapan Dan Peralatan Kantor (Abt), Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon, Biaya Rp. 57.950.000, Waktu Pelaksanaan 35 Hari Kalender, Tgl 22 September 2017 S/D 26 Oktober 2017, Penyedia Barang Cv. Sawala Karya Prima Dusun I Rt.002 Rw.001 Desa Kepompongan Kec. Talun Kab. Cirebon',NULL,2017,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(29,1,'Bidang Pengelolaan Arsip','ARS-2017-00-0004','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.21/13... -APBD-KPA-Disarpus/2017, Tanggal 14 Oktober 2017, Kegiatan: Pengadaan, Pengolahan Buku Perpustakaan & Bahan-Bahan Perpustakaan, Pekerjaan: Belanja Bahan / Material (Belanja Bahan Kimia), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Ji. Sunan Kali Jaga No.01 Kec. Sumber, Biaya Rp. 43.750.000,00 (Empat Puluh Tiga Juta Tujuh Ratus Lima Puluh Ribu Rupiah), Waktu Pelaksanaan: 2 (Dua) Hari Kalender, Mulai Tanggal 14 Oktober 2017 S/D, 15 Oktober 2017, Cv. Mitra Mandiri, Jl. Raya Fatahillah No.51 Blok Kesambi Rt.03/Rw. 03 Megu Cilik Kec. Weru Kabupaten Cirebon',NULL,2017,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(30,1,'Bidang Pengelolaan Arsip','ARS-2017-00-0005','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.21/14-APBD-KPA-Disarpus/2017, Tanggal 20 Oktober 2017, Kegiatan: Pemeliharaan Rutin / Berkala Sarana Pengolahan Dan Penyimpanan Arsip, Pekerjaan: Belanja Bahan / Material (Belanja Bahan Κimia), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon Jl. Sunan Kali Jaga No.01 Kec. Sumber, Biaya Rp. 34.440.000,00 (Tiga Puluh Empat Juta Empat Ratus Empat Puluh Ribu Rupiah), Waktu Pelaksanaan: 2 (Dua) Hari Kalender Mulai Tanggal 20 Oktober 2017 S/D. 21 Oktober 2017, Cv. Triana Bhakti, Perumahan Pondok Mutiara Blok M4/ N2 Ji. Nyi Mas Gede Cangkring Desa Tegalsari Kec. Plered Kabupaten Cirebon',NULL,2017,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(31,1,'Bidang Pengelolaan Arsip','ARS-2017-00-0006','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor : 602.21/46-APBD-PPK-B.Pengemb/2017, Tanggal: 05 Mei 2017, Kegiatan Penyediaan Bantuan Pengembangan Perpustakaan Dan Minat Baca Di Daerah, Pekerjaan Pengadaan Rak/Lemari Arsip Kayu, Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon, Biaya Rp. 18.200.000, Waktu Pelaksanaan 20 Hari Kalender, Tgl 05 Mei 2017 S/D 26 Mei 2017, Penyedia Barang, Cv. Chikal Perdana Abadi Blok Barat Rt.02 Rw.03 Desa Kecomberan Kec. Talun Kab. Cirebon',NULL,2017,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(32,1,'Bidang Pengelolaan Arsip','ARS-2017-04-0007','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor 602.21/60-APBD-PPK-B.Pengemb/2017, Tanggal 12 April 2017, Kegiatan Penyediaan Bantuan Pengembangan Perpustakaan Dan Minat Baca Di Daerah, Pekerjaan Pengadaan Rak Buku/Tv/Kembang, Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon, Biaya Rp. 173.500.000, Waktu Plaksanaan 40 Hari Kalender, Tgl 12 April 2017 S/D 26 Mei 2017 Penyedia Barang, Cv. Putri Pratama, Jalan Watu Blok Nenggala Mekar Rt.003 Rw.005 Ds.Suranenggala Kec. Suranenggala Kab.Cirebon','2017-04-12',2017,4,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(33,1,'Bidang Pengelolaan Arsip','ARS-2017-04-0008','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.21/60-APBD-PPK-B.Pengemb/2017, Tanggal: 12 April 2017, Kegiatan Penyediaan Bantuan Pengembangan Perpustakaan Dan Minat Baca Di Daerah, Pekerjaan: Pengadaan Rak Buku/Tv/Kembang Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon, Biaya Rp. 173.500.000, Waktu Pelaksanaan 40 Hari Kerja Kalender Tgl 12 April 2017 D/D 26 Mei 2017, Penyedia Barang, Cv.Putri Pratama Jalan Watu Blok Nenggala Mekar Rt.003rw.005 Ds.Suranenggala Kec.Suranenggala Kab.Cirebon','2017-04-12',2017,4,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(34,1,'Bidang Pengelolaan Arsip','ARS-2017-11-0009','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.21/…..-APBD-PPK- Dinkearsperpus/2017, Tanggal 01 November 2017, Kegiatan: Belanja Pemeliharaan Rutin/Berkala Gedung Kantor, Pekerjaan: Pemeliharaan Gedung Kantor (Rehab Auning), Lokasi : Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon Jl.Sunan Kalijaga No.01kec.Sumber, Biaya Rp. 39.785.000,- (Tiga Puluh Sembilan Juta Tujuh  Ratus Delapan Puluh Lima Ribu Rupiah), Waktu Pelaksanaan: 15(Lima Belas), Hari Kalender Mulai Tanggal 01 November 2017 S/D 15 November 2017, Cv.Cipta Andi Laksana General Trade, Supplier, Consultan And Construction Btn Cempaka Arum, Jl. Flamboyan B.78 Kec.Talun Kabupaten Cirebon','2017-11-01',2017,11,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya November 2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(35,1,'Bidang Pengelolaan Arsip','ARS-2017-11-0010','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.21/-APBD-PPK- Dinkearsperpus/2017, Tanggal: 01 Nopember 2017, Kegiatan: Belanja Pemeliharaan Rutin/Berkala Gedung Kantor, Pekerjaan: Pemeliharaan Gedung Kantor (Rehab Auning), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon Jl. Sunan Kalijaga No.01 Kec. Sumber, Biaya Rp. 39.785.000, (Tiga Puluh Sembilan Juta Tujuh Ratus Delapan Puluh Lima Ribu Rupiah), Waktu Pelaksanaan: 15 (Lima Belas) Hari Kalender Mulai Tanggal 01 November 2017 S/D. 15 November 2017, Cv. Cipta Andi Laksana General Trade, Supplier, Consultan And Construction Btn Cempaka Arum Ji. Flamboyan B.78 Kec. Talun Kabupaten Cirebon','2017-11-01',2017,11,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya November 2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(36,1,'Bidang Pengelolaan Arsip','ARS-2017-04-0011','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor Tanggal 602.21/75-APBD-PPK-B.Pengemb/2017, Tanggal 12 April 2017, Kegiatan: Pengadaan Sarana Penyimpanan Pekerjaan Pengadaan Rak Buku Besi, Lokasi: Dinas Kearsipan Dan Perpustakaan Kab.Cirebon, Biaya Rp. 98.100.000, Waktu Pelaksanaan 40 Hari Kalender, Tgl 12 April 2017 S/D 26 Mei 2017, Penyedia Barang, Cv. Putri Pratama Jalan Watu Blok Nenggala Mekar Rt.003 Rw.005 Ds.Suranenggala Kec. Suranenggala Kab.Cirebon','2017-04-12',2017,4,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2017','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(37,1,'Bidang Pengelolaan Arsip','ARS-2018-04-0003','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','KWITANSI Telah Terima Dari: BPP Bidang Layanan Pada Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Banyaknya: # EMPAT PULUH DELAPAN JUTA TIGA RATUS TUJUH PULUH LIMA RIBU RUPIAH #, Yaitu Untuk Membayar: Belanja Modal Pengadaan Perlengkapan Kantor Dan Pengadaan Meubelair Kegiatan Penyediaan Sarana Layanan Informasi Arsip, Sesuai Surat Pesanan (SP), Nomor: 602.21/..01-APBD-KPA.B.Layanan/2018, Tanggal 18 April 2018., Terbilang Rp.48.375.000,00','2018-04-27',2018,4,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(38,1,'Bidang Pengelolaan Arsip','ARS-2026-07-0004','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.71/06 - APBD-PPK. Sekret/2018, Tanggal 04 Jul 2015, Kegiatan Pengadaan Pakaian Dinas Beserta Perlengkapannya, Pekerjaan: Belanja Pakaian Dinas Harian (Pdh), Lokasi:Dinas Kearsipan Dan Perpustakaan Kab. Cirebon, Biaya Rp. 13.200.000,-, Waktu Pelaksanaan: 5 (Lima) Hari Kerja, Mulai: Tanggal 04 Juli S.D 10 Juli 2018, Penyedia Barang: Cv. Griya Indah Cemerlang Jl. Nyi Arum Sori Rt.02/02 Desa Comberan Kec. Talun Kab. Cirebon','2026-07-02',2026,7,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya Juli 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(39,1,'Bidang Pengelolaan Arsip','ARS-2026-07-0005','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor: 602.21/60.-APBD-KPA.B.Pengemb/2018 Tanggal: 16 Juli 2018, Kegiatan: Pembangunan Database Informasi Kearsipan, Pekerjaan: Belanja Modal Perangkat Lunak Pengembangan Aplikasi (Pengembangan Aplikasi Sipad), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 45.000.000,00, Waktu Pelaksanaan: 15 Hari Kalender Tanggal 16 Juli 2018 S.D 30 Juli 2018, Cv. Hanjuan Enginering Jalan Btn Karangsembung Permai Blok C2 No. 24 Rt. 002 Rw. 009 Desa/Kec. Karangsembung Kabupaten Cirebon','2026-07-13',2026,7,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya Juli 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(40,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0004','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Perintah Kerja, Nomor 602.21/56-APBD-PPK-B.Pengemb/2018, Tanggal: 14 Mei 2018, Kegiatan Pemeliharaan Peralatan Jaringan Informasi Kearsipan, Pekerjaan Pembuatan Ruang Server, Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon, Biaya Rp. 29.000.000, Waktu Pelaksanaan 25 Hari Kalender, Tgl 14 Mei 2018 S/D 08 Juni 2018, Penyedia Barang Cv. Cahaya Bintang Sentani Regency 2 Blok L.14 Rt.002 Rw.012 Kel.Tukmudal Kec. Sumber Kab. Cirebon',NULL,2018,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(41,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0005','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','CV.INDAH GRIYA CEMERLANG CONSTRACTOR SUPPLIER DAN PERDAGANGAN UMUM, Alamat: Jalan Nyi Arum Sari Rt.02/02 Desa Kecomberan Kec.Talun Kab.Cirebon, KWITANSI PEMBAYARAN Nomor Bukti KWITANSI/BUKTI PEMBAYARAN Sudah terima dari Jumlah Uang Terbilang Untuk Pembayaran, Pengguna Anggaran Dinas Kearsipan dan Perpustakaan Kab. Cirebon Rp. 17.900.000,- (Tujuh Belas Juta Sembilan Ratus Ribu Rupiah) Belanja Cetakan',NULL,2018,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2018','Biasa/Terbuka',0,'Dipinjam',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-12 07:07:19'),(42,1,'Bidang Pengelolaan Arsip','ARS-2018-04-0006','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','SURAT PESANAN, NOMOR: 602.21/APBD-KPA.B-Pengel/2018, TANGGAL: 18 APRIL 2018, Kegiatan: Pemeliharaan Rutin / Berkala Sarana Pengolahan Dan Penyimpanan Arsip, Pekerjaan: Belanja Bahan/Material (Belanja Bahan Kimia), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 48.126.800,00, Waktu Pelaksanaan: 02 Hari Kalender, Tanggal: 19 April 2018 S/D 20 April 2018, Cv. Mitra Mandiri, Jl. Raya Fatahillah No. 51 Blok Kesambi Rt. 03 Rw. 03 Megucilik Kec. Weru Kabupaten Cirebon','2018-04-18',2018,4,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(43,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0007','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Pemerintah Kabupaten Cirebon Dinas Kearsipan Dan Perpustakaan, Jln. Sunan kalijaga No. 1 Sumber Telp. (0231) 8330595 Fax. (0231) 8330595 SUMBER, SURAT PESANAN, Kegiatan: PEMELIHARAAN PERALATAN JARINGAN INFORMASI KEARSIPAN, Pekerjaan: BELANJA PEMELIHARAAN KOMPUTER DAN JARINGAN (PEMELIHARAAN SERVER), Lokasi: DINAS KEARSIPAN DAN PERPUSTAKAAN, SURAT PESANAN Nomor: 602.21/65 APBD-KPA.B.Pengemb/2018 Tanggal 30 JULI 2018, Biaya: Rp. 15.000.000,00, Waktu Pelaksanaan: 12 (Dua Belas) Hari Kalender 30 Juli 2018 S/D 10 Agustus 2018, CV. HANJUAN ENGINEERING, Jln. BTN Karangsembung Permai Blok C2 No. 24 RT 002 RW 009 Desa/Kec. Karangsembung Kabupaten Cirebon',NULL,2018,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(44,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0008','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Harga Perkiraan Sendiri (HPS), Kegiatan: Penyediaan Bantuan Pengembangan Perpustakaan Dan Minat Baca Di Daerah, Pekerjaan Pengadaan Rak Buku/Tv/Kembang, Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon Tahun Anggaran 2018',NULL,2018,NULL,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(45,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0009','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Harga Perkiraan Sendiri (HPS), Kegiatan Penyediaan Bantuan Pengembangan Perpustakaan Dan Minat Baca Di Daerah, Pekerjaan Pengadaan Kursi Lipat, Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon Tahun Anggaran 2018',NULL,2018,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(46,1,'Bidang Pengelolaan Arsip','ARS-2018-00-0010','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Harga Perkiraan Sendiri (HPS), Kegiatan Penyediaan Bantuan Pengembangan Perpustakaan Dan Minat Baca Di Daerah, Pekerjaan Pengadaan Meja Belajar, Lokasi Dinas Kearsipan Dan Perpustakaan Kab.Cirebon Tahun Anggaran 2018',NULL,2018,NULL,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya  2018','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(47,1,'Bidang Pengelolaan Arsip','ARS-2019-04-0002','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Pesanan, Nomor 602.21/18-APBD-KPA.B.Pengemb/2019, Tanggal: 04 April 2019, Kegiatan Pemeliharaan Peralatan Jaringan Informasi Kearsipan, Pekerjaan Belanja Pemeliharaan Komputer Dan Jaringan (Pemeliharaan Server), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 34.440.000,00, Waktu Pelaksanaan: 12 Hari Kalender Tanggal 04 April 2019 S.D 15 April 2019, Cv. Jm Solusi Ji. Kandang Perahu No. 32 Rt. 001 Rw. 011 Kel. Karyamulya, Kesambi Kota Cirebon','2019-04-04',2019,4,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2019','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(48,1,'Bidang Pengelolaan Arsip','ARS-2019-04-0003','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Surat Pesanan, Nomor 602.21/18-APBD-KPA.B.Pengemb/2019, Tanggal: 04 April 2019, Kegiatan: Pemeliharaan Peralatan Jaringan Informasi Kearsipan, Pekerjaan: Belanja Pemeliharaan Komputer Dan Jaringan (Pemeliharaan Server), Lokasi Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 34.440.000,00, Waktu Pelaksanaan: 12 Hari Kalender, Tanggal 04 April 2019 S.D 15 April 2019, Cv. Jm Solusi Ji. Kandang Perahu No. 32 Rt. 001 Rw. 011 Kel. Karyamulya, Kesambi Kota Cirebon','2019-04-04',2019,4,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya April 2019','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(49,1,'Bidang Pengelolaan Arsip','ARS-2026-07-0006','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Dokumen Kontrak, Nomor: 602.21/63-APBD-KPA.B.Pengemb/2019, Tanggal: 15 Juli 2019, Kegiatan: Penyediaan Bahan Pustaka Perpustakaan Umum Daerah, Pekerjaan: Belanja Modal Pengadaan Buku Ilmu Pengetahuan Umum, Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 73.833.000,-, Waktu Pelaksanaan: 10 (Sepuluh) Hari Kalender (15 S.D. 24 Juli 2019), Pt. Rajagrafindo Persada, Alamat: Jl. Raya Leuwinanggung No.112 Kel. Leuwinanggung Kec. Tapos Kota Depok','2026-07-15',2026,7,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya Juli 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(50,1,'Bidang Pengelolaan Arsip','ARS-2026-07-0007','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Dokumen Kontrak, Nomor :602.21/70.1-APBD-KPA.B. Pengemb/2019, Tanggal: 22 Jull 2019, Kegiatan: Pengadaan Sarana Penyimpanan, Pekerjaan: Belanja Modal Pengadaan Rak/Lemari Arsip Kayu (Roll O\'pack), Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 60.060.000,-, Waktu Pelaksanaan: 15 (Lima Belas) Hari Kalender (22 Juli S.D. 06 Agustus 2019), Cv. Cahaya Bintang, Alamat: Sentani Regency 2 Blok 1.14 Rt.002 Rw.012 Kelurahan Tukmudal Kecamatan Sumber Kabupaten Cirebon Jawa Barat','2026-07-22',2026,7,'tanda tangan dan cap asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya Juli 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03'),(51,1,'Bidang Pengelolaan Arsip','ARS-2026-07-0008','900.1.4.8','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya','Dokumen Kontrak, Nomor: 602.21/63-APBD-KPA.B. Pengemb/2019, Tanggal: 15 Juli 2019 Kegiatan: Penyediaan Bahan Pustaka Perpustakaan Umum Daerah, Pekerjaan: Belanja Modal Pengadaan Buku Ilmu Pengetahuan Umum, Lokasi: Dinas Kearsipan Dan Perpustakaan Kabupaten Cirebon, Biaya Rp. 73.833.000,-, Waktu Pelaksanaan: 10 (Sepuluh) Hari Kalender (15 S.D. 24 Juli 2019), Pt. Rajagrafindo Persada, Alamat: Jl. Raya Leuwinanggung No.112 Kel. Leuwinanggung Kec. Tapos Kota Depok','2026-07-15',2026,7,'tanda tangan dan cap tidak asli',NULL,NULL,NULL,'Boks 3','Dokumen Realisasi Pencairan Dana Bantuan Luar Negeri, yaitu: Surat Perintah Pencairan Dana, SPM beserta lampirannya, a.l.: SPP, Kontrak,  BA, dan data pendukung lainnya Juli 2026','Biasa/Terbuka',0,'Tersedia',NULL,NULL,NULL,NULL,1,'2026-11-11 02:25:03','2026-11-11 02:25:03');
/*!40000 ALTER TABLE `arsip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bukti_foto`
--

DROP TABLE IF EXISTS `bukti_foto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bukti_foto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `sumber` varchar(20) NOT NULL DEFAULT 'Webcam',
  `file_path` varchar(255) NOT NULL,
  `nama_asli` varchar(255) DEFAULT NULL,
  `ocr_text` text,
  `diunggah_oleh` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bukti_foto_surat_id_foreign` (`surat_id`),
  KEY `bukti_foto_diunggah_oleh_foreign` (`diunggah_oleh`),
  CONSTRAINT `bukti_foto_diunggah_oleh_foreign` FOREIGN KEY (`diunggah_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bukti_foto_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surat_peminjaman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bukti_foto`
--

LOCK TABLES `bukti_foto` WRITE;
/*!40000 ALTER TABLE `bukti_foto` DISABLE KEYS */;
INSERT INTO `bukti_foto` VALUES (3,13,'Serah Terima','Unggah CV','bukti/bukti_42762272-61bc-4f71-a881-63c77f0d35af.jpg',NULL,NULL,1,'2026-11-11 04:03:43','2026-11-11 04:03:43'),(4,16,'Serah Terima','Kamera CV','bukti/bukti_98c5eff0-d05c-436c-abbc-209283ab4701.jpg',NULL,NULL,1,'2026-11-11 06:56:34','2026-11-11 06:56:34');
/*!40000 ALTER TABLE `bukti_foto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `impor_arsip`
--

DROP TABLE IF EXISTS `impor_arsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `impor_arsip` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_pengolah_id` bigint unsigned NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `jumlah_baris` int NOT NULL DEFAULT '0',
  `jumlah_berhasil` int NOT NULL DEFAULT '0',
  `jumlah_gagal` int NOT NULL DEFAULT '0',
  `dibuat_oleh` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `impor_arsip_unit_pengolah_id_foreign` (`unit_pengolah_id`),
  KEY `impor_arsip_dibuat_oleh_foreign` (`dibuat_oleh`),
  CONSTRAINT `impor_arsip_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `impor_arsip_unit_pengolah_id_foreign` FOREIGN KEY (`unit_pengolah_id`) REFERENCES `unit_pengolah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `impor_arsip`
--

LOCK TABLES `impor_arsip` WRITE;
/*!40000 ALTER TABLE `impor_arsip` DISABLE KEYS */;
INSERT INTO `impor_arsip` VALUES (1,1,'DAFTAR ARSIP UNTUK SISTEM APLIKASI PKL.xlsx',48,48,0,1,'2026-11-11 02:25:04','2026-11-11 02:25:04');
/*!40000 ALTER TABLE `impor_arsip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_audit`
--

DROP TABLE IF EXISTS `log_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_audit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `entitas` varchar(100) NOT NULL,
  `entitas_id` bigint unsigned DEFAULT NULL,
  `detail` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `log_audit_user_id_foreign` (`user_id`),
  CONSTRAINT `log_audit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_audit`
--

LOCK TABLES `log_audit` WRITE;
/*!40000 ALTER TABLE `log_audit` DISABLE KEYS */;
INSERT INTO `log_audit` VALUES (1,1,'Tambah Data Peminjam','Peminjam',4,'{\"nama\": \"Yena\", \"jenis\": \"Internal\"}','2026-11-11 02:27:14'),(2,1,'Tambah Data Peminjam','Peminjam',5,'{\"nama\": \"Yena\", \"jenis\": \"Internal\"}','2026-11-11 02:27:17'),(3,1,'Hapus Data Peminjam','Peminjam',NULL,'{\"nama\": \"Yena\"}','2026-11-11 02:27:28'),(4,1,'Tambah Data Peminjam','Peminjam',6,'{\"nama\": \"Amel\", \"jenis\": \"Eksternal\"}','2026-11-11 02:28:36'),(21,1,'Buat Surat Peminjaman','SuratPeminjaman',13,'{\"peminjam\": \"Amel\", \"nomor_surat\": \"001/SPA/ARS/XI/2026\", \"jumlah_arsip\": 1}','2026-11-11 04:03:43'),(22,1,'Buat Surat Peminjaman','SuratPeminjaman',14,'{\"peminjam\": \"Faulan\", \"nomor_surat\": \"002/SPA/ARS/XI/2026\", \"jumlah_arsip\": 2}','2026-11-11 04:05:55'),(23,1,'Setujui Peminjaman','SuratPeminjaman',14,'{\"nomor_surat\": \"002/SPA/ARS/XI/2026\"}','2026-11-11 04:06:28'),(24,1,'Pengembalian Arsip','SuratPeminjaman',14,'{\"nomor_surat\": \"002/SPA/ARS/XI/2026\", \"penilaian_cnn\": [], \"surat_selesai\": true, \"item_dikembalikan\": 2}','2026-11-11 04:09:47'),(26,1,'Buat Surat Peminjaman','SuratPeminjaman',16,'{\"peminjam\": \"Faulan\", \"nomor_surat\": \"003/SPA/ARS/XI/2026\", \"jumlah_arsip\": 1}','2026-11-11 06:56:34'),(27,1,'Setujui Peminjaman','SuratPeminjaman',16,'{\"nomor_surat\": \"003/SPA/ARS/XI/2026\"}','2026-11-11 06:56:49'),(28,1,'Pengembalian Arsip','SuratPeminjaman',16,'{\"nomor_surat\": \"003/SPA/ARS/XI/2026\", \"penilaian_cnn\": [], \"surat_selesai\": true, \"item_dikembalikan\": 1}','2026-11-11 06:58:27'),(68,1,'Setujui Peminjaman','SuratPeminjaman',13,'{\"is_janjian\": false, \"nomor_surat\": \"001/SPA/ARS/XI/2026\", \"status_baru\": \"Aktif\", \"bentuk_layanan\": \"Fisik\"}','2026-11-12 06:59:33'),(79,1,'Buat Surat Peminjaman','SuratPeminjaman',42,'{\"peminjam\": \"Yena\", \"nomor_surat\": \"001/SPA/SEK/XI/2026\", \"jumlah_arsip\": 1, \"bentuk_layanan\": \"Fisik\", \"petugas_layanan\": \"awa\", \"petugas_layanan_bidang\": \"Sekretariat\"}','2026-11-12 07:03:41'),(80,1,'Buat Surat Peminjaman','SuratPeminjaman',43,'{\"peminjam\": \"Sabila\", \"nomor_surat\": \"004/SPA/ARS/XI/2026\", \"jumlah_arsip\": 1, \"bentuk_layanan\": \"Fisik\", \"petugas_layanan\": \"awa\", \"petugas_layanan_bidang\": \"Bidang Pengelolaan Arsip\"}','2026-11-12 07:05:57'),(81,1,'Setujui Peminjaman','SuratPeminjaman',43,'{\"is_janjian\": false, \"nomor_surat\": \"004/SPA/ARS/XI/2026\", \"status_baru\": \"Aktif\", \"bentuk_layanan\": \"Fisik\"}','2026-11-12 07:07:19'),(132,2,'Buat Surat Peminjaman','SuratPeminjaman',74,'{\"peminjam\": \"Faulan\", \"nomor_surat\": \"005/SPA/ARS/XI/2026\", \"jumlah_arsip\": 2, \"bentuk_layanan\": \"Fisik\", \"petugas_layanan\": \"awa\", \"petugas_layanan_bidang\": \"Bidang P2A\"}','2026-11-13 04:41:27');
/*!40000 ALTER TABLE `log_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_unit_pengolah_table',1),(2,'0001_01_01_000001_create_users_table',1),(3,'0001_01_01_000002_create_cache_table',1),(4,'0001_01_01_000003_create_jobs_table',1),(5,'2019_12_14_000001_create_personal_access_tokens_table',1),(6,'2026_11_05_000004_create_sipa_core_tables',1),(7,'2026_11_05_000005_widen_arsip_columns',1),(8,'2026_11_11_000006_add_jaminan_to_peminjam_and_surat_table',2),(9,'2026_11_11_000007_create_permintaan_reset_password_table',3),(10,'2026_11_11_000008_add_damage_assessment_columns',3),(11,'2026_11_12_000009_apply_interview_revisions',4),(12,'2026_11_12_000010_remove_arsip_classifications',5),(13,'2026_11_12_000011_add_p2a_and_petugas_layanan_to_sipa',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nomor_surat_counter`
--

DROP TABLE IF EXISTS `nomor_surat_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomor_surat_counter` (
  `unit_pengolah_id` bigint unsigned NOT NULL,
  `tahun` smallint NOT NULL,
  `terakhir` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`unit_pengolah_id`,`tahun`),
  CONSTRAINT `nomor_surat_counter_unit_pengolah_id_foreign` FOREIGN KEY (`unit_pengolah_id`) REFERENCES `unit_pengolah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nomor_surat_counter`
--

LOCK TABLES `nomor_surat_counter` WRITE;
/*!40000 ALTER TABLE `nomor_surat_counter` DISABLE KEYS */;
INSERT INTO `nomor_surat_counter` VALUES (1,2026,5),(2,2026,1);
/*!40000 ALTER TABLE `nomor_surat_counter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjam`
--

DROP TABLE IF EXISTS `peminjam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peminjam` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis` varchar(40) NOT NULL DEFAULT 'Internal',
  `nama` varchar(150) NOT NULL,
  `nip_nik` varchar(30) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `instansi_unit` varchar(150) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_hp` varchar(25) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jaminan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjam`
--

LOCK TABLES `peminjam` WRITE;
/*!40000 ALTER TABLE `peminjam` DISABLE KEYS */;
INSERT INTO `peminjam` VALUES (4,'Internal','Yena','32097538972618','Arsiparis','Bidang Arsiparis','sumber','0832917384628','yena@gmail.com',NULL,'2026-11-11 02:27:14','2026-11-11 02:27:14'),(6,'Eksternal','Amel','3208583679168','-','Universitas Muhammadiyah Cirebon','plered','086738628648','amel@gmail.com',NULL,'2026-11-11 02:28:35','2026-11-11 02:28:35'),(10,'Eksternal','Faulan','32086386863826','Mahasiswa','Universitas Muhammadiyah Cirebon','Bakung','089738468261',NULL,'Kartu Mahasiswa / Pelajar','2026-11-11 04:05:55','2026-11-11 04:05:55'),(22,'Eksternal','Sabila','32087537830872783','Mahasiswa','Universitas Muhammadiyah Cirebon','cirebon','089376284618',NULL,'Kartu Mahasiswa / Pelajar','2026-11-12 07:05:57','2026-11-12 07:05:57');
/*!40000 ALTER TABLE `peminjam` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_pengolah_id` bigint unsigned DEFAULT NULL,
  `kunci` varchar(100) NOT NULL,
  `nilai` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengaturan_kunci_unique` (`kunci`),
  KEY `pengaturan_unit_pengolah_id_foreign` (`unit_pengolah_id`),
  CONSTRAINT `pengaturan_unit_pengolah_id_foreign` FOREIGN KEY (`unit_pengolah_id`) REFERENCES `unit_pengolah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES (1,NULL,'instansi_nama','PEMERINTAH KABUPATEN CIREBON','2026-11-11 01:57:44','2026-11-11 01:57:44'),(2,NULL,'dinas_nama','DINAS KEARSIPAN DAN PERPUSTAKAAN','2026-11-11 01:57:44','2026-11-11 01:57:44'),(3,NULL,'dinas_alamat','Jalan Sunan Kalijaga No. 1  Telp (0231) 323234 Fax. 323234','2026-11-11 01:57:44','2026-11-11 01:57:44'),(4,NULL,'dinas_website','www.disarpus.cirebonkab.go.id','2026-11-11 01:57:44','2026-11-11 01:57:44'),(5,NULL,'dinas_email','disarpus@cirebonkab.go.id','2026-11-11 01:57:44','2026-11-11 01:57:44'),(6,NULL,'dinas_kodepos','SUMBER - 45611','2026-11-11 01:57:44','2026-11-11 01:57:44'),(7,NULL,'format_nomor_surat','{URUT}/SPA/{KODE_BIDANG}/{BULAN_ROMAWI}/{TAHUN}','2026-11-11 01:57:44','2026-11-11 01:57:44'),(8,NULL,'lama_pinjam_default','7','2026-11-11 01:57:44','2026-11-11 01:57:44'),(9,NULL,'pernyataan_peminjaman','Saya berjanji menjaga keutuhan, kerahasiaan, dan keamanan arsip fisik yang dipinjam, tidak menggandakan atau meminjamkannya ke pihak lain tanpa izin sah, dan wajib mengembalikan tepat waktu sesuai rentang peminjaman. Saya bertanggung jawab penuh bila arsip hilang atau rusak.','2026-11-11 01:57:44','2026-11-11 01:57:44');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permintaan_reset_password`
--

DROP TABLE IF EXISTS `permintaan_reset_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permintaan_reset_password` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `catatan_pemohon` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Menunggu',
  `diproses_oleh` bigint unsigned DEFAULT NULL,
  `diproses_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permintaan_reset_password_user_id_foreign` (`user_id`),
  KEY `permintaan_reset_password_diproses_oleh_foreign` (`diproses_oleh`),
  KEY `permintaan_reset_password_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `permintaan_reset_password_diproses_oleh_foreign` FOREIGN KEY (`diproses_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permintaan_reset_password_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permintaan_reset_password`
--

LOCK TABLES `permintaan_reset_password` WRITE;
/*!40000 ALTER TABLE `permintaan_reset_password` DISABLE KEYS */;
/*!40000 ALTER TABLE `permintaan_reset_password` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perpanjangan`
--

DROP TABLE IF EXISTS `perpanjangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perpanjangan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `tanggal_kembali_lama` date NOT NULL,
  `tanggal_kembali_baru` date NOT NULL,
  `alasan` text NOT NULL,
  `disetujui_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `perpanjangan_surat_id_foreign` (`surat_id`),
  KEY `perpanjangan_disetujui_oleh_foreign` (`disetujui_oleh`),
  CONSTRAINT `perpanjangan_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `perpanjangan_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surat_peminjaman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perpanjangan`
--

LOCK TABLES `perpanjangan` WRITE;
/*!40000 ALTER TABLE `perpanjangan` DISABLE KEYS */;
/*!40000 ALTER TABLE `perpanjangan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('72A37PPsUFGMrZaGKveOXYTy7GmjioCODMbiywif',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.146.0 Chrome/150.0.7871.250 Electron/43.8.6 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSURNckxhQUM0M3hadWxKdUIyZ0UyUVRYaDBzNzh4V1lEWTZ2Y1VFaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wZW1pbmRhaSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794614951),('Kx0jc1k7UUOLHdqRxEpohDfXQTir0rgqEahWjuRE',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieDhlVW5wa1dxU1BKNzdhVDd2anRtTndYRkYxbjZQSWlMakZyUGhtSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNib3IiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1794629721);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_peminjaman`
--

DROP TABLE IF EXISTS `surat_peminjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_peminjaman` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(60) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `unit_pengolah_id` bigint unsigned NOT NULL,
  `peminjam_id` bigint unsigned NOT NULL,
  `bentuk_layanan` varchar(40) NOT NULL DEFAULT 'Fisik',
  `maksud_keperluan` text NOT NULL,
  `jaminan` varchar(100) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `lama_pinjam_hari` smallint NOT NULL,
  `diperpanjang_kali` smallint NOT NULL DEFAULT '0',
  `butuh_persetujuan` tinyint(1) NOT NULL DEFAULT '1',
  `status_persetujuan` varchar(30) NOT NULL DEFAULT 'Menunggu',
  `disetujui_oleh` bigint unsigned DEFAULT NULL,
  `tanggal_disetujui` timestamp NULL DEFAULT NULL,
  `alasan_penolakan` text,
  `metode_ttd` varchar(20) DEFAULT NULL,
  `ttd_peminjam_path` varchar(255) DEFAULT NULL,
  `surat_ttd_path` varchar(255) DEFAULT NULL,
  `petugas_id` bigint unsigned NOT NULL,
  `petugas_layanan_nama` varchar(150) DEFAULT NULL,
  `petugas_layanan_nip` varchar(30) DEFAULT NULL,
  `petugas_layanan_bidang` varchar(150) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Draf',
  `is_janjian` tinyint(1) NOT NULL DEFAULT '0',
  `rencana_ambil_pada` datetime DEFAULT NULL,
  `catatan_janjian` varchar(255) DEFAULT NULL,
  `tanggal_selesai` timestamp NULL DEFAULT NULL,
  `catatan` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_peminjaman_nomor_surat_unique` (`nomor_surat`),
  KEY `surat_peminjaman_peminjam_id_foreign` (`peminjam_id`),
  KEY `surat_peminjaman_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `surat_peminjaman_petugas_id_foreign` (`petugas_id`),
  KEY `surat_peminjaman_unit_pengolah_id_status_index` (`unit_pengolah_id`,`status`),
  KEY `surat_peminjaman_tanggal_kembali_index` (`tanggal_kembali`),
  KEY `idx_surat_layanan_janjian` (`bentuk_layanan`,`is_janjian`),
  CONSTRAINT `surat_peminjaman_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_peminjaman_peminjam_id_foreign` FOREIGN KEY (`peminjam_id`) REFERENCES `peminjam` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_peminjaman_petugas_id_foreign` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_peminjaman_unit_pengolah_id_foreign` FOREIGN KEY (`unit_pengolah_id`) REFERENCES `unit_pengolah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_peminjaman`
--

LOCK TABLES `surat_peminjaman` WRITE;
/*!40000 ALTER TABLE `surat_peminjaman` DISABLE KEYS */;
INSERT INTO `surat_peminjaman` VALUES (13,'001/SPA/ARS/XI/2026','2026-11-11',1,6,'Fisik','perlu','KTP','2026-11-11','2026-11-17',7,0,1,'Disetujui',1,'2026-11-12 06:59:33',NULL,'Digital','ttd/ttd_1b76b17a-f337-4994-ab9c-364d4dac777d.png',NULL,1,NULL,NULL,NULL,'Aktif',0,NULL,NULL,NULL,NULL,'2026-11-11 04:03:43','2026-11-12 06:59:33'),(14,'002/SPA/ARS/XI/2026','2026-11-11',1,10,'Fisik','perlu','Kartu Mahasiswa / Pelajar','2026-11-11','2026-11-12',2,0,1,'Disetujui',1,'2026-11-11 04:06:28',NULL,'Digital','ttd/ttd_505deb82-654d-4475-940d-d02d8ddaef50.png',NULL,1,NULL,NULL,NULL,'Selesai',0,NULL,NULL,'2026-11-11 04:09:47',NULL,'2026-11-11 04:05:55','2026-11-11 04:09:47'),(16,'003/SPA/ARS/XI/2026','2026-11-11',1,10,'Fisik','butuh','KTP','2026-11-11','2026-11-14',4,0,1,'Disetujui',1,'2026-11-11 06:56:49',NULL,'Digital','ttd/ttd_30b4c666-9dca-4b06-b9fe-007f9da42b9a.png',NULL,1,NULL,NULL,NULL,'Selesai',0,NULL,NULL,'2026-11-11 06:58:27',NULL,'2026-11-11 06:56:33','2026-11-11 06:58:27'),(42,'001/SPA/SEK/XI/2026','2026-11-12',2,4,'Fisik','perlu','KTP','2026-11-12','2026-11-17',6,0,1,'Ditolak',1,'2026-11-12 07:06:49','mohon maaf ditolak','Manual',NULL,NULL,1,'awa','-','Sekretariat','Ditolak',0,NULL,NULL,NULL,NULL,'2026-11-12 07:03:41','2026-11-12 07:06:49'),(43,'004/SPA/ARS/XI/2026','2026-11-12',1,22,'Fisik','perlu untuk penelitian','Kartu Mahasiswa / Pelajar','2026-11-12','2026-11-17',6,0,1,'Disetujui',1,'2026-11-12 07:07:19',NULL,'Digital','ttd/ttd_799a0abc-e5ea-4cdc-a6b8-45f486989e47.png',NULL,1,'awa','-','Bidang Pengelolaan Arsip','Aktif',0,NULL,NULL,NULL,NULL,'2026-11-12 07:05:57','2026-11-12 07:07:19'),(74,'005/SPA/ARS/XI/2026','2026-11-13',1,10,'Fisik','perlu','Kartu Mahasiswa / Pelajar','2026-11-13','2026-11-18',6,0,1,'Menunggu',NULL,NULL,NULL,'Digital','ttd/ttd_52e80bb2-8cc1-40c2-aba3-27da8677b7e2.png',NULL,2,'awa','198805202010012005','Bidang P2A','Menunggu Persetujuan',0,NULL,NULL,NULL,NULL,'2026-11-13 04:41:27','2026-11-13 04:41:27');
/*!40000 ALTER TABLE `surat_peminjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_peminjaman_item`
--

DROP TABLE IF EXISTS `surat_peminjaman_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_peminjaman_item` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `arsip_id` bigint unsigned NOT NULL,
  `kondisi_keluar` varchar(255) NOT NULL DEFAULT 'Baik',
  `tanggal_kembali_riil` timestamp NULL DEFAULT NULL,
  `kondisi_kembali` varchar(255) DEFAULT NULL,
  `skor_integritas` tinyint unsigned DEFAULT NULL,
  `ringkasan_kerusakan` varchar(255) DEFAULT NULL,
  `detail_kerusakan_json` json DEFAULT NULL,
  `foto_kembali_path` varchar(255) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_peminjaman_item_surat_id_foreign` (`surat_id`),
  KEY `surat_peminjaman_item_arsip_id_foreign` (`arsip_id`),
  CONSTRAINT `surat_peminjaman_item_arsip_id_foreign` FOREIGN KEY (`arsip_id`) REFERENCES `arsip` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_peminjaman_item_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surat_peminjaman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_peminjaman_item`
--

LOCK TABLES `surat_peminjaman_item` WRITE;
/*!40000 ALTER TABLE `surat_peminjaman_item` DISABLE KEYS */;
INSERT INTO `surat_peminjaman_item` VALUES (3,13,14,'Baik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-11 04:03:43','2026-11-11 04:03:43'),(4,14,16,'Baik','2026-11-11 04:09:47','Baik',NULL,NULL,NULL,NULL,NULL,'2026-11-11 04:05:55','2026-11-11 04:09:47'),(5,14,5,'Baik','2026-11-11 04:09:47','Baik',NULL,NULL,NULL,NULL,NULL,'2026-11-11 04:05:55','2026-11-11 04:09:47'),(6,16,17,'Baik','2026-11-11 06:58:27','Baik',NULL,NULL,NULL,NULL,NULL,'2026-11-11 06:56:33','2026-11-11 06:58:27'),(32,42,33,'Baik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-12 07:03:41','2026-11-12 07:03:41'),(33,43,41,'Baik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-12 07:05:57','2026-11-12 07:05:57'),(64,74,6,'Baik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-13 04:41:27','2026-11-13 04:41:27'),(65,74,50,'Baik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-13 04:41:27','2026-11-13 04:41:27');
/*!40000 ALTER TABLE `surat_peminjaman_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_pengolah`
--

DROP TABLE IF EXISTS `unit_pengolah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_pengolah` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `kepala_nama` varchar(150) DEFAULT NULL,
  `kepala_nip` varchar(30) DEFAULT NULL,
  `lama_pinjam_standar` smallint NOT NULL DEFAULT '7',
  `lama_pinjam_maks` smallint NOT NULL DEFAULT '7',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_pengolah_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_pengolah`
--

LOCK TABLES `unit_pengolah` WRITE;
/*!40000 ALTER TABLE `unit_pengolah` DISABLE KEYS */;
INSERT INTO `unit_pengolah` VALUES (1,'ARS','Bidang Pengelolaan Arsip','H. Ahmad Supriyadi, S.AP','197508122005011002',7,7,1,'2026-11-11 01:57:40','2026-11-11 01:57:40'),(2,'SEK','Sekretariat','Dra. Hj. Sri Wahyuni, M.Si','197204151998032004',7,7,1,'2026-11-11 01:57:40','2026-11-11 01:57:40'),(3,'PERP','Bidang Perpustakaan','Drs. H. Maman Suratman','196911201994031003',7,7,1,'2026-11-11 01:57:40','2026-11-11 01:57:40'),(4,'P2A','Bidang P2A','Kepala Bidang P2A',NULL,7,7,1,'2026-11-12 07:02:45','2026-11-12 07:02:45');
/*!40000 ALTER TABLE `unit_pengolah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_pengolah_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `peran` varchar(30) NOT NULL DEFAULT 'Operator',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_unit_pengolah_id_foreign` (`unit_pengolah_id`),
  CONSTRAINT `users_unit_pengolah_id_foreign` FOREIGN KEY (`unit_pengolah_id`) REFERENCES `unit_pengolah` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Super Admin SIPA','198001012006041001','superadmin','superadmin.sipa@gmail.com','$2y$12$LUIXWxsplgI66XWgewdLderSb/ZFTr0pUIlyfSuE3IsDwyjD/yMbC','SuperAdmin',1,NULL,'2026-11-11 01:57:42','2026-11-11 01:57:42'),(2,1,'Petugas Arsip Dinamis','198805202010012003','operator.arsip','operator.arsip@gmail.com','$2y$12$MHYlWHllN9.8X4YCImPMzu.6d19CxPs6Qr4jWKhdW7kdC21DkjJ8C','Operator',1,NULL,'2026-11-11 01:57:43','2026-11-11 01:57:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-16  8:28:27
