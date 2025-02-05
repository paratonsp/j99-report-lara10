# ************************************************************
# Sequel Ace SQL dump
# Version 20062
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: localhost (MySQL 5.5.5-10.4.21-MariaDB)
# Database: juragansem_new
# Generation Time: 2023-12-09 08:08:53 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table failed_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES
	(1,'2014_10_12_000000_create_users_table',1),
	(2,'2014_10_12_100000_create_password_reset_tokens_table',1),
	(3,'2019_08_19_000000_create_failed_jobs_table',1),
	(4,'2019_12_14_000001_create_personal_access_tokens_table',1);

/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table password_reset_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table personal_access_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `personal_access_tokens`;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_uuid` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `uuid`, `role_uuid`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`)
VALUES
	(1,'36fd53cb-e9e8-4152-ba19-8cb8b766f307','2f92bb7f-e5f7-4477-a901-c3a79baa088b','super pesoros','super@gmail.com',NULL,'$2y$12$mC3juzDyyBJWdF7GRCcu5u.eQx5k33wDAA4SsiCpTuyWvNJSai3/y',NULL,'2023-11-26 17:38:05','2023-11-26 17:38:05'),
	(2,'f1a7124a-c556-4ad8-a80b-04346146cec8','74610f99-13ba-43ec-91f2-e6d986bc9d65','Jajang Sadel Kulit','jajang@gmail.com',NULL,'$2y$12$aRWbHyMUVoUlR3GGalF0s.CoJYZWx6ql1jzVVqKj/oF7YwsOfGBU2',NULL,'2023-11-26 17:38:58','2023-11-26 17:38:58'),
	(3,'b7a7ea9f-582b-45ac-8c4b-2f7edc6d616a','60b41abd-988b-43c3-a605-a6f6428ed792','Herri Lampu Tidur','herri@gmail.com',NULL,'$2y$12$dQ3dxsDhAJVbyjqaK4NFm.SIhzfmoeT1KiHAEle788GrYjpvenOaO',NULL,'2023-12-02 20:56:41','2023-12-02 20:56:41'),
	(4,'50f9043e-9f16-44b0-9a3c-8b682bf960ee','2cb92276-1675-468a-8429-f79d5841d292','Junadi Senar Pancing','junadi@gmail.com',NULL,'$2y$12$XWpImmu3VYwukvoVnRVRRuR/kTrighrc53.jJ8VhjEHFL4v4AYuYe',NULL,'2023-12-02 21:00:28','2023-12-02 21:00:28'),
	(5,'90557b52-ba98-4c6c-a01e-713ea64a1982','9ba72525-d90d-439c-9203-59b0c1c55024','Rosa Gepukan','rosa@gmail.com',NULL,'$2y$12$I1mZX0rdF5LPeoBO/ubq0eolqyN5.u1suTItzd9wAxSjPtCbJBd6S',NULL,'2023-12-03 23:30:11','2023-12-03 23:30:11'),
	(6,'dca95e40-2e7b-450c-a55b-2ebeb474c39f','60b41abd-988b-43c3-a605-a6f6428ed792','Dion Brembo','dion@gmail.com',NULL,'$2y$12$AHEmdySa6CFDUNsxjQzFEexLBEHT.Jv3WImzH4VPn3EXu05RTiU1K',NULL,'2023-12-03 23:34:25','2023-12-03 23:34:25');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_access_name
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_access_name`;

CREATE TABLE `v2_access_name` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_access_name` WRITE;
/*!40000 ALTER TABLE `v2_access_name` DISABLE KEYS */;

INSERT INTO `v2_access_name` (`id`, `name`)
VALUES
	(1,'index'),
	(2,'show'),
	(3,'add'),
	(4,'edit'),
	(5,'delete');

/*!40000 ALTER TABLE `v2_access_name` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_address
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_address`;

CREATE TABLE `v2_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `detail` longtext DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_address` WRITE;
/*!40000 ALTER TABLE `v2_address` DISABLE KEYS */;

INSERT INTO `v2_address` (`id`, `title`, `detail`, `phone`, `status`, `order`)
VALUES
	(1,'Head Office Malang','Jl.Malang Raya 10/99 12989','081288899838',1,1);

/*!40000 ALTER TABLE `v2_address` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_bus
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_bus`;

CREATE TABLE `v2_bus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` longtext DEFAULT NULL,
  `class_uuid` longtext DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `registration_number` varchar(13) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_bus` WRITE;
/*!40000 ALTER TABLE `v2_bus` DISABLE KEYS */;

