-- MySQL dump 10.13  Distrib 5.6.30, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bdd
-- ------------------------------------------------------
-- Server version	5.6.30-1

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
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES (98,'(root)');
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
) ENGINE=InnoDB AUTO_INCREMENT=579 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES (573,98,193,286,NULL,2,1,1,'all',0,0),(574,98,194,286,NULL,2,1056964608,1,'all',0,0),(575,98,193,287,NULL,1,1,1,'all',0,0),(576,98,194,287,NULL,1,1056964608,1,'all',0,0),(577,98,193,288,NULL,0,1,1,'all',0,0),(578,98,194,288,NULL,0,1056964608,1,'all',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES (193,NULL,98,'action',1),(194,NULL,98,'entity',1);
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
INSERT INTO `acl_object_identity_ancestors` VALUES (193,193),(194,194);
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
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
INSERT INTO `acl_security_identities` VALUES (286,'ROLE_ADMINISTRATOR',0),(287,'ROLE_CATALOG_MANAGER',0),(288,'ROLE_USER',0);
/*!40000 ALTER TABLE `acl_security_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acme_catalog_product_value_fabric`
--

DROP TABLE IF EXISTS `acme_catalog_product_value_fabric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acme_catalog_product_value_fabric` (
  `value_id` int(11) NOT NULL,
  `fabric_id` int(11) NOT NULL,
  PRIMARY KEY (`value_id`,`fabric_id`),
  KEY `IDX_68FB8B11F920BBA2` (`value_id`),
  KEY `IDX_68FB8B11AB43EC50` (`fabric_id`),
  CONSTRAINT `FK_68FB8B11AB43EC50` FOREIGN KEY (`fabric_id`) REFERENCES `acme_reference_data_fabric` (`id`),
  CONSTRAINT `FK_68FB8B11F920BBA2` FOREIGN KEY (`value_id`) REFERENCES `pim_catalog_product_value` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme_catalog_product_value_fabric`
--

LOCK TABLES `acme_catalog_product_value_fabric` WRITE;
/*!40000 ALTER TABLE `acme_catalog_product_value_fabric` DISABLE KEYS */;
INSERT INTO `acme_catalog_product_value_fabric` VALUES (211,5),(211,6);
/*!40000 ALTER TABLE `acme_catalog_product_value_fabric` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme_reference_data_color`
--

LOCK TABLES `acme_reference_data_color` WRITE;
/*!40000 ALTER TABLE `acme_reference_data_color` DISABLE KEYS */;
INSERT INTO `acme_reference_data_color` VALUES (7,'colorA',1,'colorA','#colora',31,95,52,28,72,0,45,35),(8,'colorB',1,'colorB','#colorb',49,54,16,46,29,79,12,95),(9,'colorc',1,'colorc','#colorc',10,45,20,88,8,59,19,32);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme_reference_data_fabric`
--

LOCK TABLES `acme_reference_data_fabric` WRITE;
/*!40000 ALTER TABLE `acme_reference_data_fabric` DISABLE KEYS */;
INSERT INTO `acme_reference_data_fabric` VALUES (5,'fabricA',1,'fabricA',NULL),(6,'fabricB',1,'fabricB',NULL);
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
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `exit_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exit_description` longtext COLLATE utf8_unicode_ci,
  `failure_exceptions` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `log_file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_62738477593D6954` (`job_instance_id`),
  CONSTRAINT `FK_62738477593D6954` FOREIGN KEY (`job_instance_id`) REFERENCES `akeneo_batch_job_instance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_execution`
--

LOCK TABLES `akeneo_batch_job_execution` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_execution` DISABLE KEYS */;
/*!40000 ALTER TABLE `akeneo_batch_job_execution` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5222 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_instance`
--

LOCK TABLES `akeneo_batch_job_instance` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_instance` DISABLE KEYS */;
INSERT INTO `akeneo_batch_job_instance` VALUES (5213,'update_product_value','Mass update products','update_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(5214,'add_product_value','Mass add products values','add_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(5215,'edit_common_attributes','Mass edit common product attributes','edit_common_attributes',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(5216,'set_attribute_requirements','Set family attribute requirements','set_attribute_requirements',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(5217,'add_to_variant_group','Mass add products to variant group','add_to_variant_group',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(5218,'csv_product_quick_export','CSV product quick export','csv_product_quick_export',0,'Akeneo Mass Edit Connector','a:8:{s:8:\"filePath\";s:52:\"/tmp/products_export_%locale%_%scope%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:7:\"filters\";N;s:11:\"mainContext\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;}','quick_export'),(5219,'csv_product_grid_context_quick_export','CSV product quick export grid context','csv_product_grid_context_quick_export',0,'Akeneo Mass Edit Connector','a:8:{s:8:\"filePath\";s:65:\"/tmp/products_export_grid_context_%locale%_%scope%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:7:\"filters\";N;s:11:\"mainContext\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;}','quick_export'),(5220,'xlsx_product_quick_export','XLSX product quick export','xlsx_product_quick_export',0,'Akeneo Mass Edit Connector','a:7:{s:8:\"filePath\";s:53:\"/tmp/products_export_%locale%_%scope%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:7:\"filters\";N;s:11:\"mainContext\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;}','quick_export'),(5221,'xlsx_product_grid_context_quick_export','XLSX product quick export grid context','xlsx_product_grid_context_quick_export',0,'Akeneo Mass Edit Connector','a:7:{s:8:\"filePath\";s:66:\"/tmp/products_export_grid_context_%locale%_%scope%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:7:\"filters\";N;s:11:\"mainContext\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;}','quick_export');
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
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `exit_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exit_description` longtext COLLATE utf8_unicode_ci,
  `terminate_only` tinyint(1) DEFAULT NULL,
  `failure_exceptions` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `errors` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `summary` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_BDE7D0925871C06B` (`job_execution_id`),
  CONSTRAINT `FK_BDE7D0925871C06B` FOREIGN KEY (`job_execution_id`) REFERENCES `akeneo_batch_job_execution` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=352 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_file_storage_file_info`
--

LOCK TABLES `akeneo_file_storage_file_info` WRITE;
/*!40000 ALTER TABLE `akeneo_file_storage_file_info` DISABLE KEYS */;
INSERT INTO `akeneo_file_storage_file_info` VALUES (35,'8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt','fileA.txt','text/plain',1048576,'txt','6545089471ba53d660d22d7b8dc8dd67904b1e28','catalogStorage'),(36,'3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg','imageA.jpg','text/plain',1048576,'jpg','a9453e6ce89dbfd776ecae609e1c23e9cfad8277','catalogStorage'),(37,'7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg','imageB-en_US.jpg','text/plain',1048576,'jpg','16850b6741c6e0d7622edb29465488571a2e63df','catalogStorage'),(38,'0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg','imageB-fr_FR.jpg','text/plain',1048576,'jpg','058c6f380b0888afadf7341f8baaf58b538e5774','catalogStorage');
/*!40000 ALTER TABLE `akeneo_file_storage_file_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_access_group`
--

DROP TABLE IF EXISTS `oro_access_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_access_group` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FEF9EDB75E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_group`
--

LOCK TABLES `oro_access_group` WRITE;
/*!40000 ALTER TABLE `oro_access_group` DISABLE KEYS */;
INSERT INTO `oro_access_group` VALUES (404,'All'),(401,'IT support'),(402,'Manager'),(403,'Redactor');
/*!40000 ALTER TABLE `oro_access_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_access_role`
--

DROP TABLE IF EXISTS `oro_access_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_access_role` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `role` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_673F65E757698A6A` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_role`
--

LOCK TABLES `oro_access_role` WRITE;
/*!40000 ALTER TABLE `oro_access_role` DISABLE KEYS */;
INSERT INTO `oro_access_role` VALUES (286,'ROLE_ADMINISTRATOR','Administrator'),(287,'ROLE_CATALOG_MANAGER','Catalog manager'),(288,'ROLE_USER','User');
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
-- Table structure for table `oro_navigation_history`
--

DROP TABLE IF EXISTS `oro_navigation_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_navigation_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` varchar(1023) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `visited_at` datetime NOT NULL,
  `visit_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B20613B9A76ED395` (`user_id`),
  CONSTRAINT `FK_B20613B9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_navigation_history`
--

LOCK TABLES `oro_navigation_history` WRITE;
/*!40000 ALTER TABLE `oro_navigation_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_navigation_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_navigation_item`
--

DROP TABLE IF EXISTS `oro_navigation_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_navigation_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(1023) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` smallint(6) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_323B0258A76ED395` (`user_id`),
  KEY `sorted_items_idx` (`user_id`,`position`),
  CONSTRAINT `FK_323B0258A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_navigation_item`
--

LOCK TABLES `oro_navigation_item` WRITE;
/*!40000 ALTER TABLE `oro_navigation_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_navigation_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_navigation_item_pinbar`
--

DROP TABLE IF EXISTS `oro_navigation_item_pinbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_navigation_item_pinbar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `maximized` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_54973433126F525E` (`item_id`),
  CONSTRAINT `FK_54973433126F525E` FOREIGN KEY (`item_id`) REFERENCES `oro_navigation_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_navigation_item_pinbar`
--

LOCK TABLES `oro_navigation_item_pinbar` WRITE;
/*!40000 ALTER TABLE `oro_navigation_item_pinbar` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_navigation_item_pinbar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_navigation_pagestate`
--

DROP TABLE IF EXISTS `oro_navigation_pagestate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_navigation_pagestate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `page_id` varchar(4000) COLLATE utf8_unicode_ci NOT NULL,
  `page_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8B43985B567C7E62` (`page_hash`),
  KEY `IDX_8B43985BA76ED395` (`user_id`),
  CONSTRAINT `FK_8B43985BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_navigation_pagestate`
--

LOCK TABLES `oro_navigation_pagestate` WRITE;
/*!40000 ALTER TABLE `oro_navigation_pagestate` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_navigation_pagestate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_navigation_title`
--

DROP TABLE IF EXISTS `oro_navigation_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_navigation_title` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_system` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_route` (`route`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_navigation_title`
--

LOCK TABLES `oro_navigation_title` WRITE;
/*!40000 ALTER TABLE `oro_navigation_title` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_navigation_title` ENABLE KEYS */;
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
  `password_requested` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int(10) unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  `product_grid_filters` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `emailNotifications` tinyint(1) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB AUTO_INCREMENT=557 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user`
--

LOCK TABLES `oro_user` WRITE;
/*!40000 ALTER TABLE `oro_user` DISABLE KEYS */;
INSERT INTO `oro_user` VALUES (556,21058,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,NULL,1,'7ovkybu86ds8gc8cw4k8k4cs4kgss0s','hGL9trt89OVW0/8w9VtEqjL+AB0mL5Fq2QURDTPurcngMugCzmh9CTyJib8s5FxY92XJmP9w6YRBMCk6yp8mVg==',NULL,NULL,NULL,0,'2016-08-04 14:28:49','2016-08-04 14:28:49','[]',0,21058,209,895);
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
  `group_id` smallint(6) NOT NULL,
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
INSERT INTO `oro_user_access_group` VALUES (556,401),(556,404);
/*!40000 ALTER TABLE `oro_user_access_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_user_access_group_role`
--

DROP TABLE IF EXISTS `oro_user_access_group_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_user_access_group_role` (
  `group_id` smallint(6) NOT NULL,
  `role_id` smallint(6) NOT NULL,
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
  `role_id` smallint(6) NOT NULL,
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
INSERT INTO `oro_user_access_role` VALUES (556,286);
/*!40000 ALTER TABLE `oro_user_access_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oro_user_api`
--

DROP TABLE IF EXISTS `oro_user_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oro_user_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_296B6993C912ED9D` (`api_key`),
  UNIQUE KEY `UNIQ_296B6993A76ED395` (`user_id`),
  CONSTRAINT `FK_296B6993A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=553 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user_api`
--

LOCK TABLES `oro_user_api` WRITE;
/*!40000 ALTER TABLE `oro_user_api` DISABLE KEYS */;
/*!40000 ALTER TABLE `oro_user_api` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association`
--

LOCK TABLES `pim_catalog_association` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association` DISABLE KEYS */;
INSERT INTO `pim_catalog_association` VALUES (9,428,49),(8,429,49),(7,431,49);
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
INSERT INTO `pim_catalog_association_group` VALUES (8,240),(9,241);
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
INSERT INTO `pim_catalog_association_product` VALUES (7,47),(7,48),(9,47);
/*!40000 ALTER TABLE `pim_catalog_association_product` ENABLE KEYS */;
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
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E6CF913A77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=432 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type`
--

LOCK TABLES `pim_catalog_association_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type` VALUES (428,'X_SELL','2016-08-04 14:28:49','2016-08-04 14:28:49'),(429,'UPSELL','2016-08-04 14:28:49','2016-08-04 14:28:49'),(430,'SUBSTITUTION','2016-08-04 14:28:49','2016-08-04 14:28:49'),(431,'PACK','2016-08-04 14:28:49','2016-08-04 14:28:49');
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
) ENGINE=InnoDB AUTO_INCREMENT=649 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type_translation`
--

LOCK TABLES `pim_catalog_association_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type_translation` VALUES (641,428,'Cross sell','en_US'),(642,428,'Vente croisée','fr_FR'),(643,429,'Upsell','en_US'),(644,429,'Vente incitative','fr_FR'),(645,430,'Substitution','en_US'),(646,430,'Remplacement','fr_FR'),(647,431,'Pack','en_US'),(648,431,'Pack','fr_FR');
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
  `date_min` datetime DEFAULT NULL,
  `date_max` datetime DEFAULT NULL,
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
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`code`,`entity_type`),
  KEY `IDX_492FD44FFE54D947` (`group_id`),
  KEY `searchcode_idx` (`code`),
  CONSTRAINT `FK_492FD44FFE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_attribute_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2542 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute`
--

LOCK TABLES `pim_catalog_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES (2523,594,0,1,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,1,1,0,0,'sku','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_identifier','varchar','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2524,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_date','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_date','date','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2525,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_file','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_file','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2526,595,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'an_image','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2527,595,0,0,NULL,'','',0,NULL,NULL,1,1,NULL,NULL,'Power','KILOWATT',NULL,'',0,0,0,0,0,'a_metric','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2528,595,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_multi_select','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_multiselect','options','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2529,595,0,0,NULL,'','',0,NULL,NULL,1,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_number_float','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2530,595,0,0,NULL,'','',0,NULL,NULL,1,1,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_number_float_negative','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2531,595,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_number_integer','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2532,594,0,0,NULL,'','',0,NULL,NULL,1,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_price','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2533,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_ref_data_multi_select','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:1:{s:19:\"reference_data_name\";s:7:\"fabrics\";}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2534,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_ref_data_simple_select','Pim\\Component\\Catalog\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:1:{s:19:\"reference_data_name\";s:5:\"color\";}','2016-08-04 14:28:49','2016-08-04 14:28:49'),(2535,595,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_simple_select','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_simpleselect','option','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),(2536,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_text','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_text','varchar','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),(2537,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_text_area','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),(2538,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,0,0,'a_yes_no','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_boolean','boolean','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),(2539,595,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,1,0,'a_localizable_image','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_image','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),(2540,594,0,0,NULL,'','',0,NULL,NULL,1,0,NULL,NULL,'','',NULL,'',0,0,0,0,1,'a_scopable_price','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),(2541,594,0,0,NULL,'','',0,NULL,NULL,0,0,NULL,NULL,'','',NULL,'',0,0,0,1,1,'a_localized_and_scopable_text_area','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_textarea','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50');
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
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E299C05E77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=596 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group`
--

LOCK TABLES `pim_catalog_attribute_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group` VALUES (593,'other',100,'2016-08-04 14:28:49','2016-08-04 14:28:49'),(594,'attributeGroupA',0,'2016-08-04 14:28:49','2016-08-04 14:28:49'),(595,'attributeGroupB',0,'2016-08-04 14:28:49','2016-08-04 14:28:49');
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
) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group_translation`
--

LOCK TABLES `pim_catalog_attribute_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group_translation` VALUES (808,593,'Other','en_US'),(809,593,'Autre','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=3805 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option`
--

LOCK TABLES `pim_catalog_attribute_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option` VALUES (3801,2528,'optionA',1),(3802,2528,'optionB',1),(3803,2535,'optionA',1),(3804,2535,'optionB',1);
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
  `value` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchunique_idx` (`locale_code`,`option_id`),
  KEY `IDX_CC4B9A83A7C41D6F` (`option_id`),
  CONSTRAINT `FK_CC4B9A83A7C41D6F` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5845 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option_value`
--

LOCK TABLES `pim_catalog_attribute_option_value` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option_value` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6743 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

LOCK TABLES `pim_catalog_attribute_requirement` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_requirement` VALUES (6705,466,2523,209,1),(6706,466,2523,210,1),(6707,466,2524,209,1),(6708,466,2525,209,1),(6709,466,2527,209,1),(6710,466,2528,209,1),(6711,466,2529,209,1),(6712,466,2530,209,1),(6713,466,2531,209,1),(6714,466,2532,209,1),(6715,466,2533,209,1),(6716,466,2534,209,1),(6717,466,2535,209,1),(6718,466,2536,209,1),(6719,466,2537,209,1),(6720,466,2538,209,1),(6721,466,2526,209,1),(6722,466,2539,209,1),(6723,466,2540,209,1),(6724,466,2541,209,1),(6725,466,2524,210,1),(6726,466,2525,210,1),(6727,466,2527,210,1),(6728,466,2528,210,1),(6729,466,2529,210,1),(6730,466,2530,210,1),(6731,466,2531,210,1),(6732,466,2532,210,1),(6733,466,2533,210,1),(6734,466,2534,210,1),(6735,466,2535,210,1),(6736,466,2536,210,1),(6737,466,2537,210,1),(6738,466,2538,210,1),(6739,466,2526,210,1),(6740,466,2539,210,1),(6741,466,2540,210,1),(6742,466,2541,210,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=3522 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_translation`
--

LOCK TABLES `pim_catalog_attribute_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (3521,2523,'SKU','en_US');
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
  `created` datetime NOT NULL,
  `root` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_350D833977153098` (`code`),
  KEY `IDX_350D8339727ACA70` (`parent_id`),
  CONSTRAINT `FK_350D8339727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `pim_catalog_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=900 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category`
--

LOCK TABLES `pim_catalog_category` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
INSERT INTO `pim_catalog_category` VALUES (895,NULL,'master','2016-08-04 14:28:49',895,0,1,10),(896,895,'categoryA','2016-08-04 14:28:49',895,1,2,7),(897,896,'categoryA1','2016-08-04 14:28:49',895,2,3,4),(898,896,'categoryA2','2016-08-04 14:28:49',895,2,5,6),(899,895,'categoryB','2016-08-04 14:28:49',895,1,8,9);
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
INSERT INTO `pim_catalog_category_product` VALUES (49,897),(49,899);
/*!40000 ALTER TABLE `pim_catalog_category_product` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=1098 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_translation`
--

LOCK TABLES `pim_catalog_category_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_translation` VALUES (1097,895,'Master catalog','en_US');
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
  `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conversionUnits` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E07E932A77153098` (`code`),
  KEY `IDX_E07E932A12469DE2` (`category_id`),
  CONSTRAINT `FK_E07E932A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `pim_catalog_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel`
--

LOCK TABLES `pim_catalog_channel` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel` VALUES (209,895,'ecommerce','Default','a:0:{}'),(210,895,'tablet','Tablet','a:0:{}');
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
INSERT INTO `pim_catalog_channel_currency` VALUES (209,10101),(210,10102);
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
INSERT INTO `pim_catalog_channel_locale` VALUES (209,21058),(210,21039),(210,21058),(210,21090);
/*!40000 ALTER TABLE `pim_catalog_channel_locale` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=780 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_completeness`
--

LOCK TABLES `pim_catalog_completeness` WRITE;
/*!40000 ALTER TABLE `pim_catalog_completeness` DISABLE KEYS */;
INSERT INTO `pim_catalog_completeness` VALUES (773,21058,209,49,100,0,19),(774,21039,210,49,89,2,19),(775,21058,210,49,100,0,19),(776,21090,210,49,100,0,19);
/*!40000 ALTER TABLE `pim_catalog_completeness` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10202 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_currency`
--

LOCK TABLES `pim_catalog_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (10101,'USD',1),(10102,'EUR',1),(10103,'ADP',0),(10104,'AED',0),(10105,'AFA',0),(10106,'ALK',0),(10107,'AOK',0),(10108,'AON',0),(10109,'AOR',0),(10110,'ARM',0),(10111,'ARP',0),(10112,'ARL',0),(10113,'ATS',0),(10114,'AZM',0),(10115,'BAD',0),(10116,'BAN',0),(10117,'BEC',0),(10118,'BEF',0),(10119,'BEL',0),(10120,'BGL',0),(10121,'BGM',0),(10122,'BGO',0),(10123,'BOL',0),(10124,'BOP',0),(10125,'BRB',0),(10126,'BRC',0),(10127,'BRE',0),(10128,'BRR',0),(10129,'BRN',0),(10130,'BRZ',0),(10131,'BYB',0),(10132,'CHE',0),(10133,'CHW',0),(10134,'CSD',0),(10135,'CSK',0),(10136,'DEM',0),(10137,'EEK',0),(10138,'ESA',0),(10139,'ESB',0),(10140,'ESP',0),(10141,'FIM',0),(10142,'FRF',0),(10143,'GHC',0),(10144,'GBP',0),(10145,'GRD',0),(10146,'GYD',0),(10147,'IEP',0),(10148,'ISJ',0),(10149,'ILR',0),(10150,'ITL',0),(10151,'KRH',0),(10152,'KRO',0),(10153,'LUF',0),(10154,'LVR',0),(10155,'MGF',0),(10156,'MKN',0),(10157,'MTL',0),(10158,'MXP',0),(10159,'MZE',0),(10160,'MZM',0),(10161,'NIC',0),(10162,'NLG',0),(10163,'PES',0),(10164,'PLZ',0),(10165,'PTE',0),(10166,'ROL',0),(10167,'SDD',0),(10168,'SDP',0),(10169,'SIT',0),(10170,'SKK',0),(10171,'SUR',0),(10172,'TMM',0),(10173,'TPE',0),(10174,'TRL',0),(10175,'UGS',0),(10176,'UYP',0),(10177,'VEB',0),(10178,'VNN',0),(10179,'XAU',0),(10180,'XBA',0),(10181,'XBB',0),(10182,'XBC',0),(10183,'XBD',0),(10184,'XEU',0),(10185,'XFO',0),(10186,'XFU',0),(10187,'XPD',0),(10188,'XDR',0),(10189,'XSU',0),(10190,'XTS',0),(10191,'XXX',0),(10192,'YUD',0),(10193,'YUM',0),(10194,'YUN',0),(10195,'YUR',0),(10196,'ZMK',0),(10197,'ZRN',0),(10198,'ZRZ',0),(10199,'ZWD',0),(10200,'ZWL',0),(10201,'ZWR',0);
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
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9063207277153098` (`code`),
  KEY `IDX_90632072E2D3A503` (`label_attribute_id`),
  CONSTRAINT `FK_90632072E2D3A503` FOREIGN KEY (`label_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=467 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family`
--

LOCK TABLES `pim_catalog_family` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
INSERT INTO `pim_catalog_family` VALUES (466,2523,'familyA','2016-08-04 14:28:50','2016-08-04 14:28:50');
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
INSERT INTO `pim_catalog_family_attribute` VALUES (466,2523),(466,2524),(466,2525),(466,2526),(466,2527),(466,2528),(466,2529),(466,2530),(466,2531),(466,2532),(466,2533),(466,2534),(466,2535),(466,2536),(466,2537),(466,2538),(466,2539),(466,2540),(466,2541);
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
) ENGINE=InnoDB AUTO_INCREMENT=589 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_translation`
--

LOCK TABLES `pim_catalog_family_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_catalog_family_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group`
--

DROP TABLE IF EXISTS `pim_catalog_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_template_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5D6997ED77153098` (`code`),
  UNIQUE KEY `UNIQ_5D6997EDA9F591A7` (`product_template_id`),
  KEY `IDX_5D6997EDC54C8C93` (`type_id`),
  CONSTRAINT `FK_5D6997EDA9F591A7` FOREIGN KEY (`product_template_id`) REFERENCES `pim_catalog_product_template` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5D6997EDC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `pim_catalog_group_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group`
--

LOCK TABLES `pim_catalog_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_group` VALUES (239,3,322,'variantA'),(240,NULL,323,'groupA'),(241,NULL,323,'groupB');
/*!40000 ALTER TABLE `pim_catalog_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_group_attribute`
--

DROP TABLE IF EXISTS `pim_catalog_group_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_group_attribute` (
  `group_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`attribute_id`),
  KEY `IDX_56A8D437FE54D947` (`group_id`),
  KEY `IDX_56A8D437B6E62EFA` (`attribute_id`),
  CONSTRAINT `FK_56A8D437B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`),
  CONSTRAINT `FK_56A8D437FE54D947` FOREIGN KEY (`group_id`) REFERENCES `pim_catalog_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_attribute`
--

LOCK TABLES `pim_catalog_group_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_attribute` VALUES (239,2535);
/*!40000 ALTER TABLE `pim_catalog_group_attribute` ENABLE KEYS */;
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
INSERT INTO `pim_catalog_group_product` VALUES (49,239),(49,240),(49,241);
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
) ENGINE=InnoDB AUTO_INCREMENT=458 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_translation`
--

LOCK TABLES `pim_catalog_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_translation` VALUES (455,239,'','en_US'),(456,240,'','en_US'),(457,241,'','en_US');
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
  `is_variant` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_693B2EF777153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=324 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type`
--

LOCK TABLES `pim_catalog_group_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type` VALUES (322,'VARIANT',1),(323,'RELATED',0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=21211 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_locale`
--

LOCK TABLES `pim_catalog_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_locale` VALUES (21001,'af_ZA',0),(21002,'am_ET',0),(21003,'ar_AE',0),(21004,'ar_BH',0),(21005,'ar_DZ',0),(21006,'ar_EG',0),(21007,'ar_IQ',0),(21008,'ar_JO',0),(21009,'ar_KW',0),(21010,'ar_LB',0),(21011,'ar_LY',0),(21012,'ar_MA',0),(21013,'arn_CL',0),(21014,'ar_OM',0),(21015,'ar_QA',0),(21016,'ar_SA',0),(21017,'ar_SY',0),(21018,'ar_TN',0),(21019,'ar_YE',0),(21020,'as_IN',0),(21021,'az_Cyrl_AZ',0),(21022,'az_Latn_AZ',0),(21023,'ba_RU',0),(21024,'be_BY',0),(21025,'bg_BG',0),(21026,'bn_BD',0),(21027,'bn_IN',0),(21028,'bo_CN',0),(21029,'br_FR',0),(21030,'bs_Cyrl_BA',0),(21031,'bs_Latn_BA',0),(21032,'ca_ES',0),(21033,'co_FR',0),(21034,'cs_CZ',0),(21035,'cy_GB',0),(21036,'da_DK',0),(21037,'de_AT',0),(21038,'de_CH',0),(21039,'de_DE',1),(21040,'de_LI',0),(21041,'de_LU',0),(21042,'dsb_DE',0),(21043,'dv_MV',0),(21044,'el_GR',0),(21045,'en_029',0),(21046,'en_AU',0),(21047,'en_BZ',0),(21048,'en_CA',0),(21049,'en_GB',0),(21050,'en_IE',0),(21051,'en_IN',0),(21052,'en_JM',0),(21053,'en_MY',0),(21054,'en_NZ',0),(21055,'en_PH',0),(21056,'en_SG',0),(21057,'en_TT',0),(21058,'en_US',1),(21059,'en_ZA',0),(21060,'en_ZW',0),(21061,'es_AR',0),(21062,'es_BO',0),(21063,'es_CL',0),(21064,'es_CO',0),(21065,'es_CR',0),(21066,'es_DO',0),(21067,'es_EC',0),(21068,'es_ES',0),(21069,'es_GT',0),(21070,'es_HN',0),(21071,'es_MX',0),(21072,'es_NI',0),(21073,'es_PA',0),(21074,'es_PE',0),(21075,'es_PR',0),(21076,'es_PY',0),(21077,'es_SV',0),(21078,'es_US',0),(21079,'es_UY',0),(21080,'es_VE',0),(21081,'et_EE',0),(21082,'eu_ES',0),(21083,'fa_IR',0),(21084,'fi_FI',0),(21085,'fil_PH',0),(21086,'fo_FO',0),(21087,'fr_BE',0),(21088,'fr_CA',0),(21089,'fr_CH',0),(21090,'fr_FR',1),(21091,'fr_LU',0),(21092,'fr_MC',0),(21093,'fy_NL',0),(21094,'ga_IE',0),(21095,'gd_GB',0),(21096,'gl_ES',0),(21097,'gsw_FR',0),(21098,'gu_IN',0),(21099,'ha_Latn_NG',0),(21100,'he_IL',0),(21101,'hi_IN',0),(21102,'hr_BA',0),(21103,'hr_HR',0),(21104,'hsb_DE',0),(21105,'hu_HU',0),(21106,'hy_AM',0),(21107,'id_ID',0),(21108,'ig_NG',0),(21109,'ii_CN',0),(21110,'is_IS',0),(21111,'it_CH',0),(21112,'it_IT',0),(21113,'iu_Cans_CA',0),(21114,'iu_Latn_CA',0),(21115,'ja_JP',0),(21116,'ka_GE',0),(21117,'kk_KZ',0),(21118,'kl_GL',0),(21119,'km_KH',0),(21120,'kn_IN',0),(21121,'kok_IN',0),(21122,'ko_KR',0),(21123,'ky_KG',0),(21124,'lb_LU',0),(21125,'lo_LA',0),(21126,'lt_LT',0),(21127,'lv_LV',0),(21128,'mi_NZ',0),(21129,'mk_MK',0),(21130,'ml_IN',0),(21131,'mn_MN',0),(21132,'mn_Mong_CN',0),(21133,'moh_CA',0),(21134,'mr_IN',0),(21135,'ms_BN',0),(21136,'ms_MY',0),(21137,'mt_MT',0),(21138,'nb_NO',0),(21139,'ne_NP',0),(21140,'nl_BE',0),(21141,'nl_NL',0),(21142,'nn_NO',0),(21143,'nso_ZA',0),(21144,'oc_FR',0),(21145,'or_IN',0),(21146,'pa_IN',0),(21147,'pl_PL',0),(21148,'prs_AF',0),(21149,'ps_AF',0),(21150,'pt_BR',0),(21151,'pt_PT',0),(21152,'qut_GT',0),(21153,'quz_BO',0),(21154,'quz_EC',0),(21155,'quz_PE',0),(21156,'rm_CH',0),(21157,'ro_RO',0),(21158,'ru_RU',0),(21159,'rw_RW',0),(21160,'sah_RU',0),(21161,'sa_IN',0),(21162,'se_FI',0),(21163,'se_NO',0),(21164,'se_SE',0),(21165,'si_LK',0),(21166,'sk_SK',0),(21167,'sl_SI',0),(21168,'sma_NO',0),(21169,'sma_SE',0),(21170,'smj_NO',0),(21171,'smj_SE',0),(21172,'smn_FI',0),(21173,'sms_FI',0),(21174,'sq_AL',0),(21175,'sr_Cyrl_BA',0),(21176,'sr_Cyrl_CS',0),(21177,'sr_Cyrl_ME',0),(21178,'sr_Cyrl_RS',0),(21179,'sr_Latn_BA',0),(21180,'sr_Latn_CS',0),(21181,'sr_Latn_ME',0),(21182,'sr_Latn_RS',0),(21183,'sv_FI',0),(21184,'sv_SE',0),(21185,'sw_KE',0),(21186,'syr_SY',0),(21187,'ta_IN',0),(21188,'te_IN',0),(21189,'tg_Cyrl_TJ',0),(21190,'th_TH',0),(21191,'tk_TM',0),(21192,'tn_ZA',0),(21193,'tr_TR',0),(21194,'tt_RU',0),(21195,'tzm_Latn_DZ',0),(21196,'ug_CN',0),(21197,'uk_UA',0),(21198,'ur_PK',0),(21199,'uz_Cyrl_UZ',0),(21200,'uz_Latn_UZ',0),(21201,'vi_VN',0),(21202,'wo_SN',0),(21203,'xh_ZA',0),(21204,'yo_NG',0),(21205,'zh_CN',0),(21206,'zh_HK',0),(21207,'zh_MO',0),(21208,'zh_SG',0),(21209,'zh_TW',0),(21210,'zu_ZA',0);
/*!40000 ALTER TABLE `pim_catalog_locale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_metric`
--

DROP TABLE IF EXISTS `pim_catalog_metric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_metric` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` decimal(24,12) DEFAULT NULL,
  `unit` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_data` decimal(24,12) DEFAULT NULL,
  `base_unit` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `family` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_metric`
--

LOCK TABLES `pim_catalog_metric` WRITE;
/*!40000 ALTER TABLE `pim_catalog_metric` DISABLE KEYS */;
INSERT INTO `pim_catalog_metric` VALUES (3,987654321987.123456789123,'KILOWATT',999999999999.999999999999,'WATT','Power');
/*!40000 ALTER TABLE `pim_catalog_metric` ENABLE KEYS */;
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
  `is_enabled` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_91CD19C0C35E566A` (`family_id`),
  CONSTRAINT `FK_91CD19C0C35E566A` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product`
--

LOCK TABLES `pim_catalog_product` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product` DISABLE KEYS */;
INSERT INTO `pim_catalog_product` VALUES (47,NULL,0,'2016-08-04 14:28:51','2016-08-04 14:28:51'),(48,NULL,1,'2016-08-04 14:28:51','2016-08-04 14:28:51'),(49,466,1,'2016-08-04 14:28:51','2016-08-04 14:28:51');
/*!40000 ALTER TABLE `pim_catalog_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_template`
--

DROP TABLE IF EXISTS `pim_catalog_product_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valuesData` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_template`
--

LOCK TABLES `pim_catalog_product_template` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_template` DISABLE KEYS */;
INSERT INTO `pim_catalog_product_template` VALUES (3,'[]');
/*!40000 ALTER TABLE `pim_catalog_product_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_value`
--

DROP TABLE IF EXISTS `pim_catalog_product_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color_id` int(11) DEFAULT NULL,
  `metric_id` int(11) DEFAULT NULL,
  `attribute_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `media_id` int(11) DEFAULT NULL,
  `value_string` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value_integer` int(11) DEFAULT NULL,
  `value_decimal` decimal(14,4) DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_text` longtext COLLATE utf8_unicode_ci,
  `value_date` date DEFAULT NULL,
  `value_datetime` datetime DEFAULT NULL,
  `locale_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scope_code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `my_new_field` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_93A1BBF3A952D583` (`metric_id`),
  KEY `IDX_93A1BBF37ADA1FB5` (`color_id`),
  KEY `IDX_93A1BBF3B6E62EFA` (`attribute_id`),
  KEY `IDX_93A1BBF3A7C41D6F` (`option_id`),
  KEY `IDX_93A1BBF381257D5D` (`entity_id`),
  KEY `IDX_93A1BBF3EA9FDD75` (`media_id`),
  KEY `value_idx` (`attribute_id`,`locale_code`,`scope_code`),
  KEY `varchar_idx` (`value_string`),
  KEY `integer_idx` (`value_integer`),
  CONSTRAINT `FK_93A1BBF37ADA1FB5` FOREIGN KEY (`color_id`) REFERENCES `acme_reference_data_color` (`id`),
  CONSTRAINT `FK_93A1BBF381257D5D` FOREIGN KEY (`entity_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_93A1BBF3A7C41D6F` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_93A1BBF3A952D583` FOREIGN KEY (`metric_id`) REFERENCES `pim_catalog_metric` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_93A1BBF3B6E62EFA` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_93A1BBF3EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `akeneo_file_storage_file_info` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_value`
--

LOCK TABLES `pim_catalog_product_value` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_value` DISABLE KEYS */;
INSERT INTO `pim_catalog_product_value` VALUES (199,NULL,NULL,2523,NULL,47,NULL,'bar',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'174878780'),(200,NULL,NULL,2523,NULL,48,NULL,'baz',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'566731801'),(201,NULL,NULL,2523,NULL,49,NULL,'foo',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'444882715'),(202,NULL,NULL,2525,NULL,49,35,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1723967000'),(203,NULL,NULL,2526,NULL,49,36,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1494623165'),(204,NULL,NULL,2524,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,'2016-06-13',NULL,NULL,NULL,'1871692103'),(205,NULL,3,2527,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1912643738'),(206,NULL,NULL,2528,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1790955411'),(207,NULL,NULL,2529,NULL,49,NULL,NULL,NULL,12.5678,NULL,NULL,NULL,NULL,NULL,NULL,'2072785373'),(208,NULL,NULL,2530,NULL,49,NULL,NULL,NULL,-99.8732,NULL,NULL,NULL,NULL,NULL,NULL,'533536503'),(209,NULL,NULL,2531,NULL,49,NULL,NULL,NULL,42.0000,NULL,NULL,NULL,NULL,NULL,NULL,'2143395388'),(210,NULL,NULL,2532,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1846269442'),(211,NULL,NULL,2533,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2103925379'),(212,8,NULL,2534,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'710152958'),(213,NULL,NULL,2535,3804,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'516043646'),(214,NULL,NULL,2536,NULL,49,NULL,'this is a text',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1211703665'),(215,NULL,NULL,2537,NULL,49,NULL,NULL,NULL,NULL,NULL,'this is a very very very very very long  text',NULL,NULL,NULL,NULL,'753768604'),(216,NULL,NULL,2538,NULL,49,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'355741662'),(217,NULL,NULL,2539,NULL,49,37,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'en_US',NULL,'389300684'),(218,NULL,NULL,2539,NULL,49,38,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'fr_FR',NULL,'1261239871'),(219,NULL,NULL,2540,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ecommerce','1389221508'),(220,NULL,NULL,2540,NULL,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'tablet','1464149237'),(221,NULL,NULL,2541,NULL,49,NULL,NULL,NULL,NULL,NULL,'a text area for eccommerce in English',NULL,NULL,'en_US','ecommerce','411138330'),(222,NULL,NULL,2541,NULL,49,NULL,NULL,NULL,NULL,NULL,'a text area for tablets in English',NULL,NULL,'en_US','tablet','1248207208'),(223,NULL,NULL,2541,NULL,49,NULL,NULL,NULL,NULL,NULL,'une zone de texte pour les tablettes en français',NULL,NULL,'fr_FR','tablet','1275161185');
/*!40000 ALTER TABLE `pim_catalog_product_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_value_option`
--

DROP TABLE IF EXISTS `pim_catalog_product_value_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_value_option` (
  `value_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`value_id`,`option_id`),
  KEY `IDX_649CDCADF920BBA2` (`value_id`),
  KEY `IDX_649CDCADA7C41D6F` (`option_id`),
  CONSTRAINT `FK_649CDCADA7C41D6F` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_649CDCADF920BBA2` FOREIGN KEY (`value_id`) REFERENCES `pim_catalog_product_value` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_value_option`
--

LOCK TABLES `pim_catalog_product_value_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_value_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_product_value_option` VALUES (206,3801),(206,3802);
/*!40000 ALTER TABLE `pim_catalog_product_value_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pim_catalog_product_value_price`
--

DROP TABLE IF EXISTS `pim_catalog_product_value_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_catalog_product_value_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value_id` int(11) DEFAULT NULL,
  `data` decimal(10,2) DEFAULT NULL,
  `currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value_currency_idx` (`value_id`,`currency_code`),
  KEY `IDX_BE63E43EF920BBA2` (`value_id`),
  CONSTRAINT `FK_BE63E43EF920BBA2` FOREIGN KEY (`value_id`) REFERENCES `pim_catalog_product_value` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_product_value_price`
--

LOCK TABLES `pim_catalog_product_value_price` WRITE;
/*!40000 ALTER TABLE `pim_catalog_product_value_price` DISABLE KEYS */;
INSERT INTO `pim_catalog_product_value_price` VALUES (13,210,56.53,'EUR'),(14,210,45.00,'USD'),(15,219,15.00,'EUR'),(16,219,20.00,'USD'),(17,220,17.00,'EUR'),(18,220,24.00,'USD');
/*!40000 ALTER TABLE `pim_catalog_product_value_price` ENABLE KEYS */;
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
  `created_at` datetime NOT NULL,
  `replied_at` datetime NOT NULL,
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
-- Table structure for table `pim_enrich_sequential_edit`
--

DROP TABLE IF EXISTS `pim_enrich_sequential_edit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pim_enrich_sequential_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `object_set` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8B8F3DA5A76ED395` (`user_id`),
  CONSTRAINT `FK_8B8F3DA5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_enrich_sequential_edit`
--

LOCK TABLES `pim_enrich_sequential_edit` WRITE;
/*!40000 ALTER TABLE `pim_enrich_sequential_edit` DISABLE KEYS */;
/*!40000 ALTER TABLE `pim_enrich_sequential_edit` ENABLE KEYS */;
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
  `created` datetime NOT NULL,
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
  `logged_at` datetime NOT NULL,
  `pending` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_name_resource_id_idx` (`resource_name`,`resource_id`),
  KEY `resource_name_idx` (`resource_name`),
  KEY `pending_idx` (`pending`),
  KEY `version_idx` (`version`),
  KEY `logged_at_idx` (`logged_at`)
) ENGINE=InnoDB AUTO_INCREMENT=33000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_versioning_version`
--

LOCK TABLES `pim_versioning_version` WRITE;
/*!40000 ALTER TABLE `pim_versioning_version` DISABLE KEYS */;
INSERT INTO `pim_versioning_version` VALUES (32725,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5196','a:5:{s:4:\"code\";s:20:\"fixtures_locale_csv0\";s:5:\"label\";s:21:\"Locales data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:248:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_locale_csv0\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Locales data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:248:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32726,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5197','a:5:{s:4:\"code\";s:22:\"fixtures_currency_csv1\";s:5:\"label\";s:24:\"Currencies data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_currency_csv1\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Currencies data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32727,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5198','a:5:{s:4:\"code\";s:24:\"fixtures_user_group_csv2\";s:5:\"label\";s:25:\"User groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:252:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_group_csv2\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"User groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:252:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32728,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5199','a:5:{s:4:\"code\";s:22:\"fixtures_category_csv3\";s:5:\"label\";s:24:\"Categories data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_category_csv3\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Categories data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32729,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5200','a:5:{s:4:\"code\";s:29:\"fixtures_attribute_group_csv4\";s:5:\"label\";s:30:\"Attribute groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:257:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_attribute_group_csv4\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"Attribute groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:257:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32730,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5201','a:5:{s:4:\"code\";s:24:\"fixtures_group_type_csv5\";s:5:\"label\";s:25:\"Group types data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:252:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_group_type_csv5\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Group types data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:252:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32731,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5202','a:5:{s:4:\"code\";s:21:\"fixtures_channel_csv6\";s:5:\"label\";s:22:\"Channels data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:249:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_channel_csv6\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Channels data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:249:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32732,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5203','a:5:{s:4:\"code\";s:30:\"fixtures_association_type_csv7\";s:5:\"label\";s:26:\"Associations data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:258:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"fixtures_association_type_csv7\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Associations data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:258:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32733,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5204','a:5:{s:4:\"code\";s:23:\"fixtures_attribute_csv8\";s:5:\"label\";s:24:\"Attributes data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"fixtures_attribute_csv8\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Attributes data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32734,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5205','a:5:{s:4:\"code\";s:31:\"fixtures_attribute_options_csv9\";s:5:\"label\";s:31:\"Attribute options data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:258:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"fixtures_attribute_options_csv9\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"Attribute options data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:258:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32735,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5206','a:5:{s:4:\"code\";s:21:\"fixtures_family_csv10\";s:5:\"label\";s:22:\"Families data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:249:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_family_csv10\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Families data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:249:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32736,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5207','a:5:{s:4:\"code\";s:28:\"fixtures_variant_group_csv11\";s:5:\"label\";s:28:\"Variant groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:317:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/variant_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"decimalSeparator\":\".\",\"dateFormat\":\"Y-m-d\",\"copyValues\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"fixtures_variant_group_csv11\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Variant groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:317:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/variant_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"decimalSeparator\":\".\",\"dateFormat\":\"Y-m-d\",\"copyValues\":true}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32737,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5208','a:5:{s:4:\"code\";s:20:\"fixtures_group_csv12\";s:5:\"label\";s:20:\"Groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:247:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_group_csv12\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:247:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32738,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5209','a:5:{s:4:\"code\";s:24:\"fixtures_user_role_csv13\";s:5:\"label\";s:24:\"User roles data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_role_csv13\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"User roles data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:251:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32739,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5210','a:5:{s:4:\"code\";s:19:\"fixtures_user_csv14\";s:5:\"label\";s:19:\"Users data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:246:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"fixtures_user_csv14\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Users data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:246:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32740,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5211','a:5:{s:4:\"code\";s:22:\"fixtures_product_csv15\";s:5:\"label\";s:21:\"Product data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:439:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"decimalSeparator\":\".\",\"dateFormat\":\"Y-m-d\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_product_csv15\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Product data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:439:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"decimalSeparator\":\".\",\"dateFormat\":\"Y-m-d\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32741,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5212','a:5:{s:4:\"code\";s:18:\"fixtures_job_yml16\";s:5:\"label\";s:18:\"Jobs data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:181:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"fixtures_job_yml16\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Jobs data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:181:\"{\"filePath\":\"\\/home\\/jjanvier\\/workspaces\\/phpstorm\\/akeneo\\/pim_master\\/pcd\\/features\\/Context\\/catalog\\/minimal\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\"}\";}}',NULL,1,'2016-08-04 14:28:48',0),(32742,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21001','a:1:{s:4:\"code\";s:5:\"af_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"af_ZA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32743,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21002','a:1:{s:4:\"code\";s:5:\"am_ET\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"am_ET\";}}',NULL,1,'2016-08-04 14:28:48',0),(32744,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21003','a:1:{s:4:\"code\";s:5:\"ar_AE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_AE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32745,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21004','a:1:{s:4:\"code\";s:5:\"ar_BH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_BH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32746,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21005','a:1:{s:4:\"code\";s:5:\"ar_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_DZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32747,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21006','a:1:{s:4:\"code\";s:5:\"ar_EG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32748,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21007','a:1:{s:4:\"code\";s:5:\"ar_IQ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_IQ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32749,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21008','a:1:{s:4:\"code\";s:5:\"ar_JO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_JO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32750,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21009','a:1:{s:4:\"code\";s:5:\"ar_KW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_KW\";}}',NULL,1,'2016-08-04 14:28:48',0),(32751,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21010','a:1:{s:4:\"code\";s:5:\"ar_LB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LB\";}}',NULL,1,'2016-08-04 14:28:48',0),(32752,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21011','a:1:{s:4:\"code\";s:5:\"ar_LY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32753,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21012','a:1:{s:4:\"code\";s:5:\"ar_MA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_MA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32754,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21013','a:1:{s:4:\"code\";s:6:\"arn_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"arn_CL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32755,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21014','a:1:{s:4:\"code\";s:5:\"ar_OM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_OM\";}}',NULL,1,'2016-08-04 14:28:48',0),(32756,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21015','a:1:{s:4:\"code\";s:5:\"ar_QA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_QA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32757,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21016','a:1:{s:4:\"code\";s:5:\"ar_SA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32758,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21017','a:1:{s:4:\"code\";s:5:\"ar_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32759,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21018','a:1:{s:4:\"code\";s:5:\"ar_TN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_TN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32760,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21019','a:1:{s:4:\"code\";s:5:\"ar_YE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_YE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32761,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21020','a:1:{s:4:\"code\";s:5:\"as_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"as_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32762,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21021','a:1:{s:4:\"code\";s:10:\"az_Cyrl_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Cyrl_AZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32763,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21022','a:1:{s:4:\"code\";s:10:\"az_Latn_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Latn_AZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32764,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21023','a:1:{s:4:\"code\";s:5:\"ba_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ba_RU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32765,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21024','a:1:{s:4:\"code\";s:5:\"be_BY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"be_BY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32766,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21025','a:1:{s:4:\"code\";s:5:\"bg_BG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bg_BG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32767,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21026','a:1:{s:4:\"code\";s:5:\"bn_BD\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_BD\";}}',NULL,1,'2016-08-04 14:28:48',0),(32768,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21027','a:1:{s:4:\"code\";s:5:\"bn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32769,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21028','a:1:{s:4:\"code\";s:5:\"bo_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bo_CN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32770,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21029','a:1:{s:4:\"code\";s:5:\"br_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"br_FR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32771,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21030','a:1:{s:4:\"code\";s:10:\"bs_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Cyrl_BA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32772,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21031','a:1:{s:4:\"code\";s:10:\"bs_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Latn_BA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32773,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21032','a:1:{s:4:\"code\";s:5:\"ca_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ca_ES\";}}',NULL,1,'2016-08-04 14:28:48',0),(32774,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21033','a:1:{s:4:\"code\";s:5:\"co_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"co_FR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32775,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21034','a:1:{s:4:\"code\";s:5:\"cs_CZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cs_CZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32776,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21035','a:1:{s:4:\"code\";s:5:\"cy_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cy_GB\";}}',NULL,1,'2016-08-04 14:28:48',0),(32777,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21036','a:1:{s:4:\"code\";s:5:\"da_DK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"da_DK\";}}',NULL,1,'2016-08-04 14:28:48',0),(32778,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21037','a:1:{s:4:\"code\";s:5:\"de_AT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_AT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32779,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21038','a:1:{s:4:\"code\";s:5:\"de_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_CH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32780,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21039','a:1:{s:4:\"code\";s:5:\"de_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32781,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21040','a:1:{s:4:\"code\";s:5:\"de_LI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32782,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21041','a:1:{s:4:\"code\";s:5:\"de_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32783,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21042','a:1:{s:4:\"code\";s:6:\"dsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"dsb_DE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32784,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21043','a:1:{s:4:\"code\";s:5:\"dv_MV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"dv_MV\";}}',NULL,1,'2016-08-04 14:28:48',0),(32785,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21044','a:1:{s:4:\"code\";s:5:\"el_GR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"el_GR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32786,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21045','a:1:{s:4:\"code\";s:6:\"en_029\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"en_029\";}}',NULL,1,'2016-08-04 14:28:48',0),(32787,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21046','a:1:{s:4:\"code\";s:5:\"en_AU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_AU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32788,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21047','a:1:{s:4:\"code\";s:5:\"en_BZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_BZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32789,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21048','a:1:{s:4:\"code\";s:5:\"en_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_CA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32790,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21049','a:1:{s:4:\"code\";s:5:\"en_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_GB\";}}',NULL,1,'2016-08-04 14:28:48',0),(32791,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21050','a:1:{s:4:\"code\";s:5:\"en_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32792,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21051','a:1:{s:4:\"code\";s:5:\"en_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32793,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21052','a:1:{s:4:\"code\";s:5:\"en_JM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_JM\";}}',NULL,1,'2016-08-04 14:28:48',0),(32794,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21053','a:1:{s:4:\"code\";s:5:\"en_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_MY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32795,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21054','a:1:{s:4:\"code\";s:5:\"en_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_NZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32796,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21055','a:1:{s:4:\"code\";s:5:\"en_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_PH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32797,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21056','a:1:{s:4:\"code\";s:5:\"en_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_SG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32798,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21057','a:1:{s:4:\"code\";s:5:\"en_TT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_TT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32799,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21058','a:1:{s:4:\"code\";s:5:\"en_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}}',NULL,1,'2016-08-04 14:28:48',0),(32800,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21059','a:1:{s:4:\"code\";s:5:\"en_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32801,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21060','a:1:{s:4:\"code\";s:5:\"en_ZW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZW\";}}',NULL,1,'2016-08-04 14:28:48',0),(32802,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21061','a:1:{s:4:\"code\";s:5:\"es_AR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_AR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32803,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21062','a:1:{s:4:\"code\";s:5:\"es_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_BO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32804,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21063','a:1:{s:4:\"code\";s:5:\"es_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32805,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21064','a:1:{s:4:\"code\";s:5:\"es_CO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32806,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21065','a:1:{s:4:\"code\";s:5:\"es_CR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32807,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21066','a:1:{s:4:\"code\";s:5:\"es_DO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_DO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32808,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21067','a:1:{s:4:\"code\";s:5:\"es_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_EC\";}}',NULL,1,'2016-08-04 14:28:48',0),(32809,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21068','a:1:{s:4:\"code\";s:5:\"es_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_ES\";}}',NULL,1,'2016-08-04 14:28:48',0),(32810,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21069','a:1:{s:4:\"code\";s:5:\"es_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_GT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32811,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21070','a:1:{s:4:\"code\";s:5:\"es_HN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_HN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32812,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21071','a:1:{s:4:\"code\";s:5:\"es_MX\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_MX\";}}',NULL,1,'2016-08-04 14:28:48',0),(32813,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21072','a:1:{s:4:\"code\";s:5:\"es_NI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_NI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32814,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21073','a:1:{s:4:\"code\";s:5:\"es_PA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32815,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21074','a:1:{s:4:\"code\";s:5:\"es_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32816,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21075','a:1:{s:4:\"code\";s:5:\"es_PR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32817,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21076','a:1:{s:4:\"code\";s:5:\"es_PY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32818,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21077','a:1:{s:4:\"code\";s:5:\"es_SV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_SV\";}}',NULL,1,'2016-08-04 14:28:48',0),(32819,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21078','a:1:{s:4:\"code\";s:5:\"es_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_US\";}}',NULL,1,'2016-08-04 14:28:48',0),(32820,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21079','a:1:{s:4:\"code\";s:5:\"es_UY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_UY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32821,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21080','a:1:{s:4:\"code\";s:5:\"es_VE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_VE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32822,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21081','a:1:{s:4:\"code\";s:5:\"et_EE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"et_EE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32823,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21082','a:1:{s:4:\"code\";s:5:\"eu_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"eu_ES\";}}',NULL,1,'2016-08-04 14:28:48',0),(32824,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21083','a:1:{s:4:\"code\";s:5:\"fa_IR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fa_IR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32825,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21084','a:1:{s:4:\"code\";s:5:\"fi_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fi_FI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32826,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21085','a:1:{s:4:\"code\";s:6:\"fil_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"fil_PH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32827,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21086','a:1:{s:4:\"code\";s:5:\"fo_FO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fo_FO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32828,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21087','a:1:{s:4:\"code\";s:5:\"fr_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_BE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32829,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21088','a:1:{s:4:\"code\";s:5:\"fr_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32830,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21089','a:1:{s:4:\"code\";s:5:\"fr_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32831,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21090','a:1:{s:4:\"code\";s:5:\"fr_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32832,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21091','a:1:{s:4:\"code\";s:5:\"fr_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_LU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32833,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21092','a:1:{s:4:\"code\";s:5:\"fr_MC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_MC\";}}',NULL,1,'2016-08-04 14:28:48',0),(32834,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21093','a:1:{s:4:\"code\";s:5:\"fy_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fy_NL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32835,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21094','a:1:{s:4:\"code\";s:5:\"ga_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ga_IE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32836,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21095','a:1:{s:4:\"code\";s:5:\"gd_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gd_GB\";}}',NULL,1,'2016-08-04 14:28:48',0),(32837,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21096','a:1:{s:4:\"code\";s:5:\"gl_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gl_ES\";}}',NULL,1,'2016-08-04 14:28:48',0),(32838,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21097','a:1:{s:4:\"code\";s:6:\"gsw_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"gsw_FR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32839,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21098','a:1:{s:4:\"code\";s:5:\"gu_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gu_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32840,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21099','a:1:{s:4:\"code\";s:10:\"ha_Latn_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"ha_Latn_NG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32841,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21100','a:1:{s:4:\"code\";s:5:\"he_IL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"he_IL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32842,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21101','a:1:{s:4:\"code\";s:5:\"hi_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hi_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32843,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21102','a:1:{s:4:\"code\";s:5:\"hr_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_BA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32844,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21103','a:1:{s:4:\"code\";s:5:\"hr_HR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_HR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32845,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21104','a:1:{s:4:\"code\";s:6:\"hsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"hsb_DE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32846,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21105','a:1:{s:4:\"code\";s:5:\"hu_HU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hu_HU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32847,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21106','a:1:{s:4:\"code\";s:5:\"hy_AM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hy_AM\";}}',NULL,1,'2016-08-04 14:28:48',0),(32848,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21107','a:1:{s:4:\"code\";s:5:\"id_ID\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"id_ID\";}}',NULL,1,'2016-08-04 14:28:48',0),(32849,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21108','a:1:{s:4:\"code\";s:5:\"ig_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ig_NG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32850,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21109','a:1:{s:4:\"code\";s:5:\"ii_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ii_CN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32851,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21110','a:1:{s:4:\"code\";s:5:\"is_IS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"is_IS\";}}',NULL,1,'2016-08-04 14:28:48',0),(32852,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21111','a:1:{s:4:\"code\";s:5:\"it_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_CH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32853,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21112','a:1:{s:4:\"code\";s:5:\"it_IT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_IT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32854,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21113','a:1:{s:4:\"code\";s:10:\"iu_Cans_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Cans_CA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32855,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21114','a:1:{s:4:\"code\";s:10:\"iu_Latn_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Latn_CA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32856,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21115','a:1:{s:4:\"code\";s:5:\"ja_JP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ja_JP\";}}',NULL,1,'2016-08-04 14:28:48',0),(32857,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21116','a:1:{s:4:\"code\";s:5:\"ka_GE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ka_GE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32858,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21117','a:1:{s:4:\"code\";s:5:\"kk_KZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kk_KZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32859,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21118','a:1:{s:4:\"code\";s:5:\"kl_GL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kl_GL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32860,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21119','a:1:{s:4:\"code\";s:5:\"km_KH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"km_KH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32861,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21120','a:1:{s:4:\"code\";s:5:\"kn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kn_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32862,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21121','a:1:{s:4:\"code\";s:6:\"kok_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"kok_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32863,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21122','a:1:{s:4:\"code\";s:5:\"ko_KR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ko_KR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32864,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21123','a:1:{s:4:\"code\";s:5:\"ky_KG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ky_KG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32865,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21124','a:1:{s:4:\"code\";s:5:\"lb_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lb_LU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32866,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21125','a:1:{s:4:\"code\";s:5:\"lo_LA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lo_LA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32867,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21126','a:1:{s:4:\"code\";s:5:\"lt_LT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lt_LT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32868,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21127','a:1:{s:4:\"code\";s:5:\"lv_LV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lv_LV\";}}',NULL,1,'2016-08-04 14:28:48',0),(32869,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21128','a:1:{s:4:\"code\";s:5:\"mi_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mi_NZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32870,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21129','a:1:{s:4:\"code\";s:5:\"mk_MK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mk_MK\";}}',NULL,1,'2016-08-04 14:28:48',0),(32871,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21130','a:1:{s:4:\"code\";s:5:\"ml_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ml_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32872,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21131','a:1:{s:4:\"code\";s:5:\"mn_MN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mn_MN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32873,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21132','a:1:{s:4:\"code\";s:10:\"mn_Mong_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"mn_Mong_CN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32874,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21133','a:1:{s:4:\"code\";s:6:\"moh_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"moh_CA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32875,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21134','a:1:{s:4:\"code\";s:5:\"mr_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mr_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32876,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21135','a:1:{s:4:\"code\";s:5:\"ms_BN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_BN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32877,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21136','a:1:{s:4:\"code\";s:5:\"ms_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_MY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32878,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21137','a:1:{s:4:\"code\";s:5:\"mt_MT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mt_MT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32879,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21138','a:1:{s:4:\"code\";s:5:\"nb_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nb_NO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32880,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21139','a:1:{s:4:\"code\";s:5:\"ne_NP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ne_NP\";}}',NULL,1,'2016-08-04 14:28:48',0),(32881,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21140','a:1:{s:4:\"code\";s:5:\"nl_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_BE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32882,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21141','a:1:{s:4:\"code\";s:5:\"nl_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_NL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32883,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21142','a:1:{s:4:\"code\";s:5:\"nn_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nn_NO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32884,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21143','a:1:{s:4:\"code\";s:6:\"nso_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"nso_ZA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32885,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21144','a:1:{s:4:\"code\";s:5:\"oc_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"oc_FR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32886,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21145','a:1:{s:4:\"code\";s:5:\"or_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"or_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32887,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21146','a:1:{s:4:\"code\";s:5:\"pa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pa_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32888,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21147','a:1:{s:4:\"code\";s:5:\"pl_PL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pl_PL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32889,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21148','a:1:{s:4:\"code\";s:6:\"prs_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"prs_AF\";}}',NULL,1,'2016-08-04 14:28:48',0),(32890,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21149','a:1:{s:4:\"code\";s:5:\"ps_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ps_AF\";}}',NULL,1,'2016-08-04 14:28:48',0),(32891,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21150','a:1:{s:4:\"code\";s:5:\"pt_BR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_BR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32892,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21151','a:1:{s:4:\"code\";s:5:\"pt_PT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_PT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32893,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21152','a:1:{s:4:\"code\";s:6:\"qut_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"qut_GT\";}}',NULL,1,'2016-08-04 14:28:48',0),(32894,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21153','a:1:{s:4:\"code\";s:6:\"quz_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_BO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32895,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21154','a:1:{s:4:\"code\";s:6:\"quz_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_EC\";}}',NULL,1,'2016-08-04 14:28:48',0),(32896,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21155','a:1:{s:4:\"code\";s:6:\"quz_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_PE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32897,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21156','a:1:{s:4:\"code\";s:5:\"rm_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rm_CH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32898,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21157','a:1:{s:4:\"code\";s:5:\"ro_RO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ro_RO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32899,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21158','a:1:{s:4:\"code\";s:5:\"ru_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ru_RU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32900,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21159','a:1:{s:4:\"code\";s:5:\"rw_RW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rw_RW\";}}',NULL,1,'2016-08-04 14:28:48',0),(32901,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21160','a:1:{s:4:\"code\";s:6:\"sah_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sah_RU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32902,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21161','a:1:{s:4:\"code\";s:5:\"sa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sa_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32903,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21162','a:1:{s:4:\"code\";s:5:\"se_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_FI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32904,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21163','a:1:{s:4:\"code\";s:5:\"se_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_NO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32905,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21164','a:1:{s:4:\"code\";s:5:\"se_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_SE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32906,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21165','a:1:{s:4:\"code\";s:5:\"si_LK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"si_LK\";}}',NULL,1,'2016-08-04 14:28:48',0),(32907,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21166','a:1:{s:4:\"code\";s:5:\"sk_SK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sk_SK\";}}',NULL,1,'2016-08-04 14:28:48',0),(32908,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21167','a:1:{s:4:\"code\";s:5:\"sl_SI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sl_SI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32909,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21168','a:1:{s:4:\"code\";s:6:\"sma_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_NO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32910,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21169','a:1:{s:4:\"code\";s:6:\"sma_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_SE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32911,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21170','a:1:{s:4:\"code\";s:6:\"smj_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_NO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32912,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21171','a:1:{s:4:\"code\";s:6:\"smj_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_SE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32913,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21172','a:1:{s:4:\"code\";s:6:\"smn_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smn_FI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32914,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21173','a:1:{s:4:\"code\";s:6:\"sms_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sms_FI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32915,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21174','a:1:{s:4:\"code\";s:5:\"sq_AL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sq_AL\";}}',NULL,1,'2016-08-04 14:28:48',0),(32916,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21175','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_BA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32917,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21176','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_CS\";}}',NULL,1,'2016-08-04 14:28:48',0),(32918,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21177','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_ME\";}}',NULL,1,'2016-08-04 14:28:48',0),(32919,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21178','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_RS\";}}',NULL,1,'2016-08-04 14:28:48',0),(32920,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21179','a:1:{s:4:\"code\";s:10:\"sr_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_BA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32921,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21180','a:1:{s:4:\"code\";s:10:\"sr_Latn_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_CS\";}}',NULL,1,'2016-08-04 14:28:48',0),(32922,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21181','a:1:{s:4:\"code\";s:10:\"sr_Latn_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_ME\";}}',NULL,1,'2016-08-04 14:28:48',0),(32923,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21182','a:1:{s:4:\"code\";s:10:\"sr_Latn_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_RS\";}}',NULL,1,'2016-08-04 14:28:48',0),(32924,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21183','a:1:{s:4:\"code\";s:5:\"sv_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_FI\";}}',NULL,1,'2016-08-04 14:28:48',0),(32925,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21184','a:1:{s:4:\"code\";s:5:\"sv_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_SE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32926,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21185','a:1:{s:4:\"code\";s:5:\"sw_KE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sw_KE\";}}',NULL,1,'2016-08-04 14:28:48',0),(32927,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21186','a:1:{s:4:\"code\";s:6:\"syr_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"syr_SY\";}}',NULL,1,'2016-08-04 14:28:48',0),(32928,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21187','a:1:{s:4:\"code\";s:5:\"ta_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ta_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32929,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21188','a:1:{s:4:\"code\";s:5:\"te_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"te_IN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32930,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21189','a:1:{s:4:\"code\";s:10:\"tg_Cyrl_TJ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"tg_Cyrl_TJ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32931,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21190','a:1:{s:4:\"code\";s:5:\"th_TH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"th_TH\";}}',NULL,1,'2016-08-04 14:28:48',0),(32932,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21191','a:1:{s:4:\"code\";s:5:\"tk_TM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tk_TM\";}}',NULL,1,'2016-08-04 14:28:48',0),(32933,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21192','a:1:{s:4:\"code\";s:5:\"tn_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tn_ZA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32934,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21193','a:1:{s:4:\"code\";s:5:\"tr_TR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tr_TR\";}}',NULL,1,'2016-08-04 14:28:48',0),(32935,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21194','a:1:{s:4:\"code\";s:5:\"tt_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tt_RU\";}}',NULL,1,'2016-08-04 14:28:48',0),(32936,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21195','a:1:{s:4:\"code\";s:11:\"tzm_Latn_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"tzm_Latn_DZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32937,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21196','a:1:{s:4:\"code\";s:5:\"ug_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ug_CN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32938,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21197','a:1:{s:4:\"code\";s:5:\"uk_UA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"uk_UA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32939,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21198','a:1:{s:4:\"code\";s:5:\"ur_PK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ur_PK\";}}',NULL,1,'2016-08-04 14:28:48',0),(32940,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21199','a:1:{s:4:\"code\";s:10:\"uz_Cyrl_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Cyrl_UZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32941,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21200','a:1:{s:4:\"code\";s:10:\"uz_Latn_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Latn_UZ\";}}',NULL,1,'2016-08-04 14:28:48',0),(32942,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21201','a:1:{s:4:\"code\";s:5:\"vi_VN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"vi_VN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32943,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21202','a:1:{s:4:\"code\";s:5:\"wo_SN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"wo_SN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32944,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21203','a:1:{s:4:\"code\";s:5:\"xh_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"xh_ZA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32945,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21204','a:1:{s:4:\"code\";s:5:\"yo_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"yo_NG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32946,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21205','a:1:{s:4:\"code\";s:5:\"zh_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_CN\";}}',NULL,1,'2016-08-04 14:28:48',0),(32947,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21206','a:1:{s:4:\"code\";s:5:\"zh_HK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_HK\";}}',NULL,1,'2016-08-04 14:28:48',0),(32948,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21207','a:1:{s:4:\"code\";s:5:\"zh_MO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_MO\";}}',NULL,1,'2016-08-04 14:28:48',0),(32949,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21208','a:1:{s:4:\"code\";s:5:\"zh_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_SG\";}}',NULL,1,'2016-08-04 14:28:48',0),(32950,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21209','a:1:{s:4:\"code\";s:5:\"zh_TW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_TW\";}}',NULL,1,'2016-08-04 14:28:48',0),(32951,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Locale','21210','a:1:{s:4:\"code\";s:5:\"zu_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zu_ZA\";}}',NULL,1,'2016-08-04 14:28:48',0),(32952,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Category','895','a:3:{s:4:\"code\";s:6:\"master\";s:6:\"parent\";s:0:\"\";s:11:\"label-en_US\";s:14:\"Master catalog\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"master\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Master catalog\";}}',NULL,1,'2016-08-04 14:28:49',0),(32953,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','593','a:5:{s:4:\"code\";s:5:\"other\";s:9:\"sortOrder\";i:100;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Other\";s:11:\"label-fr_FR\";s:5:\"Autre\";}','a:4:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:9:\"sortOrder\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:100;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Autre\";}}',NULL,1,'2016-08-04 14:28:49',0),(32954,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Channel','209','a:6:{s:4:\"code\";s:9:\"ecommerce\";s:5:\"label\";s:7:\"Default\";s:10:\"currencies\";s:3:\"USD\";s:7:\"locales\";s:5:\"en_US\";s:8:\"category\";s:6:\"master\";s:16:\"conversion_units\";s:0:\"\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"ecommerce\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Default\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"USD\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"master\";}}',NULL,1,'2016-08-04 14:28:49',0),(32955,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','428','a:3:{s:4:\"code\";s:6:\"X_SELL\";s:11:\"label-en_US\";s:10:\"Cross sell\";s:11:\"label-fr_FR\";s:14:\"Vente croisée\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"X_SELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Vente croisée\";}}',NULL,1,'2016-08-04 14:28:49',0),(32956,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','429','a:3:{s:4:\"code\";s:6:\"UPSELL\";s:11:\"label-en_US\";s:6:\"Upsell\";s:11:\"label-fr_FR\";s:16:\"Vente incitative\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"UPSELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Vente incitative\";}}',NULL,1,'2016-08-04 14:28:49',0),(32957,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','430','a:3:{s:4:\"code\";s:12:\"SUBSTITUTION\";s:11:\"label-en_US\";s:12:\"Substitution\";s:11:\"label-fr_FR\";s:12:\"Remplacement\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"SUBSTITUTION\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Substitution\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Remplacement\";}}',NULL,1,'2016-08-04 14:28:49',0),(32958,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','431','a:3:{s:4:\"code\";s:4:\"PACK\";s:11:\"label-en_US\";s:4:\"Pack\";s:11:\"label-fr_FR\";s:4:\"Pack\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"PACK\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}}',NULL,1,'2016-08-04 14:28:49',0),(32959,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2523','a:28:{s:4:\"type\";s:22:\"pim_catalog_identifier\";s:4:\"code\";s:3:\"sku\";s:11:\"label-en_US\";s:3:\"SKU\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:1;}','a:8:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"pim_catalog_identifier\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:6:\"unique\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}s:8:\"required\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}}',NULL,1,'2016-08-04 14:28:49',0),(32960,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','593','a:5:{s:4:\"code\";s:5:\"other\";s:9:\"sortOrder\";i:100;s:10:\"attributes\";s:3:\"sku\";s:11:\"label-en_US\";s:5:\"Other\";s:11:\"label-fr_FR\";s:5:\"Autre\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}}',NULL,2,'2016-08-04 14:28:49',0),(32961,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5213','a:5:{s:4:\"code\";s:20:\"update_product_value\";s:5:\"label\";s:20:\"Mass update products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"update_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass update products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2016-08-04 14:28:49',0),(32962,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5214','a:5:{s:4:\"code\";s:17:\"add_product_value\";s:5:\"label\";s:24:\"Mass add products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"add_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2016-08-04 14:28:49',0),(32963,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5215','a:5:{s:4:\"code\";s:22:\"edit_common_attributes\";s:5:\"label\";s:35:\"Mass edit common product attributes\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"edit_common_attributes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:35:\"Mass edit common product attributes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2016-08-04 14:28:49',0),(32964,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5216','a:5:{s:4:\"code\";s:26:\"set_attribute_requirements\";s:5:\"label\";s:33:\"Set family attribute requirements\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"set_attribute_requirements\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Set family attribute requirements\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2016-08-04 14:28:49',0),(32965,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5217','a:5:{s:4:\"code\";s:20:\"add_to_variant_group\";s:5:\"label\";s:34:\"Mass add products to variant group\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"add_to_variant_group\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"Mass add products to variant group\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2016-08-04 14:28:49',0),(32966,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5218','a:5:{s:4:\"code\";s:24:\"csv_product_quick_export\";s:5:\"label\";s:24:\"CSV product quick export\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:199:\"{\"filePath\":\"\\/tmp\\/products_export_%locale%_%scope%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"csv_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"CSV product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:199:\"{\"filePath\":\"\\/tmp\\/products_export_%locale%_%scope%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}}',NULL,1,'2016-08-04 14:28:49',0),(32967,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5219','a:5:{s:4:\"code\";s:37:\"csv_product_grid_context_quick_export\";s:5:\"label\";s:37:\"CSV product quick export grid context\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:212:\"{\"filePath\":\"\\/tmp\\/products_export_grid_context_%locale%_%scope%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"csv_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"CSV product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:212:\"{\"filePath\":\"\\/tmp\\/products_export_grid_context_%locale%_%scope%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}}',NULL,1,'2016-08-04 14:28:49',0),(32968,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5220','a:5:{s:4:\"code\";s:25:\"xlsx_product_quick_export\";s:5:\"label\";s:25:\"XLSX product quick export\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:188:\"{\"filePath\":\"\\/tmp\\/products_export_%locale%_%scope%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"xlsx_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"XLSX product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:188:\"{\"filePath\":\"\\/tmp\\/products_export_%locale%_%scope%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}}',NULL,1,'2016-08-04 14:28:49',0),(32969,'admin','Akeneo\\Component\\Batch\\Model\\JobInstance','5221','a:5:{s:4:\"code\";s:38:\"xlsx_product_grid_context_quick_export\";s:5:\"label\";s:38:\"XLSX product quick export grid context\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:201:\"{\"filePath\":\"\\/tmp\\/products_export_grid_context_%locale%_%scope%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"xlsx_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"XLSX product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:201:\"{\"filePath\":\"\\/tmp\\/products_export_grid_context_%locale%_%scope%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"filters\":null,\"mainContext\":null,\"selected_properties\":null,\"with_media\":true}\";}}',NULL,1,'2016-08-04 14:28:49',0),(32970,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Category','899','a:2:{s:4:\"code\";s:9:\"categoryB\";s:6:\"parent\";s:6:\"master\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"categoryB\";}s:6:\"parent\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"master\";}}',NULL,1,'2016-08-04 14:28:49',0),(32971,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','594','a:3:{s:4:\"code\";s:15:\"attributeGroupA\";s:9:\"sortOrder\";i:0;s:10:\"attributes\";s:0:\"\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}}',NULL,1,'2016-08-04 14:28:49',0),(32972,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','595','a:3:{s:4:\"code\";s:15:\"attributeGroupB\";s:9:\"sortOrder\";i:0;s:10:\"attributes\";s:0:\"\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}}',NULL,1,'2016-08-04 14:28:49',0),(32973,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2523','a:28:{s:4:\"type\";s:22:\"pim_catalog_identifier\";s:4:\"code\";s:3:\"sku\";s:11:\"label-en_US\";s:3:\"SKU\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:1;}','a:1:{s:5:\"group\";a:2:{s:3:\"old\";s:5:\"other\";s:3:\"new\";s:15:\"attributeGroupA\";}}',NULL,2,'2016-08-04 14:28:49',0),(32974,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2524','a:27:{s:4:\"type\";s:16:\"pim_catalog_date\";s:4:\"code\";s:6:\"a_date\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_date\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"a_date\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32975,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2525','a:27:{s:4:\"type\";s:16:\"pim_catalog_file\";s:4:\"code\";s:6:\"a_file\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_file\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"a_file\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32976,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2526','a:27:{s:4:\"type\";s:17:\"pim_catalog_image\";s:4:\"code\";s:8:\"an_image\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"an_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32977,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2527','a:27:{s:4:\"type\";s:18:\"pim_catalog_metric\";s:4:\"code\";s:8:\"a_metric\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:5:\"Power\";s:19:\"default_metric_unit\";s:8:\"KILOWATT\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:1;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:8:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_metric\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"a_metric\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:13:\"metric_family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Power\";}s:19:\"default_metric_unit\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"KILOWATT\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:16:\"negative_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32978,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2528','a:27:{s:4:\"type\";s:23:\"pim_catalog_multiselect\";s:4:\"code\";s:14:\"a_multi_select\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"pim_catalog_multiselect\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"a_multi_select\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32979,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2529','a:27:{s:4:\"type\";s:18:\"pim_catalog_number\";s:4:\"code\";s:14:\"a_number_float\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"a_number_float\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32980,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2530','a:27:{s:4:\"type\";s:18:\"pim_catalog_number\";s:4:\"code\";s:23:\"a_number_float_negative\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:1;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:6:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"a_number_float_negative\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:16:\"negative_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32981,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2531','a:27:{s:4:\"type\";s:18:\"pim_catalog_number\";s:4:\"code\";s:16:\"a_number_integer\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"pim_catalog_number\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"a_number_integer\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32982,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2532','a:27:{s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:4:\"code\";s:7:\"a_price\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"a_price\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32983,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2533','a:27:{s:4:\"type\";s:30:\"pim_reference_data_multiselect\";s:4:\"code\";s:23:\"a_ref_data_multi_select\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";s:7:\"fabrics\";s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"pim_reference_data_multiselect\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"a_ref_data_multi_select\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"fabrics\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32984,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2534','a:27:{s:4:\"type\";s:31:\"pim_reference_data_simpleselect\";s:4:\"code\";s:24:\"a_ref_data_simple_select\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";s:5:\"color\";s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"pim_reference_data_simpleselect\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"a_ref_data_simple_select\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:19:\"reference_data_name\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"color\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:49',0),(32985,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2535','a:27:{s:4:\"type\";s:24:\"pim_catalog_simpleselect\";s:4:\"code\";s:15:\"a_simple_select\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"pim_catalog_simpleselect\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"a_simple_select\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:50',0),(32986,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2536','a:27:{s:4:\"type\";s:16:\"pim_catalog_text\";s:4:\"code\";s:6:\"a_text\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"pim_catalog_text\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"a_text\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:50',0),(32987,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2537','a:27:{s:4:\"type\";s:20:\"pim_catalog_textarea\";s:4:\"code\";s:11:\"a_text_area\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"a_text_area\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:50',0),(32988,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2538','a:27:{s:4:\"type\";s:19:\"pim_catalog_boolean\";s:4:\"code\";s:8:\"a_yes_no\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:4:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"pim_catalog_boolean\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"a_yes_no\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:50',0),(32989,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2539','a:27:{s:4:\"type\";s:17:\"pim_catalog_image\";s:4:\"code\";s:19:\"a_localizable_image\";s:5:\"group\";s:15:\"attributeGroupB\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:1;s:5:\"scope\";s:6:\"Global\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"pim_catalog_image\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"a_localizable_image\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupB\";}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}}',NULL,1,'2016-08-04 14:28:50',0),(32990,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2540','a:27:{s:4:\"type\";s:28:\"pim_catalog_price_collection\";s:4:\"code\";s:16:\"a_scopable_price\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:1;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:0;s:5:\"scope\";s:7:\"Channel\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"pim_catalog_price_collection\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"a_scopable_price\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:16:\"decimals_allowed\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2016-08-04 14:28:50',0),(32991,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','2541','a:27:{s:4:\"type\";s:20:\"pim_catalog_textarea\";s:4:\"code\";s:34:\"a_localized_and_scopable_text_area\";s:5:\"group\";s:15:\"attributeGroupA\";s:6:\"unique\";b:0;s:22:\"useable_as_grid_filter\";b:0;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";s:0:\"\";s:19:\"default_metric_unit\";s:0:\"\";s:19:\"reference_data_name\";N;s:17:\"available_locales\";N;s:14:\"max_characters\";s:0:\"\";s:15:\"validation_rule\";s:0:\"\";s:17:\"validation_regexp\";s:0:\"\";s:15:\"wysiwyg_enabled\";b:0;s:10:\"number_min\";s:0:\"\";s:10:\"number_max\";s:0:\"\";s:16:\"decimals_allowed\";b:0;s:16:\"negative_allowed\";b:0;s:8:\"date_min\";s:0:\"\";s:8:\"date_max\";s:0:\"\";s:13:\"max_file_size\";s:0:\"\";s:20:\"minimum_input_length\";i:0;s:10:\"sort_order\";i:0;s:11:\"localizable\";b:1;s:5:\"scope\";s:7:\"Channel\";s:7:\"options\";s:0:\"\";s:8:\"required\";b:0;}','a:5:{s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"pim_catalog_textarea\";}s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"a_localized_and_scopable_text_area\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"attributeGroupA\";}s:11:\"localizable\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"Channel\";}}',NULL,1,'2016-08-04 14:28:50',0),(32992,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Group','239','a:4:{s:4:\"code\";s:8:\"variantA\";s:4:\"type\";s:7:\"VARIANT\";s:4:\"axis\";s:15:\"a_simple_select\";s:11:\"label-en_US\";s:0:\"\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"variantA\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"VARIANT\";}s:4:\"axis\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"a_simple_select\";}}',NULL,1,'2016-08-04 14:28:50',0),(32993,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Group','240','a:3:{s:4:\"code\";s:6:\"groupA\";s:4:\"type\";s:7:\"RELATED\";s:11:\"label-en_US\";s:0:\"\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"groupA\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"RELATED\";}}',NULL,1,'2016-08-04 14:28:50',0),(32994,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Group','241','a:3:{s:4:\"code\";s:6:\"groupB\";s:4:\"type\";s:7:\"RELATED\";s:11:\"label-en_US\";s:0:\"\";}','a:2:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"groupB\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"RELATED\";}}',NULL,1,'2016-08-04 14:28:50',0),(32995,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Channel','210','a:6:{s:4:\"code\";s:6:\"tablet\";s:5:\"label\";s:6:\"Tablet\";s:10:\"currencies\";s:3:\"EUR\";s:7:\"locales\";s:17:\"de_DE,en_US,fr_FR\";s:8:\"category\";s:6:\"master\";s:16:\"conversion_units\";s:0:\"\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"tablet\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Tablet\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"de_DE,en_US,fr_FR\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"master\";}}',NULL,1,'2016-08-04 14:28:50',0),(32996,'admin','Pim\\Bundle\\CatalogBundle\\Entity\\Family','466','a:5:{s:4:\"code\";s:7:\"familyA\";s:10:\"attributes\";s:279:\"a_date,a_file,a_localizable_image,a_localized_and_scopable_text_area,a_metric,a_multi_select,a_number_float,a_number_float_negative,a_number_integer,a_price,a_ref_data_multi_select,a_ref_data_simple_select,a_scopable_price,a_simple_select,a_text,a_text_area,a_yes_no,an_image,sku\";s:18:\"attribute_as_label\";s:3:\"sku\";s:22:\"requirements-ecommerce\";s:279:\"a_date,a_file,a_localizable_image,a_localized_and_scopable_text_area,a_metric,a_multi_select,a_number_float,a_number_float_negative,a_number_integer,a_price,a_ref_data_multi_select,a_ref_data_simple_select,a_scopable_price,a_simple_select,a_text,a_text_area,a_yes_no,an_image,sku\";s:19:\"requirements-tablet\";s:279:\"a_date,a_file,a_localizable_image,a_localized_and_scopable_text_area,a_metric,a_multi_select,a_number_float,a_number_float_negative,a_number_integer,a_price,a_ref_data_multi_select,a_ref_data_simple_select,a_scopable_price,a_simple_select,a_text,a_text_area,a_yes_no,an_image,sku\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"familyA\";}s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:279:\"a_date,a_file,a_localizable_image,a_localized_and_scopable_text_area,a_metric,a_multi_select,a_number_float,a_number_float_negative,a_number_integer,a_price,a_ref_data_multi_select,a_ref_data_simple_select,a_scopable_price,a_simple_select,a_text,a_text_area,a_yes_no,an_image,sku\";}s:18:\"attribute_as_label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:22:\"requirements-ecommerce\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:279:\"a_date,a_file,a_localizable_image,a_localized_and_scopable_text_area,a_metric,a_multi_select,a_number_float,a_number_float_negative,a_number_integer,a_price,a_ref_data_multi_select,a_ref_data_simple_select,a_scopable_price,a_simple_select,a_text,a_text_area,a_yes_no,an_image,sku\";}s:19:\"requirements-tablet\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:279:\"a_date,a_file,a_localizable_image,a_localized_and_scopable_text_area,a_metric,a_multi_select,a_number_float,a_number_float_negative,a_number_integer,a_price,a_ref_data_multi_select,a_ref_data_simple_select,a_scopable_price,a_simple_select,a_text,a_text_area,a_yes_no,an_image,sku\";}}',NULL,1,'2016-08-04 14:28:50',0),(32997,'admin','Pim\\Component\\Catalog\\Model\\Product','47','a:5:{s:3:\"sku\";s:3:\"bar\";s:6:\"family\";s:0:\"\";s:6:\"groups\";s:0:\"\";s:10:\"categories\";s:0:\"\";s:7:\"enabled\";i:0;}','a:1:{s:3:\"sku\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"bar\";}}',NULL,1,'2016-08-04 14:28:51',0),(32998,'admin','Pim\\Component\\Catalog\\Model\\Product','48','a:5:{s:3:\"sku\";s:3:\"baz\";s:6:\"family\";s:0:\"\";s:6:\"groups\";s:0:\"\";s:10:\"categories\";s:0:\"\";s:7:\"enabled\";i:1;}','a:2:{s:3:\"sku\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"baz\";}s:7:\"enabled\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}}',NULL,1,'2016-08-04 14:28:51',0),(32999,'admin','Pim\\Component\\Catalog\\Model\\Product','49','a:5:{s:3:\"sku\";s:3:\"foo\";s:6:\"family\";s:7:\"familyA\";s:6:\"groups\";s:0:\"\";s:10:\"categories\";s:0:\"\";s:7:\"enabled\";i:1;}','a:3:{s:3:\"sku\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"foo\";}s:6:\"family\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"familyA\";}s:7:\"enabled\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}}',NULL,1,'2016-08-04 14:28:51',0);
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

-- Dump completed on 2016-08-04 16:51:35
