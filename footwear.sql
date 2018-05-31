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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES (6,'(root)');
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES (31,6,11,16,NULL,2,1,1,'all',0,0),(32,6,12,16,NULL,2,1056964608,1,'all',0,0),(33,6,11,17,NULL,1,1,1,'all',0,0),(34,6,12,17,NULL,1,1056964608,1,'all',0,0),(35,6,11,18,NULL,0,1,1,'all',0,0),(36,6,12,18,NULL,0,1056964608,1,'all',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES (11,NULL,6,'action',1),(12,NULL,6,'entity',1);
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
INSERT INTO `acl_object_identity_ancestors` VALUES (11,11),(12,12);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
INSERT INTO `acl_security_identities` VALUES (16,'ROLE_ADMINISTRATOR',0),(17,'ROLE_CATALOG_MANAGER',0),(18,'ROLE_USER',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=353 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_instance`
--

LOCK TABLES `akeneo_batch_job_instance` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_instance` DISABLE KEYS */;
INSERT INTO `akeneo_batch_job_instance` VALUES (286,'csv_footwear_product_import','CSV footwear product import','csv_product_import',0,'Akeneo CSV Connector','a:17:{s:8:\"filePath\";s:26:\"/tmp/footwear_products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(287,'csv_footwear_product_export','CSV footwear product export','csv_product_export',0,'Akeneo CSV Connector','a:10:{s:8:\"filePath\";s:17:\"/tmp/products.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:10:\"with_media\";b:1;s:7:\"filters\";a:2:{s:4:\"data\";a:3:{i:0;a:3:{s:5:\"field\";s:7:\"enabled\";s:8:\"operator\";s:1:\"=\";s:5:\"value\";b:1;}i:1;a:3:{s:5:\"field\";s:12:\"completeness\";s:8:\"operator\";s:2:\">=\";s:5:\"value\";i:100;}i:2;a:3:{s:5:\"field\";s:10:\"categories\";s:8:\"operator\";s:11:\"IN CHILDREN\";s:5:\"value\";a:1:{i:0;s:15:\"2014_collection\";}}}s:9:\"structure\";a:2:{s:5:\"scope\";s:6:\"mobile\";s:7:\"locales\";a:1:{i:0;s:5:\"en_US\";}}}}','export'),(288,'csv_footwear_category_import','CSV footwear category import','csv_category_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(289,'csv_footwear_category_export','CSV footwear category export','csv_category_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:17:\"/tmp/category.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(290,'csv_footwear_association_type_import','CSV footwear association type import','csv_association_type_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(291,'csv_footwear_association_type_export','CSV footwear association type export','csv_association_type_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:25:\"/tmp/association_type.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(292,'csv_footwear_group_import','CSV footwear group import','csv_group_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(293,'xlsx_footwear_group_import','XLSX footwear group import','xlsx_group_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/group.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(294,'csv_footwear_group_export','CSV footwear group export','csv_group_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:14:\"/tmp/group.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(295,'csv_footwear_attribute_import','CSV footwear attribute import','csv_attribute_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(296,'csv_footwear_attribute_export','CSV footwear attribute export','csv_attribute_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:18:\"/tmp/attribute.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(297,'csv_footwear_option_import','CSV footwear option import','csv_attribute_option_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(298,'csv_footwear_option_export','CSV footwear option export','csv_attribute_option_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/option.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(299,'csv_footwear_family_export','CSV footwear family export','csv_family_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/family.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(300,'csv_footwear_channel_export','CSV footwear channel export','csv_channel_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:16:\"/tmp/channel.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(301,'csv_footwear_currency_export','CSV footwear currency export','csv_currency_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:17:\"/tmp/currency.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(302,'csv_footwear_locale_export','CSV footwear locale export','csv_locale_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:15:\"/tmp/locale.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(303,'csv_footwear_group_type_export','CSV footwear group type export','csv_group_type_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:19:\"/tmp/group_type.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(304,'csv_footwear_attribute_group_export','CSV footwear attribute group export','csv_attribute_group_export',0,'Akeneo CSV Connector','a:6:{s:8:\"filePath\";s:24:\"/tmp/attribute_group.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(305,'csv_footwear_family_import','CSV footwear family import','csv_family_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(306,'csv_footwear_currency_import','CSV footwear currency import','csv_currency_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(307,'csv_footwear_channel_import','CSV footwear channel import','csv_channel_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(308,'csv_footwear_locale_import','CSV footwear locale import','csv_locale_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(309,'csv_footwear_group_type_import','CSV footwear group_type import','csv_group_type_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(310,'csv_footwear_attribute_group_import','CSV footwear attribute_group import','csv_attribute_group_import',0,'Akeneo CSV Connector','a:9:{s:8:\"filePath\";N;s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:6:\"escape\";s:1:\"\\\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:3:\"csv\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(311,'update_product_value','Mass update products','update_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(312,'add_product_value','Mass add products values','add_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(313,'remove_product_value','Mass remove products values','remove_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(314,'move_to_category','Mass move to categories','move_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(315,'add_association','Mass associate products','add_association',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(316,'add_to_category','Mass add to categories','add_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(317,'add_to_group','Mass add product to group','add_to_group',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(318,'remove_from_category','Mass remove from categories','remove_from_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(319,'edit_common_attributes','Mass edit product attributes','edit_common_attributes',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(320,'add_attribute_value','Mass add attribute value','add_attribute_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(321,'set_attribute_requirements','Set family attribute requirements','set_attribute_requirements',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(322,'add_to_existing_product_model','Add to existing product model','add_to_existing_product_model',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(323,'delete_products_and_product_models','Mass delete products','delete_products_and_product_models',0,'Akeneo Mass Edit Connector','a:0:{}','mass_delete'),(324,'csv_product_quick_export','CSV product quick export','csv_product_quick_export',0,'Akeneo CSV Connector','a:14:{s:8:\"filePath\";s:42:\"/tmp/php/export_%job_label%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:43:\"/tmp/1_products_export_%locale%_%scope%.csv\";s:20:\"filePathProductModel\";s:49:\"/tmp/2_product_models_export_%locale%_%scope%.csv\";}','quick_export'),(325,'xlsx_footwear_product_import_fr','XLSX footwear product import FR','xlsx_product_import',0,'Akeneo XLSX Connector','a:14:{s:8:\"filePath\";s:30:\"/tmp/footwear_products-fr.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\",\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(326,'xlsx_footwear_product_import','XLSX footwear product import','xlsx_product_import',0,'Akeneo XLSX Connector','a:14:{s:8:\"filePath\";s:27:\"/tmp/footwear_products.xlsx\";s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:16:\"decimalSeparator\";s:1:\".\";s:10:\"dateFormat\";s:10:\"yyyy-MM-dd\";s:7:\"enabled\";b:1;s:16:\"categoriesColumn\";s:10:\"categories\";s:12:\"familyColumn\";s:6:\"family\";s:12:\"groupsColumn\";s:6:\"groups\";s:17:\"enabledComparison\";b:1;s:18:\"realTimeVersioning\";b:1;}','import'),(327,'xlsx_footwear_category_import','XLSX footwear category import','xlsx_category_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(328,'xlsx_footwear_association_type_import','XLSX footwear association type import','xlsx_association_type_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(329,'xlsx_footwear_attribute_import','XLSX footwear attribute import','xlsx_attribute_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(330,'xlsx_footwear_option_import','XLSX footwear option import','xlsx_attribute_option_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(331,'xlsx_footwear_family_import','XLSX footwear family import','xlsx_family_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(332,'xlsx_footwear_currency_import','XLSX footwear currency import','xlsx_currency_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(333,'xlsx_footwear_channel_import','XLSX footwear channel import','xlsx_channel_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(334,'xlsx_footwear_locale_import','XLSX footwear locale import','xlsx_locale_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(335,'xlsx_footwear_group_type_import','XLSX footwear group_type import','xlsx_group_type_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(336,'xlsx_footwear_attribute_group_import','XLSX footwear attribute_group import','xlsx_attribute_group_import',0,'Akeneo XLSX Connector','a:6:{s:8:\"filePath\";N;s:10:\"withHeader\";b:1;s:13:\"uploadAllowed\";b:1;s:25:\"invalid_items_file_format\";s:4:\"xlsx\";s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','import'),(337,'xlsx_product_quick_export','XLSX product quick export','xlsx_product_quick_export',0,'Akeneo XLSX Connector','a:13:{s:8:\"filePath\";s:43:\"/tmp/php/export_%job_label%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:44:\"/tmp/1_products_export_%locale%_%scope%.xlsx\";s:20:\"filePathProductModel\";s:50:\"/tmp/2_product_models_export_%locale%_%scope%.xlsx\";}','quick_export'),(338,'xlsx_footwear_family_export','XLSX footwear family export','xlsx_family_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:16:\"/tmp/family.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(339,'xlsx_footwear_category_export','XLSX footwear category export','xlsx_category_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:18:\"/tmp/category.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(340,'xlsx_footwear_attribute_export','XLSX footwear attribute export','xlsx_attribute_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:19:\"/tmp/attribute.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(341,'xlsx_footwear_option_export','XLSX footwear option export','xlsx_attribute_option_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:16:\"/tmp/option.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(342,'xlsx_footwear_association_type_export','XLSX footwear association type export','xlsx_association_type_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:26:\"/tmp/association_type.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(343,'xlsx_footwear_channel_export','XLSX footwear channel export','xlsx_channel_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:17:\"/tmp/channel.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(344,'xlsx_footwear_currency_export','XLSX footwear currency export','xlsx_currency_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:18:\"/tmp/currency.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(345,'xlsx_footwear_locale_export','XLSX footwear locale export','xlsx_locale_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:16:\"/tmp/locale.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(346,'xlsx_footwear_group_type_export','XLSX footwear group type export','xlsx_group_type_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:20:\"/tmp/group_type.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(347,'xlsx_footwear_attribute_group_export','XLSX footwear attribute group export','xlsx_attribute_group_export',0,'Akeneo XLSX Connector','a:5:{s:8:\"filePath\";s:25:\"/tmp/attribute_group.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;}','export'),(348,'csv_product_grid_context_quick_export','CSV product quick export grid context','csv_product_grid_context_quick_export',0,'Akeneo CSV Connector','a:14:{s:8:\"filePath\";s:42:\"/tmp/php/export_%job_label%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:56:\"/tmp/1_products_export_grid_context_%locale%_%scope%.csv\";s:20:\"filePathProductModel\";s:62:\"/tmp/2_product_models_export_grid_context_%locale%_%scope%.csv\";}','quick_export'),(349,'xlsx_product_grid_context_quick_export','XLSX product quick export grid context','xlsx_product_grid_context_quick_export',0,'Akeneo XLSX Connector','a:13:{s:8:\"filePath\";s:43:\"/tmp/php/export_%job_label%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:57:\"/tmp/1_products_export_grid_context_%locale%_%scope%.xlsx\";s:20:\"filePathProductModel\";s:63:\"/tmp/2_product_models_export_grid_context_%locale%_%scope%.xlsx\";}','quick_export'),(350,'compute_product_models_descendants','Compute product models descendants','compute_product_models_descendants',0,'internal','a:0:{}','compute_product_models_descendants'),(351,'compute_completeness_of_products_family','compute completeness of products family','compute_completeness_of_products_family',0,'internal','a:0:{}','compute_completeness_of_products_family'),(352,'compute_family_variant_structure_changes','Compute variant structure changes','compute_family_variant_structure_changes',0,'internal','a:0:{}','compute_family_variant_structure_changes');
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
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_group`
--

LOCK TABLES `oro_access_group` WRITE;
/*!40000 ALTER TABLE `oro_access_group` DISABLE KEYS */;
INSERT INTO `oro_access_group` VALUES (24,'All'),(21,'IT support'),(22,'Manager'),(23,'Redactor');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_role`
--

LOCK TABLES `oro_access_role` WRITE;
/*!40000 ALTER TABLE `oro_access_role` DISABLE KEYS */;
INSERT INTO `oro_access_role` VALUES (16,'ROLE_ADMINISTRATOR','Administrator'),(17,'ROLE_CATALOG_MANAGER','Catalog manager'),(18,'ROLE_USER','User');
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user`
--

LOCK TABLES `oro_user` WRITE;
/*!40000 ALTER TABLE `oro_user` DISABLE KEYS */;
INSERT INTO `oro_user` VALUES (26,1108,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,NULL,1,'8u54go1xsso4ck0g4ssok888cwc4g4k','1ogabR4lmkOUWY9TWmnM+cY90ndoMaYUKMPM9KZvF5C41tVY2S9V9TZWx/gcwpM27Kz+u5iEMB4wzNj2Gbsx9w==',NULL,NULL,NULL,0,'2018-06-03 13:05:08','2018-06-03 13:05:08','[]',0,NULL,'UTC',1108,13,68),(27,1108,'Julia','Julia@example.com',NULL,'Julia',NULL,'Stark',NULL,NULL,NULL,1,'q582blp48dckw0wwwg8o0w4ocs8c0o8','fsiZCHVmrHWDEOp4Ycmz7Ro1Ur+GOwpGUgfErPZwPBfi9aiv42UsTm2PURwL0w4goT2ZHWEt5uGzA7k4wduDBg==',NULL,NULL,NULL,0,'2018-06-03 13:05:08','2018-06-03 13:05:08','[]',0,NULL,'UTC',1108,12,68),(28,1108,'Peter','Peter@example.com',NULL,'Peter',NULL,'Williams',NULL,NULL,NULL,1,'d1215dywwo8ook88wsow0wkcwssgk00','mts96xJXapPy6dFjFbTwHstcClBF5YmXH/1tEfOM75RzdekmL8qKitUA5K+6Evqsc4v0CNzsfr209BXd42bpEA==',NULL,NULL,NULL,0,'2018-06-03 13:05:08','2018-06-03 13:05:08','[]',0,NULL,'UTC',1108,13,68),(29,1108,'Mary','Mary@example.com',NULL,'Mary',NULL,'Smith',NULL,NULL,NULL,1,'8lk5vz5s570ooswo4ogo8k80cs8g8g0','CuH49PUSLQNW3fmglijgt1wd8gqv1820UzzpOGsY7LqEvdRGOURFk4u+dkI/6HlwXhKeBgKRZovavXmnoKeJtA==',NULL,NULL,NULL,0,'2018-06-03 13:05:08','2018-06-03 13:05:08','[]',0,NULL,'UTC',1108,12,68),(30,1108,'Sandra','Sandra@example.com',NULL,'Sandra',NULL,'Harvey',NULL,NULL,NULL,1,'afv41jllv20wcwkw84w0wgscg04gws8','aa2f7hI1PTJHHK1fZ1jF1ZxhCMQSqUkmbY2SMeZB2esa2vluDU2X/tmO2FrhK16zxQrDtBxzGDNg+SuTkrUtnA==',NULL,NULL,NULL,0,'2018-06-03 13:05:08','2018-06-03 13:05:08','[]',0,NULL,'UTC',1108,13,68),(31,1140,'Julien','Julien@example.com',NULL,'Julien',NULL,'Février',NULL,NULL,NULL,1,'hmibyzlog5k4s48kswos48kggwcc8kg','PkwBfTKKKcIaevjGo22FLB0ZugV3O0wSLYSHaHsjShyOixxVZuE3xTukgf32rQZXy3LCI04ZB9r48V0jxyvHWA==',NULL,NULL,NULL,0,'2018-06-03 13:05:08','2018-06-03 13:05:08','[]',0,NULL,'UTC',1140,13,68);
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
INSERT INTO `oro_user_access_group` VALUES (26,21),(26,24),(27,22),(27,24),(28,21),(28,24),(29,23),(29,24),(30,23),(30,24),(31,23),(31,24);
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
INSERT INTO `oro_user_access_role` VALUES (26,16),(27,17),(28,16),(29,18),(30,18),(31,18);
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type`
--

LOCK TABLES `pim_catalog_association_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type` VALUES (21,'X_SELL','2018-06-03 13:05:07','2018-06-03 13:05:07'),(22,'UPSELL','2018-06-03 13:05:07','2018-06-03 13:05:07'),(23,'SUBSTITUTION','2018-06-03 13:05:07','2018-06-03 13:05:07'),(24,'PACK','2018-06-03 13:05:07','2018-06-03 13:05:07');
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type_translation`
--

LOCK TABLES `pim_catalog_association_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type_translation` VALUES (41,21,'Cross sell','en_US'),(42,22,'Upsell','en_US'),(43,23,'Substitution','en_US'),(44,24,'Pack','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute`
--

LOCK TABLES `pim_catalog_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES (84,21,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,1,1,0,0,'sku','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_identifier','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(85,21,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'name','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(86,21,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'manufacturer','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";b:1;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:07'),(87,21,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'weather_conditions','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:07'),(88,21,5,1,1000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'description','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','textarea','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(89,26,7,1,255,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'comment','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(90,22,1,1,NULL,NULL,NULL,NULL,1.0000,200.0000,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'price','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(91,22,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'rating','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:07'),(92,25,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,pim_catalog_association_group,jpeg,jpg',NULL,0,0,0,0,'side_view','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(93,25,2,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,0,0,'top_view','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(94,23,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'size','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:07'),(95,24,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'color','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:07'),(96,24,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'lace_color','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:07'),(97,21,10,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,'Length','CENTIMETER',NULL,'',NULL,0,0,0,0,'length','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(98,21,20,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,'Volume','CUBIC_MILLIMETER',NULL,'',NULL,0,0,0,0,'volume','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(99,26,8,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'number_in_stock','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(100,26,25,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'destocking_date','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_date','date','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(101,26,30,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'handmade','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(102,26,30,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'heel_color','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:5:\"color\";}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(103,26,35,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'sole_color','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:5:\"color\";}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(104,26,35,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'cap_color','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:5:\"color\";}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(105,26,40,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'sole_fabric','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(106,26,45,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'lace_fabric','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(107,22,1,1,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'rate_sale','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(108,21,30,1,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'Weight','GRAM',NULL,'',NULL,0,0,0,0,'weight','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(109,26,1,1,255,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'123','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','text','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06'),(110,25,2,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1.00,'gif,png,jpeg,jpg',NULL,0,0,1,1,'rear_view','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}','2018-06-03 13:05:06','2018-06-03 13:05:06');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group`
--

LOCK TABLES `pim_catalog_attribute_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group` VALUES (21,'info',1,'2018-06-03 13:05:06','2018-06-03 13:05:06'),(22,'marketing',2,'2018-06-03 13:05:06','2018-06-03 13:05:06'),(23,'sizes',3,'2018-06-03 13:05:06','2018-06-03 13:05:06'),(24,'colors',4,'2018-06-03 13:05:06','2018-06-03 13:05:06'),(25,'media',5,'2018-06-03 13:05:06','2018-06-03 13:05:06'),(26,'other',100,'2018-06-03 13:05:06','2018-06-03 13:05:06');
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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group_translation`
--

LOCK TABLES `pim_catalog_attribute_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group_translation` VALUES (44,21,'Product information','en_US'),(45,22,'Marketing','en_US'),(46,23,'Sizes','en_US'),(47,24,'Colors','en_US'),(48,25,'Media','en_US'),(49,26,'Other','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option`
--

LOCK TABLES `pim_catalog_attribute_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option` VALUES (145,86,'Converse',1),(146,86,'TimberLand',2),(147,86,'Nike',3),(148,86,'Caterpillar',4),(149,87,'dry',1),(150,87,'wet',2),(151,87,'hot',3),(152,87,'cold',4),(153,87,'snowy',5),(154,91,'1',1),(155,91,'2',2),(156,91,'3',3),(157,91,'4',4),(158,91,'5',5),(159,94,'35',1),(160,94,'36',2),(161,94,'37',3),(162,94,'38',4),(163,94,'39',5),(164,94,'40',6),(165,94,'41',7),(166,94,'42',8),(167,94,'43',9),(168,94,'44',10),(169,94,'45',11),(170,94,'46',12),(171,94,'60',13),(172,95,'white',1),(173,95,'black',2),(174,95,'blue',3),(175,95,'maroon',4),(176,95,'saddle',5),(177,95,'greem',6),(178,95,'red',7),(179,95,'charcoal',8),(180,96,'laces_black',1),(181,96,'laces_brown',2),(182,96,'laces_white',3);
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
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option_value`
--

LOCK TABLES `pim_catalog_attribute_option_value` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option_value` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option_value` VALUES (313,145,'en_US','Converse'),(314,146,'en_US','TimberLand'),(315,147,'en_US','Nike'),(316,148,'en_US','Caterpillar'),(317,149,'en_US','Dry'),(318,150,'en_US','Wet'),(319,151,'en_US','Hot'),(320,152,'en_US','Cold'),(321,153,'en_US','Snowy'),(322,154,'en_US','1 star'),(323,155,'en_US','2 stars'),(324,156,'en_US','3 stars'),(325,157,'en_US','4 stars'),(326,158,'en_US','5 stars'),(327,159,'en_US','35'),(328,160,'en_US','36'),(329,161,'en_US','37'),(330,162,'en_US','38'),(331,163,'en_US','39'),(332,164,'en_US','40'),(333,165,'en_US','41'),(334,166,'en_US','42'),(335,167,'en_US','43'),(336,168,'en_US','44'),(337,169,'en_US','45'),(338,170,'en_US','46'),(339,171,'en_US','60'),(340,172,'en_US','White'),(341,173,'en_US','Black'),(342,174,'en_US','Blue'),(343,175,'en_US','Maroon'),(344,176,'en_US','Saddle'),(345,177,'en_US','Greem'),(346,178,'en_US','Red'),(347,179,'en_US','Charcoal'),(348,180,'en_US','Black'),(349,181,'en_US','Brown'),(350,182,'en_US','White');
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
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

LOCK TABLES `pim_catalog_attribute_requirement` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_requirement` VALUES (211,12,84,13,1),(212,12,84,12,1),(213,12,85,12,1),(214,12,88,12,1),(215,12,87,12,1),(216,12,90,12,1),(217,12,91,12,1),(218,12,92,12,1),(219,12,94,12,1),(220,12,95,12,1),(221,12,85,13,1),(222,12,90,13,1),(223,12,94,13,1),(224,12,95,13,1),(225,13,84,13,1),(226,13,84,12,1),(227,13,85,12,1),(228,13,88,12,1),(229,13,90,12,1),(230,13,92,12,1),(231,13,94,12,1),(232,13,95,12,1),(233,13,102,12,1),(234,13,103,12,1),(235,13,85,13,1),(236,13,90,13,1),(237,13,94,13,1),(238,13,95,13,1),(239,13,102,13,1),(240,13,103,13,1),(241,14,84,13,1),(242,14,84,12,1),(243,14,85,12,1),(244,14,88,12,1),(245,14,87,12,1),(246,14,90,12,1),(247,14,91,12,1),(248,14,92,12,1),(249,14,94,12,1),(250,14,95,12,1),(251,14,85,13,1),(252,14,90,13,1),(253,14,94,13,1),(254,14,95,13,1),(255,15,84,13,1),(256,15,84,12,1),(257,15,85,12,1),(258,15,88,12,1),(259,15,90,12,1),(260,15,91,12,1),(261,15,92,12,1),(262,15,94,12,1),(263,15,95,12,1),(264,15,85,13,1),(265,15,90,13,1),(266,15,94,13,1),(267,15,95,13,1),(268,16,84,13,1),(269,16,84,12,1),(270,16,85,12,1),(271,16,88,12,1),(272,16,90,12,1),(273,16,91,12,1),(274,16,92,12,1),(275,16,94,12,1),(276,16,95,12,1),(277,16,85,13,1),(278,16,90,13,1),(279,16,94,13,1),(280,16,95,13,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=230 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_translation`
--

LOCK TABLES `pim_catalog_attribute_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (198,84,'SKU','en_US'),(199,85,'Name','en_US'),(200,86,'Manufacturer','en_US'),(201,87,'Weather conditions','en_US'),(202,88,'Description','en_US'),(203,89,'Comment','en_US'),(204,90,'Price','en_US'),(205,91,'Rating','en_US'),(206,92,'Side view','en_US'),(207,93,'Top view','en_US'),(208,94,'Size','en_US'),(209,95,'Color','en_US'),(210,96,'Lace color','en_US'),(211,97,'Length','en_US'),(212,97,'Longueur','fr_FR'),(213,98,'Volume','en_US'),(214,98,'Volume','fr_FR'),(215,99,'Number in stock','en_US'),(216,100,'Destocking date','en_US'),(217,100,'Date de déstockage','fr_FR'),(218,101,'Handmade','en_US'),(219,102,'Heel color','en_US'),(220,103,'Sole color','en_US'),(221,104,'Cap color','en_US'),(222,105,'Sole fabric','en_US'),(223,106,'Lace fabric','en_US'),(224,107,'Rate of sale','en_US'),(225,107,'Taux de vente','fr_FR'),(226,108,'Weight','en_US'),(227,108,'Poids','fr_FR'),(228,109,'Attribute 123','en_US'),(229,110,'Rear view','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category`
--

LOCK TABLES `pim_catalog_category` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
INSERT INTO `pim_catalog_category` VALUES (68,NULL,'2014_collection','2018-06-03 13:05:06',68,0,1,10),(69,68,'summer_collection','2018-06-03 13:05:06',68,1,2,5),(70,68,'winter_collection','2018-06-03 13:05:06',68,1,6,9),(71,70,'winter_boots','2018-06-03 13:05:06',68,2,7,8),(72,69,'sandals','2018-06-03 13:05:06',68,2,3,4);
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
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_translation`
--

LOCK TABLES `pim_catalog_category_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_translation` VALUES (189,68,'2014 collection','en_US'),(190,69,'Summer collection','en_US'),(191,70,'Winter collection','en_US'),(192,71,'Winter boots','en_US'),(193,72,'Sandals','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel`
--

LOCK TABLES `pim_catalog_channel` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel` VALUES (12,68,'tablet','a:0:{}'),(13,68,'mobile','a:0:{}');
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
INSERT INTO `pim_catalog_channel_currency` VALUES (12,1471),(12,1472),(13,1472);
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
INSERT INTO `pim_catalog_channel_locale` VALUES (12,1108),(13,1108),(13,1140);
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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_translation`
--

LOCK TABLES `pim_catalog_channel_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_translation` VALUES (34,12,'Tablet','en_US'),(35,12,'Tablet','de_DE'),(36,12,'Tablette','fr_FR'),(37,13,'Mobile','en_US'),(38,13,'Mobil','de_DE'),(39,13,'Mobile','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=1765 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_currency`
--

LOCK TABLES `pim_catalog_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (1471,'USD',1),(1472,'EUR',1),(1473,'ADP',0),(1474,'AED',0),(1475,'AFA',0),(1476,'AFN',0),(1477,'ALK',0),(1478,'ALL',0),(1479,'AMD',0),(1480,'ANG',0),(1481,'AOA',0),(1482,'AOK',0),(1483,'AON',0),(1484,'AOR',0),(1485,'ARA',0),(1486,'ARL',0),(1487,'ARM',0),(1488,'ARP',0),(1489,'ARS',0),(1490,'ATS',0),(1491,'AUD',0),(1492,'AWG',0),(1493,'AZM',0),(1494,'AZN',0),(1495,'BAD',0),(1496,'BAM',0),(1497,'BAN',0),(1498,'BBD',0),(1499,'BDT',0),(1500,'BEC',0),(1501,'BEF',0),(1502,'BEL',0),(1503,'BGL',0),(1504,'BGM',0),(1505,'BGN',0),(1506,'BGO',0),(1507,'BHD',0),(1508,'BIF',0),(1509,'BMD',0),(1510,'BND',0),(1511,'BOB',0),(1512,'BOL',0),(1513,'BOP',0),(1514,'BOV',0),(1515,'BRB',0),(1516,'BRC',0),(1517,'BRE',0),(1518,'BRL',0),(1519,'BRN',0),(1520,'BRR',0),(1521,'BRZ',0),(1522,'BSD',0),(1523,'BTN',0),(1524,'BUK',0),(1525,'BWP',0),(1526,'BYB',0),(1527,'BYR',0),(1528,'BZD',0),(1529,'CAD',0),(1530,'CDF',0),(1531,'CHE',0),(1532,'CHF',0),(1533,'CHW',0),(1534,'CLE',0),(1535,'CLF',0),(1536,'CLP',0),(1537,'CNX',0),(1538,'CNY',0),(1539,'COP',0),(1540,'COU',0),(1541,'CRC',0),(1542,'CSD',0),(1543,'CSK',0),(1544,'CUC',0),(1545,'CUP',0),(1546,'CVE',0),(1547,'CYP',0),(1548,'CZK',0),(1549,'DDM',0),(1550,'DEM',0),(1551,'DJF',0),(1552,'DKK',0),(1553,'DOP',0),(1554,'DZD',0),(1555,'ECS',0),(1556,'ECV',0),(1557,'EEK',0),(1558,'EGP',0),(1559,'ERN',0),(1560,'ESA',0),(1561,'ESB',0),(1562,'ESP',0),(1563,'ETB',0),(1564,'FIM',0),(1565,'FJD',0),(1566,'FKP',0),(1567,'FRF',0),(1568,'GBP',0),(1569,'GEK',0),(1570,'GEL',0),(1571,'GHC',0),(1572,'GHS',0),(1573,'GIP',0),(1574,'GMD',0),(1575,'GNF',0),(1576,'GNS',0),(1577,'GQE',0),(1578,'GRD',0),(1579,'GTQ',0),(1580,'GWE',0),(1581,'GWP',0),(1582,'GYD',0),(1583,'HKD',0),(1584,'HNL',0),(1585,'HRD',0),(1586,'HRK',0),(1587,'HTG',0),(1588,'HUF',0),(1589,'IDR',0),(1590,'IEP',0),(1591,'ILP',0),(1592,'ILR',0),(1593,'ILS',0),(1594,'INR',0),(1595,'IQD',0),(1596,'IRR',0),(1597,'ISJ',0),(1598,'ISK',0),(1599,'ITL',0),(1600,'JMD',0),(1601,'JOD',0),(1602,'JPY',0),(1603,'KES',0),(1604,'KGS',0),(1605,'KHR',0),(1606,'KMF',0),(1607,'KPW',0),(1608,'KRH',0),(1609,'KRO',0),(1610,'KRW',0),(1611,'KWD',0),(1612,'KYD',0),(1613,'KZT',0),(1614,'LAK',0),(1615,'LBP',0),(1616,'LKR',0),(1617,'LRD',0),(1618,'LSL',0),(1619,'LTL',0),(1620,'LTT',0),(1621,'LUC',0),(1622,'LUF',0),(1623,'LUL',0),(1624,'LVL',0),(1625,'LVR',0),(1626,'LYD',0),(1627,'MAD',0),(1628,'MAF',0),(1629,'MCF',0),(1630,'MDC',0),(1631,'MDL',0),(1632,'MGA',0),(1633,'MGF',0),(1634,'MKD',0),(1635,'MKN',0),(1636,'MLF',0),(1637,'MMK',0),(1638,'MNT',0),(1639,'MOP',0),(1640,'MRO',0),(1641,'MTL',0),(1642,'MTP',0),(1643,'MUR',0),(1644,'MVP',0),(1645,'MVR',0),(1646,'MWK',0),(1647,'MXN',0),(1648,'MXP',0),(1649,'MXV',0),(1650,'MYR',0),(1651,'MZE',0),(1652,'MZM',0),(1653,'MZN',0),(1654,'NAD',0),(1655,'NGN',0),(1656,'NIC',0),(1657,'NIO',0),(1658,'NLG',0),(1659,'NOK',0),(1660,'NPR',0),(1661,'NZD',0),(1662,'OMR',0),(1663,'PAB',0),(1664,'PEI',0),(1665,'PEN',0),(1666,'PES',0),(1667,'PGK',0),(1668,'PHP',0),(1669,'PKR',0),(1670,'PLN',0),(1671,'PLZ',0),(1672,'PTE',0),(1673,'PYG',0),(1674,'QAR',0),(1675,'RHD',0),(1676,'ROL',0),(1677,'RON',0),(1678,'RSD',0),(1679,'RUB',0),(1680,'RUR',0),(1681,'RWF',0),(1682,'SAR',0),(1683,'SBD',0),(1684,'SCR',0),(1685,'SDD',0),(1686,'SDG',0),(1687,'SDP',0),(1688,'SEK',0),(1689,'SGD',0),(1690,'SHP',0),(1691,'SIT',0),(1692,'SKK',0),(1693,'SLL',0),(1694,'SOS',0),(1695,'SRD',0),(1696,'SRG',0),(1697,'SSP',0),(1698,'STD',0),(1699,'SUR',0),(1700,'SVC',0),(1701,'SYP',0),(1702,'SZL',0),(1703,'THB',0),(1704,'TJR',0),(1705,'TJS',0),(1706,'TMM',0),(1707,'TMT',0),(1708,'TND',0),(1709,'TOP',0),(1710,'TPE',0),(1711,'TRL',0),(1712,'TRY',0),(1713,'TTD',0),(1714,'TWD',0),(1715,'TZS',0),(1716,'UAH',0),(1717,'UAK',0),(1718,'UGS',0),(1719,'UGX',0),(1720,'USN',0),(1721,'USS',0),(1722,'UYI',0),(1723,'UYP',0),(1724,'UYU',0),(1725,'UZS',0),(1726,'VEB',0),(1727,'VEF',0),(1728,'VND',0),(1729,'VNN',0),(1730,'VUV',0),(1731,'WST',0),(1732,'XAF',0),(1733,'XAU',0),(1734,'XBA',0),(1735,'XBB',0),(1736,'XBC',0),(1737,'XBD',0),(1738,'XCD',0),(1739,'XDR',0),(1740,'XEU',0),(1741,'XFO',0),(1742,'XFU',0),(1743,'XOF',0),(1744,'XPD',0),(1745,'XPF',0),(1746,'XRE',0),(1747,'XSU',0),(1748,'XTS',0),(1749,'XXX',0),(1750,'YDD',0),(1751,'YER',0),(1752,'YUD',0),(1753,'YUM',0),(1754,'YUN',0),(1755,'YUR',0),(1756,'ZAL',0),(1757,'ZAR',0),(1758,'ZMK',0),(1759,'ZMW',0),(1760,'ZRN',0),(1761,'ZRZ',0),(1762,'ZWD',0),(1763,'ZWL',0),(1764,'ZWR',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family`
--

LOCK TABLES `pim_catalog_family` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
INSERT INTO `pim_catalog_family` VALUES (12,85,NULL,'boots','2018-06-03 13:05:08','2018-06-03 13:05:08'),(13,85,NULL,'heels','2018-06-03 13:05:08','2018-06-03 13:05:08'),(14,85,NULL,'sneakers','2018-06-03 13:05:08','2018-06-03 13:05:08'),(15,85,92,'sandals','2018-06-03 13:05:08','2018-06-03 13:05:08'),(16,85,NULL,'led_tvs','2018-06-03 13:05:08','2018-06-03 13:05:08');
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
INSERT INTO `pim_catalog_family_attribute` VALUES (12,84),(12,85),(12,86),(12,87),(12,88),(12,90),(12,91),(12,92),(12,93),(12,94),(12,95),(12,96),(13,84),(13,85),(13,86),(13,88),(13,90),(13,92),(13,93),(13,94),(13,95),(13,102),(13,103),(13,105),(14,84),(14,85),(14,86),(14,87),(14,88),(14,90),(14,91),(14,92),(14,93),(14,94),(14,95),(14,96),(15,84),(15,85),(15,86),(15,88),(15,90),(15,91),(15,92),(15,94),(15,95),(16,84),(16,85),(16,86),(16,88),(16,90),(16,91),(16,92),(16,94),(16,95);
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_translation`
--

LOCK TABLES `pim_catalog_family_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_family_translation` VALUES (24,12,'Boots','en_US'),(25,13,'Heels','en_US'),(26,14,'Sneakers','en_US'),(27,15,'Sandals','en_US'),(28,16,'LED TVs','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group`
--

LOCK TABLES `pim_catalog_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_group` VALUES (4,7,'similar_boots');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_translation`
--

LOCK TABLES `pim_catalog_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_translation` VALUES (8,4,'Similar boots','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type`
--

LOCK TABLES `pim_catalog_group_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type` VALUES (7,'RELATED'),(8,'XSELL');
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
INSERT INTO `pim_catalog_group_type_translation` VALUES (6,7,'[RELATED]','en_US'),(7,8,'[XSELL]','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=1261 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_locale`
--

LOCK TABLES `pim_catalog_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_locale` VALUES (1051,'af_ZA',0),(1052,'am_ET',0),(1053,'ar_AE',0),(1054,'ar_BH',0),(1055,'ar_DZ',0),(1056,'ar_EG',0),(1057,'ar_IQ',0),(1058,'ar_JO',0),(1059,'ar_KW',0),(1060,'ar_LB',0),(1061,'ar_LY',0),(1062,'ar_MA',0),(1063,'arn_CL',0),(1064,'ar_OM',0),(1065,'ar_QA',0),(1066,'ar_SA',0),(1067,'ar_SY',0),(1068,'ar_TN',0),(1069,'ar_YE',0),(1070,'as_IN',0),(1071,'az_Cyrl_AZ',0),(1072,'az_Latn_AZ',0),(1073,'ba_RU',0),(1074,'be_BY',0),(1075,'bg_BG',0),(1076,'bn_BD',0),(1077,'bn_IN',0),(1078,'bo_CN',0),(1079,'br_FR',0),(1080,'bs_Cyrl_BA',0),(1081,'bs_Latn_BA',0),(1082,'ca_ES',0),(1083,'co_FR',0),(1084,'cs_CZ',0),(1085,'cy_GB',0),(1086,'da_DK',0),(1087,'de_AT',0),(1088,'de_CH',0),(1089,'de_DE',0),(1090,'de_LI',0),(1091,'de_LU',0),(1092,'dsb_DE',0),(1093,'dv_MV',0),(1094,'el_GR',0),(1095,'en_029',0),(1096,'en_AU',0),(1097,'en_BZ',0),(1098,'en_CA',0),(1099,'en_GB',0),(1100,'en_IE',0),(1101,'en_IN',0),(1102,'en_JM',0),(1103,'en_MY',0),(1104,'en_NZ',0),(1105,'en_PH',0),(1106,'en_SG',0),(1107,'en_TT',0),(1108,'en_US',1),(1109,'en_ZA',0),(1110,'en_ZW',0),(1111,'es_AR',0),(1112,'es_BO',0),(1113,'es_CL',0),(1114,'es_CO',0),(1115,'es_CR',0),(1116,'es_DO',0),(1117,'es_EC',0),(1118,'es_ES',0),(1119,'es_GT',0),(1120,'es_HN',0),(1121,'es_MX',0),(1122,'es_NI',0),(1123,'es_PA',0),(1124,'es_PE',0),(1125,'es_PR',0),(1126,'es_PY',0),(1127,'es_SV',0),(1128,'es_US',0),(1129,'es_UY',0),(1130,'es_VE',0),(1131,'et_EE',0),(1132,'eu_ES',0),(1133,'fa_IR',0),(1134,'fi_FI',0),(1135,'fil_PH',0),(1136,'fo_FO',0),(1137,'fr_BE',0),(1138,'fr_CA',0),(1139,'fr_CH',0),(1140,'fr_FR',1),(1141,'fr_LU',0),(1142,'fr_MC',0),(1143,'fy_NL',0),(1144,'ga_IE',0),(1145,'gd_GB',0),(1146,'gl_ES',0),(1147,'gsw_FR',0),(1148,'gu_IN',0),(1149,'ha_Latn_NG',0),(1150,'he_IL',0),(1151,'hi_IN',0),(1152,'hr_BA',0),(1153,'hr_HR',0),(1154,'hsb_DE',0),(1155,'hu_HU',0),(1156,'hy_AM',0),(1157,'id_ID',0),(1158,'ig_NG',0),(1159,'ii_CN',0),(1160,'is_IS',0),(1161,'it_CH',0),(1162,'it_IT',0),(1163,'iu_Cans_CA',0),(1164,'iu_Latn_CA',0),(1165,'ja_JP',0),(1166,'ka_GE',0),(1167,'kk_KZ',0),(1168,'kl_GL',0),(1169,'km_KH',0),(1170,'kn_IN',0),(1171,'kok_IN',0),(1172,'ko_KR',0),(1173,'ky_KG',0),(1174,'lb_LU',0),(1175,'lo_LA',0),(1176,'lt_LT',0),(1177,'lv_LV',0),(1178,'mi_NZ',0),(1179,'mk_MK',0),(1180,'ml_IN',0),(1181,'mn_MN',0),(1182,'mn_Mong_CN',0),(1183,'moh_CA',0),(1184,'mr_IN',0),(1185,'ms_BN',0),(1186,'ms_MY',0),(1187,'mt_MT',0),(1188,'nb_NO',0),(1189,'ne_NP',0),(1190,'nl_BE',0),(1191,'nl_NL',0),(1192,'nn_NO',0),(1193,'nso_ZA',0),(1194,'oc_FR',0),(1195,'or_IN',0),(1196,'pa_IN',0),(1197,'pl_PL',0),(1198,'prs_AF',0),(1199,'ps_AF',0),(1200,'pt_BR',0),(1201,'pt_PT',0),(1202,'qut_GT',0),(1203,'quz_BO',0),(1204,'quz_EC',0),(1205,'quz_PE',0),(1206,'rm_CH',0),(1207,'ro_RO',0),(1208,'ru_RU',0),(1209,'rw_RW',0),(1210,'sah_RU',0),(1211,'sa_IN',0),(1212,'se_FI',0),(1213,'se_NO',0),(1214,'se_SE',0),(1215,'si_LK',0),(1216,'sk_SK',0),(1217,'sl_SI',0),(1218,'sma_NO',0),(1219,'sma_SE',0),(1220,'smj_NO',0),(1221,'smj_SE',0),(1222,'smn_FI',0),(1223,'sms_FI',0),(1224,'sq_AL',0),(1225,'sr_Cyrl_BA',0),(1226,'sr_Cyrl_CS',0),(1227,'sr_Cyrl_ME',0),(1228,'sr_Cyrl_RS',0),(1229,'sr_Latn_BA',0),(1230,'sr_Latn_CS',0),(1231,'sr_Latn_ME',0),(1232,'sr_Latn_RS',0),(1233,'sv_FI',0),(1234,'sv_SE',0),(1235,'sw_KE',0),(1236,'syr_SY',0),(1237,'ta_IN',0),(1238,'te_IN',0),(1239,'tg_Cyrl_TJ',0),(1240,'th_TH',0),(1241,'tk_TM',0),(1242,'tn_ZA',0),(1243,'tr_TR',0),(1244,'tt_RU',0),(1245,'tzm_Latn_DZ',0),(1246,'ug_CN',0),(1247,'uk_UA',0),(1248,'ur_PK',0),(1249,'uz_Cyrl_UZ',0),(1250,'uz_Latn_UZ',0),(1251,'vi_VN',0),(1252,'wo_SN',0),(1253,'xh_ZA',0),(1254,'yo_NG',0),(1255,'zh_CN',0),(1256,'zh_HK',0),(1257,'zh_MO',0),(1258,'zh_SG',0),(1259,'zh_TW',0),(1260,'zu_ZA',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=2572 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_versioning_version`
--

LOCK TABLES `pim_versioning_version` WRITE;
/*!40000 ALTER TABLE `pim_versioning_version` DISABLE KEYS */;
INSERT INTO `pim_versioning_version` VALUES (2215,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','268','a:5:{s:4:\"code\";s:22:\"fixtures_currency_csv0\";s:5:\"label\";s:24:\"Currencies data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_currency_csv0\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Currencies data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2216,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','269','a:5:{s:4:\"code\";s:20:\"fixtures_locale_csv1\";s:5:\"label\";s:21:\"Locales data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:334:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_locale_csv1\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Locales data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:334:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2217,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','270','a:5:{s:4:\"code\";s:24:\"fixtures_user_group_csv2\";s:5:\"label\";s:25:\"User groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:338:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_group_csv2\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"User groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:338:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2218,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','271','a:5:{s:4:\"code\";s:22:\"fixtures_category_csv3\";s:5:\"label\";s:24:\"Categories data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_category_csv3\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Categories data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2219,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','272','a:5:{s:4:\"code\";s:29:\"fixtures_attribute_group_csv4\";s:5:\"label\";s:30:\"Attribute groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:343:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_attribute_group_csv4\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"Attribute groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:343:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2220,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','273','a:5:{s:4:\"code\";s:24:\"fixtures_group_type_csv5\";s:5:\"label\";s:25:\"Group types data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:338:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_group_type_csv5\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Group types data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:338:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2221,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','274','a:5:{s:4:\"code\";s:23:\"fixtures_attribute_csv6\";s:5:\"label\";s:24:\"Attributes data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"fixtures_attribute_csv6\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Attributes data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2222,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','275','a:5:{s:4:\"code\";s:21:\"fixtures_channel_csv7\";s:5:\"label\";s:22:\"Channels data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:335:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_channel_csv7\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Channels data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:335:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2223,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','276','a:5:{s:4:\"code\";s:30:\"fixtures_association_type_csv8\";s:5:\"label\";s:26:\"Associations data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:344:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"fixtures_association_type_csv8\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Associations data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:344:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2224,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','277','a:5:{s:4:\"code\";s:31:\"fixtures_attribute_options_csv9\";s:5:\"label\";s:31:\"Attribute options data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:344:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"fixtures_attribute_options_csv9\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"Attribute options data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:344:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2225,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','278','a:5:{s:4:\"code\";s:21:\"fixtures_family_csv10\";s:5:\"label\";s:22:\"Families data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:335:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_family_csv10\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Families data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:335:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2226,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','279','a:5:{s:4:\"code\";s:18:\"fixtures_job_yml11\";s:5:\"label\";s:18:\"Jobs data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:267:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"fixtures_job_yml11\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Jobs data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:267:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2227,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','280','a:5:{s:4:\"code\";s:29:\"fixtures_family_variant_csv12\";s:5:\"label\";s:29:\"Family variants data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:342:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_family_variant_csv12\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Family variants data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:342:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2228,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','281','a:5:{s:4:\"code\";s:20:\"fixtures_group_csv13\";s:5:\"label\";s:20:\"Groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:333:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_group_csv13\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:333:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2229,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','282','a:5:{s:4:\"code\";s:24:\"fixtures_user_role_csv14\";s:5:\"label\";s:24:\"User roles data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_role_csv14\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"User roles data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:337:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2230,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','283','a:5:{s:4:\"code\";s:19:\"fixtures_user_csv15\";s:5:\"label\";s:19:\"Users data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:332:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"fixtures_user_csv15\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Users data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:332:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2231,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','284','a:5:{s:4:\"code\";s:28:\"fixtures_product_model_csv16\";s:5:\"label\";s:27:\"Product model data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:527:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"fixtures_product_model_csv16\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Product model data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:527:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2232,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','285','a:5:{s:4:\"code\";s:22:\"fixtures_product_csv17\";s:5:\"label\";s:21:\"Product data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:530:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_product_csv17\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Product data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:530:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/ped\\/vendor\\/akeneo\\/pim-community-dev\\/tests\\/legacy\\/features\\/Context\\/catalog\\/footwear\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:04',0),(2233,'system','Akeneo\\Channel\\Component\\Model\\Locale','1051','a:1:{s:4:\"code\";s:5:\"af_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"af_ZA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2234,'system','Akeneo\\Channel\\Component\\Model\\Locale','1052','a:1:{s:4:\"code\";s:5:\"am_ET\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"am_ET\";}}',NULL,1,'2018-06-03 13:05:05',0),(2235,'system','Akeneo\\Channel\\Component\\Model\\Locale','1053','a:1:{s:4:\"code\";s:5:\"ar_AE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_AE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2236,'system','Akeneo\\Channel\\Component\\Model\\Locale','1054','a:1:{s:4:\"code\";s:5:\"ar_BH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_BH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2237,'system','Akeneo\\Channel\\Component\\Model\\Locale','1055','a:1:{s:4:\"code\";s:5:\"ar_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_DZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2238,'system','Akeneo\\Channel\\Component\\Model\\Locale','1056','a:1:{s:4:\"code\";s:5:\"ar_EG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2239,'system','Akeneo\\Channel\\Component\\Model\\Locale','1057','a:1:{s:4:\"code\";s:5:\"ar_IQ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_IQ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2240,'system','Akeneo\\Channel\\Component\\Model\\Locale','1058','a:1:{s:4:\"code\";s:5:\"ar_JO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_JO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2241,'system','Akeneo\\Channel\\Component\\Model\\Locale','1059','a:1:{s:4:\"code\";s:5:\"ar_KW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_KW\";}}',NULL,1,'2018-06-03 13:05:05',0),(2242,'system','Akeneo\\Channel\\Component\\Model\\Locale','1060','a:1:{s:4:\"code\";s:5:\"ar_LB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LB\";}}',NULL,1,'2018-06-03 13:05:05',0),(2243,'system','Akeneo\\Channel\\Component\\Model\\Locale','1061','a:1:{s:4:\"code\";s:5:\"ar_LY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2244,'system','Akeneo\\Channel\\Component\\Model\\Locale','1062','a:1:{s:4:\"code\";s:5:\"ar_MA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_MA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2245,'system','Akeneo\\Channel\\Component\\Model\\Locale','1063','a:1:{s:4:\"code\";s:6:\"arn_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"arn_CL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2246,'system','Akeneo\\Channel\\Component\\Model\\Locale','1064','a:1:{s:4:\"code\";s:5:\"ar_OM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_OM\";}}',NULL,1,'2018-06-03 13:05:05',0),(2247,'system','Akeneo\\Channel\\Component\\Model\\Locale','1065','a:1:{s:4:\"code\";s:5:\"ar_QA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_QA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2248,'system','Akeneo\\Channel\\Component\\Model\\Locale','1066','a:1:{s:4:\"code\";s:5:\"ar_SA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2249,'system','Akeneo\\Channel\\Component\\Model\\Locale','1067','a:1:{s:4:\"code\";s:5:\"ar_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2250,'system','Akeneo\\Channel\\Component\\Model\\Locale','1068','a:1:{s:4:\"code\";s:5:\"ar_TN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_TN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2251,'system','Akeneo\\Channel\\Component\\Model\\Locale','1069','a:1:{s:4:\"code\";s:5:\"ar_YE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_YE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2252,'system','Akeneo\\Channel\\Component\\Model\\Locale','1070','a:1:{s:4:\"code\";s:5:\"as_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"as_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2253,'system','Akeneo\\Channel\\Component\\Model\\Locale','1071','a:1:{s:4:\"code\";s:10:\"az_Cyrl_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Cyrl_AZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2254,'system','Akeneo\\Channel\\Component\\Model\\Locale','1072','a:1:{s:4:\"code\";s:10:\"az_Latn_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Latn_AZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2255,'system','Akeneo\\Channel\\Component\\Model\\Locale','1073','a:1:{s:4:\"code\";s:5:\"ba_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ba_RU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2256,'system','Akeneo\\Channel\\Component\\Model\\Locale','1074','a:1:{s:4:\"code\";s:5:\"be_BY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"be_BY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2257,'system','Akeneo\\Channel\\Component\\Model\\Locale','1075','a:1:{s:4:\"code\";s:5:\"bg_BG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bg_BG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2258,'system','Akeneo\\Channel\\Component\\Model\\Locale','1076','a:1:{s:4:\"code\";s:5:\"bn_BD\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_BD\";}}',NULL,1,'2018-06-03 13:05:05',0),(2259,'system','Akeneo\\Channel\\Component\\Model\\Locale','1077','a:1:{s:4:\"code\";s:5:\"bn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2260,'system','Akeneo\\Channel\\Component\\Model\\Locale','1078','a:1:{s:4:\"code\";s:5:\"bo_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bo_CN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2261,'system','Akeneo\\Channel\\Component\\Model\\Locale','1079','a:1:{s:4:\"code\";s:5:\"br_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"br_FR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2262,'system','Akeneo\\Channel\\Component\\Model\\Locale','1080','a:1:{s:4:\"code\";s:10:\"bs_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Cyrl_BA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2263,'system','Akeneo\\Channel\\Component\\Model\\Locale','1081','a:1:{s:4:\"code\";s:10:\"bs_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Latn_BA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2264,'system','Akeneo\\Channel\\Component\\Model\\Locale','1082','a:1:{s:4:\"code\";s:5:\"ca_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ca_ES\";}}',NULL,1,'2018-06-03 13:05:05',0),(2265,'system','Akeneo\\Channel\\Component\\Model\\Locale','1083','a:1:{s:4:\"code\";s:5:\"co_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"co_FR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2266,'system','Akeneo\\Channel\\Component\\Model\\Locale','1084','a:1:{s:4:\"code\";s:5:\"cs_CZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cs_CZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2267,'system','Akeneo\\Channel\\Component\\Model\\Locale','1085','a:1:{s:4:\"code\";s:5:\"cy_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cy_GB\";}}',NULL,1,'2018-06-03 13:05:05',0),(2268,'system','Akeneo\\Channel\\Component\\Model\\Locale','1086','a:1:{s:4:\"code\";s:5:\"da_DK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"da_DK\";}}',NULL,1,'2018-06-03 13:05:05',0),(2269,'system','Akeneo\\Channel\\Component\\Model\\Locale','1087','a:1:{s:4:\"code\";s:5:\"de_AT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_AT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2270,'system','Akeneo\\Channel\\Component\\Model\\Locale','1088','a:1:{s:4:\"code\";s:5:\"de_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_CH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2271,'system','Akeneo\\Channel\\Component\\Model\\Locale','1089','a:1:{s:4:\"code\";s:5:\"de_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2272,'system','Akeneo\\Channel\\Component\\Model\\Locale','1090','a:1:{s:4:\"code\";s:5:\"de_LI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2273,'system','Akeneo\\Channel\\Component\\Model\\Locale','1091','a:1:{s:4:\"code\";s:5:\"de_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2274,'system','Akeneo\\Channel\\Component\\Model\\Locale','1092','a:1:{s:4:\"code\";s:6:\"dsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"dsb_DE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2275,'system','Akeneo\\Channel\\Component\\Model\\Locale','1093','a:1:{s:4:\"code\";s:5:\"dv_MV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"dv_MV\";}}',NULL,1,'2018-06-03 13:05:05',0),(2276,'system','Akeneo\\Channel\\Component\\Model\\Locale','1094','a:1:{s:4:\"code\";s:5:\"el_GR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"el_GR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2277,'system','Akeneo\\Channel\\Component\\Model\\Locale','1095','a:1:{s:4:\"code\";s:6:\"en_029\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"en_029\";}}',NULL,1,'2018-06-03 13:05:05',0),(2278,'system','Akeneo\\Channel\\Component\\Model\\Locale','1096','a:1:{s:4:\"code\";s:5:\"en_AU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_AU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2279,'system','Akeneo\\Channel\\Component\\Model\\Locale','1097','a:1:{s:4:\"code\";s:5:\"en_BZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_BZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2280,'system','Akeneo\\Channel\\Component\\Model\\Locale','1098','a:1:{s:4:\"code\";s:5:\"en_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_CA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2281,'system','Akeneo\\Channel\\Component\\Model\\Locale','1099','a:1:{s:4:\"code\";s:5:\"en_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_GB\";}}',NULL,1,'2018-06-03 13:05:05',0),(2282,'system','Akeneo\\Channel\\Component\\Model\\Locale','1100','a:1:{s:4:\"code\";s:5:\"en_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2283,'system','Akeneo\\Channel\\Component\\Model\\Locale','1101','a:1:{s:4:\"code\";s:5:\"en_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2284,'system','Akeneo\\Channel\\Component\\Model\\Locale','1102','a:1:{s:4:\"code\";s:5:\"en_JM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_JM\";}}',NULL,1,'2018-06-03 13:05:05',0),(2285,'system','Akeneo\\Channel\\Component\\Model\\Locale','1103','a:1:{s:4:\"code\";s:5:\"en_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_MY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2286,'system','Akeneo\\Channel\\Component\\Model\\Locale','1104','a:1:{s:4:\"code\";s:5:\"en_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_NZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2287,'system','Akeneo\\Channel\\Component\\Model\\Locale','1105','a:1:{s:4:\"code\";s:5:\"en_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_PH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2288,'system','Akeneo\\Channel\\Component\\Model\\Locale','1106','a:1:{s:4:\"code\";s:5:\"en_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_SG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2289,'system','Akeneo\\Channel\\Component\\Model\\Locale','1107','a:1:{s:4:\"code\";s:5:\"en_TT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_TT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2290,'system','Akeneo\\Channel\\Component\\Model\\Locale','1108','a:1:{s:4:\"code\";s:5:\"en_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}}',NULL,1,'2018-06-03 13:05:05',0),(2291,'system','Akeneo\\Channel\\Component\\Model\\Locale','1109','a:1:{s:4:\"code\";s:5:\"en_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2292,'system','Akeneo\\Channel\\Component\\Model\\Locale','1110','a:1:{s:4:\"code\";s:5:\"en_ZW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZW\";}}',NULL,1,'2018-06-03 13:05:05',0),(2293,'system','Akeneo\\Channel\\Component\\Model\\Locale','1111','a:1:{s:4:\"code\";s:5:\"es_AR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_AR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2294,'system','Akeneo\\Channel\\Component\\Model\\Locale','1112','a:1:{s:4:\"code\";s:5:\"es_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_BO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2295,'system','Akeneo\\Channel\\Component\\Model\\Locale','1113','a:1:{s:4:\"code\";s:5:\"es_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2296,'system','Akeneo\\Channel\\Component\\Model\\Locale','1114','a:1:{s:4:\"code\";s:5:\"es_CO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2297,'system','Akeneo\\Channel\\Component\\Model\\Locale','1115','a:1:{s:4:\"code\";s:5:\"es_CR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2298,'system','Akeneo\\Channel\\Component\\Model\\Locale','1116','a:1:{s:4:\"code\";s:5:\"es_DO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_DO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2299,'system','Akeneo\\Channel\\Component\\Model\\Locale','1117','a:1:{s:4:\"code\";s:5:\"es_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_EC\";}}',NULL,1,'2018-06-03 13:05:05',0),(2300,'system','Akeneo\\Channel\\Component\\Model\\Locale','1118','a:1:{s:4:\"code\";s:5:\"es_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_ES\";}}',NULL,1,'2018-06-03 13:05:05',0),(2301,'system','Akeneo\\Channel\\Component\\Model\\Locale','1119','a:1:{s:4:\"code\";s:5:\"es_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_GT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2302,'system','Akeneo\\Channel\\Component\\Model\\Locale','1120','a:1:{s:4:\"code\";s:5:\"es_HN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_HN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2303,'system','Akeneo\\Channel\\Component\\Model\\Locale','1121','a:1:{s:4:\"code\";s:5:\"es_MX\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_MX\";}}',NULL,1,'2018-06-03 13:05:05',0),(2304,'system','Akeneo\\Channel\\Component\\Model\\Locale','1122','a:1:{s:4:\"code\";s:5:\"es_NI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_NI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2305,'system','Akeneo\\Channel\\Component\\Model\\Locale','1123','a:1:{s:4:\"code\";s:5:\"es_PA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2306,'system','Akeneo\\Channel\\Component\\Model\\Locale','1124','a:1:{s:4:\"code\";s:5:\"es_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2307,'system','Akeneo\\Channel\\Component\\Model\\Locale','1125','a:1:{s:4:\"code\";s:5:\"es_PR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2308,'system','Akeneo\\Channel\\Component\\Model\\Locale','1126','a:1:{s:4:\"code\";s:5:\"es_PY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2309,'system','Akeneo\\Channel\\Component\\Model\\Locale','1127','a:1:{s:4:\"code\";s:5:\"es_SV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_SV\";}}',NULL,1,'2018-06-03 13:05:05',0),(2310,'system','Akeneo\\Channel\\Component\\Model\\Locale','1128','a:1:{s:4:\"code\";s:5:\"es_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_US\";}}',NULL,1,'2018-06-03 13:05:05',0),(2311,'system','Akeneo\\Channel\\Component\\Model\\Locale','1129','a:1:{s:4:\"code\";s:5:\"es_UY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_UY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2312,'system','Akeneo\\Channel\\Component\\Model\\Locale','1130','a:1:{s:4:\"code\";s:5:\"es_VE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_VE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2313,'system','Akeneo\\Channel\\Component\\Model\\Locale','1131','a:1:{s:4:\"code\";s:5:\"et_EE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"et_EE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2314,'system','Akeneo\\Channel\\Component\\Model\\Locale','1132','a:1:{s:4:\"code\";s:5:\"eu_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"eu_ES\";}}',NULL,1,'2018-06-03 13:05:05',0),(2315,'system','Akeneo\\Channel\\Component\\Model\\Locale','1133','a:1:{s:4:\"code\";s:5:\"fa_IR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fa_IR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2316,'system','Akeneo\\Channel\\Component\\Model\\Locale','1134','a:1:{s:4:\"code\";s:5:\"fi_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fi_FI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2317,'system','Akeneo\\Channel\\Component\\Model\\Locale','1135','a:1:{s:4:\"code\";s:6:\"fil_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"fil_PH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2318,'system','Akeneo\\Channel\\Component\\Model\\Locale','1136','a:1:{s:4:\"code\";s:5:\"fo_FO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fo_FO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2319,'system','Akeneo\\Channel\\Component\\Model\\Locale','1137','a:1:{s:4:\"code\";s:5:\"fr_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_BE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2320,'system','Akeneo\\Channel\\Component\\Model\\Locale','1138','a:1:{s:4:\"code\";s:5:\"fr_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2321,'system','Akeneo\\Channel\\Component\\Model\\Locale','1139','a:1:{s:4:\"code\";s:5:\"fr_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2322,'system','Akeneo\\Channel\\Component\\Model\\Locale','1140','a:1:{s:4:\"code\";s:5:\"fr_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2323,'system','Akeneo\\Channel\\Component\\Model\\Locale','1141','a:1:{s:4:\"code\";s:5:\"fr_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_LU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2324,'system','Akeneo\\Channel\\Component\\Model\\Locale','1142','a:1:{s:4:\"code\";s:5:\"fr_MC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_MC\";}}',NULL,1,'2018-06-03 13:05:05',0),(2325,'system','Akeneo\\Channel\\Component\\Model\\Locale','1143','a:1:{s:4:\"code\";s:5:\"fy_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fy_NL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2326,'system','Akeneo\\Channel\\Component\\Model\\Locale','1144','a:1:{s:4:\"code\";s:5:\"ga_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ga_IE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2327,'system','Akeneo\\Channel\\Component\\Model\\Locale','1145','a:1:{s:4:\"code\";s:5:\"gd_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gd_GB\";}}',NULL,1,'2018-06-03 13:05:05',0),(2328,'system','Akeneo\\Channel\\Component\\Model\\Locale','1146','a:1:{s:4:\"code\";s:5:\"gl_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gl_ES\";}}',NULL,1,'2018-06-03 13:05:05',0),(2329,'system','Akeneo\\Channel\\Component\\Model\\Locale','1147','a:1:{s:4:\"code\";s:6:\"gsw_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"gsw_FR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2330,'system','Akeneo\\Channel\\Component\\Model\\Locale','1148','a:1:{s:4:\"code\";s:5:\"gu_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gu_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2331,'system','Akeneo\\Channel\\Component\\Model\\Locale','1149','a:1:{s:4:\"code\";s:10:\"ha_Latn_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"ha_Latn_NG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2332,'system','Akeneo\\Channel\\Component\\Model\\Locale','1150','a:1:{s:4:\"code\";s:5:\"he_IL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"he_IL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2333,'system','Akeneo\\Channel\\Component\\Model\\Locale','1151','a:1:{s:4:\"code\";s:5:\"hi_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hi_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2334,'system','Akeneo\\Channel\\Component\\Model\\Locale','1152','a:1:{s:4:\"code\";s:5:\"hr_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_BA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2335,'system','Akeneo\\Channel\\Component\\Model\\Locale','1153','a:1:{s:4:\"code\";s:5:\"hr_HR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_HR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2336,'system','Akeneo\\Channel\\Component\\Model\\Locale','1154','a:1:{s:4:\"code\";s:6:\"hsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"hsb_DE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2337,'system','Akeneo\\Channel\\Component\\Model\\Locale','1155','a:1:{s:4:\"code\";s:5:\"hu_HU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hu_HU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2338,'system','Akeneo\\Channel\\Component\\Model\\Locale','1156','a:1:{s:4:\"code\";s:5:\"hy_AM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hy_AM\";}}',NULL,1,'2018-06-03 13:05:05',0),(2339,'system','Akeneo\\Channel\\Component\\Model\\Locale','1157','a:1:{s:4:\"code\";s:5:\"id_ID\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"id_ID\";}}',NULL,1,'2018-06-03 13:05:05',0),(2340,'system','Akeneo\\Channel\\Component\\Model\\Locale','1158','a:1:{s:4:\"code\";s:5:\"ig_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ig_NG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2341,'system','Akeneo\\Channel\\Component\\Model\\Locale','1159','a:1:{s:4:\"code\";s:5:\"ii_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ii_CN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2342,'system','Akeneo\\Channel\\Component\\Model\\Locale','1160','a:1:{s:4:\"code\";s:5:\"is_IS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"is_IS\";}}',NULL,1,'2018-06-03 13:05:05',0),(2343,'system','Akeneo\\Channel\\Component\\Model\\Locale','1161','a:1:{s:4:\"code\";s:5:\"it_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_CH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2344,'system','Akeneo\\Channel\\Component\\Model\\Locale','1162','a:1:{s:4:\"code\";s:5:\"it_IT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_IT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2345,'system','Akeneo\\Channel\\Component\\Model\\Locale','1163','a:1:{s:4:\"code\";s:10:\"iu_Cans_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Cans_CA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2346,'system','Akeneo\\Channel\\Component\\Model\\Locale','1164','a:1:{s:4:\"code\";s:10:\"iu_Latn_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Latn_CA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2347,'system','Akeneo\\Channel\\Component\\Model\\Locale','1165','a:1:{s:4:\"code\";s:5:\"ja_JP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ja_JP\";}}',NULL,1,'2018-06-03 13:05:05',0),(2348,'system','Akeneo\\Channel\\Component\\Model\\Locale','1166','a:1:{s:4:\"code\";s:5:\"ka_GE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ka_GE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2349,'system','Akeneo\\Channel\\Component\\Model\\Locale','1167','a:1:{s:4:\"code\";s:5:\"kk_KZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kk_KZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2350,'system','Akeneo\\Channel\\Component\\Model\\Locale','1168','a:1:{s:4:\"code\";s:5:\"kl_GL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kl_GL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2351,'system','Akeneo\\Channel\\Component\\Model\\Locale','1169','a:1:{s:4:\"code\";s:5:\"km_KH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"km_KH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2352,'system','Akeneo\\Channel\\Component\\Model\\Locale','1170','a:1:{s:4:\"code\";s:5:\"kn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kn_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2353,'system','Akeneo\\Channel\\Component\\Model\\Locale','1171','a:1:{s:4:\"code\";s:6:\"kok_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"kok_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2354,'system','Akeneo\\Channel\\Component\\Model\\Locale','1172','a:1:{s:4:\"code\";s:5:\"ko_KR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ko_KR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2355,'system','Akeneo\\Channel\\Component\\Model\\Locale','1173','a:1:{s:4:\"code\";s:5:\"ky_KG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ky_KG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2356,'system','Akeneo\\Channel\\Component\\Model\\Locale','1174','a:1:{s:4:\"code\";s:5:\"lb_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lb_LU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2357,'system','Akeneo\\Channel\\Component\\Model\\Locale','1175','a:1:{s:4:\"code\";s:5:\"lo_LA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lo_LA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2358,'system','Akeneo\\Channel\\Component\\Model\\Locale','1176','a:1:{s:4:\"code\";s:5:\"lt_LT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lt_LT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2359,'system','Akeneo\\Channel\\Component\\Model\\Locale','1177','a:1:{s:4:\"code\";s:5:\"lv_LV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lv_LV\";}}',NULL,1,'2018-06-03 13:05:05',0),(2360,'system','Akeneo\\Channel\\Component\\Model\\Locale','1178','a:1:{s:4:\"code\";s:5:\"mi_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mi_NZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2361,'system','Akeneo\\Channel\\Component\\Model\\Locale','1179','a:1:{s:4:\"code\";s:5:\"mk_MK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mk_MK\";}}',NULL,1,'2018-06-03 13:05:05',0),(2362,'system','Akeneo\\Channel\\Component\\Model\\Locale','1180','a:1:{s:4:\"code\";s:5:\"ml_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ml_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2363,'system','Akeneo\\Channel\\Component\\Model\\Locale','1181','a:1:{s:4:\"code\";s:5:\"mn_MN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mn_MN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2364,'system','Akeneo\\Channel\\Component\\Model\\Locale','1182','a:1:{s:4:\"code\";s:10:\"mn_Mong_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"mn_Mong_CN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2365,'system','Akeneo\\Channel\\Component\\Model\\Locale','1183','a:1:{s:4:\"code\";s:6:\"moh_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"moh_CA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2366,'system','Akeneo\\Channel\\Component\\Model\\Locale','1184','a:1:{s:4:\"code\";s:5:\"mr_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mr_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2367,'system','Akeneo\\Channel\\Component\\Model\\Locale','1185','a:1:{s:4:\"code\";s:5:\"ms_BN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_BN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2368,'system','Akeneo\\Channel\\Component\\Model\\Locale','1186','a:1:{s:4:\"code\";s:5:\"ms_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_MY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2369,'system','Akeneo\\Channel\\Component\\Model\\Locale','1187','a:1:{s:4:\"code\";s:5:\"mt_MT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mt_MT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2370,'system','Akeneo\\Channel\\Component\\Model\\Locale','1188','a:1:{s:4:\"code\";s:5:\"nb_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nb_NO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2371,'system','Akeneo\\Channel\\Component\\Model\\Locale','1189','a:1:{s:4:\"code\";s:5:\"ne_NP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ne_NP\";}}',NULL,1,'2018-06-03 13:05:05',0),(2372,'system','Akeneo\\Channel\\Component\\Model\\Locale','1190','a:1:{s:4:\"code\";s:5:\"nl_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_BE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2373,'system','Akeneo\\Channel\\Component\\Model\\Locale','1191','a:1:{s:4:\"code\";s:5:\"nl_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_NL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2374,'system','Akeneo\\Channel\\Component\\Model\\Locale','1192','a:1:{s:4:\"code\";s:5:\"nn_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nn_NO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2375,'system','Akeneo\\Channel\\Component\\Model\\Locale','1193','a:1:{s:4:\"code\";s:6:\"nso_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"nso_ZA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2376,'system','Akeneo\\Channel\\Component\\Model\\Locale','1194','a:1:{s:4:\"code\";s:5:\"oc_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"oc_FR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2377,'system','Akeneo\\Channel\\Component\\Model\\Locale','1195','a:1:{s:4:\"code\";s:5:\"or_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"or_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2378,'system','Akeneo\\Channel\\Component\\Model\\Locale','1196','a:1:{s:4:\"code\";s:5:\"pa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pa_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2379,'system','Akeneo\\Channel\\Component\\Model\\Locale','1197','a:1:{s:4:\"code\";s:5:\"pl_PL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pl_PL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2380,'system','Akeneo\\Channel\\Component\\Model\\Locale','1198','a:1:{s:4:\"code\";s:6:\"prs_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"prs_AF\";}}',NULL,1,'2018-06-03 13:05:05',0),(2381,'system','Akeneo\\Channel\\Component\\Model\\Locale','1199','a:1:{s:4:\"code\";s:5:\"ps_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ps_AF\";}}',NULL,1,'2018-06-03 13:05:05',0),(2382,'system','Akeneo\\Channel\\Component\\Model\\Locale','1200','a:1:{s:4:\"code\";s:5:\"pt_BR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_BR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2383,'system','Akeneo\\Channel\\Component\\Model\\Locale','1201','a:1:{s:4:\"code\";s:5:\"pt_PT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_PT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2384,'system','Akeneo\\Channel\\Component\\Model\\Locale','1202','a:1:{s:4:\"code\";s:6:\"qut_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"qut_GT\";}}',NULL,1,'2018-06-03 13:05:05',0),(2385,'system','Akeneo\\Channel\\Component\\Model\\Locale','1203','a:1:{s:4:\"code\";s:6:\"quz_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_BO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2386,'system','Akeneo\\Channel\\Component\\Model\\Locale','1204','a:1:{s:4:\"code\";s:6:\"quz_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_EC\";}}',NULL,1,'2018-06-03 13:05:05',0),(2387,'system','Akeneo\\Channel\\Component\\Model\\Locale','1205','a:1:{s:4:\"code\";s:6:\"quz_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_PE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2388,'system','Akeneo\\Channel\\Component\\Model\\Locale','1206','a:1:{s:4:\"code\";s:5:\"rm_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rm_CH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2389,'system','Akeneo\\Channel\\Component\\Model\\Locale','1207','a:1:{s:4:\"code\";s:5:\"ro_RO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ro_RO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2390,'system','Akeneo\\Channel\\Component\\Model\\Locale','1208','a:1:{s:4:\"code\";s:5:\"ru_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ru_RU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2391,'system','Akeneo\\Channel\\Component\\Model\\Locale','1209','a:1:{s:4:\"code\";s:5:\"rw_RW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rw_RW\";}}',NULL,1,'2018-06-03 13:05:05',0),(2392,'system','Akeneo\\Channel\\Component\\Model\\Locale','1210','a:1:{s:4:\"code\";s:6:\"sah_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sah_RU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2393,'system','Akeneo\\Channel\\Component\\Model\\Locale','1211','a:1:{s:4:\"code\";s:5:\"sa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sa_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2394,'system','Akeneo\\Channel\\Component\\Model\\Locale','1212','a:1:{s:4:\"code\";s:5:\"se_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_FI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2395,'system','Akeneo\\Channel\\Component\\Model\\Locale','1213','a:1:{s:4:\"code\";s:5:\"se_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_NO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2396,'system','Akeneo\\Channel\\Component\\Model\\Locale','1214','a:1:{s:4:\"code\";s:5:\"se_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_SE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2397,'system','Akeneo\\Channel\\Component\\Model\\Locale','1215','a:1:{s:4:\"code\";s:5:\"si_LK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"si_LK\";}}',NULL,1,'2018-06-03 13:05:05',0),(2398,'system','Akeneo\\Channel\\Component\\Model\\Locale','1216','a:1:{s:4:\"code\";s:5:\"sk_SK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sk_SK\";}}',NULL,1,'2018-06-03 13:05:05',0),(2399,'system','Akeneo\\Channel\\Component\\Model\\Locale','1217','a:1:{s:4:\"code\";s:5:\"sl_SI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sl_SI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2400,'system','Akeneo\\Channel\\Component\\Model\\Locale','1218','a:1:{s:4:\"code\";s:6:\"sma_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_NO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2401,'system','Akeneo\\Channel\\Component\\Model\\Locale','1219','a:1:{s:4:\"code\";s:6:\"sma_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_SE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2402,'system','Akeneo\\Channel\\Component\\Model\\Locale','1220','a:1:{s:4:\"code\";s:6:\"smj_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_NO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2403,'system','Akeneo\\Channel\\Component\\Model\\Locale','1221','a:1:{s:4:\"code\";s:6:\"smj_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_SE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2404,'system','Akeneo\\Channel\\Component\\Model\\Locale','1222','a:1:{s:4:\"code\";s:6:\"smn_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smn_FI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2405,'system','Akeneo\\Channel\\Component\\Model\\Locale','1223','a:1:{s:4:\"code\";s:6:\"sms_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sms_FI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2406,'system','Akeneo\\Channel\\Component\\Model\\Locale','1224','a:1:{s:4:\"code\";s:5:\"sq_AL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sq_AL\";}}',NULL,1,'2018-06-03 13:05:05',0),(2407,'system','Akeneo\\Channel\\Component\\Model\\Locale','1225','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_BA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2408,'system','Akeneo\\Channel\\Component\\Model\\Locale','1226','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_CS\";}}',NULL,1,'2018-06-03 13:05:05',0),(2409,'system','Akeneo\\Channel\\Component\\Model\\Locale','1227','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_ME\";}}',NULL,1,'2018-06-03 13:05:05',0),(2410,'system','Akeneo\\Channel\\Component\\Model\\Locale','1228','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_RS\";}}',NULL,1,'2018-06-03 13:05:05',0),(2411,'system','Akeneo\\Channel\\Component\\Model\\Locale','1229','a:1:{s:4:\"code\";s:10:\"sr_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_BA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2412,'system','Akeneo\\Channel\\Component\\Model\\Locale','1230','a:1:{s:4:\"code\";s:10:\"sr_Latn_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_CS\";}}',NULL,1,'2018-06-03 13:05:05',0),(2413,'system','Akeneo\\Channel\\Component\\Model\\Locale','1231','a:1:{s:4:\"code\";s:10:\"sr_Latn_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_ME\";}}',NULL,1,'2018-06-03 13:05:05',0),(2414,'system','Akeneo\\Channel\\Component\\Model\\Locale','1232','a:1:{s:4:\"code\";s:10:\"sr_Latn_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_RS\";}}',NULL,1,'2018-06-03 13:05:05',0),(2415,'system','Akeneo\\Channel\\Component\\Model\\Locale','1233','a:1:{s:4:\"code\";s:5:\"sv_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_FI\";}}',NULL,1,'2018-06-03 13:05:05',0),(2416,'system','Akeneo\\Channel\\Component\\Model\\Locale','1234','a:1:{s:4:\"code\";s:5:\"sv_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_SE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2417,'system','Akeneo\\Channel\\Component\\Model\\Locale','1235','a:1:{s:4:\"code\";s:5:\"sw_KE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sw_KE\";}}',NULL,1,'2018-06-03 13:05:05',0),(2418,'system','Akeneo\\Channel\\Component\\Model\\Locale','1236','a:1:{s:4:\"code\";s:6:\"syr_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"syr_SY\";}}',NULL,1,'2018-06-03 13:05:05',0),(2419,'system','Akeneo\\Channel\\Component\\Model\\Locale','1237','a:1:{s:4:\"code\";s:5:\"ta_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ta_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2420,'system','Akeneo\\Channel\\Component\\Model\\Locale','1238','a:1:{s:4:\"code\";s:5:\"te_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"te_IN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2421,'system','Akeneo\\Channel\\Component\\Model\\Locale','1239','a:1:{s:4:\"code\";s:10:\"tg_Cyrl_TJ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"tg_Cyrl_TJ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2422,'system','Akeneo\\Channel\\Component\\Model\\Locale','1240','a:1:{s:4:\"code\";s:5:\"th_TH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"th_TH\";}}',NULL,1,'2018-06-03 13:05:05',0),(2423,'system','Akeneo\\Channel\\Component\\Model\\Locale','1241','a:1:{s:4:\"code\";s:5:\"tk_TM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tk_TM\";}}',NULL,1,'2018-06-03 13:05:05',0),(2424,'system','Akeneo\\Channel\\Component\\Model\\Locale','1242','a:1:{s:4:\"code\";s:5:\"tn_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tn_ZA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2425,'system','Akeneo\\Channel\\Component\\Model\\Locale','1243','a:1:{s:4:\"code\";s:5:\"tr_TR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tr_TR\";}}',NULL,1,'2018-06-03 13:05:05',0),(2426,'system','Akeneo\\Channel\\Component\\Model\\Locale','1244','a:1:{s:4:\"code\";s:5:\"tt_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tt_RU\";}}',NULL,1,'2018-06-03 13:05:05',0),(2427,'system','Akeneo\\Channel\\Component\\Model\\Locale','1245','a:1:{s:4:\"code\";s:11:\"tzm_Latn_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"tzm_Latn_DZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2428,'system','Akeneo\\Channel\\Component\\Model\\Locale','1246','a:1:{s:4:\"code\";s:5:\"ug_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ug_CN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2429,'system','Akeneo\\Channel\\Component\\Model\\Locale','1247','a:1:{s:4:\"code\";s:5:\"uk_UA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"uk_UA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2430,'system','Akeneo\\Channel\\Component\\Model\\Locale','1248','a:1:{s:4:\"code\";s:5:\"ur_PK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ur_PK\";}}',NULL,1,'2018-06-03 13:05:05',0),(2431,'system','Akeneo\\Channel\\Component\\Model\\Locale','1249','a:1:{s:4:\"code\";s:10:\"uz_Cyrl_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Cyrl_UZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2432,'system','Akeneo\\Channel\\Component\\Model\\Locale','1250','a:1:{s:4:\"code\";s:10:\"uz_Latn_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Latn_UZ\";}}',NULL,1,'2018-06-03 13:05:05',0),(2433,'system','Akeneo\\Channel\\Component\\Model\\Locale','1251','a:1:{s:4:\"code\";s:5:\"vi_VN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"vi_VN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2434,'system','Akeneo\\Channel\\Component\\Model\\Locale','1252','a:1:{s:4:\"code\";s:5:\"wo_SN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"wo_SN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2435,'system','Akeneo\\Channel\\Component\\Model\\Locale','1253','a:1:{s:4:\"code\";s:5:\"xh_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"xh_ZA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2436,'system','Akeneo\\Channel\\Component\\Model\\Locale','1254','a:1:{s:4:\"code\";s:5:\"yo_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"yo_NG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2437,'system','Akeneo\\Channel\\Component\\Model\\Locale','1255','a:1:{s:4:\"code\";s:5:\"zh_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_CN\";}}',NULL,1,'2018-06-03 13:05:05',0),(2438,'system','Akeneo\\Channel\\Component\\Model\\Locale','1256','a:1:{s:4:\"code\";s:5:\"zh_HK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_HK\";}}',NULL,1,'2018-06-03 13:05:05',0),(2439,'system','Akeneo\\Channel\\Component\\Model\\Locale','1257','a:1:{s:4:\"code\";s:5:\"zh_MO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_MO\";}}',NULL,1,'2018-06-03 13:05:05',0),(2440,'system','Akeneo\\Channel\\Component\\Model\\Locale','1258','a:1:{s:4:\"code\";s:5:\"zh_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_SG\";}}',NULL,1,'2018-06-03 13:05:05',0),(2441,'system','Akeneo\\Channel\\Component\\Model\\Locale','1259','a:1:{s:4:\"code\";s:5:\"zh_TW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_TW\";}}',NULL,1,'2018-06-03 13:05:05',0),(2442,'system','Akeneo\\Channel\\Component\\Model\\Locale','1260','a:1:{s:4:\"code\";s:5:\"zu_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zu_ZA\";}}',NULL,1,'2018-06-03 13:05:05',0),(2443,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','68','a:3:{s:4:\"code\";s:15:\"2014_collection\";s:6:\"parent\";N;s:11:\"label-en_US\";s:15:\"2014 collection\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014 collection\";}}',NULL,1,'2018-06-03 13:05:06',0),(2444,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','69','a:3:{s:4:\"code\";s:17:\"summer_collection\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:17:\"Summer collection\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"summer_collection\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Summer collection\";}}',NULL,1,'2018-06-03 13:05:06',0),(2445,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','70','a:3:{s:4:\"code\";s:17:\"winter_collection\";s:6:\"parent\";s:15:\"2014_collection\";s:11:\"label-en_US\";s:17:\"Winter collection\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"winter_collection\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"Winter collection\";}}',NULL,1,'2018-06-03 13:05:06',0),(2446,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','71','a:3:{s:4:\"code\";s:12:\"winter_boots\";s:6:\"parent\";s:17:\"winter_collection\";s:11:\"label-en_US\";s:12:\"Winter boots\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"winter_boots\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"winter_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Winter boots\";}}',NULL,1,'2018-06-03 13:05:06',0),(2447,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','72','a:3:{s:4:\"code\";s:7:\"sandals\";s:6:\"parent\";s:17:\"summer_collection\";s:11:\"label-en_US\";s:7:\"Sandals\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"sandals\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"summer_collection\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}}',NULL,1,'2018-06-03 13:05:06',0),(2448,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','21','a:4:{s:4:\"code\";s:4:\"info\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:19:\"Product information\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Product information\";}}',NULL,1,'2018-06-03 13:05:06',0),(2449,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','22','a:4:{s:4:\"code\";s:9:\"marketing\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:9:\"Marketing\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Marketing\";}}',NULL,1,'2018-06-03 13:05:06',0),(2450,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','23','a:4:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Sizes\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Sizes\";}}',NULL,1,'2018-06-03 13:05:06',0),(2451,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','24','a:4:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:6:\"Colors\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Colors\";}}',NULL,1,'2018-06-03 13:05:06',0),(2452,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','25','a:4:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Media\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Media\";}}',NULL,1,'2018-06-03 13:05:06',0),(2453,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','26','a:4:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Other\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:100;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}}',NULL,1,'2018-06-03 13:05:06',0),(2454,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','84','a:29:{s:4:\"code\";s:3:\"sku\";s:4:\"type\";s:22:\"pim_catalog_identifier\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:3:\"SKU\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:1;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"pim_catalog_identifier\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:6:\"unique\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}s:8:\"required\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}}',NULL,1,'2018-06-03 13:05:06',0),(2455,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','21','a:4:{s:4:\"code\";s:4:\"info\";s:10:\"sort_order\";i:1;s:10:\"attributes\";s:73:\"sku,name,manufacturer,weather_conditions,description,length,volume,weight\";s:11:\"label-en_US\";s:19:\"Product information\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:73:\"sku,name,manufacturer,weather_conditions,description,length,volume,weight\";}}',NULL,2,'2018-06-03 13:05:06',0),(2456,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','85','a:29:{s:4:\"code\";s:4:\"name\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Name\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Name\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2457,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','86','a:29:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";b:1;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"manufacturer\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:3;}s:19:\"auto_option_sorting\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Manufacturer\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2458,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','87','a:29:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"weather_conditions\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:4;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Weather conditions\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2459,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','88','a:29:{s:4:\"code\";s:11:\"description\";s:4:\"type\";s:20:\"pim_catalog_textarea\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:1000;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:5;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Description\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"description\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1000;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:5;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Description\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:06',0),(2460,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','89','a:29:{s:4:\"code\";s:7:\"comment\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:255;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:7;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:7:\"Comment\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"comment\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:255;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:7;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Comment\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2461,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','26','a:4:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:108:\"comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123\";s:11:\"label-en_US\";s:5:\"Other\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:108:\"comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123\";}}',NULL,2,'2018-06-03 13:05:06',0),(2462,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','90','a:29:{s:4:\"code\";s:5:\"price\";s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";s:6:\"1.0000\";s:10:\"number_max\";s:8:\"200.0000\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Price\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"price\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"number_min\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"number_max\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"200.0000\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Price\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2463,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','22','a:4:{s:4:\"code\";s:9:\"marketing\";s:10:\"sort_order\";i:2;s:10:\"attributes\";s:22:\"price,rating,rate_sale\";s:11:\"label-en_US\";s:9:\"Marketing\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"price,rating,rate_sale\";}}',NULL,2,'2018-06-03 13:05:06',0),(2464,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','91','a:29:{s:4:\"code\";s:6:\"rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Rating\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"rating\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Rating\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2465,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','92','a:29:{s:4:\"code\";s:9:\"side_view\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Side view\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"side_view\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Side view\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2466,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','25','a:4:{s:4:\"code\";s:5:\"media\";s:10:\"sort_order\";i:5;s:10:\"attributes\";s:28:\"side_view,top_view,rear_view\";s:11:\"label-en_US\";s:5:\"Media\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"side_view,top_view,rear_view\";}}',NULL,2,'2018-06-03 13:05:06',0),(2467,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','93','a:29:{s:4:\"code\";s:8:\"top_view\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Top view\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"top_view\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Top view\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2468,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','94','a:29:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"size\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sizes\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Size\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2469,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','23','a:4:{s:4:\"code\";s:5:\"sizes\";s:10:\"sort_order\";i:3;s:10:\"attributes\";s:4:\"size\";s:11:\"label-en_US\";s:5:\"Sizes\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"size\";}}',NULL,2,'2018-06-03 13:05:06',0),(2470,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','95','a:29:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2471,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','24','a:4:{s:4:\"code\";s:6:\"colors\";s:10:\"sort_order\";i:4;s:10:\"attributes\";s:16:\"color,lace_color\";s:11:\"label-en_US\";s:6:\"Colors\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"color,lace_color\";}}',NULL,2,'2018-06-03 13:05:06',0),(2472,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','96','a:29:{s:4:\"code\";s:10:\"lace_color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Lace color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"lace_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"colors\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Lace color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2473,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','97','a:30:{s:4:\"code\";s:6:\"length\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Length\";s:19:\"default_metric_unit\";s:10:\"CENTIMETER\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:10;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Length\";s:11:\"label-fr_FR\";s:8:\"Longueur\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"length\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Length\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"CENTIMETER\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:10;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Length\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Longueur\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2474,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','98','a:30:{s:4:\"code\";s:6:\"volume\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Volume\";s:19:\"default_metric_unit\";s:16:\"CUBIC_MILLIMETER\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:20;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Volume\";s:11:\"label-fr_FR\";s:6:\"Volume\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"volume\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Volume\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"CUBIC_MILLIMETER\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:20;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Volume\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Volume\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2475,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','99','a:29:{s:4:\"code\";s:15:\"number_in_stock\";s:4:\"type\";s:18:\"pim_catalog_number\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:8;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Number in stock\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"number_in_stock\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:8;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Number in stock\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2476,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','100','a:30:{s:4:\"code\";s:15:\"destocking_date\";s:4:\"type\";s:16:\"pim_catalog_date\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:25;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:15:\"Destocking date\";s:11:\"label-fr_FR\";s:19:\"Date de déstockage\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"destocking_date\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_date\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:25;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"Destocking date\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Date de déstockage\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2477,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','101','a:29:{s:4:\"code\";s:8:\"handmade\";s:4:\"type\";s:19:\"pim_catalog_boolean\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:30;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:8:\"Handmade\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"handmade\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:30;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Handmade\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2478,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','102','a:29:{s:4:\"code\";s:10:\"heel_color\";s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:30;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Heel color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"heel_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:30;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Heel color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2479,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','103','a:29:{s:4:\"code\";s:10:\"sole_color\";s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:35;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Sole color\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sole_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:35;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Sole color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2480,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','104','a:29:{s:4:\"code\";s:9:\"cap_color\";s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:35;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Cap color\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"cap_color\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:35;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Cap color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:06',0),(2481,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','105','a:29:{s:4:\"code\";s:11:\"sole_fabric\";s:4:\"type\";s:30:\"pim_reference_data_multiselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:40;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Sole fabric\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"sole_fabric\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"pim_reference_data_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"fabrics\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:40;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Sole fabric\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2482,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','106','a:29:{s:4:\"code\";s:11:\"lace_fabric\";s:4:\"type\";s:30:\"pim_reference_data_multiselect\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";s:7:\"fabrics\";s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:45;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:11:\"Lace fabric\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"lace_fabric\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"pim_reference_data_multiselect\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"fabrics\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:45;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"Lace fabric\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:06',0),(2483,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','107','a:30:{s:4:\"code\";s:9:\"rate_sale\";s:4:\"type\";s:18:\"pim_catalog_number\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:12:\"Rate of sale\";s:11:\"label-fr_FR\";s:13:\"Taux de vente\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"rate_sale\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"marketing\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Rate of sale\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Taux de vente\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2484,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','108','a:30:{s:4:\"code\";s:6:\"weight\";s:4:\"type\";s:18:\"pim_catalog_metric\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:6:\"Weight\";s:19:\"default_metric_unit\";s:4:\"GRAM\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:30;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Weight\";s:11:\"label-fr_FR\";s:5:\"Poids\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:11:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"weight\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"info\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"GRAM\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:30;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Weight\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Poids\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2485,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','109','a:29:{s:4:\"code\";s:3:\"123\";s:4:\"type\";s:16:\"pim_catalog_text\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";i:255;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:13:\"Attribute 123\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:8:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"123\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:14:\"max_characters\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:255;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Attribute 123\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2018-06-03 13:05:06',0),(2486,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','110','a:29:{s:4:\"code\";s:9:\"rear_view\";s:4:\"type\";s:17:\"pim_catalog_image\";s:5:\"group\";s:5:\"media\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:16:\"gif,png,jpeg,jpg\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";s:6:\"1.0000\";s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:1;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:9:\"Rear view\";s:7:\"options\";N;s:5:\"scope\";s:7:\"Channel\";s:8:\"required\";b:0;}','a:9:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"rear_view\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"media\";}s:18:\"allowed_extensions\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"gif,png,jpeg,jpg\";}s:13:\"max_file_size\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"1.0000\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:2;}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Rear view\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2018-06-03 13:05:06',0),(2487,'system','Akeneo\\Channel\\Component\\Model\\Channel','12','a:7:{s:4:\"code\";s:6:\"tablet\";s:10:\"currencies\";s:7:\"EUR,USD\";s:7:\"locales\";s:5:\"en_US\";s:11:\"label-en_US\";s:6:\"Tablet\";s:11:\"label-de_DE\";s:6:\"Tablet\";s:11:\"label-fr_FR\";s:8:\"Tablette\";s:8:\"category\";s:15:\"2014_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"tablet\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"EUR,USD\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Tablette\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}}',NULL,1,'2018-06-03 13:05:07',0),(2488,'system','Akeneo\\Channel\\Component\\Model\\Channel','13','a:7:{s:4:\"code\";s:6:\"mobile\";s:10:\"currencies\";s:3:\"EUR\";s:7:\"locales\";s:11:\"en_US,fr_FR\";s:11:\"label-en_US\";s:6:\"Mobile\";s:11:\"label-de_DE\";s:5:\"Mobil\";s:11:\"label-fr_FR\";s:6:\"Mobile\";s:8:\"category\";s:15:\"2014_collection\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"mobile\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,fr_FR\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Mobile\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Mobil\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Mobile\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"2014_collection\";}}',NULL,1,'2018-06-03 13:05:07',0),(2489,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','21','a:2:{s:4:\"code\";s:6:\"X_SELL\";s:11:\"label-en_US\";s:10:\"Cross sell\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"X_SELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}}',NULL,1,'2018-06-03 13:05:07',0),(2490,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','22','a:2:{s:4:\"code\";s:6:\"UPSELL\";s:11:\"label-en_US\";s:6:\"Upsell\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"UPSELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}}',NULL,1,'2018-06-03 13:05:07',0),(2491,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','23','a:2:{s:4:\"code\";s:12:\"SUBSTITUTION\";s:11:\"label-en_US\";s:12:\"Substitution\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"SUBSTITUTION\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Substitution\";}}',NULL,1,'2018-06-03 13:05:07',0),(2492,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','24','a:2:{s:4:\"code\";s:4:\"PACK\";s:11:\"label-en_US\";s:4:\"Pack\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"PACK\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}}',NULL,1,'2018-06-03 13:05:07',0),(2493,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','86','a:29:{s:4:\"code\";s:12:\"manufacturer\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:3;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";b:1;s:11:\"label-en_US\";s:12:\"Manufacturer\";s:7:\"options\";s:117:\"Code:Converse,en_US:Converse|Code:TimberLand,en_US:TimberLand|Code:Nike,en_US:Nike|Code:Caterpillar,en_US:Caterpillar\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:117:\"Code:Converse,en_US:Converse|Code:TimberLand,en_US:TimberLand|Code:Nike,en_US:Nike|Code:Caterpillar,en_US:Caterpillar\";}}',NULL,2,'2018-06-03 13:05:07',0),(2494,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','87','a:29:{s:4:\"code\";s:18:\"weather_conditions\";s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:5:\"group\";s:4:\"info\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:4;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:18:\"Weather conditions\";s:7:\"options\";s:100:\"Code:dry,en_US:Dry|Code:wet,en_US:Wet|Code:hot,en_US:Hot|Code:cold,en_US:Cold|Code:snowy,en_US:Snowy\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:100:\"Code:dry,en_US:Dry|Code:wet,en_US:Wet|Code:hot,en_US:Hot|Code:cold,en_US:Cold|Code:snowy,en_US:Snowy\";}}',NULL,2,'2018-06-03 13:05:07',0),(2495,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','91','a:29:{s:4:\"code\";s:6:\"rating\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:9:\"marketing\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:6:\"Rating\";s:7:\"options\";s:103:\"Code:1,en_US:1 star|Code:2,en_US:2 stars|Code:3,en_US:3 stars|Code:4,en_US:4 stars|Code:5,en_US:5 stars\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:103:\"Code:1,en_US:1 star|Code:2,en_US:2 stars|Code:3,en_US:3 stars|Code:4,en_US:4 stars|Code:5,en_US:5 stars\";}}',NULL,2,'2018-06-03 13:05:07',0),(2496,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','94','a:29:{s:4:\"code\";s:4:\"size\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:5:\"sizes\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:4:\"Size\";s:7:\"options\";s:220:\"Code:35,en_US:35|Code:36,en_US:36|Code:37,en_US:37|Code:38,en_US:38|Code:39,en_US:39|Code:40,en_US:40|Code:41,en_US:41|Code:42,en_US:42|Code:43,en_US:43|Code:44,en_US:44|Code:45,en_US:45|Code:46,en_US:46|Code:60,en_US:60\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:220:\"Code:35,en_US:35|Code:36,en_US:36|Code:37,en_US:37|Code:38,en_US:38|Code:39,en_US:39|Code:40,en_US:40|Code:41,en_US:41|Code:42,en_US:42|Code:43,en_US:43|Code:44,en_US:44|Code:45,en_US:45|Code:46,en_US:46|Code:60,en_US:60\";}}',NULL,2,'2018-06-03 13:05:07',0),(2497,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','95','a:29:{s:4:\"code\";s:5:\"color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:5:\"Color\";s:7:\"options\";s:187:\"Code:white,en_US:White|Code:black,en_US:Black|Code:blue,en_US:Blue|Code:maroon,en_US:Maroon|Code:saddle,en_US:Saddle|Code:greem,en_US:Greem|Code:red,en_US:Red|Code:charcoal,en_US:Charcoal\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:187:\"Code:white,en_US:White|Code:black,en_US:Black|Code:blue,en_US:Blue|Code:maroon,en_US:Maroon|Code:saddle,en_US:Saddle|Code:greem,en_US:Greem|Code:red,en_US:Red|Code:charcoal,en_US:Charcoal\";}}',NULL,2,'2018-06-03 13:05:07',0),(2498,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','96','a:29:{s:4:\"code\";s:10:\"lace_color\";s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:5:\"group\";s:6:\"colors\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:2;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:10:\"Lace color\";s:7:\"options\";s:86:\"Code:laces_black,en_US:Black|Code:laces_brown,en_US:Brown|Code:laces_white,en_US:White\";s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:0;}','a:1:{s:7:\"options\";a:2:{s:3:\"old\";N;s:3:\"new\";s:86:\"Code:laces_black,en_US:Black|Code:laces_brown,en_US:Brown|Code:laces_white,en_US:White\";}}',NULL,2,'2018-06-03 13:05:07',0),(2499,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','12','a:7:{s:4:\"code\";s:5:\"boots\";s:10:\"attributes\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";s:11:\"label-en_US\";s:5:\"Boots\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"boots\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Boots\";}}',NULL,1,'2018-06-03 13:05:08',0),(2500,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','13','a:7:{s:4:\"code\";s:5:\"heels\";s:10:\"attributes\";s:103:\"color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:47:\"color,heel_color,name,price,size,sku,sole_color\";s:19:\"requirements-tablet\";s:69:\"color,description,heel_color,name,price,side_view,size,sku,sole_color\";s:11:\"label-en_US\";s:5:\"Heels\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"heels\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:103:\"color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:47:\"color,heel_color,name,price,size,sku,sole_color\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:69:\"color,description,heel_color,name,price,side_view,size,sku,sole_color\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Heels\";}}',NULL,1,'2018-06-03 13:05:08',0),(2501,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','14','a:7:{s:4:\"code\";s:8:\"sneakers\";s:10:\"attributes\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";s:11:\"label-en_US\";s:8:\"Sneakers\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"sneakers\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:106:\"color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:73:\"color,description,name,price,rating,side_view,size,sku,weather_conditions\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"Sneakers\";}}',NULL,1,'2018-06-03 13:05:08',0),(2502,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','15','a:7:{s:4:\"code\";s:7:\"sandals\";s:10:\"attributes\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";s:9:\"side_view\";s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:54:\"color,description,name,price,rating,side_view,size,sku\";s:11:\"label-en_US\";s:7:\"Sandals\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"sandals\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:18:\"attribute_as_image\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"side_view\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:54:\"color,description,name,price,rating,side_view,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Sandals\";}}',NULL,1,'2018-06-03 13:05:08',0),(2503,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Family','16','a:7:{s:4:\"code\";s:7:\"led_tvs\";s:10:\"attributes\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";s:18:\"attribute_as_label\";s:4:\"name\";s:18:\"attribute_as_image\";N;s:19:\"requirements-mobile\";s:25:\"color,name,price,size,sku\";s:19:\"requirements-tablet\";s:54:\"color,description,name,price,rating,side_view,size,sku\";s:11:\"label-en_US\";s:7:\"LED TVs\";}','a:6:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"led_tvs\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:67:\"color,description,manufacturer,name,price,rating,side_view,size,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"name\";}s:19:\"requirements-mobile\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"color,name,price,size,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:54:\"color,description,name,price,rating,side_view,size,sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"LED TVs\";}}',NULL,1,'2018-06-03 13:05:08',0),(2504,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','286','a:5:{s:4:\"code\";s:27:\"csv_footwear_product_import\";s:5:\"label\";s:27:\"CSV footwear product import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:410:\"{\"filePath\":\"\\/tmp\\/footwear_products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:410:\"{\"filePath\":\"\\/tmp\\/footwear_products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2505,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','287','a:5:{s:4:\"code\";s:27:\"csv_footwear_product_export\";s:5:\"label\";s:27:\"CSV footwear product export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:453:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]}],\"structure\":{\"scope\":\"mobile\",\"locales\":[\"en_US\"]}}}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_product_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear product export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:453:\"{\"filePath\":\"\\/tmp\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"with_media\":true,\"filters\":{\"data\":[{\"field\":\"enabled\",\"operator\":\"=\",\"value\":true},{\"field\":\"completeness\",\"operator\":\">=\",\"value\":100},{\"field\":\"categories\",\"operator\":\"IN CHILDREN\",\"value\":[\"2014_collection\"]}],\"structure\":{\"scope\":\"mobile\",\"locales\":[\"en_US\"]}}}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2506,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','288','a:5:{s:4:\"code\";s:28:\"csv_footwear_category_import\";s:5:\"label\";s:28:\"CSV footwear category import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_category_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear category import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2507,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','289','a:5:{s:4:\"code\";s:28:\"csv_footwear_category_export\";s:5:\"label\";s:28:\"CSV footwear category export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_category_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear category export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:137:\"{\"filePath\":\"\\/tmp\\/category.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2508,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','290','a:5:{s:4:\"code\";s:36:\"csv_footwear_association_type_import\";s:5:\"label\";s:36:\"CSV footwear association type import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"csv_footwear_association_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"CSV footwear association type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2509,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','291','a:5:{s:4:\"code\";s:36:\"csv_footwear_association_type_export\";s:5:\"label\";s:36:\"CSV footwear association type export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"csv_footwear_association_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"CSV footwear association type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:145:\"{\"filePath\":\"\\/tmp\\/association_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2510,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','292','a:5:{s:4:\"code\";s:25:\"csv_footwear_group_import\";s:5:\"label\";s:25:\"CSV footwear group import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"csv_footwear_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"CSV footwear group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2511,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','293','a:5:{s:4:\"code\";s:26:\"xlsx_footwear_group_import\";s:5:\"label\";s:26:\"XLSX footwear group import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:158:\"{\"filePath\":\"\\/tmp\\/group.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"xlsx_footwear_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"XLSX footwear group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:158:\"{\"filePath\":\"\\/tmp\\/group.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2512,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','294','a:5:{s:4:\"code\";s:25:\"csv_footwear_group_export\";s:5:\"label\";s:25:\"CSV footwear group export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"csv_footwear_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"CSV footwear group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:134:\"{\"filePath\":\"\\/tmp\\/group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2513,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','295','a:5:{s:4:\"code\";s:29:\"csv_footwear_attribute_import\";s:5:\"label\";s:29:\"CSV footwear attribute import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"csv_footwear_attribute_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"CSV footwear attribute import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2514,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','296','a:5:{s:4:\"code\";s:29:\"csv_footwear_attribute_export\";s:5:\"label\";s:29:\"CSV footwear attribute export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"csv_footwear_attribute_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"CSV footwear attribute export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:138:\"{\"filePath\":\"\\/tmp\\/attribute.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2515,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','297','a:5:{s:4:\"code\";s:26:\"csv_footwear_option_import\";s:5:\"label\";s:26:\"CSV footwear option import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_option_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear option import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2516,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','298','a:5:{s:4:\"code\";s:26:\"csv_footwear_option_export\";s:5:\"label\";s:26:\"CSV footwear option export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_option_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear option export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/option.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2517,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','299','a:5:{s:4:\"code\";s:26:\"csv_footwear_family_export\";s:5:\"label\";s:26:\"CSV footwear family export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_family_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear family export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/family.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2518,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','300','a:5:{s:4:\"code\";s:27:\"csv_footwear_channel_export\";s:5:\"label\";s:27:\"CSV footwear channel export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:136:\"{\"filePath\":\"\\/tmp\\/channel.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_channel_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear channel export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:136:\"{\"filePath\":\"\\/tmp\\/channel.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2519,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','301','a:5:{s:4:\"code\";s:28:\"csv_footwear_currency_export\";s:5:\"label\";s:28:\"CSV footwear currency export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:137:\"{\"filePath\":\"\\/tmp\\/currency.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_currency_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear currency export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:137:\"{\"filePath\":\"\\/tmp\\/currency.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2520,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','302','a:5:{s:4:\"code\";s:26:\"csv_footwear_locale_export\";s:5:\"label\";s:26:\"CSV footwear locale export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:135:\"{\"filePath\":\"\\/tmp\\/locale.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_locale_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear locale export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:135:\"{\"filePath\":\"\\/tmp\\/locale.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2521,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','303','a:5:{s:4:\"code\";s:30:\"csv_footwear_group_type_export\";s:5:\"label\";s:30:\"CSV footwear group type export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:139:\"{\"filePath\":\"\\/tmp\\/group_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"csv_footwear_group_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"CSV footwear group type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:139:\"{\"filePath\":\"\\/tmp\\/group_type.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2522,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','304','a:5:{s:4:\"code\";s:35:\"csv_footwear_attribute_group_export\";s:5:\"label\";s:35:\"CSV footwear attribute group export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:144:\"{\"filePath\":\"\\/tmp\\/attribute_group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"csv_footwear_attribute_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"CSV footwear attribute group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:144:\"{\"filePath\":\"\\/tmp\\/attribute_group.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2523,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','305','a:5:{s:4:\"code\";s:26:\"csv_footwear_family_import\";s:5:\"label\";s:26:\"CSV footwear family import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_family_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear family import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2524,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','306','a:5:{s:4:\"code\";s:28:\"csv_footwear_currency_import\";s:5:\"label\";s:28:\"CSV footwear currency import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"csv_footwear_currency_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"CSV footwear currency import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2525,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','307','a:5:{s:4:\"code\";s:27:\"csv_footwear_channel_import\";s:5:\"label\";s:27:\"CSV footwear channel import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"csv_footwear_channel_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"CSV footwear channel import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2526,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','308','a:5:{s:4:\"code\";s:26:\"csv_footwear_locale_import\";s:5:\"label\";s:26:\"CSV footwear locale import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_footwear_locale_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV footwear locale import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2527,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','309','a:5:{s:4:\"code\";s:30:\"csv_footwear_group_type_import\";s:5:\"label\";s:30:\"CSV footwear group_type import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"csv_footwear_group_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"CSV footwear group_type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2528,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','310','a:5:{s:4:\"code\";s:35:\"csv_footwear_attribute_group_import\";s:5:\"label\";s:35:\"CSV footwear attribute_group import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"csv_footwear_attribute_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"CSV footwear attribute_group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:189:\"{\"filePath\":null,\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2529,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','311','a:5:{s:4:\"code\";s:20:\"update_product_value\";s:5:\"label\";s:20:\"Mass update products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"update_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass update products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2530,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','312','a:5:{s:4:\"code\";s:17:\"add_product_value\";s:5:\"label\";s:24:\"Mass add products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"add_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2531,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','313','a:5:{s:4:\"code\";s:20:\"remove_product_value\";s:5:\"label\";s:27:\"Mass remove products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2532,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','314','a:5:{s:4:\"code\";s:16:\"move_to_category\";s:5:\"label\";s:23:\"Mass move to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"move_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass move to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2533,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','315','a:5:{s:4:\"code\";s:15:\"add_association\";s:5:\"label\";s:23:\"Mass associate products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_association\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass associate products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2534,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','316','a:5:{s:4:\"code\";s:15:\"add_to_category\";s:5:\"label\";s:22:\"Mass add to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Mass add to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2535,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','317','a:5:{s:4:\"code\";s:12:\"add_to_group\";s:5:\"label\";s:25:\"Mass add product to group\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"add_to_group\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Mass add product to group\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2536,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','318','a:5:{s:4:\"code\";s:20:\"remove_from_category\";s:5:\"label\";s:27:\"Mass remove from categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_from_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove from categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2537,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','319','a:5:{s:4:\"code\";s:22:\"edit_common_attributes\";s:5:\"label\";s:28:\"Mass edit product attributes\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"edit_common_attributes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Mass edit product attributes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2538,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','320','a:5:{s:4:\"code\";s:19:\"add_attribute_value\";s:5:\"label\";s:24:\"Mass add attribute value\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"add_attribute_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add attribute value\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2539,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','321','a:5:{s:4:\"code\";s:26:\"set_attribute_requirements\";s:5:\"label\";s:33:\"Set family attribute requirements\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"set_attribute_requirements\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Set family attribute requirements\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2540,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','322','a:5:{s:4:\"code\";s:29:\"add_to_existing_product_model\";s:5:\"label\";s:29:\"Add to existing product model\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"add_to_existing_product_model\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Add to existing product model\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2541,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','323','a:5:{s:4:\"code\";s:34:\"delete_products_and_product_models\";s:5:\"label\";s:20:\"Mass delete products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:11:\"mass_delete\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"delete_products_and_product_models\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass delete products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"mass_delete\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2542,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','324','a:5:{s:4:\"code\";s:24:\"csv_product_quick_export\";s:5:\"label\";s:24:\"CSV product quick export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:410:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"csv_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"CSV product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:410:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.csv\"}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2543,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','325','a:5:{s:4:\"code\";s:31:\"xlsx_footwear_product_import_fr\";s:5:\"label\";s:31:\"XLSX footwear product import FR\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:368:\"{\"filePath\":\"\\/tmp\\/footwear_products-fr.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\",\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"xlsx_footwear_product_import_fr\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"XLSX footwear product import FR\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:368:\"{\"filePath\":\"\\/tmp\\/footwear_products-fr.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\",\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2544,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','326','a:5:{s:4:\"code\";s:28:\"xlsx_footwear_product_import\";s:5:\"label\";s:28:\"XLSX footwear product import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:365:\"{\"filePath\":\"\\/tmp\\/footwear_products.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"xlsx_footwear_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"XLSX footwear product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:365:\"{\"filePath\":\"\\/tmp\\/footwear_products.xlsx\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2545,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','327','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_category_import\";s:5:\"label\";s:29:\"XLSX footwear category import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_category_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear category import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2546,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','328','a:5:{s:4:\"code\";s:37:\"xlsx_footwear_association_type_import\";s:5:\"label\";s:37:\"XLSX footwear association type import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"xlsx_footwear_association_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"XLSX footwear association type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2547,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','329','a:5:{s:4:\"code\";s:30:\"xlsx_footwear_attribute_import\";s:5:\"label\";s:30:\"XLSX footwear attribute import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"xlsx_footwear_attribute_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"XLSX footwear attribute import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2548,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','330','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_option_import\";s:5:\"label\";s:27:\"XLSX footwear option import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_option_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear option import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2549,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','331','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_family_import\";s:5:\"label\";s:27:\"XLSX footwear family import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_family_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear family import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2550,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','332','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_currency_import\";s:5:\"label\";s:29:\"XLSX footwear currency import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_currency_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear currency import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2551,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','333','a:5:{s:4:\"code\";s:28:\"xlsx_footwear_channel_import\";s:5:\"label\";s:28:\"XLSX footwear channel import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"xlsx_footwear_channel_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"XLSX footwear channel import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2552,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','334','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_locale_import\";s:5:\"label\";s:27:\"XLSX footwear locale import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_locale_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear locale import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2553,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','335','a:5:{s:4:\"code\";s:31:\"xlsx_footwear_group_type_import\";s:5:\"label\";s:31:\"XLSX footwear group_type import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"xlsx_footwear_group_type_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"XLSX footwear group_type import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2554,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','336','a:5:{s:4:\"code\";s:36:\"xlsx_footwear_attribute_group_import\";s:5:\"label\";s:36:\"XLSX footwear attribute_group import\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"xlsx_footwear_attribute_group_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"XLSX footwear attribute_group import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:143:\"{\"filePath\":null,\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"xlsx\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2555,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','337','a:5:{s:4:\"code\";s:25:\"xlsx_product_quick_export\";s:5:\"label\";s:25:\"XLSX product quick export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:401:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.xlsx\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"xlsx_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"XLSX product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:401:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.xlsx\"}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2556,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','338','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_family_export\";s:5:\"label\";s:27:\"XLSX footwear family export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:124:\"{\"filePath\":\"\\/tmp\\/family.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_family_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear family export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:124:\"{\"filePath\":\"\\/tmp\\/family.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2557,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','339','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_category_export\";s:5:\"label\";s:29:\"XLSX footwear category export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:126:\"{\"filePath\":\"\\/tmp\\/category.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_category_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear category export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:126:\"{\"filePath\":\"\\/tmp\\/category.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2558,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','340','a:5:{s:4:\"code\";s:30:\"xlsx_footwear_attribute_export\";s:5:\"label\";s:30:\"XLSX footwear attribute export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:127:\"{\"filePath\":\"\\/tmp\\/attribute.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"xlsx_footwear_attribute_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"XLSX footwear attribute export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:127:\"{\"filePath\":\"\\/tmp\\/attribute.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2559,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','341','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_option_export\";s:5:\"label\";s:27:\"XLSX footwear option export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:124:\"{\"filePath\":\"\\/tmp\\/option.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_option_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear option export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:124:\"{\"filePath\":\"\\/tmp\\/option.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2560,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','342','a:5:{s:4:\"code\";s:37:\"xlsx_footwear_association_type_export\";s:5:\"label\";s:37:\"XLSX footwear association type export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:134:\"{\"filePath\":\"\\/tmp\\/association_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"xlsx_footwear_association_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"XLSX footwear association type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:134:\"{\"filePath\":\"\\/tmp\\/association_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2561,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','343','a:5:{s:4:\"code\";s:28:\"xlsx_footwear_channel_export\";s:5:\"label\";s:28:\"XLSX footwear channel export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:125:\"{\"filePath\":\"\\/tmp\\/channel.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"xlsx_footwear_channel_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"XLSX footwear channel export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:125:\"{\"filePath\":\"\\/tmp\\/channel.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2562,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','344','a:5:{s:4:\"code\";s:29:\"xlsx_footwear_currency_export\";s:5:\"label\";s:29:\"XLSX footwear currency export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:126:\"{\"filePath\":\"\\/tmp\\/currency.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"xlsx_footwear_currency_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"XLSX footwear currency export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:126:\"{\"filePath\":\"\\/tmp\\/currency.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2563,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','345','a:5:{s:4:\"code\";s:27:\"xlsx_footwear_locale_export\";s:5:\"label\";s:27:\"XLSX footwear locale export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:124:\"{\"filePath\":\"\\/tmp\\/locale.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"xlsx_footwear_locale_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"XLSX footwear locale export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:124:\"{\"filePath\":\"\\/tmp\\/locale.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2564,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','346','a:5:{s:4:\"code\";s:31:\"xlsx_footwear_group_type_export\";s:5:\"label\";s:31:\"XLSX footwear group type export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:128:\"{\"filePath\":\"\\/tmp\\/group_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"xlsx_footwear_group_type_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"XLSX footwear group type export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:128:\"{\"filePath\":\"\\/tmp\\/group_type.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2565,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','347','a:5:{s:4:\"code\";s:36:\"xlsx_footwear_attribute_group_export\";s:5:\"label\";s:36:\"XLSX footwear attribute group export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:6:\"export\";s:13:\"configuration\";s:133:\"{\"filePath\":\"\\/tmp\\/attribute_group.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"xlsx_footwear_attribute_group_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:36:\"XLSX footwear attribute group export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:133:\"{\"filePath\":\"\\/tmp\\/attribute_group.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2566,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','348','a:5:{s:4:\"code\";s:37:\"csv_product_grid_context_quick_export\";s:5:\"label\";s:37:\"CSV product quick export grid context\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:436:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"csv_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"CSV product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:436:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.csv\"}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2567,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','349','a:5:{s:4:\"code\";s:38:\"xlsx_product_grid_context_quick_export\";s:5:\"label\";s:38:\"XLSX product quick export grid context\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:427:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.xlsx\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"xlsx_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"XLSX product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:427:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.xlsx\"}\";}}',NULL,1,'2018-06-03 13:05:08',0),(2568,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','350','a:5:{s:4:\"code\";s:34:\"compute_product_models_descendants\";s:5:\"label\";s:34:\"Compute product models descendants\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:34:\"compute_product_models_descendants\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"Compute product models descendants\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2569,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','351','a:5:{s:4:\"code\";s:39:\"compute_completeness_of_products_family\";s:5:\"label\";s:39:\"compute completeness of products family\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:39:\"compute_completeness_of_products_family\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute completeness of products family\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2570,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','352','a:5:{s:4:\"code\";s:40:\"compute_family_variant_structure_changes\";s:5:\"label\";s:33:\"Compute variant structure changes\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:40:\"compute_family_variant_structure_changes\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Compute variant structure changes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-06-03 13:05:08',0),(2571,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Group','4','a:3:{s:4:\"code\";s:13:\"similar_boots\";s:4:\"type\";s:7:\"RELATED\";s:11:\"label-en_US\";s:13:\"Similar boots\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"similar_boots\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"RELATED\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Similar boots\";}}',NULL,1,'2018-06-03 13:05:08',0);
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

-- Dump completed on 2018-06-03 15:05:09