INSERT INTO `v2_bus` (`id`, `uuid`, `class_uuid`, `name`, `registration_number`, `brand`, `model`, `status`, `created_at`, `updated_at`)
VALUES
	(12,'657b8b74-b5b2-4cca-88b0-b85e8fd933ad','389f1479-b598-4720-bbc0-da2874128033','Pikachu','N 1929 KPL','Scania','HKL-9989',1,'2023-12-08 03:00:39','2023-12-08 03:00:39'),
	(13,'4eb14f96-5b54-4ac2-8ae8-9f1a620af047','879c722c-2465-4eb7-9e12-b603fe772801','Gundala','N 1789 POJ','Marcedes Benz','E 200 Advantage',1,'2023-12-08 03:13:15','2023-12-08 03:13:15'),
	(14,'75f06c85-57a0-48b9-8b89-f9768426df1c','cc940a34-885d-4e6f-82ad-7e5fb4bce273','Fireflies','N 9892 QOL','Hino','Big Dutro',1,'2023-12-09 14:51:05','2023-12-09 14:51:05');

/*!40000 ALTER TABLE `v2_bus` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_class
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_class`;

CREATE TABLE `v2_class` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` longtext DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `seat` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_class` WRITE;
/*!40000 ALTER TABLE `v2_class` DISABLE KEYS */;

INSERT INTO `v2_class` (`id`, `uuid`, `name`, `seat`)
VALUES
	(5,'cc940a34-885d-4e6f-82ad-7e5fb4bce273','Standard',20),
	(6,'389f1479-b598-4720-bbc0-da2874128033','Premium',16),
	(7,'879c722c-2465-4eb7-9e12-b603fe772801','Executive',9);

/*!40000 ALTER TABLE `v2_class` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_class_facilities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_class_facilities`;

CREATE TABLE `v2_class_facilities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` longtext DEFAULT NULL,
  `facilities_id` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_class_facilities` WRITE;
/*!40000 ALTER TABLE `v2_class_facilities` DISABLE KEYS */;

INSERT INTO `v2_class_facilities` (`id`, `class_id`, `facilities_id`)
VALUES
	(1,'1','6'),
	(2,'1','9'),
	(3,'1','4'),
	(4,'1','5'),
	(5,'1','6'),
	(6,'1','9'),
	(7,'1','1'),
	(8,'1','3'),
	(9,'1','4'),
	(10,'1','5'),
	(11,'1','6'),
	(12,'1','7'),
	(13,'1','8'),
	(14,'1','9');

/*!40000 ALTER TABLE `v2_class_facilities` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_facilities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_facilities`;

CREATE TABLE `v2_facilities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` longtext DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_facilities` WRITE;
/*!40000 ALTER TABLE `v2_facilities` DISABLE KEYS */;

INSERT INTO `v2_facilities` (`id`, `uuid`, `name`)
VALUES
	(1,'2769f9f9-66f0-4da1-8d5d-2f3b13346e0d','AC'),
	(2,'f1910cb2-fa7e-4baa-8b8b-1854615dcb73','Televisi'),
	(3,'68f448a3-3772-4f73-a50b-31983b75131d','Karaoke'),
	(4,'14278662-9f54-4c39-bc49-ce0b7bbb7953','Head Rest'),
	(5,'221b1ac2-ca37-4ceb-be23-46b3e17c5524','Leg Rest'),
	(6,'9bd1a85e-474c-47a9-ad37-84851d3632be','Toilet'),
	(7,'a6612d7c-2c73-4a74-b388-ff3c5a7202c2','Smoking Room'),
	(8,'79473725-65d1-46c2-a9da-18de4d933551','Recleaning Seat'),
	(9,'ea39f9e2-b28e-4380-832c-e98709e95a8f','Snack'),
	(11,'04670a9b-fbf2-4ad3-9db4-8ae19c9bc2c3','Dispenser');

/*!40000 ALTER TABLE `v2_facilities` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_menu`;

CREATE TABLE `v2_menu` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `url` longtext DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `parent_id` int(6) DEFAULT NULL,
  `icon` varchar(100) NOT NULL DEFAULT '<i class="far fa-circle nav-icon"></i>',
  `order` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_menu` WRITE;
/*!40000 ALTER TABLE `v2_menu` DISABLE KEYS */;

