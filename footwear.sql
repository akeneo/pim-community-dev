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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES (4,'(root)');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES (19,4,7,10,NULL,2,1,1,'all',0,0),(20,4,8,10,NULL,2,1056964608,1,'all',0,0),(21,4,7,11,NULL,1,1,1,'all',0,0),(22,4,8,11,NULL,1,1056964608,1,'all',0,0),(23,4,7,12,NULL,0,1,1,'all',0,0),(24,4,8,12,NULL,0,1056964608,1,'all',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES (7,NULL,4,'action',1),(8,NULL,4,'entity',1);
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
INSERT INTO `acl_object_identity_ancestors` VALUES (7,7),(8,8);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
INSERT INTO `acl_security_identities` VALUES (10,'ROLE_ADMINISTRATOR',0),(11,'ROLE_CATALOG_MANAGER',0),(12,'ROLE_USER',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_instance`
--

LOCK TABLES `akeneo_batch_job_instance` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_instance` DISABLE KEYS */;
INSERT INTO `akeneo_batch_job_instance` VALUES (148,'csv_footwear_product_import','CSV footwear product import','csv_product_import',0,'Akeneo CSV Connector','a:17:{s:8:\"filePath\";s:26:\"/tmp/footwear_products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(149,'csv_footwear_product_export','CSV footwear product export','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}i:2;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2014_collection\";}}}s:9:\"structure\";a:2:{s:5:\"scope\";s:6:\"mobile\";s:7:\"locales\";a:1:{i:0;s:5:\"en_US\";}}}}','export'),(150,'csv_footwear_category_import','CSV footwear category import','csv_category_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(151,'csv_footwear_category_export','CSV footwear category export','csv_category_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:17:\"/tmp/category.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(152,'csv_footwear_association_type_import','CSV footwear association type import','csv_association_type_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(153,'csv_footwear_association_type_export','CSV footwear association type export','csv_association_type_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:25:\"/tmp/association_type.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(154,'csv_footwear_group_import','CSV footwear group import','csv_group_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(155,'xlsx_footwear_group_import','XLSX footwear group import','xlsx_group_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/group.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(156,'csv_footwear_group_export','CSV footwear group export','csv_group_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:14:\"/tmp/group.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(157,'csv_footwear_attribute_import','CSV footwear attribute import','csv_attribute_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(158,'csv_footwear_attribute_export','CSV footwear attribute export','csv_attribute_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:18:\"/tmp/attribute.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(159,'csv_footwear_option_import','CSV footwear option import','csv_attribute_option_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(160,'csv_footwear_option_export','CSV footwear option export','csv_attribute_option_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/option.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(161,'csv_footwear_family_export','CSV footwear family export','csv_family_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/family.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(162,'csv_footwear_channel_export','CSV footwear channel export','csv_channel_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:16:\"/tmp/channel.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(163,'csv_footwear_currency_export','CSV footwear currency export','csv_currency_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:17:\"/tmp/currency.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(164,'csv_footwear_locale_export','CSV footwear locale export','csv_locale_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/locale.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(165,'csv_footwear_group_type_export','CSV footwear group type export','csv_group_type_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:19:\"/tmp/group_type.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(166,'csv_footwear_attribute_group_export','CSV footwear attribute group export','csv_attribute_group_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:24:\"/tmp/attribute_group.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(167,'csv_footwear_family_import','CSV footwear family import','csv_family_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(168,'csv_footwear_currency_import','CSV footwear currency import','csv_currency_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(169,'csv_footwear_channel_import','CSV footwear channel import','csv_channel_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(170,'csv_footwear_locale_import','CSV footwear locale import','csv_locale_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(171,'csv_footwear_group_type_import','CSV footwear group_type import','csv_group_type_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(172,'csv_footwear_attribute_group_import','CSV footwear attribute_group import','csv_attribute_group_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(173,'update_product_value','Mass update products','update_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(174,'add_product_value','Mass add products values','add_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(175,'remove_product_value','Mass remove products values','remove_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(176,'move_to_category','Mass move to categories','move_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(177,'add_association','Mass associate products','add_association',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(178,'add_to_category','Mass add to categories','add_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(179,'add_to_group','Mass add product to group','add_to_group',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(180,'remove_from_category','Mass remove from categories','remove_from_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(181,'edit_common_attributes','Mass edit product attributes','edit_common_attributes',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(182,'add_attribute_value','Mass add attribute value','add_attribute_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(183,'set_attribute_requirements','Set family attribute requirements','set_attribute_requirements',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(184,'add_to_existing_product_model','Add to existing product model','add_to_existing_product_model',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(185,'delete_products_and_product_models','Mass delete products','delete_products_and_product_models',0,'Akeneo Mass Edit Connector','a:0:{}','mass_delete'),(186,'csv_product_quick_export','CSV product quick export','csv_product_quick_export',0,'Akeneo CSV Connector','a:14:{s:8:\"filePath\";s:42:\"/tmp/php/export_%job_label%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:43:\"/tmp/1_products_export_%locale%_%scope%.csv\";s:20:\"filePathProductModel\";s:49:\"/tmp/2_product_models_export_%locale%_%scope%.csv\";}','quick_export'),(187,'xlsx_footwear_product_import_fr','XLSX footwear product import FR','xlsx_product_import',0,'Akeneo XLSX Connector','a:14:{s:8:\"filePath\";s:30:\"/tmp/footwear_products-fr.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\",\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(188,'xlsx_footwear_product_import','XLSX footwear product import','xlsx_product_import',0,'Akeneo XLSX Connector','a:14:{s:8:\"filePath\";s:27:\"/tmp/footwear_products.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(189,'xlsx_footwear_category_import','XLSX footwear category import','xlsx_category_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(190,'xlsx_footwear_association_type_import','XLSX footwear association type import','xlsx_association_type_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(191,'xlsx_footwear_attribute_import','XLSX footwear attribute import','xlsx_attribute_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(192,'xlsx_footwear_option_import','XLSX footwear option import','xlsx_attribute_option_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(193,'xlsx_footwear_family_import','XLSX footwear family import','xlsx_family_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(194,'xlsx_footwear_currency_import','XLSX footwear currency import','xlsx_currency_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(195,'xlsx_footwear_channel_import','XLSX footwear channel import','xlsx_channel_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(196,'xlsx_footwear_locale_import','XLSX footwear locale import','xlsx_locale_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(197,'xlsx_footwear_group_type_import','XLSX footwear group_type import','xlsx_group_type_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(198,'xlsx_footwear_attribute_group_import','XLSX footwear attribute_group import','xlsx_attribute_group_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(199,'xlsx_product_quick_export','XLSX product quick export','xlsx_product_quick_export',0,'Akeneo XLSX Connector','a:13:{s:8:\"filePath\";s:43:\"/tmp/php/export_%job_label%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:44:\"/tmp/1_products_export_%locale%_%scope%.xlsx\";s:20:\"filePathProductModel\";s:50:\"/tmp/2_product_models_export_%locale%_%scope%.xlsx\";}','quick_export'),(200,'xlsx_footwear_family_export','XLSX footwear family export','xlsx_family_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:16:\"/tmp/family.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(201,'xlsx_footwear_category_export','XLSX footwear category export','xlsx_category_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:18:\"/tmp/category.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(202,'xlsx_footwear_attribute_export','XLSX footwear attribute export','xlsx_attribute_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:19:\"/tmp/attribute.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(203,'xlsx_footwear_option_export','XLSX footwear option export','xlsx_attribute_option_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:16:\"/tmp/option.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(204,'xlsx_footwear_association_type_export','XLSX footwear association type export','xlsx_association_type_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:26:\"/tmp/association_type.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(205,'xlsx_footwear_channel_export','XLSX footwear channel export','xlsx_channel_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:17:\"/tmp/channel.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(206,'xlsx_footwear_currency_export','XLSX footwear currency export','xlsx_currency_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:18:\"/tmp/currency.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(207,'xlsx_footwear_locale_export','XLSX footwear locale export','xlsx_locale_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:16:\"/tmp/locale.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(208,'xlsx_footwear_group_type_export','XLSX footwear group type export','xlsx_group_type_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:20:\"/tmp/group_type.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(209,'xlsx_footwear_attribute_group_export','XLSX footwear attribute group export','xlsx_attribute_group_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:25:\"/tmp/attribute_group.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(210,'csv_product_grid_context_quick_export','CSV product quick export grid context','csv_product_grid_context_quick_export',0,'Akeneo CSV Connector','a:14:{s:8:\"filePath\";s:42:\"/tmp/php/export_%job_label%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:56:\"/tmp/1_products_export_grid_context_%locale%_%scope%.csv\";s:20:\"filePathProductModel\";s:62:\"/tmp/2_product_models_export_grid_context_%locale%_%scope%.csv\";}','quick_export'),(211,'xlsx_product_grid_context_quick_export','XLSX product quick export grid context','xlsx_product_grid_context_quick_export',0,'Akeneo XLSX Connector','a:13:{s:8:\"filePath\";s:43:\"/tmp/php/export_%job_label%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:57:\"/tmp/1_products_export_grid_context_%locale%_%scope%.xlsx\";s:20:\"filePathProductModel\";s:63:\"/tmp/2_product_models_export_grid_context_%locale%_%scope%.xlsx\";}','quick_export'),(212,'compute_product_models_descendants','Compute product models descendants','compute_product_models_descendants',0,'internal','a:0:{}','compute_product_models_descendants'),(213,'compute_completeness_of_products_family','compute completeness of products family','compute_completeness_of_products_family',0,'internal','a:0:{}','compute_completeness_of_products_family'),(214,'compute_family_variant_structure_changes','Compute variant structure changes','compute_family_variant_structure_changes',0,'internal','a:0:{}','compute_family_variant_structure_changes');
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
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_group`
--

LOCK TABLES `oro_access_group` WRITE;
/*!40000 ALTER TABLE `oro_access_group` DISABLE KEYS */;
INSERT INTO `oro_access_group` VALUES (16,'All'),(13,'IT support'),(14,'Manager'),(15,'Redactor');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_role`
--

LOCK TABLES `oro_access_role` WRITE;
/*!40000 ALTER TABLE `oro_access_role` DISABLE KEYS */;
INSERT INTO `oro_access_role` VALUES (10,'ROLE_ADMINISTRATOR','Administrator'),(11,'ROLE_CATALOG_MANAGER','Catalog manager'),(12,'ROLE_USER','User');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user`
--

LOCK TABLES `oro_user` WRITE;
/*!40000 ALTER TABLE `oro_user` DISABLE KEYS */;
INSERT INTO `oro_user` VALUES (14,688,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,NULL,1,'lzzedd8rwcg04sg00ow8g4oo8gskwks','CZ9BhxlpWpwgikio63s9J/usi6ai++R8mLFIupatnVwFUDGVbD2L5WBGmf06Wm3HUjmhFLs5LhKX5KzBMcTNBA==',NULL,NULL,NULL,0,'2018-05-30 13:22:11','2018-05-30 13:22:11','[]',0,NULL,'UTC',688,8,33),(15,688,'Julia','Julia@example.com',NULL,'Julia',NULL,'Stark',NULL,NULL,NULL,1,'9pu9lux8bhssoko0ssskcsk8c8cok44','7F3qqNOyDXEIMKveIPhQttiWH7zQmYFPOkmP0P4SKRRjSNMoE2CAtQxIvKSoAbt0glOr/06V/yT3MiA1CR88VA==',NULL,NULL,NULL,0,'2018-05-30 13:22:11','2018-05-30 13:22:11','[]',0,NULL,'UTC',688,7,33),(16,688,'Peter','Peter@example.com',NULL,'Peter',NULL,'Williams',NULL,NULL,NULL,1,'l2t090gf3v4c0os4ogsgw08ckso40ks','DefJebsqiHe3LLYyfaIow4ff/f/xuBMGLnE6O8kjX5DRPuZUVqWVYrJZHTDzz2OxERagLHfqjx9244I76DK4Ag==',NULL,NULL,NULL,0,'2018-05-30 13:22:11','2018-05-30 13:22:11','[]',0,NULL,'UTC',688,8,33),(17,688,'Mary','Mary@example.com',NULL,'Mary',NULL,'Smith',NULL,NULL,NULL,1,'ins46z5j2rcwws8wo004cowo8cogc48','9dVKjxh914xO6zmI1MgYE42lU25Ebi4KD6kx39G7dScb7c+pmSxoGWNmE92SLT/PEvLhqHiFoLaVRM5Y/O7Agg==',NULL,NULL,NULL,0,'2018-05-30 13:22:11','2018-05-30 13:22:11','[]',0,NULL,'UTC',688,7,33),(18,688,'Sandra','Sandra@example.com',NULL,'Sandra',NULL,'Harvey',NULL,NULL,NULL,1,'r3iw9fpcdqo8g88sks8og0cgkoossc','ys7Afr1SpTqYCVkeXTkYEwTBIwAuc6EUwwyvA/5Pn4BC4wAMBLacZXRYF5khgvXFMH6wyuZfhtwQZvSc9oNRWA==',NULL,NULL,NULL,0,'2018-05-30 13:22:11','2018-05-30 13:22:11','[]',0,NULL,'UTC',688,8,33),(19,720,'Julien','Julien@example.com',NULL,'Julien',NULL,'Février',NULL,NULL,NULL,1,'3x03sd8uq2iocsss88g0kowo0cscw8o','uoo0b2Vswbpj/Vl1KYnEbH+AnYPLN4mkBhbuYka3tbohvtw58yufEk53f2TwzRcSIwqLNd/r/BEm9S7S4dQ1LA==',NULL,NULL,NULL,0,'2018-05-30 13:22:11','2018-05-30 13:22:11','[]',0,NULL,'UTC',720,8,33);
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
INSERT INTO `oro_user_access_group` VALUES (14,13),(14,16),(15,14),(15,16),(16,13),(16,16),(17,15),(17,16),(18,15),(18,16),(19,15),(19,16);
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
INSERT INTO `oro_user_access_role` VALUES (14,10),(15,11),(16,10),(17,12),(18,12),(19,12);
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type`
--

LOCK TABLES `pim_catalog_association_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type` VALUES (13,'X_SELL','2018-05-30 13:22:09','2018-05-30 13:22:09'),(14,'UPSELL','2018-05-30 13:22:09','2018-05-30 13:22:09'),(15,'SUBSTITUTION','2018-05-30 13:22:09','2018-05-30 13:22:09'),(16,'PACK','2018-05-30 13:22:09','2018-05-30 13:22:09');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type_translation`
--

LOCK TABLES `pim_catalog_association_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type_translation` VALUES (27,13,'Cross sell','en_US'),(28,14,'Upsell','en_US'),(29,15,'Substitution','en_US'),(30,16,'Pack','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute`
--

LOCK TABLES `pim_catalog_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES (30,9,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,1,1,0,0,'sku','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_identifier','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(31,9,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'name','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(32,9,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'manufacturer','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";b:1;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:10'),(33,9,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'weather_conditions','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:10'),(34,9,5,1,1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'description','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','textarea','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(35,14,7,1,255,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'comment','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(36,10,1,1,NULL,NULL,NULL,NULL,1.0000,200.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'price','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(37,10,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'rating','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:10'),(38,13,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'side_view','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(39,13,2,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'top_view','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(40,11,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:10'),(41,12,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'color','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:10'),(42,12,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'lace_color','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:10'),(43,9,10,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,'Length','CENTIMETER',NULL,'',NULL,0,0,0,0,'length','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(44,9,20,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,'Volume','CUBIC_MILLIMETER',NULL,'',NULL,0,0,0,0,'volume','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(45,14,8,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'number_in_stock','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(46,14,25,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'destocking_date','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_date','date','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(47,14,30,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'handmade','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(48,14,30,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'heel_color','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:5:\"color\";}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(49,14,35,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'sole_color','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:5:\"color\";}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(50,14,35,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'cap_color','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:5:\"color\";}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(51,14,40,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'sole_fabric','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(52,14,45,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'lace_fabric','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(53,10,1,1,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'rate_sale','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(54,9,30,1,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'Weight','GRAM',NULL,'',NULL,0,0,0,0,'weight','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(55,14,1,1,255,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'123','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09'),(56,13,2,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,1,1,'rear_view','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-05-30 13:22:09','2018-05-30 13:22:09');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group`
--

LOCK TABLES `pim_catalog_attribute_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group` VALUES (9,'info',1,'2018-05-30 13:22:08','2018-05-30 13:22:08'),(10,'marketing',2,'2018-05-30 13:22:08','2018-05-30 13:22:08'),(11,'sizes',3,'2018-05-30 13:22:08','2018-05-30 13:22:08'),(12,'colors',4,'2018-05-30 13:22:08','2018-05-30 13:22:08'),(13,'media',5,'2018-05-30 13:22:08','2018-05-30 13:22:08'),(14,'other',100,'2018-05-30 13:22:08','2018-05-30 13:22:08');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group_translation`
--

LOCK TABLES `pim_catalog_attribute_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group_translation` VALUES (21,9,'Product information','en_US'),(22,10,'Marketing','en_US'),(23,11,'Sizes','en_US'),(24,12,'Colors','en_US'),(25,13,'Media','en_US'),(26,14,'Other','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option`
--

LOCK TABLES `pim_catalog_attribute_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option` VALUES (54,32,'Converse',1),(55,32,'TimberLand',2),(56,32,'Nike',3),(57,32,'Caterpillar',4),(58,33,'dry',1),(59,33,'wet',2),(60,33,'hot',3),(61,33,'cold',4),(62,33,'snowy',5),(63,37,'1',1),(64,37,'2',2),(65,37,'3',3),(66,37,'4',4),(67,37,'5',5),(68,40,'35',1),(69,40,'36',2),(70,40,'37',3),(71,40,'38',4),(72,40,'39',5),(73,40,'40',6),(74,40,'41',7),(75,40,'42',8),(76,40,'43',9),(77,40,'44',10),(78,40,'45',11),(79,40,'46',12),(80,40,'60',13),(81,41,'white',1),(82,41,'black',2),(83,41,'blue',3),(84,41,'maroon',4),(85,41,'saddle',5),(86,41,'greem',6),(87,41,'red',7),(88,41,'charcoal',8),(89,42,'laces_black',1),(90,42,'laces_brown',2),(91,42,'laces_white',3);
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
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option_value`
--

LOCK TABLES `pim_catalog_attribute_option_value` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option_value` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option_value` VALUES (138,54,'en_US','Converse'),(139,55,'en_US','TimberLand'),(140,56,'en_US','Nike'),(141,57,'en_US','Caterpillar'),(142,58,'en_US','Dry'),(143,59,'en_US','Wet'),(144,60,'en_US','Hot'),(145,61,'en_US','Cold'),(146,62,'en_US','Snowy'),(147,63,'en_US','1 star'),(148,64,'en_US','2 stars'),(149,65,'en_US','3 stars'),(150,66,'en_US','4 stars'),(151,67,'en_US','5 stars'),(152,68,'en_US','35'),(153,69,'en_US','36'),(154,70,'en_US','37'),(155,71,'en_US','38'),(156,72,'en_US','39'),(157,73,'en_US','40'),(158,74,'en_US','41'),(159,75,'en_US','42'),(160,76,'en_US','43'),(161,77,'en_US','44'),(162,78,'en_US','45'),(163,79,'en_US','46'),(164,80,'en_US','60'),(165,81,'en_US','White'),(166,82,'en_US','Black'),(167,83,'en_US','Blue'),(168,84,'en_US','Maroon'),(169,85,'en_US','Saddle'),(170,86,'en_US','Greem'),(171,87,'en_US','Red'),(172,88,'en_US','Charcoal'),(173,89,'en_US','Black'),(174,90,'en_US','Brown'),(175,91,'en_US','White');
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
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

LOCK TABLES `pim_catalog_attribute_requirement` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_requirement` VALUES (71,4,30,8,1),(72,4,30,7,1),(73,4,31,7,1),(74,4,34,7,1),(75,4,33,7,1),(76,4,36,7,1),(77,4,37,7,1),(78,4,38,7,1),(79,4,40,7,1),(80,4,41,7,1),(81,4,31,8,1),(82,4,36,8,1),(83,4,40,8,1),(84,4,41,8,1),(85,5,30,8,1),(86,5,30,7,1),(87,5,31,7,1),(88,5,34,7,1),(89,5,36,7,1),(90,5,38,7,1),(91,5,40,7,1),(92,5,41,7,1),(93,5,48,7,1),(94,5,49,7,1),(95,5,31,8,1),(96,5,36,8,1),(97,5,40,8,1),(98,5,41,8,1),(99,5,48,8,1),(100,5,49,8,1),(101,6,30,8,1),(102,6,30,7,1),(103,6,31,7,1),(104,6,34,7,1),(105,6,33,7,1),(106,6,36,7,1),(107,6,37,7,1),(108,6,38,7,1),(109,6,40,7,1),(110,6,41,7,1),(111,6,31,8,1),(112,6,36,8,1),(113,6,40,8,1),(114,6,41,8,1),(115,7,30,8,1),(116,7,30,7,1),(117,7,31,7,1),(118,7,34,7,1),(119,7,36,7,1),(120,7,37,7,1),(121,7,38,7,1),(122,7,40,7,1),(123,7,41,7,1),(124,7,31,8,1),(125,7,36,8,1),(126,7,40,8,1),(127,7,41,8,1),(128,8,30,8,1),(129,8,30,7,1),(130,8,31,7,1),(131,8,34,7,1),(132,8,36,7,1),(133,8,37,7,1),(134,8,38,7,1),(135,8,40,7,1),(136,8,41,7,1),(137,8,31,8,1),(138,8,36,8,1),(139,8,40,8,1),(140,8,41,8,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_translation`
--

LOCK TABLES `pim_catalog_attribute_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (85,30,'SKU','en_US'),(86,31,'Name','en_US'),(87,32,'Manufacturer','en_US'),(88,33,'Weather conditions','en_US'),(89,34,'Description','en_US'),(90,35,'Comment','en_US'),(91,36,'Price','en_US'),(92,37,'Rating','en_US'),(93,38,'Side view','en_US'),(94,39,'Top view','en_US'),(95,40,'Size','en_US'),(96,41,'Color','en_US'),(97,42,'Lace color','en_US'),(98,43,'Length','en_US'),(99,43,'Longueur','fr_FR'),(100,44,'Volume','en_US'),(101,44,'Volume','fr_FR'),(102,45,'Number in stock','en_US'),(103,46,'Destocking date','en_US'),(104,46,'Date de déstockage','fr_FR'),(105,47,'Handmade','en_US'),(106,48,'Heel color','en_US'),(107,49,'Sole color','en_US'),(108,50,'Cap color','en_US'),(109,51,'Sole fabric','en_US'),(110,52,'Lace fabric','en_US'),(111,53,'Rate of sale','en_US'),(112,53,'Taux de vente','fr_FR'),(113,54,'Weight','en_US'),(114,54,'Poids','fr_FR'),(115,55,'Attribute 123','en_US'),(116,56,'Rear view','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category`
--

LOCK TABLES `pim_catalog_category` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
INSERT INTO `pim_catalog_category` VALUES (33,NULL,'2014_collection','2018-05-30 13:22:08',33,0,1,10),(34,33,'summer_collection','2018-05-30 13:22:08',33,1,2,5),(35,33,'winter_collection','2018-05-30 13:22:08',33,1,6,9),(36,35,'winter_boots','2018-05-30 13:22:08',33,2,7,8),(37,34,'sandals','2018-05-30 13:22:08',33,2,3,4);
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
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_translation`
--

LOCK TABLES `pim_catalog_category_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_translation` VALUES (94,33,'2014 collection','en_US'),(95,34,'Summer collection','en_US'),(96,35,'Winter collection','en_US'),(97,36,'Winter boots','en_US'),(98,37,'Sandals','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel`
--

LOCK TABLES `pim_catalog_channel` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel` VALUES (7,33,'tablet','a:0:{}'),(8,33,'mobile','a:0:{}');
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
INSERT INTO `pim_catalog_channel_currency` VALUES (7,883),(7,884),(8,884);
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
INSERT INTO `pim_catalog_channel_locale` VALUES (7,688),(8,688),(8,720);
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_translation`
--

LOCK TABLES `pim_catalog_channel_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_translation` VALUES (19,7,'Tablet','en_US'),(20,7,'Tablet','de_DE'),(21,7,'Tablette','fr_FR'),(22,8,'Mobile','en_US'),(23,8,'Mobil','de_DE'),(24,8,'Mobile','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=1177 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_currency`
--

LOCK TABLES `pim_catalog_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (883,'USD',1),(884,'EUR',1),(885,'ADP',0),(886,'AED',0),(887,'AFA',0),(888,'AFN',0),(889,'ALK',0),(890,'ALL',0),(891,'AMD',0),(892,'ANG',0),(893,'AOA',0),(894,'AOK',0),(895,'AON',0),(896,'AOR',0),(897,'ARA',0),(898,'ARL',0),(899,'ARM',0),(900,'ARP',0),(901,'ARS',0),(902,'ATS',0),(903,'AUD',0),(904,'AWG',0),(905,'AZM',0),(906,'AZN',0),(907,'BAD',0),(908,'BAM',0),(909,'BAN',0),(910,'BBD',0),(911,'BDT',0),(912,'BEC',0),(913,'BEF',0),(914,'BEL',0),(915,'BGL',0),(916,'BGM',0),(917,'BGN',0),(918,'BGO',0),(919,'BHD',0),(920,'BIF',0),(921,'BMD',0),(922,'BND',0),(923,'BOB',0),(924,'BOL',0),(925,'BOP',0),(926,'BOV',0),(927,'BRB',0),(928,'BRC',0),(929,'BRE',0),(930,'BRL',0),(931,'BRN',0),(932,'BRR',0),(933,'BRZ',0),(934,'BSD',0),(935,'BTN',0),(936,'BUK',0),(937,'BWP',0),(938,'BYB',0),(939,'BYR',0),(940,'BZD',0),(941,'CAD',0),(942,'CDF',0),(943,'CHE',0),(944,'CHF',0),(945,'CHW',0),(946,'CLE',0),(947,'CLF',0),(948,'CLP',0),(949,'CNX',0),(950,'CNY',0),(951,'COP',0),(952,'COU',0),(953,'CRC',0),(954,'CSD',0),(955,'CSK',0),(956,'CUC',0),(957,'CUP',0),(958,'CVE',0),(959,'CYP',0),(960,'CZK',0),(961,'DDM',0),(962,'DEM',0),(963,'DJF',0),(964,'DKK',0),(965,'DOP',0),(966,'DZD',0),(967,'ECS',0),(968,'ECV',0),(969,'EEK',0),(970,'EGP',0),(971,'ERN',0),(972,'ESA',0),(973,'ESB',0),(974,'ESP',0),(975,'ETB',0),(976,'FIM',0),(977,'FJD',0),(978,'FKP',0),(979,'FRF',0),(980,'GBP',0),(981,'GEK',0),(982,'GEL',0),(983,'GHC',0),(984,'GHS',0),(985,'GIP',0),(986,'GMD',0),(987,'GNF',0),(988,'GNS',0),(989,'GQE',0),(990,'GRD',0),(991,'GTQ',0),(992,'GWE',0),(993,'GWP',0),(994,'GYD',0),(995,'HKD',0),(996,'HNL',0),(997,'HRD',0),(998,'HRK',0),(999,'HTG',0),(1000,'HUF',0),(1001,'IDR',0),(1002,'IEP',0),(1003,'ILP',0),(1004,'ILR',0),(1005,'ILS',0),(1006,'INR',0),(1007,'IQD',0),(1008,'IRR',0),(1009,'ISJ',0),(1010,'ISK',0),(1011,'ITL',0),(1012,'JMD',0),(1013,'JOD',0),(1014,'JPY',0),(1015,'KES',0),(1016,'KGS',0),(1017,'KHR',0),(1018,'KMF',0),(1019,'KPW',0),(1020,'KRH',0),(1021,'KRO',0),(1022,'KRW',0),(1023,'KWD',0),(1024,'KYD',0),(1025,'KZT',0),(1026,'LAK',0),(1027,'LBP',0),(1028,'LKR',0),(1029,'LRD',0),(1030,'LSL',0),(1031,'LTL',0),(1032,'LTT',0),(1033,'LUC',0),(1034,'LUF',0),(1035,'LUL',0),(1036,'LVL',0),(1037,'LVR',0),(1038,'LYD',0),(1039,'MAD',0),(1040,'MAF',0),(1041,'MCF',0),(1042,'MDC',0),(1043,'MDL',0),(1044,'MGA',0),(1045,'MGF',0),(1046,'MKD',0),(1047,'MKN',0),(1048,'MLF',0),(1049,'MMK',0),(1050,'MNT',0),(1051,'MOP',0),(1052,'MRO',0),(1053,'MTL',0),(1054,'MTP',0),(1055,'MUR',0),(1056,'MVP',0),(1057,'MVR',0),(1058,'MWK',0),(1059,'MXN',0),(1060,'MXP',0),(1061,'MXV',0),(1062,'MYR',0),(1063,'MZE',0),(1064,'MZM',0),(1065,'MZN',0),(1066,'NAD',0),(1067,'NGN',0),(1068,'NIC',0),(1069,'NIO',0),(1070,'NLG',0),(1071,'NOK',0),(1072,'NPR',0),(1073,'NZD',0),(1074,'OMR',0),(1075,'PAB',0),(1076,'PEI',0),(1077,'PEN',0),(1078,'PES',0),(1079,'PGK',0),(1080,'PHP',0),(1081,'PKR',0),(1082,'PLN',0),(1083,'PLZ',0),(1084,'PTE',0),(1085,'PYG',0),(1086,'QAR',0),(1087,'RHD',0),(1088,'ROL',0),(1089,'RON',0),(1090,'RSD',0),(1091,'RUB',0),(1092,'RUR',0),(1093,'RWF',0),(1094,'SAR',0),(1095,'SBD',0),(1096,'SCR',0),(1097,'SDD',0),(1098,'SDG',0),(1099,'SDP',0),(1100,'SEK',0),(1101,'SGD',0),(1102,'SHP',0),(1103,'SIT',0),(1104,'SKK',0),(1105,'SLL',0),(1106,'SOS',0),(1107,'SRD',0),(1108,'SRG',0),(1109,'SSP',0),(1110,'STD',0),(1111,'SUR',0),(1112,'SVC',0),(1113,'SYP',0),(1114,'SZL',0),(1115,'THB',0),(1116,'TJR',0),(1117,'TJS',0),(1118,'TMM',0),(1119,'TMT',0),(1120,'TND',0),(1121,'TOP',0),(1122,'TPE',0),(1123,'TRL',0),(1124,'TRY',0),(1125,'TTD',0),(1126,'TWD',0),(1127,'TZS',0),(1128,'UAH',0),(1129,'UAK',0),(1130,'UGS',0),(1131,'UGX',0),(1132,'USN',0),(1133,'USS',0),(1134,'UYI',0),(1135,'UYP',0),(1136,'UYU',0),(1137,'UZS',0),(1138,'VEB',0),(1139,'VEF',0),(1140,'VND',0),(1141,'VNN',0),(1142,'VUV',0),(1143,'WST',0),(1144,'XAF',0),(1145,'XAU',0),(1146,'XBA',0),(1147,'XBB',0),(1148,'XBC',0),(1149,'XBD',0),(1150,'XCD',0),(1151,'XDR',0),(1152,'XEU',0),(1153,'XFO',0),(1154,'XFU',0),(1155,'XOF',0),(1156,'XPD',0),(1157,'XPF',0),(1158,'XRE',0),(1159,'XSU',0),(1160,'XTS',0),(1161,'XXX',0),(1162,'YDD',0),(1163,'YER',0),(1164,'YUD',0),(1165,'YUM',0),(1166,'YUN',0),(1167,'YUR',0),(1168,'ZAL',0),(1169,'ZAR',0),(1170,'ZMK',0),(1171,'ZMW',0),(1172,'ZRN',0),(1173,'ZRZ',0),(1174,'ZWD',0),(1175,'ZWL',0),(1176,'ZWR',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family`
--

LOCK TABLES `pim_catalog_family` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
INSERT INTO `pim_catalog_family` VALUES (4,31,NULL,'boots','2018-05-30 13:22:10','2018-05-30 13:22:10'),(5,31,NULL,'heels','2018-05-30 13:22:10','2018-05-30 13:22:10'),(6,31,NULL,'sneakers','2018-05-30 13:22:10','2018-05-30 13:22:10'),(7,31,38,'sandals','2018-05-30 13:22:10','2018-05-30 13:22:10'),(8,31,NULL,'led_tvs','2018-05-30 13:22:10','2018-05-30 13:22:10');
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
INSERT INTO `pim_catalog_family_attribute` VALUES (4,30),(4,31),(4,32),(4,33),(4,34),(4,36),(4,37),(4,38),(4,39),(4,40),(4,41),(4,42),(5,30),(5,31),(5,32),(5,34),(5,36),(5,38),(5,39),(5,40),(5,41),(5,48),(5,49),(5,51),(6,30),(6,31),(6,32),(6,33),(6,34),(6,36),(6,37),(6,38),(6,39),(6,40),(6,41),(6,42),(7,30),(7,31),(7,32),(7,34),(7,36),(7,37),(7,38),(7,40),(7,41),(8,30),(8,31),(8,32),(8,34),(8,36),(8,37),(8,38),(8,40),(8,41);
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_translation`
--

LOCK TABLES `pim_catalog_family_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_family_translation` VALUES (10,4,'Boots','en_US'),(11,5,'Heels','en_US'),(12,6,'Sneakers','en_US'),(13,7,'Sandals','en_US'),(14,8,'LED TVs','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group`
--

LOCK TABLES `pim_catalog_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_group` VALUES (2,4,'similar_boots');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_translation`
--

LOCK TABLES `pim_catalog_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_translation` VALUES (4,2,'Similar boots','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type`
--

LOCK TABLES `pim_catalog_group_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type` VALUES (4,'RELATED'),(5,'XSELL');
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
INSERT INTO `pim_catalog_group_type_translation` VALUES (3,4,'[RELATED]','en_US'),(4,5,'[XSELL]','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=841 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_locale`
--

LOCK TABLES `pim_catalog_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_locale` VALUES (631,'af_ZA',0),(632,'am_ET',0),(633,'ar_AE',0),(634,'ar_BH',0),(635,'ar_DZ',0),(636,'ar_EG',0),(637,'ar_IQ',0),(638,'ar_JO',0),(639,'ar_KW',0),(640,'ar_LB',0),(641,'ar_LY',0),(642,'ar_MA',0),(643,'arn_CL',0),(644,'ar_OM',0),(645,'ar_QA',0),(646,'ar_SA',0),(647,'ar_SY',0),(648,'ar_TN',0),(649,'ar_YE',0),(650,'as_IN',0),(651,'az_Cyrl_AZ',0),(652,'az_Latn_AZ',0),(653,'ba_RU',0),(654,'be_BY',0),(655,'bg_BG',0),(656,'bn_BD',0),(657,'bn_IN',0),(658,'bo_CN',0),(659,'br_FR',0),(660,'bs_Cyrl_BA',0),(661,'bs_Latn_BA',0),(662,'ca_ES',0),(663,'co_FR',0),(664,'cs_CZ',0),(665,'cy_GB',0),(666,'da_DK',0),(667,'de_AT',0),(668,'de_CH',0),(669,'de_DE',0),(670,'de_LI',0),(671,'de_LU',0),(672,'dsb_DE',0),(673,'dv_MV',0),(674,'el_GR',0),(675,'en_029',0),(676,'en_AU',0),(677,'en_BZ',0),(678,'en_CA',0),(679,'en_GB',0),(680,'en_IE',0),(681,'en_IN',0),(682,'en_JM',0),(683,'en_MY',0),(684,'en_NZ',0),(685,'en_PH',0),(686,'en_SG',0),(687,'en_TT',0),(688,'en_US',1),(689,'en_ZA',0),(690,'en_ZW',0),(691,'es_AR',0),(692,'es_BO',0),(693,'es_CL',0),(694,'es_CO',0),(695,'es_CR',0),(696,'es_DO',0),(697,'es_EC',0),(698,'es_ES',0),(699,'es_GT',0),(700,'es_HN',0),(701,'es_MX',0),(702,'es_NI',0),(703,'es_PA',0),(704,'es_PE',0),(705,'es_PR',0),(706,'es_PY',0),(707,'es_SV',0),(708,'es_US',0),(709,'es_UY',0),(710,'es_VE',0),(711,'et_EE',0),(712,'eu_ES',0),(713,'fa_IR',0),(714,'fi_FI',0),(715,'fil_PH',0),(716,'fo_FO',0),(717,'fr_BE',0),(718,'fr_CA',0),(719,'fr_CH',0),(720,'fr_FR',1),(721,'fr_LU',0),(722,'fr_MC',0),(723,'fy_NL',0),(724,'ga_IE',0),(725,'gd_GB',0),(726,'gl_ES',0),(727,'gsw_FR',0),(728,'gu_IN',0),(729,'ha_Latn_NG',0),(730,'he_IL',0),(731,'hi_IN',0),(732,'hr_BA',0),(733,'hr_HR',0),(734,'hsb_DE',0),(735,'hu_HU',0),(736,'hy_AM',0),(737,'id_ID',0),(738,'ig_NG',0),(739,'ii_CN',0),(740,'is_IS',0),(741,'it_CH',0),(742,'it_IT',0),(743,'iu_Cans_CA',0),(744,'iu_Latn_CA',0),(745,'ja_JP',0),(746,'ka_GE',0),(747,'kk_KZ',0),(748,'kl_GL',0),(749,'km_KH',0),(750,'kn_IN',0),(751,'kok_IN',0),(752,'ko_KR',0),(753,'ky_KG',0),(754,'lb_LU',0),(755,'lo_LA',0),(756,'lt_LT',0),(757,'lv_LV',0),(758,'mi_NZ',0),(759,'mk_MK',0),(760,'ml_IN',0),(761,'mn_MN',0),(762,'mn_Mong_CN',0),(763,'moh_CA',0),(764,'mr_IN',0),(765,'ms_BN',0),(766,'ms_MY',0),(767,'mt_MT',0),(768,'nb_NO',0),(769,'ne_NP',0),(770,'nl_BE',0),(771,'nl_NL',0),(772,'nn_NO',0),(773,'nso_ZA',0),(774,'oc_FR',0),(775,'or_IN',0),(776,'pa_IN',0),(777,'pl_PL',0),(778,'prs_AF',0),(779,'ps_AF',0),(780,'pt_BR',0),(781,'pt_PT',0),(782,'qut_GT',0),(783,'quz_BO',0),(784,'quz_EC',0),(785,'quz_PE',0),(786,'rm_CH',0),(787,'ro_RO',0),(788,'ru_RU',0),(789,'rw_RW',0),(790,'sah_RU',0),(791,'sa_IN',0),(792,'se_FI',0),(793,'se_NO',0),(794,'se_SE',0),(795,'si_LK',0),(796,'sk_SK',0),(797,'sl_SI',0),(798,'sma_NO',0),(799,'sma_SE',0),(800,'smj_NO',0),(801,'smj_SE',0),(802,'smn_FI',0),(803,'sms_FI',0),(804,'sq_AL',0),(805,'sr_Cyrl_BA',0),(806,'sr_Cyrl_CS',0),(807,'sr_Cyrl_ME',0),(808,'sr_Cyrl_RS',0),(809,'sr_Latn_BA',0),(810,'sr_Latn_CS',0),(811,'sr_Latn_ME',0),(812,'sr_Latn_RS',0),(813,'sv_FI',0),(814,'sv_SE',0),(815,'sw_KE',0),(816,'syr_SY',0),(817,'ta_IN',0),(818,'te_IN',0),(819,'tg_Cyrl_TJ',0),(820,'th_TH',0),(821,'tk_TM',0),(822,'tn_ZA',0),(823,'tr_TR',0),(824,'tt_RU',0),(825,'tzm_Latn_DZ',0),(826,'ug_CN',0),(827,'uk_UA',0),(828,'ur_PK',0),(829,'uz_Cyrl_UZ',0),(830,'uz_Latn_UZ',0),(831,'vi_VN',0),(832,'wo_SN',0),(833,'xh_ZA',0),(834,'yo_NG',0),(835,'zh_CN',0),(836,'zh_HK',0),(837,'zh_MO',0),(838,'zh_SG',0),(839,'zh_TW',0),(840,'zu_ZA',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=1544 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_versioning_version`
--

LOCK TABLES `pim_versioning_version` WRITE;
/*!40000 ALTER TABLE `pim_versioning_version` DISABLE KEYS */;
INSERT INTO `pim_versioning_version` VALUES (1187,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','130','a:5:{s:4:\"code\";s:22:\"fixtures_currency_csv0\";s:5:\"label\";s:24:\"Currencies data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_currency_csv0\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Currencies data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1188,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','131','a:5:{s:4:\"code\";s:20:\"fixtures_locale_csv1\";s:5:\"label\";s:21:\"Locales data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_locale_csv1\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Locales data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1189,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','132','a:5:{s:4:\"code\";s:24:\"fixtures_user_group_csv2\";s:5:\"label\";s:25:\"User groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:303:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_group_csv2\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"User groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:303:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1190,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','133','a:5:{s:4:\"code\";s:22:\"fixtures_category_csv3\";s:5:\"label\";s:24:\"Categories data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_category_csv3\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Categories data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1191,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','134','a:5:{s:4:\"code\";s:29:\"fixtures_attribute_group_csv4\";s:5:\"label\";s:30:\"Attribute groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_attribute_group_csv4\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"Attribute groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1192,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','135','a:5:{s:4:\"code\";s:24:\"fixtures_group_type_csv5\";s:5:\"label\";s:25:\"Group types data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:303:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_group_type_csv5\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Group types data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:303:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1193,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','136','a:5:{s:4:\"code\";s:23:\"fixtures_attribute_csv6\";s:5:\"label\";s:24:\"Attributes data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"fixtures_attribute_csv6\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Attributes data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1194,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','137','a:5:{s:4:\"code\";s:21:\"fixtures_channel_csv7\";s:5:\"label\";s:22:\"Channels data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:300:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_channel_csv7\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Channels data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:300:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1195,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','138','a:5:{s:4:\"code\";s:30:\"fixtures_association_type_csv8\";s:5:\"label\";s:26:\"Associations data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:309:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"fixtures_association_type_csv8\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Associations data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:309:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1196,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','139','a:5:{s:4:\"code\";s:31:\"fixtures_attribute_options_csv9\";s:5:\"label\";s:31:\"Attribute options data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:309:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"fixtures_attribute_options_csv9\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"Attribute options data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:309:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1197,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','140','a:5:{s:4:\"code\";s:21:\"fixtures_family_csv10\";s:5:\"label\";s:22:\"Families data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:300:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_family_csv10\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Families data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:300:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1198,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','141','a:5:{s:4:\"code\";s:18:\"fixtures_job_yml11\";s:5:\"label\";s:18:\"Jobs data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:232:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"fixtures_job_yml11\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Jobs data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:232:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1199,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','142','a:5:{s:4:\"code\";s:29:\"fixtures_family_variant_csv12\";s:5:\"label\";s:29:\"Family variants data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:307:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_family_variant_csv12\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Family variants data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:307:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1200,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','143','a:5:{s:4:\"code\";s:20:\"fixtures_group_csv13\";s:5:\"label\";s:20:\"Groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:298:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_group_csv13\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:298:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1201,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','144','a:5:{s:4:\"code\";s:24:\"fixtures_user_role_csv14\";s:5:\"label\";s:24:\"User roles data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_role_csv14\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"User roles data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1202,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','145','a:5:{s:4:\"code\";s:19:\"fixtures_user_csv15\";s:5:\"label\";s:19:\"Users data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:297:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"fixtures_user_csv15\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Users data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:297:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1203,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','146','a:5:{s:4:\"code\";s:28:\"fixtures_product_model_csv16\";s:5:\"label\";s:27:\"Product model data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:492:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"fixtures_product_model_csv16\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Product model data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:492:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1204,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','147','a:5:{s:4:\"code\";s:22:\"fixtures_product_csv17\";s:5:\"label\";s:21:\"Product data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:495:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_product_csv17\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Product data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:495:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:07',0),(1205,'system','Akeneo\\Channel\\Component\\Model\\Locale','631','a:1:{s:4:\"code\";s:5:\"af_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"af_ZA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1206,'system','Akeneo\\Channel\\Component\\Model\\Locale','632','a:1:{s:4:\"code\";s:5:\"am_ET\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"am_ET\";}}',NULL,1,'2018-05-30 13:22:08',0),(1207,'system','Akeneo\\Channel\\Component\\Model\\Locale','633','a:1:{s:4:\"code\";s:5:\"ar_AE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_AE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1208,'system','Akeneo\\Channel\\Component\\Model\\Locale','634','a:1:{s:4:\"code\";s:5:\"ar_BH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_BH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1209,'system','Akeneo\\Channel\\Component\\Model\\Locale','635','a:1:{s:4:\"code\";s:5:\"ar_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_DZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1210,'system','Akeneo\\Channel\\Component\\Model\\Locale','636','a:1:{s:4:\"code\";s:5:\"ar_EG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1211,'system','Akeneo\\Channel\\Component\\Model\\Locale','637','a:1:{s:4:\"code\";s:5:\"ar_IQ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_IQ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1212,'system','Akeneo\\Channel\\Component\\Model\\Locale','638','a:1:{s:4:\"code\";s:5:\"ar_JO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_JO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1213,'system','Akeneo\\Channel\\Component\\Model\\Locale','639','a:1:{s:4:\"code\";s:5:\"ar_KW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_KW\";}}',NULL,1,'2018-05-30 13:22:08',0),(1214,'system','Akeneo\\Channel\\Component\\Model\\Locale','640','a:1:{s:4:\"code\";s:5:\"ar_LB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LB\";}}',NULL,1,'2018-05-30 13:22:08',0),(1215,'system','Akeneo\\Channel\\Component\\Model\\Locale','641','a:1:{s:4:\"code\";s:5:\"ar_LY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1216,'system','Akeneo\\Channel\\Component\\Model\\Locale','642','a:1:{s:4:\"code\";s:5:\"ar_MA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_MA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1217,'system','Akeneo\\Channel\\Component\\Model\\Locale','643','a:1:{s:4:\"code\";s:6:\"arn_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"arn_CL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1218,'system','Akeneo\\Channel\\Component\\Model\\Locale','644','a:1:{s:4:\"code\";s:5:\"ar_OM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_OM\";}}',NULL,1,'2018-05-30 13:22:08',0),(1219,'system','Akeneo\\Channel\\Component\\Model\\Locale','645','a:1:{s:4:\"code\";s:5:\"ar_QA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_QA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1220,'system','Akeneo\\Channel\\Component\\Model\\Locale','646','a:1:{s:4:\"code\";s:5:\"ar_SA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1221,'system','Akeneo\\Channel\\Component\\Model\\Locale','647','a:1:{s:4:\"code\";s:5:\"ar_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1222,'system','Akeneo\\Channel\\Component\\Model\\Locale','648','a:1:{s:4:\"code\";s:5:\"ar_TN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_TN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1223,'system','Akeneo\\Channel\\Component\\Model\\Locale','649','a:1:{s:4:\"code\";s:5:\"ar_YE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_YE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1224,'system','Akeneo\\Channel\\Component\\Model\\Locale','650','a:1:{s:4:\"code\";s:5:\"as_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"as_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1225,'system','Akeneo\\Channel\\Component\\Model\\Locale','651','a:1:{s:4:\"code\";s:10:\"az_Cyrl_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Cyrl_AZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1226,'system','Akeneo\\Channel\\Component\\Model\\Locale','652','a:1:{s:4:\"code\";s:10:\"az_Latn_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Latn_AZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1227,'system','Akeneo\\Channel\\Component\\Model\\Locale','653','a:1:{s:4:\"code\";s:5:\"ba_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ba_RU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1228,'system','Akeneo\\Channel\\Component\\Model\\Locale','654','a:1:{s:4:\"code\";s:5:\"be_BY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"be_BY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1229,'system','Akeneo\\Channel\\Component\\Model\\Locale','655','a:1:{s:4:\"code\";s:5:\"bg_BG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bg_BG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1230,'system','Akeneo\\Channel\\Component\\Model\\Locale','656','a:1:{s:4:\"code\";s:5:\"bn_BD\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_BD\";}}',NULL,1,'2018-05-30 13:22:08',0),(1231,'system','Akeneo\\Channel\\Component\\Model\\Locale','657','a:1:{s:4:\"code\";s:5:\"bn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1232,'system','Akeneo\\Channel\\Component\\Model\\Locale','658','a:1:{s:4:\"code\";s:5:\"bo_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bo_CN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1233,'system','Akeneo\\Channel\\Component\\Model\\Locale','659','a:1:{s:4:\"code\";s:5:\"br_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"br_FR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1234,'system','Akeneo\\Channel\\Component\\Model\\Locale','660','a:1:{s:4:\"code\";s:10:\"bs_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Cyrl_BA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1235,'system','Akeneo\\Channel\\Component\\Model\\Locale','661','a:1:{s:4:\"code\";s:10:\"bs_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Latn_BA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1236,'system','Akeneo\\Channel\\Component\\Model\\Locale','662','a:1:{s:4:\"code\";s:5:\"ca_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ca_ES\";}}',NULL,1,'2018-05-30 13:22:08',0),(1237,'system','Akeneo\\Channel\\Component\\Model\\Locale','663','a:1:{s:4:\"code\";s:5:\"co_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"co_FR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1238,'system','Akeneo\\Channel\\Component\\Model\\Locale','664','a:1:{s:4:\"code\";s:5:\"cs_CZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cs_CZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1239,'system','Akeneo\\Channel\\Component\\Model\\Locale','665','a:1:{s:4:\"code\";s:5:\"cy_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cy_GB\";}}',NULL,1,'2018-05-30 13:22:08',0),(1240,'system','Akeneo\\Channel\\Component\\Model\\Locale','666','a:1:{s:4:\"code\";s:5:\"da_DK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"da_DK\";}}',NULL,1,'2018-05-30 13:22:08',0),(1241,'system','Akeneo\\Channel\\Component\\Model\\Locale','667','a:1:{s:4:\"code\";s:5:\"de_AT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_AT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1242,'system','Akeneo\\Channel\\Component\\Model\\Locale','668','a:1:{s:4:\"code\";s:5:\"de_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_CH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1243,'system','Akeneo\\Channel\\Component\\Model\\Locale','669','a:1:{s:4:\"code\";s:5:\"de_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1244,'system','Akeneo\\Channel\\Component\\Model\\Locale','670','a:1:{s:4:\"code\";s:5:\"de_LI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1245,'system','Akeneo\\Channel\\Component\\Model\\Locale','671','a:1:{s:4:\"code\";s:5:\"de_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1246,'system','Akeneo\\Channel\\Component\\Model\\Locale','672','a:1:{s:4:\"code\";s:6:\"dsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"dsb_DE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1247,'system','Akeneo\\Channel\\Component\\Model\\Locale','673','a:1:{s:4:\"code\";s:5:\"dv_MV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"dv_MV\";}}',NULL,1,'2018-05-30 13:22:08',0),(1248,'system','Akeneo\\Channel\\Component\\Model\\Locale','674','a:1:{s:4:\"code\";s:5:\"el_GR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"el_GR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1249,'system','Akeneo\\Channel\\Component\\Model\\Locale','675','a:1:{s:4:\"code\";s:6:\"en_029\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"en_029\";}}',NULL,1,'2018-05-30 13:22:08',0),(1250,'system','Akeneo\\Channel\\Component\\Model\\Locale','676','a:1:{s:4:\"code\";s:5:\"en_AU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_AU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1251,'system','Akeneo\\Channel\\Component\\Model\\Locale','677','a:1:{s:4:\"code\";s:5:\"en_BZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_BZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1252,'system','Akeneo\\Channel\\Component\\Model\\Locale','678','a:1:{s:4:\"code\";s:5:\"en_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_CA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1253,'system','Akeneo\\Channel\\Component\\Model\\Locale','679','a:1:{s:4:\"code\";s:5:\"en_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_GB\";}}',NULL,1,'2018-05-30 13:22:08',0),(1254,'system','Akeneo\\Channel\\Component\\Model\\Locale','680','a:1:{s:4:\"code\";s:5:\"en_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1255,'system','Akeneo\\Channel\\Component\\Model\\Locale','681','a:1:{s:4:\"code\";s:5:\"en_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1256,'system','Akeneo\\Channel\\Component\\Model\\Locale','682','a:1:{s:4:\"code\";s:5:\"en_JM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_JM\";}}',NULL,1,'2018-05-30 13:22:08',0),(1257,'system','Akeneo\\Channel\\Component\\Model\\Locale','683','a:1:{s:4:\"code\";s:5:\"en_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_MY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1258,'system','Akeneo\\Channel\\Component\\Model\\Locale','684','a:1:{s:4:\"code\";s:5:\"en_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_NZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1259,'system','Akeneo\\Channel\\Component\\Model\\Locale','685','a:1:{s:4:\"code\";s:5:\"en_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_PH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1260,'system','Akeneo\\Channel\\Component\\Model\\Locale','686','a:1:{s:4:\"code\";s:5:\"en_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_SG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1261,'system','Akeneo\\Channel\\Component\\Model\\Locale','687','a:1:{s:4:\"code\";s:5:\"en_TT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_TT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1262,'system','Akeneo\\Channel\\Component\\Model\\Locale','688','a:1:{s:4:\"code\";s:5:\"en_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}}',NULL,1,'2018-05-30 13:22:08',0),(1263,'system','Akeneo\\Channel\\Component\\Model\\Locale','689','a:1:{s:4:\"code\";s:5:\"en_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1264,'system','Akeneo\\Channel\\Component\\Model\\Locale','690','a:1:{s:4:\"code\";s:5:\"en_ZW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZW\";}}',NULL,1,'2018-05-30 13:22:08',0),(1265,'system','Akeneo\\Channel\\Component\\Model\\Locale','691','a:1:{s:4:\"code\";s:5:\"es_AR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_AR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1266,'system','Akeneo\\Channel\\Component\\Model\\Locale','692','a:1:{s:4:\"code\";s:5:\"es_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_BO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1267,'system','Akeneo\\Channel\\Component\\Model\\Locale','693','a:1:{s:4:\"code\";s:5:\"es_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1268,'system','Akeneo\\Channel\\Component\\Model\\Locale','694','a:1:{s:4:\"code\";s:5:\"es_CO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1269,'system','Akeneo\\Channel\\Component\\Model\\Locale','695','a:1:{s:4:\"code\";s:5:\"es_CR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1270,'system','Akeneo\\Channel\\Component\\Model\\Locale','696','a:1:{s:4:\"code\";s:5:\"es_DO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_DO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1271,'system','Akeneo\\Channel\\Component\\Model\\Locale','697','a:1:{s:4:\"code\";s:5:\"es_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_EC\";}}',NULL,1,'2018-05-30 13:22:08',0),(1272,'system','Akeneo\\Channel\\Component\\Model\\Locale','698','a:1:{s:4:\"code\";s:5:\"es_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_ES\";}}',NULL,1,'2018-05-30 13:22:08',0),(1273,'system','Akeneo\\Channel\\Component\\Model\\Locale','699','a:1:{s:4:\"code\";s:5:\"es_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_GT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1274,'system','Akeneo\\Channel\\Component\\Model\\Locale','700','a:1:{s:4:\"code\";s:5:\"es_HN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_HN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1275,'system','Akeneo\\Channel\\Component\\Model\\Locale','701','a:1:{s:4:\"code\";s:5:\"es_MX\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_MX\";}}',NULL,1,'2018-05-30 13:22:08',0),(1276,'system','Akeneo\\Channel\\Component\\Model\\Locale','702','a:1:{s:4:\"code\";s:5:\"es_NI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_NI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1277,'system','Akeneo\\Channel\\Component\\Model\\Locale','703','a:1:{s:4:\"code\";s:5:\"es_PA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1278,'system','Akeneo\\Channel\\Component\\Model\\Locale','704','a:1:{s:4:\"code\";s:5:\"es_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1279,'system','Akeneo\\Channel\\Component\\Model\\Locale','705','a:1:{s:4:\"code\";s:5:\"es_PR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1280,'system','Akeneo\\Channel\\Component\\Model\\Locale','706','a:1:{s:4:\"code\";s:5:\"es_PY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1281,'system','Akeneo\\Channel\\Component\\Model\\Locale','707','a:1:{s:4:\"code\";s:5:\"es_SV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_SV\";}}',NULL,1,'2018-05-30 13:22:08',0),(1282,'system','Akeneo\\Channel\\Component\\Model\\Locale','708','a:1:{s:4:\"code\";s:5:\"es_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_US\";}}',NULL,1,'2018-05-30 13:22:08',0),(1283,'system','Akeneo\\Channel\\Component\\Model\\Locale','709','a:1:{s:4:\"code\";s:5:\"es_UY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_UY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1284,'system','Akeneo\\Channel\\Component\\Model\\Locale','710','a:1:{s:4:\"code\";s:5:\"es_VE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_VE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1285,'system','Akeneo\\Channel\\Component\\Model\\Locale','711','a:1:{s:4:\"code\";s:5:\"et_EE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"et_EE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1286,'system','Akeneo\\Channel\\Component\\Model\\Locale','712','a:1:{s:4:\"code\";s:5:\"eu_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"eu_ES\";}}',NULL,1,'2018-05-30 13:22:08',0),(1287,'system','Akeneo\\Channel\\Component\\Model\\Locale','713','a:1:{s:4:\"code\";s:5:\"fa_IR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fa_IR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1288,'system','Akeneo\\Channel\\Component\\Model\\Locale','714','a:1:{s:4:\"code\";s:5:\"fi_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fi_FI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1289,'system','Akeneo\\Channel\\Component\\Model\\Locale','715','a:1:{s:4:\"code\";s:6:\"fil_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"fil_PH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1290,'system','Akeneo\\Channel\\Component\\Model\\Locale','716','a:1:{s:4:\"code\";s:5:\"fo_FO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fo_FO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1291,'system','Akeneo\\Channel\\Component\\Model\\Locale','717','a:1:{s:4:\"code\";s:5:\"fr_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_BE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1292,'system','Akeneo\\Channel\\Component\\Model\\Locale','718','a:1:{s:4:\"code\";s:5:\"fr_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1293,'system','Akeneo\\Channel\\Component\\Model\\Locale','719','a:1:{s:4:\"code\";s:5:\"fr_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1294,'system','Akeneo\\Channel\\Component\\Model\\Locale','720','a:1:{s:4:\"code\";s:5:\"fr_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1295,'system','Akeneo\\Channel\\Component\\Model\\Locale','721','a:1:{s:4:\"code\";s:5:\"fr_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_LU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1296,'system','Akeneo\\Channel\\Component\\Model\\Locale','722','a:1:{s:4:\"code\";s:5:\"fr_MC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_MC\";}}',NULL,1,'2018-05-30 13:22:08',0),(1297,'system','Akeneo\\Channel\\Component\\Model\\Locale','723','a:1:{s:4:\"code\";s:5:\"fy_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fy_NL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1298,'system','Akeneo\\Channel\\Component\\Model\\Locale','724','a:1:{s:4:\"code\";s:5:\"ga_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ga_IE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1299,'system','Akeneo\\Channel\\Component\\Model\\Locale','725','a:1:{s:4:\"code\";s:5:\"gd_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gd_GB\";}}',NULL,1,'2018-05-30 13:22:08',0),(1300,'system','Akeneo\\Channel\\Component\\Model\\Locale','726','a:1:{s:4:\"code\";s:5:\"gl_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gl_ES\";}}',NULL,1,'2018-05-30 13:22:08',0),(1301,'system','Akeneo\\Channel\\Component\\Model\\Locale','727','a:1:{s:4:\"code\";s:6:\"gsw_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"gsw_FR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1302,'system','Akeneo\\Channel\\Component\\Model\\Locale','728','a:1:{s:4:\"code\";s:5:\"gu_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gu_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1303,'system','Akeneo\\Channel\\Component\\Model\\Locale','729','a:1:{s:4:\"code\";s:10:\"ha_Latn_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"ha_Latn_NG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1304,'system','Akeneo\\Channel\\Component\\Model\\Locale','730','a:1:{s:4:\"code\";s:5:\"he_IL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"he_IL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1305,'system','Akeneo\\Channel\\Component\\Model\\Locale','731','a:1:{s:4:\"code\";s:5:\"hi_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hi_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1306,'system','Akeneo\\Channel\\Component\\Model\\Locale','732','a:1:{s:4:\"code\";s:5:\"hr_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_BA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1307,'system','Akeneo\\Channel\\Component\\Model\\Locale','733','a:1:{s:4:\"code\";s:5:\"hr_HR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_HR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1308,'system','Akeneo\\Channel\\Component\\Model\\Locale','734','a:1:{s:4:\"code\";s:6:\"hsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"hsb_DE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1309,'system','Akeneo\\Channel\\Component\\Model\\Locale','735','a:1:{s:4:\"code\";s:5:\"hu_HU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hu_HU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1310,'system','Akeneo\\Channel\\Component\\Model\\Locale','736','a:1:{s:4:\"code\";s:5:\"hy_AM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hy_AM\";}}',NULL,1,'2018-05-30 13:22:08',0),(1311,'system','Akeneo\\Channel\\Component\\Model\\Locale','737','a:1:{s:4:\"code\";s:5:\"id_ID\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"id_ID\";}}',NULL,1,'2018-05-30 13:22:08',0),(1312,'system','Akeneo\\Channel\\Component\\Model\\Locale','738','a:1:{s:4:\"code\";s:5:\"ig_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ig_NG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1313,'system','Akeneo\\Channel\\Component\\Model\\Locale','739','a:1:{s:4:\"code\";s:5:\"ii_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ii_CN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1314,'system','Akeneo\\Channel\\Component\\Model\\Locale','740','a:1:{s:4:\"code\";s:5:\"is_IS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"is_IS\";}}',NULL,1,'2018-05-30 13:22:08',0),(1315,'system','Akeneo\\Channel\\Component\\Model\\Locale','741','a:1:{s:4:\"code\";s:5:\"it_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_CH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1316,'system','Akeneo\\Channel\\Component\\Model\\Locale','742','a:1:{s:4:\"code\";s:5:\"it_IT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_IT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1317,'system','Akeneo\\Channel\\Component\\Model\\Locale','743','a:1:{s:4:\"code\";s:10:\"iu_Cans_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Cans_CA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1318,'system','Akeneo\\Channel\\Component\\Model\\Locale','744','a:1:{s:4:\"code\";s:10:\"iu_Latn_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Latn_CA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1319,'system','Akeneo\\Channel\\Component\\Model\\Locale','745','a:1:{s:4:\"code\";s:5:\"ja_JP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ja_JP\";}}',NULL,1,'2018-05-30 13:22:08',0),(1320,'system','Akeneo\\Channel\\Component\\Model\\Locale','746','a:1:{s:4:\"code\";s:5:\"ka_GE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ka_GE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1321,'system','Akeneo\\Channel\\Component\\Model\\Locale','747','a:1:{s:4:\"code\";s:5:\"kk_KZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kk_KZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1322,'system','Akeneo\\Channel\\Component\\Model\\Locale','748','a:1:{s:4:\"code\";s:5:\"kl_GL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kl_GL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1323,'system','Akeneo\\Channel\\Component\\Model\\Locale','749','a:1:{s:4:\"code\";s:5:\"km_KH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"km_KH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1324,'system','Akeneo\\Channel\\Component\\Model\\Locale','750','a:1:{s:4:\"code\";s:5:\"kn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kn_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1325,'system','Akeneo\\Channel\\Component\\Model\\Locale','751','a:1:{s:4:\"code\";s:6:\"kok_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"kok_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1326,'system','Akeneo\\Channel\\Component\\Model\\Locale','752','a:1:{s:4:\"code\";s:5:\"ko_KR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ko_KR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1327,'system','Akeneo\\Channel\\Component\\Model\\Locale','753','a:1:{s:4:\"code\";s:5:\"ky_KG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ky_KG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1328,'system','Akeneo\\Channel\\Component\\Model\\Locale','754','a:1:{s:4:\"code\";s:5:\"lb_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lb_LU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1329,'system','Akeneo\\Channel\\Component\\Model\\Locale','755','a:1:{s:4:\"code\";s:5:\"lo_LA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lo_LA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1330,'system','Akeneo\\Channel\\Component\\Model\\Locale','756','a:1:{s:4:\"code\";s:5:\"lt_LT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lt_LT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1331,'system','Akeneo\\Channel\\Component\\Model\\Locale','757','a:1:{s:4:\"code\";s:5:\"lv_LV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lv_LV\";}}',NULL,1,'2018-05-30 13:22:08',0),(1332,'system','Akeneo\\Channel\\Component\\Model\\Locale','758','a:1:{s:4:\"code\";s:5:\"mi_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mi_NZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1333,'system','Akeneo\\Channel\\Component\\Model\\Locale','759','a:1:{s:4:\"code\";s:5:\"mk_MK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mk_MK\";}}',NULL,1,'2018-05-30 13:22:08',0),(1334,'system','Akeneo\\Channel\\Component\\Model\\Locale','760','a:1:{s:4:\"code\";s:5:\"ml_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ml_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1335,'system','Akeneo\\Channel\\Component\\Model\\Locale','761','a:1:{s:4:\"code\";s:5:\"mn_MN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mn_MN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1336,'system','Akeneo\\Channel\\Component\\Model\\Locale','762','a:1:{s:4:\"code\";s:10:\"mn_Mong_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"mn_Mong_CN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1337,'system','Akeneo\\Channel\\Component\\Model\\Locale','763','a:1:{s:4:\"code\";s:6:\"moh_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"moh_CA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1338,'system','Akeneo\\Channel\\Component\\Model\\Locale','764','a:1:{s:4:\"code\";s:5:\"mr_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mr_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1339,'system','Akeneo\\Channel\\Component\\Model\\Locale','765','a:1:{s:4:\"code\";s:5:\"ms_BN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_BN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1340,'system','Akeneo\\Channel\\Component\\Model\\Locale','766','a:1:{s:4:\"code\";s:5:\"ms_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_MY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1341,'system','Akeneo\\Channel\\Component\\Model\\Locale','767','a:1:{s:4:\"code\";s:5:\"mt_MT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mt_MT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1342,'system','Akeneo\\Channel\\Component\\Model\\Locale','768','a:1:{s:4:\"code\";s:5:\"nb_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nb_NO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1343,'system','Akeneo\\Channel\\Component\\Model\\Locale','769','a:1:{s:4:\"code\";s:5:\"ne_NP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ne_NP\";}}',NULL,1,'2018-05-30 13:22:08',0),(1344,'system','Akeneo\\Channel\\Component\\Model\\Locale','770','a:1:{s:4:\"code\";s:5:\"nl_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_BE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1345,'system','Akeneo\\Channel\\Component\\Model\\Locale','771','a:1:{s:4:\"code\";s:5:\"nl_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_NL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1346,'system','Akeneo\\Channel\\Component\\Model\\Locale','772','a:1:{s:4:\"code\";s:5:\"nn_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nn_NO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1347,'system','Akeneo\\Channel\\Component\\Model\\Locale','773','a:1:{s:4:\"code\";s:6:\"nso_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"nso_ZA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1348,'system','Akeneo\\Channel\\Component\\Model\\Locale','774','a:1:{s:4:\"code\";s:5:\"oc_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"oc_FR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1349,'system','Akeneo\\Channel\\Component\\Model\\Locale','775','a:1:{s:4:\"code\";s:5:\"or_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"or_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1350,'system','Akeneo\\Channel\\Component\\Model\\Locale','776','a:1:{s:4:\"code\";s:5:\"pa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pa_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1351,'system','Akeneo\\Channel\\Component\\Model\\Locale','777','a:1:{s:4:\"code\";s:5:\"pl_PL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pl_PL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1352,'system','Akeneo\\Channel\\Component\\Model\\Locale','778','a:1:{s:4:\"code\";s:6:\"prs_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"prs_AF\";}}',NULL,1,'2018-05-30 13:22:08',0),(1353,'system','Akeneo\\Channel\\Component\\Model\\Locale','779','a:1:{s:4:\"code\";s:5:\"ps_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ps_AF\";}}',NULL,1,'2018-05-30 13:22:08',0),(1354,'system','Akeneo\\Channel\\Component\\Model\\Locale','780','a:1:{s:4:\"code\";s:5:\"pt_BR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_BR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1355,'system','Akeneo\\Channel\\Component\\Model\\Locale','781','a:1:{s:4:\"code\";s:5:\"pt_PT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_PT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1356,'system','Akeneo\\Channel\\Component\\Model\\Locale','782','a:1:{s:4:\"code\";s:6:\"qut_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"qut_GT\";}}',NULL,1,'2018-05-30 13:22:08',0),(1357,'system','Akeneo\\Channel\\Component\\Model\\Locale','783','a:1:{s:4:\"code\";s:6:\"quz_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_BO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1358,'system','Akeneo\\Channel\\Component\\Model\\Locale','784','a:1:{s:4:\"code\";s:6:\"quz_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_EC\";}}',NULL,1,'2018-05-30 13:22:08',0),(1359,'system','Akeneo\\Channel\\Component\\Model\\Locale','785','a:1:{s:4:\"code\";s:6:\"quz_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_PE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1360,'system','Akeneo\\Channel\\Component\\Model\\Locale','786','a:1:{s:4:\"code\";s:5:\"rm_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rm_CH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1361,'system','Akeneo\\Channel\\Component\\Model\\Locale','787','a:1:{s:4:\"code\";s:5:\"ro_RO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ro_RO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1362,'system','Akeneo\\Channel\\Component\\Model\\Locale','788','a:1:{s:4:\"code\";s:5:\"ru_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ru_RU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1363,'system','Akeneo\\Channel\\Component\\Model\\Locale','789','a:1:{s:4:\"code\";s:5:\"rw_RW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rw_RW\";}}',NULL,1,'2018-05-30 13:22:08',0),(1364,'system','Akeneo\\Channel\\Component\\Model\\Locale','790','a:1:{s:4:\"code\";s:6:\"sah_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sah_RU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1365,'system','Akeneo\\Channel\\Component\\Model\\Locale','791','a:1:{s:4:\"code\";s:5:\"sa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sa_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1366,'system','Akeneo\\Channel\\Component\\Model\\Locale','792','a:1:{s:4:\"code\";s:5:\"se_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_FI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1367,'system','Akeneo\\Channel\\Component\\Model\\Locale','793','a:1:{s:4:\"code\";s:5:\"se_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_NO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1368,'system','Akeneo\\Channel\\Component\\Model\\Locale','794','a:1:{s:4:\"code\";s:5:\"se_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_SE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1369,'system','Akeneo\\Channel\\Component\\Model\\Locale','795','a:1:{s:4:\"code\";s:5:\"si_LK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"si_LK\";}}',NULL,1,'2018-05-30 13:22:08',0),(1370,'system','Akeneo\\Channel\\Component\\Model\\Locale','796','a:1:{s:4:\"code\";s:5:\"sk_SK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sk_SK\";}}',NULL,1,'2018-05-30 13:22:08',0),(1371,'system','Akeneo\\Channel\\Component\\Model\\Locale','797','a:1:{s:4:\"code\";s:5:\"sl_SI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sl_SI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1372,'system','Akeneo\\Channel\\Component\\Model\\Locale','798','a:1:{s:4:\"code\";s:6:\"sma_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_NO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1373,'system','Akeneo\\Channel\\Component\\Model\\Locale','799','a:1:{s:4:\"code\";s:6:\"sma_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_SE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1374,'system','Akeneo\\Channel\\Component\\Model\\Locale','800','a:1:{s:4:\"code\";s:6:\"smj_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_NO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1375,'system','Akeneo\\Channel\\Component\\Model\\Locale','801','a:1:{s:4:\"code\";s:6:\"smj_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_SE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1376,'system','Akeneo\\Channel\\Component\\Model\\Locale','802','a:1:{s:4:\"code\";s:6:\"smn_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smn_FI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1377,'system','Akeneo\\Channel\\Component\\Model\\Locale','803','a:1:{s:4:\"code\";s:6:\"sms_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sms_FI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1378,'system','Akeneo\\Channel\\Component\\Model\\Locale','804','a:1:{s:4:\"code\";s:5:\"sq_AL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sq_AL\";}}',NULL,1,'2018-05-30 13:22:08',0),(1379,'system','Akeneo\\Channel\\Component\\Model\\Locale','805','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_BA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1380,'system','Akeneo\\Channel\\Component\\Model\\Locale','806','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_CS\";}}',NULL,1,'2018-05-30 13:22:08',0),(1381,'system','Akeneo\\Channel\\Component\\Model\\Locale','807','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_ME\";}}',NULL,1,'2018-05-30 13:22:08',0),(1382,'system','Akeneo\\Channel\\Component\\Model\\Locale','808','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_RS\";}}',NULL,1,'2018-05-30 13:22:08',0),(1383,'system','Akeneo\\Channel\\Component\\Model\\Locale','809','a:1:{s:4:\"code\";s:10:\"sr_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_BA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1384,'system','Akeneo\\Channel\\Component\\Model\\Locale','810','a:1:{s:4:\"code\";s:10:\"sr_Latn_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_CS\";}}',NULL,1,'2018-05-30 13:22:08',0),(1385,'system','Akeneo\\Channel\\Component\\Model\\Locale','811','a:1:{s:4:\"code\";s:10:\"sr_Latn_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_ME\";}}',NULL,1,'2018-05-30 13:22:08',0),(1386,'system','Akeneo\\Channel\\Component\\Model\\Locale','812','a:1:{s:4:\"code\";s:10:\"sr_Latn_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_RS\";}}',NULL,1,'2018-05-30 13:22:08',0),(1387,'system','Akeneo\\Channel\\Component\\Model\\Locale','813','a:1:{s:4:\"code\";s:5:\"sv_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_FI\";}}',NULL,1,'2018-05-30 13:22:08',0),(1388,'system','Akeneo\\Channel\\Component\\Model\\Locale','814','a:1:{s:4:\"code\";s:5:\"sv_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_SE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1389,'system','Akeneo\\Channel\\Component\\Model\\Locale','815','a:1:{s:4:\"code\";s:5:\"sw_KE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sw_KE\";}}',NULL,1,'2018-05-30 13:22:08',0),(1390,'system','Akeneo\\Channel\\Component\\Model\\Locale','816','a:1:{s:4:\"code\";s:6:\"syr_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"syr_SY\";}}',NULL,1,'2018-05-30 13:22:08',0),(1391,'system','Akeneo\\Channel\\Component\\Model\\Locale','817','a:1:{s:4:\"code\";s:5:\"ta_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ta_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1392,'system','Akeneo\\Channel\\Component\\Model\\Locale','818','a:1:{s:4:\"code\";s:5:\"te_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"te_IN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1393,'system','Akeneo\\Channel\\Component\\Model\\Locale','819','a:1:{s:4:\"code\";s:10:\"tg_Cyrl_TJ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"tg_Cyrl_TJ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1394,'system','Akeneo\\Channel\\Component\\Model\\Locale','820','a:1:{s:4:\"code\";s:5:\"th_TH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"th_TH\";}}',NULL,1,'2018-05-30 13:22:08',0),(1395,'system','Akeneo\\Channel\\Component\\Model\\Locale','821','a:1:{s:4:\"code\";s:5:\"tk_TM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tk_TM\";}}',NULL,1,'2018-05-30 13:22:08',0),(1396,'system','Akeneo\\Channel\\Component\\Model\\Locale','822','a:1:{s:4:\"code\";s:5:\"tn_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tn_ZA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1397,'system','Akeneo\\Channel\\Component\\Model\\Locale','823','a:1:{s:4:\"code\";s:5:\"tr_TR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tr_TR\";}}',NULL,1,'2018-05-30 13:22:08',0),(1398,'system','Akeneo\\Channel\\Component\\Model\\Locale','824','a:1:{s:4:\"code\";s:5:\"tt_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tt_RU\";}}',NULL,1,'2018-05-30 13:22:08',0),(1399,'system','Akeneo\\Channel\\Component\\Model\\Locale','825','a:1:{s:4:\"code\";s:11:\"tzm_Latn_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"tzm_Latn_DZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1400,'system','Akeneo\\Channel\\Component\\Model\\Locale','826','a:1:{s:4:\"code\";s:5:\"ug_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ug_CN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1401,'system','Akeneo\\Channel\\Component\\Model\\Locale','827','a:1:{s:4:\"code\";s:5:\"uk_UA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"uk_UA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1402,'system','Akeneo\\Channel\\Component\\Model\\Locale','828','a:1:{s:4:\"code\";s:5:\"ur_PK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ur_PK\";}}',NULL,1,'2018-05-30 13:22:08',0),(1403,'system','Akeneo\\Channel\\Component\\Model\\Locale','829','a:1:{s:4:\"code\";s:10:\"uz_Cyrl_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Cyrl_UZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1404,'system','Akeneo\\Channel\\Component\\Model\\Locale','830','a:1:{s:4:\"code\";s:10:\"uz_Latn_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Latn_UZ\";}}',NULL,1,'2018-05-30 13:22:08',0),(1405,'system','Akeneo\\Channel\\Component\\Model\\Locale','831','a:1:{s:4:\"code\";s:5:\"vi_VN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"vi_VN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1406,'system','Akeneo\\Channel\\Component\\Model\\Locale','832','a:1:{s:4:\"code\";s:5:\"wo_SN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"wo_SN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1407,'system','Akeneo\\Channel\\Component\\Model\\Locale','833','a:1:{s:4:\"code\";s:5:\"xh_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"xh_ZA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1408,'system','Akeneo\\Channel\\Component\\Model\\Locale','834','a:1:{s:4:\"code\";s:5:\"yo_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"yo_NG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1409,'system','Akeneo\\Channel\\Component\\Model\\Locale','835','a:1:{s:4:\"code\";s:5:\"zh_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_CN\";}}',NULL,1,'2018-05-30 13:22:08',0),(1410,'system','Akeneo\\Channel\\Component\\Model\\Locale','836','a:1:{s:4:\"code\";s:5:\"zh_HK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_HK\";}}',NULL,1,'2018-05-30 13:22:08',0),(1411,'system','Akeneo\\Channel\\Component\\Model\\Locale','837','a:1:{s:4:\"code\";s:5:\"zh_MO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_MO\";}}',NULL,1,'2018-05-30 13:22:08',0),(1412,'system','Akeneo\\Channel\\Component\\Model\\Locale','838','a:1:{s:4:\"code\";s:5:\"zh_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_SG\";}}',NULL,1,'2018-05-30 13:22:08',0),(1413,'system','Akeneo\\Channel\\Component\\Model\\Locale','839','a:1:{s:4:\"code\";s:5:\"zh_TW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_TW\";}}',NULL,1,'2018-05-30 13:22:08',0),(1414,'system','Akeneo\\Channel\\Component\\Model\\Locale','840','a:1:{s:4:\"code\";s:5:\"zu_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zu_ZA\";}}',NULL,1,'2018-05-30 13:22:08',0),(1415,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','33','a:3:{s:4:\"code\";s:15:\"2014_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2014 collection\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 collection\";}}',NULL,1,'2018-05-30 13:22:08',0),(1416,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','34','a:3:{s:4:\"code\";s:17:\"summer_collection\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:17:\"Summer collection\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"summer_collection\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Summer collection\";}}',NULL,1,'2018-05-30 13:22:08',0),(1417,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','35','a:3:{s:4:\"code\";s:17:\"winter_collection\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:17:\"Winter collection\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"winter_collection\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Winter collection\";}}',NULL,1,'2018-05-30 13:22:08',0),(1418,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','36','a:3:{s:4:\"code\";s:12:\"winter_boots\";s:6:\"parent\";s:17:\"winter_collection\";s:11:\"label-en_US\";s:12:\"Winter boots\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"winter_boots\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"winter_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Winter boots\";}}',NULL,1,'2018-05-30 13:22:08',0),(1419,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','37','a:3:{s:4:\"code\";s:7:\"sandals\";s:6:\"parent\";s:17:\"summer_collection\";s:11:\"label-en_US\";s:7:\"Sandals\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"sandals\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"summer_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}}',NULL,1,'2018-05-30 13:22:08',0),(1420,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','9','a:4:{s:4:\"code\";s:4:\"info\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:19:\"Product information\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Product information\";}}',NULL,1,'2018-05-30 13:22:08',0),(1421,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','10','a:4:{s:4:\"code\";s:9:\"marketing\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:9:\"Marketing\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Marketing\";}}',NULL,1,'2018-05-30 13:22:08',0),(1422,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','11','a:4:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Sizes\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sizes\";}}',NULL,1,'2018-05-30 13:22:08',0),(1423,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','12','a:4:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:6:\"Colors\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Colors\";}}',NULL,1,'2018-05-30 13:22:08',0),(1424,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','13','a:4:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Media\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Media\";}}',NULL,1,'2018-05-30 13:22:08',0),(1425,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','14','a:4:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Other\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:100;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}}',NULL,1,'2018-05-30 13:22:08',0),(1426,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','30','a:29:{s:4:\"code\";s:3:\"sku\";s:4:\"type\";s:22:\"pim_catalog_identifier\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:3:\"SKU\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:1;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"pim_catalog_identifier\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:6:\"unique\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}s:8:\"required\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}}',NULL,1,'2018-05-30 13:22:09',0),(1427,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','9','a:4:{s:4:\"code\";s:4:\"info\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:73:\"sku,name,manufacturer,weather_conditions,description,length,volume,weight\";s:11:\"label-en_US\";s:19:\"Product information\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:73:\"sku,name,manufacturer,weather_conditions,description,length,volume,weight\";}}',NULL,2,'2018-05-30 13:22:09',0),(1428,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','31','a:29:{s:4:\"code\";s:4:\"name\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Name\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1429,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','32','a:29:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";b:1;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"manufacturer\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:19:\"auto_option_sorting\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Manufacturer\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1430,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','33','a:29:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"weather_conditions\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Weather conditions\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1431,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','34','a:29:{s:4:\"code\";s:11:\"description\";s:4:\"type\";s:20:\"pim_catalog_textarea\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:1000;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:5;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Description\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"description\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1000;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:09',0),(1432,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','35','a:29:{s:4:\"code\";s:7:\"comment\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:255;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:7;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:7:\"Comment\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"comment\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:255;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:7;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Comment\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1433,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','14','a:4:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:108:\"comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123\";s:11:\"label-en_US\";s:5:\"Other\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:108:\"comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123\";}}',NULL,2,'2018-05-30 13:22:09',0),(1434,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','36','a:29:{s:4:\"code\";s:5:\"price\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:8:\"200.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Price\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"price\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"200.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Price\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1435,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','10','a:4:{s:4:\"code\";s:9:\"marketing\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:22:\"price,rating,rate_sale\";s:11:\"label-en_US\";s:9:\"Marketing\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"price,rating,rate_sale\";}}',NULL,2,'2018-05-30 13:22:09',0),(1436,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','37','a:29:{s:4:\"code\";s:6:\"rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Rating\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"rating\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Rating\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1437,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','38','a:29:{s:4:\"code\";s:9:\"side_view\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Side view\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"side_view\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Side view\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1438,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','13','a:4:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:28:\"side_view,top_view,rear_view\";s:11:\"label-en_US\";s:5:\"Media\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"side_view,top_view,rear_view\";}}',NULL,2,'2018-05-30 13:22:09',0),(1439,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','39','a:29:{s:4:\"code\";s:8:\"top_view\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Top view\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"top_view\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Top view\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1440,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','40','a:29:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Size\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1441,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','11','a:4:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:4:\"size\";s:11:\"label-en_US\";s:5:\"Sizes\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"size\";}}',NULL,2,'2018-05-30 13:22:09',0),(1442,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','41','a:29:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1443,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','12','a:4:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:16:\"color,lace_color\";s:11:\"label-en_US\";s:6:\"Colors\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"color,lace_color\";}}',NULL,2,'2018-05-30 13:22:09',0),(1444,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','42','a:29:{s:4:\"code\";s:10:\"lace_color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Lace color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"lace_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Lace color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1445,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','43','a:30:{s:4:\"code\";s:6:\"length\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Length\";s:19:\"default_metric_unit\";s:10:\"CENTIMETER\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:10;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Length\";s:11:\"label-fr_FR\";s:8:\"Longueur\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"length\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Length\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"CENTIMETER\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:10;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Length\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Longueur\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1446,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','44','a:30:{s:4:\"code\";s:6:\"volume\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Volume\";s:19:\"default_metric_unit\";s:16:\"CUBIC_MILLIMETER\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:20;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Volume\";s:11:\"label-fr_FR\";s:6:\"Volume\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"volume\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Volume\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"CUBIC_MILLIMETER\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:20;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Volume\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Volume\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1447,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','45','a:29:{s:4:\"code\";s:15:\"number_in_stock\";s:4:\"type\";s:18:\"pim_catalog_number\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:8;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Number in stock\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"number_in_stock\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:8;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Number in stock\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1448,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','46','a:30:{s:4:\"code\";s:15:\"destocking_date\";s:4:\"type\";s:16:\"pim_catalog_date\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:25;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Destocking date\";s:11:\"label-fr_FR\";s:19:\"Date de déstockage\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"destocking_date\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_date\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:25;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Destocking date\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Date de déstockage\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1449,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','47','a:29:{s:4:\"code\";s:8:\"handmade\";s:4:\"type\";s:19:\"pim_catalog_boolean\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:30;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Handmade\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"handmade\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:30;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Handmade\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1450,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','48','a:29:{s:4:\"code\";s:10:\"heel_color\";s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:30;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Heel color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"heel_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:30;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Heel color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1451,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','49','a:29:{s:4:\"code\";s:10:\"sole_color\";s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:35;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Sole color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sole_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:35;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Sole color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1452,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','50','a:29:{s:4:\"code\";s:9:\"cap_color\";s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:35;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Cap color\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"cap_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:35;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Cap color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:09',0),(1453,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','51','a:29:{s:4:\"code\";s:11:\"sole_fabric\";s:4:\"type\";s:30:\"pim_reference_data_multiselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:40;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Sole fabric\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"sole_fabric\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"pim_reference_data_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"fabrics\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:40;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Sole fabric\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1454,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','52','a:29:{s:4:\"code\";s:11:\"lace_fabric\";s:4:\"type\";s:30:\"pim_reference_data_multiselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:45;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Lace fabric\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"lace_fabric\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"pim_reference_data_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"fabrics\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:45;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Lace fabric\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:09',0),(1455,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','53','a:30:{s:4:\"code\";s:9:\"rate_sale\";s:4:\"type\";s:18:\"pim_catalog_number\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Rate of sale\";s:11:\"label-fr_FR\";s:13:\"Taux de vente\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"rate_sale\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Rate of sale\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Taux de vente\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1456,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','54','a:30:{s:4:\"code\";s:6:\"weight\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Weight\";s:19:\"default_metric_unit\";s:4:\"GRAM\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:30;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Weight\";s:11:\"label-fr_FR\";s:5:\"Poids\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"weight\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"GRAM\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:30;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Poids\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1457,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','55','a:29:{s:4:\"code\";s:3:\"123\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:255;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:13:\"Attribute 123\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"123\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:255;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Attribute 123\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-05-30 13:22:09',0),(1458,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','56','a:29:{s:4:\"code\";s:9:\"rear_view\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Rear view\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"rear_view\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Rear view\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-05-30 13:22:09',0),(1459,'system','Akeneo\\Channel\\Component\\Model\\Channel','7','a:7:{s:4:\"code\";s:6:\"tablet\";s:10:\"currencies\";s:7:\"EUR,USD\";s:7:\"locales\";s:5:\"en_US\";s:11:\"label-en_US\";s:6:\"Tablet\";s:11:\"label-de_DE\";s:6:\"Tablet\";s:11:\"label-fr_FR\";s:8:\"Tablette\";s:8:\"category\";s:15:\"2014_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"tablet\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"EUR,USD\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Tablette\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}}',NULL,1,'2018-05-30 13:22:09',0),(1460,'system','Akeneo\\Channel\\Component\\Model\\Channel','8','a:7:{s:4:\"code\";s:6:\"mobile\";s:10:\"currencies\";s:3:\"EUR\";s:7:\"locales\";s:11:\"en_US,fr_FR\";s:11:\"label-en_US\";s:6:\"Mobile\";s:11:\"label-de_DE\";s:5:\"Mobil\";s:11:\"label-fr_FR\";s:6:\"Mobile\";s:8:\"category\";s:15:\"2014_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"mobile\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,fr_FR\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Mobile\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Mobil\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Mobile\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}}',NULL,1,'2018-05-30 13:22:09',0),(1461,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','13','a:2:{s:4:\"code\";s:6:\"X_SELL\";s:11:\"label-en_US\";s:10:\"Cross sell\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"X_SELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}}',NULL,1,'2018-05-30 13:22:09',0),(1462,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','14','a:2:{s:4:\"code\";s:6:\"UPSELL\";s:11:\"label-en_US\";s:6:\"Upsell\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"UPSELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}}',NULL,1,'2018-05-30 13:22:09',0),(1463,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','15','a:2:{s:4:\"code\";s:12:\"SUBSTITUTION\";s:11:\"label-en_US\";s:12:\"Substitution\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"SUBSTITUTION\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Substitution\";}}',NULL,1,'2018-05-30 13:22:09',0),(1464,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','16','a:2:{s:4:\"code\";s:4:\"PACK\";s:11:\"label-en_US\";s:4:\"Pack\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"PACK\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}}',NULL,1,'2018-05-30 13:22:09',0),(1465,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','32','a:29:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";b:1;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:7:\"options\";s:117:\"Code:Converse,en_US:Converse|Code:TimberLand,en_US:TimberLand|Code:Nike,en_US:Nike|Code:Caterpillar,en_US:Caterpillar\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:117:\"Code:Converse,en_US:Converse|Code:TimberLand,en_US:TimberLand|Code:Nike,en_US:Nike|Code:Caterpillar,en_US:Caterpillar\";}}',NULL,2,'2018-05-30 13:22:10',0),(1466,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','33','a:29:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:7:\"options\";s:100:\"Code:dry,en_US:Dry|Code:wet,en_US:Wet|Code:hot,en_US:Hot|Code:cold,en_US:Cold|Code:snowy,en_US:Snowy\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:100:\"Code:dry,en_US:Dry|Code:wet,en_US:Wet|Code:hot,en_US:Hot|Code:cold,en_US:Cold|Code:snowy,en_US:Snowy\";}}',NULL,2,'2018-05-30 13:22:10',0),(1467,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','37','a:29:{s:4:\"code\";s:6:\"rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Rating\";s:7:\"options\";s:103:\"Code:1,en_US:1 star|Code:2,en_US:2 stars|Code:3,en_US:3 stars|Code:4,en_US:4 stars|Code:5,en_US:5 stars\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:103:\"Code:1,en_US:1 star|Code:2,en_US:2 stars|Code:3,en_US:3 stars|Code:4,en_US:4 stars|Code:5,en_US:5 stars\";}}',NULL,2,'2018-05-30 13:22:10',0),(1468,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','40','a:29:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:7:\"options\";s:220:\"Code:35,en_US:35|Code:36,en_US:36|Code:37,en_US:37|Code:38,en_US:38|Code:39,en_US:39|Code:40,en_US:40|Code:41,en_US:41|Code:42,en_US:42|Code:43,en_US:43|Code:44,en_US:44|Code:45,en_US:45|Code:46,en_US:46|Code:60,en_US:60\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:220:\"Code:35,en_US:35|Code:36,en_US:36|Code:37,en_US:37|Code:38,en_US:38|Code:39,en_US:39|Code:40,en_US:40|Code:41,en_US:41|Code:42,en_US:42|Code:43,en_US:43|Code:44,en_US:44|Code:45,en_US:45|Code:46,en_US:46|Code:60,en_US:60\";}}',NULL,2,'2018-05-30 13:22:10',0),(1469,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','41','a:29:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:7:\"options\";s:187:\"Code:white,en_US:White|Code:black,en_US:Black|Code:blue,en_US:Blue|Code:maroon,en_US:Maroon|Code:saddle,en_US:Saddle|Code:greem,en_US:Greem|Code:red,en_US:Red|Code:charcoal,en_US:Charcoal\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:187:\"Code:white,en_US:White|Code:black,en_US:Black|Code:blue,en_US:Blue|Code:maroon,en_US:Maroon|Code:saddle,en_US:Saddle|Code:greem,en_US:Greem|Code:red,en_US:Red|Code:charcoal,en_US:Charcoal\";}}',NULL,2,'2018-05-30 13:22:10',0),(1470,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','42','a:29:{s:4:\"code\";s:10:\"lace_color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Lace color\";s:7:\"options\";s:86:\"Code:laces_black,en_US:Black|Code:laces_brown,en_US:Brown|Code:laces_white,en_US:White\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:86:\"Code:laces_black,en_US:Black|Code:laces_brown,en_US:Brown|Code:laces_white,en_US:White\";}}',NULL,2,'2018-05-30 13:22:10',0),(1471,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','4','a:7:{s:4:\"code\";s:5:\"boots\";s:10:\"attributes\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";s:11:\"label-en_US\";s:5:\"Boots\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"boots\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Boots\";}}',NULL,1,'2018-05-30 13:22:10',0),(1472,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','5','a:7:{s:4:\"code\";s:5:\"heels\";s:10:\"attributes\";s:103:\"color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:47:\"color,heel_color,name,price,size,sku,sole_color\";s:19:\"requirements-tablet\";s:69:\"color,description,heel_color,name,price,side_view,size,sku,sole_color\";s:11:\"label-en_US\";s:5:\"Heels\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"heels\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:103:\"color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:47:\"color,heel_color,name,price,size,sku,sole_color\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:69:\"color,description,heel_color,name,price,side_view,size,sku,sole_color\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Heels\";}}',NULL,1,'2018-05-30 13:22:10',0),(1473,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','6','a:7:{s:4:\"code\";s:8:\"sneakers\";s:10:\"attributes\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";s:11:\"label-en_US\";s:8:\"Sneakers\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"sneakers\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Sneakers\";}}',NULL,1,'2018-05-30 13:22:10',0),(1474,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','7','a:7:{s:4:\"code\";s:7:\"sandals\";s:10:\"attributes\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";s:9:\"side_view\";s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:54:\"color,description,name,price,rating,side_view,size,sku\";s:11:\"label-en_US\";s:7:\"Sandals\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"sandals\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:18:\"attribute_as_image\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"side_view\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:54:\"color,description,name,price,rating,side_view,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}}',NULL,1,'2018-05-30 13:22:10',0),(1475,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','8','a:7:{s:4:\"code\";s:7:\"led_tvs\";s:10:\"attributes\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:54:\"color,description,name,price,rating,side_view,size,sku\";s:11:\"label-en_US\";s:7:\"LED TVs\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"led_tvs\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:54:\"color,description,name,price,rating,side_view,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"LED TVs\";}}',NULL,1,'2018-05-30 13:22:10',0),(1476,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','148','a:5:{s:4:\"code\";s:27:\"csv_footwear_product_import\";s:5:\"label\";s:27:\"CSV footwear product import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:410:\"{\"filePath\":\"\\/tmp\\/footwear_products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:410:\"{\"filePath\":\"\\/tmp\\/footwear_products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1477,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','149','a:5:{s:4:\"code\";s:27:\"csv_footwear_product_export\";s:5:\"label\";s:27:\"CSV footwear product export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:453:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]}],\"structure\":{\"scope\":\"mobile\",\"locales\":[\"en_US\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear product export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:453:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]}],\"structure\":{\"scope\":\"mobile\",\"locales\":[\"en_US\"]}}}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1478,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','150','a:5:{s:4:\"code\";s:28:\"csv_footwear_category_import\";s:5:\"label\";s:28:\"CSV footwear category import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_category_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear category import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1479,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','151','a:5:{s:4:\"code\";s:28:\"csv_footwear_category_export\";s:5:\"label\";s:28:\"CSV footwear category export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_category_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear category export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1480,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','152','a:5:{s:4:\"code\";s:36:\"csv_footwear_association_type_import\";s:5:\"label\";s:36:\"CSV footwear association type import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"csv_footwear_association_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"CSV footwear association type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1481,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','153','a:5:{s:4:\"code\";s:36:\"csv_footwear_association_type_export\";s:5:\"label\";s:36:\"CSV footwear association type export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"csv_footwear_association_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"CSV footwear association type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1482,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','154','a:5:{s:4:\"code\";s:25:\"csv_footwear_group_import\";s:5:\"label\";s:25:\"CSV footwear group import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"csv_footwear_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"CSV footwear group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1483,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','155','a:5:{s:4:\"code\";s:26:\"xlsx_footwear_group_import\";s:5:\"label\";s:26:\"XLSX footwear group import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:158:\"{\"filePath\":\"\\/tmp\\/group.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"xlsx_footwear_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"XLSX footwear group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:158:\"{\"filePath\":\"\\/tmp\\/group.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1484,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','156','a:5:{s:4:\"code\";s:25:\"csv_footwear_group_export\";s:5:\"label\";s:25:\"CSV footwear group export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"csv_footwear_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"CSV footwear group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1485,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','157','a:5:{s:4:\"code\";s:29:\"csv_footwear_attribute_import\";s:5:\"label\";s:29:\"CSV footwear attribute import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"csv_footwear_attribute_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"CSV footwear attribute import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1486,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','158','a:5:{s:4:\"code\";s:29:\"csv_footwear_attribute_export\";s:5:\"label\";s:29:\"CSV footwear attribute export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"csv_footwear_attribute_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"CSV footwear attribute export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1487,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','159','a:5:{s:4:\"code\";s:26:\"csv_footwear_option_import\";s:5:\"label\";s:26:\"CSV footwear option import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_option_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear option import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1488,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','160','a:5:{s:4:\"code\";s:26:\"csv_footwear_option_export\";s:5:\"label\";s:26:\"CSV footwear option export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_option_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear option export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1489,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','161','a:5:{s:4:\"code\";s:26:\"csv_footwear_family_export\";s:5:\"label\";s:26:\"CSV footwear family export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_family_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear family export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1490,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','162','a:5:{s:4:\"code\";s:27:\"csv_footwear_channel_export\";s:5:\"label\";s:27:\"CSV footwear channel export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:136:\"{\"filePath\":\"\\/tmp\\/channel.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_channel_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear channel export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:136:\"{\"filePath\":\"\\/tmp\\/channel.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1491,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','163','a:5:{s:4:\"code\";s:28:\"csv_footwear_currency_export\";s:5:\"label\";s:28:\"CSV footwear currency export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:137:\"{\"filePath\":\"\\/tmp\\/currency.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_currency_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear currency export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:137:\"{\"filePath\":\"\\/tmp\\/currency.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1492,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','164','a:5:{s:4:\"code\";s:26:\"csv_footwear_locale_export\";s:5:\"label\";s:26:\"CSV footwear locale export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/locale.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_locale_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear locale export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/locale.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1493,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','165','a:5:{s:4:\"code\";s:30:\"csv_footwear_group_type_export\";s:5:\"label\";s:30:\"CSV footwear group type export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:139:\"{\"filePath\":\"\\/tmp\\/group_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"csv_footwear_group_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"CSV footwear group type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:139:\"{\"filePath\":\"\\/tmp\\/group_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1494,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','166','a:5:{s:4:\"code\";s:35:\"csv_footwear_attribute_group_export\";s:5:\"label\";s:35:\"CSV footwear attribute group export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:144:\"{\"filePath\":\"\\/tmp\\/attribute_group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"csv_footwear_attribute_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"CSV footwear attribute group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:144:\"{\"filePath\":\"\\/tmp\\/attribute_group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1495,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','167','a:5:{s:4:\"code\";s:26:\"csv_footwear_family_import\";s:5:\"label\";s:26:\"CSV footwear family import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_family_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear family import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1496,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','168','a:5:{s:4:\"code\";s:28:\"csv_footwear_currency_import\";s:5:\"label\";s:28:\"CSV footwear currency import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_currency_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear currency import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1497,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','169','a:5:{s:4:\"code\";s:27:\"csv_footwear_channel_import\";s:5:\"label\";s:27:\"CSV footwear channel import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_channel_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear channel import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1498,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','170','a:5:{s:4:\"code\";s:26:\"csv_footwear_locale_import\";s:5:\"label\";s:26:\"CSV footwear locale import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_locale_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear locale import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1499,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','171','a:5:{s:4:\"code\";s:30:\"csv_footwear_group_type_import\";s:5:\"label\";s:30:\"CSV footwear group_type import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"csv_footwear_group_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"CSV footwear group_type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1500,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','172','a:5:{s:4:\"code\";s:35:\"csv_footwear_attribute_group_import\";s:5:\"label\";s:35:\"CSV footwear attribute_group import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"csv_footwear_attribute_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"CSV footwear attribute_group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1501,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','173','a:5:{s:4:\"code\";s:20:\"update_product_value\";s:5:\"label\";s:20:\"Mass update products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"update_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass update products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1502,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','174','a:5:{s:4:\"code\";s:17:\"add_product_value\";s:5:\"label\";s:24:\"Mass add products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"add_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1503,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','175','a:5:{s:4:\"code\";s:20:\"remove_product_value\";s:5:\"label\";s:27:\"Mass remove products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1504,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','176','a:5:{s:4:\"code\";s:16:\"move_to_category\";s:5:\"label\";s:23:\"Mass move to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"move_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass move to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1505,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','177','a:5:{s:4:\"code\";s:15:\"add_association\";s:5:\"label\";s:23:\"Mass associate products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_association\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass associate products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1506,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','178','a:5:{s:4:\"code\";s:15:\"add_to_category\";s:5:\"label\";s:22:\"Mass add to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Mass add to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1507,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','179','a:5:{s:4:\"code\";s:12:\"add_to_group\";s:5:\"label\";s:25:\"Mass add product to group\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"add_to_group\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Mass add product to group\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1508,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','180','a:5:{s:4:\"code\";s:20:\"remove_from_category\";s:5:\"label\";s:27:\"Mass remove from categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_from_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove from categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1509,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','181','a:5:{s:4:\"code\";s:22:\"edit_common_attributes\";s:5:\"label\";s:28:\"Mass edit product attributes\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"edit_common_attributes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Mass edit product attributes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1510,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','182','a:5:{s:4:\"code\";s:19:\"add_attribute_value\";s:5:\"label\";s:24:\"Mass add attribute value\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"add_attribute_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add attribute value\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1511,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','183','a:5:{s:4:\"code\";s:26:\"set_attribute_requirements\";s:5:\"label\";s:33:\"Set family attribute requirements\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"set_attribute_requirements\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Set family attribute requirements\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1512,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','184','a:5:{s:4:\"code\";s:29:\"add_to_existing_product_model\";s:5:\"label\";s:29:\"Add to existing product model\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"add_to_existing_product_model\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Add to existing product model\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1513,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','185','a:5:{s:4:\"code\";s:34:\"delete_products_and_product_models\";s:5:\"label\";s:20:\"Mass delete products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:11:\"mass_delete\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"delete_products_and_product_models\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass delete products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"mass_delete\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1514,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','186','a:5:{s:4:\"code\";s:24:\"csv_product_quick_export\";s:5:\"label\";s:24:\"CSV product quick export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:410:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"csv_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"CSV product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:410:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.csv\"}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1515,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','187','a:5:{s:4:\"code\";s:31:\"xlsx_footwear_product_import_fr\";s:5:\"label\";s:31:\"XLSX footwear product import FR\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:368:\"{\"filePath\":\"\\/tmp\\/footwear_products-fr.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\",\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"xlsx_footwear_product_import_fr\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"XLSX footwear product import FR\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:368:\"{\"filePath\":\"\\/tmp\\/footwear_products-fr.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\",\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1516,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','188','a:5:{s:4:\"code\";s:28:\"xlsx_footwear_product_import\";s:5:\"label\";s:28:\"XLSX footwear product import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:365:\"{\"filePath\":\"\\/tmp\\/footwear_products.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"xlsx_footwear_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"XLSX footwear product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:365:\"{\"filePath\":\"\\/tmp\\/footwear_products.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1517,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','189','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_category_import\";s:5:\"label\";s:29:\"XLSX footwear category import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_category_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear category import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1518,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','190','a:5:{s:4:\"code\";s:37:\"xlsx_footwear_association_type_import\";s:5:\"label\";s:37:\"XLSX footwear association type import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"xlsx_footwear_association_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"XLSX footwear association type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1519,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','191','a:5:{s:4:\"code\";s:30:\"xlsx_footwear_attribute_import\";s:5:\"label\";s:30:\"XLSX footwear attribute import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"xlsx_footwear_attribute_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"XLSX footwear attribute import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1520,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','192','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_option_import\";s:5:\"label\";s:27:\"XLSX footwear option import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_option_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear option import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1521,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','193','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_family_import\";s:5:\"label\";s:27:\"XLSX footwear family import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_family_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear family import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1522,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','194','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_currency_import\";s:5:\"label\";s:29:\"XLSX footwear currency import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_currency_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear currency import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1523,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','195','a:5:{s:4:\"code\";s:28:\"xlsx_footwear_channel_import\";s:5:\"label\";s:28:\"XLSX footwear channel import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"xlsx_footwear_channel_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"XLSX footwear channel import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1524,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','196','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_locale_import\";s:5:\"label\";s:27:\"XLSX footwear locale import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_locale_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear locale import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1525,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','197','a:5:{s:4:\"code\";s:31:\"xlsx_footwear_group_type_import\";s:5:\"label\";s:31:\"XLSX footwear group_type import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"xlsx_footwear_group_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"XLSX footwear group_type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1526,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','198','a:5:{s:4:\"code\";s:36:\"xlsx_footwear_attribute_group_import\";s:5:\"label\";s:36:\"XLSX footwear attribute_group import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"xlsx_footwear_attribute_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"XLSX footwear attribute_group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1527,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','199','a:5:{s:4:\"code\";s:25:\"xlsx_product_quick_export\";s:5:\"label\";s:25:\"XLSX product quick export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:401:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.xlsx\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"xlsx_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"XLSX product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:401:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.xlsx\"}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1528,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','200','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_family_export\";s:5:\"label\";s:27:\"XLSX footwear family export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:124:\"{\"filePath\":\"\\/tmp\\/family.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_family_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear family export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:124:\"{\"filePath\":\"\\/tmp\\/family.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1529,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','201','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_category_export\";s:5:\"label\";s:29:\"XLSX footwear category export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:126:\"{\"filePath\":\"\\/tmp\\/category.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_category_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear category export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:126:\"{\"filePath\":\"\\/tmp\\/category.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1530,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','202','a:5:{s:4:\"code\";s:30:\"xlsx_footwear_attribute_export\";s:5:\"label\";s:30:\"XLSX footwear attribute export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:127:\"{\"filePath\":\"\\/tmp\\/attribute.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"xlsx_footwear_attribute_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"XLSX footwear attribute export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:127:\"{\"filePath\":\"\\/tmp\\/attribute.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1531,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','203','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_option_export\";s:5:\"label\";s:27:\"XLSX footwear option export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:124:\"{\"filePath\":\"\\/tmp\\/option.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_option_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear option export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:124:\"{\"filePath\":\"\\/tmp\\/option.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1532,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','204','a:5:{s:4:\"code\";s:37:\"xlsx_footwear_association_type_export\";s:5:\"label\";s:37:\"XLSX footwear association type export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:134:\"{\"filePath\":\"\\/tmp\\/association_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"xlsx_footwear_association_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"XLSX footwear association type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:134:\"{\"filePath\":\"\\/tmp\\/association_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1533,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','205','a:5:{s:4:\"code\";s:28:\"xlsx_footwear_channel_export\";s:5:\"label\";s:28:\"XLSX footwear channel export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:125:\"{\"filePath\":\"\\/tmp\\/channel.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"xlsx_footwear_channel_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"XLSX footwear channel export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:125:\"{\"filePath\":\"\\/tmp\\/channel.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1534,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','206','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_currency_export\";s:5:\"label\";s:29:\"XLSX footwear currency export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:126:\"{\"filePath\":\"\\/tmp\\/currency.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_currency_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear currency export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:126:\"{\"filePath\":\"\\/tmp\\/currency.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1535,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','207','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_locale_export\";s:5:\"label\";s:27:\"XLSX footwear locale export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:124:\"{\"filePath\":\"\\/tmp\\/locale.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_locale_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear locale export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:124:\"{\"filePath\":\"\\/tmp\\/locale.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1536,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','208','a:5:{s:4:\"code\";s:31:\"xlsx_footwear_group_type_export\";s:5:\"label\";s:31:\"XLSX footwear group type export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:128:\"{\"filePath\":\"\\/tmp\\/group_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"xlsx_footwear_group_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"XLSX footwear group type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:128:\"{\"filePath\":\"\\/tmp\\/group_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1537,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','209','a:5:{s:4:\"code\";s:36:\"xlsx_footwear_attribute_group_export\";s:5:\"label\";s:36:\"XLSX footwear attribute group export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:133:\"{\"filePath\":\"\\/tmp\\/attribute_group.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"xlsx_footwear_attribute_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"XLSX footwear attribute group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:133:\"{\"filePath\":\"\\/tmp\\/attribute_group.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1538,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','210','a:5:{s:4:\"code\";s:37:\"csv_product_grid_context_quick_export\";s:5:\"label\";s:37:\"CSV product quick export grid context\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:436:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"csv_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"CSV product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:436:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.csv\"}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1539,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','211','a:5:{s:4:\"code\";s:38:\"xlsx_product_grid_context_quick_export\";s:5:\"label\";s:38:\"XLSX product quick export grid context\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:427:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.xlsx\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"xlsx_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"XLSX product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:427:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.xlsx\"}\";}}',NULL,1,'2018-05-30 13:22:11',0),(1540,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','212','a:5:{s:4:\"code\";s:34:\"compute_product_models_descendants\";s:5:\"label\";s:34:\"Compute product models descendants\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:34:\"compute_product_models_descendants\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"Compute product models descendants\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1541,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','213','a:5:{s:4:\"code\";s:39:\"compute_completeness_of_products_family\";s:5:\"label\";s:39:\"compute completeness of products family\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:39:\"compute_completeness_of_products_family\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute completeness of products family\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1542,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','214','a:5:{s:4:\"code\";s:40:\"compute_family_variant_structure_changes\";s:5:\"label\";s:33:\"Compute variant structure changes\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:40:\"compute_family_variant_structure_changes\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Compute variant structure changes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:11',0),(1543,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','2','a:3:{s:4:\"code\";s:13:\"similar_boots\";s:4:\"type\";s:7:\"RELATED\";s:11:\"label-en_US\";s:13:\"Similar boots\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"similar_boots\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"RELATED\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Similar boots\";}}',NULL,1,'2018-05-30 13:22:11',0);
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

-- Dump completed on 2018-05-30 15:22:12
