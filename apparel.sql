-- MySQL dump 10.13  Distrib 5.7.18, for osx10.11 (x86_64)
--
-- Host: localhost    Database: pcdb
-- ------------------------------------------------------
-- Server version	5.7.18

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acl_classes`
--

DROP TABLE IF EXISTS `acl_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_69DD750638A36066` (`class_type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES (5,'(root)');
/*!40000 ALTER TABLE `acl_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_entries`
--

DROP TABLE IF EXISTS `acl_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `object_identity_id` int(10) unsigned DEFAULT NULL,
  `security_identity_id` int(10) unsigned NOT NULL,
  `field_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ace_order` smallint(5) unsigned NOT NULL,
  `mask` int(11) NOT NULL,
  `granting` tinyint(1) NOT NULL,
  `granting_strategy` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `audit_success` tinyint(1) NOT NULL,
  `audit_failure` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4` (`class_id`,`object_identity_id`,`field_name`,`ace_order`),
  KEY `IDX_46C8B806EA000B103D9AB4A6DF9183C9` (`class_id`,`object_identity_id`,`security_identity_id`),
  KEY `IDX_46C8B806EA000B10` (`class_id`),
  KEY `IDX_46C8B8063D9AB4A6` (`object_identity_id`),
  KEY `IDX_46C8B806DF9183C9` (`security_identity_id`),
  CONSTRAINT `FK_46C8B8063D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_46C8B806DF9183C9` FOREIGN KEY (`security_identity_id`) REFERENCES `acl_security_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_46C8B806EA000B10` FOREIGN KEY (`class_id`) REFERENCES `acl_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES (25,5,9,13,NULL,2,1,1,'all',0,0),(26,5,10,13,NULL,2,1056964608,1,'all',0,0),(27,5,9,14,NULL,1,1,1,'all',0,0),(28,5,10,14,NULL,1,1056964608,1,'all',0,0),(29,5,9,15,NULL,0,1,1,'all',0,0),(30,5,10,15,NULL,0,1056964608,1,'all',0,0);
/*!40000 ALTER TABLE `acl_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_object_identities`
--

DROP TABLE IF EXISTS `acl_object_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_object_identities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_object_identity_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `object_identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `entries_inheriting` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9407E5494B12AD6EA000B10` (`object_identifier`,`class_id`),
  KEY `IDX_9407E54977FA751A` (`parent_object_identity_id`),
  CONSTRAINT `FK_9407E54977FA751A` FOREIGN KEY (`parent_object_identity_id`) REFERENCES `acl_object_identities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES (9,NULL,5,'action',1),(10,NULL,5,'entity',1);
/*!40000 ALTER TABLE `acl_object_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_object_identity_ancestors`
--

DROP TABLE IF EXISTS `acl_object_identity_ancestors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_object_identity_ancestors` (
  `object_identity_id` int(10) unsigned NOT NULL,
  `ancestor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`object_identity_id`,`ancestor_id`),
  KEY `IDX_825DE2993D9AB4A6` (`object_identity_id`),
  KEY `IDX_825DE299C671CEA1` (`ancestor_id`),
  CONSTRAINT `FK_825DE2993D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_825DE299C671CEA1` FOREIGN KEY (`ancestor_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identity_ancestors`
--

LOCK TABLES `acl_object_identity_ancestors` WRITE;
/*!40000 ALTER TABLE `acl_object_identity_ancestors` DISABLE KEYS */;
INSERT INTO `acl_object_identity_ancestors` VALUES (9,9),(10,10);
/*!40000 ALTER TABLE `acl_object_identity_ancestors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_security_identities`
--

DROP TABLE IF EXISTS `acl_security_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_security_identities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `username` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8835EE78772E836AF85E0677` (`identifier`,`username`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
INSERT INTO `acl_security_identities` VALUES (13,'ROLE_ADMINISTRATOR',0),(14,'ROLE_CATALOG_MANAGER',0),(15,'ROLE_USER',0);
/*!40000 ALTER TABLE `acl_security_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acme_reference_data_color`
--

DROP TABLE IF EXISTS `acme_reference_data_color`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acme_reference_data_color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sortOrder` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hex` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `red` int(11) NOT NULL,
  `green` int(11) NOT NULL,
  `blue` int(11) NOT NULL,
  `hue` int(11) NOT NULL,
  `hslSaturation` int(11) NOT NULL,
  `light` int(11) NOT NULL,
  `hsvSaturation` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D28047C977153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme_reference_data_color`
--

LOCK TABLES `acme_reference_data_color` WRITE;
/*!40000 ALTER TABLE `acme_reference_data_color` DISABLE KEYS */;
/*!40000 ALTER TABLE `acme_reference_data_color` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acme_reference_data_fabric`
--

DROP TABLE IF EXISTS `acme_reference_data_fabric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acme_reference_data_fabric` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sortOrder` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alternativeName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5639866477153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme_reference_data_fabric`
--

LOCK TABLES `acme_reference_data_fabric` WRITE;
/*!40000 ALTER TABLE `acme_reference_data_fabric` DISABLE KEYS */;
/*!40000 ALTER TABLE `acme_reference_data_fabric` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akeneo_batch_job_execution`
--

DROP TABLE IF EXISTS `akeneo_batch_job_execution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akeneo_batch_job_execution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_instance_id` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `user` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL,
  `start_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `end_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `create_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `updated_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `health_check_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `exit_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exit_description` longtext COLLATE utf8_unicode_ci,
  `failure_exceptions` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `log_file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `raw_parameters` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  PRIMARY KEY (`id`),
  KEY `IDX_62738477593D6954` (`job_instance_id`),
  CONSTRAINT `FK_62738477593D6954` FOREIGN KEY (`job_instance_id`) REFERENCES `akeneo_batch_job_instance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_execution`
--

LOCK TABLES `akeneo_batch_job_execution` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_execution` DISABLE KEYS */;
/*!40000 ALTER TABLE `akeneo_batch_job_execution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akeneo_batch_job_execution_queue`
--

DROP TABLE IF EXISTS `akeneo_batch_job_execution_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akeneo_batch_job_execution_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_execution_id` int(11) DEFAULT NULL,
  `options` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `consumer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `updated_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_execution_queue`
--

LOCK TABLES `akeneo_batch_job_execution_queue` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_execution_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `akeneo_batch_job_execution_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akeneo_batch_job_instance`
--

DROP TABLE IF EXISTS `akeneo_batch_job_instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akeneo_batch_job_instance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `connector` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `raw_parameters` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_instance`
--

LOCK TABLES `akeneo_batch_job_instance` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_instance` DISABLE KEYS */;
INSERT INTO `akeneo_batch_job_instance` VALUES (233,'product_import','CSV Product import','csv_product_import',0,'Akeneo CSV Connector','a:17:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(234,'xlsx_product_import','XLSX product import','xlsx_product_import',0,'Akeneo XLSX Connector','a:14:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(235,'ecommerce_product_export','Product export for ecommerce','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2014_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:9:\"ecommerce\";s:7:\"locales\";a:4:{i:0;s:5:\"en_US\";i:1;s:5:\"en_GB\";i:2;s:5:\"fr_FR\";i:3;s:5:\"de_DE\";}}}}','export'),(236,'tablet_product_export','Product export for tablet','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2013_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:6:\"tablet\";s:7:\"locales\";a:2:{i:0;s:5:\"en_US\";i:1;s:5:\"en_GB\";}}}}','export'),(237,'xlsx_tablet_product_export','XLSX product export for tablet','xlsx_product_export',0,'Akeneo XLSX Connector','a:9:{s:8:\"filePath\";s:18:\"/tmp/products.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2013_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:6:\"tablet\";s:7:\"locales\";a:2:{i:0;s:5:\"en_US\";i:1;s:5:\"en_GB\";}}}}','export'),(238,'print_product_export','Product export for print','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2015_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:5:\"print\";s:7:\"locales\";a:2:{i:0;s:5:\"en_US\";i:1;s:5:\"de_DE\";}}}}','export'),(239,'csv_apparel_family_export','Footwear family export','csv_family_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/family.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(240,'category_import','Category import','csv_category_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(241,'category_export','Category export','csv_category_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:17:\"/tmp/category.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(242,'association_type_import','Association type import','csv_association_type_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(243,'association_type_export','Association type export','csv_association_type_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:25:\"/tmp/association_type.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(244,'group_import','Group import','csv_group_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(245,'group_export','Group export','csv_group_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:14:\"/tmp/group.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(246,'xlsx_group_export','XLSX group export','xlsx_group_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:27:\"/tmp/xlsx_group_export.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(247,'attribute_import','Attribute import','csv_attribute_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(248,'attribute_export','Attribute export','csv_attribute_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:18:\"/tmp/attribute.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(249,'option_import','Option import','csv_attribute_option_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(250,'option_export','Option export','csv_attribute_option_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/option.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(251,'update_product_value','Mass update products','update_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(252,'add_product_value','Mass add products values','add_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(253,'remove_product_value','Mass remove products values','remove_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(254,'move_to_category','Mass move to categories','move_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(255,'add_association','Mass associate products','add_association',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(256,'add_to_category','Mass add to categories','add_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(257,'add_to_group','Mass add product to group','add_to_group',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(258,'remove_from_category','Mass remove from categories','remove_from_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(259,'edit_common_attributes','Mass edit product attributes','edit_common_attributes',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(260,'add_attribute_value','Mass add attribute value','add_attribute_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(261,'add_to_existing_product_model','Add to existing product model','add_to_existing_product_model',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(262,'compute_product_models_descendants','Compute product models descendants','compute_product_models_descendants',0,'internal','a:0:{}','compute_product_models_descendants'),(263,'compute_completeness_of_products_family','compute completeness of products family','compute_completeness_of_products_family',0,'internal','a:0:{}','compute_completeness_of_products_family'),(264,'compute_family_variant_structure_changes','Compute variant structure changes','compute_family_variant_structure_changes',0,'internal','a:0:{}','compute_family_variant_structure_changes'),(265,'delete_products_and_product_models','Mass delete products','delete_products_and_product_models',0,'Akeneo Mass Edit Connector','a:0:{}','mass_delete');
/*!40000 ALTER TABLE `akeneo_batch_job_instance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akeneo_batch_step_execution`
--

DROP TABLE IF EXISTS `akeneo_batch_step_execution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akeneo_batch_step_execution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_execution_id` int(11) DEFAULT NULL,
  `step_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL,
  `read_count` int(11) NOT NULL,
  `write_count` int(11) NOT NULL,
  `filter_count` int(11) NOT NULL,
  `start_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `end_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `exit_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exit_description` longtext COLLATE utf8_unicode_ci,
  `terminate_only` tinyint(1) DEFAULT NULL,
  `failure_exceptions` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `errors` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `summary` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_BDE7D0925871C06B` (`job_execution_id`),
  CONSTRAINT `FK_BDE7D0925871C06B` FOREIGN KEY (`job_execution_id`) REFERENCES `akeneo_batch_job_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_step_execution`
--

LOCK TABLES `akeneo_batch_step_execution` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_step_execution` DISABLE KEYS */;
/*!40000 ALTER TABLE `akeneo_batch_step_execution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akeneo_batch_warning`
--

DROP TABLE IF EXISTS `akeneo_batch_warning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akeneo_batch_warning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_execution_id` int(11) DEFAULT NULL,
  `reason` longtext COLLATE utf8_unicode_ci,
  `reason_parameters` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `item` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_8EE0AE736C7DA296` (`step_execution_id`),
  CONSTRAINT `FK_8EE0AE736C7DA296` FOREIGN KEY (`step_execution_id`) REFERENCES `akeneo_batch_step_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_warning`
--

LOCK TABLES `akeneo_batch_warning` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_warning` DISABLE KEYS */;
/*!40000 ALTER TABLE `akeneo_batch_warning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akeneo_file_storage_file_info`
--

DROP TABLE IF EXISTS `akeneo_file_storage_file_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akeneo_file_storage_file_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) DEFAULT NULL,
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `storage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F19B3719A5D32530` (`file_key`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_file_storage_file_info`
--

LOCK TABLES `akeneo_file_storage_file_info` WRITE;
/*!40000 ALTER TABLE `akeneo_file_storage_file_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `akeneo_file_storage_file_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_access_group`
--

DROP TABLE IF EXISTS `oro_access_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_access_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FEF9EDB75E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_group`
--

LOCK TABLES `oro_access_group` WRITE;
/*!40000 ALTER TABLE `oro_access_group` DISABLE KEYS */;
INSERT INTO `oro_access_group` VALUES (20,'All'),(17,'IT support'),(18,'Manager'),(19,'Redactor');
/*!40000 ALTER TABLE `oro_access_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_access_role`
--

DROP TABLE IF EXISTS `oro_access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_access_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_673F65E757698A6A` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_role`
--

LOCK TABLES `oro_access_role` WRITE;
/*!40000 ALTER TABLE `oro_access_role` DISABLE KEYS */;
INSERT INTO `oro_access_role` VALUES (13,'ROLE_ADMINISTRATOR','Administrator'),(14,'ROLE_CATALOG_MANAGER','Catalog manager'),(15,'ROLE_USER','User');
/*!40000 ALTER TABLE `oro_access_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_config`
--

DROP TABLE IF EXISTS `oro_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CONFIG_UQ_ENTITY` (`entity`,`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_config`
--

LOCK TABLES `oro_config` WRITE;
/*!40000 ALTER TABLE `oro_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_config_value`
--

DROP TABLE IF EXISTS `oro_config_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_config_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `section` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CONFIG_VALUE_UQ_ENTITY` (`name`,`section`,`config_id`),
  KEY `IDX_DAF6DF5524DB0683` (`config_id`),
  CONSTRAINT `FK_DAF6DF5524DB0683` FOREIGN KEY (`config_id`) REFERENCES `oro_config` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_config_value`
--

LOCK TABLES `oro_config_value` WRITE;
/*!40000 ALTER TABLE `oro_config_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_config_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_user`
--

DROP TABLE IF EXISTS `oro_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ui_locale_id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_prefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `middle_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_suffix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `last_login` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `login_count` int(10) unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updatedAt` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `product_grid_filters` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `emailNotifications` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timezone` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `catalogLocale_id` int(11) DEFAULT NULL,
  `catalogScope_id` int(11) DEFAULT NULL,
  `defaultTree_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F82840BCF85E0677` (`username`),
  UNIQUE KEY `UNIQ_F82840BCE7927C74` (`email`),
  KEY `IDX_F82840BC7BBFC60C` (`catalogLocale_id`),
  KEY `IDX_F82840BCA7EA8E8C` (`ui_locale_id`),
  KEY `IDX_F82840BCEAA72736` (`catalogScope_id`),
  KEY `IDX_F82840BCD654B1EE` (`defaultTree_id`),
  CONSTRAINT `FK_F82840BC7BBFC60C` FOREIGN KEY (`catalogLocale_id`) REFERENCES `pim_catalog_locale` (`id`),
  CONSTRAINT `FK_F82840BCA7EA8E8C` FOREIGN KEY (`ui_locale_id`) REFERENCES `pim_catalog_locale` (`id`),
  CONSTRAINT `FK_F82840BCD654B1EE` FOREIGN KEY (`defaultTree_id`) REFERENCES `pim_catalog_category` (`id`),
  CONSTRAINT `FK_F82840BCEAA72736` FOREIGN KEY (`catalogScope_id`) REFERENCES `pim_catalog_channel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user`
--

LOCK TABLES `oro_user` WRITE;
/*!40000 ALTER TABLE `oro_user` DISABLE KEYS */;
INSERT INTO `oro_user` VALUES (20,898,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,NULL,1,'7j09bx4eve8s0sk0gwk4w8ocg8gok0k','DZlYJXh7XyafJbBD5FxAmxua9tTY/l3iAlDVs8z8W/q/oDln5iU58QA36CSbbig42gQNHj669TG8jdmI/edyYQ==',NULL,NULL,NULL,0,'2018-05-30 13:22:17','2018-05-30 13:22:17','[]',0,NULL,'UTC',898,9,39),(21,898,'Julia','Julia@example.com',NULL,'Julia',NULL,'Stark',NULL,NULL,NULL,1,'r6wmdss8xlc84go0ogsc0o0csko08ws','DixNGQjychTSX69R8yAt8bcnHFSYFProFnzuh85Yr6tdxE8ZJSIKmvBRJb72hxRES44GqqSVVxd3nNEs0H2xsA==',NULL,NULL,NULL,0,'2018-05-30 13:22:17','2018-05-30 13:22:17','[]',0,NULL,'UTC',898,10,38),(22,898,'Peter','Peter@example.com',NULL,'Peter',NULL,'Williams',NULL,NULL,NULL,1,'91pk21l1hlwkww4c0wko4g0c88s4ogs','qIwnSI6BNIiPYa2kQNIQW6Sjd16gWBTStAu4V41zceCca5MkIjBIucMkrbtp3gFfcYAjvOPfq0TxtzoErwBBPw==',NULL,NULL,NULL,0,'2018-05-30 13:22:17','2018-05-30 13:22:17','[]',0,NULL,'UTC',898,11,40),(23,898,'Mary','Mary@example.com',NULL,'Mary',NULL,'Smith',NULL,NULL,NULL,1,'7oimw99pqmo8wc4040kkkgs4ow04844','fG1vvX9ztDCIUoyYG5/RBhzDrmzC8/RsTHZvIrjBBFPYkyKyA5avAgj7+n+G06fkNrKK7ccG96FSpBgnlnwxzw==',NULL,NULL,NULL,0,'2018-05-30 13:22:17','2018-05-30 13:22:17','[]',0,NULL,'UTC',898,9,38),(24,898,'Sandra','Sandra@example.com',NULL,'Sandra',NULL,'Harvey',NULL,NULL,NULL,1,'88t3hgvkazgg4kk44o04sg4k4wss888','M+QWvG4WIyViUDX/vVZ9R/18JVods9f7xoOFPHMQAOWrGizOGwxhq97AOAl/GWDN7YObxEpEJT8aUnmPqZFrww==',NULL,NULL,NULL,0,'2018-05-30 13:22:17','2018-05-30 13:22:17','[]',0,NULL,'UTC',898,10,39),(25,930,'Julien','Julien@example.com',NULL,'Julien',NULL,'Février',NULL,NULL,NULL,1,'evfy1o6r1340k44cc8ss4ko0k4cgs0s','dgRxXXMSE7kXCGB6fecmzSc5kQyF8qkx9WZT6jbcauHrFBVcfI1k4TwrpnPv+zQQGnd8kHfEfuO5MUv6e81R0A==',NULL,NULL,NULL,0,'2018-05-30 13:22:17','2018-05-30 13:22:17','[]',0,NULL,'UTC',930,9,39);
/*!40000 ALTER TABLE `oro_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_user_access_group`
--

DROP TABLE IF EXISTS `oro_user_access_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_user_access_group` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `IDX_EC003EF3A76ED395` (`user_id`),
  KEY `IDX_EC003EF3FE54D947` (`group_id`),
  CONSTRAINT `FK_EC003EF3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC003EF3FE54D947` FOREIGN KEY (`group_id`) REFERENCES `oro_access_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user_access_group`
--

LOCK TABLES `oro_user_access_group` WRITE;
/*!40000 ALTER TABLE `oro_user_access_group` DISABLE KEYS */;
INSERT INTO `oro_user_access_group` VALUES (20,17),(20,20),(21,18),(21,20),(22,17),(22,20),(23,19),(23,20),(24,19),(24,20),(25,19),(25,20);
/*!40000 ALTER TABLE `oro_user_access_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_user_access_group_role`
--

DROP TABLE IF EXISTS `oro_user_access_group_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_user_access_group_role` (
  `group_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`role_id`),
  KEY `IDX_E7E7E38EFE54D947` (`group_id`),
  KEY `IDX_E7E7E38ED60322AC` (`role_id`),
  CONSTRAINT `FK_E7E7E38ED60322AC` FOREIGN KEY (`role_id`) REFERENCES `oro_access_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E7E7E38EFE54D947` FOREIGN KEY (`group_id`) REFERENCES `oro_access_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user_access_group_role`
--

LOCK TABLES `oro_user_access_group_role` WRITE;
/*!40000 ALTER TABLE `oro_user_access_group_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_user_access_group_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_user_access_role`
--

DROP TABLE IF EXISTS `oro_user_access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_user_access_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_290571BEA76ED395` (`user_id`),
  KEY `IDX_290571BED60322AC` (`role_id`),
  CONSTRAINT `FK_290571BEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_290571BED60322AC` FOREIGN KEY (`role_id`) REFERENCES `oro_access_role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user_access_role`
--

LOCK TABLES `oro_user_access_role` WRITE;
/*!40000 ALTER TABLE `oro_user_access_role` DISABLE KEYS */;
INSERT INTO `oro_user_access_role` VALUES (20,13),(21,14),(22,13),(23,15),(24,15),(25,15);
/*!40000 ALTER TABLE `oro_user_access_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_aggregated_volume`
--

DROP TABLE IF EXISTS `pim_aggregated_volume`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_aggregated_volume` (
  `volume_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `volume` json NOT NULL COMMENT '(DC2Type:native_json)',
  `aggregated_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`volume_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_aggregated_volume`
--

LOCK TABLES `pim_aggregated_volume` WRITE;
/*!40000 ALTER TABLE `pim_aggregated_volume` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_aggregated_volume` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_api_access_token`
--

DROP TABLE IF EXISTS `pim_api_access_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_api_access_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BD5E40235F37A13B` (`token`),
  KEY `IDX_BD5E4023C7440455` (`client`),
  KEY `IDX_BD5E40238D93D649` (`user`),
  CONSTRAINT `FK_BD5E40238D93D649` FOREIGN KEY (`user`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BD5E4023C7440455` FOREIGN KEY (`client`) REFERENCES `pim_api_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_api_access_token`
--

LOCK TABLES `pim_api_access_token` WRITE;
/*!40000 ALTER TABLE `pim_api_access_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_api_access_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_api_auth_code`
--

DROP TABLE IF EXISTS `pim_api_auth_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_api_auth_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AD5DC7C65F37A13B` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_api_auth_code`
--

LOCK TABLES `pim_api_auth_code` WRITE;
/*!40000 ALTER TABLE `pim_api_auth_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_api_auth_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_api_client`
--

DROP TABLE IF EXISTS `pim_api_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_api_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `random_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uris` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allowed_grant_types` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_api_client`
--

LOCK TABLES `pim_api_client` WRITE;
/*!40000 ALTER TABLE `pim_api_client` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_api_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_api_refresh_token`
--

DROP TABLE IF EXISTS `pim_api_refresh_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_api_refresh_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_264A45105F37A13B` (`token`),
  KEY `IDX_264A4510C7440455` (`client`),
  KEY `IDX_264A45108D93D649` (`user`),
  CONSTRAINT `FK_264A45108D93D649` FOREIGN KEY (`user`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_264A4510C7440455` FOREIGN KEY (`client`) REFERENCES `pim_api_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_api_refresh_token`
--

LOCK TABLES `pim_api_refresh_token` WRITE;
/*!40000 ALTER TABLE `pim_api_refresh_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_api_refresh_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association`
--

DROP TABLE IF EXISTS `pim_catalog_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `association_type_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`owner_id`,`association_type_id`),
  KEY `IDX_CC271001B1E1C39` (`association_type_id`),
  KEY `IDX_CC2710017E3C61F9` (`owner_id`),
  CONSTRAINT `FK_CC2710017E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CC271001B1E1C39` FOREIGN KEY (`association_type_id`) REFERENCES `pim_catalog_association_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association`
--

LOCK TABLES `pim_catalog_association` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_group`
--

DROP TABLE IF EXISTS `pim_catalog_association_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_group` (
  `association_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`association_id`,`group_id`),
  KEY `IDX_E91414DDEFB9C8A5` (`association_id`),
  KEY `IDX_E91414DDFE54D947` (`group_id`),
  CONSTRAINT `FK_E91414DDEFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_association` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E91414DDFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_group`
--

LOCK TABLES `pim_catalog_association_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_product`
--

DROP TABLE IF EXISTS `pim_catalog_association_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_product` (
  `association_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`association_id`,`product_id`),
  KEY `IDX_3A3A49D4EFB9C8A5` (`association_id`),
  KEY `IDX_3A3A49D44584665A` (`product_id`),
  CONSTRAINT `FK_3A3A49D44584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3A3A49D4EFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_product`
--

LOCK TABLES `pim_catalog_association_product` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_product_model`
--

DROP TABLE IF EXISTS `pim_catalog_association_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_product_model` (
  `association_id` int(11) NOT NULL,
  `product_model_id` int(11) NOT NULL,
  PRIMARY KEY (`association_id`,`product_model_id`),
  KEY `IDX_378B82C7EFB9C8A5` (`association_id`),
  KEY `IDX_378B82C7B2C5DD70` (`product_model_id`),
  CONSTRAINT `FK_378B82C7B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_378B82C7EFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_product_model`
--

LOCK TABLES `pim_catalog_association_product_model` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_product_model` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association_product_model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_product_model_to_group`
--

DROP TABLE IF EXISTS `pim_catalog_association_product_model_to_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_product_model_to_group` (
  `association_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`association_id`,`group_id`),
  KEY `IDX_16EA55AEEFB9C8A5` (`association_id`),
  KEY `IDX_16EA55AEFE54D947` (`group_id`),
  CONSTRAINT `FK_16EA55AEEFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_product_model_association` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_16EA55AEFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_product_model_to_group`
--

LOCK TABLES `pim_catalog_association_product_model_to_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_product_model_to_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association_product_model_to_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_product_model_to_product`
--

DROP TABLE IF EXISTS `pim_catalog_association_product_model_to_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_product_model_to_product` (
  `association_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`association_id`,`product_id`),
  KEY `IDX_3FF3ED19EFB9C8A5` (`association_id`),
  KEY `IDX_3FF3ED194584665A` (`product_id`),
  CONSTRAINT `FK_3FF3ED194584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3FF3ED19EFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_product_model_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_product_model_to_product`
--

LOCK TABLES `pim_catalog_association_product_model_to_product` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_product_model_to_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association_product_model_to_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_product_model_to_product_model`
--

DROP TABLE IF EXISTS `pim_catalog_association_product_model_to_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_product_model_to_product_model` (
  `association_id` int(11) NOT NULL,
  `product_model_id` int(11) NOT NULL,
  PRIMARY KEY (`association_id`,`product_model_id`),
  KEY `IDX_12D4D59CEFB9C8A5` (`association_id`),
  KEY `IDX_12D4D59CB2C5DD70` (`product_model_id`),
  CONSTRAINT `FK_12D4D59CB2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_12D4D59CEFB9C8A5` FOREIGN KEY (`association_id`) REFERENCES `pim_catalog_product_model_association` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_product_model_to_product_model`
--

LOCK TABLES `pim_catalog_association_product_model_to_product_model` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_product_model_to_product_model` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_association_product_model_to_product_model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_type`
--

DROP TABLE IF EXISTS `pim_catalog_association_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E6CF913A77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type`
--

LOCK TABLES `pim_catalog_association_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type` VALUES (17,'variant','2018-05-30 13:22:15','2018-05-30 13:22:15'),(18,'similar','2018-05-30 13:22:15','2018-05-30 13:22:15'),(19,'related','2018-05-30 13:22:15','2018-05-30 13:22:15'),(20,'cross_sell','2018-05-30 13:22:15','2018-05-30 13:22:15'),(21,'upsell','2018-05-30 13:22:15','2018-05-30 13:22:15'),(22,'substitute','2018-05-30 13:22:15','2018-05-30 13:22:15'),(23,'pack','2018-05-30 13:22:15','2018-05-30 13:22:15');
/*!40000 ALTER TABLE `pim_catalog_association_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_association_type_translation`
--

DROP TABLE IF EXISTS `pim_catalog_association_type_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_association_type_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_CCCBAA2D7E366551` (`foreign_key`),
  CONSTRAINT `FK_CCCBAA2D7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_association_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type_translation`
--

LOCK TABLES `pim_catalog_association_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type_translation` VALUES (31,17,'Variant','en_US'),(32,17,'Variant','en_GB'),(33,17,'Variante','fr_FR'),(34,17,'Variante','de_DE'),(35,18,'Similar','en_US'),(36,18,'Similar','en_GB'),(37,18,'Similaire','fr_FR'),(38,18,'Ähnlich','de_DE'),(39,19,'Related','en_US'),(40,19,'Related','en_GB'),(41,19,'Connexe','fr_FR'),(42,19,'Verbunden','de_DE'),(43,20,'Cross sell','en_US'),(44,20,'Cross sell','en_GB'),(45,20,'Vente croisée','fr_FR'),(46,20,'Cross-Selling','de_DE'),(47,21,'Upsell','en_US'),(48,21,'Upsell','en_GB'),(49,21,'Vente incitative','fr_FR'),(50,21,'Upsell','de_DE'),(51,22,'Substitute','en_US'),(52,22,'Substitute','en_GB'),(53,22,'Substitut','fr_FR'),(54,22,'Ersatz','de_DE'),(55,23,'Pack','en_US'),(56,23,'Pack','en_GB'),(57,23,'Paquet','fr_FR'),(58,23,'Pack','de_DE');
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute`
--

DROP TABLE IF EXISTS `pim_catalog_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  `useable_as_grid_filter` tinyint(1) NOT NULL DEFAULT '0',
  `max_characters` smallint(6) DEFAULT NULL,
  `validation_rule` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `validation_regexp` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wysiwyg_enabled` tinyint(1) DEFAULT NULL,
  `number_min` decimal(14,4) DEFAULT NULL,
  `number_max` decimal(14,4) DEFAULT NULL,
  `decimals_allowed` tinyint(1) DEFAULT NULL,
  `negative_allowed` tinyint(1) DEFAULT NULL,
  `date_min` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `date_max` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
  `metric_family` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_metric_unit` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_file_size` decimal(6,2) DEFAULT NULL,
  `allowed_extensions` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `minimumInputLength` smallint(6) DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_unique` tinyint(1) NOT NULL,
  `is_localizable` tinyint(1) NOT NULL,
  `is_scopable` tinyint(1) NOT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `entity_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `backend_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `properties` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`,`entity_type`),
  KEY `IDX_492FD44FFE54D947` (`group_id`),
  KEY `searchcode_idx` (`code`),
  CONSTRAINT `FK_492FD44FFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_attribute_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute`
--

LOCK TABLES `pim_catalog_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES (57,15,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,1,1,0,0,'sku','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_identifier','text','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(58,15,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'name','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(59,15,3,1,1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'description','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','textarea','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(60,16,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'thumbnail','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(61,16,2,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'image','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(62,16,3,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'legend','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(63,17,1,1,NULL,NULL,NULL,NULL,1.0000,1000.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'price','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(64,17,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'customer_rating','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(65,17,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'release_date','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_date','date','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(66,18,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(67,18,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'chest_size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(68,18,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'waist_size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(69,19,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'color','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(70,19,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'additional_colors','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(71,20,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'manufacturer','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(72,20,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'country_of_manufacture','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(73,20,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'handmade','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(74,20,4,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'weather_conditions','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(75,20,5,0,1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'care_instructions','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','textarea','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(76,20,6,0,NULL,NULL,NULL,NULL,NULL,100.0000,0,0,NULL,NULL,'Temperature','CELSIUS',NULL,'',NULL,0,0,0,0,'washing_temperature','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(77,20,7,0,NULL,NULL,NULL,NULL,NULL,10.0000,0,0,NULL,NULL,'Weight','KILOGRAM',NULL,'',NULL,0,0,0,0,'weight','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(78,20,8,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'material','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(79,20,9,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'additional_materials','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:16'),(80,21,1,1,NULL,NULL,NULL,NULL,1.0000,1000.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'cost','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(81,21,2,1,NULL,NULL,NULL,NULL,1.0000,10000.0000,0,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'number_in_stock','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(82,21,3,1,NULL,NULL,NULL,NULL,1.0000,1000.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'customs_tax','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(83,15,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'under_european_law','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(84,21,4,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10.00,'txt,pdf,doc,docx,csv,rtf',NULL,0,0,0,0,'datasheet','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_file','media','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(85,21,5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10.00,'txt,pdf,doc,docx,csv,rtf',NULL,0,0,0,0,'attachment','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_file','media','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15'),(86,16,3,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,1,0,'localizable_image','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:0:{}','2018-05-30 13:22:15','2018-05-30 13:22:15');
/*!40000 ALTER TABLE `pim_catalog_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_group`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E299C05E77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group`
--

LOCK TABLES `pim_catalog_attribute_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group` VALUES (15,'general',1,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(16,'media',2,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(17,'sales',3,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(18,'sizes',4,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(19,'colors',5,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(20,'additional',6,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(21,'internal',7,'2018-05-30 13:22:15','2018-05-30 13:22:15'),(22,'other',100,'2018-05-30 13:22:15','2018-05-30 13:22:15');
/*!40000 ALTER TABLE `pim_catalog_attribute_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_group_translation`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_group_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_group_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_B74958BD7E366551` (`foreign_key`),
  CONSTRAINT `FK_B74958BD7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_attribute_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group_translation`
--

LOCK TABLES `pim_catalog_attribute_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group_translation` VALUES (27,15,'General','en_US'),(28,15,'General','en_GB'),(29,15,'Général','fr_FR'),(30,15,'Allgemein','de_DE'),(31,16,'Media','en_US'),(32,16,'Media','en_GB'),(33,16,'Médias','fr_FR'),(34,16,'Medien','de_DE'),(35,17,'Sales','en_US'),(36,17,'Sales','en_GB'),(37,17,'Ventes','fr_FR'),(38,17,'Vertrieb','de_DE'),(39,18,'Sizes','en_US'),(40,18,'Sizes','en_GB'),(41,18,'Tailles','fr_FR'),(42,18,'Größen','de_DE'),(43,19,'Colors','en_US'),(44,19,'Colours','en_GB'),(45,19,'Couleurs','fr_FR'),(46,19,'Farben','de_DE'),(47,20,'Additional information','en_US'),(48,20,'Additional information','en_GB'),(49,20,'Informations complémentaires','fr_FR'),(50,20,'Zusätzliche Informationen','de_DE'),(51,21,'Internal','en_US'),(52,21,'Internal','en_GB'),(53,21,'Interne','fr_FR'),(54,21,'Intern','de_DE'),(55,22,'Other','en_US'),(56,22,'Other','en_GB'),(57,22,'Autre','fr_FR'),(58,22,'Ander','de_DE');
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_locale`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_locale` (
  `attribute_id` int(11) NOT NULL,
  `locale_id` int(11) NOT NULL,
  PRIMARY KEY (`attribute_id`,`locale_id`),
  KEY `IDX_26D2D5D0B6E62EFA` (`attribute_id`),
  KEY `IDX_26D2D5D0E559DFD1` (`locale_id`),
  CONSTRAINT `FK_26D2D5D0B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_26D2D5D0E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_locale`
--

LOCK TABLES `pim_catalog_attribute_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_locale` VALUES (82,879),(83,879),(83,930);
/*!40000 ALTER TABLE `pim_catalog_attribute_locale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_option`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`,`attribute_id`),
  KEY `IDX_3DD413F8B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_3DD413F8B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option`
--

LOCK TABLES `pim_catalog_attribute_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option` VALUES (92,64,'1',0),(93,64,'2',0),(94,64,'3',0),(95,64,'4',0),(96,64,'5',0),(97,66,'size_XXS',0),(98,66,'size_XS',0),(99,66,'size_S',0),(100,66,'size_M',0),(101,66,'size_L',0),(102,66,'size_XL',0),(103,66,'size_2XL',0),(104,66,'size_3XL',0),(105,67,'chest_size_XXS',0),(106,67,'chest_size_XS',0),(107,67,'chest_size_S',0),(108,67,'chest_size_M',0),(109,67,'chest_size_L',0),(110,67,'chest_size_XL',0),(111,67,'chest_size_2XL',0),(112,67,'chest_size_3XL',0),(113,68,'waist_size_XXS',0),(114,68,'waist_size_XS',0),(115,68,'waist_size_S',0),(116,68,'waist_size_M',0),(117,68,'waist_size_L',0),(118,68,'waist_size_XL',0),(119,68,'waist_size_2XL',0),(120,68,'waist_size_3XL',0),(121,69,'black',0),(122,69,'white',0),(123,69,'gray',0),(124,69,'red',0),(125,69,'blue',0),(126,69,'green',0),(127,69,'yellow',0),(128,70,'additional_black',1),(129,70,'additional_white',2),(130,70,'additional_gray',3),(131,70,'additional_red',4),(132,70,'additional_blue',5),(133,70,'additional_green',6),(134,70,'additional_yellow',7),(135,71,'american_apparel',0),(136,71,'lacoste',0),(137,71,'columbia',0),(138,71,'levi_strauss',0),(139,71,'diesel',0),(140,71,'prada',0),(141,71,'marc_o_polo',0),(142,72,'usa',0),(143,72,'france',0),(144,72,'germany',0),(145,72,'italy',0),(146,72,'china',0),(147,72,'taiwan',0),(148,72,'vietnam',0),(149,74,'dry',1),(150,74,'wet',2),(151,74,'hot',3),(152,74,'cold',4),(153,74,'snowy',5),(154,78,'cotton',1),(155,78,'flax',2),(156,78,'wool',3),(157,78,'silk',4),(158,78,'denim',5),(159,78,'leather',6),(160,78,'fur',7),(161,79,'additional_cotton',1),(162,79,'additional_flax',2),(163,79,'additional_wool',3),(164,79,'additional_silk',4),(165,79,'additional_denim',5),(166,79,'additional_leather',6),(167,79,'additional_fur',7);
/*!40000 ALTER TABLE `pim_catalog_attribute_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_option_value`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_option_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_option_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `locale_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`locale_code`,`option_id`),
  KEY `IDX_CC4B9A83A7C41D6F` (`option_id`),
  CONSTRAINT `FK_CC4B9A83A7C41D6F` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=480 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option_value`
--

LOCK TABLES `pim_catalog_attribute_option_value` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option_value` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option_value` VALUES (176,92,'en_US','1 star'),(177,92,'en_GB','1 star'),(178,92,'fr_FR','1 étoile'),(179,92,'de_DE','1 Stern'),(180,93,'en_US','2 stars'),(181,93,'en_GB','2 stars'),(182,93,'fr_FR','2 étoiles'),(183,93,'de_DE','2 Sterne'),(184,94,'en_US','3 stars'),(185,94,'en_GB','3 stars'),(186,94,'fr_FR','3 étoiles'),(187,94,'de_DE','3 Sterne'),(188,95,'en_US','4 stars'),(189,95,'en_GB','4 stars'),(190,95,'fr_FR','4 étoiles'),(191,95,'de_DE','4 Sterne'),(192,96,'en_US','5 stars'),(193,96,'en_GB','5 stars'),(194,96,'fr_FR','5 étoiles'),(195,96,'de_DE','5 Sternen'),(196,97,'en_US','XXS'),(197,97,'en_GB','XXS'),(198,97,'fr_FR','XXS'),(199,97,'de_DE','XXS'),(200,98,'en_US','XS'),(201,98,'en_GB','XS'),(202,98,'fr_FR','XS'),(203,98,'de_DE','XS'),(204,99,'en_US','S'),(205,99,'en_GB','S'),(206,99,'fr_FR','S'),(207,99,'de_DE','S'),(208,100,'en_US','M'),(209,100,'en_GB','M'),(210,100,'fr_FR','M'),(211,100,'de_DE','M'),(212,101,'en_US','L'),(213,101,'en_GB','L'),(214,101,'fr_FR','L'),(215,101,'de_DE','L'),(216,102,'en_US','XL'),(217,102,'en_GB','XL'),(218,102,'fr_FR','XL'),(219,102,'de_DE','XL'),(220,103,'en_US','2XL'),(221,103,'en_GB','2XL'),(222,103,'fr_FR','2XL'),(223,103,'de_DE','2XL'),(224,104,'en_US','3XL'),(225,104,'en_GB','3XL'),(226,104,'fr_FR','3XL'),(227,104,'de_DE','3XL'),(228,105,'en_US','XXS'),(229,105,'en_GB','XXS'),(230,105,'fr_FR','XXS'),(231,105,'de_DE','XXS'),(232,106,'en_US','XS'),(233,106,'en_GB','XS'),(234,106,'fr_FR','XS'),(235,106,'de_DE','XS'),(236,107,'en_US','S'),(237,107,'en_GB','S'),(238,107,'fr_FR','S'),(239,107,'de_DE','S'),(240,108,'en_US','M'),(241,108,'en_GB','M'),(242,108,'fr_FR','M'),(243,108,'de_DE','M'),(244,109,'en_US','L'),(245,109,'en_GB','L'),(246,109,'fr_FR','L'),(247,109,'de_DE','L'),(248,110,'en_US','XL'),(249,110,'en_GB','XL'),(250,110,'fr_FR','XL'),(251,110,'de_DE','XL'),(252,111,'en_US','2XL'),(253,111,'en_GB','2XL'),(254,111,'fr_FR','2XL'),(255,111,'de_DE','2XL'),(256,112,'en_US','3XL'),(257,112,'en_GB','3XL'),(258,112,'fr_FR','3XL'),(259,112,'de_DE','3XL'),(260,113,'en_US','XXS'),(261,113,'en_GB','XXS'),(262,113,'fr_FR','XXS'),(263,113,'de_DE','XXS'),(264,114,'en_US','XS'),(265,114,'en_GB','XS'),(266,114,'fr_FR','XS'),(267,114,'de_DE','XS'),(268,115,'en_US','S'),(269,115,'en_GB','S'),(270,115,'fr_FR','S'),(271,115,'de_DE','S'),(272,116,'en_US','M'),(273,116,'en_GB','M'),(274,116,'fr_FR','M'),(275,116,'de_DE','M'),(276,117,'en_US','L'),(277,117,'en_GB','L'),(278,117,'fr_FR','L'),(279,117,'de_DE','L'),(280,118,'en_US','XL'),(281,118,'en_GB','XL'),(282,118,'fr_FR','XL'),(283,118,'de_DE','XL'),(284,119,'en_US','2XL'),(285,119,'en_GB','2XL'),(286,119,'fr_FR','2XL'),(287,119,'de_DE','2XL'),(288,120,'en_US','3XL'),(289,120,'en_GB','3XL'),(290,120,'fr_FR','3XL'),(291,120,'de_DE','3XL'),(292,121,'en_US','Black'),(293,121,'en_GB','Black'),(294,121,'fr_FR','Noir'),(295,121,'de_DE','Schwarz'),(296,122,'en_US','White'),(297,122,'en_GB','White'),(298,122,'fr_FR','Blanc'),(299,122,'de_DE','Weiß'),(300,123,'en_US','Gray'),(301,123,'en_GB','Gray'),(302,123,'fr_FR','Gris'),(303,123,'de_DE','Grau'),(304,124,'en_US','Red'),(305,124,'en_GB','Red'),(306,124,'fr_FR','Rouge'),(307,124,'de_DE','Rot'),(308,125,'en_US','Blue'),(309,125,'en_GB','Blue'),(310,125,'fr_FR','Bleu'),(311,125,'de_DE','Blau'),(312,126,'en_US','Green'),(313,126,'en_GB','Green'),(314,126,'fr_FR','Vert'),(315,126,'de_DE','Grün'),(316,127,'en_US','Yellow'),(317,127,'en_GB','Yellow'),(318,127,'fr_FR','Jaune'),(319,127,'de_DE','Gelb'),(320,128,'en_US','Black'),(321,128,'en_GB','Black'),(322,128,'fr_FR','Noir'),(323,128,'de_DE','Schwarz'),(324,129,'en_US','White'),(325,129,'en_GB','White'),(326,129,'fr_FR','Blanc'),(327,129,'de_DE','Weiß'),(328,130,'en_US','Gray'),(329,130,'en_GB','Gray'),(330,130,'fr_FR','Gris'),(331,130,'de_DE','Grau'),(332,131,'en_US','Red'),(333,131,'en_GB','Red'),(334,131,'fr_FR','Rouge'),(335,131,'de_DE','Rot'),(336,132,'en_US','Blue'),(337,132,'en_GB','Blue'),(338,132,'fr_FR','Bleu'),(339,132,'de_DE','Blau'),(340,133,'en_US','Green'),(341,133,'en_GB','Green'),(342,133,'fr_FR','Vert'),(343,133,'de_DE','Grün'),(344,134,'en_US','Yellow'),(345,134,'en_GB','Yellow'),(346,134,'fr_FR','Jaune'),(347,134,'de_DE','Gelb'),(348,135,'en_US','American apparel'),(349,135,'en_GB','American apparel'),(350,135,'fr_FR','American apparel'),(351,135,'de_DE','American apparel'),(352,136,'en_US','Lacoste'),(353,136,'en_GB','Lacoste'),(354,136,'fr_FR','Lacoste'),(355,136,'de_DE','Lacoste'),(356,137,'en_US','Columbia'),(357,137,'en_GB','Columbia'),(358,137,'fr_FR','Columbia'),(359,137,'de_DE','Columbia'),(360,138,'en_US','Levi Strauss'),(361,138,'en_GB','Levi Strauss'),(362,138,'fr_FR','Levi Strauss'),(363,138,'de_DE','Levi Strauss'),(364,139,'en_US','Diesel'),(365,139,'en_GB','Diesel'),(366,139,'fr_FR','Diesel'),(367,139,'de_DE','Diesel'),(368,140,'en_US','Prada'),(369,140,'en_GB','Prada'),(370,140,'fr_FR','Prada'),(371,140,'de_DE','Prada'),(372,141,'en_US','Marc O’Polo'),(373,141,'en_GB','Marc O’Polo'),(374,141,'fr_FR','Marc O’Polo'),(375,141,'de_DE','Marc O’Polo'),(376,142,'en_US','USA'),(377,142,'en_GB','USA'),(378,142,'fr_FR','USA'),(379,142,'de_DE','USA'),(380,143,'en_US','France'),(381,143,'en_GB','France'),(382,143,'fr_FR','France'),(383,143,'de_DE','Frankreich'),(384,144,'en_US','Germany'),(385,144,'en_GB','Germany'),(386,144,'fr_FR','Allemagne'),(387,144,'de_DE','Deutschland'),(388,145,'en_US','Italy'),(389,145,'en_GB','Italy'),(390,145,'fr_FR','Italie'),(391,145,'de_DE','Italien'),(392,146,'en_US','China'),(393,146,'en_GB','China'),(394,146,'fr_FR','Chine'),(395,146,'de_DE','China'),(396,147,'en_US','Taiwan'),(397,147,'en_GB','Taiwan'),(398,147,'fr_FR','Taiwan'),(399,147,'de_DE','Taiwan'),(400,148,'en_US','Vietnam'),(401,148,'en_GB','Vietnam'),(402,148,'fr_FR','Vietnam'),(403,148,'de_DE','Vietnam'),(404,149,'en_US','Dry'),(405,149,'en_GB','Dry'),(406,149,'fr_FR','Sec'),(407,149,'de_DE','Trocken'),(408,150,'en_US','Wet'),(409,150,'en_GB','Wet'),(410,150,'fr_FR','Humide'),(411,150,'de_DE','Nass'),(412,151,'en_US','Hot'),(413,151,'en_GB','Hot'),(414,151,'fr_FR','Chaud'),(415,151,'de_DE','Heiß'),(416,152,'en_US','Cold'),(417,152,'en_GB','Cold'),(418,152,'fr_FR','Froid'),(419,152,'de_DE','Kalt'),(420,153,'en_US','Snowy'),(421,153,'en_GB','Snowy'),(422,153,'fr_FR','Neigeux'),(423,153,'de_DE','Verschneit'),(424,154,'en_US','Cotton'),(425,154,'en_GB','Cotton'),(426,154,'fr_FR','Coton'),(427,154,'de_DE','Baumwolle'),(428,155,'en_US','Flax'),(429,155,'en_GB','Flax'),(430,155,'fr_FR','Lin'),(431,155,'de_DE','Flachs'),(432,156,'en_US','Wool'),(433,156,'en_GB','Wool'),(434,156,'fr_FR','Laine'),(435,156,'de_DE','Wolle'),(436,157,'en_US','Silk'),(437,157,'en_GB','Silk'),(438,157,'fr_FR','Soie'),(439,157,'de_DE','Seide'),(440,158,'en_US','Denim'),(441,158,'en_GB','Denim'),(442,158,'fr_FR','Toile'),(443,158,'de_DE','Denim'),(444,159,'en_US','Leather'),(445,159,'en_GB','Leather'),(446,159,'fr_FR','Cuir'),(447,159,'de_DE','Leder'),(448,160,'en_US','Fur'),(449,160,'en_GB','Fur'),(450,160,'fr_FR','Fourrure'),(451,160,'de_DE','Pelz'),(452,161,'en_US','Cotton'),(453,161,'en_GB','Cotton'),(454,161,'fr_FR','Coton'),(455,161,'de_DE','Baumwolle'),(456,162,'en_US','Flax'),(457,162,'en_GB','Flax'),(458,162,'fr_FR','Lin'),(459,162,'de_DE','Flachs'),(460,163,'en_US','Wool'),(461,163,'en_GB','Wool'),(462,163,'fr_FR','Laine'),(463,163,'de_DE','Wolle'),(464,164,'en_US','Silk'),(465,164,'en_GB','Silk'),(466,164,'fr_FR','Soie'),(467,164,'de_DE','Seide'),(468,165,'en_US','Denim'),(469,165,'en_GB','Denim'),(470,165,'fr_FR','Toile'),(471,165,'de_DE','Denim'),(472,166,'en_US','Leather'),(473,166,'en_GB','Leather'),(474,166,'fr_FR','Cuir'),(475,166,'de_DE','Leder'),(476,167,'en_US','Fur'),(477,167,'en_GB','Fur'),(478,167,'fr_FR','Fourrure'),(479,167,'de_DE','Pelz');
/*!40000 ALTER TABLE `pim_catalog_attribute_option_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_requirement`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_requirement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_requirement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`channel_id`,`family_id`,`attribute_id`),
  KEY `IDX_B494B917C35E566A` (`family_id`),
  KEY `IDX_B494B917B6E62EFA` (`attribute_id`),
  KEY `IDX_B494B91772F5A1AA` (`channel_id`),
  CONSTRAINT `FK_B494B91772F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B494B917B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B494B917C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

LOCK TABLES `pim_catalog_attribute_requirement` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_requirement` VALUES (141,9,57,9,1),(142,9,57,11,1),(143,9,57,10,1),(144,9,58,9,1),(145,9,59,9,1),(146,9,63,9,1),(147,9,66,9,1),(148,9,69,9,1),(149,9,71,9,1),(150,9,78,9,1),(151,9,58,10,1),(152,9,63,10,1),(153,9,66,10,1),(154,9,69,10,1),(155,9,71,10,1),(156,9,58,11,1),(157,9,59,11,1),(158,9,63,11,1),(159,9,66,11,1),(160,9,69,11,1),(161,9,71,11,1),(162,9,72,11,1),(163,9,78,11,1),(164,10,57,9,1),(165,10,57,11,1),(166,10,57,10,1),(167,10,58,9,1),(168,10,59,9,1),(169,10,63,9,1),(170,10,66,9,1),(171,10,69,9,1),(172,10,71,9,1),(173,10,78,9,1),(174,10,58,10,1),(175,10,63,10,1),(176,10,66,10,1),(177,10,69,10,1),(178,10,71,10,1),(179,10,58,11,1),(180,10,59,11,1),(181,10,63,11,1),(182,10,66,11,1),(183,10,69,11,1),(184,10,71,11,1),(185,10,72,11,1),(186,10,78,11,1),(187,11,57,9,1),(188,11,57,11,1),(189,11,57,10,1),(190,11,58,9,1),(191,11,59,9,1),(192,11,63,9,1),(193,11,67,9,1),(194,11,68,9,1),(195,11,69,9,1),(196,11,71,9,1),(197,11,78,9,1),(198,11,58,10,1),(199,11,63,10,1),(200,11,67,10,1),(201,11,68,10,1),(202,11,69,10,1),(203,11,71,10,1),(204,11,58,11,1),(205,11,59,11,1),(206,11,63,11,1),(207,11,67,11,1),(208,11,68,11,1),(209,11,69,11,1),(210,11,71,11,1),(211,11,72,11,1),(212,11,74,11,1),(213,11,78,11,1),(214,12,57,9,1),(215,12,57,11,1),(216,12,57,10,1),(217,12,58,9,1),(218,12,59,9,1),(219,12,63,9,1),(220,12,58,10,1),(221,12,63,10,1),(222,12,58,11,1),(223,12,59,11,1),(224,12,63,11,1);
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_attribute_translation`
--

DROP TABLE IF EXISTS `pim_catalog_attribute_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_attribute_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_DBC2A9287E366551` (`foreign_key`),
  CONSTRAINT `FK_DBC2A9287E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_translation`
--

LOCK TABLES `pim_catalog_attribute_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (117,57,'SKU','en_US'),(118,57,'SKU','en_GB'),(119,57,'SKU','fr_FR'),(120,57,'SKU','de_DE'),(121,58,'Name','en_US'),(122,58,'Name','en_GB'),(123,58,'Nom','fr_FR'),(124,58,'Name','de_DE'),(125,59,'Description','en_US'),(126,59,'Description','en_GB'),(127,59,'Description','fr_FR'),(128,59,'Beschreibung','de_DE'),(129,60,'Thumbnail','en_US'),(130,60,'Thumbnail','en_GB'),(131,60,'Imagette','fr_FR'),(132,60,'Miniaturansicht','de_DE'),(133,61,'Image','en_US'),(134,61,'Image','en_GB'),(135,61,'Image','fr_FR'),(136,61,'Bild','de_DE'),(137,62,'Legend','en_US'),(138,62,'Legend','en_GB'),(139,62,'Légende','fr_FR'),(140,62,'Legende','de_DE'),(141,63,'Price','en_US'),(142,63,'Price','en_GB'),(143,63,'Prix','fr_FR'),(144,63,'Preis','de_DE'),(145,64,'Customer rating','en_US'),(146,64,'Customer rating','en_GB'),(147,64,'Évaluation du client','fr_FR'),(148,64,'Kundenbewertung','de_DE'),(149,65,'Release date','en_US'),(150,65,'Release date','en_GB'),(151,65,'Date de sortie','fr_FR'),(152,65,'Erscheinungsdatum','de_DE'),(153,66,'Size','en_US'),(154,66,'Size','en_GB'),(155,66,'Taille','fr_FR'),(156,66,'Größe','de_DE'),(157,67,'Chest size','en_US'),(158,67,'Chest size','en_GB'),(159,67,'Tour de poitrine','fr_FR'),(160,67,'Brustumfang','de_DE'),(161,68,'Waist size','en_US'),(162,68,'Waist size','en_GB'),(163,68,'Tour de taille','fr_FR'),(164,68,'Taillengröße','de_DE'),(165,69,'Color','en_US'),(166,69,'Colour','en_GB'),(167,69,'Couleur','fr_FR'),(168,69,'Farbe','de_DE'),(169,70,'Additional colors','en_US'),(170,70,'Additional colours','en_GB'),(171,70,'Autres couleurs','fr_FR'),(172,70,'Zusätzliche Farben','de_DE'),(173,71,'Manufacturer','en_US'),(174,71,'Manufacturer','en_GB'),(175,71,'Fabricant','fr_FR'),(176,71,'Hersteller','de_DE'),(177,72,'Country of manufacture','en_US'),(178,72,'Country of manufacture','en_GB'),(179,72,'Pays de fabrication','fr_FR'),(180,72,'Herstellungsland','de_DE'),(181,73,'Handmade','en_US'),(182,73,'Handmade','en_GB'),(183,73,'Fait main','fr_FR'),(184,73,'Handgefertigt','de_DE'),(185,74,'Weather conditions','en_US'),(186,74,'Weather conditions','en_GB'),(187,74,'Conditions météorologiques','fr_FR'),(188,74,'Wetterverhältnisse','de_DE'),(189,75,'Care instructions','en_US'),(190,75,'Care instructions','en_GB'),(191,75,'Conseils d\'entretien','fr_FR'),(192,75,'Pflegehinweise','de_DE'),(193,76,'Washing temperature','en_US'),(194,76,'Washing temperature','en_GB'),(195,76,'Température de lavage','fr_FR'),(196,76,'Waschtemperatur','de_DE'),(197,77,'Weight','en_US'),(198,77,'Weight','en_GB'),(199,77,'Poids','fr_FR'),(200,77,'Gewicht','de_DE'),(201,78,'Material','en_US'),(202,78,'Material','en_GB'),(203,78,'Matériau','fr_FR'),(204,78,'Material','de_DE'),(205,79,'Additional materials','en_US'),(206,79,'Additional materials','en_GB'),(207,79,'Matériels supplémentaires','fr_FR'),(208,79,'Zusätzliche Materialien','de_DE'),(209,80,'Cost','en_US'),(210,80,'Cost','en_GB'),(211,80,'Coût','fr_FR'),(212,80,'Kosten','de_DE'),(213,81,'Number in stock','en_US'),(214,81,'Number in stock','en_GB'),(215,81,'Nombre en stock','fr_FR'),(216,81,'Anzahl auf Lager','de_DE'),(217,82,'Customs tax','en_US'),(218,82,'Customs tax','en_GB'),(219,82,'Taxe de douanes','fr_FR'),(220,82,'Zollsteuer','de_DE'),(221,83,'Under European law','en_US'),(222,83,'Under European law','en_GB'),(223,83,'Sous la loi Européenne','fr_FR'),(224,83,'Nach europäischem Recht','de_DE'),(225,84,'Datasheet','en_US'),(226,84,'Datasheet','en_GB'),(227,84,'Fiche technique','fr_FR'),(228,84,'Datenblatt','de_DE'),(229,85,'Attachment','en_US'),(230,85,'Attachment','en_GB'),(231,85,'Attachements','fr_FR'),(232,85,'Befestigung','de_DE'),(233,86,'Localizable image','en_US'),(234,86,'Localizable image','en_GB'),(235,86,'Image localisée','fr_FR'),(236,86,'Lokalisiertes Bild','de_DE');
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_category`
--

DROP TABLE IF EXISTS `pim_catalog_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `root` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pim_category_code_uc` (`code`),
  KEY `IDX_350D8339727ACA70` (`parent_id`),
  CONSTRAINT `FK_350D8339727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category`
--

LOCK TABLES `pim_catalog_category` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
INSERT INTO `pim_catalog_category` VALUES (38,NULL,'2013_collection','2018-05-30 13:22:14',38,0,1,6),(39,NULL,'2014_collection','2018-05-30 13:22:14',39,0,1,6),(40,NULL,'2015_collection','2018-05-30 13:22:14',40,0,1,22),(41,38,'men_2013','2018-05-30 13:22:14',38,1,2,3),(42,38,'women_2013','2018-05-30 13:22:14',38,1,4,5),(43,39,'men_2014','2018-05-30 13:22:14',39,1,2,3),(44,39,'women_2014','2018-05-30 13:22:14',39,1,4,5),(45,40,'men_2015','2018-05-30 13:22:14',40,1,2,11),(46,40,'women_2015','2018-05-30 13:22:14',40,1,12,21),(47,45,'men_2015_spring','2018-05-30 13:22:15',40,2,3,4),(48,45,'men_2015_summer','2018-05-30 13:22:15',40,2,5,6),(49,45,'men_2015_autumn','2018-05-30 13:22:15',40,2,7,8),(50,45,'men_2015_winter','2018-05-30 13:22:15',40,2,9,10),(51,46,'women_2015_spring','2018-05-30 13:22:15',40,2,13,14),(52,46,'women_2015_summer','2018-05-30 13:22:15',40,2,15,16),(53,46,'women_2015_autumn','2018-05-30 13:22:15',40,2,17,18),(54,46,'women_2015_winter','2018-05-30 13:22:15',40,2,19,20);
/*!40000 ALTER TABLE `pim_catalog_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_category_product`
--

DROP TABLE IF EXISTS `pim_catalog_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_category_product` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `IDX_512179C14584665A` (`product_id`),
  KEY `IDX_512179C112469DE2` (`category_id`),
  CONSTRAINT `FK_512179C112469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_512179C14584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_product`
--

LOCK TABLES `pim_catalog_category_product` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_category_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_category_product_model`
--

DROP TABLE IF EXISTS `pim_catalog_category_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_category_product_model` (
  `product_model_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`product_model_id`,`category_id`),
  KEY `IDX_62B5D36B2C5DD70` (`product_model_id`),
  KEY `IDX_62B5D3612469DE2` (`category_id`),
  CONSTRAINT `FK_62B5D3612469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_62B5D36B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_product_model`
--

LOCK TABLES `pim_catalog_category_product_model` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_product_model` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_category_product_model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_category_translation`
--

DROP TABLE IF EXISTS `pim_catalog_category_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_category_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_1C0FA6B77E366551` (`foreign_key`),
  CONSTRAINT `FK_1C0FA6B77E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_translation`
--

LOCK TABLES `pim_catalog_category_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_translation` VALUES (99,38,'2013 collection','en_US'),(100,38,'2013 collection','en_GB'),(101,39,'2014 collection','en_US'),(102,39,'2014 collection','en_GB'),(103,39,'Collection 2014','fr_FR'),(104,39,'2014 Kollektion','de_DE'),(105,40,'2015 collection','en_US'),(106,40,'Kollektion','de_DE'),(107,41,'2013 men\'s collection','en_US'),(108,41,'2013 men\'s collection','en_GB'),(109,42,'2013 women\'s collection','en_US'),(110,42,'2013 women\'s collection','en_GB'),(111,43,'2014 men\'s collection','en_US'),(112,43,'2014 men\'s collection','en_GB'),(113,43,'La collection de 2014 hommes','fr_FR'),(114,43,'2014 Männer-Kollektion','de_DE'),(115,44,'2014 women\'s collection','en_US'),(116,44,'2014 women\'s collection','en_GB'),(117,44,'La collection de 2014 femmes','fr_FR'),(118,44,'2014 Damenkollektion','de_DE'),(119,45,'2015 men\'s collection','en_US'),(120,45,'2015 Männer-Kollektion','de_DE'),(121,46,'2015 women\'s collection','en_US'),(122,46,'2015 Damenkollektion','de_DE'),(123,47,'2015 men\'s spring collection','en_US'),(124,47,'2015 Männer-Frühjahrskollektion','de_DE'),(125,48,'2015 men\'s summer collection','en_US'),(126,48,'2015 Männer-Sommer-Kollektion','de_DE'),(127,49,'2015 men\'s autumn collection','en_US'),(128,49,'2015 Männer-Herbstkollektion','de_DE'),(129,50,'2015 men\'s winter collection','en_US'),(130,50,'2015 Männer-Winterkollektion','de_DE'),(131,51,'2015 women\'s spring collection','en_US'),(132,51,'2015 Damen-Frühjahrskollektion','de_DE'),(133,52,'2015 women\'s summer collection','en_US'),(134,52,'2015 Damen-Sommer-Kollektion','de_DE'),(135,53,'2015 women\'s autumn collection','en_US'),(136,53,'2015 Damen-Herbstkollektion','de_DE'),(137,54,'2015 women\'s winter collection','en_US'),(138,54,'2015 Damen-Winterkollektion','de_DE');
/*!40000 ALTER TABLE `pim_catalog_category_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_channel`
--

DROP TABLE IF EXISTS `pim_catalog_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `conversionUnits` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E07E932A77153098` (`code`),
  KEY `IDX_E07E932A12469DE2` (`category_id`),
  CONSTRAINT `FK_E07E932A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel`
--

LOCK TABLES `pim_catalog_channel` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel` VALUES (9,39,'ecommerce','a:0:{}'),(10,38,'tablet','a:0:{}'),(11,40,'print','a:0:{}');
/*!40000 ALTER TABLE `pim_catalog_channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_channel_currency`
--

DROP TABLE IF EXISTS `pim_catalog_channel_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_channel_currency` (
  `channel_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  PRIMARY KEY (`channel_id`,`currency_id`),
  KEY `IDX_5B55C68472F5A1AA` (`channel_id`),
  KEY `IDX_5B55C68438248176` (`currency_id`),
  CONSTRAINT `FK_5B55C68438248176` FOREIGN KEY (`currency_id`) REFERENCES `pim_catalog_currency` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5B55C68472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_currency`
--

LOCK TABLES `pim_catalog_channel_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_currency` VALUES (9,1177),(9,1178),(9,1179),(10,1177),(10,1179),(11,1177),(11,1178);
/*!40000 ALTER TABLE `pim_catalog_channel_currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_channel_locale`
--

DROP TABLE IF EXISTS `pim_catalog_channel_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_channel_locale` (
  `channel_id` int(11) NOT NULL,
  `locale_id` int(11) NOT NULL,
  PRIMARY KEY (`channel_id`,`locale_id`),
  KEY `IDX_D8113CB272F5A1AA` (`channel_id`),
  KEY `IDX_D8113CB2E559DFD1` (`locale_id`),
  CONSTRAINT `FK_D8113CB272F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D8113CB2E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_locale`
--

LOCK TABLES `pim_catalog_channel_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_locale` VALUES (9,879),(9,889),(9,898),(9,930),(10,889),(10,898),(11,879),(11,898);
/*!40000 ALTER TABLE `pim_catalog_channel_locale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_channel_translation`
--

DROP TABLE IF EXISTS `pim_catalog_channel_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_channel_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_8A91679D7E366551` (`foreign_key`),
  CONSTRAINT `FK_8A91679D7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_translation`
--

LOCK TABLES `pim_catalog_channel_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_translation` VALUES (25,9,'Ecommerce','en_US'),(26,9,'Ecommerce','de_DE'),(27,9,'Ecommerce','fr_FR'),(28,10,'Tablet','en_US'),(29,10,'Tablet','de_DE'),(30,10,'Tablette','fr_FR'),(31,11,'Print','en_US'),(32,11,'Drucken','de_DE'),(33,11,'Impression','fr_FR');
/*!40000 ALTER TABLE `pim_catalog_channel_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_completeness`
--

DROP TABLE IF EXISTS `pim_catalog_completeness`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_completeness` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locale_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ratio` int(11) NOT NULL,
  `missing_count` int(11) NOT NULL,
  `required_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`channel_id`,`locale_id`,`product_id`),
  KEY `IDX_113BA854E559DFD1` (`locale_id`),
  KEY `IDX_113BA85472F5A1AA` (`channel_id`),
  KEY `IDX_113BA8544584665A` (`product_id`),
  KEY `ratio_idx` (`ratio`),
  CONSTRAINT `FK_113BA8544584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2179 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_completeness`
--

LOCK TABLES `pim_catalog_completeness` WRITE;
/*!40000 ALTER TABLE `pim_catalog_completeness` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_completeness` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_completeness_missing_attribute`
--

DROP TABLE IF EXISTS `pim_catalog_completeness_missing_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_completeness_missing_attribute` (
  `completeness_id` int(11) NOT NULL,
  `missing_attribute_id` int(11) NOT NULL,
  PRIMARY KEY (`completeness_id`,`missing_attribute_id`),
  KEY `IDX_DF61BBD3B640FBA5` (`completeness_id`),
  KEY `IDX_DF61BBD3762147F6` (`missing_attribute_id`),
  CONSTRAINT `FK_DF61BBD3762147F6` FOREIGN KEY (`missing_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DF61BBD3B640FBA5` FOREIGN KEY (`completeness_id`) REFERENCES `pim_catalog_completeness` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_completeness_missing_attribute`
--

LOCK TABLES `pim_catalog_completeness_missing_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_completeness_missing_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_completeness_missing_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_currency`
--

DROP TABLE IF EXISTS `pim_catalog_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `is_activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5A1712C777153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1471 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_currency`
--

LOCK TABLES `pim_catalog_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (1177,'USD',1),(1178,'EUR',1),(1179,'GBP',1),(1180,'ADP',0),(1181,'AED',0),(1182,'AFA',0),(1183,'AFN',0),(1184,'ALK',0),(1185,'ALL',0),(1186,'AMD',0),(1187,'ANG',0),(1188,'AOA',0),(1189,'AOK',0),(1190,'AON',0),(1191,'AOR',0),(1192,'ARA',0),(1193,'ARL',0),(1194,'ARM',0),(1195,'ARP',0),(1196,'ARS',0),(1197,'ATS',0),(1198,'AUD',0),(1199,'AWG',0),(1200,'AZM',0),(1201,'AZN',0),(1202,'BAD',0),(1203,'BAM',0),(1204,'BAN',0),(1205,'BBD',0),(1206,'BDT',0),(1207,'BEC',0),(1208,'BEF',0),(1209,'BEL',0),(1210,'BGL',0),(1211,'BGM',0),(1212,'BGN',0),(1213,'BGO',0),(1214,'BHD',0),(1215,'BIF',0),(1216,'BMD',0),(1217,'BND',0),(1218,'BOB',0),(1219,'BOL',0),(1220,'BOP',0),(1221,'BOV',0),(1222,'BRB',0),(1223,'BRC',0),(1224,'BRE',0),(1225,'BRL',0),(1226,'BRN',0),(1227,'BRR',0),(1228,'BRZ',0),(1229,'BSD',0),(1230,'BTN',0),(1231,'BUK',0),(1232,'BWP',0),(1233,'BYB',0),(1234,'BYR',0),(1235,'BZD',0),(1236,'CAD',0),(1237,'CDF',0),(1238,'CHE',0),(1239,'CHF',0),(1240,'CHW',0),(1241,'CLE',0),(1242,'CLF',0),(1243,'CLP',0),(1244,'CNX',0),(1245,'CNY',0),(1246,'COP',0),(1247,'COU',0),(1248,'CRC',0),(1249,'CSD',0),(1250,'CSK',0),(1251,'CUC',0),(1252,'CUP',0),(1253,'CVE',0),(1254,'CYP',0),(1255,'CZK',0),(1256,'DDM',0),(1257,'DEM',0),(1258,'DJF',0),(1259,'DKK',0),(1260,'DOP',0),(1261,'DZD',0),(1262,'ECS',0),(1263,'ECV',0),(1264,'EEK',0),(1265,'EGP',0),(1266,'ERN',0),(1267,'ESA',0),(1268,'ESB',0),(1269,'ESP',0),(1270,'ETB',0),(1271,'FIM',0),(1272,'FJD',0),(1273,'FKP',0),(1274,'FRF',0),(1275,'GEK',0),(1276,'GEL',0),(1277,'GHC',0),(1278,'GHS',0),(1279,'GIP',0),(1280,'GMD',0),(1281,'GNF',0),(1282,'GNS',0),(1283,'GQE',0),(1284,'GRD',0),(1285,'GTQ',0),(1286,'GWE',0),(1287,'GWP',0),(1288,'GYD',0),(1289,'HKD',0),(1290,'HNL',0),(1291,'HRD',0),(1292,'HRK',0),(1293,'HTG',0),(1294,'HUF',0),(1295,'IDR',0),(1296,'IEP',0),(1297,'ILP',0),(1298,'ILR',0),(1299,'ILS',0),(1300,'INR',0),(1301,'IQD',0),(1302,'IRR',0),(1303,'ISJ',0),(1304,'ISK',0),(1305,'ITL',0),(1306,'JMD',0),(1307,'JOD',0),(1308,'JPY',0),(1309,'KES',0),(1310,'KGS',0),(1311,'KHR',0),(1312,'KMF',0),(1313,'KPW',0),(1314,'KRH',0),(1315,'KRO',0),(1316,'KRW',0),(1317,'KWD',0),(1318,'KYD',0),(1319,'KZT',0),(1320,'LAK',0),(1321,'LBP',0),(1322,'LKR',0),(1323,'LRD',0),(1324,'LSL',0),(1325,'LTL',0),(1326,'LTT',0),(1327,'LUC',0),(1328,'LUF',0),(1329,'LUL',0),(1330,'LVL',0),(1331,'LVR',0),(1332,'LYD',0),(1333,'MAD',0),(1334,'MAF',0),(1335,'MCF',0),(1336,'MDC',0),(1337,'MDL',0),(1338,'MGA',0),(1339,'MGF',0),(1340,'MKD',0),(1341,'MKN',0),(1342,'MLF',0),(1343,'MMK',0),(1344,'MNT',0),(1345,'MOP',0),(1346,'MRO',0),(1347,'MTL',0),(1348,'MTP',0),(1349,'MUR',0),(1350,'MVP',0),(1351,'MVR',0),(1352,'MWK',0),(1353,'MXN',0),(1354,'MXP',0),(1355,'MXV',0),(1356,'MYR',0),(1357,'MZE',0),(1358,'MZM',0),(1359,'MZN',0),(1360,'NAD',0),(1361,'NGN',0),(1362,'NIC',0),(1363,'NIO',0),(1364,'NLG',0),(1365,'NOK',0),(1366,'NPR',0),(1367,'NZD',0),(1368,'OMR',0),(1369,'PAB',0),(1370,'PEI',0),(1371,'PEN',0),(1372,'PES',0),(1373,'PGK',0),(1374,'PHP',0),(1375,'PKR',0),(1376,'PLN',0),(1377,'PLZ',0),(1378,'PTE',0),(1379,'PYG',0),(1380,'QAR',0),(1381,'RHD',0),(1382,'ROL',0),(1383,'RON',0),(1384,'RSD',0),(1385,'RUB',0),(1386,'RUR',0),(1387,'RWF',0),(1388,'SAR',0),(1389,'SBD',0),(1390,'SCR',0),(1391,'SDD',0),(1392,'SDG',0),(1393,'SDP',0),(1394,'SEK',0),(1395,'SGD',0),(1396,'SHP',0),(1397,'SIT',0),(1398,'SKK',0),(1399,'SLL',0),(1400,'SOS',0),(1401,'SRD',0),(1402,'SRG',0),(1403,'SSP',0),(1404,'STD',0),(1405,'SUR',0),(1406,'SVC',0),(1407,'SYP',0),(1408,'SZL',0),(1409,'THB',0),(1410,'TJR',0),(1411,'TJS',0),(1412,'TMM',0),(1413,'TMT',0),(1414,'TND',0),(1415,'TOP',0),(1416,'TPE',0),(1417,'TRL',0),(1418,'TRY',0),(1419,'TTD',0),(1420,'TWD',0),(1421,'TZS',0),(1422,'UAH',0),(1423,'UAK',0),(1424,'UGS',0),(1425,'UGX',0),(1426,'USN',0),(1427,'USS',0),(1428,'UYI',0),(1429,'UYP',0),(1430,'UYU',0),(1431,'UZS',0),(1432,'VEB',0),(1433,'VEF',0),(1434,'VND',0),(1435,'VNN',0),(1436,'VUV',0),(1437,'WST',0),(1438,'XAF',0),(1439,'XAU',0),(1440,'XBA',0),(1441,'XBB',0),(1442,'XBC',0),(1443,'XBD',0),(1444,'XCD',0),(1445,'XDR',0),(1446,'XEU',0),(1447,'XFO',0),(1448,'XFU',0),(1449,'XOF',0),(1450,'XPD',0),(1451,'XPF',0),(1452,'XRE',0),(1453,'XSU',0),(1454,'XTS',0),(1455,'XXX',0),(1456,'YDD',0),(1457,'YER',0),(1458,'YUD',0),(1459,'YUM',0),(1460,'YUN',0),(1461,'YUR',0),(1462,'ZAL',0),(1463,'ZAR',0),(1464,'ZMK',0),(1465,'ZMW',0),(1466,'ZRN',0),(1467,'ZRZ',0),(1468,'ZWD',0),(1469,'ZWL',0),(1470,'ZWR',0);
/*!40000 ALTER TABLE `pim_catalog_currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family`
--

DROP TABLE IF EXISTS `pim_catalog_family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label_attribute_id` int(11) DEFAULT NULL,
  `image_attribute_id` int(11) DEFAULT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9063207277153098` (`code`),
  KEY `IDX_90632072E2D3A503` (`label_attribute_id`),
  KEY `IDX_90632072BC295696` (`image_attribute_id`),
  CONSTRAINT `FK_90632072BC295696` FOREIGN KEY (`image_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_90632072E2D3A503` FOREIGN KEY (`label_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family`
--

LOCK TABLES `pim_catalog_family` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
INSERT INTO `pim_catalog_family` VALUES (9,58,NULL,'tshirts','2018-05-30 13:22:16','2018-05-30 13:22:16'),(10,58,NULL,'sweaters','2018-05-30 13:22:16','2018-05-30 13:22:16'),(11,58,NULL,'jackets','2018-05-30 13:22:16','2018-05-30 13:22:16'),(12,58,NULL,'sandals','2018-05-30 13:22:16','2018-05-30 13:22:16');
/*!40000 ALTER TABLE `pim_catalog_family` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family_attribute`
--

DROP TABLE IF EXISTS `pim_catalog_family_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family_attribute` (
  `family_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  PRIMARY KEY (`family_id`,`attribute_id`),
  KEY `IDX_76074884C35E566A` (`family_id`),
  KEY `IDX_76074884B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_76074884B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_76074884C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_attribute`
--

LOCK TABLES `pim_catalog_family_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_family_attribute` VALUES (9,57),(9,58),(9,59),(9,60),(9,61),(9,62),(9,63),(9,64),(9,65),(9,66),(9,69),(9,70),(9,71),(9,72),(9,73),(9,76),(9,77),(9,78),(9,80),(9,81),(9,82),(9,84),(10,57),(10,58),(10,59),(10,60),(10,61),(10,63),(10,64),(10,65),(10,66),(10,69),(10,70),(10,71),(10,72),(10,73),(10,75),(10,76),(10,77),(10,78),(10,79),(10,80),(10,81),(10,82),(11,57),(11,58),(11,59),(11,60),(11,61),(11,63),(11,64),(11,65),(11,67),(11,68),(11,69),(11,70),(11,71),(11,72),(11,73),(11,74),(11,75),(11,76),(11,77),(11,78),(11,79),(11,80),(11,81),(11,82),(12,57),(12,58),(12,59),(12,63);
/*!40000 ALTER TABLE `pim_catalog_family_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family_translation`
--

DROP TABLE IF EXISTS `pim_catalog_family_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_547C9A607E366551` (`foreign_key`),
  CONSTRAINT `FK_547C9A607E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_translation`
--

LOCK TABLES `pim_catalog_family_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_family_translation` VALUES (15,9,'T-shirts','en_US'),(16,9,'T-shirts','en_GB'),(17,9,'T-shirts','fr_FR'),(18,9,'T-shirts','de_DE'),(19,10,'Sweaters','en_US'),(20,10,'Chandails','en_GB'),(21,10,'Sweaters','fr_FR'),(22,10,'Pullovern','de_DE'),(23,11,'Jackets','en_US'),(24,11,'Jackets','en_GB'),(25,11,'Vestes','fr_FR'),(26,11,'Jacken','de_DE'),(27,12,'Sandals','en_US'),(28,12,'Sandals','en_GB'),(29,12,'Sandals','fr_FR'),(30,12,'Sandals','de_DE');
/*!40000 ALTER TABLE `pim_catalog_family_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family_variant`
--

DROP TABLE IF EXISTS `pim_catalog_family_variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) DEFAULT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FBA5AB4577153098` (`code`),
  KEY `IDX_FBA5AB45C35E566A` (`family_id`),
  CONSTRAINT `FK_FBA5AB45C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_variant`
--

LOCK TABLES `pim_catalog_family_variant` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_variant` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_family_variant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family_variant_attribute_set`
--

DROP TABLE IF EXISTS `pim_catalog_family_variant_attribute_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family_variant_attribute_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_variant_attribute_set`
--

LOCK TABLES `pim_catalog_family_variant_attribute_set` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_variant_attribute_set` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_family_variant_attribute_set` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family_variant_has_variant_attribute_sets`
--

DROP TABLE IF EXISTS `pim_catalog_family_variant_has_variant_attribute_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family_variant_has_variant_attribute_sets` (
  `family_variant_id` int(11) NOT NULL,
  `variant_attribute_sets_id` int(11) NOT NULL,
  PRIMARY KEY (`family_variant_id`,`variant_attribute_sets_id`),
  UNIQUE KEY `UNIQ_1F4DC702D8404D` (`variant_attribute_sets_id`),
  KEY `IDX_1F4DC7028A37AD0` (`family_variant_id`),
  CONSTRAINT `FK_1F4DC7028A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (`id`),
  CONSTRAINT `FK_1F4DC702D8404D` FOREIGN KEY (`variant_attribute_sets_id`) REFERENCES `pim_catalog_family_variant_attribute_set` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_variant_has_variant_attribute_sets`
--

LOCK TABLES `pim_catalog_family_variant_has_variant_attribute_sets` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_variant_has_variant_attribute_sets` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_family_variant_has_variant_attribute_sets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_family_variant_translation`
--

DROP TABLE IF EXISTS `pim_catalog_family_variant_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_family_variant_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_CB9A96AF7E366551` (`foreign_key`),
  CONSTRAINT `FK_CB9A96AF7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_family_variant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_variant_translation`
--

LOCK TABLES `pim_catalog_family_variant_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_variant_translation` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_family_variant_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group`
--

DROP TABLE IF EXISTS `pim_catalog_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5D6997ED77153098` (`code`),
  KEY `IDX_5D6997EDC54C8C93` (`type_id`),
  CONSTRAINT `FK_5D6997EDC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `pim_catalog_group_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group`
--

LOCK TABLES `pim_catalog_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_group` VALUES (3,6,'similar'),(4,7,'related'),(5,8,'cross_sell'),(6,9,'upsell'),(7,10,'substitute');
/*!40000 ALTER TABLE `pim_catalog_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group_product`
--

DROP TABLE IF EXISTS `pim_catalog_group_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group_product` (
  `product_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`group_id`),
  KEY `IDX_7AC0C83A4584665A` (`product_id`),
  KEY `IDX_7AC0C83AFE54D947` (`group_id`),
  CONSTRAINT `FK_7AC0C83A4584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7AC0C83AFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_product`
--

LOCK TABLES `pim_catalog_group_product` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_group_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group_translation`
--

DROP TABLE IF EXISTS `pim_catalog_group_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_622D98DB7E366551` (`foreign_key`),
  CONSTRAINT `FK_622D98DB7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_translation`
--

LOCK TABLES `pim_catalog_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_translation` VALUES (5,3,'Similar','en_US'),(6,3,'Similar','en_GB'),(7,3,'Similaire','fr_FR'),(8,3,'Ähnlich','de_DE'),(9,4,'Related','en_US'),(10,4,'Related','en_GB'),(11,4,'Connexe','fr_FR'),(12,4,'Verbunden','de_DE'),(13,5,'Cross sell','en_US'),(14,5,'Cross sell','en_GB'),(15,5,'Vente croisée','fr_FR'),(16,5,'Cross-Selling','de_DE'),(17,6,'Upsell','en_US'),(18,6,'Upsell','en_GB'),(19,6,'Vente incitative','fr_FR'),(20,6,'Upsell','de_DE'),(21,7,'Substitute','en_US'),(22,7,'Substitute','en_GB'),(23,7,'Substitut','fr_FR'),(24,7,'Ersatz','de_DE');
/*!40000 ALTER TABLE `pim_catalog_group_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group_type`
--

DROP TABLE IF EXISTS `pim_catalog_group_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_693B2EF777153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type`
--

LOCK TABLES `pim_catalog_group_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type` VALUES (8,'cross_sell'),(7,'related'),(6,'similar'),(10,'substitute'),(9,'upsell');
/*!40000 ALTER TABLE `pim_catalog_group_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group_type_translation`
--

DROP TABLE IF EXISTS `pim_catalog_group_type_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group_type_translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`),
  KEY `IDX_6EF81AEB7E366551` (`foreign_key`),
  CONSTRAINT `FK_6EF81AEB7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_group_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type_translation`
--

LOCK TABLES `pim_catalog_group_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type_translation` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_group_type_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_locale`
--

DROP TABLE IF EXISTS `pim_catalog_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_locale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `is_activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7405C7B177153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1051 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_locale`
--

LOCK TABLES `pim_catalog_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_locale` VALUES (841,'af_ZA',0),(842,'am_ET',0),(843,'ar_AE',0),(844,'ar_BH',0),(845,'ar_DZ',0),(846,'ar_EG',0),(847,'ar_IQ',0),(848,'ar_JO',0),(849,'ar_KW',0),(850,'ar_LB',0),(851,'ar_LY',0),(852,'ar_MA',0),(853,'arn_CL',0),(854,'ar_OM',0),(855,'ar_QA',0),(856,'ar_SA',0),(857,'ar_SY',0),(858,'ar_TN',0),(859,'ar_YE',0),(860,'as_IN',0),(861,'az_Cyrl_AZ',0),(862,'az_Latn_AZ',0),(863,'ba_RU',0),(864,'be_BY',0),(865,'bg_BG',0),(866,'bn_BD',0),(867,'bn_IN',0),(868,'bo_CN',0),(869,'br_FR',0),(870,'bs_Cyrl_BA',0),(871,'bs_Latn_BA',0),(872,'ca_ES',0),(873,'co_FR',0),(874,'cs_CZ',0),(875,'cy_GB',0),(876,'da_DK',0),(877,'de_AT',0),(878,'de_CH',0),(879,'de_DE',1),(880,'de_LI',0),(881,'de_LU',0),(882,'dsb_DE',0),(883,'dv_MV',0),(884,'el_GR',0),(885,'en_029',0),(886,'en_AU',0),(887,'en_BZ',0),(888,'en_CA',0),(889,'en_GB',1),(890,'en_IE',0),(891,'en_IN',0),(892,'en_JM',0),(893,'en_MY',0),(894,'en_NZ',0),(895,'en_PH',0),(896,'en_SG',0),(897,'en_TT',0),(898,'en_US',1),(899,'en_ZA',0),(900,'en_ZW',0),(901,'es_AR',0),(902,'es_BO',0),(903,'es_CL',0),(904,'es_CO',0),(905,'es_CR',0),(906,'es_DO',0),(907,'es_EC',0),(908,'es_ES',0),(909,'es_GT',0),(910,'es_HN',0),(911,'es_MX',0),(912,'es_NI',0),(913,'es_PA',0),(914,'es_PE',0),(915,'es_PR',0),(916,'es_PY',0),(917,'es_SV',0),(918,'es_US',0),(919,'es_UY',0),(920,'es_VE',0),(921,'et_EE',0),(922,'eu_ES',0),(923,'fa_IR',0),(924,'fi_FI',0),(925,'fil_PH',0),(926,'fo_FO',0),(927,'fr_BE',0),(928,'fr_CA',0),(929,'fr_CH',0),(930,'fr_FR',1),(931,'fr_LU',0),(932,'fr_MC',0),(933,'fy_NL',0),(934,'ga_IE',0),(935,'gd_GB',0),(936,'gl_ES',0),(937,'gsw_FR',0),(938,'gu_IN',0),(939,'ha_Latn_NG',0),(940,'he_IL',0),(941,'hi_IN',0),(942,'hr_BA',0),(943,'hr_HR',0),(944,'hsb_DE',0),(945,'hu_HU',0),(946,'hy_AM',0),(947,'id_ID',0),(948,'ig_NG',0),(949,'ii_CN',0),(950,'is_IS',0),(951,'it_CH',0),(952,'it_IT',0),(953,'iu_Cans_CA',0),(954,'iu_Latn_CA',0),(955,'ja_JP',0),(956,'ka_GE',0),(957,'kk_KZ',0),(958,'kl_GL',0),(959,'km_KH',0),(960,'kn_IN',0),(961,'kok_IN',0),(962,'ko_KR',0),(963,'ky_KG',0),(964,'lb_LU',0),(965,'lo_LA',0),(966,'lt_LT',0),(967,'lv_LV',0),(968,'mi_NZ',0),(969,'mk_MK',0),(970,'ml_IN',0),(971,'mn_MN',0),(972,'mn_Mong_CN',0),(973,'moh_CA',0),(974,'mr_IN',0),(975,'ms_BN',0),(976,'ms_MY',0),(977,'mt_MT',0),(978,'nb_NO',0),(979,'ne_NP',0),(980,'nl_BE',0),(981,'nl_NL',0),(982,'nn_NO',0),(983,'nso_ZA',0),(984,'oc_FR',0),(985,'or_IN',0),(986,'pa_IN',0),(987,'pl_PL',0),(988,'prs_AF',0),(989,'ps_AF',0),(990,'pt_BR',0),(991,'pt_PT',0),(992,'qut_GT',0),(993,'quz_BO',0),(994,'quz_EC',0),(995,'quz_PE',0),(996,'rm_CH',0),(997,'ro_RO',0),(998,'ru_RU',0),(999,'rw_RW',0),(1000,'sah_RU',0),(1001,'sa_IN',0),(1002,'se_FI',0),(1003,'se_NO',0),(1004,'se_SE',0),(1005,'si_LK',0),(1006,'sk_SK',0),(1007,'sl_SI',0),(1008,'sma_NO',0),(1009,'sma_SE',0),(1010,'smj_NO',0),(1011,'smj_SE',0),(1012,'smn_FI',0),(1013,'sms_FI',0),(1014,'sq_AL',0),(1015,'sr_Cyrl_BA',0),(1016,'sr_Cyrl_CS',0),(1017,'sr_Cyrl_ME',0),(1018,'sr_Cyrl_RS',0),(1019,'sr_Latn_BA',0),(1020,'sr_Latn_CS',0),(1021,'sr_Latn_ME',0),(1022,'sr_Latn_RS',0),(1023,'sv_FI',0),(1024,'sv_SE',0),(1025,'sw_KE',0),(1026,'syr_SY',0),(1027,'ta_IN',0),(1028,'te_IN',0),(1029,'tg_Cyrl_TJ',0),(1030,'th_TH',0),(1031,'tk_TM',0),(1032,'tn_ZA',0),(1033,'tr_TR',0),(1034,'tt_RU',0),(1035,'tzm_Latn_DZ',0),(1036,'ug_CN',0),(1037,'uk_UA',0),(1038,'ur_PK',0),(1039,'uz_Cyrl_UZ',0),(1040,'uz_Latn_UZ',0),(1041,'vi_VN',0),(1042,'wo_SN',0),(1043,'xh_ZA',0),(1044,'yo_NG',0),(1045,'zh_CN',0),(1046,'zh_HK',0),(1047,'zh_MO',0),(1048,'zh_SG',0),(1049,'zh_TW',0),(1050,'zu_ZA',0);
/*!40000 ALTER TABLE `pim_catalog_locale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product`
--

DROP TABLE IF EXISTS `pim_catalog_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) DEFAULT NULL,
  `product_model_id` int(11) DEFAULT NULL,
  `family_variant_id` int(11) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `raw_values` json NOT NULL COMMENT '(DC2Type:native_json)',
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_91CD19C0772E836A` (`identifier`),
  KEY `IDX_91CD19C0C35E566A` (`family_id`),
  KEY `IDX_91CD19C0B2C5DD70` (`product_model_id`),
  KEY `IDX_91CD19C08A37AD0` (`family_variant_id`),
  CONSTRAINT `FK_91CD19C08A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (`id`),
  CONSTRAINT `FK_91CD19C0B2C5DD70` FOREIGN KEY (`product_model_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_91CD19C0C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product`
--

LOCK TABLES `pim_catalog_product` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_model`
--

DROP TABLE IF EXISTS `pim_catalog_product_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `family_variant_id` int(11) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `raw_values` json NOT NULL COMMENT '(DC2Type:native_json)',
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `root` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5943911E77153098` (`code`),
  KEY `IDX_5943911E727ACA70` (`parent_id`),
  KEY `IDX_5943911E8A37AD0` (`family_variant_id`),
  CONSTRAINT `FK_5943911E727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5943911E8A37AD0` FOREIGN KEY (`family_variant_id`) REFERENCES `pim_catalog_family_variant` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_model`
--

LOCK TABLES `pim_catalog_product_model` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_model` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_product_model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_model_association`
--

DROP TABLE IF EXISTS `pim_catalog_product_model_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_model_association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `association_type_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_foreign_key_idx` (`owner_id`,`association_type_id`),
  KEY `IDX_F5F4C8CAB1E1C39` (`association_type_id`),
  KEY `IDX_F5F4C8CA7E3C61F9` (`owner_id`),
  CONSTRAINT `FK_F5F4C8CA7E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `pim_catalog_product_model` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F5F4C8CAB1E1C39` FOREIGN KEY (`association_type_id`) REFERENCES `pim_catalog_association_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_model_association`
--

LOCK TABLES `pim_catalog_product_model_association` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_model_association` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_product_model_association` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_unique_data`
--

DROP TABLE IF EXISTS `pim_catalog_product_unique_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_unique_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `raw_data` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_value_idx` (`attribute_id`,`raw_data`),
  KEY `IDX_E0768BA34584665A` (`product_id`),
  KEY `IDX_E0768BA3B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_E0768BA34584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E0768BA3B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=485 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_unique_data`
--

LOCK TABLES `pim_catalog_product_unique_data` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_unique_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_product_unique_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_variant_attribute_set_has_attributes`
--

DROP TABLE IF EXISTS `pim_catalog_variant_attribute_set_has_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_variant_attribute_set_has_attributes` (
  `variant_attribute_set_id` int(11) NOT NULL,
  `attributes_id` int(11) NOT NULL,
  PRIMARY KEY (`variant_attribute_set_id`,`attributes_id`),
  KEY `IDX_E9C4264A11D06F0E` (`variant_attribute_set_id`),
  KEY `IDX_E9C4264ABAAF4009` (`attributes_id`),
  CONSTRAINT `FK_E9C4264A11D06F0E` FOREIGN KEY (`variant_attribute_set_id`) REFERENCES `pim_catalog_family_variant_attribute_set` (`id`),
  CONSTRAINT `FK_E9C4264ABAAF4009` FOREIGN KEY (`attributes_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_variant_attribute_set_has_attributes`
--

LOCK TABLES `pim_catalog_variant_attribute_set_has_attributes` WRITE;
/*!40000 ALTER TABLE `pim_catalog_variant_attribute_set_has_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_variant_attribute_set_has_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_variant_attribute_set_has_axes`
--

DROP TABLE IF EXISTS `pim_catalog_variant_attribute_set_has_axes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_variant_attribute_set_has_axes` (
  `variant_attribute_set_id` int(11) NOT NULL,
  `axes_id` int(11) NOT NULL,
  PRIMARY KEY (`variant_attribute_set_id`,`axes_id`),
  KEY `IDX_6965051E11D06F0E` (`variant_attribute_set_id`),
  KEY `IDX_6965051EE684FCEE` (`axes_id`),
  CONSTRAINT `FK_6965051E11D06F0E` FOREIGN KEY (`variant_attribute_set_id`) REFERENCES `pim_catalog_family_variant_attribute_set` (`id`),
  CONSTRAINT `FK_6965051EE684FCEE` FOREIGN KEY (`axes_id`) REFERENCES `pim_catalog_attribute` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_variant_attribute_set_has_axes`
--

LOCK TABLES `pim_catalog_variant_attribute_set_has_axes` WRITE;
/*!40000 ALTER TABLE `pim_catalog_variant_attribute_set_has_axes` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_variant_attribute_set_has_axes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_comment_comment`
--

DROP TABLE IF EXISTS `pim_comment_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_comment_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `resource_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `resource_id` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `replied_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY (`id`),
  KEY `IDX_2A32D03D727ACA70` (`parent_id`),
  KEY `IDX_2A32D03DF675F31B` (`author_id`),
  KEY `resource_name_resource_id_idx` (`resource_name`,`resource_id`),
  CONSTRAINT `FK_2A32D03D727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_comment_comment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2A32D03DF675F31B` FOREIGN KEY (`author_id`) REFERENCES `oro_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_comment_comment`
--

LOCK TABLES `pim_comment_comment` WRITE;
/*!40000 ALTER TABLE `pim_comment_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_comment_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_datagrid_view`
--

DROP TABLE IF EXISTS `pim_datagrid_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_datagrid_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datagrid_alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `columns` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `filters` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B56B38F1EA750E8` (`label`),
  KEY `IDX_B56B38F17E3C61F9` (`owner_id`),
  CONSTRAINT `FK_B56B38F17E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_datagrid_view`
--

LOCK TABLES `pim_datagrid_view` WRITE;
/*!40000 ALTER TABLE `pim_datagrid_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_datagrid_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_notification_notification`
--

DROP TABLE IF EXISTS `pim_notification_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_notification_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `routeParams` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `messageParams` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `context` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_notification_notification`
--

LOCK TABLES `pim_notification_notification` WRITE;
/*!40000 ALTER TABLE `pim_notification_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_notification_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_notification_user_notification`
--

DROP TABLE IF EXISTS `pim_notification_user_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_notification_user_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `viewed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_342AA855BF5476CA` (`notification`),
  KEY `IDX_342AA8558D93D649` (`user`),
  CONSTRAINT `FK_342AA8558D93D649` FOREIGN KEY (`user`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_342AA855BF5476CA` FOREIGN KEY (`notification`) REFERENCES `pim_notification_notification` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_notification_user_notification`
--

LOCK TABLES `pim_notification_user_notification` WRITE;
/*!40000 ALTER TABLE `pim_notification_user_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_notification_user_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_session`
--

DROP TABLE IF EXISTS `pim_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_session` (
  `sess_id` varbinary(128) NOT NULL,
  `sess_data` blob NOT NULL,
  `sess_time` int(10) unsigned NOT NULL,
  `sess_lifetime` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_session`
--

LOCK TABLES `pim_session` WRITE;
/*!40000 ALTER TABLE `pim_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_user_default_datagrid_view`
--

DROP TABLE IF EXISTS `pim_user_default_datagrid_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_user_default_datagrid_view` (
  `user_id` int(11) NOT NULL,
  `view_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`view_id`),
  KEY `IDX_3CEEC2F2A76ED395` (`user_id`),
  KEY `IDX_3CEEC2F231518C7` (`view_id`),
  CONSTRAINT `FK_3CEEC2F231518C7` FOREIGN KEY (`view_id`) REFERENCES `pim_datagrid_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3CEEC2F2A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_user_default_datagrid_view`
--

LOCK TABLES `pim_user_default_datagrid_view` WRITE;
/*!40000 ALTER TABLE `pim_user_default_datagrid_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_user_default_datagrid_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_versioning_version`
--

DROP TABLE IF EXISTS `pim_versioning_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_versioning_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `resource_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `resource_id` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `snapshot` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `changeset` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `context` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `logged_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
  `pending` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_name_resource_id_idx` (`resource_name`,`resource_id`),
  KEY `resource_name_idx` (`resource_name`),
  KEY `pending_idx` (`pending`),
  KEY `version_idx` (`version`),
  KEY `logged_at_idx` (`logged_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1897 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_versioning_version`
--

LOCK TABLES `pim_versioning_version` WRITE;
/*!40000 ALTER TABLE `pim_versioning_version` DISABLE KEYS */;
INSERT INTO `pim_versioning_version` VALUES (1544,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','215','a:5:{s:4:\"code\";s:22:\"fixtures_currency_csv0\";s:5:\"label\";s:24:\"Currencies data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_currency_csv0\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Currencies data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1545,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','216','a:5:{s:4:\"code\";s:20:\"fixtures_locale_csv1\";s:5:\"label\";s:21:\"Locales data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:298:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_locale_csv1\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Locales data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:298:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1546,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','217','a:5:{s:4:\"code\";s:24:\"fixtures_user_group_csv2\";s:5:\"label\";s:25:\"User groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_group_csv2\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"User groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1547,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','218','a:5:{s:4:\"code\";s:22:\"fixtures_category_csv3\";s:5:\"label\";s:24:\"Categories data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_category_csv3\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Categories data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1548,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','219','a:5:{s:4:\"code\";s:29:\"fixtures_attribute_group_csv4\";s:5:\"label\";s:30:\"Attribute groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:307:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_attribute_group_csv4\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"Attribute groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:307:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1549,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','220','a:5:{s:4:\"code\";s:24:\"fixtures_group_type_csv5\";s:5:\"label\";s:25:\"Group types data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_group_type_csv5\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Group types data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1550,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','221','a:5:{s:4:\"code\";s:23:\"fixtures_attribute_csv6\";s:5:\"label\";s:24:\"Attributes data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"fixtures_attribute_csv6\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Attributes data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1551,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','222','a:5:{s:4:\"code\";s:21:\"fixtures_channel_csv7\";s:5:\"label\";s:22:\"Channels data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_channel_csv7\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Channels data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1552,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','223','a:5:{s:4:\"code\";s:30:\"fixtures_association_type_csv8\";s:5:\"label\";s:26:\"Associations data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"fixtures_association_type_csv8\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Associations data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1553,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','224','a:5:{s:4:\"code\";s:31:\"fixtures_attribute_options_csv9\";s:5:\"label\";s:31:\"Attribute options data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"fixtures_attribute_options_csv9\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"Attribute options data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1554,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','225','a:5:{s:4:\"code\";s:21:\"fixtures_family_csv10\";s:5:\"label\";s:22:\"Families data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_family_csv10\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Families data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1555,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','226','a:5:{s:4:\"code\";s:18:\"fixtures_job_yml11\";s:5:\"label\";s:18:\"Jobs data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:231:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"fixtures_job_yml11\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Jobs data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:231:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1556,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','227','a:5:{s:4:\"code\";s:29:\"fixtures_family_variant_csv12\";s:5:\"label\";s:29:\"Family variants data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:306:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_family_variant_csv12\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Family variants data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:306:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1557,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','228','a:5:{s:4:\"code\";s:20:\"fixtures_group_csv13\";s:5:\"label\";s:20:\"Groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:297:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_group_csv13\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:297:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1558,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','229','a:5:{s:4:\"code\";s:24:\"fixtures_user_role_csv14\";s:5:\"label\";s:24:\"User roles data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_role_csv14\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"User roles data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1559,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','230','a:5:{s:4:\"code\";s:19:\"fixtures_user_csv15\";s:5:\"label\";s:19:\"Users data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:296:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"fixtures_user_csv15\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Users data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:296:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1560,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','231','a:5:{s:4:\"code\";s:28:\"fixtures_product_model_csv16\";s:5:\"label\";s:27:\"Product model data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:491:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"fixtures_product_model_csv16\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Product model data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:491:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1561,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','232','a:5:{s:4:\"code\";s:22:\"fixtures_product_csv17\";s:5:\"label\";s:21:\"Product data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:494:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_product_csv17\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Product data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:494:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:13',0),(1562,'system','Akeneo\\Channel\\Component\\Model\\Locale','841','a:1:{s:4:\"code\";s:5:\"af_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"af_ZA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1563,'system','Akeneo\\Channel\\Component\\Model\\Locale','842','a:1:{s:4:\"code\";s:5:\"am_ET\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"am_ET\";}}',NULL,1,'2018-05-30 13:22:14',0),(1564,'system','Akeneo\\Channel\\Component\\Model\\Locale','843','a:1:{s:4:\"code\";s:5:\"ar_AE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_AE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1565,'system','Akeneo\\Channel\\Component\\Model\\Locale','844','a:1:{s:4:\"code\";s:5:\"ar_BH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_BH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1566,'system','Akeneo\\Channel\\Component\\Model\\Locale','845','a:1:{s:4:\"code\";s:5:\"ar_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_DZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1567,'system','Akeneo\\Channel\\Component\\Model\\Locale','846','a:1:{s:4:\"code\";s:5:\"ar_EG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1568,'system','Akeneo\\Channel\\Component\\Model\\Locale','847','a:1:{s:4:\"code\";s:5:\"ar_IQ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_IQ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1569,'system','Akeneo\\Channel\\Component\\Model\\Locale','848','a:1:{s:4:\"code\";s:5:\"ar_JO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_JO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1570,'system','Akeneo\\Channel\\Component\\Model\\Locale','849','a:1:{s:4:\"code\";s:5:\"ar_KW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_KW\";}}',NULL,1,'2018-05-30 13:22:14',0),(1571,'system','Akeneo\\Channel\\Component\\Model\\Locale','850','a:1:{s:4:\"code\";s:5:\"ar_LB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LB\";}}',NULL,1,'2018-05-30 13:22:14',0),(1572,'system','Akeneo\\Channel\\Component\\Model\\Locale','851','a:1:{s:4:\"code\";s:5:\"ar_LY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1573,'system','Akeneo\\Channel\\Component\\Model\\Locale','852','a:1:{s:4:\"code\";s:5:\"ar_MA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_MA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1574,'system','Akeneo\\Channel\\Component\\Model\\Locale','853','a:1:{s:4:\"code\";s:6:\"arn_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"arn_CL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1575,'system','Akeneo\\Channel\\Component\\Model\\Locale','854','a:1:{s:4:\"code\";s:5:\"ar_OM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_OM\";}}',NULL,1,'2018-05-30 13:22:14',0),(1576,'system','Akeneo\\Channel\\Component\\Model\\Locale','855','a:1:{s:4:\"code\";s:5:\"ar_QA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_QA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1577,'system','Akeneo\\Channel\\Component\\Model\\Locale','856','a:1:{s:4:\"code\";s:5:\"ar_SA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1578,'system','Akeneo\\Channel\\Component\\Model\\Locale','857','a:1:{s:4:\"code\";s:5:\"ar_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1579,'system','Akeneo\\Channel\\Component\\Model\\Locale','858','a:1:{s:4:\"code\";s:5:\"ar_TN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_TN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1580,'system','Akeneo\\Channel\\Component\\Model\\Locale','859','a:1:{s:4:\"code\";s:5:\"ar_YE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_YE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1581,'system','Akeneo\\Channel\\Component\\Model\\Locale','860','a:1:{s:4:\"code\";s:5:\"as_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"as_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1582,'system','Akeneo\\Channel\\Component\\Model\\Locale','861','a:1:{s:4:\"code\";s:10:\"az_Cyrl_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Cyrl_AZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1583,'system','Akeneo\\Channel\\Component\\Model\\Locale','862','a:1:{s:4:\"code\";s:10:\"az_Latn_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Latn_AZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1584,'system','Akeneo\\Channel\\Component\\Model\\Locale','863','a:1:{s:4:\"code\";s:5:\"ba_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ba_RU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1585,'system','Akeneo\\Channel\\Component\\Model\\Locale','864','a:1:{s:4:\"code\";s:5:\"be_BY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"be_BY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1586,'system','Akeneo\\Channel\\Component\\Model\\Locale','865','a:1:{s:4:\"code\";s:5:\"bg_BG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bg_BG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1587,'system','Akeneo\\Channel\\Component\\Model\\Locale','866','a:1:{s:4:\"code\";s:5:\"bn_BD\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_BD\";}}',NULL,1,'2018-05-30 13:22:14',0),(1588,'system','Akeneo\\Channel\\Component\\Model\\Locale','867','a:1:{s:4:\"code\";s:5:\"bn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1589,'system','Akeneo\\Channel\\Component\\Model\\Locale','868','a:1:{s:4:\"code\";s:5:\"bo_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bo_CN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1590,'system','Akeneo\\Channel\\Component\\Model\\Locale','869','a:1:{s:4:\"code\";s:5:\"br_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"br_FR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1591,'system','Akeneo\\Channel\\Component\\Model\\Locale','870','a:1:{s:4:\"code\";s:10:\"bs_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Cyrl_BA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1592,'system','Akeneo\\Channel\\Component\\Model\\Locale','871','a:1:{s:4:\"code\";s:10:\"bs_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Latn_BA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1593,'system','Akeneo\\Channel\\Component\\Model\\Locale','872','a:1:{s:4:\"code\";s:5:\"ca_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ca_ES\";}}',NULL,1,'2018-05-30 13:22:14',0),(1594,'system','Akeneo\\Channel\\Component\\Model\\Locale','873','a:1:{s:4:\"code\";s:5:\"co_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"co_FR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1595,'system','Akeneo\\Channel\\Component\\Model\\Locale','874','a:1:{s:4:\"code\";s:5:\"cs_CZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cs_CZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1596,'system','Akeneo\\Channel\\Component\\Model\\Locale','875','a:1:{s:4:\"code\";s:5:\"cy_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cy_GB\";}}',NULL,1,'2018-05-30 13:22:14',0),(1597,'system','Akeneo\\Channel\\Component\\Model\\Locale','876','a:1:{s:4:\"code\";s:5:\"da_DK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"da_DK\";}}',NULL,1,'2018-05-30 13:22:14',0),(1598,'system','Akeneo\\Channel\\Component\\Model\\Locale','877','a:1:{s:4:\"code\";s:5:\"de_AT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_AT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1599,'system','Akeneo\\Channel\\Component\\Model\\Locale','878','a:1:{s:4:\"code\";s:5:\"de_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_CH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1600,'system','Akeneo\\Channel\\Component\\Model\\Locale','879','a:1:{s:4:\"code\";s:5:\"de_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1601,'system','Akeneo\\Channel\\Component\\Model\\Locale','880','a:1:{s:4:\"code\";s:5:\"de_LI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1602,'system','Akeneo\\Channel\\Component\\Model\\Locale','881','a:1:{s:4:\"code\";s:5:\"de_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1603,'system','Akeneo\\Channel\\Component\\Model\\Locale','882','a:1:{s:4:\"code\";s:6:\"dsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"dsb_DE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1604,'system','Akeneo\\Channel\\Component\\Model\\Locale','883','a:1:{s:4:\"code\";s:5:\"dv_MV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"dv_MV\";}}',NULL,1,'2018-05-30 13:22:14',0),(1605,'system','Akeneo\\Channel\\Component\\Model\\Locale','884','a:1:{s:4:\"code\";s:5:\"el_GR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"el_GR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1606,'system','Akeneo\\Channel\\Component\\Model\\Locale','885','a:1:{s:4:\"code\";s:6:\"en_029\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"en_029\";}}',NULL,1,'2018-05-30 13:22:14',0),(1607,'system','Akeneo\\Channel\\Component\\Model\\Locale','886','a:1:{s:4:\"code\";s:5:\"en_AU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_AU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1608,'system','Akeneo\\Channel\\Component\\Model\\Locale','887','a:1:{s:4:\"code\";s:5:\"en_BZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_BZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1609,'system','Akeneo\\Channel\\Component\\Model\\Locale','888','a:1:{s:4:\"code\";s:5:\"en_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_CA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1610,'system','Akeneo\\Channel\\Component\\Model\\Locale','889','a:1:{s:4:\"code\";s:5:\"en_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_GB\";}}',NULL,1,'2018-05-30 13:22:14',0),(1611,'system','Akeneo\\Channel\\Component\\Model\\Locale','890','a:1:{s:4:\"code\";s:5:\"en_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1612,'system','Akeneo\\Channel\\Component\\Model\\Locale','891','a:1:{s:4:\"code\";s:5:\"en_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1613,'system','Akeneo\\Channel\\Component\\Model\\Locale','892','a:1:{s:4:\"code\";s:5:\"en_JM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_JM\";}}',NULL,1,'2018-05-30 13:22:14',0),(1614,'system','Akeneo\\Channel\\Component\\Model\\Locale','893','a:1:{s:4:\"code\";s:5:\"en_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_MY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1615,'system','Akeneo\\Channel\\Component\\Model\\Locale','894','a:1:{s:4:\"code\";s:5:\"en_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_NZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1616,'system','Akeneo\\Channel\\Component\\Model\\Locale','895','a:1:{s:4:\"code\";s:5:\"en_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_PH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1617,'system','Akeneo\\Channel\\Component\\Model\\Locale','896','a:1:{s:4:\"code\";s:5:\"en_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_SG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1618,'system','Akeneo\\Channel\\Component\\Model\\Locale','897','a:1:{s:4:\"code\";s:5:\"en_TT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_TT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1619,'system','Akeneo\\Channel\\Component\\Model\\Locale','898','a:1:{s:4:\"code\";s:5:\"en_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}}',NULL,1,'2018-05-30 13:22:14',0),(1620,'system','Akeneo\\Channel\\Component\\Model\\Locale','899','a:1:{s:4:\"code\";s:5:\"en_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1621,'system','Akeneo\\Channel\\Component\\Model\\Locale','900','a:1:{s:4:\"code\";s:5:\"en_ZW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZW\";}}',NULL,1,'2018-05-30 13:22:14',0),(1622,'system','Akeneo\\Channel\\Component\\Model\\Locale','901','a:1:{s:4:\"code\";s:5:\"es_AR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_AR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1623,'system','Akeneo\\Channel\\Component\\Model\\Locale','902','a:1:{s:4:\"code\";s:5:\"es_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_BO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1624,'system','Akeneo\\Channel\\Component\\Model\\Locale','903','a:1:{s:4:\"code\";s:5:\"es_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1625,'system','Akeneo\\Channel\\Component\\Model\\Locale','904','a:1:{s:4:\"code\";s:5:\"es_CO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1626,'system','Akeneo\\Channel\\Component\\Model\\Locale','905','a:1:{s:4:\"code\";s:5:\"es_CR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1627,'system','Akeneo\\Channel\\Component\\Model\\Locale','906','a:1:{s:4:\"code\";s:5:\"es_DO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_DO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1628,'system','Akeneo\\Channel\\Component\\Model\\Locale','907','a:1:{s:4:\"code\";s:5:\"es_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_EC\";}}',NULL,1,'2018-05-30 13:22:14',0),(1629,'system','Akeneo\\Channel\\Component\\Model\\Locale','908','a:1:{s:4:\"code\";s:5:\"es_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_ES\";}}',NULL,1,'2018-05-30 13:22:14',0),(1630,'system','Akeneo\\Channel\\Component\\Model\\Locale','909','a:1:{s:4:\"code\";s:5:\"es_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_GT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1631,'system','Akeneo\\Channel\\Component\\Model\\Locale','910','a:1:{s:4:\"code\";s:5:\"es_HN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_HN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1632,'system','Akeneo\\Channel\\Component\\Model\\Locale','911','a:1:{s:4:\"code\";s:5:\"es_MX\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_MX\";}}',NULL,1,'2018-05-30 13:22:14',0),(1633,'system','Akeneo\\Channel\\Component\\Model\\Locale','912','a:1:{s:4:\"code\";s:5:\"es_NI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_NI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1634,'system','Akeneo\\Channel\\Component\\Model\\Locale','913','a:1:{s:4:\"code\";s:5:\"es_PA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1635,'system','Akeneo\\Channel\\Component\\Model\\Locale','914','a:1:{s:4:\"code\";s:5:\"es_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1636,'system','Akeneo\\Channel\\Component\\Model\\Locale','915','a:1:{s:4:\"code\";s:5:\"es_PR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1637,'system','Akeneo\\Channel\\Component\\Model\\Locale','916','a:1:{s:4:\"code\";s:5:\"es_PY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1638,'system','Akeneo\\Channel\\Component\\Model\\Locale','917','a:1:{s:4:\"code\";s:5:\"es_SV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_SV\";}}',NULL,1,'2018-05-30 13:22:14',0),(1639,'system','Akeneo\\Channel\\Component\\Model\\Locale','918','a:1:{s:4:\"code\";s:5:\"es_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_US\";}}',NULL,1,'2018-05-30 13:22:14',0),(1640,'system','Akeneo\\Channel\\Component\\Model\\Locale','919','a:1:{s:4:\"code\";s:5:\"es_UY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_UY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1641,'system','Akeneo\\Channel\\Component\\Model\\Locale','920','a:1:{s:4:\"code\";s:5:\"es_VE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_VE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1642,'system','Akeneo\\Channel\\Component\\Model\\Locale','921','a:1:{s:4:\"code\";s:5:\"et_EE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"et_EE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1643,'system','Akeneo\\Channel\\Component\\Model\\Locale','922','a:1:{s:4:\"code\";s:5:\"eu_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"eu_ES\";}}',NULL,1,'2018-05-30 13:22:14',0),(1644,'system','Akeneo\\Channel\\Component\\Model\\Locale','923','a:1:{s:4:\"code\";s:5:\"fa_IR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fa_IR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1645,'system','Akeneo\\Channel\\Component\\Model\\Locale','924','a:1:{s:4:\"code\";s:5:\"fi_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fi_FI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1646,'system','Akeneo\\Channel\\Component\\Model\\Locale','925','a:1:{s:4:\"code\";s:6:\"fil_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"fil_PH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1647,'system','Akeneo\\Channel\\Component\\Model\\Locale','926','a:1:{s:4:\"code\";s:5:\"fo_FO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fo_FO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1648,'system','Akeneo\\Channel\\Component\\Model\\Locale','927','a:1:{s:4:\"code\";s:5:\"fr_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_BE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1649,'system','Akeneo\\Channel\\Component\\Model\\Locale','928','a:1:{s:4:\"code\";s:5:\"fr_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1650,'system','Akeneo\\Channel\\Component\\Model\\Locale','929','a:1:{s:4:\"code\";s:5:\"fr_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1651,'system','Akeneo\\Channel\\Component\\Model\\Locale','930','a:1:{s:4:\"code\";s:5:\"fr_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1652,'system','Akeneo\\Channel\\Component\\Model\\Locale','931','a:1:{s:4:\"code\";s:5:\"fr_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_LU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1653,'system','Akeneo\\Channel\\Component\\Model\\Locale','932','a:1:{s:4:\"code\";s:5:\"fr_MC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_MC\";}}',NULL,1,'2018-05-30 13:22:14',0),(1654,'system','Akeneo\\Channel\\Component\\Model\\Locale','933','a:1:{s:4:\"code\";s:5:\"fy_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fy_NL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1655,'system','Akeneo\\Channel\\Component\\Model\\Locale','934','a:1:{s:4:\"code\";s:5:\"ga_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ga_IE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1656,'system','Akeneo\\Channel\\Component\\Model\\Locale','935','a:1:{s:4:\"code\";s:5:\"gd_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gd_GB\";}}',NULL,1,'2018-05-30 13:22:14',0),(1657,'system','Akeneo\\Channel\\Component\\Model\\Locale','936','a:1:{s:4:\"code\";s:5:\"gl_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gl_ES\";}}',NULL,1,'2018-05-30 13:22:14',0),(1658,'system','Akeneo\\Channel\\Component\\Model\\Locale','937','a:1:{s:4:\"code\";s:6:\"gsw_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"gsw_FR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1659,'system','Akeneo\\Channel\\Component\\Model\\Locale','938','a:1:{s:4:\"code\";s:5:\"gu_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gu_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1660,'system','Akeneo\\Channel\\Component\\Model\\Locale','939','a:1:{s:4:\"code\";s:10:\"ha_Latn_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"ha_Latn_NG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1661,'system','Akeneo\\Channel\\Component\\Model\\Locale','940','a:1:{s:4:\"code\";s:5:\"he_IL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"he_IL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1662,'system','Akeneo\\Channel\\Component\\Model\\Locale','941','a:1:{s:4:\"code\";s:5:\"hi_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hi_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1663,'system','Akeneo\\Channel\\Component\\Model\\Locale','942','a:1:{s:4:\"code\";s:5:\"hr_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_BA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1664,'system','Akeneo\\Channel\\Component\\Model\\Locale','943','a:1:{s:4:\"code\";s:5:\"hr_HR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_HR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1665,'system','Akeneo\\Channel\\Component\\Model\\Locale','944','a:1:{s:4:\"code\";s:6:\"hsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"hsb_DE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1666,'system','Akeneo\\Channel\\Component\\Model\\Locale','945','a:1:{s:4:\"code\";s:5:\"hu_HU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hu_HU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1667,'system','Akeneo\\Channel\\Component\\Model\\Locale','946','a:1:{s:4:\"code\";s:5:\"hy_AM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hy_AM\";}}',NULL,1,'2018-05-30 13:22:14',0),(1668,'system','Akeneo\\Channel\\Component\\Model\\Locale','947','a:1:{s:4:\"code\";s:5:\"id_ID\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"id_ID\";}}',NULL,1,'2018-05-30 13:22:14',0),(1669,'system','Akeneo\\Channel\\Component\\Model\\Locale','948','a:1:{s:4:\"code\";s:5:\"ig_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ig_NG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1670,'system','Akeneo\\Channel\\Component\\Model\\Locale','949','a:1:{s:4:\"code\";s:5:\"ii_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ii_CN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1671,'system','Akeneo\\Channel\\Component\\Model\\Locale','950','a:1:{s:4:\"code\";s:5:\"is_IS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"is_IS\";}}',NULL,1,'2018-05-30 13:22:14',0),(1672,'system','Akeneo\\Channel\\Component\\Model\\Locale','951','a:1:{s:4:\"code\";s:5:\"it_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_CH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1673,'system','Akeneo\\Channel\\Component\\Model\\Locale','952','a:1:{s:4:\"code\";s:5:\"it_IT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_IT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1674,'system','Akeneo\\Channel\\Component\\Model\\Locale','953','a:1:{s:4:\"code\";s:10:\"iu_Cans_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Cans_CA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1675,'system','Akeneo\\Channel\\Component\\Model\\Locale','954','a:1:{s:4:\"code\";s:10:\"iu_Latn_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Latn_CA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1676,'system','Akeneo\\Channel\\Component\\Model\\Locale','955','a:1:{s:4:\"code\";s:5:\"ja_JP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ja_JP\";}}',NULL,1,'2018-05-30 13:22:14',0),(1677,'system','Akeneo\\Channel\\Component\\Model\\Locale','956','a:1:{s:4:\"code\";s:5:\"ka_GE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ka_GE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1678,'system','Akeneo\\Channel\\Component\\Model\\Locale','957','a:1:{s:4:\"code\";s:5:\"kk_KZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kk_KZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1679,'system','Akeneo\\Channel\\Component\\Model\\Locale','958','a:1:{s:4:\"code\";s:5:\"kl_GL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kl_GL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1680,'system','Akeneo\\Channel\\Component\\Model\\Locale','959','a:1:{s:4:\"code\";s:5:\"km_KH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"km_KH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1681,'system','Akeneo\\Channel\\Component\\Model\\Locale','960','a:1:{s:4:\"code\";s:5:\"kn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kn_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1682,'system','Akeneo\\Channel\\Component\\Model\\Locale','961','a:1:{s:4:\"code\";s:6:\"kok_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"kok_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1683,'system','Akeneo\\Channel\\Component\\Model\\Locale','962','a:1:{s:4:\"code\";s:5:\"ko_KR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ko_KR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1684,'system','Akeneo\\Channel\\Component\\Model\\Locale','963','a:1:{s:4:\"code\";s:5:\"ky_KG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ky_KG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1685,'system','Akeneo\\Channel\\Component\\Model\\Locale','964','a:1:{s:4:\"code\";s:5:\"lb_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lb_LU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1686,'system','Akeneo\\Channel\\Component\\Model\\Locale','965','a:1:{s:4:\"code\";s:5:\"lo_LA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lo_LA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1687,'system','Akeneo\\Channel\\Component\\Model\\Locale','966','a:1:{s:4:\"code\";s:5:\"lt_LT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lt_LT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1688,'system','Akeneo\\Channel\\Component\\Model\\Locale','967','a:1:{s:4:\"code\";s:5:\"lv_LV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lv_LV\";}}',NULL,1,'2018-05-30 13:22:14',0),(1689,'system','Akeneo\\Channel\\Component\\Model\\Locale','968','a:1:{s:4:\"code\";s:5:\"mi_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mi_NZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1690,'system','Akeneo\\Channel\\Component\\Model\\Locale','969','a:1:{s:4:\"code\";s:5:\"mk_MK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mk_MK\";}}',NULL,1,'2018-05-30 13:22:14',0),(1691,'system','Akeneo\\Channel\\Component\\Model\\Locale','970','a:1:{s:4:\"code\";s:5:\"ml_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ml_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1692,'system','Akeneo\\Channel\\Component\\Model\\Locale','971','a:1:{s:4:\"code\";s:5:\"mn_MN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mn_MN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1693,'system','Akeneo\\Channel\\Component\\Model\\Locale','972','a:1:{s:4:\"code\";s:10:\"mn_Mong_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"mn_Mong_CN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1694,'system','Akeneo\\Channel\\Component\\Model\\Locale','973','a:1:{s:4:\"code\";s:6:\"moh_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"moh_CA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1695,'system','Akeneo\\Channel\\Component\\Model\\Locale','974','a:1:{s:4:\"code\";s:5:\"mr_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mr_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1696,'system','Akeneo\\Channel\\Component\\Model\\Locale','975','a:1:{s:4:\"code\";s:5:\"ms_BN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_BN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1697,'system','Akeneo\\Channel\\Component\\Model\\Locale','976','a:1:{s:4:\"code\";s:5:\"ms_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_MY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1698,'system','Akeneo\\Channel\\Component\\Model\\Locale','977','a:1:{s:4:\"code\";s:5:\"mt_MT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mt_MT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1699,'system','Akeneo\\Channel\\Component\\Model\\Locale','978','a:1:{s:4:\"code\";s:5:\"nb_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nb_NO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1700,'system','Akeneo\\Channel\\Component\\Model\\Locale','979','a:1:{s:4:\"code\";s:5:\"ne_NP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ne_NP\";}}',NULL,1,'2018-05-30 13:22:14',0),(1701,'system','Akeneo\\Channel\\Component\\Model\\Locale','980','a:1:{s:4:\"code\";s:5:\"nl_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_BE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1702,'system','Akeneo\\Channel\\Component\\Model\\Locale','981','a:1:{s:4:\"code\";s:5:\"nl_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_NL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1703,'system','Akeneo\\Channel\\Component\\Model\\Locale','982','a:1:{s:4:\"code\";s:5:\"nn_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nn_NO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1704,'system','Akeneo\\Channel\\Component\\Model\\Locale','983','a:1:{s:4:\"code\";s:6:\"nso_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"nso_ZA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1705,'system','Akeneo\\Channel\\Component\\Model\\Locale','984','a:1:{s:4:\"code\";s:5:\"oc_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"oc_FR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1706,'system','Akeneo\\Channel\\Component\\Model\\Locale','985','a:1:{s:4:\"code\";s:5:\"or_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"or_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1707,'system','Akeneo\\Channel\\Component\\Model\\Locale','986','a:1:{s:4:\"code\";s:5:\"pa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pa_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1708,'system','Akeneo\\Channel\\Component\\Model\\Locale','987','a:1:{s:4:\"code\";s:5:\"pl_PL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pl_PL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1709,'system','Akeneo\\Channel\\Component\\Model\\Locale','988','a:1:{s:4:\"code\";s:6:\"prs_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"prs_AF\";}}',NULL,1,'2018-05-30 13:22:14',0),(1710,'system','Akeneo\\Channel\\Component\\Model\\Locale','989','a:1:{s:4:\"code\";s:5:\"ps_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ps_AF\";}}',NULL,1,'2018-05-30 13:22:14',0),(1711,'system','Akeneo\\Channel\\Component\\Model\\Locale','990','a:1:{s:4:\"code\";s:5:\"pt_BR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_BR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1712,'system','Akeneo\\Channel\\Component\\Model\\Locale','991','a:1:{s:4:\"code\";s:5:\"pt_PT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_PT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1713,'system','Akeneo\\Channel\\Component\\Model\\Locale','992','a:1:{s:4:\"code\";s:6:\"qut_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"qut_GT\";}}',NULL,1,'2018-05-30 13:22:14',0),(1714,'system','Akeneo\\Channel\\Component\\Model\\Locale','993','a:1:{s:4:\"code\";s:6:\"quz_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_BO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1715,'system','Akeneo\\Channel\\Component\\Model\\Locale','994','a:1:{s:4:\"code\";s:6:\"quz_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_EC\";}}',NULL,1,'2018-05-30 13:22:14',0),(1716,'system','Akeneo\\Channel\\Component\\Model\\Locale','995','a:1:{s:4:\"code\";s:6:\"quz_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_PE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1717,'system','Akeneo\\Channel\\Component\\Model\\Locale','996','a:1:{s:4:\"code\";s:5:\"rm_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rm_CH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1718,'system','Akeneo\\Channel\\Component\\Model\\Locale','997','a:1:{s:4:\"code\";s:5:\"ro_RO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ro_RO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1719,'system','Akeneo\\Channel\\Component\\Model\\Locale','998','a:1:{s:4:\"code\";s:5:\"ru_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ru_RU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1720,'system','Akeneo\\Channel\\Component\\Model\\Locale','999','a:1:{s:4:\"code\";s:5:\"rw_RW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rw_RW\";}}',NULL,1,'2018-05-30 13:22:14',0),(1721,'system','Akeneo\\Channel\\Component\\Model\\Locale','1000','a:1:{s:4:\"code\";s:6:\"sah_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sah_RU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1722,'system','Akeneo\\Channel\\Component\\Model\\Locale','1001','a:1:{s:4:\"code\";s:5:\"sa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sa_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1723,'system','Akeneo\\Channel\\Component\\Model\\Locale','1002','a:1:{s:4:\"code\";s:5:\"se_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_FI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1724,'system','Akeneo\\Channel\\Component\\Model\\Locale','1003','a:1:{s:4:\"code\";s:5:\"se_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_NO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1725,'system','Akeneo\\Channel\\Component\\Model\\Locale','1004','a:1:{s:4:\"code\";s:5:\"se_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_SE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1726,'system','Akeneo\\Channel\\Component\\Model\\Locale','1005','a:1:{s:4:\"code\";s:5:\"si_LK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"si_LK\";}}',NULL,1,'2018-05-30 13:22:14',0),(1727,'system','Akeneo\\Channel\\Component\\Model\\Locale','1006','a:1:{s:4:\"code\";s:5:\"sk_SK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sk_SK\";}}',NULL,1,'2018-05-30 13:22:14',0),(1728,'system','Akeneo\\Channel\\Component\\Model\\Locale','1007','a:1:{s:4:\"code\";s:5:\"sl_SI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sl_SI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1729,'system','Akeneo\\Channel\\Component\\Model\\Locale','1008','a:1:{s:4:\"code\";s:6:\"sma_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_NO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1730,'system','Akeneo\\Channel\\Component\\Model\\Locale','1009','a:1:{s:4:\"code\";s:6:\"sma_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_SE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1731,'system','Akeneo\\Channel\\Component\\Model\\Locale','1010','a:1:{s:4:\"code\";s:6:\"smj_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_NO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1732,'system','Akeneo\\Channel\\Component\\Model\\Locale','1011','a:1:{s:4:\"code\";s:6:\"smj_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_SE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1733,'system','Akeneo\\Channel\\Component\\Model\\Locale','1012','a:1:{s:4:\"code\";s:6:\"smn_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smn_FI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1734,'system','Akeneo\\Channel\\Component\\Model\\Locale','1013','a:1:{s:4:\"code\";s:6:\"sms_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sms_FI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1735,'system','Akeneo\\Channel\\Component\\Model\\Locale','1014','a:1:{s:4:\"code\";s:5:\"sq_AL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sq_AL\";}}',NULL,1,'2018-05-30 13:22:14',0),(1736,'system','Akeneo\\Channel\\Component\\Model\\Locale','1015','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_BA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1737,'system','Akeneo\\Channel\\Component\\Model\\Locale','1016','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_CS\";}}',NULL,1,'2018-05-30 13:22:14',0),(1738,'system','Akeneo\\Channel\\Component\\Model\\Locale','1017','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_ME\";}}',NULL,1,'2018-05-30 13:22:14',0),(1739,'system','Akeneo\\Channel\\Component\\Model\\Locale','1018','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_RS\";}}',NULL,1,'2018-05-30 13:22:14',0),(1740,'system','Akeneo\\Channel\\Component\\Model\\Locale','1019','a:1:{s:4:\"code\";s:10:\"sr_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_BA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1741,'system','Akeneo\\Channel\\Component\\Model\\Locale','1020','a:1:{s:4:\"code\";s:10:\"sr_Latn_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_CS\";}}',NULL,1,'2018-05-30 13:22:14',0),(1742,'system','Akeneo\\Channel\\Component\\Model\\Locale','1021','a:1:{s:4:\"code\";s:10:\"sr_Latn_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_ME\";}}',NULL,1,'2018-05-30 13:22:14',0),(1743,'system','Akeneo\\Channel\\Component\\Model\\Locale','1022','a:1:{s:4:\"code\";s:10:\"sr_Latn_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_RS\";}}',NULL,1,'2018-05-30 13:22:14',0),(1744,'system','Akeneo\\Channel\\Component\\Model\\Locale','1023','a:1:{s:4:\"code\";s:5:\"sv_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_FI\";}}',NULL,1,'2018-05-30 13:22:14',0),(1745,'system','Akeneo\\Channel\\Component\\Model\\Locale','1024','a:1:{s:4:\"code\";s:5:\"sv_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_SE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1746,'system','Akeneo\\Channel\\Component\\Model\\Locale','1025','a:1:{s:4:\"code\";s:5:\"sw_KE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sw_KE\";}}',NULL,1,'2018-05-30 13:22:14',0),(1747,'system','Akeneo\\Channel\\Component\\Model\\Locale','1026','a:1:{s:4:\"code\";s:6:\"syr_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"syr_SY\";}}',NULL,1,'2018-05-30 13:22:14',0),(1748,'system','Akeneo\\Channel\\Component\\Model\\Locale','1027','a:1:{s:4:\"code\";s:5:\"ta_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ta_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1749,'system','Akeneo\\Channel\\Component\\Model\\Locale','1028','a:1:{s:4:\"code\";s:5:\"te_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"te_IN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1750,'system','Akeneo\\Channel\\Component\\Model\\Locale','1029','a:1:{s:4:\"code\";s:10:\"tg_Cyrl_TJ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"tg_Cyrl_TJ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1751,'system','Akeneo\\Channel\\Component\\Model\\Locale','1030','a:1:{s:4:\"code\";s:5:\"th_TH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"th_TH\";}}',NULL,1,'2018-05-30 13:22:14',0),(1752,'system','Akeneo\\Channel\\Component\\Model\\Locale','1031','a:1:{s:4:\"code\";s:5:\"tk_TM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tk_TM\";}}',NULL,1,'2018-05-30 13:22:14',0),(1753,'system','Akeneo\\Channel\\Component\\Model\\Locale','1032','a:1:{s:4:\"code\";s:5:\"tn_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tn_ZA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1754,'system','Akeneo\\Channel\\Component\\Model\\Locale','1033','a:1:{s:4:\"code\";s:5:\"tr_TR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tr_TR\";}}',NULL,1,'2018-05-30 13:22:14',0),(1755,'system','Akeneo\\Channel\\Component\\Model\\Locale','1034','a:1:{s:4:\"code\";s:5:\"tt_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tt_RU\";}}',NULL,1,'2018-05-30 13:22:14',0),(1756,'system','Akeneo\\Channel\\Component\\Model\\Locale','1035','a:1:{s:4:\"code\";s:11:\"tzm_Latn_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"tzm_Latn_DZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1757,'system','Akeneo\\Channel\\Component\\Model\\Locale','1036','a:1:{s:4:\"code\";s:5:\"ug_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ug_CN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1758,'system','Akeneo\\Channel\\Component\\Model\\Locale','1037','a:1:{s:4:\"code\";s:5:\"uk_UA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"uk_UA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1759,'system','Akeneo\\Channel\\Component\\Model\\Locale','1038','a:1:{s:4:\"code\";s:5:\"ur_PK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ur_PK\";}}',NULL,1,'2018-05-30 13:22:14',0),(1760,'system','Akeneo\\Channel\\Component\\Model\\Locale','1039','a:1:{s:4:\"code\";s:10:\"uz_Cyrl_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Cyrl_UZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1761,'system','Akeneo\\Channel\\Component\\Model\\Locale','1040','a:1:{s:4:\"code\";s:10:\"uz_Latn_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Latn_UZ\";}}',NULL,1,'2018-05-30 13:22:14',0),(1762,'system','Akeneo\\Channel\\Component\\Model\\Locale','1041','a:1:{s:4:\"code\";s:5:\"vi_VN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"vi_VN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1763,'system','Akeneo\\Channel\\Component\\Model\\Locale','1042','a:1:{s:4:\"code\";s:5:\"wo_SN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"wo_SN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1764,'system','Akeneo\\Channel\\Component\\Model\\Locale','1043','a:1:{s:4:\"code\";s:5:\"xh_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"xh_ZA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1765,'system','Akeneo\\Channel\\Component\\Model\\Locale','1044','a:1:{s:4:\"code\";s:5:\"yo_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"yo_NG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1766,'system','Akeneo\\Channel\\Component\\Model\\Locale','1045','a:1:{s:4:\"code\";s:5:\"zh_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_CN\";}}',NULL,1,'2018-05-30 13:22:14',0),(1767,'system','Akeneo\\Channel\\Component\\Model\\Locale','1046','a:1:{s:4:\"code\";s:5:\"zh_HK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_HK\";}}',NULL,1,'2018-05-30 13:22:14',0),(1768,'system','Akeneo\\Channel\\Component\\Model\\Locale','1047','a:1:{s:4:\"code\";s:5:\"zh_MO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_MO\";}}',NULL,1,'2018-05-30 13:22:14',0),(1769,'system','Akeneo\\Channel\\Component\\Model\\Locale','1048','a:1:{s:4:\"code\";s:5:\"zh_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_SG\";}}',NULL,1,'2018-05-30 13:22:14',0),(1770,'system','Akeneo\\Channel\\Component\\Model\\Locale','1049','a:1:{s:4:\"code\";s:5:\"zh_TW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_TW\";}}',NULL,1,'2018-05-30 13:22:14',0),(1771,'system','Akeneo\\Channel\\Component\\Model\\Locale','1050','a:1:{s:4:\"code\";s:5:\"zu_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zu_ZA\";}}',NULL,1,'2018-05-30 13:22:14',0),(1772,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','38','a:4:{s:4:\"code\";s:15:\"2013_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2013 collection\";s:11:\"label-en_GB\";s:15:\"2013 collection\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013 collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013 collection\";}}',NULL,1,'2018-05-30 13:22:14',0),(1773,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','39','a:6:{s:4:\"code\";s:15:\"2014_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2014 collection\";s:11:\"label-en_GB\";s:15:\"2014 collection\";s:11:\"label-fr_FR\";s:15:\"Collection 2014\";s:11:\"label-de_DE\";s:15:\"2014 Kollektion\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 collection\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Collection 2014\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 Kollektion\";}}',NULL,1,'2018-05-30 13:22:14',0),(1774,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','40','a:4:{s:4:\"code\";s:15:\"2015_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2015 collection\";s:11:\"label-de_DE\";s:10:\"Kollektion\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015 collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Kollektion\";}}',NULL,1,'2018-05-30 13:22:14',0),(1775,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','41','a:4:{s:4:\"code\";s:8:\"men_2013\";s:6:\"parent\";s:15:\"2013_collection\";s:11:\"label-en_US\";s:21:\"2013 men\'s collection\";s:11:\"label-en_GB\";s:21:\"2013 men\'s collection\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2013\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2013 men\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2013 men\'s collection\";}}',NULL,1,'2018-05-30 13:22:14',0),(1776,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','42','a:4:{s:4:\"code\";s:10:\"women_2013\";s:6:\"parent\";s:15:\"2013_collection\";s:11:\"label-en_US\";s:23:\"2013 women\'s collection\";s:11:\"label-en_GB\";s:23:\"2013 women\'s collection\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2013\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2013 women\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2013 women\'s collection\";}}',NULL,1,'2018-05-30 13:22:14',0),(1777,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','43','a:6:{s:4:\"code\";s:8:\"men_2014\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:21:\"2014 men\'s collection\";s:11:\"label-en_GB\";s:21:\"2014 men\'s collection\";s:11:\"label-fr_FR\";s:28:\"La collection de 2014 hommes\";s:11:\"label-de_DE\";s:23:\"2014 Männer-Kollektion\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2014\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2014 men\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2014 men\'s collection\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"La collection de 2014 hommes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2014 Männer-Kollektion\";}}',NULL,1,'2018-05-30 13:22:14',0),(1778,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','44','a:6:{s:4:\"code\";s:10:\"women_2014\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:23:\"2014 women\'s collection\";s:11:\"label-en_GB\";s:23:\"2014 women\'s collection\";s:11:\"label-fr_FR\";s:28:\"La collection de 2014 femmes\";s:11:\"label-de_DE\";s:20:\"2014 Damenkollektion\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2014\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2014 women\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2014 women\'s collection\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"La collection de 2014 femmes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"2014 Damenkollektion\";}}',NULL,1,'2018-05-30 13:22:14',0),(1779,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','45','a:4:{s:4:\"code\";s:8:\"men_2015\";s:6:\"parent\";s:15:\"2015_collection\";s:11:\"label-en_US\";s:21:\"2015 men\'s collection\";s:11:\"label-de_DE\";s:23:\"2015 Männer-Kollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2015 men\'s collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2015 Männer-Kollektion\";}}',NULL,1,'2018-05-30 13:22:14',0),(1780,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','46','a:4:{s:4:\"code\";s:10:\"women_2015\";s:6:\"parent\";s:15:\"2015_collection\";s:11:\"label-en_US\";s:23:\"2015 women\'s collection\";s:11:\"label-de_DE\";s:20:\"2015 Damenkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2015 women\'s collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"2015 Damenkollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1781,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','47','a:4:{s:4:\"code\";s:15:\"men_2015_spring\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s spring collection\";s:11:\"label-de_DE\";s:33:\"2015 Männer-Frühjahrskollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_spring\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s spring collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"2015 Männer-Frühjahrskollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1782,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','48','a:4:{s:4:\"code\";s:15:\"men_2015_summer\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s summer collection\";s:11:\"label-de_DE\";s:30:\"2015 Männer-Sommer-Kollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_summer\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s summer collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 Männer-Sommer-Kollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1783,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','49','a:4:{s:4:\"code\";s:15:\"men_2015_autumn\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s autumn collection\";s:11:\"label-de_DE\";s:29:\"2015 Männer-Herbstkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_autumn\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s autumn collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"2015 Männer-Herbstkollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1784,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','50','a:4:{s:4:\"code\";s:15:\"men_2015_winter\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s winter collection\";s:11:\"label-de_DE\";s:29:\"2015 Männer-Winterkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_winter\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s winter collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"2015 Männer-Winterkollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1785,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','51','a:4:{s:4:\"code\";s:17:\"women_2015_spring\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s spring collection\";s:11:\"label-de_DE\";s:31:\"2015 Damen-Frühjahrskollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_spring\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s spring collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"2015 Damen-Frühjahrskollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1786,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','52','a:4:{s:4:\"code\";s:17:\"women_2015_summer\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s summer collection\";s:11:\"label-de_DE\";s:28:\"2015 Damen-Sommer-Kollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_summer\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s summer collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 Damen-Sommer-Kollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1787,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','53','a:4:{s:4:\"code\";s:17:\"women_2015_autumn\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s autumn collection\";s:11:\"label-de_DE\";s:27:\"2015 Damen-Herbstkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_autumn\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s autumn collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"2015 Damen-Herbstkollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1788,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','54','a:4:{s:4:\"code\";s:17:\"women_2015_winter\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s winter collection\";s:11:\"label-de_DE\";s:27:\"2015 Damen-Winterkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_winter\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s winter collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"2015 Damen-Winterkollektion\";}}',NULL,1,'2018-05-30 13:22:15',0),(1789,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','15','a:7:{s:4:\"code\";s:7:\"general\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:7:\"General\";s:11:\"label-en_GB\";s:7:\"General\";s:11:\"label-fr_FR\";s:9:\"Général\";s:11:\"label-de_DE\";s:9:\"Allgemein\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"General\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"General\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Général\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Allgemein\";}}',NULL,1,'2018-05-30 13:22:15',0),(1790,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','16','a:7:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Media\";s:11:\"label-en_GB\";s:5:\"Media\";s:11:\"label-fr_FR\";s:7:\"Médias\";s:11:\"label-de_DE\";s:6:\"Medien\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Media\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Media\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Médias\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Medien\";}}',NULL,1,'2018-05-30 13:22:15',0),(1791,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','17','a:7:{s:4:\"code\";s:5:\"sales\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Sales\";s:11:\"label-en_GB\";s:5:\"Sales\";s:11:\"label-fr_FR\";s:6:\"Ventes\";s:11:\"label-de_DE\";s:8:\"Vertrieb\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sales\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sales\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Ventes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Vertrieb\";}}',NULL,1,'2018-05-30 13:22:15',0),(1792,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','18','a:7:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Sizes\";s:11:\"label-en_GB\";s:5:\"Sizes\";s:11:\"label-fr_FR\";s:7:\"Tailles\";s:11:\"label-de_DE\";s:8:\"Größen\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sizes\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sizes\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Tailles\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Größen\";}}',NULL,1,'2018-05-30 13:22:15',0),(1793,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','19','a:7:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:6:\"Colors\";s:11:\"label-en_GB\";s:7:\"Colours\";s:11:\"label-fr_FR\";s:8:\"Couleurs\";s:11:\"label-de_DE\";s:6:\"Farben\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Colors\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Colours\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Couleurs\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Farben\";}}',NULL,1,'2018-05-30 13:22:15',0),(1794,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','20','a:7:{s:4:\"code\";s:10:\"additional\";s:10:\"sort_order\";i:6;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:22:\"Additional information\";s:11:\"label-en_GB\";s:22:\"Additional information\";s:11:\"label-fr_FR\";s:29:\"Informations complémentaires\";s:11:\"label-de_DE\";s:26:\"Zusätzliche Informationen\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:6;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Additional information\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Additional information\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Informations complémentaires\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Zusätzliche Informationen\";}}',NULL,1,'2018-05-30 13:22:15',0),(1795,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','21','a:7:{s:4:\"code\";s:8:\"internal\";s:10:\"sort_order\";i:7;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:8:\"Internal\";s:11:\"label-en_GB\";s:8:\"Internal\";s:11:\"label-fr_FR\";s:7:\"Interne\";s:11:\"label-de_DE\";s:6:\"Intern\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:7;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Internal\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Internal\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Interne\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Intern\";}}',NULL,1,'2018-05-30 13:22:15',0),(1796,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','22','a:7:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Other\";s:11:\"label-en_GB\";s:5:\"Other\";s:11:\"label-fr_FR\";s:5:\"Autre\";s:11:\"label-de_DE\";s:5:\"Ander\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:100;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Autre\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Ander\";}}',NULL,1,'2018-05-30 13:22:15',0),(1797,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','57','a:32:{s:4:\"code\";s:3:\"sku\";s:4:\"type\";s:22:\"pim_catalog_identifier\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:3:\"SKU\";s:11:\"label-en_GB\";s:3:\"SKU\";s:11:\"label-fr_FR\";s:3:\"SKU\";s:11:\"label-de_DE\";s:3:\"SKU\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:1;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"pim_catalog_identifier\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:6:\"unique\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}s:8:\"required\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}}',NULL,1,'2018-05-30 13:22:15',0),(1798,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','15','a:7:{s:4:\"code\";s:7:\"general\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:39:\"sku,name,description,under_european_law\";s:11:\"label-en_US\";s:7:\"General\";s:11:\"label-en_GB\";s:7:\"General\";s:11:\"label-fr_FR\";s:9:\"Général\";s:11:\"label-de_DE\";s:9:\"Allgemein\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"sku,name,description,under_european_law\";}}',NULL,2,'2018-05-30 13:22:15',0),(1799,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','58','a:32:{s:4:\"code\";s:4:\"name\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Name\";s:11:\"label-en_GB\";s:4:\"Name\";s:11:\"label-fr_FR\";s:3:\"Nom\";s:11:\"label-de_DE\";s:4:\"Name\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"Nom\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1800,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','59','a:32:{s:4:\"code\";s:11:\"description\";s:4:\"type\";s:20:\"pim_catalog_textarea\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:1000;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Description\";s:11:\"label-en_GB\";s:11:\"Description\";s:11:\"label-fr_FR\";s:11:\"Description\";s:11:\"label-de_DE\";s:12:\"Beschreibung\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"description\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1000;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Beschreibung\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:15',0),(1801,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','60','a:32:{s:4:\"code\";s:9:\"thumbnail\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Thumbnail\";s:11:\"label-en_GB\";s:9:\"Thumbnail\";s:11:\"label-fr_FR\";s:8:\"Imagette\";s:11:\"label-de_DE\";s:15:\"Miniaturansicht\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"thumbnail\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Thumbnail\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Thumbnail\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Imagette\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Miniaturansicht\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1802,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','16','a:7:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:40:\"thumbnail,image,legend,localizable_image\";s:11:\"label-en_US\";s:5:\"Media\";s:11:\"label-en_GB\";s:5:\"Media\";s:11:\"label-fr_FR\";s:7:\"Médias\";s:11:\"label-de_DE\";s:6:\"Medien\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"thumbnail,image,legend,localizable_image\";}}',NULL,2,'2018-05-30 13:22:15',0),(1803,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','61','a:32:{s:4:\"code\";s:5:\"image\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Image\";s:11:\"label-en_GB\";s:5:\"Image\";s:11:\"label-fr_FR\";s:5:\"Image\";s:11:\"label-de_DE\";s:4:\"Bild\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"image\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Image\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Image\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Image\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Bild\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1804,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','62','a:32:{s:4:\"code\";s:6:\"legend\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Legend\";s:11:\"label-en_GB\";s:6:\"Legend\";s:11:\"label-fr_FR\";s:8:\"Légende\";s:11:\"label-de_DE\";s:7:\"Legende\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"legend\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Legend\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Legend\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Légende\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Legende\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1805,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','63','a:32:{s:4:\"code\";s:5:\"price\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:9:\"1000.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Price\";s:11:\"label-en_GB\";s:5:\"Price\";s:11:\"label-fr_FR\";s:4:\"Prix\";s:11:\"label-de_DE\";s:5:\"Preis\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:13:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"price\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"1000.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Price\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Price\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Prix\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Preis\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1806,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','17','a:7:{s:4:\"code\";s:5:\"sales\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:34:\"price,customer_rating,release_date\";s:11:\"label-en_US\";s:5:\"Sales\";s:11:\"label-en_GB\";s:5:\"Sales\";s:11:\"label-fr_FR\";s:6:\"Ventes\";s:11:\"label-de_DE\";s:8:\"Vertrieb\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"price,customer_rating,release_date\";}}',NULL,2,'2018-05-30 13:22:15',0),(1807,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','64','a:32:{s:4:\"code\";s:15:\"customer_rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Customer rating\";s:11:\"label-en_GB\";s:15:\"Customer rating\";s:11:\"label-fr_FR\";s:21:\"Évaluation du client\";s:11:\"label-de_DE\";s:15:\"Kundenbewertung\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"customer_rating\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Customer rating\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Customer rating\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Évaluation du client\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Kundenbewertung\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:15',0),(1808,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','65','a:32:{s:4:\"code\";s:12:\"release_date\";s:4:\"type\";s:16:\"pim_catalog_date\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Release date\";s:11:\"label-en_GB\";s:12:\"Release date\";s:11:\"label-fr_FR\";s:14:\"Date de sortie\";s:11:\"label-de_DE\";s:17:\"Erscheinungsdatum\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"release_date\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_date\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Release date\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Release date\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Date de sortie\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Erscheinungsdatum\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:15',0),(1809,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','66','a:32:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:11:\"label-en_GB\";s:4:\"Size\";s:11:\"label-fr_FR\";s:6:\"Taille\";s:11:\"label-de_DE\";s:7:\"Größe\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Size\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Size\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Taille\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Größe\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1810,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','18','a:7:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:26:\"size,chest_size,waist_size\";s:11:\"label-en_US\";s:5:\"Sizes\";s:11:\"label-en_GB\";s:5:\"Sizes\";s:11:\"label-fr_FR\";s:7:\"Tailles\";s:11:\"label-de_DE\";s:8:\"Größen\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"size,chest_size,waist_size\";}}',NULL,2,'2018-05-30 13:22:15',0),(1811,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','67','a:32:{s:4:\"code\";s:10:\"chest_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Chest size\";s:11:\"label-en_GB\";s:10:\"Chest size\";s:11:\"label-fr_FR\";s:16:\"Tour de poitrine\";s:11:\"label-de_DE\";s:11:\"Brustumfang\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"chest_size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Chest size\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Chest size\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Tour de poitrine\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Brustumfang\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1812,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','68','a:32:{s:4:\"code\";s:10:\"waist_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Waist size\";s:11:\"label-en_GB\";s:10:\"Waist size\";s:11:\"label-fr_FR\";s:14:\"Tour de taille\";s:11:\"label-de_DE\";s:14:\"Taillengröße\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"waist_size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Waist size\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Waist size\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Tour de taille\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Taillengröße\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1813,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','69','a:32:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:11:\"label-en_GB\";s:6:\"Colour\";s:11:\"label-fr_FR\";s:7:\"Couleur\";s:11:\"label-de_DE\";s:5:\"Farbe\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Color\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Colour\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Couleur\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Farbe\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1814,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','19','a:7:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:23:\"color,additional_colors\";s:11:\"label-en_US\";s:6:\"Colors\";s:11:\"label-en_GB\";s:7:\"Colours\";s:11:\"label-fr_FR\";s:8:\"Couleurs\";s:11:\"label-de_DE\";s:6:\"Farben\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"color,additional_colors\";}}',NULL,2,'2018-05-30 13:22:15',0),(1815,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','70','a:32:{s:4:\"code\";s:17:\"additional_colors\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Additional colors\";s:11:\"label-en_GB\";s:18:\"Additional colours\";s:11:\"label-fr_FR\";s:15:\"Autres couleurs\";s:11:\"label-de_DE\";s:19:\"Zusätzliche Farben\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"additional_colors\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Additional colors\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Additional colours\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Autres couleurs\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Zusätzliche Farben\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1816,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','71','a:32:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:11:\"label-en_GB\";s:12:\"Manufacturer\";s:11:\"label-fr_FR\";s:9:\"Fabricant\";s:11:\"label-de_DE\";s:10:\"Hersteller\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"manufacturer\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Manufacturer\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Manufacturer\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Fabricant\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Hersteller\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1817,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','20','a:7:{s:4:\"code\";s:10:\"additional\";s:10:\"sort_order\";i:6;s:10:\"attributes\";s:138:\"manufacturer,country_of_manufacture,handmade,weather_conditions,care_instructions,washing_temperature,weight,material,additional_materials\";s:11:\"label-en_US\";s:22:\"Additional information\";s:11:\"label-en_GB\";s:22:\"Additional information\";s:11:\"label-fr_FR\";s:29:\"Informations complémentaires\";s:11:\"label-de_DE\";s:26:\"Zusätzliche Informationen\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:138:\"manufacturer,country_of_manufacture,handmade,weather_conditions,care_instructions,washing_temperature,weight,material,additional_materials\";}}',NULL,2,'2018-05-30 13:22:15',0),(1818,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','72','a:32:{s:4:\"code\";s:22:\"country_of_manufacture\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:22:\"Country of manufacture\";s:11:\"label-en_GB\";s:22:\"Country of manufacture\";s:11:\"label-fr_FR\";s:19:\"Pays de fabrication\";s:11:\"label-de_DE\";s:16:\"Herstellungsland\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"country_of_manufacture\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Country of manufacture\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Country of manufacture\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Pays de fabrication\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Herstellungsland\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1819,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','73','a:32:{s:4:\"code\";s:8:\"handmade\";s:4:\"type\";s:19:\"pim_catalog_boolean\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Handmade\";s:11:\"label-en_GB\";s:8:\"Handmade\";s:11:\"label-fr_FR\";s:9:\"Fait main\";s:11:\"label-de_DE\";s:13:\"Handgefertigt\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"handmade\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Handmade\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Handmade\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Fait main\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Handgefertigt\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1820,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','74','a:32:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:11:\"label-en_GB\";s:18:\"Weather conditions\";s:11:\"label-fr_FR\";s:28:\"Conditions météorologiques\";s:11:\"label-de_DE\";s:19:\"Wetterverhältnisse\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"weather_conditions\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Weather conditions\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Weather conditions\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Conditions météorologiques\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Wetterverhältnisse\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1821,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','75','a:32:{s:4:\"code\";s:17:\"care_instructions\";s:4:\"type\";s:20:\"pim_catalog_textarea\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:1000;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:5;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Care instructions\";s:11:\"label-en_GB\";s:17:\"Care instructions\";s:11:\"label-fr_FR\";s:20:\"Conseils d\'entretien\";s:11:\"label-de_DE\";s:14:\"Pflegehinweise\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"care_instructions\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1000;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Care instructions\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Care instructions\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Conseils d\'entretien\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Pflegehinweise\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:15',0),(1822,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','76','a:32:{s:4:\"code\";s:19:\"washing_temperature\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:11:\"Temperature\";s:19:\"default_metric_unit\";s:7:\"CELSIUS\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";s:8:\"100.0000\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:6;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:19:\"Washing temperature\";s:11:\"label-en_GB\";s:19:\"Washing temperature\";s:11:\"label-fr_FR\";s:22:\"Température de lavage\";s:11:\"label-de_DE\";s:15:\"Waschtemperatur\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"washing_temperature\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Temperature\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"CELSIUS\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"100.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:6;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Washing temperature\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Washing temperature\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Température de lavage\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Waschtemperatur\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1823,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','77','a:32:{s:4:\"code\";s:6:\"weight\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Weight\";s:19:\"default_metric_unit\";s:8:\"KILOGRAM\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";s:7:\"10.0000\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:7;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Weight\";s:11:\"label-en_GB\";s:6:\"Weight\";s:11:\"label-fr_FR\";s:5:\"Poids\";s:11:\"label-de_DE\";s:7:\"Gewicht\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"weight\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"KILOGRAM\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"10.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:7;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Poids\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Gewicht\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1824,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','78','a:32:{s:4:\"code\";s:8:\"material\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:8;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Material\";s:11:\"label-en_GB\";s:8:\"Material\";s:11:\"label-fr_FR\";s:9:\"Matériau\";s:11:\"label-de_DE\";s:8:\"Material\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"material\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:8;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Material\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Material\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Matériau\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Material\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1825,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','79','a:32:{s:4:\"code\";s:20:\"additional_materials\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:9;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:20:\"Additional materials\";s:11:\"label-en_GB\";s:20:\"Additional materials\";s:11:\"label-fr_FR\";s:27:\"Matériels supplémentaires\";s:11:\"label-de_DE\";s:24:\"Zusätzliche Materialien\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"additional_materials\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:9;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Additional materials\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Additional materials\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Matériels supplémentaires\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Zusätzliche Materialien\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1826,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','80','a:32:{s:4:\"code\";s:4:\"cost\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:9:\"1000.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Cost\";s:11:\"label-en_GB\";s:4:\"Cost\";s:11:\"label-fr_FR\";s:5:\"Coût\";s:11:\"label-de_DE\";s:6:\"Kosten\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:13:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"cost\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"1000.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Cost\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Cost\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Coût\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Kosten\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1827,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','21','a:7:{s:4:\"code\";s:8:\"internal\";s:10:\"sort_order\";i:7;s:10:\"attributes\";s:53:\"cost,number_in_stock,customs_tax,datasheet,attachment\";s:11:\"label-en_US\";s:8:\"Internal\";s:11:\"label-en_GB\";s:8:\"Internal\";s:11:\"label-fr_FR\";s:7:\"Interne\";s:11:\"label-de_DE\";s:6:\"Intern\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:53:\"cost,number_in_stock,customs_tax,datasheet,attachment\";}}',NULL,2,'2018-05-30 13:22:15',0),(1828,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','81','a:32:{s:4:\"code\";s:15:\"number_in_stock\";s:4:\"type\";s:18:\"pim_catalog_number\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:10:\"10000.0000\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Number in stock\";s:11:\"label-en_GB\";s:15:\"Number in stock\";s:11:\"label-fr_FR\";s:15:\"Nombre en stock\";s:11:\"label-de_DE\";s:16:\"Anzahl auf Lager\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"number_in_stock\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"10000.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Number in stock\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Number in stock\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Nombre en stock\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Anzahl auf Lager\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:15',0),(1829,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','82','a:32:{s:4:\"code\";s:11:\"customs_tax\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:5:\"de_DE\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:9:\"1000.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Customs tax\";s:11:\"label-en_GB\";s:11:\"Customs tax\";s:11:\"label-fr_FR\";s:15:\"Taxe de douanes\";s:11:\"label-de_DE\";s:10:\"Zollsteuer\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:15:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"customs_tax\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:17:\"available_locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"1000.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Customs tax\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Customs tax\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Taxe de douanes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Zollsteuer\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1830,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','83','a:32:{s:4:\"code\";s:18:\"under_european_law\";s:4:\"type\";s:19:\"pim_catalog_boolean\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:11:\"de_DE,fr_FR\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Under European law\";s:11:\"label-en_GB\";s:18:\"Under European law\";s:11:\"label-fr_FR\";s:23:\"Sous la loi Européenne\";s:11:\"label-de_DE\";s:24:\"Nach europäischem Recht\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"under_european_law\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:17:\"available_locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"de_DE,fr_FR\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Under European law\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Under European law\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Sous la loi Européenne\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Nach europäischem Recht\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1831,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','84','a:32:{s:4:\"code\";s:9:\"datasheet\";s:4:\"type\";s:16:\"pim_catalog_file\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:24:\"txt,pdf,doc,docx,csv,rtf\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:7:\"10.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Datasheet\";s:11:\"label-en_GB\";s:9:\"Datasheet\";s:11:\"label-fr_FR\";s:15:\"Fiche technique\";s:11:\"label-de_DE\";s:10:\"Datenblatt\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"datasheet\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_file\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"txt,pdf,doc,docx,csv,rtf\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"10.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Datasheet\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Datasheet\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Fiche technique\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Datenblatt\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1832,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','85','a:32:{s:4:\"code\";s:10:\"attachment\";s:4:\"type\";s:16:\"pim_catalog_file\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:24:\"txt,pdf,doc,docx,csv,rtf\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:7:\"10.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:5;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Attachment\";s:11:\"label-en_GB\";s:10:\"Attachment\";s:11:\"label-fr_FR\";s:12:\"Attachements\";s:11:\"label-de_DE\";s:11:\"Befestigung\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"attachment\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_file\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"txt,pdf,doc,docx,csv,rtf\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"10.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Attachment\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Attachment\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Attachements\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Befestigung\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1833,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','86','a:32:{s:4:\"code\";s:17:\"localizable_image\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Localizable image\";s:11:\"label-en_GB\";s:17:\"Localizable image\";s:11:\"label-fr_FR\";s:16:\"Image localisée\";s:11:\"label-de_DE\";s:18:\"Lokalisiertes Bild\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"localizable_image\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Localizable image\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Localizable image\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Image localisée\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Lokalisiertes Bild\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:15',0),(1834,'system','Akeneo\\Channel\\Component\\Model\\Channel','9','a:7:{s:4:\"code\";s:9:\"ecommerce\";s:10:\"currencies\";s:11:\"USD,GBP,EUR\";s:7:\"locales\";s:23:\"en_US,en_GB,fr_FR,de_DE\";s:11:\"label-en_US\";s:9:\"Ecommerce\";s:11:\"label-de_DE\";s:9:\"Ecommerce\";s:11:\"label-fr_FR\";s:9:\"Ecommerce\";s:8:\"category\";s:15:\"2014_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"ecommerce\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"USD,GBP,EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"en_US,en_GB,fr_FR,de_DE\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}}',NULL,1,'2018-05-30 13:22:15',0),(1835,'system','Akeneo\\Channel\\Component\\Model\\Channel','10','a:7:{s:4:\"code\";s:6:\"tablet\";s:10:\"currencies\";s:7:\"USD,GBP\";s:7:\"locales\";s:11:\"en_US,en_GB\";s:11:\"label-en_US\";s:6:\"Tablet\";s:11:\"label-de_DE\";s:6:\"Tablet\";s:11:\"label-fr_FR\";s:8:\"Tablette\";s:8:\"category\";s:15:\"2013_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"tablet\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"USD,GBP\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,en_GB\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Tablette\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}}',NULL,1,'2018-05-30 13:22:15',0),(1836,'system','Akeneo\\Channel\\Component\\Model\\Channel','11','a:7:{s:4:\"code\";s:5:\"print\";s:10:\"currencies\";s:7:\"USD,EUR\";s:7:\"locales\";s:11:\"en_US,de_DE\";s:11:\"label-en_US\";s:5:\"Print\";s:11:\"label-de_DE\";s:7:\"Drucken\";s:11:\"label-fr_FR\";s:10:\"Impression\";s:8:\"category\";s:15:\"2015_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"print\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"USD,EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,de_DE\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Print\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Drucken\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Impression\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}}',NULL,1,'2018-05-30 13:22:15',0),(1837,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','17','a:5:{s:4:\"code\";s:7:\"variant\";s:11:\"label-en_US\";s:7:\"Variant\";s:11:\"label-en_GB\";s:7:\"Variant\";s:11:\"label-fr_FR\";s:8:\"Variante\";s:11:\"label-de_DE\";s:8:\"Variante\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"variant\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Variant\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Variant\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Variante\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Variante\";}}',NULL,1,'2018-05-30 13:22:15',0),(1838,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','18','a:5:{s:4:\"code\";s:7:\"similar\";s:11:\"label-en_US\";s:7:\"Similar\";s:11:\"label-en_GB\";s:7:\"Similar\";s:11:\"label-fr_FR\";s:9:\"Similaire\";s:11:\"label-de_DE\";s:8:\"Ähnlich\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"similar\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Similaire\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Ähnlich\";}}',NULL,1,'2018-05-30 13:22:15',0),(1839,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','19','a:5:{s:4:\"code\";s:7:\"related\";s:11:\"label-en_US\";s:7:\"Related\";s:11:\"label-en_GB\";s:7:\"Related\";s:11:\"label-fr_FR\";s:7:\"Connexe\";s:11:\"label-de_DE\";s:9:\"Verbunden\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"related\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Connexe\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Verbunden\";}}',NULL,1,'2018-05-30 13:22:15',0),(1840,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','20','a:5:{s:4:\"code\";s:10:\"cross_sell\";s:11:\"label-en_US\";s:10:\"Cross sell\";s:11:\"label-en_GB\";s:10:\"Cross sell\";s:11:\"label-fr_FR\";s:14:\"Vente croisée\";s:11:\"label-de_DE\";s:13:\"Cross-Selling\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"cross_sell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Vente croisée\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Cross-Selling\";}}',NULL,1,'2018-05-30 13:22:15',0),(1841,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','21','a:5:{s:4:\"code\";s:6:\"upsell\";s:11:\"label-en_US\";s:6:\"Upsell\";s:11:\"label-en_GB\";s:6:\"Upsell\";s:11:\"label-fr_FR\";s:16:\"Vente incitative\";s:11:\"label-de_DE\";s:6:\"Upsell\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"upsell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Vente incitative\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}}',NULL,1,'2018-05-30 13:22:15',0),(1842,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','22','a:5:{s:4:\"code\";s:10:\"substitute\";s:11:\"label-en_US\";s:10:\"Substitute\";s:11:\"label-en_GB\";s:10:\"Substitute\";s:11:\"label-fr_FR\";s:9:\"Substitut\";s:11:\"label-de_DE\";s:6:\"Ersatz\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"substitute\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Substitut\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Ersatz\";}}',NULL,1,'2018-05-30 13:22:15',0),(1843,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','23','a:5:{s:4:\"code\";s:4:\"pack\";s:11:\"label-en_US\";s:4:\"Pack\";s:11:\"label-en_GB\";s:4:\"Pack\";s:11:\"label-fr_FR\";s:6:\"Paquet\";s:11:\"label-de_DE\";s:4:\"Pack\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"pack\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Paquet\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}}',NULL,1,'2018-05-30 13:22:15',0),(1844,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','64','a:32:{s:4:\"code\";s:15:\"customer_rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Customer rating\";s:11:\"label-en_GB\";s:15:\"Customer rating\";s:11:\"label-fr_FR\";s:21:\"Évaluation du client\";s:11:\"label-de_DE\";s:15:\"Kundenbewertung\";s:7:\"options\";s:331:\"Code:1,en_US:1 star,en_GB:1 star,fr_FR:1 étoile,de_DE:1 Stern|Code:2,en_US:2 stars,en_GB:2 stars,fr_FR:2 étoiles,de_DE:2 Sterne|Code:3,en_US:3 stars,en_GB:3 stars,fr_FR:3 étoiles,de_DE:3 Sterne|Code:4,en_US:4 stars,en_GB:4 stars,fr_FR:4 étoiles,de_DE:4 Sterne|Code:5,en_US:5 stars,en_GB:5 stars,fr_FR:5 étoiles,de_DE:5 Sternen\";s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:331:\"Code:1,en_US:1 star,en_GB:1 star,fr_FR:1 étoile,de_DE:1 Stern|Code:2,en_US:2 stars,en_GB:2 stars,fr_FR:2 étoiles,de_DE:2 Sterne|Code:3,en_US:3 stars,en_GB:3 stars,fr_FR:3 étoiles,de_DE:3 Sterne|Code:4,en_US:4 stars,en_GB:4 stars,fr_FR:4 étoiles,de_DE:4 Sterne|Code:5,en_US:5 stars,en_GB:5 stars,fr_FR:5 étoiles,de_DE:5 Sternen\";}}',NULL,2,'2018-05-30 13:22:16',0),(1845,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','66','a:32:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:11:\"label-en_GB\";s:4:\"Size\";s:11:\"label-fr_FR\";s:6:\"Taille\";s:11:\"label-de_DE\";s:7:\"Größe\";s:7:\"options\";s:391:\"Code:size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:391:\"Code:size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";}}',NULL,2,'2018-05-30 13:22:16',0),(1846,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','67','a:32:{s:4:\"code\";s:10:\"chest_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Chest size\";s:11:\"label-en_GB\";s:10:\"Chest size\";s:11:\"label-fr_FR\";s:16:\"Tour de poitrine\";s:11:\"label-de_DE\";s:11:\"Brustumfang\";s:7:\"options\";s:439:\"Code:chest_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:chest_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:chest_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:chest_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:chest_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:chest_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:chest_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:chest_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:439:\"Code:chest_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:chest_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:chest_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:chest_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:chest_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:chest_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:chest_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:chest_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";}}',NULL,2,'2018-05-30 13:22:16',0),(1847,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','68','a:32:{s:4:\"code\";s:10:\"waist_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Waist size\";s:11:\"label-en_GB\";s:10:\"Waist size\";s:11:\"label-fr_FR\";s:14:\"Tour de taille\";s:11:\"label-de_DE\";s:14:\"Taillengröße\";s:7:\"options\";s:439:\"Code:waist_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:waist_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:waist_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:waist_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:waist_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:waist_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:waist_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:waist_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:439:\"Code:waist_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:waist_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:waist_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:waist_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:waist_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:waist_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:waist_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:waist_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";}}',NULL,2,'2018-05-30 13:22:16',0),(1848,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','69','a:32:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:11:\"label-en_GB\";s:6:\"Colour\";s:11:\"label-fr_FR\";s:7:\"Couleur\";s:11:\"label-de_DE\";s:5:\"Farbe\";s:7:\"options\";s:396:\"Code:black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:396:\"Code:black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";}}',NULL,2,'2018-05-30 13:22:16',0),(1849,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','70','a:32:{s:4:\"code\";s:17:\"additional_colors\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Additional colors\";s:11:\"label-en_GB\";s:18:\"Additional colours\";s:11:\"label-fr_FR\";s:15:\"Autres couleurs\";s:11:\"label-de_DE\";s:19:\"Zusätzliche Farben\";s:7:\"options\";s:473:\"Code:additional_black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:additional_white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:additional_gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:additional_red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:additional_blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:additional_green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:additional_yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:473:\"Code:additional_black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:additional_white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:additional_gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:additional_red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:additional_blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:additional_green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:additional_yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";}}',NULL,2,'2018-05-30 13:22:16',0),(1850,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','71','a:32:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:11:\"label-en_GB\";s:12:\"Manufacturer\";s:11:\"label-fr_FR\";s:9:\"Fabricant\";s:11:\"label-de_DE\";s:10:\"Hersteller\";s:7:\"options\";s:570:\"Code:american_apparel,en_US:American apparel,en_GB:American apparel,fr_FR:American apparel,de_DE:American apparel|Code:columbia,en_US:Columbia,en_GB:Columbia,fr_FR:Columbia,de_DE:Columbia|Code:diesel,en_US:Diesel,en_GB:Diesel,fr_FR:Diesel,de_DE:Diesel|Code:lacoste,en_US:Lacoste,en_GB:Lacoste,fr_FR:Lacoste,de_DE:Lacoste|Code:levi_strauss,en_US:Levi Strauss,en_GB:Levi Strauss,fr_FR:Levi Strauss,de_DE:Levi Strauss|Code:marc_o_polo,en_US:Marc O’Polo,en_GB:Marc O’Polo,fr_FR:Marc O’Polo,de_DE:Marc O’Polo|Code:prada,en_US:Prada,en_GB:Prada,fr_FR:Prada,de_DE:Prada\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:570:\"Code:american_apparel,en_US:American apparel,en_GB:American apparel,fr_FR:American apparel,de_DE:American apparel|Code:columbia,en_US:Columbia,en_GB:Columbia,fr_FR:Columbia,de_DE:Columbia|Code:diesel,en_US:Diesel,en_GB:Diesel,fr_FR:Diesel,de_DE:Diesel|Code:lacoste,en_US:Lacoste,en_GB:Lacoste,fr_FR:Lacoste,de_DE:Lacoste|Code:levi_strauss,en_US:Levi Strauss,en_GB:Levi Strauss,fr_FR:Levi Strauss,de_DE:Levi Strauss|Code:marc_o_polo,en_US:Marc O’Polo,en_GB:Marc O’Polo,fr_FR:Marc O’Polo,de_DE:Marc O’Polo|Code:prada,en_US:Prada,en_GB:Prada,fr_FR:Prada,de_DE:Prada\";}}',NULL,2,'2018-05-30 13:22:16',0),(1851,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','72','a:32:{s:4:\"code\";s:22:\"country_of_manufacture\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:22:\"Country of manufacture\";s:11:\"label-en_GB\";s:22:\"Country of manufacture\";s:11:\"label-fr_FR\";s:19:\"Pays de fabrication\";s:11:\"label-de_DE\";s:16:\"Herstellungsland\";s:7:\"options\";s:445:\"Code:china,en_US:China,en_GB:China,fr_FR:Chine,de_DE:China|Code:france,en_US:France,en_GB:France,fr_FR:France,de_DE:Frankreich|Code:germany,en_US:Germany,en_GB:Germany,fr_FR:Allemagne,de_DE:Deutschland|Code:italy,en_US:Italy,en_GB:Italy,fr_FR:Italie,de_DE:Italien|Code:taiwan,en_US:Taiwan,en_GB:Taiwan,fr_FR:Taiwan,de_DE:Taiwan|Code:usa,en_US:USA,en_GB:USA,fr_FR:USA,de_DE:USA|Code:vietnam,en_US:Vietnam,en_GB:Vietnam,fr_FR:Vietnam,de_DE:Vietnam\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:445:\"Code:china,en_US:China,en_GB:China,fr_FR:Chine,de_DE:China|Code:france,en_US:France,en_GB:France,fr_FR:France,de_DE:Frankreich|Code:germany,en_US:Germany,en_GB:Germany,fr_FR:Allemagne,de_DE:Deutschland|Code:italy,en_US:Italy,en_GB:Italy,fr_FR:Italie,de_DE:Italien|Code:taiwan,en_US:Taiwan,en_GB:Taiwan,fr_FR:Taiwan,de_DE:Taiwan|Code:usa,en_US:USA,en_GB:USA,fr_FR:USA,de_DE:USA|Code:vietnam,en_US:Vietnam,en_GB:Vietnam,fr_FR:Vietnam,de_DE:Vietnam\";}}',NULL,2,'2018-05-30 13:22:16',0),(1852,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','74','a:32:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:11:\"label-en_GB\";s:18:\"Weather conditions\";s:11:\"label-fr_FR\";s:28:\"Conditions météorologiques\";s:11:\"label-de_DE\";s:19:\"Wetterverhältnisse\";s:7:\"options\";s:279:\"Code:dry,en_US:Dry,en_GB:Dry,fr_FR:Sec,de_DE:Trocken|Code:wet,en_US:Wet,en_GB:Wet,fr_FR:Humide,de_DE:Nass|Code:hot,en_US:Hot,en_GB:Hot,fr_FR:Chaud,de_DE:Heiß|Code:cold,en_US:Cold,en_GB:Cold,fr_FR:Froid,de_DE:Kalt|Code:snowy,en_US:Snowy,en_GB:Snowy,fr_FR:Neigeux,de_DE:Verschneit\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:279:\"Code:dry,en_US:Dry,en_GB:Dry,fr_FR:Sec,de_DE:Trocken|Code:wet,en_US:Wet,en_GB:Wet,fr_FR:Humide,de_DE:Nass|Code:hot,en_US:Hot,en_GB:Hot,fr_FR:Chaud,de_DE:Heiß|Code:cold,en_US:Cold,en_GB:Cold,fr_FR:Froid,de_DE:Kalt|Code:snowy,en_US:Snowy,en_GB:Snowy,fr_FR:Neigeux,de_DE:Verschneit\";}}',NULL,2,'2018-05-30 13:22:16',0),(1853,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','78','a:32:{s:4:\"code\";s:8:\"material\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:8;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Material\";s:11:\"label-en_GB\";s:8:\"Material\";s:11:\"label-fr_FR\";s:9:\"Matériau\";s:11:\"label-de_DE\";s:8:\"Material\";s:7:\"options\";s:409:\"Code:cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:409:\"Code:cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";}}',NULL,2,'2018-05-30 13:22:16',0),(1854,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','79','a:32:{s:4:\"code\";s:20:\"additional_materials\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:9;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:20:\"Additional materials\";s:11:\"label-en_GB\";s:20:\"Additional materials\";s:11:\"label-fr_FR\";s:27:\"Matériels supplémentaires\";s:11:\"label-de_DE\";s:24:\"Zusätzliche Materialien\";s:7:\"options\";s:486:\"Code:additional_cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:additional_flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:additional_wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:additional_silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:additional_denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:additional_leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:additional_fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:486:\"Code:additional_cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:additional_flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:additional_wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:additional_silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:additional_denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:additional_leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:additional_fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";}}',NULL,2,'2018-05-30 13:22:16',0),(1855,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','9','a:11:{s:4:\"code\";s:7:\"tshirts\";s:10:\"attributes\";s:231:\"additional_colors,color,cost,country_of_manufacture,customer_rating,customs_tax,datasheet,description,handmade,image,legend,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";s:18:\"requirements-print\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";s:19:\"requirements-tablet\";s:38:\"color,manufacturer,name,price,size,sku\";s:11:\"label-en_US\";s:8:\"T-shirts\";s:11:\"label-en_GB\";s:8:\"T-shirts\";s:11:\"label-fr_FR\";s:8:\"T-shirts\";s:11:\"label-de_DE\";s:8:\"T-shirts\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"tshirts\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:231:\"additional_colors,color,cost,country_of_manufacture,customer_rating,customs_tax,datasheet,description,handmade,image,legend,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"color,manufacturer,name,price,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}}',NULL,1,'2018-05-30 13:22:17',0),(1856,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','10','a:11:{s:4:\"code\";s:8:\"sweaters\";s:10:\"attributes\";s:253:\"additional_colors,additional_materials,care_instructions,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";s:18:\"requirements-print\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";s:19:\"requirements-tablet\";s:38:\"color,manufacturer,name,price,size,sku\";s:11:\"label-en_US\";s:8:\"Sweaters\";s:11:\"label-en_GB\";s:9:\"Chandails\";s:11:\"label-fr_FR\";s:8:\"Sweaters\";s:11:\"label-de_DE\";s:9:\"Pullovern\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"sweaters\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:253:\"additional_colors,additional_materials,care_instructions,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"color,manufacturer,name,price,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Sweaters\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Chandails\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Sweaters\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Pullovern\";}}',NULL,1,'2018-05-30 13:22:17',0),(1857,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','11','a:11:{s:4:\"code\";s:7:\"jackets\";s:10:\"attributes\";s:289:\"additional_colors,additional_materials,care_instructions,chest_size,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,sku,thumbnail,waist_size,washing_temperature,weather_conditions,weight\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:76:\"chest_size,color,description,manufacturer,material,name,price,sku,waist_size\";s:18:\"requirements-print\";s:118:\"chest_size,color,country_of_manufacture,description,manufacturer,material,name,price,sku,waist_size,weather_conditions\";s:19:\"requirements-tablet\";s:55:\"chest_size,color,manufacturer,name,price,sku,waist_size\";s:11:\"label-en_US\";s:7:\"Jackets\";s:11:\"label-en_GB\";s:7:\"Jackets\";s:11:\"label-fr_FR\";s:6:\"Vestes\";s:11:\"label-de_DE\";s:6:\"Jacken\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"jackets\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:289:\"additional_colors,additional_materials,care_instructions,chest_size,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,sku,thumbnail,waist_size,washing_temperature,weather_conditions,weight\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:76:\"chest_size,color,description,manufacturer,material,name,price,sku,waist_size\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:118:\"chest_size,color,country_of_manufacture,description,manufacturer,material,name,price,sku,waist_size,weather_conditions\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:55:\"chest_size,color,manufacturer,name,price,sku,waist_size\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Jackets\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Jackets\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Vestes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Jacken\";}}',NULL,1,'2018-05-30 13:22:17',0),(1858,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','12','a:11:{s:4:\"code\";s:7:\"sandals\";s:10:\"attributes\";s:26:\"description,name,price,sku\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:26:\"description,name,price,sku\";s:18:\"requirements-print\";s:26:\"description,name,price,sku\";s:19:\"requirements-tablet\";s:14:\"name,price,sku\";s:11:\"label-en_US\";s:7:\"Sandals\";s:11:\"label-en_GB\";s:7:\"Sandals\";s:11:\"label-fr_FR\";s:7:\"Sandals\";s:11:\"label-de_DE\";s:7:\"Sandals\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"sandals\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"description,name,price,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"description,name,price,sku\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"description,name,price,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"name,price,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}}',NULL,1,'2018-05-30 13:22:17',0),(1859,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','233','a:5:{s:4:\"code\";s:14:\"product_import\";s:5:\"label\";s:18:\"CSV Product import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:384:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"CSV Product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:384:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1860,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','234','a:5:{s:4:\"code\";s:19:\"xlsx_product_import\";s:5:\"label\";s:19:\"XLSX product import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:338:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"xlsx_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"XLSX product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:338:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1861,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','235','a:5:{s:4:\"code\";s:24:\"ecommerce_product_export\";s:5:\"label\";s:28:\"Product export for ecommerce\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:480:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"ecommerce\",\"locales\":[\"en_US\",\"en_GB\",\"fr_FR\",\"de_DE\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"ecommerce_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Product export for ecommerce\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:480:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"ecommerce\",\"locales\":[\"en_US\",\"en_GB\",\"fr_FR\",\"de_DE\"]}}}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1862,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','236','a:5:{s:4:\"code\";s:21:\"tablet_product_export\";s:5:\"label\";s:25:\"Product export for tablet\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:461:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"tablet_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Product export for tablet\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:461:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1863,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','237','a:5:{s:4:\"code\";s:26:\"xlsx_tablet_product_export\";s:5:\"label\";s:30:\"XLSX product export for tablet\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:450:\"{\"filePath\":\"\\/tmp\\/products.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"xlsx_tablet_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"XLSX product export for tablet\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:450:\"{\"filePath\":\"\\/tmp\\/products.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1864,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','238','a:5:{s:4:\"code\";s:20:\"print_product_export\";s:5:\"label\";s:24:\"Product export for print\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:460:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2015_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"print\",\"locales\":[\"en_US\",\"de_DE\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"print_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Product export for print\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:460:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2015_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"print\",\"locales\":[\"en_US\",\"de_DE\"]}}}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1865,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','239','a:5:{s:4:\"code\";s:25:\"csv_apparel_family_export\";s:5:\"label\";s:22:\"Footwear family export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"csv_apparel_family_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Footwear family export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1866,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','240','a:5:{s:4:\"code\";s:15:\"category_import\";s:5:\"label\";s:15:\"Category import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"category_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Category import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1867,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','241','a:5:{s:4:\"code\";s:15:\"category_export\";s:5:\"label\";s:15:\"Category export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"category_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Category export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1868,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','242','a:5:{s:4:\"code\";s:23:\"association_type_import\";s:5:\"label\";s:23:\"Association type import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"association_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Association type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1869,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','243','a:5:{s:4:\"code\";s:23:\"association_type_export\";s:5:\"label\";s:23:\"Association type export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"association_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Association type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1870,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','244','a:5:{s:4:\"code\";s:12:\"group_import\";s:5:\"label\";s:12:\"Group import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1871,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','245','a:5:{s:4:\"code\";s:12:\"group_export\";s:5:\"label\";s:12:\"Group export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1872,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','246','a:5:{s:4:\"code\";s:17:\"xlsx_group_export\";s:5:\"label\";s:17:\"XLSX group export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/xlsx_group_export.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"xlsx_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"XLSX group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/xlsx_group_export.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1873,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','247','a:5:{s:4:\"code\";s:16:\"attribute_import\";s:5:\"label\";s:16:\"Attribute import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"attribute_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Attribute import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1874,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','248','a:5:{s:4:\"code\";s:16:\"attribute_export\";s:5:\"label\";s:16:\"Attribute export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"attribute_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Attribute export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1875,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','249','a:5:{s:4:\"code\";s:13:\"option_import\";s:5:\"label\";s:13:\"Option import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"option_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Option import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1876,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','250','a:5:{s:4:\"code\";s:13:\"option_export\";s:5:\"label\";s:13:\"Option export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"option_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Option export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:17',0),(1877,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','251','a:5:{s:4:\"code\";s:20:\"update_product_value\";s:5:\"label\";s:20:\"Mass update products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"update_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass update products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1878,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','252','a:5:{s:4:\"code\";s:17:\"add_product_value\";s:5:\"label\";s:24:\"Mass add products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"add_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1879,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','253','a:5:{s:4:\"code\";s:20:\"remove_product_value\";s:5:\"label\";s:27:\"Mass remove products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1880,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','254','a:5:{s:4:\"code\";s:16:\"move_to_category\";s:5:\"label\";s:23:\"Mass move to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"move_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass move to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1881,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','255','a:5:{s:4:\"code\";s:15:\"add_association\";s:5:\"label\";s:23:\"Mass associate products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_association\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass associate products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1882,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','256','a:5:{s:4:\"code\";s:15:\"add_to_category\";s:5:\"label\";s:22:\"Mass add to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Mass add to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1883,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','257','a:5:{s:4:\"code\";s:12:\"add_to_group\";s:5:\"label\";s:25:\"Mass add product to group\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"add_to_group\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Mass add product to group\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1884,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','258','a:5:{s:4:\"code\";s:20:\"remove_from_category\";s:5:\"label\";s:27:\"Mass remove from categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_from_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove from categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1885,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','259','a:5:{s:4:\"code\";s:22:\"edit_common_attributes\";s:5:\"label\";s:28:\"Mass edit product attributes\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"edit_common_attributes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Mass edit product attributes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1886,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','260','a:5:{s:4:\"code\";s:19:\"add_attribute_value\";s:5:\"label\";s:24:\"Mass add attribute value\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"add_attribute_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add attribute value\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1887,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','261','a:5:{s:4:\"code\";s:29:\"add_to_existing_product_model\";s:5:\"label\";s:29:\"Add to existing product model\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"add_to_existing_product_model\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Add to existing product model\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1888,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','262','a:5:{s:4:\"code\";s:34:\"compute_product_models_descendants\";s:5:\"label\";s:34:\"Compute product models descendants\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:34:\"compute_product_models_descendants\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"Compute product models descendants\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1889,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','263','a:5:{s:4:\"code\";s:39:\"compute_completeness_of_products_family\";s:5:\"label\";s:39:\"compute completeness of products family\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:39:\"compute_completeness_of_products_family\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute completeness of products family\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1890,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','264','a:5:{s:4:\"code\";s:40:\"compute_family_variant_structure_changes\";s:5:\"label\";s:33:\"Compute variant structure changes\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:40:\"compute_family_variant_structure_changes\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Compute variant structure changes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1891,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','265','a:5:{s:4:\"code\";s:34:\"delete_products_and_product_models\";s:5:\"label\";s:20:\"Mass delete products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:11:\"mass_delete\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"delete_products_and_product_models\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass delete products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"mass_delete\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:17',0),(1892,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','3','a:6:{s:4:\"code\";s:7:\"similar\";s:4:\"type\";s:7:\"similar\";s:11:\"label-en_US\";s:7:\"Similar\";s:11:\"label-en_GB\";s:7:\"Similar\";s:11:\"label-fr_FR\";s:9:\"Similaire\";s:11:\"label-de_DE\";s:8:\"Ähnlich\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"similar\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"similar\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Similaire\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Ähnlich\";}}',NULL,1,'2018-05-30 13:22:17',0),(1893,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','4','a:6:{s:4:\"code\";s:7:\"related\";s:4:\"type\";s:7:\"related\";s:11:\"label-en_US\";s:7:\"Related\";s:11:\"label-en_GB\";s:7:\"Related\";s:11:\"label-fr_FR\";s:7:\"Connexe\";s:11:\"label-de_DE\";s:9:\"Verbunden\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"related\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"related\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Connexe\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Verbunden\";}}',NULL,1,'2018-05-30 13:22:17',0),(1894,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','5','a:6:{s:4:\"code\";s:10:\"cross_sell\";s:4:\"type\";s:10:\"cross_sell\";s:11:\"label-en_US\";s:10:\"Cross sell\";s:11:\"label-en_GB\";s:10:\"Cross sell\";s:11:\"label-fr_FR\";s:14:\"Vente croisée\";s:11:\"label-de_DE\";s:13:\"Cross-Selling\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"cross_sell\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"cross_sell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Vente croisée\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Cross-Selling\";}}',NULL,1,'2018-05-30 13:22:17',0),(1895,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','6','a:6:{s:4:\"code\";s:6:\"upsell\";s:4:\"type\";s:6:\"upsell\";s:11:\"label-en_US\";s:6:\"Upsell\";s:11:\"label-en_GB\";s:6:\"Upsell\";s:11:\"label-fr_FR\";s:16:\"Vente incitative\";s:11:\"label-de_DE\";s:6:\"Upsell\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"upsell\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"upsell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Vente incitative\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}}',NULL,1,'2018-05-30 13:22:17',0),(1896,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','7','a:6:{s:4:\"code\";s:10:\"substitute\";s:4:\"type\";s:10:\"substitute\";s:11:\"label-en_US\";s:10:\"Substitute\";s:11:\"label-en_GB\";s:10:\"Substitute\";s:11:\"label-fr_FR\";s:9:\"Substitut\";s:11:\"label-de_DE\";s:6:\"Ersatz\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"substitute\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"substitute\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Substitut\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Ersatz\";}}',NULL,1,'2018-05-30 13:22:17',0);
/*!40000 ALTER TABLE `pim_versioning_version` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-05-30 15:22:18