INSERT INTO `v2_menu` (`id`, `title`, `slug`, `url`, `module`, `parent_id`, `icon`, `order`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'Dashboard','dashboard','dashboard','dashboard',NULL,'<i class=\"nav-icon fas fa-tachometer-alt\"></i>',1,1,'2023-12-02 22:30:44','2023-12-02 13:15:09'),
	(2,'Managemen Akun','usermanagement','usermanagement','usermanagement',NULL,'<i class=\"nav-icon fas fa-users\"></i>',23,1,'2023-12-07 23:56:43','2023-12-02 13:17:16'),
	(3,'Akun','account','usermanagement/account','usermanagement',2,'<i class=\"far fa-circle nav-icon\"></i>',1,1,'2023-12-04 11:37:48','2023-12-02 13:18:21'),
	(4,'Role','role','usermanagement/role','usermanagement',2,'<i class=\"far fa-circle nav-icon\"></i>',2,1,'2023-12-04 11:37:51','2023-12-02 21:38:23'),
	(5,'Menu','menu','usermanagement/menu','usermanagement',2,'<i class=\"far fa-circle nav-icon\"></i>',3,1,'2023-12-03 01:50:54','2023-12-03 01:50:54'),
	(13,'Cms','cms','cms','cms',NULL,'<i class=\"far fa-circle nav-icon\"></i>',22,1,'2023-12-07 23:56:33','2023-12-04 16:19:17'),
	(14,'Address','address','cms/address','cms',13,'<i class=\"far fa-circle nav-icon\"></i>',1,1,'2023-12-04 16:46:03','2023-12-04 16:22:44'),
	(15,'Master Data','master-data','masterdata','masterdata',NULL,'<i class=\"fas fa-book\"></i>',2,1,'2023-12-07 23:58:27','2023-12-07 23:55:04'),
	(16,'Bus','bus','masterdata/bus','masterdata',15,'<i class=\"far fa-circle nav-icon\"></i>',1,1,'2023-12-07 23:55:34','2023-12-07 23:55:34'),
	(18,'Fasilitas','facilities','masterdata/facilities','masterdata',15,'<i class=\"far fa-circle nav-icon\"></i>',3,1,'2023-12-09 15:06:56','2023-12-08 03:30:08'),
	(19,'Kelas','class','masterdata/class','masterdata',15,'<i class=\"far fa-circle nav-icon\"></i>',2,1,'2023-12-09 14:40:42','2023-12-09 14:40:42');

/*!40000 ALTER TABLE `v2_menu` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_permission
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_permission`;

CREATE TABLE `v2_permission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) DEFAULT NULL,
  `access` varchar(50) DEFAULT NULL,
  `status` tinyint(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_permission` WRITE;
/*!40000 ALTER TABLE `v2_permission` DISABLE KEYS */;

INSERT INTO `v2_permission` (`id`, `slug`, `access`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'dashboard','show',1,'2023-12-02 22:48:36','2023-12-02 22:48:36'),
	(2,'account','show',1,'2023-12-02 22:52:05','2023-12-02 22:52:05'),
	(3,'account','add',1,'2023-12-02 22:52:35','2023-12-02 22:52:35'),
	(4,'account','edit',1,'2023-12-02 22:53:22','2023-12-02 22:53:22'),
	(5,'account','delete',1,'2023-12-02 22:53:32','2023-12-02 22:53:32'),
	(6,'dashboard','index',1,'2023-12-03 00:13:38','2023-12-03 00:13:38'),
	(7,'usermanagement','index',1,'2023-12-03 00:13:46','2023-12-03 00:13:46'),
	(8,'role','show',1,'2023-12-03 01:39:09','2023-12-03 01:39:09'),
	(9,'role','add',1,'2023-12-03 01:39:20','2023-12-03 01:39:20'),
	(10,'role','edit',1,'2023-12-03 01:39:30','2023-12-03 01:39:30'),
	(11,'role','delete',1,'2023-12-03 01:39:41','2023-12-03 01:39:41'),
	(12,'menu','show',1,'2023-12-03 01:39:51','2023-12-03 01:39:51'),
	(13,'menu','add',1,'2023-12-03 01:41:55','2023-12-03 01:41:55'),
	(14,'menu','edit',1,'2023-12-03 01:42:08','2023-12-03 01:42:08'),
	(15,'menu','delete',1,'2023-12-03 01:42:15','2023-12-03 01:42:15'),
	(16,'cms','index',1,'2023-12-04 16:19:17','2023-12-04 16:19:17'),
	(17,'address','show',1,'2023-12-04 16:22:44','2023-12-04 16:22:44'),
	(18,'address','add',1,'2023-12-04 16:22:44','2023-12-04 16:22:44'),
	(19,'address','edit',1,'2023-12-04 16:22:44','2023-12-04 16:22:44'),
	(20,'address','delete',1,'2023-12-04 16:22:44','2023-12-04 16:22:44'),
	(21,'master-data','index',1,'2023-12-07 23:55:04','2023-12-07 23:55:04'),
	(22,'bus','show',1,'2023-12-07 23:55:34','2023-12-07 23:55:34'),
	(23,'bus','add',1,'2023-12-07 23:55:34','2023-12-07 23:55:34'),
	(24,'bus','edit',1,'2023-12-07 23:55:34','2023-12-07 23:55:34'),
	(25,'bus','delete',1,'2023-12-07 23:55:34','2023-12-07 23:55:34'),
	(30,'facilities','show',1,'2023-12-08 03:30:08','2023-12-08 03:30:08'),
	(31,'facilities','add',1,'2023-12-08 03:30:08','2023-12-08 03:30:08'),
	(32,'facilities','edit',1,'2023-12-08 03:30:08','2023-12-08 03:30:08'),
	(33,'facilities','delete',1,'2023-12-08 03:30:08','2023-12-08 03:30:08'),
	(34,'class','show',1,'2023-12-09 14:40:42','2023-12-09 14:40:42'),
	(35,'class','add',1,'2023-12-09 14:40:42','2023-12-09 14:40:42'),
	(36,'class','edit',1,'2023-12-09 14:40:42','2023-12-09 14:40:42'),
	(37,'class','delete',1,'2023-12-09 14:40:42','2023-12-09 14:40:42');

