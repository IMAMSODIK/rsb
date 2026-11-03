-- MySQL dump 10.13  Distrib 26.10.0, for macos26.6 (arm64)
--
-- Host: 127.0.0.1    Database: db_rsb
-- ------------------------------------------------------
-- Server version	26.10.0

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
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
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `dokumen_renstras`
--

DROP TABLE IF EXISTS `dokumen_renstras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dokumen_renstras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun` year NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_asli` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ukuran` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dokumen_renstras`
--

LOCK TABLES `dokumen_renstras` WRITE;
/*!40000 ALTER TABLE `dokumen_renstras` DISABLE KEYS */;
/*!40000 ALTER TABLE `dokumen_renstras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `iku_kinerja_layanan`
--

DROP TABLE IF EXISTS `iku_kinerja_layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iku_kinerja_layanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sasaran_strategis_id` bigint unsigned DEFAULT NULL,
  `unit_kerja_id` bigint unsigned DEFAULT NULL,
  `kode_iku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iku_kinerja_layanan_kode_iku_unique` (`kode_iku`),
  KEY `iku_kinerja_layanan_sasaran_strategis_id_foreign` (`sasaran_strategis_id`),
  KEY `iku_kinerja_layanan_unit_kerja_id_foreign` (`unit_kerja_id`),
  CONSTRAINT `iku_kinerja_layanan_sasaran_strategis_id_foreign` FOREIGN KEY (`sasaran_strategis_id`) REFERENCES `sasaran_strategis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iku_kinerja_layanan_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerja` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iku_kinerja_layanan`
--

LOCK TABLES `iku_kinerja_layanan` WRITE;
/*!40000 ALTER TABLE `iku_kinerja_layanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `iku_kinerja_layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jenis_kinerja_layanan`
--

DROP TABLE IF EXISTS `jenis_kinerja_layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_kinerja_layanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_layanan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode_rsb_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_kinerja_layanan_kode_layanan_unique` (`kode_layanan`),
  KEY `jenis_kinerja_layanan_periode_rsb_id_foreign` (`periode_rsb_id`),
  CONSTRAINT `jenis_kinerja_layanan_periode_rsb_id_foreign` FOREIGN KEY (`periode_rsb_id`) REFERENCES `periode_rsb` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_kinerja_layanan`
--

LOCK TABLES `jenis_kinerja_layanan` WRITE;
/*!40000 ALTER TABLE `jenis_kinerja_layanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `jenis_kinerja_layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
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
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `kategori_kinerja_layanan`
--

DROP TABLE IF EXISTS `kategori_kinerja_layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori_kinerja_layanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `layanan_id` bigint unsigned NOT NULL,
  `kode_kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_kerja_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_kinerja_layanan_kode_kategori_unique` (`kode_kategori`),
  KEY `kategori_kinerja_layanan_layanan_id_foreign` (`layanan_id`),
  KEY `kategori_kinerja_layanan_unit_kerja_id_foreign` (`unit_kerja_id`),
  CONSTRAINT `kategori_kinerja_layanan_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `jenis_kinerja_layanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kategori_kinerja_layanan_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerja` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_kinerja_layanan`
--

