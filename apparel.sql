-- MySQL dump 10.13  Distrib 5.7.22, for osx10.12 (x86_64)
--
-- Host: localhost    Database: pcdb
-- ------------------------------------------------------
-- Server version	5.7.22

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES (7,'(root)');
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES (37,7,13,19,NULL,2,1,1,'all',0,0),(38,7,14,19,NULL,2,1056964608,1,'all',0,0),(39,7,13,20,NULL,1,1,1,'all',0,0),(40,7,14,20,NULL,1,1056964608,1,'all',0,0),(41,7,13,21,NULL,0,1,1,'all',0,0),(42,7,14,21,NULL,0,1056964608,1,'all',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES (13,NULL,7,'action',1),(14,NULL,7,'entity',1);
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
INSERT INTO `acl_object_identity_ancestors` VALUES (13,13),(14,14);
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
INSERT INTO `acl_security_identities` VALUES (19,'ROLE_ADMINISTRATOR',0),(20,'ROLE_CATALOG_MANAGER',0),(21,'ROLE_USER',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=404 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_instance`
--

LOCK TABLES `akeneo_batch_job_instance` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_instance` DISABLE KEYS */;
INSERT INTO `akeneo_batch_job_instance` VALUES (371,'product_import','CSV Product import','csv_product_import',0,'Akeneo CSV Connector','a:17:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(372,'xlsx_product_import','XLSX product import','xlsx_product_import',0,'Akeneo XLSX Connector','a:14:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(373,'ecommerce_product_export','Product export for ecommerce','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2014_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:9:\"ecommerce\";s:7:\"locales\";a:4:{i:0;s:5:\"en_US\";i:1;s:5:\"en_GB\";i:2;s:5:\"fr_FR\";i:3;s:5:\"de_DE\";}}}}','export'),(374,'tablet_product_export','Product export for tablet','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2013_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:6:\"tablet\";s:7:\"locales\";a:2:{i:0;s:5:\"en_US\";i:1;s:5:\"en_GB\";}}}}','export'),(375,'xlsx_tablet_product_export','XLSX product export for tablet','xlsx_product_export',0,'Akeneo XLSX Connector','a:9:{s:8:\"filePath\";s:18:\"/tmp/products.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2013_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:6:\"tablet\";s:7:\"locales\";a:2:{i:0;s:5:\"en_US\";i:1;s:5:\"en_GB\";}}}}','export'),(376,'print_product_export','Product export for print','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2015_collection\";}}i:2;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}}s:9:\"structure\";a:2:{s:5:\"scope\";s:5:\"print\";s:7:\"locales\";a:2:{i:0;s:5:\"en_US\";i:1;s:5:\"de_DE\";}}}}','export'),(377,'csv_apparel_family_export','Footwear family export','csv_family_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/family.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(378,'category_import','Category import','csv_category_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(379,'category_export','Category export','csv_category_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:17:\"/tmp/category.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(380,'association_type_import','Association type import','csv_association_type_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(381,'association_type_export','Association type export','csv_association_type_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:25:\"/tmp/association_type.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(382,'group_import','Group import','csv_group_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(383,'group_export','Group export','csv_group_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:14:\"/tmp/group.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(384,'xlsx_group_export','XLSX group export','xlsx_group_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:27:\"/tmp/xlsx_group_export.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(385,'attribute_import','Attribute import','csv_attribute_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(386,'attribute_export','Attribute export','csv_attribute_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:18:\"/tmp/attribute.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(387,'option_import','Option import','csv_attribute_option_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(388,'option_export','Option export','csv_attribute_option_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/option.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(389,'update_product_value','Mass update products','update_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(390,'add_product_value','Mass add products values','add_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(391,'remove_product_value','Mass remove products values','remove_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(392,'move_to_category','Mass move to categories','move_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(393,'add_association','Mass associate products','add_association',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(394,'add_to_category','Mass add to categories','add_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(395,'add_to_group','Mass add product to group','add_to_group',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(396,'remove_from_category','Mass remove from categories','remove_from_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(397,'edit_common_attributes','Mass edit product attributes','edit_common_attributes',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(398,'add_attribute_value','Mass add attribute value','add_attribute_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(399,'add_to_existing_product_model','Add to existing product model','add_to_existing_product_model',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(400,'compute_product_models_descendants','Compute product models descendants','compute_product_models_descendants',0,'internal','a:0:{}','compute_product_models_descendants'),(401,'compute_completeness_of_products_family','compute completeness of products family','compute_completeness_of_products_family',0,'internal','a:0:{}','compute_completeness_of_products_family'),(402,'compute_family_variant_structure_changes','Compute variant structure changes','compute_family_variant_structure_changes',0,'internal','a:0:{}','compute_family_variant_structure_changes'),(403,'delete_products_and_product_models','Mass delete products','delete_products_and_product_models',0,'Akeneo Mass Edit Connector','a:0:{}','mass_delete');
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
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_group`
--

LOCK TABLES `oro_access_group` WRITE;
/*!40000 ALTER TABLE `oro_access_group` DISABLE KEYS */;
INSERT INTO `oro_access_group` VALUES (28,'All'),(25,'IT support'),(26,'Manager'),(27,'Redactor');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_role`
--

LOCK TABLES `oro_access_role` WRITE;
/*!40000 ALTER TABLE `oro_access_role` DISABLE KEYS */;
INSERT INTO `oro_access_role` VALUES (19,'ROLE_ADMINISTRATOR','Administrator'),(20,'ROLE_CATALOG_MANAGER','Catalog manager'),(21,'ROLE_USER','User');
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user`
--

LOCK TABLES `oro_user` WRITE;
/*!40000 ALTER TABLE `oro_user` DISABLE KEYS */;
INSERT INTO `oro_user` VALUES (32,1318,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,NULL,1,'s7yld6a13c0gkc484ksccow4sgwg4og','YIRayZfoC/nXt5kGbYBdo6UYgsvv6JWsES4rqMXsW4CaRYDy8XVKO2vlcrZKCV2wWf/z+t1YoQzyBYrX5/GbiQ==',NULL,NULL,NULL,0,'2018-06-03 13:05:15','2018-06-03 13:05:15','[]',0,NULL,'UTC',1318,14,74),(33,1318,'Julia','Julia@example.com',NULL,'Julia',NULL,'Stark',NULL,NULL,NULL,1,'5nveldle51oocckow800gookwc40cg4','gKUgE1eEf5xdMuQlNTznTeHpfJqHz4o9hkubw0u8A8Ofx3JPaNpyyzPXQD6u95VX/CKUnUCxhdx5MBClEGSZOQ==',NULL,NULL,NULL,0,'2018-06-03 13:05:15','2018-06-03 13:05:15','[]',0,NULL,'UTC',1318,15,73),(34,1318,'Peter','Peter@example.com',NULL,'Peter',NULL,'Williams',NULL,NULL,NULL,1,'avuc7xc4l08c0888gskc4sgccokwwck','QLLt4x2epuV2ooc6gLssFEQZNCCNeUf+9G3jHmbTjbyfKLC4GkVJFa2IwBYKObMe7sHtXozGmfETovszXHM3cg==',NULL,NULL,NULL,0,'2018-06-03 13:05:15','2018-06-03 13:05:15','[]',0,NULL,'UTC',1318,16,75),(35,1318,'Mary','Mary@example.com',NULL,'Mary',NULL,'Smith',NULL,NULL,NULL,1,'lmefukmwmpwk0408k00w0kkkw0gwcgs','SrkuEt7ytfM11+sFoRynhmXklzNqT9GspUAZjU1yeiZvBn7lOJiaZ5MHRMIiou/8d7ARCFe14+c137pvwAEFsA==',NULL,NULL,NULL,0,'2018-06-03 13:05:15','2018-06-03 13:05:15','[]',0,NULL,'UTC',1318,14,73),(36,1318,'Sandra','Sandra@example.com',NULL,'Sandra',NULL,'Harvey',NULL,NULL,NULL,1,'iwpidkfjlm0o404wccs8wks0k8ck0gg','CQ1DoVykwCk0kZqzF8l9L6yvtLT3S0Qd7iXUcq6f6JqaOfm0iyqQrdsT6M+8fDs9QjrdGmxxF1RHl2BVnMPDqw==',NULL,NULL,NULL,0,'2018-06-03 13:05:15','2018-06-03 13:05:15','[]',0,NULL,'UTC',1318,15,74),(37,1350,'Julien','Julien@example.com',NULL,'Julien',NULL,'Février',NULL,NULL,NULL,1,'czdxml4juhsg0ss8og48go4wo0cso4s','VBtbyH3JDe8Y7y7OUwOEfD7cGk8zCfp+S99Kw1G2CmuXyIM9sKBCBH7+w8NvUojrjxXxzZQoN4miTSW0iHbk9Q==',NULL,NULL,NULL,0,'2018-06-03 13:05:15','2018-06-03 13:05:15','[]',0,NULL,'UTC',1350,14,74);
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
INSERT INTO `oro_user_access_group` VALUES (32,25),(32,28),(33,26),(33,28),(34,25),(34,28),(35,27),(35,28),(36,27),(36,28),(37,27),(37,28);
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
INSERT INTO `oro_user_access_role` VALUES (32,19),(33,20),(34,19),(35,21),(36,21),(37,21);
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type`
--

LOCK TABLES `pim_catalog_association_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type` VALUES (25,'variant','2018-06-03 13:05:13','2018-06-03 13:05:13'),(26,'similar','2018-06-03 13:05:13','2018-06-03 13:05:13'),(27,'related','2018-06-03 13:05:13','2018-06-03 13:05:13'),(28,'cross_sell','2018-06-03 13:05:13','2018-06-03 13:05:13'),(29,'upsell','2018-06-03 13:05:13','2018-06-03 13:05:13'),(30,'substitute','2018-06-03 13:05:13','2018-06-03 13:05:13'),(31,'pack','2018-06-03 13:05:13','2018-06-03 13:05:13');
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type_translation`
--

LOCK TABLES `pim_catalog_association_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type_translation` VALUES (45,25,'Variant','en_US'),(46,25,'Variant','en_GB'),(47,25,'Variante','fr_FR'),(48,25,'Variante','de_DE'),(49,26,'Similar','en_US'),(50,26,'Similar','en_GB'),(51,26,'Similaire','fr_FR'),(52,26,'Ähnlich','de_DE'),(53,27,'Related','en_US'),(54,27,'Related','en_GB'),(55,27,'Connexe','fr_FR'),(56,27,'Verbunden','de_DE'),(57,28,'Cross sell','en_US'),(58,28,'Cross sell','en_GB'),(59,28,'Vente croisée','fr_FR'),(60,28,'Cross-Selling','de_DE'),(61,29,'Upsell','en_US'),(62,29,'Upsell','en_GB'),(63,29,'Vente incitative','fr_FR'),(64,29,'Upsell','de_DE'),(65,30,'Substitute','en_US'),(66,30,'Substitute','en_GB'),(67,30,'Substitut','fr_FR'),(68,30,'Ersatz','de_DE'),(69,31,'Pack','en_US'),(70,31,'Pack','en_GB'),(71,31,'Paquet','fr_FR'),(72,31,'Pack','de_DE');
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
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute`
--

LOCK TABLES `pim_catalog_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES (111,27,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,1,1,0,0,'sku','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_identifier','text','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(112,27,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'name','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(113,27,3,1,1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'description','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','textarea','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(114,28,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'thumbnail','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(115,28,2,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'image','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(116,28,3,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'legend','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(117,29,1,1,NULL,NULL,NULL,NULL,1.0000,1000.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'price','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(118,29,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'customer_rating','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(119,29,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'release_date','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_date','date','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:12'),(120,30,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(121,30,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'chest_size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(122,30,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'waist_size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(123,31,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'color','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(124,31,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'additional_colors','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(125,32,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'manufacturer','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(126,32,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'country_of_manufacture','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(127,32,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'handmade','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(128,32,4,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'weather_conditions','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(129,32,5,0,1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'care_instructions','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','textarea','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(130,32,6,0,NULL,NULL,NULL,NULL,NULL,100.0000,0,0,NULL,NULL,'Temperature','CELSIUS',NULL,'',NULL,0,0,0,0,'washing_temperature','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(131,32,7,0,NULL,NULL,NULL,NULL,NULL,10.0000,0,0,NULL,NULL,'Weight','KILOGRAM',NULL,'',NULL,0,0,0,0,'weight','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(132,32,8,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'material','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(133,32,9,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'additional_materials','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:14'),(134,33,1,1,NULL,NULL,NULL,NULL,1.0000,1000.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'cost','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(135,33,2,1,NULL,NULL,NULL,NULL,1.0000,10000.0000,0,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'number_in_stock','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(136,33,3,1,NULL,NULL,NULL,NULL,1.0000,1000.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'customs_tax','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(137,27,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'under_european_law','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(138,33,4,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10.00,'txt,pdf,doc,docx,csv,rtf',NULL,0,0,0,0,'datasheet','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_file','media','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(139,33,5,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10.00,'txt,pdf,doc,docx,csv,rtf',NULL,0,0,0,0,'attachment','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_file','media','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13'),(140,28,3,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,1,0,'localizable_image','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:0:{}','2018-06-03 13:05:12','2018-06-03 13:05:13');
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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group`
--

LOCK TABLES `pim_catalog_attribute_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group` VALUES (27,'general',1,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(28,'media',2,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(29,'sales',3,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(30,'sizes',4,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(31,'colors',5,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(32,'additional',6,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(33,'internal',7,'2018-06-03 13:05:12','2018-06-03 13:05:12'),(34,'other',100,'2018-06-03 13:05:12','2018-06-03 13:05:12');
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
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group_translation`
--

LOCK TABLES `pim_catalog_attribute_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group_translation` VALUES (50,27,'General','en_US'),(51,27,'General','en_GB'),(52,27,'Général','fr_FR'),(53,27,'Allgemein','de_DE'),(54,28,'Media','en_US'),(55,28,'Media','en_GB'),(56,28,'Médias','fr_FR'),(57,28,'Medien','de_DE'),(58,29,'Sales','en_US'),(59,29,'Sales','en_GB'),(60,29,'Ventes','fr_FR'),(61,29,'Vertrieb','de_DE'),(62,30,'Sizes','en_US'),(63,30,'Sizes','en_GB'),(64,30,'Tailles','fr_FR'),(65,30,'Größen','de_DE'),(66,31,'Colors','en_US'),(67,31,'Colours','en_GB'),(68,31,'Couleurs','fr_FR'),(69,31,'Farben','de_DE'),(70,32,'Additional information','en_US'),(71,32,'Additional information','en_GB'),(72,32,'Informations complémentaires','fr_FR'),(73,32,'Zusätzliche Informationen','de_DE'),(74,33,'Internal','en_US'),(75,33,'Internal','en_GB'),(76,33,'Interne','fr_FR'),(77,33,'Intern','de_DE'),(78,34,'Other','en_US'),(79,34,'Other','en_GB'),(80,34,'Autre','fr_FR'),(81,34,'Ander','de_DE');
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
INSERT INTO `pim_catalog_attribute_locale` VALUES (136,1299),(137,1299),(137,1350);
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
) ENGINE=InnoDB AUTO_INCREMENT=259 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option`
--

LOCK TABLES `pim_catalog_attribute_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option` VALUES (183,118,'1',0),(184,118,'2',0),(185,118,'3',0),(186,118,'4',0),(187,118,'5',0),(188,120,'size_XXS',0),(189,120,'size_XS',0),(190,120,'size_S',0),(191,120,'size_M',0),(192,120,'size_L',0),(193,120,'size_XL',0),(194,120,'size_2XL',0),(195,120,'size_3XL',0),(196,121,'chest_size_XXS',0),(197,121,'chest_size_XS',0),(198,121,'chest_size_S',0),(199,121,'chest_size_M',0),(200,121,'chest_size_L',0),(201,121,'chest_size_XL',0),(202,121,'chest_size_2XL',0),(203,121,'chest_size_3XL',0),(204,122,'waist_size_XXS',0),(205,122,'waist_size_XS',0),(206,122,'waist_size_S',0),(207,122,'waist_size_M',0),(208,122,'waist_size_L',0),(209,122,'waist_size_XL',0),(210,122,'waist_size_2XL',0),(211,122,'waist_size_3XL',0),(212,123,'black',0),(213,123,'white',0),(214,123,'gray',0),(215,123,'red',0),(216,123,'blue',0),(217,123,'green',0),(218,123,'yellow',0),(219,124,'additional_black',1),(220,124,'additional_white',2),(221,124,'additional_gray',3),(222,124,'additional_red',4),(223,124,'additional_blue',5),(224,124,'additional_green',6),(225,124,'additional_yellow',7),(226,125,'american_apparel',0),(227,125,'lacoste',0),(228,125,'columbia',0),(229,125,'levi_strauss',0),(230,125,'diesel',0),(231,125,'prada',0),(232,125,'marc_o_polo',0),(233,126,'usa',0),(234,126,'france',0),(235,126,'germany',0),(236,126,'italy',0),(237,126,'china',0),(238,126,'taiwan',0),(239,126,'vietnam',0),(240,128,'dry',1),(241,128,'wet',2),(242,128,'hot',3),(243,128,'cold',4),(244,128,'snowy',5),(245,132,'cotton',1),(246,132,'flax',2),(247,132,'wool',3),(248,132,'silk',4),(249,132,'denim',5),(250,132,'leather',6),(251,132,'fur',7),(252,133,'additional_cotton',1),(253,133,'additional_flax',2),(254,133,'additional_wool',3),(255,133,'additional_silk',4),(256,133,'additional_denim',5),(257,133,'additional_leather',6),(258,133,'additional_fur',7);
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
) ENGINE=InnoDB AUTO_INCREMENT=655 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option_value`
--

LOCK TABLES `pim_catalog_attribute_option_value` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option_value` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option_value` VALUES (351,183,'en_US','1 star'),(352,183,'en_GB','1 star'),(353,183,'fr_FR','1 étoile'),(354,183,'de_DE','1 Stern'),(355,184,'en_US','2 stars'),(356,184,'en_GB','2 stars'),(357,184,'fr_FR','2 étoiles'),(358,184,'de_DE','2 Sterne'),(359,185,'en_US','3 stars'),(360,185,'en_GB','3 stars'),(361,185,'fr_FR','3 étoiles'),(362,185,'de_DE','3 Sterne'),(363,186,'en_US','4 stars'),(364,186,'en_GB','4 stars'),(365,186,'fr_FR','4 étoiles'),(366,186,'de_DE','4 Sterne'),(367,187,'en_US','5 stars'),(368,187,'en_GB','5 stars'),(369,187,'fr_FR','5 étoiles'),(370,187,'de_DE','5 Sternen'),(371,188,'en_US','XXS'),(372,188,'en_GB','XXS'),(373,188,'fr_FR','XXS'),(374,188,'de_DE','XXS'),(375,189,'en_US','XS'),(376,189,'en_GB','XS'),(377,189,'fr_FR','XS'),(378,189,'de_DE','XS'),(379,190,'en_US','S'),(380,190,'en_GB','S'),(381,190,'fr_FR','S'),(382,190,'de_DE','S'),(383,191,'en_US','M'),(384,191,'en_GB','M'),(385,191,'fr_FR','M'),(386,191,'de_DE','M'),(387,192,'en_US','L'),(388,192,'en_GB','L'),(389,192,'fr_FR','L'),(390,192,'de_DE','L'),(391,193,'en_US','XL'),(392,193,'en_GB','XL'),(393,193,'fr_FR','XL'),(394,193,'de_DE','XL'),(395,194,'en_US','2XL'),(396,194,'en_GB','2XL'),(397,194,'fr_FR','2XL'),(398,194,'de_DE','2XL'),(399,195,'en_US','3XL'),(400,195,'en_GB','3XL'),(401,195,'fr_FR','3XL'),(402,195,'de_DE','3XL'),(403,196,'en_US','XXS'),(404,196,'en_GB','XXS'),(405,196,'fr_FR','XXS'),(406,196,'de_DE','XXS'),(407,197,'en_US','XS'),(408,197,'en_GB','XS'),(409,197,'fr_FR','XS'),(410,197,'de_DE','XS'),(411,198,'en_US','S'),(412,198,'en_GB','S'),(413,198,'fr_FR','S'),(414,198,'de_DE','S'),(415,199,'en_US','M'),(416,199,'en_GB','M'),(417,199,'fr_FR','M'),(418,199,'de_DE','M'),(419,200,'en_US','L'),(420,200,'en_GB','L'),(421,200,'fr_FR','L'),(422,200,'de_DE','L'),(423,201,'en_US','XL'),(424,201,'en_GB','XL'),(425,201,'fr_FR','XL'),(426,201,'de_DE','XL'),(427,202,'en_US','2XL'),(428,202,'en_GB','2XL'),(429,202,'fr_FR','2XL'),(430,202,'de_DE','2XL'),(431,203,'en_US','3XL'),(432,203,'en_GB','3XL'),(433,203,'fr_FR','3XL'),(434,203,'de_DE','3XL'),(435,204,'en_US','XXS'),(436,204,'en_GB','XXS'),(437,204,'fr_FR','XXS'),(438,204,'de_DE','XXS'),(439,205,'en_US','XS'),(440,205,'en_GB','XS'),(441,205,'fr_FR','XS'),(442,205,'de_DE','XS'),(443,206,'en_US','S'),(444,206,'en_GB','S'),(445,206,'fr_FR','S'),(446,206,'de_DE','S'),(447,207,'en_US','M'),(448,207,'en_GB','M'),(449,207,'fr_FR','M'),(450,207,'de_DE','M'),(451,208,'en_US','L'),(452,208,'en_GB','L'),(453,208,'fr_FR','L'),(454,208,'de_DE','L'),(455,209,'en_US','XL'),(456,209,'en_GB','XL'),(457,209,'fr_FR','XL'),(458,209,'de_DE','XL'),(459,210,'en_US','2XL'),(460,210,'en_GB','2XL'),(461,210,'fr_FR','2XL'),(462,210,'de_DE','2XL'),(463,211,'en_US','3XL'),(464,211,'en_GB','3XL'),(465,211,'fr_FR','3XL'),(466,211,'de_DE','3XL'),(467,212,'en_US','Black'),(468,212,'en_GB','Black'),(469,212,'fr_FR','Noir'),(470,212,'de_DE','Schwarz'),(471,213,'en_US','White'),(472,213,'en_GB','White'),(473,213,'fr_FR','Blanc'),(474,213,'de_DE','Weiß'),(475,214,'en_US','Gray'),(476,214,'en_GB','Gray'),(477,214,'fr_FR','Gris'),(478,214,'de_DE','Grau'),(479,215,'en_US','Red'),(480,215,'en_GB','Red'),(481,215,'fr_FR','Rouge'),(482,215,'de_DE','Rot'),(483,216,'en_US','Blue'),(484,216,'en_GB','Blue'),(485,216,'fr_FR','Bleu'),(486,216,'de_DE','Blau'),(487,217,'en_US','Green'),(488,217,'en_GB','Green'),(489,217,'fr_FR','Vert'),(490,217,'de_DE','Grün'),(491,218,'en_US','Yellow'),(492,218,'en_GB','Yellow'),(493,218,'fr_FR','Jaune'),(494,218,'de_DE','Gelb'),(495,219,'en_US','Black'),(496,219,'en_GB','Black'),(497,219,'fr_FR','Noir'),(498,219,'de_DE','Schwarz'),(499,220,'en_US','White'),(500,220,'en_GB','White'),(501,220,'fr_FR','Blanc'),(502,220,'de_DE','Weiß'),(503,221,'en_US','Gray'),(504,221,'en_GB','Gray'),(505,221,'fr_FR','Gris'),(506,221,'de_DE','Grau'),(507,222,'en_US','Red'),(508,222,'en_GB','Red'),(509,222,'fr_FR','Rouge'),(510,222,'de_DE','Rot'),(511,223,'en_US','Blue'),(512,223,'en_GB','Blue'),(513,223,'fr_FR','Bleu'),(514,223,'de_DE','Blau'),(515,224,'en_US','Green'),(516,224,'en_GB','Green'),(517,224,'fr_FR','Vert'),(518,224,'de_DE','Grün'),(519,225,'en_US','Yellow'),(520,225,'en_GB','Yellow'),(521,225,'fr_FR','Jaune'),(522,225,'de_DE','Gelb'),(523,226,'en_US','American apparel'),(524,226,'en_GB','American apparel'),(525,226,'fr_FR','American apparel'),(526,226,'de_DE','American apparel'),(527,227,'en_US','Lacoste'),(528,227,'en_GB','Lacoste'),(529,227,'fr_FR','Lacoste'),(530,227,'de_DE','Lacoste'),(531,228,'en_US','Columbia'),(532,228,'en_GB','Columbia'),(533,228,'fr_FR','Columbia'),(534,228,'de_DE','Columbia'),(535,229,'en_US','Levi Strauss'),(536,229,'en_GB','Levi Strauss'),(537,229,'fr_FR','Levi Strauss'),(538,229,'de_DE','Levi Strauss'),(539,230,'en_US','Diesel'),(540,230,'en_GB','Diesel'),(541,230,'fr_FR','Diesel'),(542,230,'de_DE','Diesel'),(543,231,'en_US','Prada'),(544,231,'en_GB','Prada'),(545,231,'fr_FR','Prada'),(546,231,'de_DE','Prada'),(547,232,'en_US','Marc O’Polo'),(548,232,'en_GB','Marc O’Polo'),(549,232,'fr_FR','Marc O’Polo'),(550,232,'de_DE','Marc O’Polo'),(551,233,'en_US','USA'),(552,233,'en_GB','USA'),(553,233,'fr_FR','USA'),(554,233,'de_DE','USA'),(555,234,'en_US','France'),(556,234,'en_GB','France'),(557,234,'fr_FR','France'),(558,234,'de_DE','Frankreich'),(559,235,'en_US','Germany'),(560,235,'en_GB','Germany'),(561,235,'fr_FR','Allemagne'),(562,235,'de_DE','Deutschland'),(563,236,'en_US','Italy'),(564,236,'en_GB','Italy'),(565,236,'fr_FR','Italie'),(566,236,'de_DE','Italien'),(567,237,'en_US','China'),(568,237,'en_GB','China'),(569,237,'fr_FR','Chine'),(570,237,'de_DE','China'),(571,238,'en_US','Taiwan'),(572,238,'en_GB','Taiwan'),(573,238,'fr_FR','Taiwan'),(574,238,'de_DE','Taiwan'),(575,239,'en_US','Vietnam'),(576,239,'en_GB','Vietnam'),(577,239,'fr_FR','Vietnam'),(578,239,'de_DE','Vietnam'),(579,240,'en_US','Dry'),(580,240,'en_GB','Dry'),(581,240,'fr_FR','Sec'),(582,240,'de_DE','Trocken'),(583,241,'en_US','Wet'),(584,241,'en_GB','Wet'),(585,241,'fr_FR','Humide'),(586,241,'de_DE','Nass'),(587,242,'en_US','Hot'),(588,242,'en_GB','Hot'),(589,242,'fr_FR','Chaud'),(590,242,'de_DE','Heiß'),(591,243,'en_US','Cold'),(592,243,'en_GB','Cold'),(593,243,'fr_FR','Froid'),(594,243,'de_DE','Kalt'),(595,244,'en_US','Snowy'),(596,244,'en_GB','Snowy'),(597,244,'fr_FR','Neigeux'),(598,244,'de_DE','Verschneit'),(599,245,'en_US','Cotton'),(600,245,'en_GB','Cotton'),(601,245,'fr_FR','Coton'),(602,245,'de_DE','Baumwolle'),(603,246,'en_US','Flax'),(604,246,'en_GB','Flax'),(605,246,'fr_FR','Lin'),(606,246,'de_DE','Flachs'),(607,247,'en_US','Wool'),(608,247,'en_GB','Wool'),(609,247,'fr_FR','Laine'),(610,247,'de_DE','Wolle'),(611,248,'en_US','Silk'),(612,248,'en_GB','Silk'),(613,248,'fr_FR','Soie'),(614,248,'de_DE','Seide'),(615,249,'en_US','Denim'),(616,249,'en_GB','Denim'),(617,249,'fr_FR','Toile'),(618,249,'de_DE','Denim'),(619,250,'en_US','Leather'),(620,250,'en_GB','Leather'),(621,250,'fr_FR','Cuir'),(622,250,'de_DE','Leder'),(623,251,'en_US','Fur'),(624,251,'en_GB','Fur'),(625,251,'fr_FR','Fourrure'),(626,251,'de_DE','Pelz'),(627,252,'en_US','Cotton'),(628,252,'en_GB','Cotton'),(629,252,'fr_FR','Coton'),(630,252,'de_DE','Baumwolle'),(631,253,'en_US','Flax'),(632,253,'en_GB','Flax'),(633,253,'fr_FR','Lin'),(634,253,'de_DE','Flachs'),(635,254,'en_US','Wool'),(636,254,'en_GB','Wool'),(637,254,'fr_FR','Laine'),(638,254,'de_DE','Wolle'),(639,255,'en_US','Silk'),(640,255,'en_GB','Silk'),(641,255,'fr_FR','Soie'),(642,255,'de_DE','Seide'),(643,256,'en_US','Denim'),(644,256,'en_GB','Denim'),(645,256,'fr_FR','Toile'),(646,256,'de_DE','Denim'),(647,257,'en_US','Leather'),(648,257,'en_GB','Leather'),(649,257,'fr_FR','Cuir'),(650,257,'de_DE','Leder'),(651,258,'en_US','Fur'),(652,258,'en_GB','Fur'),(653,258,'fr_FR','Fourrure'),(654,258,'de_DE','Pelz');
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
) ENGINE=InnoDB AUTO_INCREMENT=365 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

LOCK TABLES `pim_catalog_attribute_requirement` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_requirement` VALUES (281,17,111,14,1),(282,17,111,16,1),(283,17,111,15,1),(284,17,112,14,1),(285,17,113,14,1),(286,17,117,14,1),(287,17,120,14,1),(288,17,123,14,1),(289,17,125,14,1),(290,17,132,14,1),(291,17,112,15,1),(292,17,117,15,1),(293,17,120,15,1),(294,17,123,15,1),(295,17,125,15,1),(296,17,112,16,1),(297,17,113,16,1),(298,17,117,16,1),(299,17,120,16,1),(300,17,123,16,1),(301,17,125,16,1),(302,17,126,16,1),(303,17,132,16,1),(304,18,111,14,1),(305,18,111,16,1),(306,18,111,15,1),(307,18,112,14,1),(308,18,113,14,1),(309,18,117,14,1),(310,18,120,14,1),(311,18,123,14,1),(312,18,125,14,1),(313,18,132,14,1),(314,18,112,15,1),(315,18,117,15,1),(316,18,120,15,1),(317,18,123,15,1),(318,18,125,15,1),(319,18,112,16,1),(320,18,113,16,1),(321,18,117,16,1),(322,18,120,16,1),(323,18,123,16,1),(324,18,125,16,1),(325,18,126,16,1),(326,18,132,16,1),(327,19,111,14,1),(328,19,111,16,1),(329,19,111,15,1),(330,19,112,14,1),(331,19,113,14,1),(332,19,117,14,1),(333,19,121,14,1),(334,19,122,14,1),(335,19,123,14,1),(336,19,125,14,1),(337,19,132,14,1),(338,19,112,15,1),(339,19,117,15,1),(340,19,121,15,1),(341,19,122,15,1),(342,19,123,15,1),(343,19,125,15,1),(344,19,112,16,1),(345,19,113,16,1),(346,19,117,16,1),(347,19,121,16,1),(348,19,122,16,1),(349,19,123,16,1),(350,19,125,16,1),(351,19,126,16,1),(352,19,128,16,1),(353,19,132,16,1),(354,20,111,14,1),(355,20,111,16,1),(356,20,111,15,1),(357,20,112,14,1),(358,20,113,14,1),(359,20,117,14,1),(360,20,112,15,1),(361,20,117,15,1),(362,20,112,16,1),(363,20,113,16,1),(364,20,117,16,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=350 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_translation`
--

LOCK TABLES `pim_catalog_attribute_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (230,111,'SKU','en_US'),(231,111,'SKU','en_GB'),(232,111,'SKU','fr_FR'),(233,111,'SKU','de_DE'),(234,112,'Name','en_US'),(235,112,'Name','en_GB'),(236,112,'Nom','fr_FR'),(237,112,'Name','de_DE'),(238,113,'Description','en_US'),(239,113,'Description','en_GB'),(240,113,'Description','fr_FR'),(241,113,'Beschreibung','de_DE'),(242,114,'Thumbnail','en_US'),(243,114,'Thumbnail','en_GB'),(244,114,'Imagette','fr_FR'),(245,114,'Miniaturansicht','de_DE'),(246,115,'Image','en_US'),(247,115,'Image','en_GB'),(248,115,'Image','fr_FR'),(249,115,'Bild','de_DE'),(250,116,'Legend','en_US'),(251,116,'Legend','en_GB'),(252,116,'Légende','fr_FR'),(253,116,'Legende','de_DE'),(254,117,'Price','en_US'),(255,117,'Price','en_GB'),(256,117,'Prix','fr_FR'),(257,117,'Preis','de_DE'),(258,118,'Customer rating','en_US'),(259,118,'Customer rating','en_GB'),(260,118,'Évaluation du client','fr_FR'),(261,118,'Kundenbewertung','de_DE'),(262,119,'Release date','en_US'),(263,119,'Release date','en_GB'),(264,119,'Date de sortie','fr_FR'),(265,119,'Erscheinungsdatum','de_DE'),(266,120,'Size','en_US'),(267,120,'Size','en_GB'),(268,120,'Taille','fr_FR'),(269,120,'Größe','de_DE'),(270,121,'Chest size','en_US'),(271,121,'Chest size','en_GB'),(272,121,'Tour de poitrine','fr_FR'),(273,121,'Brustumfang','de_DE'),(274,122,'Waist size','en_US'),(275,122,'Waist size','en_GB'),(276,122,'Tour de taille','fr_FR'),(277,122,'Taillengröße','de_DE'),(278,123,'Color','en_US'),(279,123,'Colour','en_GB'),(280,123,'Couleur','fr_FR'),(281,123,'Farbe','de_DE'),(282,124,'Additional colors','en_US'),(283,124,'Additional colours','en_GB'),(284,124,'Autres couleurs','fr_FR'),(285,124,'Zusätzliche Farben','de_DE'),(286,125,'Manufacturer','en_US'),(287,125,'Manufacturer','en_GB'),(288,125,'Fabricant','fr_FR'),(289,125,'Hersteller','de_DE'),(290,126,'Country of manufacture','en_US'),(291,126,'Country of manufacture','en_GB'),(292,126,'Pays de fabrication','fr_FR'),(293,126,'Herstellungsland','de_DE'),(294,127,'Handmade','en_US'),(295,127,'Handmade','en_GB'),(296,127,'Fait main','fr_FR'),(297,127,'Handgefertigt','de_DE'),(298,128,'Weather conditions','en_US'),(299,128,'Weather conditions','en_GB'),(300,128,'Conditions météorologiques','fr_FR'),(301,128,'Wetterverhältnisse','de_DE'),(302,129,'Care instructions','en_US'),(303,129,'Care instructions','en_GB'),(304,129,'Conseils d\'entretien','fr_FR'),(305,129,'Pflegehinweise','de_DE'),(306,130,'Washing temperature','en_US'),(307,130,'Washing temperature','en_GB'),(308,130,'Température de lavage','fr_FR'),(309,130,'Waschtemperatur','de_DE'),(310,131,'Weight','en_US'),(311,131,'Weight','en_GB'),(312,131,'Poids','fr_FR'),(313,131,'Gewicht','de_DE'),(314,132,'Material','en_US'),(315,132,'Material','en_GB'),(316,132,'Matériau','fr_FR'),(317,132,'Material','de_DE'),(318,133,'Additional materials','en_US'),(319,133,'Additional materials','en_GB'),(320,133,'Matériels supplémentaires','fr_FR'),(321,133,'Zusätzliche Materialien','de_DE'),(322,134,'Cost','en_US'),(323,134,'Cost','en_GB'),(324,134,'Coût','fr_FR'),(325,134,'Kosten','de_DE'),(326,135,'Number in stock','en_US'),(327,135,'Number in stock','en_GB'),(328,135,'Nombre en stock','fr_FR'),(329,135,'Anzahl auf Lager','de_DE'),(330,136,'Customs tax','en_US'),(331,136,'Customs tax','en_GB'),(332,136,'Taxe de douanes','fr_FR'),(333,136,'Zollsteuer','de_DE'),(334,137,'Under European law','en_US'),(335,137,'Under European law','en_GB'),(336,137,'Sous la loi Européenne','fr_FR'),(337,137,'Nach europäischem Recht','de_DE'),(338,138,'Datasheet','en_US'),(339,138,'Datasheet','en_GB'),(340,138,'Fiche technique','fr_FR'),(341,138,'Datenblatt','de_DE'),(342,139,'Attachment','en_US'),(343,139,'Attachment','en_GB'),(344,139,'Attachements','fr_FR'),(345,139,'Befestigung','de_DE'),(346,140,'Localizable image','en_US'),(347,140,'Localizable image','en_GB'),(348,140,'Image localisée','fr_FR'),(349,140,'Lokalisiertes Bild','de_DE');
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
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category`
--

LOCK TABLES `pim_catalog_category` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
INSERT INTO `pim_catalog_category` VALUES (73,NULL,'2013_collection','2018-06-03 13:05:11',73,0,1,6),(74,NULL,'2014_collection','2018-06-03 13:05:11',74,0,1,6),(75,NULL,'2015_collection','2018-06-03 13:05:11',75,0,1,22),(76,73,'men_2013','2018-06-03 13:05:11',73,1,2,3),(77,73,'women_2013','2018-06-03 13:05:11',73,1,4,5),(78,74,'men_2014','2018-06-03 13:05:11',74,1,2,3),(79,74,'women_2014','2018-06-03 13:05:11',74,1,4,5),(80,75,'men_2015','2018-06-03 13:05:11',75,1,2,11),(81,75,'women_2015','2018-06-03 13:05:11',75,1,12,21),(82,80,'men_2015_spring','2018-06-03 13:05:11',75,2,3,4),(83,80,'men_2015_summer','2018-06-03 13:05:11',75,2,5,6),(84,80,'men_2015_autumn','2018-06-03 13:05:11',75,2,7,8),(85,80,'men_2015_winter','2018-06-03 13:05:11',75,2,9,10),(86,81,'women_2015_spring','2018-06-03 13:05:11',75,2,13,14),(87,81,'women_2015_summer','2018-06-03 13:05:11',75,2,15,16),(88,81,'women_2015_autumn','2018-06-03 13:05:11',75,2,17,18),(89,81,'women_2015_winter','2018-06-03 13:05:12',75,2,19,20);
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
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_translation`
--

LOCK TABLES `pim_catalog_category_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_translation` VALUES (194,73,'2013 collection','en_US'),(195,73,'2013 collection','en_GB'),(196,74,'2014 collection','en_US'),(197,74,'2014 collection','en_GB'),(198,74,'Collection 2014','fr_FR'),(199,74,'2014 Kollektion','de_DE'),(200,75,'2015 collection','en_US'),(201,75,'Kollektion','de_DE'),(202,76,'2013 men\'s collection','en_US'),(203,76,'2013 men\'s collection','en_GB'),(204,77,'2013 women\'s collection','en_US'),(205,77,'2013 women\'s collection','en_GB'),(206,78,'2014 men\'s collection','en_US'),(207,78,'2014 men\'s collection','en_GB'),(208,78,'La collection de 2014 hommes','fr_FR'),(209,78,'2014 Männer-Kollektion','de_DE'),(210,79,'2014 women\'s collection','en_US'),(211,79,'2014 women\'s collection','en_GB'),(212,79,'La collection de 2014 femmes','fr_FR'),(213,79,'2014 Damenkollektion','de_DE'),(214,80,'2015 men\'s collection','en_US'),(215,80,'2015 Männer-Kollektion','de_DE'),(216,81,'2015 women\'s collection','en_US'),(217,81,'2015 Damenkollektion','de_DE'),(218,82,'2015 men\'s spring collection','en_US'),(219,82,'2015 Männer-Frühjahrskollektion','de_DE'),(220,83,'2015 men\'s summer collection','en_US'),(221,83,'2015 Männer-Sommer-Kollektion','de_DE'),(222,84,'2015 men\'s autumn collection','en_US'),(223,84,'2015 Männer-Herbstkollektion','de_DE'),(224,85,'2015 men\'s winter collection','en_US'),(225,85,'2015 Männer-Winterkollektion','de_DE'),(226,86,'2015 women\'s spring collection','en_US'),(227,86,'2015 Damen-Frühjahrskollektion','de_DE'),(228,87,'2015 women\'s summer collection','en_US'),(229,87,'2015 Damen-Sommer-Kollektion','de_DE'),(230,88,'2015 women\'s autumn collection','en_US'),(231,88,'2015 Damen-Herbstkollektion','de_DE'),(232,89,'2015 women\'s winter collection','en_US'),(233,89,'2015 Damen-Winterkollektion','de_DE');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel`
--

LOCK TABLES `pim_catalog_channel` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel` VALUES (14,74,'ecommerce','a:0:{}'),(15,73,'tablet','a:0:{}'),(16,75,'print','a:0:{}');
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
INSERT INTO `pim_catalog_channel_currency` VALUES (14,1765),(14,1766),(14,1767),(15,1765),(15,1767),(16,1765),(16,1766);
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
INSERT INTO `pim_catalog_channel_locale` VALUES (14,1299),(14,1309),(14,1318),(14,1350),(15,1309),(15,1318),(16,1299),(16,1318);
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_translation`
--

LOCK TABLES `pim_catalog_channel_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_translation` VALUES (40,14,'Ecommerce','en_US'),(41,14,'Ecommerce','de_DE'),(42,14,'Ecommerce','fr_FR'),(43,15,'Tablet','en_US'),(44,15,'Tablet','de_DE'),(45,15,'Tablette','fr_FR'),(46,16,'Print','en_US'),(47,16,'Drucken','de_DE'),(48,16,'Impression','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=4357 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2059 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_currency`
--

LOCK TABLES `pim_catalog_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (1765,'USD',1),(1766,'EUR',1),(1767,'GBP',1),(1768,'ADP',0),(1769,'AED',0),(1770,'AFA',0),(1771,'AFN',0),(1772,'ALK',0),(1773,'ALL',0),(1774,'AMD',0),(1775,'ANG',0),(1776,'AOA',0),(1777,'AOK',0),(1778,'AON',0),(1779,'AOR',0),(1780,'ARA',0),(1781,'ARL',0),(1782,'ARM',0),(1783,'ARP',0),(1784,'ARS',0),(1785,'ATS',0),(1786,'AUD',0),(1787,'AWG',0),(1788,'AZM',0),(1789,'AZN',0),(1790,'BAD',0),(1791,'BAM',0),(1792,'BAN',0),(1793,'BBD',0),(1794,'BDT',0),(1795,'BEC',0),(1796,'BEF',0),(1797,'BEL',0),(1798,'BGL',0),(1799,'BGM',0),(1800,'BGN',0),(1801,'BGO',0),(1802,'BHD',0),(1803,'BIF',0),(1804,'BMD',0),(1805,'BND',0),(1806,'BOB',0),(1807,'BOL',0),(1808,'BOP',0),(1809,'BOV',0),(1810,'BRB',0),(1811,'BRC',0),(1812,'BRE',0),(1813,'BRL',0),(1814,'BRN',0),(1815,'BRR',0),(1816,'BRZ',0),(1817,'BSD',0),(1818,'BTN',0),(1819,'BUK',0),(1820,'BWP',0),(1821,'BYB',0),(1822,'BYR',0),(1823,'BZD',0),(1824,'CAD',0),(1825,'CDF',0),(1826,'CHE',0),(1827,'CHF',0),(1828,'CHW',0),(1829,'CLE',0),(1830,'CLF',0),(1831,'CLP',0),(1832,'CNX',0),(1833,'CNY',0),(1834,'COP',0),(1835,'COU',0),(1836,'CRC',0),(1837,'CSD',0),(1838,'CSK',0),(1839,'CUC',0),(1840,'CUP',0),(1841,'CVE',0),(1842,'CYP',0),(1843,'CZK',0),(1844,'DDM',0),(1845,'DEM',0),(1846,'DJF',0),(1847,'DKK',0),(1848,'DOP',0),(1849,'DZD',0),(1850,'ECS',0),(1851,'ECV',0),(1852,'EEK',0),(1853,'EGP',0),(1854,'ERN',0),(1855,'ESA',0),(1856,'ESB',0),(1857,'ESP',0),(1858,'ETB',0),(1859,'FIM',0),(1860,'FJD',0),(1861,'FKP',0),(1862,'FRF',0),(1863,'GEK',0),(1864,'GEL',0),(1865,'GHC',0),(1866,'GHS',0),(1867,'GIP',0),(1868,'GMD',0),(1869,'GNF',0),(1870,'GNS',0),(1871,'GQE',0),(1872,'GRD',0),(1873,'GTQ',0),(1874,'GWE',0),(1875,'GWP',0),(1876,'GYD',0),(1877,'HKD',0),(1878,'HNL',0),(1879,'HRD',0),(1880,'HRK',0),(1881,'HTG',0),(1882,'HUF',0),(1883,'IDR',0),(1884,'IEP',0),(1885,'ILP',0),(1886,'ILR',0),(1887,'ILS',0),(1888,'INR',0),(1889,'IQD',0),(1890,'IRR',0),(1891,'ISJ',0),(1892,'ISK',0),(1893,'ITL',0),(1894,'JMD',0),(1895,'JOD',0),(1896,'JPY',0),(1897,'KES',0),(1898,'KGS',0),(1899,'KHR',0),(1900,'KMF',0),(1901,'KPW',0),(1902,'KRH',0),(1903,'KRO',0),(1904,'KRW',0),(1905,'KWD',0),(1906,'KYD',0),(1907,'KZT',0),(1908,'LAK',0),(1909,'LBP',0),(1910,'LKR',0),(1911,'LRD',0),(1912,'LSL',0),(1913,'LTL',0),(1914,'LTT',0),(1915,'LUC',0),(1916,'LUF',0),(1917,'LUL',0),(1918,'LVL',0),(1919,'LVR',0),(1920,'LYD',0),(1921,'MAD',0),(1922,'MAF',0),(1923,'MCF',0),(1924,'MDC',0),(1925,'MDL',0),(1926,'MGA',0),(1927,'MGF',0),(1928,'MKD',0),(1929,'MKN',0),(1930,'MLF',0),(1931,'MMK',0),(1932,'MNT',0),(1933,'MOP',0),(1934,'MRO',0),(1935,'MTL',0),(1936,'MTP',0),(1937,'MUR',0),(1938,'MVP',0),(1939,'MVR',0),(1940,'MWK',0),(1941,'MXN',0),(1942,'MXP',0),(1943,'MXV',0),(1944,'MYR',0),(1945,'MZE',0),(1946,'MZM',0),(1947,'MZN',0),(1948,'NAD',0),(1949,'NGN',0),(1950,'NIC',0),(1951,'NIO',0),(1952,'NLG',0),(1953,'NOK',0),(1954,'NPR',0),(1955,'NZD',0),(1956,'OMR',0),(1957,'PAB',0),(1958,'PEI',0),(1959,'PEN',0),(1960,'PES',0),(1961,'PGK',0),(1962,'PHP',0),(1963,'PKR',0),(1964,'PLN',0),(1965,'PLZ',0),(1966,'PTE',0),(1967,'PYG',0),(1968,'QAR',0),(1969,'RHD',0),(1970,'ROL',0),(1971,'RON',0),(1972,'RSD',0),(1973,'RUB',0),(1974,'RUR',0),(1975,'RWF',0),(1976,'SAR',0),(1977,'SBD',0),(1978,'SCR',0),(1979,'SDD',0),(1980,'SDG',0),(1981,'SDP',0),(1982,'SEK',0),(1983,'SGD',0),(1984,'SHP',0),(1985,'SIT',0),(1986,'SKK',0),(1987,'SLL',0),(1988,'SOS',0),(1989,'SRD',0),(1990,'SRG',0),(1991,'SSP',0),(1992,'STD',0),(1993,'SUR',0),(1994,'SVC',0),(1995,'SYP',0),(1996,'SZL',0),(1997,'THB',0),(1998,'TJR',0),(1999,'TJS',0),(2000,'TMM',0),(2001,'TMT',0),(2002,'TND',0),(2003,'TOP',0),(2004,'TPE',0),(2005,'TRL',0),(2006,'TRY',0),(2007,'TTD',0),(2008,'TWD',0),(2009,'TZS',0),(2010,'UAH',0),(2011,'UAK',0),(2012,'UGS',0),(2013,'UGX',0),(2014,'USN',0),(2015,'USS',0),(2016,'UYI',0),(2017,'UYP',0),(2018,'UYU',0),(2019,'UZS',0),(2020,'VEB',0),(2021,'VEF',0),(2022,'VND',0),(2023,'VNN',0),(2024,'VUV',0),(2025,'WST',0),(2026,'XAF',0),(2027,'XAU',0),(2028,'XBA',0),(2029,'XBB',0),(2030,'XBC',0),(2031,'XBD',0),(2032,'XCD',0),(2033,'XDR',0),(2034,'XEU',0),(2035,'XFO',0),(2036,'XFU',0),(2037,'XOF',0),(2038,'XPD',0),(2039,'XPF',0),(2040,'XRE',0),(2041,'XSU',0),(2042,'XTS',0),(2043,'XXX',0),(2044,'YDD',0),(2045,'YER',0),(2046,'YUD',0),(2047,'YUM',0),(2048,'YUN',0),(2049,'YUR',0),(2050,'ZAL',0),(2051,'ZAR',0),(2052,'ZMK',0),(2053,'ZMW',0),(2054,'ZRN',0),(2055,'ZRZ',0),(2056,'ZWD',0),(2057,'ZWL',0),(2058,'ZWR',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family`
--

LOCK TABLES `pim_catalog_family` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
INSERT INTO `pim_catalog_family` VALUES (17,112,NULL,'tshirts','2018-06-03 13:05:14','2018-06-03 13:05:14'),(18,112,NULL,'sweaters','2018-06-03 13:05:14','2018-06-03 13:05:14'),(19,112,NULL,'jackets','2018-06-03 13:05:14','2018-06-03 13:05:14'),(20,112,NULL,'sandals','2018-06-03 13:05:14','2018-06-03 13:05:14');
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
INSERT INTO `pim_catalog_family_attribute` VALUES (17,111),(17,112),(17,113),(17,114),(17,115),(17,116),(17,117),(17,118),(17,119),(17,120),(17,123),(17,124),(17,125),(17,126),(17,127),(17,130),(17,131),(17,132),(17,134),(17,135),(17,136),(17,138),(18,111),(18,112),(18,113),(18,114),(18,115),(18,117),(18,118),(18,119),(18,120),(18,123),(18,124),(18,125),(18,126),(18,127),(18,129),(18,130),(18,131),(18,132),(18,133),(18,134),(18,135),(18,136),(19,111),(19,112),(19,113),(19,114),(19,115),(19,117),(19,118),(19,119),(19,121),(19,122),(19,123),(19,124),(19,125),(19,126),(19,127),(19,128),(19,129),(19,130),(19,131),(19,132),(19,133),(19,134),(19,135),(19,136),(20,111),(20,112),(20,113),(20,117);
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_translation`
--

LOCK TABLES `pim_catalog_family_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_family_translation` VALUES (29,17,'T-shirts','en_US'),(30,17,'T-shirts','en_GB'),(31,17,'T-shirts','fr_FR'),(32,17,'T-shirts','de_DE'),(33,18,'Sweaters','en_US'),(34,18,'Chandails','en_GB'),(35,18,'Sweaters','fr_FR'),(36,18,'Pullovern','de_DE'),(37,19,'Jackets','en_US'),(38,19,'Jackets','en_GB'),(39,19,'Vestes','fr_FR'),(40,19,'Jacken','de_DE'),(41,20,'Sandals','en_US'),(42,20,'Sandals','en_GB'),(43,20,'Sandals','fr_FR'),(44,20,'Sandals','de_DE');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group`
--

LOCK TABLES `pim_catalog_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_group` VALUES (5,9,'similar'),(6,10,'related'),(7,11,'cross_sell'),(8,12,'upsell'),(9,13,'substitute');
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_translation`
--

LOCK TABLES `pim_catalog_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_translation` VALUES (9,5,'Similar','en_US'),(10,5,'Similar','en_GB'),(11,5,'Similaire','fr_FR'),(12,5,'Ähnlich','de_DE'),(13,6,'Related','en_US'),(14,6,'Related','en_GB'),(15,6,'Connexe','fr_FR'),(16,6,'Verbunden','de_DE'),(17,7,'Cross sell','en_US'),(18,7,'Cross sell','en_GB'),(19,7,'Vente croisée','fr_FR'),(20,7,'Cross-Selling','de_DE'),(21,8,'Upsell','en_US'),(22,8,'Upsell','en_GB'),(23,8,'Vente incitative','fr_FR'),(24,8,'Upsell','de_DE'),(25,9,'Substitute','en_US'),(26,9,'Substitute','en_GB'),(27,9,'Substitut','fr_FR'),(28,9,'Ersatz','de_DE');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type`
--

LOCK TABLES `pim_catalog_group_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type` VALUES (11,'cross_sell'),(10,'related'),(9,'similar'),(13,'substitute'),(12,'upsell');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=1471 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_locale`
--

LOCK TABLES `pim_catalog_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_locale` VALUES (1261,'af_ZA',0),(1262,'am_ET',0),(1263,'ar_AE',0),(1264,'ar_BH',0),(1265,'ar_DZ',0),(1266,'ar_EG',0),(1267,'ar_IQ',0),(1268,'ar_JO',0),(1269,'ar_KW',0),(1270,'ar_LB',0),(1271,'ar_LY',0),(1272,'ar_MA',0),(1273,'arn_CL',0),(1274,'ar_OM',0),(1275,'ar_QA',0),(1276,'ar_SA',0),(1277,'ar_SY',0),(1278,'ar_TN',0),(1279,'ar_YE',0),(1280,'as_IN',0),(1281,'az_Cyrl_AZ',0),(1282,'az_Latn_AZ',0),(1283,'ba_RU',0),(1284,'be_BY',0),(1285,'bg_BG',0),(1286,'bn_BD',0),(1287,'bn_IN',0),(1288,'bo_CN',0),(1289,'br_FR',0),(1290,'bs_Cyrl_BA',0),(1291,'bs_Latn_BA',0),(1292,'ca_ES',0),(1293,'co_FR',0),(1294,'cs_CZ',0),(1295,'cy_GB',0),(1296,'da_DK',0),(1297,'de_AT',0),(1298,'de_CH',0),(1299,'de_DE',1),(1300,'de_LI',0),(1301,'de_LU',0),(1302,'dsb_DE',0),(1303,'dv_MV',0),(1304,'el_GR',0),(1305,'en_029',0),(1306,'en_AU',0),(1307,'en_BZ',0),(1308,'en_CA',0),(1309,'en_GB',1),(1310,'en_IE',0),(1311,'en_IN',0),(1312,'en_JM',0),(1313,'en_MY',0),(1314,'en_NZ',0),(1315,'en_PH',0),(1316,'en_SG',0),(1317,'en_TT',0),(1318,'en_US',1),(1319,'en_ZA',0),(1320,'en_ZW',0),(1321,'es_AR',0),(1322,'es_BO',0),(1323,'es_CL',0),(1324,'es_CO',0),(1325,'es_CR',0),(1326,'es_DO',0),(1327,'es_EC',0),(1328,'es_ES',0),(1329,'es_GT',0),(1330,'es_HN',0),(1331,'es_MX',0),(1332,'es_NI',0),(1333,'es_PA',0),(1334,'es_PE',0),(1335,'es_PR',0),(1336,'es_PY',0),(1337,'es_SV',0),(1338,'es_US',0),(1339,'es_UY',0),(1340,'es_VE',0),(1341,'et_EE',0),(1342,'eu_ES',0),(1343,'fa_IR',0),(1344,'fi_FI',0),(1345,'fil_PH',0),(1346,'fo_FO',0),(1347,'fr_BE',0),(1348,'fr_CA',0),(1349,'fr_CH',0),(1350,'fr_FR',1),(1351,'fr_LU',0),(1352,'fr_MC',0),(1353,'fy_NL',0),(1354,'ga_IE',0),(1355,'gd_GB',0),(1356,'gl_ES',0),(1357,'gsw_FR',0),(1358,'gu_IN',0),(1359,'ha_Latn_NG',0),(1360,'he_IL',0),(1361,'hi_IN',0),(1362,'hr_BA',0),(1363,'hr_HR',0),(1364,'hsb_DE',0),(1365,'hu_HU',0),(1366,'hy_AM',0),(1367,'id_ID',0),(1368,'ig_NG',0),(1369,'ii_CN',0),(1370,'is_IS',0),(1371,'it_CH',0),(1372,'it_IT',0),(1373,'iu_Cans_CA',0),(1374,'iu_Latn_CA',0),(1375,'ja_JP',0),(1376,'ka_GE',0),(1377,'kk_KZ',0),(1378,'kl_GL',0),(1379,'km_KH',0),(1380,'kn_IN',0),(1381,'kok_IN',0),(1382,'ko_KR',0),(1383,'ky_KG',0),(1384,'lb_LU',0),(1385,'lo_LA',0),(1386,'lt_LT',0),(1387,'lv_LV',0),(1388,'mi_NZ',0),(1389,'mk_MK',0),(1390,'ml_IN',0),(1391,'mn_MN',0),(1392,'mn_Mong_CN',0),(1393,'moh_CA',0),(1394,'mr_IN',0),(1395,'ms_BN',0),(1396,'ms_MY',0),(1397,'mt_MT',0),(1398,'nb_NO',0),(1399,'ne_NP',0),(1400,'nl_BE',0),(1401,'nl_NL',0),(1402,'nn_NO',0),(1403,'nso_ZA',0),(1404,'oc_FR',0),(1405,'or_IN',0),(1406,'pa_IN',0),(1407,'pl_PL',0),(1408,'prs_AF',0),(1409,'ps_AF',0),(1410,'pt_BR',0),(1411,'pt_PT',0),(1412,'qut_GT',0),(1413,'quz_BO',0),(1414,'quz_EC',0),(1415,'quz_PE',0),(1416,'rm_CH',0),(1417,'ro_RO',0),(1418,'ru_RU',0),(1419,'rw_RW',0),(1420,'sah_RU',0),(1421,'sa_IN',0),(1422,'se_FI',0),(1423,'se_NO',0),(1424,'se_SE',0),(1425,'si_LK',0),(1426,'sk_SK',0),(1427,'sl_SI',0),(1428,'sma_NO',0),(1429,'sma_SE',0),(1430,'smj_NO',0),(1431,'smj_SE',0),(1432,'smn_FI',0),(1433,'sms_FI',0),(1434,'sq_AL',0),(1435,'sr_Cyrl_BA',0),(1436,'sr_Cyrl_CS',0),(1437,'sr_Cyrl_ME',0),(1438,'sr_Cyrl_RS',0),(1439,'sr_Latn_BA',0),(1440,'sr_Latn_CS',0),(1441,'sr_Latn_ME',0),(1442,'sr_Latn_RS',0),(1443,'sv_FI',0),(1444,'sv_SE',0),(1445,'sw_KE',0),(1446,'syr_SY',0),(1447,'ta_IN',0),(1448,'te_IN',0),(1449,'tg_Cyrl_TJ',0),(1450,'th_TH',0),(1451,'tk_TM',0),(1452,'tn_ZA',0),(1453,'tr_TR',0),(1454,'tt_RU',0),(1455,'tzm_Latn_DZ',0),(1456,'ug_CN',0),(1457,'uk_UA',0),(1458,'ur_PK',0),(1459,'uz_Cyrl_UZ',0),(1460,'uz_Latn_UZ',0),(1461,'vi_VN',0),(1462,'wo_SN',0),(1463,'xh_ZA',0),(1464,'yo_NG',0),(1465,'zh_CN',0),(1466,'zh_HK',0),(1467,'zh_MO',0),(1468,'zh_SG',0),(1469,'zh_TW',0),(1470,'zu_ZA',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=485 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=969 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2925 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_versioning_version`
--

LOCK TABLES `pim_versioning_version` WRITE;
/*!40000 ALTER TABLE `pim_versioning_version` DISABLE KEYS */;
INSERT INTO `pim_versioning_version` VALUES (2572,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','353','a:5:{s:4:\"code\";s:22:\"fixtures_currency_csv0\";s:5:\"label\";s:24:\"Currencies data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_currency_csv0\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Currencies data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2573,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','354','a:5:{s:4:\"code\";s:20:\"fixtures_locale_csv1\";s:5:\"label\";s:21:\"Locales data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:333:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_locale_csv1\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Locales data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:333:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2574,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','355','a:5:{s:4:\"code\";s:24:\"fixtures_user_group_csv2\";s:5:\"label\";s:25:\"User groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_group_csv2\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"User groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2575,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','356','a:5:{s:4:\"code\";s:22:\"fixtures_category_csv3\";s:5:\"label\";s:24:\"Categories data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_category_csv3\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Categories data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2576,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','357','a:5:{s:4:\"code\";s:29:\"fixtures_attribute_group_csv4\";s:5:\"label\";s:30:\"Attribute groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:342:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_attribute_group_csv4\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"Attribute groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:342:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2577,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','358','a:5:{s:4:\"code\";s:24:\"fixtures_group_type_csv5\";s:5:\"label\";s:25:\"Group types data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_group_type_csv5\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Group types data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2578,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','359','a:5:{s:4:\"code\";s:23:\"fixtures_attribute_csv6\";s:5:\"label\";s:24:\"Attributes data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"fixtures_attribute_csv6\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Attributes data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2579,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','360','a:5:{s:4:\"code\";s:21:\"fixtures_channel_csv7\";s:5:\"label\";s:22:\"Channels data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:334:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_channel_csv7\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Channels data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:334:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2580,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','361','a:5:{s:4:\"code\";s:30:\"fixtures_association_type_csv8\";s:5:\"label\";s:26:\"Associations data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:343:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"fixtures_association_type_csv8\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Associations data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:343:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2581,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','362','a:5:{s:4:\"code\";s:31:\"fixtures_attribute_options_csv9\";s:5:\"label\";s:31:\"Attribute options data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:343:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"fixtures_attribute_options_csv9\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"Attribute options data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:343:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2582,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','363','a:5:{s:4:\"code\";s:21:\"fixtures_family_csv10\";s:5:\"label\";s:22:\"Families data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:334:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_family_csv10\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Families data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:334:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2583,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','364','a:5:{s:4:\"code\";s:18:\"fixtures_job_yml11\";s:5:\"label\";s:18:\"Jobs data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:266:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"fixtures_job_yml11\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Jobs data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:266:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2584,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','365','a:5:{s:4:\"code\";s:29:\"fixtures_family_variant_csv12\";s:5:\"label\";s:29:\"Family variants data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:341:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_family_variant_csv12\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Family variants data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:341:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2585,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','366','a:5:{s:4:\"code\";s:20:\"fixtures_group_csv13\";s:5:\"label\";s:20:\"Groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:332:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_group_csv13\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:332:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2586,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','367','a:5:{s:4:\"code\";s:24:\"fixtures_user_role_csv14\";s:5:\"label\";s:24:\"User roles data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_role_csv14\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"User roles data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:336:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2587,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','368','a:5:{s:4:\"code\";s:19:\"fixtures_user_csv15\";s:5:\"label\";s:19:\"Users data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:331:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"fixtures_user_csv15\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Users data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:331:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2588,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','369','a:5:{s:4:\"code\";s:28:\"fixtures_product_model_csv16\";s:5:\"label\";s:27:\"Product model data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:526:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"fixtures_product_model_csv16\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Product model data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:526:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2589,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','370','a:5:{s:4:\"code\";s:22:\"fixtures_product_csv17\";s:5:\"label\";s:21:\"Product data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:529:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_product_csv17\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Product data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:529:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/apparel\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:10',0),(2590,'system','Akeneo\\Channel\\Component\\Model\\Locale','1261','a:1:{s:4:\"code\";s:5:\"af_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"af_ZA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2591,'system','Akeneo\\Channel\\Component\\Model\\Locale','1262','a:1:{s:4:\"code\";s:5:\"am_ET\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"am_ET\";}}',NULL,1,'2018-06-03 13:05:10',0),(2592,'system','Akeneo\\Channel\\Component\\Model\\Locale','1263','a:1:{s:4:\"code\";s:5:\"ar_AE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_AE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2593,'system','Akeneo\\Channel\\Component\\Model\\Locale','1264','a:1:{s:4:\"code\";s:5:\"ar_BH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_BH\";}}',NULL,1,'2018-06-03 13:05:10',0),(2594,'system','Akeneo\\Channel\\Component\\Model\\Locale','1265','a:1:{s:4:\"code\";s:5:\"ar_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_DZ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2595,'system','Akeneo\\Channel\\Component\\Model\\Locale','1266','a:1:{s:4:\"code\";s:5:\"ar_EG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}',NULL,1,'2018-06-03 13:05:10',0),(2596,'system','Akeneo\\Channel\\Component\\Model\\Locale','1267','a:1:{s:4:\"code\";s:5:\"ar_IQ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_IQ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2597,'system','Akeneo\\Channel\\Component\\Model\\Locale','1268','a:1:{s:4:\"code\";s:5:\"ar_JO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_JO\";}}',NULL,1,'2018-06-03 13:05:10',0),(2598,'system','Akeneo\\Channel\\Component\\Model\\Locale','1269','a:1:{s:4:\"code\";s:5:\"ar_KW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_KW\";}}',NULL,1,'2018-06-03 13:05:10',0),(2599,'system','Akeneo\\Channel\\Component\\Model\\Locale','1270','a:1:{s:4:\"code\";s:5:\"ar_LB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LB\";}}',NULL,1,'2018-06-03 13:05:10',0),(2600,'system','Akeneo\\Channel\\Component\\Model\\Locale','1271','a:1:{s:4:\"code\";s:5:\"ar_LY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LY\";}}',NULL,1,'2018-06-03 13:05:10',0),(2601,'system','Akeneo\\Channel\\Component\\Model\\Locale','1272','a:1:{s:4:\"code\";s:5:\"ar_MA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_MA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2602,'system','Akeneo\\Channel\\Component\\Model\\Locale','1273','a:1:{s:4:\"code\";s:6:\"arn_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"arn_CL\";}}',NULL,1,'2018-06-03 13:05:10',0),(2603,'system','Akeneo\\Channel\\Component\\Model\\Locale','1274','a:1:{s:4:\"code\";s:5:\"ar_OM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_OM\";}}',NULL,1,'2018-06-03 13:05:10',0),(2604,'system','Akeneo\\Channel\\Component\\Model\\Locale','1275','a:1:{s:4:\"code\";s:5:\"ar_QA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_QA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2605,'system','Akeneo\\Channel\\Component\\Model\\Locale','1276','a:1:{s:4:\"code\";s:5:\"ar_SA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2606,'system','Akeneo\\Channel\\Component\\Model\\Locale','1277','a:1:{s:4:\"code\";s:5:\"ar_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SY\";}}',NULL,1,'2018-06-03 13:05:10',0),(2607,'system','Akeneo\\Channel\\Component\\Model\\Locale','1278','a:1:{s:4:\"code\";s:5:\"ar_TN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_TN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2608,'system','Akeneo\\Channel\\Component\\Model\\Locale','1279','a:1:{s:4:\"code\";s:5:\"ar_YE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_YE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2609,'system','Akeneo\\Channel\\Component\\Model\\Locale','1280','a:1:{s:4:\"code\";s:5:\"as_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"as_IN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2610,'system','Akeneo\\Channel\\Component\\Model\\Locale','1281','a:1:{s:4:\"code\";s:10:\"az_Cyrl_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Cyrl_AZ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2611,'system','Akeneo\\Channel\\Component\\Model\\Locale','1282','a:1:{s:4:\"code\";s:10:\"az_Latn_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Latn_AZ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2612,'system','Akeneo\\Channel\\Component\\Model\\Locale','1283','a:1:{s:4:\"code\";s:5:\"ba_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ba_RU\";}}',NULL,1,'2018-06-03 13:05:10',0),(2613,'system','Akeneo\\Channel\\Component\\Model\\Locale','1284','a:1:{s:4:\"code\";s:5:\"be_BY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"be_BY\";}}',NULL,1,'2018-06-03 13:05:10',0),(2614,'system','Akeneo\\Channel\\Component\\Model\\Locale','1285','a:1:{s:4:\"code\";s:5:\"bg_BG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bg_BG\";}}',NULL,1,'2018-06-03 13:05:10',0),(2615,'system','Akeneo\\Channel\\Component\\Model\\Locale','1286','a:1:{s:4:\"code\";s:5:\"bn_BD\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_BD\";}}',NULL,1,'2018-06-03 13:05:10',0),(2616,'system','Akeneo\\Channel\\Component\\Model\\Locale','1287','a:1:{s:4:\"code\";s:5:\"bn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_IN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2617,'system','Akeneo\\Channel\\Component\\Model\\Locale','1288','a:1:{s:4:\"code\";s:5:\"bo_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bo_CN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2618,'system','Akeneo\\Channel\\Component\\Model\\Locale','1289','a:1:{s:4:\"code\";s:5:\"br_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"br_FR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2619,'system','Akeneo\\Channel\\Component\\Model\\Locale','1290','a:1:{s:4:\"code\";s:10:\"bs_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Cyrl_BA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2620,'system','Akeneo\\Channel\\Component\\Model\\Locale','1291','a:1:{s:4:\"code\";s:10:\"bs_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Latn_BA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2621,'system','Akeneo\\Channel\\Component\\Model\\Locale','1292','a:1:{s:4:\"code\";s:5:\"ca_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ca_ES\";}}',NULL,1,'2018-06-03 13:05:10',0),(2622,'system','Akeneo\\Channel\\Component\\Model\\Locale','1293','a:1:{s:4:\"code\";s:5:\"co_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"co_FR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2623,'system','Akeneo\\Channel\\Component\\Model\\Locale','1294','a:1:{s:4:\"code\";s:5:\"cs_CZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cs_CZ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2624,'system','Akeneo\\Channel\\Component\\Model\\Locale','1295','a:1:{s:4:\"code\";s:5:\"cy_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cy_GB\";}}',NULL,1,'2018-06-03 13:05:10',0),(2625,'system','Akeneo\\Channel\\Component\\Model\\Locale','1296','a:1:{s:4:\"code\";s:5:\"da_DK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"da_DK\";}}',NULL,1,'2018-06-03 13:05:10',0),(2626,'system','Akeneo\\Channel\\Component\\Model\\Locale','1297','a:1:{s:4:\"code\";s:5:\"de_AT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_AT\";}}',NULL,1,'2018-06-03 13:05:10',0),(2627,'system','Akeneo\\Channel\\Component\\Model\\Locale','1298','a:1:{s:4:\"code\";s:5:\"de_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_CH\";}}',NULL,1,'2018-06-03 13:05:10',0),(2628,'system','Akeneo\\Channel\\Component\\Model\\Locale','1299','a:1:{s:4:\"code\";s:5:\"de_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2629,'system','Akeneo\\Channel\\Component\\Model\\Locale','1300','a:1:{s:4:\"code\";s:5:\"de_LI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LI\";}}',NULL,1,'2018-06-03 13:05:10',0),(2630,'system','Akeneo\\Channel\\Component\\Model\\Locale','1301','a:1:{s:4:\"code\";s:5:\"de_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LU\";}}',NULL,1,'2018-06-03 13:05:10',0),(2631,'system','Akeneo\\Channel\\Component\\Model\\Locale','1302','a:1:{s:4:\"code\";s:6:\"dsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"dsb_DE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2632,'system','Akeneo\\Channel\\Component\\Model\\Locale','1303','a:1:{s:4:\"code\";s:5:\"dv_MV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"dv_MV\";}}',NULL,1,'2018-06-03 13:05:10',0),(2633,'system','Akeneo\\Channel\\Component\\Model\\Locale','1304','a:1:{s:4:\"code\";s:5:\"el_GR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"el_GR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2634,'system','Akeneo\\Channel\\Component\\Model\\Locale','1305','a:1:{s:4:\"code\";s:6:\"en_029\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"en_029\";}}',NULL,1,'2018-06-03 13:05:10',0),(2635,'system','Akeneo\\Channel\\Component\\Model\\Locale','1306','a:1:{s:4:\"code\";s:5:\"en_AU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_AU\";}}',NULL,1,'2018-06-03 13:05:10',0),(2636,'system','Akeneo\\Channel\\Component\\Model\\Locale','1307','a:1:{s:4:\"code\";s:5:\"en_BZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_BZ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2637,'system','Akeneo\\Channel\\Component\\Model\\Locale','1308','a:1:{s:4:\"code\";s:5:\"en_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_CA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2638,'system','Akeneo\\Channel\\Component\\Model\\Locale','1309','a:1:{s:4:\"code\";s:5:\"en_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_GB\";}}',NULL,1,'2018-06-03 13:05:10',0),(2639,'system','Akeneo\\Channel\\Component\\Model\\Locale','1310','a:1:{s:4:\"code\";s:5:\"en_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2640,'system','Akeneo\\Channel\\Component\\Model\\Locale','1311','a:1:{s:4:\"code\";s:5:\"en_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2641,'system','Akeneo\\Channel\\Component\\Model\\Locale','1312','a:1:{s:4:\"code\";s:5:\"en_JM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_JM\";}}',NULL,1,'2018-06-03 13:05:10',0),(2642,'system','Akeneo\\Channel\\Component\\Model\\Locale','1313','a:1:{s:4:\"code\";s:5:\"en_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_MY\";}}',NULL,1,'2018-06-03 13:05:10',0),(2643,'system','Akeneo\\Channel\\Component\\Model\\Locale','1314','a:1:{s:4:\"code\";s:5:\"en_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_NZ\";}}',NULL,1,'2018-06-03 13:05:10',0),(2644,'system','Akeneo\\Channel\\Component\\Model\\Locale','1315','a:1:{s:4:\"code\";s:5:\"en_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_PH\";}}',NULL,1,'2018-06-03 13:05:10',0),(2645,'system','Akeneo\\Channel\\Component\\Model\\Locale','1316','a:1:{s:4:\"code\";s:5:\"en_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_SG\";}}',NULL,1,'2018-06-03 13:05:10',0),(2646,'system','Akeneo\\Channel\\Component\\Model\\Locale','1317','a:1:{s:4:\"code\";s:5:\"en_TT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_TT\";}}',NULL,1,'2018-06-03 13:05:10',0),(2647,'system','Akeneo\\Channel\\Component\\Model\\Locale','1318','a:1:{s:4:\"code\";s:5:\"en_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}}',NULL,1,'2018-06-03 13:05:10',0),(2648,'system','Akeneo\\Channel\\Component\\Model\\Locale','1319','a:1:{s:4:\"code\";s:5:\"en_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2649,'system','Akeneo\\Channel\\Component\\Model\\Locale','1320','a:1:{s:4:\"code\";s:5:\"en_ZW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZW\";}}',NULL,1,'2018-06-03 13:05:10',0),(2650,'system','Akeneo\\Channel\\Component\\Model\\Locale','1321','a:1:{s:4:\"code\";s:5:\"es_AR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_AR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2651,'system','Akeneo\\Channel\\Component\\Model\\Locale','1322','a:1:{s:4:\"code\";s:5:\"es_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_BO\";}}',NULL,1,'2018-06-03 13:05:10',0),(2652,'system','Akeneo\\Channel\\Component\\Model\\Locale','1323','a:1:{s:4:\"code\";s:5:\"es_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CL\";}}',NULL,1,'2018-06-03 13:05:10',0),(2653,'system','Akeneo\\Channel\\Component\\Model\\Locale','1324','a:1:{s:4:\"code\";s:5:\"es_CO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CO\";}}',NULL,1,'2018-06-03 13:05:10',0),(2654,'system','Akeneo\\Channel\\Component\\Model\\Locale','1325','a:1:{s:4:\"code\";s:5:\"es_CR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2655,'system','Akeneo\\Channel\\Component\\Model\\Locale','1326','a:1:{s:4:\"code\";s:5:\"es_DO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_DO\";}}',NULL,1,'2018-06-03 13:05:10',0),(2656,'system','Akeneo\\Channel\\Component\\Model\\Locale','1327','a:1:{s:4:\"code\";s:5:\"es_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_EC\";}}',NULL,1,'2018-06-03 13:05:10',0),(2657,'system','Akeneo\\Channel\\Component\\Model\\Locale','1328','a:1:{s:4:\"code\";s:5:\"es_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_ES\";}}',NULL,1,'2018-06-03 13:05:10',0),(2658,'system','Akeneo\\Channel\\Component\\Model\\Locale','1329','a:1:{s:4:\"code\";s:5:\"es_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_GT\";}}',NULL,1,'2018-06-03 13:05:10',0),(2659,'system','Akeneo\\Channel\\Component\\Model\\Locale','1330','a:1:{s:4:\"code\";s:5:\"es_HN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_HN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2660,'system','Akeneo\\Channel\\Component\\Model\\Locale','1331','a:1:{s:4:\"code\";s:5:\"es_MX\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_MX\";}}',NULL,1,'2018-06-03 13:05:10',0),(2661,'system','Akeneo\\Channel\\Component\\Model\\Locale','1332','a:1:{s:4:\"code\";s:5:\"es_NI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_NI\";}}',NULL,1,'2018-06-03 13:05:10',0),(2662,'system','Akeneo\\Channel\\Component\\Model\\Locale','1333','a:1:{s:4:\"code\";s:5:\"es_PA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2663,'system','Akeneo\\Channel\\Component\\Model\\Locale','1334','a:1:{s:4:\"code\";s:5:\"es_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2664,'system','Akeneo\\Channel\\Component\\Model\\Locale','1335','a:1:{s:4:\"code\";s:5:\"es_PR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2665,'system','Akeneo\\Channel\\Component\\Model\\Locale','1336','a:1:{s:4:\"code\";s:5:\"es_PY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PY\";}}',NULL,1,'2018-06-03 13:05:10',0),(2666,'system','Akeneo\\Channel\\Component\\Model\\Locale','1337','a:1:{s:4:\"code\";s:5:\"es_SV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_SV\";}}',NULL,1,'2018-06-03 13:05:10',0),(2667,'system','Akeneo\\Channel\\Component\\Model\\Locale','1338','a:1:{s:4:\"code\";s:5:\"es_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_US\";}}',NULL,1,'2018-06-03 13:05:10',0),(2668,'system','Akeneo\\Channel\\Component\\Model\\Locale','1339','a:1:{s:4:\"code\";s:5:\"es_UY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_UY\";}}',NULL,1,'2018-06-03 13:05:10',0),(2669,'system','Akeneo\\Channel\\Component\\Model\\Locale','1340','a:1:{s:4:\"code\";s:5:\"es_VE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_VE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2670,'system','Akeneo\\Channel\\Component\\Model\\Locale','1341','a:1:{s:4:\"code\";s:5:\"et_EE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"et_EE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2671,'system','Akeneo\\Channel\\Component\\Model\\Locale','1342','a:1:{s:4:\"code\";s:5:\"eu_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"eu_ES\";}}',NULL,1,'2018-06-03 13:05:10',0),(2672,'system','Akeneo\\Channel\\Component\\Model\\Locale','1343','a:1:{s:4:\"code\";s:5:\"fa_IR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fa_IR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2673,'system','Akeneo\\Channel\\Component\\Model\\Locale','1344','a:1:{s:4:\"code\";s:5:\"fi_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fi_FI\";}}',NULL,1,'2018-06-03 13:05:10',0),(2674,'system','Akeneo\\Channel\\Component\\Model\\Locale','1345','a:1:{s:4:\"code\";s:6:\"fil_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"fil_PH\";}}',NULL,1,'2018-06-03 13:05:10',0),(2675,'system','Akeneo\\Channel\\Component\\Model\\Locale','1346','a:1:{s:4:\"code\";s:5:\"fo_FO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fo_FO\";}}',NULL,1,'2018-06-03 13:05:10',0),(2676,'system','Akeneo\\Channel\\Component\\Model\\Locale','1347','a:1:{s:4:\"code\";s:5:\"fr_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_BE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2677,'system','Akeneo\\Channel\\Component\\Model\\Locale','1348','a:1:{s:4:\"code\";s:5:\"fr_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CA\";}}',NULL,1,'2018-06-03 13:05:10',0),(2678,'system','Akeneo\\Channel\\Component\\Model\\Locale','1349','a:1:{s:4:\"code\";s:5:\"fr_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CH\";}}',NULL,1,'2018-06-03 13:05:10',0),(2679,'system','Akeneo\\Channel\\Component\\Model\\Locale','1350','a:1:{s:4:\"code\";s:5:\"fr_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2680,'system','Akeneo\\Channel\\Component\\Model\\Locale','1351','a:1:{s:4:\"code\";s:5:\"fr_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_LU\";}}',NULL,1,'2018-06-03 13:05:10',0),(2681,'system','Akeneo\\Channel\\Component\\Model\\Locale','1352','a:1:{s:4:\"code\";s:5:\"fr_MC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_MC\";}}',NULL,1,'2018-06-03 13:05:10',0),(2682,'system','Akeneo\\Channel\\Component\\Model\\Locale','1353','a:1:{s:4:\"code\";s:5:\"fy_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fy_NL\";}}',NULL,1,'2018-06-03 13:05:10',0),(2683,'system','Akeneo\\Channel\\Component\\Model\\Locale','1354','a:1:{s:4:\"code\";s:5:\"ga_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ga_IE\";}}',NULL,1,'2018-06-03 13:05:10',0),(2684,'system','Akeneo\\Channel\\Component\\Model\\Locale','1355','a:1:{s:4:\"code\";s:5:\"gd_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gd_GB\";}}',NULL,1,'2018-06-03 13:05:10',0),(2685,'system','Akeneo\\Channel\\Component\\Model\\Locale','1356','a:1:{s:4:\"code\";s:5:\"gl_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gl_ES\";}}',NULL,1,'2018-06-03 13:05:10',0),(2686,'system','Akeneo\\Channel\\Component\\Model\\Locale','1357','a:1:{s:4:\"code\";s:6:\"gsw_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"gsw_FR\";}}',NULL,1,'2018-06-03 13:05:10',0),(2687,'system','Akeneo\\Channel\\Component\\Model\\Locale','1358','a:1:{s:4:\"code\";s:5:\"gu_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gu_IN\";}}',NULL,1,'2018-06-03 13:05:10',0),(2688,'system','Akeneo\\Channel\\Component\\Model\\Locale','1359','a:1:{s:4:\"code\";s:10:\"ha_Latn_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"ha_Latn_NG\";}}',NULL,1,'2018-06-03 13:05:10',0),(2689,'system','Akeneo\\Channel\\Component\\Model\\Locale','1360','a:1:{s:4:\"code\";s:5:\"he_IL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"he_IL\";}}',NULL,1,'2018-06-03 13:05:10',0),(2690,'system','Akeneo\\Channel\\Component\\Model\\Locale','1361','a:1:{s:4:\"code\";s:5:\"hi_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hi_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2691,'system','Akeneo\\Channel\\Component\\Model\\Locale','1362','a:1:{s:4:\"code\";s:5:\"hr_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_BA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2692,'system','Akeneo\\Channel\\Component\\Model\\Locale','1363','a:1:{s:4:\"code\";s:5:\"hr_HR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_HR\";}}',NULL,1,'2018-06-03 13:05:11',0),(2693,'system','Akeneo\\Channel\\Component\\Model\\Locale','1364','a:1:{s:4:\"code\";s:6:\"hsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"hsb_DE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2694,'system','Akeneo\\Channel\\Component\\Model\\Locale','1365','a:1:{s:4:\"code\";s:5:\"hu_HU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hu_HU\";}}',NULL,1,'2018-06-03 13:05:11',0),(2695,'system','Akeneo\\Channel\\Component\\Model\\Locale','1366','a:1:{s:4:\"code\";s:5:\"hy_AM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hy_AM\";}}',NULL,1,'2018-06-03 13:05:11',0),(2696,'system','Akeneo\\Channel\\Component\\Model\\Locale','1367','a:1:{s:4:\"code\";s:5:\"id_ID\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"id_ID\";}}',NULL,1,'2018-06-03 13:05:11',0),(2697,'system','Akeneo\\Channel\\Component\\Model\\Locale','1368','a:1:{s:4:\"code\";s:5:\"ig_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ig_NG\";}}',NULL,1,'2018-06-03 13:05:11',0),(2698,'system','Akeneo\\Channel\\Component\\Model\\Locale','1369','a:1:{s:4:\"code\";s:5:\"ii_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ii_CN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2699,'system','Akeneo\\Channel\\Component\\Model\\Locale','1370','a:1:{s:4:\"code\";s:5:\"is_IS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"is_IS\";}}',NULL,1,'2018-06-03 13:05:11',0),(2700,'system','Akeneo\\Channel\\Component\\Model\\Locale','1371','a:1:{s:4:\"code\";s:5:\"it_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_CH\";}}',NULL,1,'2018-06-03 13:05:11',0),(2701,'system','Akeneo\\Channel\\Component\\Model\\Locale','1372','a:1:{s:4:\"code\";s:5:\"it_IT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_IT\";}}',NULL,1,'2018-06-03 13:05:11',0),(2702,'system','Akeneo\\Channel\\Component\\Model\\Locale','1373','a:1:{s:4:\"code\";s:10:\"iu_Cans_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Cans_CA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2703,'system','Akeneo\\Channel\\Component\\Model\\Locale','1374','a:1:{s:4:\"code\";s:10:\"iu_Latn_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Latn_CA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2704,'system','Akeneo\\Channel\\Component\\Model\\Locale','1375','a:1:{s:4:\"code\";s:5:\"ja_JP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ja_JP\";}}',NULL,1,'2018-06-03 13:05:11',0),(2705,'system','Akeneo\\Channel\\Component\\Model\\Locale','1376','a:1:{s:4:\"code\";s:5:\"ka_GE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ka_GE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2706,'system','Akeneo\\Channel\\Component\\Model\\Locale','1377','a:1:{s:4:\"code\";s:5:\"kk_KZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kk_KZ\";}}',NULL,1,'2018-06-03 13:05:11',0),(2707,'system','Akeneo\\Channel\\Component\\Model\\Locale','1378','a:1:{s:4:\"code\";s:5:\"kl_GL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kl_GL\";}}',NULL,1,'2018-06-03 13:05:11',0),(2708,'system','Akeneo\\Channel\\Component\\Model\\Locale','1379','a:1:{s:4:\"code\";s:5:\"km_KH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"km_KH\";}}',NULL,1,'2018-06-03 13:05:11',0),(2709,'system','Akeneo\\Channel\\Component\\Model\\Locale','1380','a:1:{s:4:\"code\";s:5:\"kn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kn_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2710,'system','Akeneo\\Channel\\Component\\Model\\Locale','1381','a:1:{s:4:\"code\";s:6:\"kok_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"kok_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2711,'system','Akeneo\\Channel\\Component\\Model\\Locale','1382','a:1:{s:4:\"code\";s:5:\"ko_KR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ko_KR\";}}',NULL,1,'2018-06-03 13:05:11',0),(2712,'system','Akeneo\\Channel\\Component\\Model\\Locale','1383','a:1:{s:4:\"code\";s:5:\"ky_KG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ky_KG\";}}',NULL,1,'2018-06-03 13:05:11',0),(2713,'system','Akeneo\\Channel\\Component\\Model\\Locale','1384','a:1:{s:4:\"code\";s:5:\"lb_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lb_LU\";}}',NULL,1,'2018-06-03 13:05:11',0),(2714,'system','Akeneo\\Channel\\Component\\Model\\Locale','1385','a:1:{s:4:\"code\";s:5:\"lo_LA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lo_LA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2715,'system','Akeneo\\Channel\\Component\\Model\\Locale','1386','a:1:{s:4:\"code\";s:5:\"lt_LT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lt_LT\";}}',NULL,1,'2018-06-03 13:05:11',0),(2716,'system','Akeneo\\Channel\\Component\\Model\\Locale','1387','a:1:{s:4:\"code\";s:5:\"lv_LV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lv_LV\";}}',NULL,1,'2018-06-03 13:05:11',0),(2717,'system','Akeneo\\Channel\\Component\\Model\\Locale','1388','a:1:{s:4:\"code\";s:5:\"mi_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mi_NZ\";}}',NULL,1,'2018-06-03 13:05:11',0),(2718,'system','Akeneo\\Channel\\Component\\Model\\Locale','1389','a:1:{s:4:\"code\";s:5:\"mk_MK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mk_MK\";}}',NULL,1,'2018-06-03 13:05:11',0),(2719,'system','Akeneo\\Channel\\Component\\Model\\Locale','1390','a:1:{s:4:\"code\";s:5:\"ml_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ml_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2720,'system','Akeneo\\Channel\\Component\\Model\\Locale','1391','a:1:{s:4:\"code\";s:5:\"mn_MN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mn_MN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2721,'system','Akeneo\\Channel\\Component\\Model\\Locale','1392','a:1:{s:4:\"code\";s:10:\"mn_Mong_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"mn_Mong_CN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2722,'system','Akeneo\\Channel\\Component\\Model\\Locale','1393','a:1:{s:4:\"code\";s:6:\"moh_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"moh_CA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2723,'system','Akeneo\\Channel\\Component\\Model\\Locale','1394','a:1:{s:4:\"code\";s:5:\"mr_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mr_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2724,'system','Akeneo\\Channel\\Component\\Model\\Locale','1395','a:1:{s:4:\"code\";s:5:\"ms_BN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_BN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2725,'system','Akeneo\\Channel\\Component\\Model\\Locale','1396','a:1:{s:4:\"code\";s:5:\"ms_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_MY\";}}',NULL,1,'2018-06-03 13:05:11',0),(2726,'system','Akeneo\\Channel\\Component\\Model\\Locale','1397','a:1:{s:4:\"code\";s:5:\"mt_MT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mt_MT\";}}',NULL,1,'2018-06-03 13:05:11',0),(2727,'system','Akeneo\\Channel\\Component\\Model\\Locale','1398','a:1:{s:4:\"code\";s:5:\"nb_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nb_NO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2728,'system','Akeneo\\Channel\\Component\\Model\\Locale','1399','a:1:{s:4:\"code\";s:5:\"ne_NP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ne_NP\";}}',NULL,1,'2018-06-03 13:05:11',0),(2729,'system','Akeneo\\Channel\\Component\\Model\\Locale','1400','a:1:{s:4:\"code\";s:5:\"nl_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_BE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2730,'system','Akeneo\\Channel\\Component\\Model\\Locale','1401','a:1:{s:4:\"code\";s:5:\"nl_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_NL\";}}',NULL,1,'2018-06-03 13:05:11',0),(2731,'system','Akeneo\\Channel\\Component\\Model\\Locale','1402','a:1:{s:4:\"code\";s:5:\"nn_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nn_NO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2732,'system','Akeneo\\Channel\\Component\\Model\\Locale','1403','a:1:{s:4:\"code\";s:6:\"nso_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"nso_ZA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2733,'system','Akeneo\\Channel\\Component\\Model\\Locale','1404','a:1:{s:4:\"code\";s:5:\"oc_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"oc_FR\";}}',NULL,1,'2018-06-03 13:05:11',0),(2734,'system','Akeneo\\Channel\\Component\\Model\\Locale','1405','a:1:{s:4:\"code\";s:5:\"or_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"or_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2735,'system','Akeneo\\Channel\\Component\\Model\\Locale','1406','a:1:{s:4:\"code\";s:5:\"pa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pa_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2736,'system','Akeneo\\Channel\\Component\\Model\\Locale','1407','a:1:{s:4:\"code\";s:5:\"pl_PL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pl_PL\";}}',NULL,1,'2018-06-03 13:05:11',0),(2737,'system','Akeneo\\Channel\\Component\\Model\\Locale','1408','a:1:{s:4:\"code\";s:6:\"prs_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"prs_AF\";}}',NULL,1,'2018-06-03 13:05:11',0),(2738,'system','Akeneo\\Channel\\Component\\Model\\Locale','1409','a:1:{s:4:\"code\";s:5:\"ps_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ps_AF\";}}',NULL,1,'2018-06-03 13:05:11',0),(2739,'system','Akeneo\\Channel\\Component\\Model\\Locale','1410','a:1:{s:4:\"code\";s:5:\"pt_BR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_BR\";}}',NULL,1,'2018-06-03 13:05:11',0),(2740,'system','Akeneo\\Channel\\Component\\Model\\Locale','1411','a:1:{s:4:\"code\";s:5:\"pt_PT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_PT\";}}',NULL,1,'2018-06-03 13:05:11',0),(2741,'system','Akeneo\\Channel\\Component\\Model\\Locale','1412','a:1:{s:4:\"code\";s:6:\"qut_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"qut_GT\";}}',NULL,1,'2018-06-03 13:05:11',0),(2742,'system','Akeneo\\Channel\\Component\\Model\\Locale','1413','a:1:{s:4:\"code\";s:6:\"quz_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_BO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2743,'system','Akeneo\\Channel\\Component\\Model\\Locale','1414','a:1:{s:4:\"code\";s:6:\"quz_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_EC\";}}',NULL,1,'2018-06-03 13:05:11',0),(2744,'system','Akeneo\\Channel\\Component\\Model\\Locale','1415','a:1:{s:4:\"code\";s:6:\"quz_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_PE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2745,'system','Akeneo\\Channel\\Component\\Model\\Locale','1416','a:1:{s:4:\"code\";s:5:\"rm_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rm_CH\";}}',NULL,1,'2018-06-03 13:05:11',0),(2746,'system','Akeneo\\Channel\\Component\\Model\\Locale','1417','a:1:{s:4:\"code\";s:5:\"ro_RO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ro_RO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2747,'system','Akeneo\\Channel\\Component\\Model\\Locale','1418','a:1:{s:4:\"code\";s:5:\"ru_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ru_RU\";}}',NULL,1,'2018-06-03 13:05:11',0),(2748,'system','Akeneo\\Channel\\Component\\Model\\Locale','1419','a:1:{s:4:\"code\";s:5:\"rw_RW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rw_RW\";}}',NULL,1,'2018-06-03 13:05:11',0),(2749,'system','Akeneo\\Channel\\Component\\Model\\Locale','1420','a:1:{s:4:\"code\";s:6:\"sah_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sah_RU\";}}',NULL,1,'2018-06-03 13:05:11',0),(2750,'system','Akeneo\\Channel\\Component\\Model\\Locale','1421','a:1:{s:4:\"code\";s:5:\"sa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sa_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2751,'system','Akeneo\\Channel\\Component\\Model\\Locale','1422','a:1:{s:4:\"code\";s:5:\"se_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_FI\";}}',NULL,1,'2018-06-03 13:05:11',0),(2752,'system','Akeneo\\Channel\\Component\\Model\\Locale','1423','a:1:{s:4:\"code\";s:5:\"se_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_NO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2753,'system','Akeneo\\Channel\\Component\\Model\\Locale','1424','a:1:{s:4:\"code\";s:5:\"se_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_SE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2754,'system','Akeneo\\Channel\\Component\\Model\\Locale','1425','a:1:{s:4:\"code\";s:5:\"si_LK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"si_LK\";}}',NULL,1,'2018-06-03 13:05:11',0),(2755,'system','Akeneo\\Channel\\Component\\Model\\Locale','1426','a:1:{s:4:\"code\";s:5:\"sk_SK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sk_SK\";}}',NULL,1,'2018-06-03 13:05:11',0),(2756,'system','Akeneo\\Channel\\Component\\Model\\Locale','1427','a:1:{s:4:\"code\";s:5:\"sl_SI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sl_SI\";}}',NULL,1,'2018-06-03 13:05:11',0),(2757,'system','Akeneo\\Channel\\Component\\Model\\Locale','1428','a:1:{s:4:\"code\";s:6:\"sma_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_NO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2758,'system','Akeneo\\Channel\\Component\\Model\\Locale','1429','a:1:{s:4:\"code\";s:6:\"sma_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_SE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2759,'system','Akeneo\\Channel\\Component\\Model\\Locale','1430','a:1:{s:4:\"code\";s:6:\"smj_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_NO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2760,'system','Akeneo\\Channel\\Component\\Model\\Locale','1431','a:1:{s:4:\"code\";s:6:\"smj_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_SE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2761,'system','Akeneo\\Channel\\Component\\Model\\Locale','1432','a:1:{s:4:\"code\";s:6:\"smn_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smn_FI\";}}',NULL,1,'2018-06-03 13:05:11',0),(2762,'system','Akeneo\\Channel\\Component\\Model\\Locale','1433','a:1:{s:4:\"code\";s:6:\"sms_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sms_FI\";}}',NULL,1,'2018-06-03 13:05:11',0),(2763,'system','Akeneo\\Channel\\Component\\Model\\Locale','1434','a:1:{s:4:\"code\";s:5:\"sq_AL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sq_AL\";}}',NULL,1,'2018-06-03 13:05:11',0),(2764,'system','Akeneo\\Channel\\Component\\Model\\Locale','1435','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_BA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2765,'system','Akeneo\\Channel\\Component\\Model\\Locale','1436','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_CS\";}}',NULL,1,'2018-06-03 13:05:11',0),(2766,'system','Akeneo\\Channel\\Component\\Model\\Locale','1437','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_ME\";}}',NULL,1,'2018-06-03 13:05:11',0),(2767,'system','Akeneo\\Channel\\Component\\Model\\Locale','1438','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_RS\";}}',NULL,1,'2018-06-03 13:05:11',0),(2768,'system','Akeneo\\Channel\\Component\\Model\\Locale','1439','a:1:{s:4:\"code\";s:10:\"sr_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_BA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2769,'system','Akeneo\\Channel\\Component\\Model\\Locale','1440','a:1:{s:4:\"code\";s:10:\"sr_Latn_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_CS\";}}',NULL,1,'2018-06-03 13:05:11',0),(2770,'system','Akeneo\\Channel\\Component\\Model\\Locale','1441','a:1:{s:4:\"code\";s:10:\"sr_Latn_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_ME\";}}',NULL,1,'2018-06-03 13:05:11',0),(2771,'system','Akeneo\\Channel\\Component\\Model\\Locale','1442','a:1:{s:4:\"code\";s:10:\"sr_Latn_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_RS\";}}',NULL,1,'2018-06-03 13:05:11',0),(2772,'system','Akeneo\\Channel\\Component\\Model\\Locale','1443','a:1:{s:4:\"code\";s:5:\"sv_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_FI\";}}',NULL,1,'2018-06-03 13:05:11',0),(2773,'system','Akeneo\\Channel\\Component\\Model\\Locale','1444','a:1:{s:4:\"code\";s:5:\"sv_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_SE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2774,'system','Akeneo\\Channel\\Component\\Model\\Locale','1445','a:1:{s:4:\"code\";s:5:\"sw_KE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sw_KE\";}}',NULL,1,'2018-06-03 13:05:11',0),(2775,'system','Akeneo\\Channel\\Component\\Model\\Locale','1446','a:1:{s:4:\"code\";s:6:\"syr_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"syr_SY\";}}',NULL,1,'2018-06-03 13:05:11',0),(2776,'system','Akeneo\\Channel\\Component\\Model\\Locale','1447','a:1:{s:4:\"code\";s:5:\"ta_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ta_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2777,'system','Akeneo\\Channel\\Component\\Model\\Locale','1448','a:1:{s:4:\"code\";s:5:\"te_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"te_IN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2778,'system','Akeneo\\Channel\\Component\\Model\\Locale','1449','a:1:{s:4:\"code\";s:10:\"tg_Cyrl_TJ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"tg_Cyrl_TJ\";}}',NULL,1,'2018-06-03 13:05:11',0),(2779,'system','Akeneo\\Channel\\Component\\Model\\Locale','1450','a:1:{s:4:\"code\";s:5:\"th_TH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"th_TH\";}}',NULL,1,'2018-06-03 13:05:11',0),(2780,'system','Akeneo\\Channel\\Component\\Model\\Locale','1451','a:1:{s:4:\"code\";s:5:\"tk_TM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tk_TM\";}}',NULL,1,'2018-06-03 13:05:11',0),(2781,'system','Akeneo\\Channel\\Component\\Model\\Locale','1452','a:1:{s:4:\"code\";s:5:\"tn_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tn_ZA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2782,'system','Akeneo\\Channel\\Component\\Model\\Locale','1453','a:1:{s:4:\"code\";s:5:\"tr_TR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tr_TR\";}}',NULL,1,'2018-06-03 13:05:11',0),(2783,'system','Akeneo\\Channel\\Component\\Model\\Locale','1454','a:1:{s:4:\"code\";s:5:\"tt_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tt_RU\";}}',NULL,1,'2018-06-03 13:05:11',0),(2784,'system','Akeneo\\Channel\\Component\\Model\\Locale','1455','a:1:{s:4:\"code\";s:11:\"tzm_Latn_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"tzm_Latn_DZ\";}}',NULL,1,'2018-06-03 13:05:11',0),(2785,'system','Akeneo\\Channel\\Component\\Model\\Locale','1456','a:1:{s:4:\"code\";s:5:\"ug_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ug_CN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2786,'system','Akeneo\\Channel\\Component\\Model\\Locale','1457','a:1:{s:4:\"code\";s:5:\"uk_UA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"uk_UA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2787,'system','Akeneo\\Channel\\Component\\Model\\Locale','1458','a:1:{s:4:\"code\";s:5:\"ur_PK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ur_PK\";}}',NULL,1,'2018-06-03 13:05:11',0),(2788,'system','Akeneo\\Channel\\Component\\Model\\Locale','1459','a:1:{s:4:\"code\";s:10:\"uz_Cyrl_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Cyrl_UZ\";}}',NULL,1,'2018-06-03 13:05:11',0),(2789,'system','Akeneo\\Channel\\Component\\Model\\Locale','1460','a:1:{s:4:\"code\";s:10:\"uz_Latn_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Latn_UZ\";}}',NULL,1,'2018-06-03 13:05:11',0),(2790,'system','Akeneo\\Channel\\Component\\Model\\Locale','1461','a:1:{s:4:\"code\";s:5:\"vi_VN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"vi_VN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2791,'system','Akeneo\\Channel\\Component\\Model\\Locale','1462','a:1:{s:4:\"code\";s:5:\"wo_SN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"wo_SN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2792,'system','Akeneo\\Channel\\Component\\Model\\Locale','1463','a:1:{s:4:\"code\";s:5:\"xh_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"xh_ZA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2793,'system','Akeneo\\Channel\\Component\\Model\\Locale','1464','a:1:{s:4:\"code\";s:5:\"yo_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"yo_NG\";}}',NULL,1,'2018-06-03 13:05:11',0),(2794,'system','Akeneo\\Channel\\Component\\Model\\Locale','1465','a:1:{s:4:\"code\";s:5:\"zh_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_CN\";}}',NULL,1,'2018-06-03 13:05:11',0),(2795,'system','Akeneo\\Channel\\Component\\Model\\Locale','1466','a:1:{s:4:\"code\";s:5:\"zh_HK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_HK\";}}',NULL,1,'2018-06-03 13:05:11',0),(2796,'system','Akeneo\\Channel\\Component\\Model\\Locale','1467','a:1:{s:4:\"code\";s:5:\"zh_MO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_MO\";}}',NULL,1,'2018-06-03 13:05:11',0),(2797,'system','Akeneo\\Channel\\Component\\Model\\Locale','1468','a:1:{s:4:\"code\";s:5:\"zh_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_SG\";}}',NULL,1,'2018-06-03 13:05:11',0),(2798,'system','Akeneo\\Channel\\Component\\Model\\Locale','1469','a:1:{s:4:\"code\";s:5:\"zh_TW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_TW\";}}',NULL,1,'2018-06-03 13:05:11',0),(2799,'system','Akeneo\\Channel\\Component\\Model\\Locale','1470','a:1:{s:4:\"code\";s:5:\"zu_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zu_ZA\";}}',NULL,1,'2018-06-03 13:05:11',0),(2800,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','73','a:4:{s:4:\"code\";s:15:\"2013_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2013 collection\";s:11:\"label-en_GB\";s:15:\"2013 collection\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013 collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013 collection\";}}',NULL,1,'2018-06-03 13:05:11',0),(2801,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','74','a:6:{s:4:\"code\";s:15:\"2014_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2014 collection\";s:11:\"label-en_GB\";s:15:\"2014 collection\";s:11:\"label-fr_FR\";s:15:\"Collection 2014\";s:11:\"label-de_DE\";s:15:\"2014 Kollektion\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 collection\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Collection 2014\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 Kollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2802,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','75','a:4:{s:4:\"code\";s:15:\"2015_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2015 collection\";s:11:\"label-de_DE\";s:10:\"Kollektion\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015 collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Kollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2803,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','76','a:4:{s:4:\"code\";s:8:\"men_2013\";s:6:\"parent\";s:15:\"2013_collection\";s:11:\"label-en_US\";s:21:\"2013 men\'s collection\";s:11:\"label-en_GB\";s:21:\"2013 men\'s collection\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2013\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2013 men\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2013 men\'s collection\";}}',NULL,1,'2018-06-03 13:05:11',0),(2804,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','77','a:4:{s:4:\"code\";s:10:\"women_2013\";s:6:\"parent\";s:15:\"2013_collection\";s:11:\"label-en_US\";s:23:\"2013 women\'s collection\";s:11:\"label-en_GB\";s:23:\"2013 women\'s collection\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2013\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2013 women\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2013 women\'s collection\";}}',NULL,1,'2018-06-03 13:05:11',0),(2805,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','78','a:6:{s:4:\"code\";s:8:\"men_2014\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:21:\"2014 men\'s collection\";s:11:\"label-en_GB\";s:21:\"2014 men\'s collection\";s:11:\"label-fr_FR\";s:28:\"La collection de 2014 hommes\";s:11:\"label-de_DE\";s:23:\"2014 Männer-Kollektion\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2014\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2014 men\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2014 men\'s collection\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"La collection de 2014 hommes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2014 Männer-Kollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2806,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','79','a:6:{s:4:\"code\";s:10:\"women_2014\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:23:\"2014 women\'s collection\";s:11:\"label-en_GB\";s:23:\"2014 women\'s collection\";s:11:\"label-fr_FR\";s:28:\"La collection de 2014 femmes\";s:11:\"label-de_DE\";s:20:\"2014 Damenkollektion\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2014\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2014 women\'s collection\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2014 women\'s collection\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"La collection de 2014 femmes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"2014 Damenkollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2807,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','80','a:4:{s:4:\"code\";s:8:\"men_2015\";s:6:\"parent\";s:15:\"2015_collection\";s:11:\"label-en_US\";s:21:\"2015 men\'s collection\";s:11:\"label-de_DE\";s:23:\"2015 Männer-Kollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"2015 men\'s collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2015 Männer-Kollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2808,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','81','a:4:{s:4:\"code\";s:10:\"women_2015\";s:6:\"parent\";s:15:\"2015_collection\";s:11:\"label-en_US\";s:23:\"2015 women\'s collection\";s:11:\"label-de_DE\";s:20:\"2015 Damenkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"2015 women\'s collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"2015 Damenkollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2809,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','82','a:4:{s:4:\"code\";s:15:\"men_2015_spring\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s spring collection\";s:11:\"label-de_DE\";s:33:\"2015 Männer-Frühjahrskollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_spring\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s spring collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"2015 Männer-Frühjahrskollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2810,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','83','a:4:{s:4:\"code\";s:15:\"men_2015_summer\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s summer collection\";s:11:\"label-de_DE\";s:30:\"2015 Männer-Sommer-Kollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_summer\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s summer collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 Männer-Sommer-Kollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2811,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','84','a:4:{s:4:\"code\";s:15:\"men_2015_autumn\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s autumn collection\";s:11:\"label-de_DE\";s:29:\"2015 Männer-Herbstkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_autumn\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s autumn collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"2015 Männer-Herbstkollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2812,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','85','a:4:{s:4:\"code\";s:15:\"men_2015_winter\";s:6:\"parent\";s:8:\"men_2015\";s:11:\"label-en_US\";s:28:\"2015 men\'s winter collection\";s:11:\"label-de_DE\";s:29:\"2015 Männer-Winterkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"men_2015_winter\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"men_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 men\'s winter collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"2015 Männer-Winterkollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2813,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','86','a:4:{s:4:\"code\";s:17:\"women_2015_spring\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s spring collection\";s:11:\"label-de_DE\";s:31:\"2015 Damen-Frühjahrskollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_spring\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s spring collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"2015 Damen-Frühjahrskollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2814,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','87','a:4:{s:4:\"code\";s:17:\"women_2015_summer\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s summer collection\";s:11:\"label-de_DE\";s:28:\"2015 Damen-Sommer-Kollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_summer\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s summer collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"2015 Damen-Sommer-Kollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2815,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','88','a:4:{s:4:\"code\";s:17:\"women_2015_autumn\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s autumn collection\";s:11:\"label-de_DE\";s:27:\"2015 Damen-Herbstkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_autumn\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s autumn collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"2015 Damen-Herbstkollektion\";}}',NULL,1,'2018-06-03 13:05:11',0),(2816,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','89','a:4:{s:4:\"code\";s:17:\"women_2015_winter\";s:6:\"parent\";s:10:\"women_2015\";s:11:\"label-en_US\";s:30:\"2015 women\'s winter collection\";s:11:\"label-de_DE\";s:27:\"2015 Damen-Winterkollektion\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"women_2015_winter\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"women_2015\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"2015 women\'s winter collection\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"2015 Damen-Winterkollektion\";}}',NULL,1,'2018-06-03 13:05:12',0),(2817,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','27','a:7:{s:4:\"code\";s:7:\"general\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:7:\"General\";s:11:\"label-en_GB\";s:7:\"General\";s:11:\"label-fr_FR\";s:9:\"Général\";s:11:\"label-de_DE\";s:9:\"Allgemein\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"General\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"General\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Général\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Allgemein\";}}',NULL,1,'2018-06-03 13:05:12',0),(2818,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','28','a:7:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Media\";s:11:\"label-en_GB\";s:5:\"Media\";s:11:\"label-fr_FR\";s:7:\"Médias\";s:11:\"label-de_DE\";s:6:\"Medien\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Media\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Media\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Médias\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Medien\";}}',NULL,1,'2018-06-03 13:05:12',0),(2819,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','29','a:7:{s:4:\"code\";s:5:\"sales\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Sales\";s:11:\"label-en_GB\";s:5:\"Sales\";s:11:\"label-fr_FR\";s:6:\"Ventes\";s:11:\"label-de_DE\";s:8:\"Vertrieb\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sales\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sales\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Ventes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Vertrieb\";}}',NULL,1,'2018-06-03 13:05:12',0),(2820,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','30','a:7:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Sizes\";s:11:\"label-en_GB\";s:5:\"Sizes\";s:11:\"label-fr_FR\";s:7:\"Tailles\";s:11:\"label-de_DE\";s:8:\"Größen\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sizes\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sizes\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Tailles\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Größen\";}}',NULL,1,'2018-06-03 13:05:12',0),(2821,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','31','a:7:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:6:\"Colors\";s:11:\"label-en_GB\";s:7:\"Colours\";s:11:\"label-fr_FR\";s:8:\"Couleurs\";s:11:\"label-de_DE\";s:6:\"Farben\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Colors\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Colours\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Couleurs\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Farben\";}}',NULL,1,'2018-06-03 13:05:12',0),(2822,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','32','a:7:{s:4:\"code\";s:10:\"additional\";s:10:\"sort_order\";i:6;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:22:\"Additional information\";s:11:\"label-en_GB\";s:22:\"Additional information\";s:11:\"label-fr_FR\";s:29:\"Informations complémentaires\";s:11:\"label-de_DE\";s:26:\"Zusätzliche Informationen\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:6;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Additional information\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Additional information\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Informations complémentaires\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Zusätzliche Informationen\";}}',NULL,1,'2018-06-03 13:05:12',0),(2823,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','33','a:7:{s:4:\"code\";s:8:\"internal\";s:10:\"sort_order\";i:7;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:8:\"Internal\";s:11:\"label-en_GB\";s:8:\"Internal\";s:11:\"label-fr_FR\";s:7:\"Interne\";s:11:\"label-de_DE\";s:6:\"Intern\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:7;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Internal\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Internal\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Interne\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Intern\";}}',NULL,1,'2018-06-03 13:05:12',0),(2824,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','34','a:7:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Other\";s:11:\"label-en_GB\";s:5:\"Other\";s:11:\"label-fr_FR\";s:5:\"Autre\";s:11:\"label-de_DE\";s:5:\"Ander\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:100;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Autre\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Ander\";}}',NULL,1,'2018-06-03 13:05:12',0),(2825,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','111','a:32:{s:4:\"code\";s:3:\"sku\";s:4:\"type\";s:22:\"pim_catalog_identifier\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:3:\"SKU\";s:11:\"label-en_GB\";s:3:\"SKU\";s:11:\"label-fr_FR\";s:3:\"SKU\";s:11:\"label-de_DE\";s:3:\"SKU\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:1;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"pim_catalog_identifier\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:6:\"unique\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}s:8:\"required\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}}',NULL,1,'2018-06-03 13:05:12',0),(2826,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','27','a:7:{s:4:\"code\";s:7:\"general\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:39:\"sku,name,description,under_european_law\";s:11:\"label-en_US\";s:7:\"General\";s:11:\"label-en_GB\";s:7:\"General\";s:11:\"label-fr_FR\";s:9:\"Général\";s:11:\"label-de_DE\";s:9:\"Allgemein\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"sku,name,description,under_european_law\";}}',NULL,2,'2018-06-03 13:05:12',0),(2827,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','112','a:32:{s:4:\"code\";s:4:\"name\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Name\";s:11:\"label-en_GB\";s:4:\"Name\";s:11:\"label-fr_FR\";s:3:\"Nom\";s:11:\"label-de_DE\";s:4:\"Name\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"Nom\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:12',0),(2828,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','113','a:32:{s:4:\"code\";s:11:\"description\";s:4:\"type\";s:20:\"pim_catalog_textarea\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:1000;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Description\";s:11:\"label-en_GB\";s:11:\"Description\";s:11:\"label-fr_FR\";s:11:\"Description\";s:11:\"label-de_DE\";s:12:\"Beschreibung\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"description\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1000;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Beschreibung\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:12',0),(2829,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','114','a:32:{s:4:\"code\";s:9:\"thumbnail\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Thumbnail\";s:11:\"label-en_GB\";s:9:\"Thumbnail\";s:11:\"label-fr_FR\";s:8:\"Imagette\";s:11:\"label-de_DE\";s:15:\"Miniaturansicht\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"thumbnail\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Thumbnail\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Thumbnail\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Imagette\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Miniaturansicht\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:12',0),(2830,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','28','a:7:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:40:\"thumbnail,image,legend,localizable_image\";s:11:\"label-en_US\";s:5:\"Media\";s:11:\"label-en_GB\";s:5:\"Media\";s:11:\"label-fr_FR\";s:7:\"Médias\";s:11:\"label-de_DE\";s:6:\"Medien\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"thumbnail,image,legend,localizable_image\";}}',NULL,2,'2018-06-03 13:05:12',0),(2831,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','115','a:32:{s:4:\"code\";s:5:\"image\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Image\";s:11:\"label-en_GB\";s:5:\"Image\";s:11:\"label-fr_FR\";s:5:\"Image\";s:11:\"label-de_DE\";s:4:\"Bild\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"image\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Image\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Image\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Image\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Bild\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:12',0),(2832,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','116','a:32:{s:4:\"code\";s:6:\"legend\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Legend\";s:11:\"label-en_GB\";s:6:\"Legend\";s:11:\"label-fr_FR\";s:8:\"Légende\";s:11:\"label-de_DE\";s:7:\"Legende\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"legend\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Legend\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Legend\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Légende\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Legende\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:12',0),(2833,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','117','a:32:{s:4:\"code\";s:5:\"price\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:9:\"1000.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Price\";s:11:\"label-en_GB\";s:5:\"Price\";s:11:\"label-fr_FR\";s:4:\"Prix\";s:11:\"label-de_DE\";s:5:\"Preis\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:13:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"price\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"1000.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Price\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Price\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Prix\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Preis\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:12',0),(2834,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','29','a:7:{s:4:\"code\";s:5:\"sales\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:34:\"price,customer_rating,release_date\";s:11:\"label-en_US\";s:5:\"Sales\";s:11:\"label-en_GB\";s:5:\"Sales\";s:11:\"label-fr_FR\";s:6:\"Ventes\";s:11:\"label-de_DE\";s:8:\"Vertrieb\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"price,customer_rating,release_date\";}}',NULL,2,'2018-06-03 13:05:12',0),(2835,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','118','a:32:{s:4:\"code\";s:15:\"customer_rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Customer rating\";s:11:\"label-en_GB\";s:15:\"Customer rating\";s:11:\"label-fr_FR\";s:21:\"Évaluation du client\";s:11:\"label-de_DE\";s:15:\"Kundenbewertung\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"customer_rating\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Customer rating\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Customer rating\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Évaluation du client\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Kundenbewertung\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:12',0),(2836,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','119','a:32:{s:4:\"code\";s:12:\"release_date\";s:4:\"type\";s:16:\"pim_catalog_date\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Release date\";s:11:\"label-en_GB\";s:12:\"Release date\";s:11:\"label-fr_FR\";s:14:\"Date de sortie\";s:11:\"label-de_DE\";s:17:\"Erscheinungsdatum\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"release_date\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_date\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sales\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Release date\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Release date\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Date de sortie\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Erscheinungsdatum\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:12',0),(2837,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','120','a:32:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:11:\"label-en_GB\";s:4:\"Size\";s:11:\"label-fr_FR\";s:6:\"Taille\";s:11:\"label-de_DE\";s:7:\"Größe\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Size\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Size\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Taille\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Größe\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:12',0),(2838,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','30','a:7:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:26:\"size,chest_size,waist_size\";s:11:\"label-en_US\";s:5:\"Sizes\";s:11:\"label-en_GB\";s:5:\"Sizes\";s:11:\"label-fr_FR\";s:7:\"Tailles\";s:11:\"label-de_DE\";s:8:\"Größen\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"size,chest_size,waist_size\";}}',NULL,2,'2018-06-03 13:05:12',0),(2839,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','121','a:32:{s:4:\"code\";s:10:\"chest_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Chest size\";s:11:\"label-en_GB\";s:10:\"Chest size\";s:11:\"label-fr_FR\";s:16:\"Tour de poitrine\";s:11:\"label-de_DE\";s:11:\"Brustumfang\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"chest_size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Chest size\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Chest size\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Tour de poitrine\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Brustumfang\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2840,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','122','a:32:{s:4:\"code\";s:10:\"waist_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Waist size\";s:11:\"label-en_GB\";s:10:\"Waist size\";s:11:\"label-fr_FR\";s:14:\"Tour de taille\";s:11:\"label-de_DE\";s:14:\"Taillengröße\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"waist_size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Waist size\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Waist size\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Tour de taille\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Taillengröße\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2841,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','123','a:32:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:11:\"label-en_GB\";s:6:\"Colour\";s:11:\"label-fr_FR\";s:7:\"Couleur\";s:11:\"label-de_DE\";s:5:\"Farbe\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Color\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Colour\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Couleur\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Farbe\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2842,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','31','a:7:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:23:\"color,additional_colors\";s:11:\"label-en_US\";s:6:\"Colors\";s:11:\"label-en_GB\";s:7:\"Colours\";s:11:\"label-fr_FR\";s:8:\"Couleurs\";s:11:\"label-de_DE\";s:6:\"Farben\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"color,additional_colors\";}}',NULL,2,'2018-06-03 13:05:13',0),(2843,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','124','a:32:{s:4:\"code\";s:17:\"additional_colors\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Additional colors\";s:11:\"label-en_GB\";s:18:\"Additional colours\";s:11:\"label-fr_FR\";s:15:\"Autres couleurs\";s:11:\"label-de_DE\";s:19:\"Zusätzliche Farben\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"additional_colors\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Additional colors\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Additional colours\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Autres couleurs\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Zusätzliche Farben\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2844,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','125','a:32:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:11:\"label-en_GB\";s:12:\"Manufacturer\";s:11:\"label-fr_FR\";s:9:\"Fabricant\";s:11:\"label-de_DE\";s:10:\"Hersteller\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"manufacturer\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Manufacturer\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Manufacturer\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Fabricant\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Hersteller\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2845,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','32','a:7:{s:4:\"code\";s:10:\"additional\";s:10:\"sort_order\";i:6;s:10:\"attributes\";s:138:\"manufacturer,country_of_manufacture,handmade,weather_conditions,care_instructions,washing_temperature,weight,material,additional_materials\";s:11:\"label-en_US\";s:22:\"Additional information\";s:11:\"label-en_GB\";s:22:\"Additional information\";s:11:\"label-fr_FR\";s:29:\"Informations complémentaires\";s:11:\"label-de_DE\";s:26:\"Zusätzliche Informationen\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:138:\"manufacturer,country_of_manufacture,handmade,weather_conditions,care_instructions,washing_temperature,weight,material,additional_materials\";}}',NULL,2,'2018-06-03 13:05:13',0),(2846,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','126','a:32:{s:4:\"code\";s:22:\"country_of_manufacture\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:22:\"Country of manufacture\";s:11:\"label-en_GB\";s:22:\"Country of manufacture\";s:11:\"label-fr_FR\";s:19:\"Pays de fabrication\";s:11:\"label-de_DE\";s:16:\"Herstellungsland\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"country_of_manufacture\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Country of manufacture\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Country of manufacture\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Pays de fabrication\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Herstellungsland\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2847,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','127','a:32:{s:4:\"code\";s:8:\"handmade\";s:4:\"type\";s:19:\"pim_catalog_boolean\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Handmade\";s:11:\"label-en_GB\";s:8:\"Handmade\";s:11:\"label-fr_FR\";s:9:\"Fait main\";s:11:\"label-de_DE\";s:13:\"Handgefertigt\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"handmade\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Handmade\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Handmade\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Fait main\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Handgefertigt\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2848,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','128','a:32:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:11:\"label-en_GB\";s:18:\"Weather conditions\";s:11:\"label-fr_FR\";s:28:\"Conditions météorologiques\";s:11:\"label-de_DE\";s:19:\"Wetterverhältnisse\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"weather_conditions\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Weather conditions\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Weather conditions\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Conditions météorologiques\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Wetterverhältnisse\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2849,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','129','a:32:{s:4:\"code\";s:17:\"care_instructions\";s:4:\"type\";s:20:\"pim_catalog_textarea\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:1000;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:5;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Care instructions\";s:11:\"label-en_GB\";s:17:\"Care instructions\";s:11:\"label-fr_FR\";s:20:\"Conseils d\'entretien\";s:11:\"label-de_DE\";s:14:\"Pflegehinweise\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"care_instructions\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1000;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Care instructions\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Care instructions\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Conseils d\'entretien\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Pflegehinweise\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:13',0),(2850,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','130','a:32:{s:4:\"code\";s:19:\"washing_temperature\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:11:\"Temperature\";s:19:\"default_metric_unit\";s:7:\"CELSIUS\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";s:8:\"100.0000\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:6;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:19:\"Washing temperature\";s:11:\"label-en_GB\";s:19:\"Washing temperature\";s:11:\"label-fr_FR\";s:22:\"Température de lavage\";s:11:\"label-de_DE\";s:15:\"Waschtemperatur\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"washing_temperature\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Temperature\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"CELSIUS\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"100.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:6;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Washing temperature\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Washing temperature\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Température de lavage\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Waschtemperatur\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2851,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','131','a:32:{s:4:\"code\";s:6:\"weight\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Weight\";s:19:\"default_metric_unit\";s:8:\"KILOGRAM\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";s:7:\"10.0000\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:7;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Weight\";s:11:\"label-en_GB\";s:6:\"Weight\";s:11:\"label-fr_FR\";s:5:\"Poids\";s:11:\"label-de_DE\";s:7:\"Gewicht\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"weight\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"KILOGRAM\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"10.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:7;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Poids\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Gewicht\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2852,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','132','a:32:{s:4:\"code\";s:8:\"material\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:8;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Material\";s:11:\"label-en_GB\";s:8:\"Material\";s:11:\"label-fr_FR\";s:9:\"Matériau\";s:11:\"label-de_DE\";s:8:\"Material\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"material\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:8;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Material\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Material\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Matériau\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Material\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2853,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','133','a:32:{s:4:\"code\";s:20:\"additional_materials\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:9;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:20:\"Additional materials\";s:11:\"label-en_GB\";s:20:\"Additional materials\";s:11:\"label-fr_FR\";s:27:\"Matériels supplémentaires\";s:11:\"label-de_DE\";s:24:\"Zusätzliche Materialien\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"additional_materials\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"additional\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:9;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Additional materials\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Additional materials\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Matériels supplémentaires\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Zusätzliche Materialien\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2854,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','134','a:32:{s:4:\"code\";s:4:\"cost\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:9:\"1000.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Cost\";s:11:\"label-en_GB\";s:4:\"Cost\";s:11:\"label-fr_FR\";s:5:\"Coût\";s:11:\"label-de_DE\";s:6:\"Kosten\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:13:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"cost\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"1000.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Cost\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Cost\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Coût\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Kosten\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2855,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','33','a:7:{s:4:\"code\";s:8:\"internal\";s:10:\"sort_order\";i:7;s:10:\"attributes\";s:53:\"cost,number_in_stock,customs_tax,datasheet,attachment\";s:11:\"label-en_US\";s:8:\"Internal\";s:11:\"label-en_GB\";s:8:\"Internal\";s:11:\"label-fr_FR\";s:7:\"Interne\";s:11:\"label-de_DE\";s:6:\"Intern\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:53:\"cost,number_in_stock,customs_tax,datasheet,attachment\";}}',NULL,2,'2018-06-03 13:05:13',0),(2856,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','135','a:32:{s:4:\"code\";s:15:\"number_in_stock\";s:4:\"type\";s:18:\"pim_catalog_number\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:10:\"10000.0000\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Number in stock\";s:11:\"label-en_GB\";s:15:\"Number in stock\";s:11:\"label-fr_FR\";s:15:\"Nombre en stock\";s:11:\"label-de_DE\";s:16:\"Anzahl auf Lager\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"number_in_stock\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"10000.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Number in stock\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Number in stock\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Nombre en stock\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Anzahl auf Lager\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:13',0),(2857,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','136','a:32:{s:4:\"code\";s:11:\"customs_tax\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:5:\"de_DE\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:9:\"1000.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Customs tax\";s:11:\"label-en_GB\";s:11:\"Customs tax\";s:11:\"label-fr_FR\";s:15:\"Taxe de douanes\";s:11:\"label-de_DE\";s:10:\"Zollsteuer\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:15:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"customs_tax\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:17:\"available_locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"1000.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Customs tax\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Customs tax\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Taxe de douanes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Zollsteuer\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2858,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','137','a:32:{s:4:\"code\";s:18:\"under_european_law\";s:4:\"type\";s:19:\"pim_catalog_boolean\";s:5:\"group\";s:7:\"general\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:11:\"de_DE,fr_FR\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Under European law\";s:11:\"label-en_GB\";s:18:\"Under European law\";s:11:\"label-fr_FR\";s:23:\"Sous la loi Européenne\";s:11:\"label-de_DE\";s:24:\"Nach europäischem Recht\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"under_european_law\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"general\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:17:\"available_locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"de_DE,fr_FR\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Under European law\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Under European law\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Sous la loi Européenne\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Nach europäischem Recht\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2859,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','138','a:32:{s:4:\"code\";s:9:\"datasheet\";s:4:\"type\";s:16:\"pim_catalog_file\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:24:\"txt,pdf,doc,docx,csv,rtf\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:7:\"10.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Datasheet\";s:11:\"label-en_GB\";s:9:\"Datasheet\";s:11:\"label-fr_FR\";s:15:\"Fiche technique\";s:11:\"label-de_DE\";s:10:\"Datenblatt\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"datasheet\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_file\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"txt,pdf,doc,docx,csv,rtf\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"10.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Datasheet\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Datasheet\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Fiche technique\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Datenblatt\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2860,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','139','a:32:{s:4:\"code\";s:10:\"attachment\";s:4:\"type\";s:16:\"pim_catalog_file\";s:5:\"group\";s:8:\"internal\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:24:\"txt,pdf,doc,docx,csv,rtf\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:7:\"10.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:5;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Attachment\";s:11:\"label-en_GB\";s:10:\"Attachment\";s:11:\"label-fr_FR\";s:12:\"Attachements\";s:11:\"label-de_DE\";s:11:\"Befestigung\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"attachment\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_file\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"txt,pdf,doc,docx,csv,rtf\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"10.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Attachment\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Attachment\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Attachements\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Befestigung\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2861,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','140','a:32:{s:4:\"code\";s:17:\"localizable_image\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Localizable image\";s:11:\"label-en_GB\";s:17:\"Localizable image\";s:11:\"label-fr_FR\";s:16:\"Image localisée\";s:11:\"label-de_DE\";s:18:\"Lokalisiertes Bild\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:12:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"localizable_image\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Localizable image\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Localizable image\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Image localisée\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Lokalisiertes Bild\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:13',0),(2862,'system','Akeneo\\Channel\\Component\\Model\\Channel','14','a:7:{s:4:\"code\";s:9:\"ecommerce\";s:10:\"currencies\";s:11:\"USD,GBP,EUR\";s:7:\"locales\";s:23:\"en_US,en_GB,fr_FR,de_DE\";s:11:\"label-en_US\";s:9:\"Ecommerce\";s:11:\"label-de_DE\";s:9:\"Ecommerce\";s:11:\"label-fr_FR\";s:9:\"Ecommerce\";s:8:\"category\";s:15:\"2014_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"ecommerce\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"USD,GBP,EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"en_US,en_GB,fr_FR,de_DE\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}}',NULL,1,'2018-06-03 13:05:13',0),(2863,'system','Akeneo\\Channel\\Component\\Model\\Channel','15','a:7:{s:4:\"code\";s:6:\"tablet\";s:10:\"currencies\";s:7:\"USD,GBP\";s:7:\"locales\";s:11:\"en_US,en_GB\";s:11:\"label-en_US\";s:6:\"Tablet\";s:11:\"label-de_DE\";s:6:\"Tablet\";s:11:\"label-fr_FR\";s:8:\"Tablette\";s:8:\"category\";s:15:\"2013_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"tablet\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"USD,GBP\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,en_GB\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Tablette\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2013_collection\";}}',NULL,1,'2018-06-03 13:05:13',0),(2864,'system','Akeneo\\Channel\\Component\\Model\\Channel','16','a:7:{s:4:\"code\";s:5:\"print\";s:10:\"currencies\";s:7:\"USD,EUR\";s:7:\"locales\";s:11:\"en_US,de_DE\";s:11:\"label-en_US\";s:5:\"Print\";s:11:\"label-de_DE\";s:7:\"Drucken\";s:11:\"label-fr_FR\";s:10:\"Impression\";s:8:\"category\";s:15:\"2015_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"print\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"USD,EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,de_DE\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Print\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Drucken\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Impression\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2015_collection\";}}',NULL,1,'2018-06-03 13:05:13',0),(2865,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','25','a:5:{s:4:\"code\";s:7:\"variant\";s:11:\"label-en_US\";s:7:\"Variant\";s:11:\"label-en_GB\";s:7:\"Variant\";s:11:\"label-fr_FR\";s:8:\"Variante\";s:11:\"label-de_DE\";s:8:\"Variante\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"variant\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Variant\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Variant\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Variante\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Variante\";}}',NULL,1,'2018-06-03 13:05:13',0),(2866,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','26','a:5:{s:4:\"code\";s:7:\"similar\";s:11:\"label-en_US\";s:7:\"Similar\";s:11:\"label-en_GB\";s:7:\"Similar\";s:11:\"label-fr_FR\";s:9:\"Similaire\";s:11:\"label-de_DE\";s:8:\"Ähnlich\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"similar\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Similaire\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Ähnlich\";}}',NULL,1,'2018-06-03 13:05:13',0),(2867,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','27','a:5:{s:4:\"code\";s:7:\"related\";s:11:\"label-en_US\";s:7:\"Related\";s:11:\"label-en_GB\";s:7:\"Related\";s:11:\"label-fr_FR\";s:7:\"Connexe\";s:11:\"label-de_DE\";s:9:\"Verbunden\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"related\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Connexe\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Verbunden\";}}',NULL,1,'2018-06-03 13:05:13',0),(2868,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','28','a:5:{s:4:\"code\";s:10:\"cross_sell\";s:11:\"label-en_US\";s:10:\"Cross sell\";s:11:\"label-en_GB\";s:10:\"Cross sell\";s:11:\"label-fr_FR\";s:14:\"Vente croisée\";s:11:\"label-de_DE\";s:13:\"Cross-Selling\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"cross_sell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Vente croisée\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Cross-Selling\";}}',NULL,1,'2018-06-03 13:05:13',0),(2869,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','29','a:5:{s:4:\"code\";s:6:\"upsell\";s:11:\"label-en_US\";s:6:\"Upsell\";s:11:\"label-en_GB\";s:6:\"Upsell\";s:11:\"label-fr_FR\";s:16:\"Vente incitative\";s:11:\"label-de_DE\";s:6:\"Upsell\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"upsell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Vente incitative\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}}',NULL,1,'2018-06-03 13:05:13',0),(2870,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','30','a:5:{s:4:\"code\";s:10:\"substitute\";s:11:\"label-en_US\";s:10:\"Substitute\";s:11:\"label-en_GB\";s:10:\"Substitute\";s:11:\"label-fr_FR\";s:9:\"Substitut\";s:11:\"label-de_DE\";s:6:\"Ersatz\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"substitute\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Substitut\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Ersatz\";}}',NULL,1,'2018-06-03 13:05:13',0),(2871,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','31','a:5:{s:4:\"code\";s:4:\"pack\";s:11:\"label-en_US\";s:4:\"Pack\";s:11:\"label-en_GB\";s:4:\"Pack\";s:11:\"label-fr_FR\";s:6:\"Paquet\";s:11:\"label-de_DE\";s:4:\"Pack\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"pack\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Paquet\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}}',NULL,1,'2018-06-03 13:05:13',0),(2872,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','118','a:32:{s:4:\"code\";s:15:\"customer_rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sales\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Customer rating\";s:11:\"label-en_GB\";s:15:\"Customer rating\";s:11:\"label-fr_FR\";s:21:\"Évaluation du client\";s:11:\"label-de_DE\";s:15:\"Kundenbewertung\";s:7:\"options\";s:331:\"Code:1,en_US:1 star,en_GB:1 star,fr_FR:1 étoile,de_DE:1 Stern|Code:2,en_US:2 stars,en_GB:2 stars,fr_FR:2 étoiles,de_DE:2 Sterne|Code:3,en_US:3 stars,en_GB:3 stars,fr_FR:3 étoiles,de_DE:3 Sterne|Code:4,en_US:4 stars,en_GB:4 stars,fr_FR:4 étoiles,de_DE:4 Sterne|Code:5,en_US:5 stars,en_GB:5 stars,fr_FR:5 étoiles,de_DE:5 Sternen\";s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:331:\"Code:1,en_US:1 star,en_GB:1 star,fr_FR:1 étoile,de_DE:1 Stern|Code:2,en_US:2 stars,en_GB:2 stars,fr_FR:2 étoiles,de_DE:2 Sterne|Code:3,en_US:3 stars,en_GB:3 stars,fr_FR:3 étoiles,de_DE:3 Sterne|Code:4,en_US:4 stars,en_GB:4 stars,fr_FR:4 étoiles,de_DE:4 Sterne|Code:5,en_US:5 stars,en_GB:5 stars,fr_FR:5 étoiles,de_DE:5 Sternen\";}}',NULL,2,'2018-06-03 13:05:14',0),(2873,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','120','a:32:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:11:\"label-en_GB\";s:4:\"Size\";s:11:\"label-fr_FR\";s:6:\"Taille\";s:11:\"label-de_DE\";s:7:\"Größe\";s:7:\"options\";s:391:\"Code:size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:391:\"Code:size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";}}',NULL,2,'2018-06-03 13:05:14',0),(2874,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','121','a:32:{s:4:\"code\";s:10:\"chest_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Chest size\";s:11:\"label-en_GB\";s:10:\"Chest size\";s:11:\"label-fr_FR\";s:16:\"Tour de poitrine\";s:11:\"label-de_DE\";s:11:\"Brustumfang\";s:7:\"options\";s:439:\"Code:chest_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:chest_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:chest_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:chest_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:chest_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:chest_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:chest_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:chest_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:439:\"Code:chest_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:chest_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:chest_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:chest_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:chest_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:chest_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:chest_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:chest_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";}}',NULL,2,'2018-06-03 13:05:14',0),(2875,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','122','a:32:{s:4:\"code\";s:10:\"waist_size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Waist size\";s:11:\"label-en_GB\";s:10:\"Waist size\";s:11:\"label-fr_FR\";s:14:\"Tour de taille\";s:11:\"label-de_DE\";s:14:\"Taillengröße\";s:7:\"options\";s:439:\"Code:waist_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:waist_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:waist_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:waist_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:waist_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:waist_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:waist_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:waist_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:439:\"Code:waist_size_2XL,en_US:2XL,en_GB:2XL,fr_FR:2XL,de_DE:2XL|Code:waist_size_3XL,en_US:3XL,en_GB:3XL,fr_FR:3XL,de_DE:3XL|Code:waist_size_L,en_US:L,en_GB:L,fr_FR:L,de_DE:L|Code:waist_size_M,en_US:M,en_GB:M,fr_FR:M,de_DE:M|Code:waist_size_S,en_US:S,en_GB:S,fr_FR:S,de_DE:S|Code:waist_size_XL,en_US:XL,en_GB:XL,fr_FR:XL,de_DE:XL|Code:waist_size_XS,en_US:XS,en_GB:XS,fr_FR:XS,de_DE:XS|Code:waist_size_XXS,en_US:XXS,en_GB:XXS,fr_FR:XXS,de_DE:XXS\";}}',NULL,2,'2018-06-03 13:05:14',0),(2876,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','123','a:32:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:11:\"label-en_GB\";s:6:\"Colour\";s:11:\"label-fr_FR\";s:7:\"Couleur\";s:11:\"label-de_DE\";s:5:\"Farbe\";s:7:\"options\";s:396:\"Code:black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:396:\"Code:black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";}}',NULL,2,'2018-06-03 13:05:14',0),(2877,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','124','a:32:{s:4:\"code\";s:17:\"additional_colors\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:17:\"Additional colors\";s:11:\"label-en_GB\";s:18:\"Additional colours\";s:11:\"label-fr_FR\";s:15:\"Autres couleurs\";s:11:\"label-de_DE\";s:19:\"Zusätzliche Farben\";s:7:\"options\";s:473:\"Code:additional_black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:additional_white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:additional_gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:additional_red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:additional_blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:additional_green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:additional_yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:473:\"Code:additional_black,en_US:Black,en_GB:Black,fr_FR:Noir,de_DE:Schwarz|Code:additional_white,en_US:White,en_GB:White,fr_FR:Blanc,de_DE:Weiß|Code:additional_gray,en_US:Gray,en_GB:Gray,fr_FR:Gris,de_DE:Grau|Code:additional_red,en_US:Red,en_GB:Red,fr_FR:Rouge,de_DE:Rot|Code:additional_blue,en_US:Blue,en_GB:Blue,fr_FR:Bleu,de_DE:Blau|Code:additional_green,en_US:Green,en_GB:Green,fr_FR:Vert,de_DE:Grün|Code:additional_yellow,en_US:Yellow,en_GB:Yellow,fr_FR:Jaune,de_DE:Gelb\";}}',NULL,2,'2018-06-03 13:05:14',0),(2878,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','125','a:32:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:11:\"label-en_GB\";s:12:\"Manufacturer\";s:11:\"label-fr_FR\";s:9:\"Fabricant\";s:11:\"label-de_DE\";s:10:\"Hersteller\";s:7:\"options\";s:570:\"Code:american_apparel,en_US:American apparel,en_GB:American apparel,fr_FR:American apparel,de_DE:American apparel|Code:columbia,en_US:Columbia,en_GB:Columbia,fr_FR:Columbia,de_DE:Columbia|Code:diesel,en_US:Diesel,en_GB:Diesel,fr_FR:Diesel,de_DE:Diesel|Code:lacoste,en_US:Lacoste,en_GB:Lacoste,fr_FR:Lacoste,de_DE:Lacoste|Code:levi_strauss,en_US:Levi Strauss,en_GB:Levi Strauss,fr_FR:Levi Strauss,de_DE:Levi Strauss|Code:marc_o_polo,en_US:Marc O’Polo,en_GB:Marc O’Polo,fr_FR:Marc O’Polo,de_DE:Marc O’Polo|Code:prada,en_US:Prada,en_GB:Prada,fr_FR:Prada,de_DE:Prada\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:570:\"Code:american_apparel,en_US:American apparel,en_GB:American apparel,fr_FR:American apparel,de_DE:American apparel|Code:columbia,en_US:Columbia,en_GB:Columbia,fr_FR:Columbia,de_DE:Columbia|Code:diesel,en_US:Diesel,en_GB:Diesel,fr_FR:Diesel,de_DE:Diesel|Code:lacoste,en_US:Lacoste,en_GB:Lacoste,fr_FR:Lacoste,de_DE:Lacoste|Code:levi_strauss,en_US:Levi Strauss,en_GB:Levi Strauss,fr_FR:Levi Strauss,de_DE:Levi Strauss|Code:marc_o_polo,en_US:Marc O’Polo,en_GB:Marc O’Polo,fr_FR:Marc O’Polo,de_DE:Marc O’Polo|Code:prada,en_US:Prada,en_GB:Prada,fr_FR:Prada,de_DE:Prada\";}}',NULL,2,'2018-06-03 13:05:14',0),(2879,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','126','a:32:{s:4:\"code\";s:22:\"country_of_manufacture\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:22:\"Country of manufacture\";s:11:\"label-en_GB\";s:22:\"Country of manufacture\";s:11:\"label-fr_FR\";s:19:\"Pays de fabrication\";s:11:\"label-de_DE\";s:16:\"Herstellungsland\";s:7:\"options\";s:445:\"Code:china,en_US:China,en_GB:China,fr_FR:Chine,de_DE:China|Code:france,en_US:France,en_GB:France,fr_FR:France,de_DE:Frankreich|Code:germany,en_US:Germany,en_GB:Germany,fr_FR:Allemagne,de_DE:Deutschland|Code:italy,en_US:Italy,en_GB:Italy,fr_FR:Italie,de_DE:Italien|Code:taiwan,en_US:Taiwan,en_GB:Taiwan,fr_FR:Taiwan,de_DE:Taiwan|Code:usa,en_US:USA,en_GB:USA,fr_FR:USA,de_DE:USA|Code:vietnam,en_US:Vietnam,en_GB:Vietnam,fr_FR:Vietnam,de_DE:Vietnam\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:445:\"Code:china,en_US:China,en_GB:China,fr_FR:Chine,de_DE:China|Code:france,en_US:France,en_GB:France,fr_FR:France,de_DE:Frankreich|Code:germany,en_US:Germany,en_GB:Germany,fr_FR:Allemagne,de_DE:Deutschland|Code:italy,en_US:Italy,en_GB:Italy,fr_FR:Italie,de_DE:Italien|Code:taiwan,en_US:Taiwan,en_GB:Taiwan,fr_FR:Taiwan,de_DE:Taiwan|Code:usa,en_US:USA,en_GB:USA,fr_FR:USA,de_DE:USA|Code:vietnam,en_US:Vietnam,en_GB:Vietnam,fr_FR:Vietnam,de_DE:Vietnam\";}}',NULL,2,'2018-06-03 13:05:14',0),(2880,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','128','a:32:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:11:\"label-en_GB\";s:18:\"Weather conditions\";s:11:\"label-fr_FR\";s:28:\"Conditions météorologiques\";s:11:\"label-de_DE\";s:19:\"Wetterverhältnisse\";s:7:\"options\";s:279:\"Code:dry,en_US:Dry,en_GB:Dry,fr_FR:Sec,de_DE:Trocken|Code:wet,en_US:Wet,en_GB:Wet,fr_FR:Humide,de_DE:Nass|Code:hot,en_US:Hot,en_GB:Hot,fr_FR:Chaud,de_DE:Heiß|Code:cold,en_US:Cold,en_GB:Cold,fr_FR:Froid,de_DE:Kalt|Code:snowy,en_US:Snowy,en_GB:Snowy,fr_FR:Neigeux,de_DE:Verschneit\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:279:\"Code:dry,en_US:Dry,en_GB:Dry,fr_FR:Sec,de_DE:Trocken|Code:wet,en_US:Wet,en_GB:Wet,fr_FR:Humide,de_DE:Nass|Code:hot,en_US:Hot,en_GB:Hot,fr_FR:Chaud,de_DE:Heiß|Code:cold,en_US:Cold,en_GB:Cold,fr_FR:Froid,de_DE:Kalt|Code:snowy,en_US:Snowy,en_GB:Snowy,fr_FR:Neigeux,de_DE:Verschneit\";}}',NULL,2,'2018-06-03 13:05:14',0),(2881,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','132','a:32:{s:4:\"code\";s:8:\"material\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:8;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Material\";s:11:\"label-en_GB\";s:8:\"Material\";s:11:\"label-fr_FR\";s:9:\"Matériau\";s:11:\"label-de_DE\";s:8:\"Material\";s:7:\"options\";s:409:\"Code:cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:409:\"Code:cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";}}',NULL,2,'2018-06-03 13:05:14',0),(2882,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','133','a:32:{s:4:\"code\";s:20:\"additional_materials\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:10:\"additional\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:9;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:20:\"Additional materials\";s:11:\"label-en_GB\";s:20:\"Additional materials\";s:11:\"label-fr_FR\";s:27:\"Matériels supplémentaires\";s:11:\"label-de_DE\";s:24:\"Zusätzliche Materialien\";s:7:\"options\";s:486:\"Code:additional_cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:additional_flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:additional_wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:additional_silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:additional_denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:additional_leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:additional_fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:486:\"Code:additional_cotton,en_US:Cotton,en_GB:Cotton,fr_FR:Coton,de_DE:Baumwolle|Code:additional_flax,en_US:Flax,en_GB:Flax,fr_FR:Lin,de_DE:Flachs|Code:additional_wool,en_US:Wool,en_GB:Wool,fr_FR:Laine,de_DE:Wolle|Code:additional_silk,en_US:Silk,en_GB:Silk,fr_FR:Soie,de_DE:Seide|Code:additional_denim,en_US:Denim,en_GB:Denim,fr_FR:Toile,de_DE:Denim|Code:additional_leather,en_US:Leather,en_GB:Leather,fr_FR:Cuir,de_DE:Leder|Code:additional_fur,en_US:Fur,en_GB:Fur,fr_FR:Fourrure,de_DE:Pelz\";}}',NULL,2,'2018-06-03 13:05:14',0),(2883,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','17','a:11:{s:4:\"code\";s:7:\"tshirts\";s:10:\"attributes\";s:231:\"additional_colors,color,cost,country_of_manufacture,customer_rating,customs_tax,datasheet,description,handmade,image,legend,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";s:18:\"requirements-print\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";s:19:\"requirements-tablet\";s:38:\"color,manufacturer,name,price,size,sku\";s:11:\"label-en_US\";s:8:\"T-shirts\";s:11:\"label-en_GB\";s:8:\"T-shirts\";s:11:\"label-fr_FR\";s:8:\"T-shirts\";s:11:\"label-de_DE\";s:8:\"T-shirts\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"tshirts\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:231:\"additional_colors,color,cost,country_of_manufacture,customer_rating,customs_tax,datasheet,description,handmade,image,legend,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"color,manufacturer,name,price,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"T-shirts\";}}',NULL,1,'2018-06-03 13:05:14',0),(2884,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','18','a:11:{s:4:\"code\";s:8:\"sweaters\";s:10:\"attributes\";s:253:\"additional_colors,additional_materials,care_instructions,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";s:18:\"requirements-print\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";s:19:\"requirements-tablet\";s:38:\"color,manufacturer,name,price,size,sku\";s:11:\"label-en_US\";s:8:\"Sweaters\";s:11:\"label-en_GB\";s:9:\"Chandails\";s:11:\"label-fr_FR\";s:8:\"Sweaters\";s:11:\"label-de_DE\";s:9:\"Pullovern\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"sweaters\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:253:\"additional_colors,additional_materials,care_instructions,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,size,sku,thumbnail,washing_temperature,weight\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:59:\"color,description,manufacturer,material,name,price,size,sku\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:82:\"color,country_of_manufacture,description,manufacturer,material,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"color,manufacturer,name,price,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Sweaters\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Chandails\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Sweaters\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Pullovern\";}}',NULL,1,'2018-06-03 13:05:14',0),(2885,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','19','a:11:{s:4:\"code\";s:7:\"jackets\";s:10:\"attributes\";s:289:\"additional_colors,additional_materials,care_instructions,chest_size,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,sku,thumbnail,waist_size,washing_temperature,weather_conditions,weight\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:76:\"chest_size,color,description,manufacturer,material,name,price,sku,waist_size\";s:18:\"requirements-print\";s:118:\"chest_size,color,country_of_manufacture,description,manufacturer,material,name,price,sku,waist_size,weather_conditions\";s:19:\"requirements-tablet\";s:55:\"chest_size,color,manufacturer,name,price,sku,waist_size\";s:11:\"label-en_US\";s:7:\"Jackets\";s:11:\"label-en_GB\";s:7:\"Jackets\";s:11:\"label-fr_FR\";s:6:\"Vestes\";s:11:\"label-de_DE\";s:6:\"Jacken\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"jackets\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:289:\"additional_colors,additional_materials,care_instructions,chest_size,color,cost,country_of_manufacture,customer_rating,customs_tax,description,handmade,image,manufacturer,material,name,number_in_stock,price,release_date,sku,thumbnail,waist_size,washing_temperature,weather_conditions,weight\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:76:\"chest_size,color,description,manufacturer,material,name,price,sku,waist_size\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:118:\"chest_size,color,country_of_manufacture,description,manufacturer,material,name,price,sku,waist_size,weather_conditions\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:55:\"chest_size,color,manufacturer,name,price,sku,waist_size\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Jackets\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Jackets\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Vestes\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Jacken\";}}',NULL,1,'2018-06-03 13:05:14',0),(2886,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','20','a:11:{s:4:\"code\";s:7:\"sandals\";s:10:\"attributes\";s:26:\"description,name,price,sku\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:22:\"requirements-ecommerce\";s:26:\"description,name,price,sku\";s:18:\"requirements-print\";s:26:\"description,name,price,sku\";s:19:\"requirements-tablet\";s:14:\"name,price,sku\";s:11:\"label-en_US\";s:7:\"Sandals\";s:11:\"label-en_GB\";s:7:\"Sandals\";s:11:\"label-fr_FR\";s:7:\"Sandals\";s:11:\"label-de_DE\";s:7:\"Sandals\";}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"sandals\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"description,name,price,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"description,name,price,sku\";}s:18:\"requirements-print\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"description,name,price,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"name,price,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}}',NULL,1,'2018-06-03 13:05:14',0),(2887,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','371','a:5:{s:4:\"code\";s:14:\"product_import\";s:5:\"label\";s:18:\"CSV Product import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:384:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"CSV Product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:384:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2888,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','372','a:5:{s:4:\"code\";s:19:\"xlsx_product_import\";s:5:\"label\";s:19:\"XLSX product import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:338:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"xlsx_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"XLSX product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:338:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2889,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','373','a:5:{s:4:\"code\";s:24:\"ecommerce_product_export\";s:5:\"label\";s:28:\"Product export for ecommerce\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:480:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"ecommerce\",\"locales\":[\"en_US\",\"en_GB\",\"fr_FR\",\"de_DE\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"ecommerce_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Product export for ecommerce\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:480:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"ecommerce\",\"locales\":[\"en_US\",\"en_GB\",\"fr_FR\",\"de_DE\"]}}}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2890,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','374','a:5:{s:4:\"code\";s:21:\"tablet_product_export\";s:5:\"label\";s:25:\"Product export for tablet\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:461:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"tablet_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Product export for tablet\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:461:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2891,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','375','a:5:{s:4:\"code\";s:26:\"xlsx_tablet_product_export\";s:5:\"label\";s:30:\"XLSX product export for tablet\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:450:\"{\"filePath\":\"\\/tmp\\/products.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"xlsx_tablet_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"XLSX product export for tablet\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:450:\"{\"filePath\":\"\\/tmp\\/products.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2013_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"tablet\",\"locales\":[\"en_US\",\"en_GB\"]}}}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2892,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','376','a:5:{s:4:\"code\";s:20:\"print_product_export\";s:5:\"label\";s:24:\"Product export for print\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:460:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2015_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"print\",\"locales\":[\"en_US\",\"de_DE\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"print_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Product export for print\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:460:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2015_collection\"]},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100}],\"structure\":{\"scope\":\"print\",\"locales\":[\"en_US\",\"de_DE\"]}}}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2893,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','377','a:5:{s:4:\"code\";s:25:\"csv_apparel_family_export\";s:5:\"label\";s:22:\"Footwear family export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"csv_apparel_family_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Footwear family export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2894,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','378','a:5:{s:4:\"code\";s:15:\"category_import\";s:5:\"label\";s:15:\"Category import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"category_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Category import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2895,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','379','a:5:{s:4:\"code\";s:15:\"category_export\";s:5:\"label\";s:15:\"Category export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"category_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Category export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2896,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','380','a:5:{s:4:\"code\";s:23:\"association_type_import\";s:5:\"label\";s:23:\"Association type import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"association_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Association type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2897,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','381','a:5:{s:4:\"code\";s:23:\"association_type_export\";s:5:\"label\";s:23:\"Association type export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"association_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Association type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2898,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','382','a:5:{s:4:\"code\";s:12:\"group_import\";s:5:\"label\";s:12:\"Group import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2899,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','383','a:5:{s:4:\"code\";s:12:\"group_export\";s:5:\"label\";s:12:\"Group export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2900,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','384','a:5:{s:4:\"code\";s:17:\"xlsx_group_export\";s:5:\"label\";s:17:\"XLSX group export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/xlsx_group_export.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"xlsx_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"XLSX group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/xlsx_group_export.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2901,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','385','a:5:{s:4:\"code\";s:16:\"attribute_import\";s:5:\"label\";s:16:\"Attribute import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"attribute_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Attribute import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2902,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','386','a:5:{s:4:\"code\";s:16:\"attribute_export\";s:5:\"label\";s:16:\"Attribute export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"attribute_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Attribute export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2903,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','387','a:5:{s:4:\"code\";s:13:\"option_import\";s:5:\"label\";s:13:\"Option import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"option_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Option import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2904,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','388','a:5:{s:4:\"code\";s:13:\"option_export\";s:5:\"label\";s:13:\"Option export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"option_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Option export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:14',0),(2905,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','389','a:5:{s:4:\"code\";s:20:\"update_product_value\";s:5:\"label\";s:20:\"Mass update products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"update_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass update products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2906,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','390','a:5:{s:4:\"code\";s:17:\"add_product_value\";s:5:\"label\";s:24:\"Mass add products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"add_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2907,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','391','a:5:{s:4:\"code\";s:20:\"remove_product_value\";s:5:\"label\";s:27:\"Mass remove products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2908,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','392','a:5:{s:4:\"code\";s:16:\"move_to_category\";s:5:\"label\";s:23:\"Mass move to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"move_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass move to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2909,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','393','a:5:{s:4:\"code\";s:15:\"add_association\";s:5:\"label\";s:23:\"Mass associate products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_association\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass associate products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2910,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','394','a:5:{s:4:\"code\";s:15:\"add_to_category\";s:5:\"label\";s:22:\"Mass add to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Mass add to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2911,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','395','a:5:{s:4:\"code\";s:12:\"add_to_group\";s:5:\"label\";s:25:\"Mass add product to group\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"add_to_group\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Mass add product to group\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2912,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','396','a:5:{s:4:\"code\";s:20:\"remove_from_category\";s:5:\"label\";s:27:\"Mass remove from categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_from_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove from categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2913,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','397','a:5:{s:4:\"code\";s:22:\"edit_common_attributes\";s:5:\"label\";s:28:\"Mass edit product attributes\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"edit_common_attributes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Mass edit product attributes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2914,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','398','a:5:{s:4:\"code\";s:19:\"add_attribute_value\";s:5:\"label\";s:24:\"Mass add attribute value\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"add_attribute_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add attribute value\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2915,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','399','a:5:{s:4:\"code\";s:29:\"add_to_existing_product_model\";s:5:\"label\";s:29:\"Add to existing product model\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"add_to_existing_product_model\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Add to existing product model\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2916,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','400','a:5:{s:4:\"code\";s:34:\"compute_product_models_descendants\";s:5:\"label\";s:34:\"Compute product models descendants\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:34:\"compute_product_models_descendants\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"Compute product models descendants\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2917,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','401','a:5:{s:4:\"code\";s:39:\"compute_completeness_of_products_family\";s:5:\"label\";s:39:\"compute completeness of products family\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:39:\"compute_completeness_of_products_family\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute completeness of products family\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2918,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','402','a:5:{s:4:\"code\";s:40:\"compute_family_variant_structure_changes\";s:5:\"label\";s:33:\"Compute variant structure changes\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:40:\"compute_family_variant_structure_changes\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Compute variant structure changes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2919,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','403','a:5:{s:4:\"code\";s:34:\"delete_products_and_product_models\";s:5:\"label\";s:20:\"Mass delete products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:11:\"mass_delete\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"delete_products_and_product_models\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass delete products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"mass_delete\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:14',0),(2920,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','5','a:6:{s:4:\"code\";s:7:\"similar\";s:4:\"type\";s:7:\"similar\";s:11:\"label-en_US\";s:7:\"Similar\";s:11:\"label-en_GB\";s:7:\"Similar\";s:11:\"label-fr_FR\";s:9:\"Similaire\";s:11:\"label-de_DE\";s:8:\"Ähnlich\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"similar\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"similar\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Similar\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Similaire\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Ähnlich\";}}',NULL,1,'2018-06-03 13:05:15',0),(2921,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','6','a:6:{s:4:\"code\";s:7:\"related\";s:4:\"type\";s:7:\"related\";s:11:\"label-en_US\";s:7:\"Related\";s:11:\"label-en_GB\";s:7:\"Related\";s:11:\"label-fr_FR\";s:7:\"Connexe\";s:11:\"label-de_DE\";s:9:\"Verbunden\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"related\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"related\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Related\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Connexe\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Verbunden\";}}',NULL,1,'2018-06-03 13:05:15',0),(2922,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','7','a:6:{s:4:\"code\";s:10:\"cross_sell\";s:4:\"type\";s:10:\"cross_sell\";s:11:\"label-en_US\";s:10:\"Cross sell\";s:11:\"label-en_GB\";s:10:\"Cross sell\";s:11:\"label-fr_FR\";s:14:\"Vente croisée\";s:11:\"label-de_DE\";s:13:\"Cross-Selling\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"cross_sell\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"cross_sell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Vente croisée\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Cross-Selling\";}}',NULL,1,'2018-06-03 13:05:15',0),(2923,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','8','a:6:{s:4:\"code\";s:6:\"upsell\";s:4:\"type\";s:6:\"upsell\";s:11:\"label-en_US\";s:6:\"Upsell\";s:11:\"label-en_GB\";s:6:\"Upsell\";s:11:\"label-fr_FR\";s:16:\"Vente incitative\";s:11:\"label-de_DE\";s:6:\"Upsell\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"upsell\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"upsell\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Vente incitative\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}}',NULL,1,'2018-06-03 13:05:15',0),(2924,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','9','a:6:{s:4:\"code\";s:10:\"substitute\";s:4:\"type\";s:10:\"substitute\";s:11:\"label-en_US\";s:10:\"Substitute\";s:11:\"label-en_GB\";s:10:\"Substitute\";s:11:\"label-fr_FR\";s:9:\"Substitut\";s:11:\"label-de_DE\";s:6:\"Ersatz\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"substitute\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"substitute\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-en_GB\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Substitute\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Substitut\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Ersatz\";}}',NULL,1,'2018-06-03 13:05:15',0);
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

-- Dump completed on 2018-06-03 15:05:15