/*!40000 ALTER TABLE `v2_permission` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_role`;

CREATE TABLE `v2_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(300) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_role` WRITE;
/*!40000 ALTER TABLE `v2_role` DISABLE KEYS */;

INSERT INTO `v2_role` (`id`, `uuid`, `title`, `slug`, `description`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'2f92bb7f-e5f7-4477-a901-c3a79baa088b','Super User','super-user',NULL,1,'2023-12-02 22:06:58','2023-12-02 22:06:58'),
	(2,'74610f99-13ba-43ec-91f2-e6d986bc9d65','Chief','chief',NULL,1,'2023-12-02 22:07:29','2023-12-02 22:07:29'),
	(3,'60b41abd-988b-43c3-a605-a6f6428ed792','Admin','admin',NULL,1,'2023-12-02 22:07:35','2023-12-02 22:07:35'),
	(4,'2cb92276-1675-468a-8429-f79d5841d292','Agent','agent',NULL,1,'2023-12-02 22:07:43','2023-12-02 22:07:43'),
	(7,'9ba72525-d90d-439c-9203-59b0c1c55024','Finance Auditor','finance-auditor','Sebagai akses laporan keuangan untuk tim audit J99',1,'2023-12-02 23:02:32','2023-12-02 23:02:32'),
	(8,'c3586604-fdda-4b49-9a6b-7eaaeaf8adbf','Internship','internship','Akses untuk siswa magang',1,'2023-12-02 23:05:57','2023-12-02 23:05:57'),
	(9,'380f939c-f3fe-4f95-8642-5d909eef64d5','Driver','driver','Akses untuk pengemudi dan kru',1,'2023-12-03 04:13:05','2023-12-03 04:13:05');

/*!40000 ALTER TABLE `v2_role` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table v2_role_permission
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v2_role_permission`;

CREATE TABLE `v2_role_permission` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `v2_role_permission` WRITE;
/*!40000 ALTER TABLE `v2_role_permission` DISABLE KEYS */;

INSERT INTO `v2_role_permission` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`)
VALUES
	(1,1,1,'2023-12-02 22:54:04','2023-12-02 22:54:04'),
	(2,1,2,'2023-12-02 22:54:09','2023-12-02 22:54:09'),
	(3,1,3,'2023-12-02 22:54:16','2023-12-02 22:54:16'),
	(4,1,4,'2023-12-02 22:54:21','2023-12-02 22:54:21'),
	(5,1,5,'2023-12-02 22:54:42','2023-12-02 22:54:42'),
	(7,1,6,'2023-12-03 00:17:02','2023-12-03 00:17:02'),
	(8,1,7,'2023-12-03 00:17:09','2023-12-03 00:17:09'),
	(10,1,8,'2023-12-03 01:48:02','2023-12-03 01:48:02'),
	(11,1,9,'2023-12-03 01:48:08','2023-12-03 01:48:08'),
	(12,1,10,'2023-12-03 01:48:14','2023-12-03 01:48:14'),
	(13,1,11,'2023-12-03 01:48:20','2023-12-03 01:48:20'),
	(14,1,12,'2023-12-03 01:48:26','2023-12-03 01:48:26'),
	(15,1,13,'2023-12-03 01:48:35','2023-12-03 01:48:35'),
	(16,1,14,'2023-12-03 01:48:39','2023-12-03 01:48:39'),
	(17,1,15,'2023-12-03 01:48:50','2023-12-03 01:48:50'),
	(64,2,6,'2023-12-05 23:38:03','2023-12-05 23:38:03'),
	(65,2,1,'2023-12-05 23:38:03','2023-12-05 23:38:03'),
	(66,2,16,'2023-12-05 23:38:03','2023-12-05 23:38:03'),
	(67,2,17,'2023-12-05 23:38:03','2023-12-05 23:38:03'),
	(68,2,18,'2023-12-05 23:38:03','2023-12-05 23:38:03');

/*!40000 ALTER TABLE `v2_role_permission` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