LOCK TABLES `kategori_kinerja_layanan` WRITE;
/*!40000 ALTER TABLE `kategori_kinerja_layanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `kategori_kinerja_layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_03_071404_create_otp_codes_table',1),(5,'2026_08_03_080000_create_unit_kerja_table',1),(6,'2026_08_03_080100_add_unit_kerja_to_users_table',1),(7,'2026_08_03_080150_move_operator_relation_to_unit_kerja',1),(8,'2026_08_03_080200_create_jenis_kinerja_layanan_table',1),(9,'2026_08_03_080300_create_kategori_kinerja_layanan_table',1),(10,'2026_08_03_080400_create_iku_kinerja_layanan_table',1),(11,'2026_08_03_080500_create_tujuan_rsb_table',1),(12,'2026_08_03_080600_create_sasaran_program_table',1),(13,'2026_08_03_080700_create_sasaran_strategis_table',1),(14,'2026_08_03_080800_create_periode_rsb_table',1),(15,'2026_08_04_000000_create_slider_items_table',1),(16,'2026_08_12_000000_add_rsb_parent_relations',1),(17,'2026_08_12_000001_remove_kategori_from_iku_kinerja_layanan',1),(18,'2026_08_12_000002_add_unit_kerja_to_iku_kinerja_layanan',1),(19,'2026_08_12_000003_create_target_kinerja_layanan_table',1),(20,'2026_08_12_000004_create_target_kinerja_iku_rsb_table',1),(21,'2026_08_12_000005_create_realisasi_kinerja_layanan_tables',1),(22,'2026_08_12_000006_add_semester_to_realisasi_dokumen',1),(23,'2026_08_12_000007_create_realisasi_kinerja_iku_rsb_tables',1),(24,'2026_08_12_000008_add_avatar_to_users_table',1),(25,'2026_08_15_000000_add_periode_to_jenis_kinerja_layanan',1),(26,'2026_08_15_000001_add_periode_to_tujuan_rsb',1),(27,'2026_08_24_000000_create_dokumen_renstras_table',1),(28,'2026_11_03_000001_create_rba_master_tables',1),(29,'2026_11_03_000002_create_rba_tahun_anggaran_table',1),(30,'2026_11_03_000003_create_rba_target_realisasi_tables',1),(31,'2026_11_03_000004_create_rba_history_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_codes`
--

DROP TABLE IF EXISTS `otp_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otp_codes_email_code_index` (`email`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_codes`
--

LOCK TABLES `otp_codes` WRITE;
/*!40000 ALTER TABLE `otp_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `otp_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `periode_rsb`
--

DROP TABLE IF EXISTS `periode_rsb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periode_rsb` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_awal` year NOT NULL,
  `tahun_akhir` year NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periode_rsb`
--

LOCK TABLES `periode_rsb` WRITE;
/*!40000 ALTER TABLE `periode_rsb` DISABLE KEYS */;
/*!40000 ALTER TABLE `periode_rsb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_detail`
--

DROP TABLE IF EXISTS `rba_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indikator_id` bigint unsigned NOT NULL,
  `kode_detail` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_detail_kode_detail_unique` (`kode_detail`),
  KEY `rba_detail_indikator_id_foreign` (`indikator_id`),
  CONSTRAINT `rba_detail_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `rba_indikator` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_detail`
--

LOCK TABLES `rba_detail` WRITE;
/*!40000 ALTER TABLE `rba_detail` DISABLE KEYS */;
INSERT INTO `rba_detail` VALUES (1,1,'J01.001.001.01','RM BOPTN','2026-11-02 04:44:08','2026-11-02 04:44:08'),(2,2,'J01.001.002.02','Pendidikan','2026-11-02 04:44:27','2026-11-02 04:44:27'),(3,2,'J01.001.002.01','Bisnis','2026-11-02 04:44:48','2026-11-02 04:44:48');
/*!40000 ALTER TABLE `rba_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_history`
--

DROP TABLE IF EXISTS `rba_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aksi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tabel_referensi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referensi_id` bigint unsigned DEFAULT NULL,
  `data_sebelum` json DEFAULT NULL,
  `data_sesudah` json DEFAULT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rba_history_user_id_foreign` (`user_id`),
  CONSTRAINT `rba_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_history`
--

LOCK TABLES `rba_history` WRITE;
/*!40000 ALTER TABLE `rba_history` DISABLE KEYS */;
INSERT INTO `rba_history` VALUES (1,'INSERT','rba_jenis',1,NULL,'{\"id\": 1, \"uraian\": \"Aspek Keuangan\", \"created_at\": \"2026-11-02 11:37:56\", \"kode_jenis\": \"J01\", \"updated_at\": \"2026-11-02 11:37:56\"}',NULL,10,'2026-11-02 04:37:56','2026-11-02 04:37:56'),(2,'INSERT','rba_jenis',2,NULL,'{\"id\": 2, \"uraian\": \"Aspek Aset dan Persediaan\", \"created_at\": \"2026-11-02 11:38:11\", \"kode_jenis\": \"J02\", \"updated_at\": \"2026-11-02 11:38:11\"}',NULL,10,'2026-11-02 04:38:11','2026-11-02 04:38:11'),(3,'INSERT','rba_kategori',1,NULL,'{\"id\": 1, \"uraian\": \"Pendapatan\", \"jenis_id\": \"1\", \"created_at\": \"2026-11-02 11:38:24\", \"updated_at\": \"2026-11-02 11:38:24\", \"kode_kategori\": \"J01.001\"}',NULL,10,'2026-11-02 04:38:24','2026-11-02 04:38:24'),(4,'INSERT','rba_kategori',2,NULL,'{\"id\": 2, \"uraian\": \"Belanja\", \"jenis_id\": \"1\", \"created_at\": \"2026-11-02 11:38:32\", \"updated_at\": \"2026-11-02 11:38:32\", \"kode_kategori\": \"J01.002\"}',NULL,10,'2026-11-02 04:38:32','2026-11-02 04:38:32'),(5,'INSERT','rba_indikator',1,NULL,'{\"id\": 1, \"uraian\": \"Rupiah Murni\", \"created_at\": \"2026-11-02 11:43:13\", \"updated_at\": \"2026-11-02 11:43:13\", \"kategori_id\": \"1\", \"kode_indikator\": \"J01.001.001\"}',NULL,10,'2026-11-02 04:43:13','2026-11-02 04:43:13'),(6,'INSERT','rba_indikator',2,NULL,'{\"id\": 2, \"uraian\": \"bLU\", \"created_at\": \"2026-11-02 11:43:22\", \"updated_at\": \"2026-11-02 11:43:22\", \"kategori_id\": \"1\", \"kode_indikator\": \"J01.001.002\"}',NULL,10,'2026-11-02 04:43:22','2026-11-02 04:43:22'),(7,'UPDATE','rba_indikator',2,'{\"id\": 2, \"uraian\": \"bLU\", \"created_at\": \"2026-11-02 11:43:22\", \"updated_at\": \"2026-11-02 11:43:22\", \"kategori_id\": 1, \"kode_indikator\": \"J01.001.002\"}','{\"id\": 2, \"uraian\": \"BLU\", \"created_at\": \"2026-11-02 11:43:22\", \"updated_at\": \"2026-11-02 11:43:28\", \"kategori_id\": 1, \"kode_indikator\": \"J01.001.002\"}',NULL,10,'2026-11-02 04:43:28','2026-11-02 04:43:28'),(8,'INSERT','rba_indikator',3,NULL,'{\"id\": 3, \"uraian\": \"Rupiah Murni\", \"created_at\": \"2026-11-02 11:43:45\", \"updated_at\": \"2026-11-02 11:43:45\", \"kategori_id\": \"2\", \"kode_indikator\": \"J01.002.001\"}',NULL,10,'2026-11-02 04:43:45','2026-11-02 04:43:45'),(9,'INSERT','rba_indikator',4,NULL,'{\"id\": 4, \"uraian\": \"BLU\", \"created_at\": \"2026-11-02 11:43:55\", \"updated_at\": \"2026-11-02 11:43:55\", \"kategori_id\": \"2\", \"kode_indikator\": \"J01.002.002\"}',NULL,10,'2026-11-02 04:43:55','2026-11-02 04:43:55'),(10,'INSERT','rba_detail',1,NULL,'{\"id\": 1, \"uraian\": \"RM BOPTN\", \"created_at\": \"2026-11-02 11:44:08\", \"updated_at\": \"2026-11-02 11:44:08\", \"kode_detail\": \"J01.001.001.01\", \"indikator_id\": \"1\"}',NULL,10,'2026-11-02 04:44:08','2026-11-02 04:44:08'),(11,'INSERT','rba_detail',2,NULL,'{\"id\": 2, \"uraian\": \"Pendidikan\", \"created_at\": \"2026-11-02 11:44:27\", \"updated_at\": \"2026-11-02 11:44:27\", \"kode_detail\": \"J01.001.002.02\", \"indikator_id\": \"2\"}',NULL,10,'2026-11-02 04:44:27','2026-11-02 04:44:27'),(12,'INSERT','rba_detail',3,NULL,'{\"id\": 3, \"uraian\": \"Bisnis\", \"created_at\": \"2026-11-02 11:44:48\", \"updated_at\": \"2026-11-02 11:44:48\", \"kode_detail\": \"J01.001.002.01\", \"indikator_id\": \"2\"}',NULL,10,'2026-11-02 04:44:48','2026-11-02 04:44:48'),(13,'INSERT','rba_tahun_anggaran',1,NULL,'{\"id\": 1, \"tahun\": 2026, \"status\": \"OPEN\", \"created_at\": \"2026-11-02 11:45:20\", \"is_current\": 1, \"updated_at\": \"2026-11-02 11:45:20\"}',NULL,10,'2026-11-02 04:45:20','2026-11-02 04:45:20'),(14,'INSERT','rba_tahun_anggaran',2,NULL,'{\"id\": 2, \"tahun\": 2027, \"status\": \"OPEN\", \"created_at\": \"2026-11-02 11:45:51\", \"is_current\": 0, \"updated_at\": \"2026-11-02 11:45:51\"}',NULL,10,'2026-11-02 04:45:51','2026-11-02 04:45:51'),(15,'INSERT','rba_target',1,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(16,'INSERT','rba_realisasi',1,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(17,'INSERT','rba_target',2,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(18,'INSERT','rba_realisasi',2,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(19,'INSERT','rba_target',3,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(20,'INSERT','rba_realisasi',3,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(21,'INSERT','rba_target',4,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(22,'INSERT','rba_realisasi',4,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(23,'INSERT','rba_target',5,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(24,'INSERT','rba_realisasi',5,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(25,'INSERT','rba_target',6,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(26,'INSERT','rba_realisasi',6,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(27,'INSERT','rba_target',7,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(28,'INSERT','rba_realisasi',7,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(29,'INSERT','rba_target',8,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(30,'INSERT','rba_realisasi',8,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(31,'INSERT','rba_target',9,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(32,'INSERT','rba_realisasi',9,NULL,'{\"dummy\": true}','Data dummy dibuat via skrip sekali pakai',10,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(33,'INSERT','rba_target',10,NULL,'{\"id\": 10, \"nilai\": \"5000000000.00\", \"detail_id\": 1, \"created_at\": \"2026-11-02T12:11:30.000000Z\", \"updated_at\": \"2026-11-02T12:11:30.000000Z\", \"tahun_anggaran_id\": 1}','Isi target tahun berjalan 2026 menyalin 2025 (tetap bisa edit)',10,'2026-11-02 05:11:30','2026-11-02 05:11:30'),(34,'INSERT','rba_target',11,NULL,'{\"id\": 11, \"nilai\": \"3000000000.00\", \"detail_id\": 2, \"created_at\": \"2026-11-02T12:11:30.000000Z\", \"updated_at\": \"2026-11-02T12:11:30.000000Z\", \"tahun_anggaran_id\": 1}','Isi target tahun berjalan 2026 menyalin 2025 (tetap bisa edit)',10,'2026-11-02 05:11:30','2026-11-02 05:11:30'),(35,'INSERT','rba_target',12,NULL,'{\"id\": 12, \"nilai\": \"2000000000.00\", \"detail_id\": 3, \"created_at\": \"2026-11-02T12:11:30.000000Z\", \"updated_at\": \"2026-11-02T12:11:30.000000Z\", \"tahun_anggaran_id\": 1}','Isi target tahun berjalan 2026 menyalin 2025 (tetap bisa edit)',10,'2026-11-02 05:11:30','2026-11-02 05:11:30'),(36,'UPDATE','rba_target',10,'{\"id\": 10, \"nilai\": \"5000000000.00\", \"detail_id\": 1, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}','{\"id\": 10, \"nilai\": \"5000000000.00\", \"detail_id\": 1, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}',NULL,10,'2026-11-02 05:23:59','2026-11-02 05:23:59'),(37,'UPDATE','rba_target',12,'{\"id\": 12, \"nilai\": \"2000000000.00\", \"detail_id\": 3, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}','{\"id\": 12, \"nilai\": \"2000000000.00\", \"detail_id\": 3, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}',NULL,10,'2026-11-02 05:23:59','2026-11-02 05:23:59'),(38,'UPDATE','rba_target',11,'{\"id\": 11, \"nilai\": \"3000000000.00\", \"detail_id\": 2, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}','{\"id\": 11, \"nilai\": \"3000000000.00\", \"detail_id\": 2, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}',NULL,10,'2026-11-02 05:23:59','2026-11-02 05:23:59'),(39,'UPDATE','rba_target',10,'{\"id\": 10, \"nilai\": \"5000000000.00\", \"detail_id\": 1, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}','{\"id\": 10, \"nilai\": \"5000000000.00\", \"detail_id\": 1, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}',NULL,10,'2026-11-02 05:24:08','2026-11-02 05:24:08'),(40,'UPDATE','rba_target',12,'{\"id\": 12, \"nilai\": \"2000000000.00\", \"detail_id\": 3, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}','{\"id\": 12, \"nilai\": \"2000000000.00\", \"detail_id\": 3, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}',NULL,10,'2026-11-02 05:24:08','2026-11-02 05:24:08'),(41,'UPDATE','rba_target',11,'{\"id\": 11, \"nilai\": \"3000000000.00\", \"detail_id\": 2, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}','{\"id\": 11, \"nilai\": \"3000000000.00\", \"detail_id\": 2, \"created_at\": \"2026-11-02 12:11:30\", \"updated_at\": \"2026-11-02 12:11:30\", \"tahun_anggaran_id\": 1}',NULL,10,'2026-11-02 05:24:08','2026-11-02 05:24:08'),(42,'INSERT','rba_target',13,NULL,'{\"id\": 13, \"nilai\": \"900000000.00\", \"detail_id\": 1, \"created_at\": \"2026-11-02 12:24:08\", \"updated_at\": \"2026-11-02 12:24:08\", \"tahun_anggaran_id\": 2}',NULL,10,'2026-11-02 05:24:08','2026-11-02 05:24:08');
/*!40000 ALTER TABLE `rba_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_indikator`
--

DROP TABLE IF EXISTS `rba_indikator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_indikator` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kategori_id` bigint unsigned NOT NULL,
  `kode_indikator` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_indikator_kode_indikator_unique` (`kode_indikator`),
  KEY `rba_indikator_kategori_id_foreign` (`kategori_id`),
  CONSTRAINT `rba_indikator_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `rba_kategori` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_indikator`
--

LOCK TABLES `rba_indikator` WRITE;
/*!40000 ALTER TABLE `rba_indikator` DISABLE KEYS */;
INSERT INTO `rba_indikator` VALUES (1,1,'J01.001.001','Rupiah Murni','2026-11-02 04:43:13','2026-11-02 04:43:13'),(2,1,'J01.001.002','BLU','2026-11-02 04:43:22','2026-11-02 04:43:28'),(3,2,'J01.002.001','Rupiah Murni','2026-11-02 04:43:45','2026-11-02 04:43:45'),(4,2,'J01.002.002','BLU','2026-11-02 04:43:55','2026-11-02 04:43:55');
/*!40000 ALTER TABLE `rba_indikator` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_jenis`
--

DROP TABLE IF EXISTS `rba_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_jenis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_jenis_kode_jenis_unique` (`kode_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_jenis`
--

LOCK TABLES `rba_jenis` WRITE;
/*!40000 ALTER TABLE `rba_jenis` DISABLE KEYS */;
INSERT INTO `rba_jenis` VALUES (1,'J01','Aspek Keuangan','2026-11-02 04:37:56','2026-11-02 04:37:56'),(2,'J02','Aspek Aset dan Persediaan','2026-11-02 04:38:11','2026-11-02 04:38:11');
/*!40000 ALTER TABLE `rba_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_kategori`
--

DROP TABLE IF EXISTS `rba_kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_kategori` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_id` bigint unsigned NOT NULL,
  `kode_kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_kategori_kode_kategori_unique` (`kode_kategori`),
  KEY `rba_kategori_jenis_id_foreign` (`jenis_id`),
  CONSTRAINT `rba_kategori_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `rba_jenis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_kategori`
--

LOCK TABLES `rba_kategori` WRITE;
/*!40000 ALTER TABLE `rba_kategori` DISABLE KEYS */;
INSERT INTO `rba_kategori` VALUES (1,1,'J01.001','Pendapatan','2026-11-02 04:38:24','2026-11-02 04:38:24'),(2,1,'J01.002','Belanja','2026-11-02 04:38:32','2026-11-02 04:38:32');
/*!40000 ALTER TABLE `rba_kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_realisasi`
--

DROP TABLE IF EXISTS `rba_realisasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_realisasi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `target_id` bigint unsigned NOT NULL,
  `semester_1` decimal(20,2) DEFAULT NULL,
  `semester_2` decimal(20,2) DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_realisasi_target_unique` (`target_id`),
  CONSTRAINT `rba_realisasi_target_id_foreign` FOREIGN KEY (`target_id`) REFERENCES `rba_target` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_realisasi`
--

LOCK TABLES `rba_realisasi` WRITE;
/*!40000 ALTER TABLE `rba_realisasi` DISABLE KEYS */;
INSERT INTO `rba_realisasi` VALUES (1,1,2250000000.00,2000000000.00,'Data dummy tahun 2023','2026-11-02 04:53:08','2026-11-02 04:53:08'),(2,2,1350000000.00,1200000000.00,'Data dummy tahun 2023','2026-11-02 04:53:08','2026-11-02 04:53:08'),(3,3,900000000.00,800000000.00,'Data dummy tahun 2023','2026-11-02 04:53:08','2026-11-02 04:53:08'),(4,4,2250000000.00,2000000000.00,'Data dummy tahun 2024','2026-11-02 04:53:08','2026-11-02 04:53:08'),(5,5,1350000000.00,1200000000.00,'Data dummy tahun 2024','2026-11-02 04:53:08','2026-11-02 04:53:08'),(6,6,900000000.00,800000000.00,'Data dummy tahun 2024','2026-11-02 04:53:08','2026-11-02 04:53:08'),(7,7,2250000000.00,2000000000.00,'Data dummy tahun 2025','2026-11-02 04:53:08','2026-11-02 04:53:08'),(8,8,1350000000.00,1200000000.00,'Data dummy tahun 2025','2026-11-02 04:53:08','2026-11-02 04:53:08'),(9,9,900000000.00,800000000.00,'Data dummy tahun 2025','2026-11-02 04:53:08','2026-11-02 04:53:08');
/*!40000 ALTER TABLE `rba_realisasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_tahun_anggaran`
--

DROP TABLE IF EXISTS `rba_tahun_anggaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_tahun_anggaran` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun` smallint unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_tahun_anggaran_tahun_unique` (`tahun`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_tahun_anggaran`
--

LOCK TABLES `rba_tahun_anggaran` WRITE;
/*!40000 ALTER TABLE `rba_tahun_anggaran` DISABLE KEYS */;
INSERT INTO `rba_tahun_anggaran` VALUES (1,2026,'OPEN',1,'2026-11-02 04:45:20','2026-11-02 04:45:20'),(2,2027,'OPEN',0,'2026-11-02 04:45:51','2026-11-02 04:45:51'),(3,2023,'OPEN',0,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(4,2024,'OPEN',0,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(5,2025,'OPEN',0,'2026-11-02 04:53:08','2026-11-02 04:53:08');
/*!40000 ALTER TABLE `rba_tahun_anggaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rba_target`
--

DROP TABLE IF EXISTS `rba_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rba_target` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `detail_id` bigint unsigned NOT NULL,
  `tahun_anggaran_id` bigint unsigned NOT NULL,
  `nilai` decimal(20,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rba_target_detail_tahun_unique` (`detail_id`,`tahun_anggaran_id`),
  KEY `rba_target_tahun_anggaran_id_foreign` (`tahun_anggaran_id`),
  CONSTRAINT `rba_target_detail_id_foreign` FOREIGN KEY (`detail_id`) REFERENCES `rba_detail` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rba_target_tahun_anggaran_id_foreign` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `rba_tahun_anggaran` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rba_target`
--

LOCK TABLES `rba_target` WRITE;
/*!40000 ALTER TABLE `rba_target` DISABLE KEYS */;
INSERT INTO `rba_target` VALUES (1,1,3,5000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(2,2,3,3000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(3,3,3,2000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(4,1,4,5000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(5,2,4,3000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(6,3,4,2000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(7,1,5,5000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(8,2,5,3000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(9,3,5,2000000000.00,'2026-11-02 04:53:08','2026-11-02 04:53:08'),(10,1,1,5000000000.00,'2026-11-02 05:11:30','2026-11-02 05:11:30'),(11,2,1,3000000000.00,'2026-11-02 05:11:30','2026-11-02 05:11:30'),(12,3,1,2000000000.00,'2026-11-02 05:11:30','2026-11-02 05:11:30'),(13,1,2,900000000.00,'2026-11-02 05:24:08','2026-11-02 05:24:08');
/*!40000 ALTER TABLE `rba_target` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisasi_kinerja_iku_rsb`
--

DROP TABLE IF EXISTS `realisasi_kinerja_iku_rsb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realisasi_kinerja_iku_rsb` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `target_kinerja_iku_rsb_id` bigint unsigned NOT NULL,
  `realisasi_tahunan` json DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realisasi_iku_target_unique` (`target_kinerja_iku_rsb_id`),
  CONSTRAINT `realisasi_kinerja_iku_rsb_target_kinerja_iku_rsb_id_foreign` FOREIGN KEY (`target_kinerja_iku_rsb_id`) REFERENCES `target_kinerja_iku_rsb` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisasi_kinerja_iku_rsb`
--

LOCK TABLES `realisasi_kinerja_iku_rsb` WRITE;
/*!40000 ALTER TABLE `realisasi_kinerja_iku_rsb` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisasi_kinerja_iku_rsb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisasi_kinerja_iku_rsb_dokumen`
--

DROP TABLE IF EXISTS `realisasi_kinerja_iku_rsb_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realisasi_kinerja_iku_rsb_dokumen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `realisasi_id` bigint unsigned NOT NULL,
  `tahun` smallint unsigned NOT NULL,
  `semester` tinyint unsigned NOT NULL,
  `nama_asli` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ukuran` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `realisasi_kinerja_iku_rsb_dokumen_realisasi_id_foreign` (`realisasi_id`),
  KEY `realisasi_iku_dokumen_tahun_semester_index` (`tahun`,`semester`),
  CONSTRAINT `realisasi_kinerja_iku_rsb_dokumen_realisasi_id_foreign` FOREIGN KEY (`realisasi_id`) REFERENCES `realisasi_kinerja_iku_rsb` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisasi_kinerja_iku_rsb_dokumen`
--

LOCK TABLES `realisasi_kinerja_iku_rsb_dokumen` WRITE;
/*!40000 ALTER TABLE `realisasi_kinerja_iku_rsb_dokumen` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisasi_kinerja_iku_rsb_dokumen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisasi_kinerja_layanan`
--

DROP TABLE IF EXISTS `realisasi_kinerja_layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realisasi_kinerja_layanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `target_kinerja_layanan_id` bigint unsigned NOT NULL,
  `realisasi_tahunan` json DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realisasi_layanan_target_unique` (`target_kinerja_layanan_id`),
  CONSTRAINT `realisasi_kinerja_layanan_target_kinerja_layanan_id_foreign` FOREIGN KEY (`target_kinerja_layanan_id`) REFERENCES `target_kinerja_layanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisasi_kinerja_layanan`
--

LOCK TABLES `realisasi_kinerja_layanan` WRITE;
/*!40000 ALTER TABLE `realisasi_kinerja_layanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisasi_kinerja_layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisasi_kinerja_layanan_dokumen`
--

DROP TABLE IF EXISTS `realisasi_kinerja_layanan_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realisasi_kinerja_layanan_dokumen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `realisasi_id` bigint unsigned NOT NULL,
  `tahun` smallint unsigned DEFAULT NULL,
  `semester` tinyint unsigned DEFAULT NULL,
  `nama_asli` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ukuran` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `realisasi_kinerja_layanan_dokumen_realisasi_id_foreign` (`realisasi_id`),
  KEY `realisasi_dokumen_tahun_semester_index` (`tahun`,`semester`),
  CONSTRAINT `realisasi_kinerja_layanan_dokumen_realisasi_id_foreign` FOREIGN KEY (`realisasi_id`) REFERENCES `realisasi_kinerja_layanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisasi_kinerja_layanan_dokumen`
--

LOCK TABLES `realisasi_kinerja_layanan_dokumen` WRITE;
/*!40000 ALTER TABLE `realisasi_kinerja_layanan_dokumen` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisasi_kinerja_layanan_dokumen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sasaran_program`
--

DROP TABLE IF EXISTS `sasaran_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sasaran_program` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tujuan_id` bigint unsigned DEFAULT NULL,
  `kode_sasaran_program` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sasaran_program_kode_sasaran_program_unique` (`kode_sasaran_program`),
  KEY `sasaran_program_tujuan_id_foreign` (`tujuan_id`),
  CONSTRAINT `sasaran_program_tujuan_id_foreign` FOREIGN KEY (`tujuan_id`) REFERENCES `tujuan_rsb` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sasaran_program`
--

LOCK TABLES `sasaran_program` WRITE;
/*!40000 ALTER TABLE `sasaran_program` DISABLE KEYS */;
/*!40000 ALTER TABLE `sasaran_program` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sasaran_strategis`
--

DROP TABLE IF EXISTS `sasaran_strategis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sasaran_strategis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sasaran_program_id` bigint unsigned DEFAULT NULL,
  `kode_sasaran_strategis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sasaran_strategis_kode_sasaran_strategis_unique` (`kode_sasaran_strategis`),
  KEY `sasaran_strategis_sasaran_program_id_foreign` (`sasaran_program_id`),
  CONSTRAINT `sasaran_strategis_sasaran_program_id_foreign` FOREIGN KEY (`sasaran_program_id`) REFERENCES `sasaran_program` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sasaran_strategis`
--

LOCK TABLES `sasaran_strategis` WRITE;
/*!40000 ALTER TABLE `sasaran_strategis` DISABLE KEYS */;
/*!40000 ALTER TABLE `sasaran_strategis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `sessions` VALUES ('136U5J3oUmjufyvDDxsbAZEyYQ4NdW5fwJJ6YHSj',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSEpJY0k0WGxVejdPN3VUM0xuZTdoZGdIVWRkc2R2eHJtVThRQ2VLMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvdHJhbnNha3NpL3JlYWxpc2FzaSI7czo1OiJyb3V0ZSI7czoyMzoicmJhLnRyYW5zYWtzaS5yZWFsaXNhc2kiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793620692),('7nkzjmg0dLZvn3YVaoza5djANrQbuljHL08JzKNp',10,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5 Safari/605.1.15','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVmg0VEZPRmQxOVNUQ0dDbVlpcFVncW9oclJFYmNJNjk4bGhjWTlYWSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjcxOiJodHRwOi8vMTI3LjAuMC4xOjkwODAvcmJhL2xhcG9yYW4vZGF0YT9tb2RlPXRhaHVuYW4mdGFodW5fYW5nZ2FyYW5faWQ9MSI7czo1OiJyb3V0ZSI7czoxNjoicmJhLmxhcG9yYW4uZGF0YSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEwO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIyYzNkYWZlZmNjZGI4ZDk2YjQ4MWQxNjNiMmE0ZGE2OTNjODUwZjdhOWU0YjJiOGFlOTE0Nzc2ODQwMDNjZTFlIjt9',1793622274),('arye3N464nJRjrfPWLrD4iQDNiu8IkSj4QfdF39d',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiV28zOFhUUjAyemwzYnk1RU02RUc3UHBhUG91WG5sYlJadGJOdkEyOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvbWFzdGVyL2RldGFpbCI7czo1OiJyb3V0ZSI7czoyMzoicmJhLm1hc3Rlci5kZXRhaWwuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793619661),('AscEuUSm75x3P5lbHmP2p5TnT0NiaUoc6JHO7NB8',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZHM4TmJXMUt1NGNQUkxqaW9zMTdsYUNDdFBxMkNMQXk3Ym5uNHFvMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njc6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvdHJhbnNha3NpL3RhcmdldC9kYXRhP3RhaHVuX2FuZ2dhcmFuX2lkPTEiO3M6NToicm91dGUiO3M6MjU6InJiYS50cmFuc2Frc2kudGFyZ2V0LmRhdGEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793622132),('hkezfKwkh1ahZxWHSuVbUTOK8tc1M4OQvWcBC6DM',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSmZ5N2t2bUNXTDVSdnZrWTZZUTgwWWFMMUs4TnBOMUxsbGNWZjNFMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvdHJhbnNha3NpL3JlYWxpc2FzaSI7czo1OiJyb3V0ZSI7czoyMzoicmJhLnRyYW5zYWtzaS5yZWFsaXNhc2kiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793621002),('KDQ5LzNqRqTeAcU9Q5W9eg1d8wa5UpzZW97AoBRD',9,'127.0.0.1','Symfony','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM05MNzhKUXVPdURNblEyTk1Idk9PcUhISGo5MzdWTW9pWUtuTUZqVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvbWFzdGVyL2plbmlzIjtzOjU6InJvdXRlIjtzOjIyOiJyYmEubWFzdGVyLmplbmlzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793618025),('oRwhRSCHLXBbqtzfERrY1iGpMKjCGJWdCbP8D6xG',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoieVk4ZUFVbG10ZlFNM0o3TTluYTFmVzg4eHBxeGVQZ1U0NXpBelF4aSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvbWFzdGVyL2plbmlzIjtzOjU6InJvdXRlIjtzOjIyOiJyYmEubWFzdGVyLmplbmlzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjJjM2RhZmVmY2NkYjhkOTZiNDgxZDE2M2IyYTRkYTY5M2M4NTBmN2E5ZTRiMmI4YWU5MTQ3NzY4NDAwM2NlMWUiO30=',1793618853),('pNcBZnWek8VYZHLHrTnMiCnCjGtL8AW3V3kgOiv6',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNTU0RmVlR1hiallwd2lKRmVvUm1WVHg2NU5ReE80WUhsY1JVVlhDYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzA6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvdHJhbnNha3NpL3JlYWxpc2FzaS9kYXRhP3RhaHVuX2FuZ2dhcmFuX2lkPTEiO3M6NToicm91dGUiO3M6Mjg6InJiYS50cmFuc2Frc2kucmVhbGlzYXNpLmRhdGEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793621496),('q3nIKlPzP4NtSvfAY30rHXczk9noaZPPmGFbMj94',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZXJXeDBJY0tnYUhtNGJYSlJiTUlSYjA2QU9KN0tvazJIYzNaNXM4YyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjJjM2RhZmVmY2NkYjhkOTZiNDgxZDE2M2IyYTRkYTY5M2M4NTBmN2E5ZTRiMmI4YWU5MTQ3NzY4NDAwM2NlMWUiO30=',1793618746),('u35bHtfS5Gs4HlccVfpruBWLlrNl3P5fL3v9KvrK',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiT1Nsb1g4cXBmUmUzSTY2REJxckdZMjlSWWZWbjJrZFpBbmhYSDY5WiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvbGFwb3JhbiI7czo1OiJyb3V0ZSI7czoxNzoicmJhLmxhcG9yYW4uaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793621611),('WVDDa1c0W6jF2QCd1NYdToEhHFmJ5St5JLZJCtfY',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoia0VKa1dST3EyNXd6Zzd5TUF1UGVJaWpwQkk2cEdqdFRLSFJiZ2g1TiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njc6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvdHJhbnNha3NpL3RhcmdldC9kYXRhP3RhaHVuX2FuZ2dhcmFuX2lkPTMiO3M6NToicm91dGUiO3M6MjU6InJiYS50cmFuc2Frc2kudGFyZ2V0LmRhdGEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMDtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMmMzZGFmZWZjY2RiOGQ5NmI0ODFkMTYzYjJhNGRhNjkzYzg1MGY3YTllNGIyYjhhZTkxNDc3Njg0MDAzY2UxZSI7fQ==',1793620398),('zuzNSIbGy6m2Uu4HAZYU0yQQ99sBdHNqFqX85d7v',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiMnRPYjU0bjludE9NTklyMEZPUkN5V1l3U3c3S0M1d0pDMWY1UTAyWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6OTA4MC9yYmEvbGFwb3Jhbi9oaXN0b3J5IjtzOjU6InJvdXRlIjtzOjE5OiJyYmEubGFwb3Jhbi5oaXN0b3J5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjJjM2RhZmVmY2NkYjhkOTZiNDgxZDE2M2IyYTRkYTY5M2M4NTBmN2E5ZTRiMmI4YWU5MTQ3NzY4NDAwM2NlMWUiO30=',1793619022);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slider_items`
--

DROP TABLE IF EXISTS `slider_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slider_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slider_items`
--

LOCK TABLES `slider_items` WRITE;
/*!40000 ALTER TABLE `slider_items` DISABLE KEYS */;
INSERT INTO `slider_items` VALUES (1,'Kampus Modern','https://lh3.googleusercontent.com/aida-public/AB6AXuDouqth3arL9x8jKRYKWCFfNQWyU8oxgP2AyizRbYJBcjRHYn61EdiITof0QLbjs-7T6BSnu6NGRaQpz7ax-_-PE9VGEGiC1F5rWWHCrHp5D8Mq3N_fybOYL6ue7C0c44_tGl_tVFlmWJyA3oqzHmDQgS3gQLEF7jTLxesbNYRByz0IA5LZMbFacnrIf3mfi7IAH4mh5VbStc6ErlgpD65brxeubfw0Gow_PXT4wdo6T93LSn7Bkkyl',1,1,'2026-11-02 04:13:44','2026-11-02 04:13:44'),(2,'Auditorium','https://lh3.googleusercontent.com/aida-public/AB6AXuDXp4nNKAAB4TeykNRJH1ZxcfXxk_67PwVVeyneyL6WTH6VlK7Og66THdEKGlHSRFqmoDNe0XFtRJeRFRZULjW8USTNNOvPNlaw5yS63aVUKHxrPvxGlzpAQOdRp1rxJEYpLs4cIWvPC6xlPDknraNAgjTt6bR7kb5-WqDji0PnDQE4zb5k0oSg2T47DFVqroU_GGfXWiEQoEmmyNbaN-YhqdFm7x0stPVMadjzraoNCSZ9ezca6SpC',2,1,'2026-11-02 04:13:44','2026-11-02 04:13:44'),(3,'Mahasiswa','https://lh3.googleusercontent.com/aida-public/AB6AXuBLiO3lMKLJa8fXS-4clkOujT7JK7j7PiNxFSy7ncZzwpGJ4XQcIQMkcNVy35NwuSjqDZzbvr8F7Fm0BwBzDD4KDTjbpAXQjv9L8Awb8sogz5nFHsQDr6gcLOdUNcnIiA-NLM9amdq3leg3o47AH-UUue8je1H8nnSxvHvpgj3_V6jq2UHBOzubumBetQKjRfb3eOT3jufy_YIYPFo6EQ6dsqKsvFyZ8ShmCSj_mRNbkW6eXB9hxd94',3,1,'2026-11-02 04:13:44','2026-11-02 04:13:44');
/*!40000 ALTER TABLE `slider_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_kinerja_iku_rsb`
--

DROP TABLE IF EXISTS `target_kinerja_iku_rsb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_kinerja_iku_rsb` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode_rsb_id` bigint unsigned NOT NULL,
  `iku_kinerja_layanan_id` bigint unsigned NOT NULL,
  `unit_kerja_id` bigint unsigned NOT NULL,
  `target_tahunan` json DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `target_iku_periode_iku_unit_unique` (`periode_rsb_id`,`iku_kinerja_layanan_id`,`unit_kerja_id`),
  KEY `target_kinerja_iku_rsb_iku_kinerja_layanan_id_foreign` (`iku_kinerja_layanan_id`),
  KEY `target_kinerja_iku_rsb_unit_kerja_id_foreign` (`unit_kerja_id`),
  CONSTRAINT `target_kinerja_iku_rsb_iku_kinerja_layanan_id_foreign` FOREIGN KEY (`iku_kinerja_layanan_id`) REFERENCES `iku_kinerja_layanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `target_kinerja_iku_rsb_periode_rsb_id_foreign` FOREIGN KEY (`periode_rsb_id`) REFERENCES `periode_rsb` (`id`) ON DELETE CASCADE,
  CONSTRAINT `target_kinerja_iku_rsb_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerja` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_kinerja_iku_rsb`
--

LOCK TABLES `target_kinerja_iku_rsb` WRITE;
/*!40000 ALTER TABLE `target_kinerja_iku_rsb` DISABLE KEYS */;
/*!40000 ALTER TABLE `target_kinerja_iku_rsb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_kinerja_layanan`
--

DROP TABLE IF EXISTS `target_kinerja_layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_kinerja_layanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode_rsb_id` bigint unsigned NOT NULL,
  `kategori_id` bigint unsigned NOT NULL,
  `unit_kerja_id` bigint unsigned NOT NULL,
  `target_tahunan` json DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `target_layanan_periode_kategori_unit_unique` (`periode_rsb_id`,`kategori_id`,`unit_kerja_id`),
  KEY `target_kinerja_layanan_kategori_id_foreign` (`kategori_id`),
  KEY `target_kinerja_layanan_unit_kerja_id_foreign` (`unit_kerja_id`),
  CONSTRAINT `target_kinerja_layanan_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_kinerja_layanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `target_kinerja_layanan_periode_rsb_id_foreign` FOREIGN KEY (`periode_rsb_id`) REFERENCES `periode_rsb` (`id`) ON DELETE CASCADE,
  CONSTRAINT `target_kinerja_layanan_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerja` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_kinerja_layanan`
--

LOCK TABLES `target_kinerja_layanan` WRITE;
/*!40000 ALTER TABLE `target_kinerja_layanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `target_kinerja_layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tujuan_rsb`
--

DROP TABLE IF EXISTS `tujuan_rsb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tujuan_rsb` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_tujuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode_rsb_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tujuan_rsb_kode_tujuan_unique` (`kode_tujuan`),
  KEY `tujuan_rsb_periode_rsb_id_foreign` (`periode_rsb_id`),
  CONSTRAINT `tujuan_rsb_periode_rsb_id_foreign` FOREIGN KEY (`periode_rsb_id`) REFERENCES `periode_rsb` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tujuan_rsb`
--

LOCK TABLES `tujuan_rsb` WRITE;
/*!40000 ALTER TABLE `tujuan_rsb` DISABLE KEYS */;
/*!40000 ALTER TABLE `tujuan_rsb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_kerja`
--

DROP TABLE IF EXISTS `unit_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_kerja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_kerja_kode_unit_unique` (`kode_unit`),
  KEY `unit_kerja_operator_id_foreign` (`operator_id`),
  CONSTRAINT `unit_kerja_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_kerja`
--

LOCK TABLES `unit_kerja` WRITE;
/*!40000 ALTER TABLE `unit_kerja` DISABLE KEYS */;
/*!40000 ALTER TABLE `unit_kerja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','operator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operator',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (9,'Johnson Cummerata','marques18@example.org','admin',NULL,'2026-11-02 04:13:45','$2y$12$mV7qz/buR4Q0Szo/KWS46.CiKoQA//puVO9jE3hiOw3mr.m5Kn0q6','0cnHZT6zuV','2026-11-02 04:13:45','2026-11-02 04:13:45'),(10,'Super Admin','admin@gmail.com','admin',NULL,NULL,'$2y$12$DucA4J.D5SJUf9tbgUiKaOYqEJZIJC.pmvxwz/S.WcOjj/XVzcBjK',NULL,'2026-11-02 04:25:38','2026-11-02 04:25:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'db_rsb'
--

--
-- Dumping routines for database 'db_rsb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-03  0:01:35
