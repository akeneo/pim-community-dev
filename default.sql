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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES (3,'(root)');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES (13,3,5,7,NULL,2,1,1,'all',0,0),(14,3,6,7,NULL,2,1056964608,1,'all',0,0),(15,3,5,8,NULL,1,1,1,'all',0,0),(16,3,6,8,NULL,1,1056964608,1,'all',0,0),(17,3,5,9,NULL,0,1,1,'all',0,0),(18,3,6,9,NULL,0,1056964608,1,'all',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES (5,NULL,3,'action',1),(6,NULL,3,'entity',1);
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
INSERT INTO `acl_object_identity_ancestors` VALUES (5,5),(6,6);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
INSERT INTO `acl_security_identities` VALUES (7,'ROLE_ADMINISTRATOR',0),(8,'ROLE_CATALOG_MANAGER',0),(9,'ROLE_USER',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akeneo_batch_job_instance`
--

LOCK TABLES `akeneo_batch_job_instance` WRITE;
/*!40000 ALTER TABLE `akeneo_batch_job_instance` DISABLE KEYS */;
INSERT INTO `akeneo_batch_job_instance` VALUES (109,'add_product_value','Mass add products values','add_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(110,'update_product_value','Mass update products','update_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(111,'remove_product_value','Mass remove products values','remove_product_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(112,'move_to_category','Mass move to categories','move_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(113,'add_association','Mass associate products','add_association',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(114,'add_to_category','Mass add to categories','add_to_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(115,'remove_from_category','Mass remove from categories','remove_from_category',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(116,'add_to_group','Mass add product to group','add_to_group',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(117,'edit_common_attributes','Mass edit product attributes','edit_common_attributes',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(118,'add_attribute_value','Mass add attribute value','add_attribute_value',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(119,'set_attribute_requirements','Set family attribute requirements','set_attribute_requirements',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(120,'delete_products_and_product_models','Mass delete products','delete_products_and_product_models',0,'Akeneo Mass Edit Connector','a:0:{}','mass_delete'),(121,'add_to_existing_product_model','Add to existing product model','add_to_existing_product_model',0,'Akeneo Mass Edit Connector','a:0:{}','mass_edit'),(122,'csv_product_quick_export','CSV product quick export','csv_product_quick_export',0,'Akeneo CSV Connector','a:14:{s:8:\"filePath\";s:42:\"/tmp/php/export_%job_label%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:32:\"/tmp/1_products-quick-export.csv\";s:20:\"filePathProductModel\";s:38:\"/tmp/2_product-models-quick-export.csv\";}','quick_export'),(123,'csv_product_grid_context_quick_export','CSV product quick export grid context','csv_product_grid_context_quick_export',0,'Akeneo CSV Connector','a:14:{s:8:\"filePath\";s:42:\"/tmp/php/export_%job_label%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:56:\"/tmp/1_products_export_grid_context_%locale%_%scope%.csv\";s:20:\"filePathProductModel\";s:62:\"/tmp/2_product_models_export_grid_context_%locale%_%scope%.csv\";}','quick_export'),(124,'xlsx_product_quick_export','XLSX product quick export','xlsx_product_quick_export',0,'Akeneo XLSX Connector','a:13:{s:8:\"filePath\";s:43:\"/tmp/php/export_%job_label%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:44:\"/tmp/1_products_export_%locale%_%scope%.xlsx\";s:20:\"filePathProductModel\";s:50:\"/tmp/2_product_models_export_%locale%_%scope%.xlsx\";}','quick_export'),(125,'xlsx_product_grid_context_quick_export','XLSX product quick export grid context','xlsx_product_grid_context_quick_export',0,'Akeneo XLSX Connector','a:13:{s:8:\"filePath\";s:43:\"/tmp/php/export_%job_label%_%datetime%.xlsx\";s:10:\"withHeader\";b:1;s:12:\"linesPerFile\";i:10000;s:14:\"user_to_notify\";N;s:21:\"is_user_authenticated\";b:0;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;s:6:\"locale\";N;s:5:\"scope\";N;s:9:\"ui_locale\";N;s:15:\"filePathProduct\";s:57:\"/tmp/1_products_export_grid_context_%locale%_%scope%.xlsx\";s:20:\"filePathProductModel\";s:63:\"/tmp/2_product_models_export_grid_context_%locale%_%scope%.xlsx\";}','quick_export'),(126,'csv_default_product_import','CSV default product import','csv_product_import',0,'Akeneo CSV Connector','a:0:{}','import'),(127,'compute_product_models_descendants','Compute product models descendants','compute_product_models_descendants',0,'internal','a:0:{}','compute_product_models_descendants'),(128,'compute_completeness_of_products_family','compute completeness of products family','compute_completeness_of_products_family',0,'internal','a:0:{}','compute_completeness_of_products_family'),(129,'compute_family_variant_structure_changes','Compute variant structure changes','compute_family_variant_structure_changes',0,'internal','a:0:{}','compute_family_variant_structure_changes');
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
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_group`
--

LOCK TABLES `oro_access_group` WRITE;
/*!40000 ALTER TABLE `oro_access_group` DISABLE KEYS */;
INSERT INTO `oro_access_group` VALUES (12,'All'),(9,'IT support'),(10,'Manager'),(11,'Redactor');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_access_role`
--

LOCK TABLES `oro_access_role` WRITE;
/*!40000 ALTER TABLE `oro_access_role` DISABLE KEYS */;
INSERT INTO `oro_access_role` VALUES (7,'ROLE_ADMINISTRATOR','Administrator'),(8,'ROLE_CATALOG_MANAGER','Catalog manager'),(9,'ROLE_USER','User');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oro_user`
--

LOCK TABLES `oro_user` WRITE;
/*!40000 ALTER TABLE `oro_user` DISABLE KEYS */;
INSERT INTO `oro_user` VALUES (8,478,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,NULL,1,'53ak10artdkwkcosk8ssc4wcckc0w44','dsvJ0EUn9ZPHQBXbZ6uYB372+VlqqnnxjpNGkuj2osJpMVk4O5htkl1UmBK8qLLPzqJ1avCafkiLX6kRhvh8Wg==',NULL,NULL,NULL,0,'2018-05-30 13:22:06','2018-05-30 13:22:06','[]',0,NULL,'UTC',478,5,32),(9,478,'Julia','Julia@example.com',NULL,'Julia',NULL,'Stark',NULL,NULL,NULL,1,'fqad8hmijlkwwwg8scwgkccggokks0k','HyOYKpY1hkelyLG6i0tZsDew+Yr0joIqCStq5qs2S+VvAY/QK9TNr2s7VUqZc1uvYwnU3IcNCW9yfJelwAvOMQ==',NULL,NULL,NULL,0,'2018-05-30 13:22:06','2018-05-30 13:22:06','[]',0,NULL,'America/New_York',478,5,32),(10,478,'Peter','Peter@example.com',NULL,'Peter',NULL,'Williams',NULL,NULL,NULL,1,'3mt99ysp3nuoss0k8kkck0440c8ocsk','CQgHxPoBaDP8Kb8JH0NG4PXzmjrMeFiu0CVq96wRykPiXCswmbZ78qp69wzq7sOfHSvzQVE+lqKpfL+4A7QPvg==',NULL,NULL,NULL,0,'2018-05-30 13:22:06','2018-05-30 13:22:06','[]',0,NULL,'UTC',478,5,32),(11,478,'Mary','Mary@example.com',NULL,'Mary',NULL,'Smith',NULL,NULL,NULL,1,'p1xidbe65kgo88oko4sscsww8k8400c','sDDp65aREASApcx0qXK35I1N+5QER5XKPGsAh+52bj1g19rULB+f+Lu2q9O5UURf5qJKiyuxyu020UjkCWK0ug==',NULL,NULL,NULL,0,'2018-05-30 13:22:06','2018-05-30 13:22:06','[]',0,NULL,'UTC',478,5,32),(12,478,'Sandra','Sandra@example.com',NULL,'Sandra',NULL,'Harvey',NULL,NULL,NULL,1,'ozud6rqrt1cwwgws08wcccckcskk8gc','2ZsRo9dKb/ktLCdbGMUPclPLYjOIn/qxgrXsI6O0S3u7aHkh7LTsyAtIUWTW3ZrVlw+Mk4v6DsR9onO+GO3exg==',NULL,NULL,NULL,0,'2018-05-30 13:22:06','2018-05-30 13:22:06','[]',0,NULL,'UTC',478,6,32),(13,510,'Julien','Julien@example.com',NULL,'Julien',NULL,'Février',NULL,NULL,NULL,1,'sr3sff5i7aosko0ck84c84c88484oww','PxrsC6QHGamNgLB+dJqqSJu1VBdydj6B8LTq0W5MXnSyxjGJ6pXYZ5Mz6x8yZWcGvZeb9XXTW9N50EjUCrv3DQ==',NULL,NULL,NULL,0,'2018-05-30 13:22:06','2018-05-30 13:22:06','[]',0,NULL,'Europe/Paris',510,6,32);
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
INSERT INTO `oro_user_access_group` VALUES (8,9),(8,12),(9,10),(9,12),(10,9),(10,12),(11,11),(11,12),(12,11),(12,12),(13,11),(13,12);
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
INSERT INTO `oro_user_access_role` VALUES (8,7),(9,8),(10,7),(11,9),(12,9),(13,9);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type`
--

LOCK TABLES `pim_catalog_association_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type` VALUES (9,'X_SELL','2018-05-30 13:22:05','2018-05-30 13:22:05'),(10,'UPSELL','2018-05-30 13:22:05','2018-05-30 13:22:05'),(11,'SUBSTITUTION','2018-05-30 13:22:05','2018-05-30 13:22:05'),(12,'PACK','2018-05-30 13:22:05','2018-05-30 13:22:05');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_association_type_translation`
--

LOCK TABLES `pim_catalog_association_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_association_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_type_translation` VALUES (19,9,'Cross sell','en_US'),(20,9,'Vente croisée','fr_FR'),(21,10,'Upsell','en_US'),(22,10,'Vente incitative','fr_FR'),(23,11,'Substitution','en_US'),(24,11,'Remplacement','fr_FR'),(25,12,'Pack','en_US'),(26,12,'Pack','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute`
--

LOCK TABLES `pim_catalog_attribute` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES (29,8,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,'sku','Pim\\Component\\Catalog\\Model\\Product','pim_catalog_identifier','text','a:0:{}','2018-05-30 13:22:05','2018-05-30 13:22:05');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group`
--

LOCK TABLES `pim_catalog_attribute_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group` VALUES (8,'other',100,'2018-05-30 13:22:05','2018-05-30 13:22:05');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_group_translation`
--

LOCK TABLES `pim_catalog_attribute_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_group_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_group_translation` VALUES (20,8,'Other','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_option`
--

LOCK TABLES `pim_catalog_attribute_option` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

LOCK TABLES `pim_catalog_attribute_requirement` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_attribute_translation`
--

LOCK TABLES `pim_catalog_attribute_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (83,29,'SKU','en_US'),(84,29,'SKU','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category`
--

LOCK TABLES `pim_catalog_category` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
INSERT INTO `pim_catalog_category` VALUES (32,NULL,'default','2018-05-30 13:22:05',32,0,1,2);
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
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_category_translation`
--

LOCK TABLES `pim_catalog_category_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_category_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_translation` VALUES (92,32,'Master catalog','en_US'),(93,32,' Catalog principal','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel`
--

LOCK TABLES `pim_catalog_channel` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel` VALUES (5,32,'ecommerce','a:0:{}'),(6,32,'mobile','a:0:{}');
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
INSERT INTO `pim_catalog_channel_currency` VALUES (5,589),(5,590),(6,590);
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
INSERT INTO `pim_catalog_channel_locale` VALUES (5,478),(5,510),(6,510);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_channel_translation`
--

LOCK TABLES `pim_catalog_channel_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_channel_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_translation` VALUES (13,5,'Ecommerce','en_US'),(14,5,'Ecommerce','de_DE'),(15,5,'Ecommerce','fr_FR'),(16,6,'Mobile','en_US'),(17,6,'Mobil','de_DE'),(18,6,'Mobile','fr_FR');
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
) ENGINE=InnoDB AUTO_INCREMENT=883 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_currency`
--

LOCK TABLES `pim_catalog_currency` WRITE;
/*!40000 ALTER TABLE `pim_catalog_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (589,'USD',1),(590,'EUR',1),(591,'ADP',0),(592,'AED',0),(593,'AFA',0),(594,'AFN',0),(595,'ALK',0),(596,'ALL',0),(597,'AMD',0),(598,'ANG',0),(599,'AOA',0),(600,'AOK',0),(601,'AON',0),(602,'AOR',0),(603,'ARA',0),(604,'ARL',0),(605,'ARM',0),(606,'ARP',0),(607,'ARS',0),(608,'ATS',0),(609,'AUD',0),(610,'AWG',0),(611,'AZM',0),(612,'AZN',0),(613,'BAD',0),(614,'BAM',0),(615,'BAN',0),(616,'BBD',0),(617,'BDT',0),(618,'BEC',0),(619,'BEF',0),(620,'BEL',0),(621,'BGL',0),(622,'BGM',0),(623,'BGN',0),(624,'BGO',0),(625,'BHD',0),(626,'BIF',0),(627,'BMD',0),(628,'BND',0),(629,'BOB',0),(630,'BOL',0),(631,'BOP',0),(632,'BOV',0),(633,'BRB',0),(634,'BRC',0),(635,'BRE',0),(636,'BRL',0),(637,'BRN',0),(638,'BRR',0),(639,'BRZ',0),(640,'BSD',0),(641,'BTN',0),(642,'BUK',0),(643,'BWP',0),(644,'BYB',0),(645,'BYR',0),(646,'BZD',0),(647,'CAD',0),(648,'CDF',0),(649,'CHE',0),(650,'CHF',0),(651,'CHW',0),(652,'CLE',0),(653,'CLF',0),(654,'CLP',0),(655,'CNX',0),(656,'CNY',0),(657,'COP',0),(658,'COU',0),(659,'CRC',0),(660,'CSD',0),(661,'CSK',0),(662,'CUC',0),(663,'CUP',0),(664,'CVE',0),(665,'CYP',0),(666,'CZK',0),(667,'DDM',0),(668,'DEM',0),(669,'DJF',0),(670,'DKK',0),(671,'DOP',0),(672,'DZD',0),(673,'ECS',0),(674,'ECV',0),(675,'EEK',0),(676,'EGP',0),(677,'ERN',0),(678,'ESA',0),(679,'ESB',0),(680,'ESP',0),(681,'ETB',0),(682,'FIM',0),(683,'FJD',0),(684,'FKP',0),(685,'FRF',0),(686,'GBP',0),(687,'GEK',0),(688,'GEL',0),(689,'GHC',0),(690,'GHS',0),(691,'GIP',0),(692,'GMD',0),(693,'GNF',0),(694,'GNS',0),(695,'GQE',0),(696,'GRD',0),(697,'GTQ',0),(698,'GWE',0),(699,'GWP',0),(700,'GYD',0),(701,'HKD',0),(702,'HNL',0),(703,'HRD',0),(704,'HRK',0),(705,'HTG',0),(706,'HUF',0),(707,'IDR',0),(708,'IEP',0),(709,'ILP',0),(710,'ILR',0),(711,'ILS',0),(712,'INR',0),(713,'IQD',0),(714,'IRR',0),(715,'ISJ',0),(716,'ISK',0),(717,'ITL',0),(718,'JMD',0),(719,'JOD',0),(720,'JPY',0),(721,'KES',0),(722,'KGS',0),(723,'KHR',0),(724,'KMF',0),(725,'KPW',0),(726,'KRH',0),(727,'KRO',0),(728,'KRW',0),(729,'KWD',0),(730,'KYD',0),(731,'KZT',0),(732,'LAK',0),(733,'LBP',0),(734,'LKR',0),(735,'LRD',0),(736,'LSL',0),(737,'LTL',0),(738,'LTT',0),(739,'LUC',0),(740,'LUF',0),(741,'LUL',0),(742,'LVL',0),(743,'LVR',0),(744,'LYD',0),(745,'MAD',0),(746,'MAF',0),(747,'MCF',0),(748,'MDC',0),(749,'MDL',0),(750,'MGA',0),(751,'MGF',0),(752,'MKD',0),(753,'MKN',0),(754,'MLF',0),(755,'MMK',0),(756,'MNT',0),(757,'MOP',0),(758,'MRO',0),(759,'MTL',0),(760,'MTP',0),(761,'MUR',0),(762,'MVP',0),(763,'MVR',0),(764,'MWK',0),(765,'MXN',0),(766,'MXP',0),(767,'MXV',0),(768,'MYR',0),(769,'MZE',0),(770,'MZM',0),(771,'MZN',0),(772,'NAD',0),(773,'NGN',0),(774,'NIC',0),(775,'NIO',0),(776,'NLG',0),(777,'NOK',0),(778,'NPR',0),(779,'NZD',0),(780,'OMR',0),(781,'PAB',0),(782,'PEI',0),(783,'PEN',0),(784,'PES',0),(785,'PGK',0),(786,'PHP',0),(787,'PKR',0),(788,'PLN',0),(789,'PLZ',0),(790,'PTE',0),(791,'PYG',0),(792,'QAR',0),(793,'RHD',0),(794,'ROL',0),(795,'RON',0),(796,'RSD',0),(797,'RUB',0),(798,'RUR',0),(799,'RWF',0),(800,'SAR',0),(801,'SBD',0),(802,'SCR',0),(803,'SDD',0),(804,'SDG',0),(805,'SDP',0),(806,'SEK',0),(807,'SGD',0),(808,'SHP',0),(809,'SIT',0),(810,'SKK',0),(811,'SLL',0),(812,'SOS',0),(813,'SRD',0),(814,'SRG',0),(815,'SSP',0),(816,'STD',0),(817,'SUR',0),(818,'SVC',0),(819,'SYP',0),(820,'SZL',0),(821,'THB',0),(822,'TJR',0),(823,'TJS',0),(824,'TMM',0),(825,'TMT',0),(826,'TND',0),(827,'TOP',0),(828,'TPE',0),(829,'TRL',0),(830,'TRY',0),(831,'TTD',0),(832,'TWD',0),(833,'TZS',0),(834,'UAH',0),(835,'UAK',0),(836,'UGS',0),(837,'UGX',0),(838,'USN',0),(839,'USS',0),(840,'UYI',0),(841,'UYP',0),(842,'UYU',0),(843,'UZS',0),(844,'VEB',0),(845,'VEF',0),(846,'VND',0),(847,'VNN',0),(848,'VUV',0),(849,'WST',0),(850,'XAF',0),(851,'XAU',0),(852,'XBA',0),(853,'XBB',0),(854,'XBC',0),(855,'XBD',0),(856,'XCD',0),(857,'XDR',0),(858,'XEU',0),(859,'XFO',0),(860,'XFU',0),(861,'XOF',0),(862,'XPD',0),(863,'XPF',0),(864,'XRE',0),(865,'XSU',0),(866,'XTS',0),(867,'XXX',0),(868,'YDD',0),(869,'YER',0),(870,'YUD',0),(871,'YUM',0),(872,'YUN',0),(873,'YUR',0),(874,'ZAL',0),(875,'ZAR',0),(876,'ZMK',0),(877,'ZMW',0),(878,'ZRN',0),(879,'ZRZ',0),(880,'ZWD',0),(881,'ZWL',0),(882,'ZWR',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family`
--

LOCK TABLES `pim_catalog_family` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_family_translation`
--

LOCK TABLES `pim_catalog_family_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group`
--

LOCK TABLES `pim_catalog_group` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_translation`
--

LOCK TABLES `pim_catalog_group_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_translation` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type`
--

LOCK TABLES `pim_catalog_group_type` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type` VALUES (3,'X_SELL');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_group_type_translation`
--

LOCK TABLES `pim_catalog_group_type_translation` WRITE;
/*!40000 ALTER TABLE `pim_catalog_group_type_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_type_translation` VALUES (2,3,'Cross sell','en_US');
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
) ENGINE=InnoDB AUTO_INCREMENT=631 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_catalog_locale`
--

LOCK TABLES `pim_catalog_locale` WRITE;
/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_locale` VALUES (421,'af_ZA',0),(422,'am_ET',0),(423,'ar_AE',0),(424,'ar_BH',0),(425,'ar_DZ',0),(426,'ar_EG',0),(427,'ar_IQ',0),(428,'ar_JO',0),(429,'ar_KW',0),(430,'ar_LB',0),(431,'ar_LY',0),(432,'ar_MA',0),(433,'arn_CL',0),(434,'ar_OM',0),(435,'ar_QA',0),(436,'ar_SA',0),(437,'ar_SY',0),(438,'ar_TN',0),(439,'ar_YE',0),(440,'as_IN',0),(441,'az_Cyrl_AZ',0),(442,'az_Latn_AZ',0),(443,'ba_RU',0),(444,'be_BY',0),(445,'bg_BG',0),(446,'bn_BD',0),(447,'bn_IN',0),(448,'bo_CN',0),(449,'br_FR',0),(450,'bs_Cyrl_BA',0),(451,'bs_Latn_BA',0),(452,'ca_ES',0),(453,'co_FR',0),(454,'cs_CZ',0),(455,'cy_GB',0),(456,'da_DK',0),(457,'de_AT',0),(458,'de_CH',0),(459,'de_DE',0),(460,'de_LI',0),(461,'de_LU',0),(462,'dsb_DE',0),(463,'dv_MV',0),(464,'el_GR',0),(465,'en_029',0),(466,'en_AU',0),(467,'en_BZ',0),(468,'en_CA',0),(469,'en_GB',0),(470,'en_IE',0),(471,'en_IN',0),(472,'en_JM',0),(473,'en_MY',0),(474,'en_NZ',0),(475,'en_PH',0),(476,'en_SG',0),(477,'en_TT',0),(478,'en_US',1),(479,'en_ZA',0),(480,'en_ZW',0),(481,'es_AR',0),(482,'es_BO',0),(483,'es_CL',0),(484,'es_CO',0),(485,'es_CR',0),(486,'es_DO',0),(487,'es_EC',0),(488,'es_ES',0),(489,'es_GT',0),(490,'es_HN',0),(491,'es_MX',0),(492,'es_NI',0),(493,'es_PA',0),(494,'es_PE',0),(495,'es_PR',0),(496,'es_PY',0),(497,'es_SV',0),(498,'es_US',0),(499,'es_UY',0),(500,'es_VE',0),(501,'et_EE',0),(502,'eu_ES',0),(503,'fa_IR',0),(504,'fi_FI',0),(505,'fil_PH',0),(506,'fo_FO',0),(507,'fr_BE',0),(508,'fr_CA',0),(509,'fr_CH',0),(510,'fr_FR',1),(511,'fr_LU',0),(512,'fr_MC',0),(513,'fy_NL',0),(514,'ga_IE',0),(515,'gd_GB',0),(516,'gl_ES',0),(517,'gsw_FR',0),(518,'gu_IN',0),(519,'ha_Latn_NG',0),(520,'he_IL',0),(521,'hi_IN',0),(522,'hr_BA',0),(523,'hr_HR',0),(524,'hsb_DE',0),(525,'hu_HU',0),(526,'hy_AM',0),(527,'id_ID',0),(528,'ig_NG',0),(529,'ii_CN',0),(530,'is_IS',0),(531,'it_CH',0),(532,'it_IT',0),(533,'iu_Cans_CA',0),(534,'iu_Latn_CA',0),(535,'ja_JP',0),(536,'ka_GE',0),(537,'kk_KZ',0),(538,'kl_GL',0),(539,'km_KH',0),(540,'kn_IN',0),(541,'kok_IN',0),(542,'ko_KR',0),(543,'ky_KG',0),(544,'lb_LU',0),(545,'lo_LA',0),(546,'lt_LT',0),(547,'lv_LV',0),(548,'mi_NZ',0),(549,'mk_MK',0),(550,'ml_IN',0),(551,'mn_MN',0),(552,'mn_Mong_CN',0),(553,'moh_CA',0),(554,'mr_IN',0),(555,'ms_BN',0),(556,'ms_MY',0),(557,'mt_MT',0),(558,'nb_NO',0),(559,'ne_NP',0),(560,'nl_BE',0),(561,'nl_NL',0),(562,'nn_NO',0),(563,'nso_ZA',0),(564,'oc_FR',0),(565,'or_IN',0),(566,'pa_IN',0),(567,'pl_PL',0),(568,'prs_AF',0),(569,'ps_AF',0),(570,'pt_BR',0),(571,'pt_PT',0),(572,'qut_GT',0),(573,'quz_BO',0),(574,'quz_EC',0),(575,'quz_PE',0),(576,'rm_CH',0),(577,'ro_RO',0),(578,'ru_RU',0),(579,'rw_RW',0),(580,'sah_RU',0),(581,'sa_IN',0),(582,'se_FI',0),(583,'se_NO',0),(584,'se_SE',0),(585,'si_LK',0),(586,'sk_SK',0),(587,'sl_SI',0),(588,'sma_NO',0),(589,'sma_SE',0),(590,'smj_NO',0),(591,'smj_SE',0),(592,'smn_FI',0),(593,'sms_FI',0),(594,'sq_AL',0),(595,'sr_Cyrl_BA',0),(596,'sr_Cyrl_CS',0),(597,'sr_Cyrl_ME',0),(598,'sr_Cyrl_RS',0),(599,'sr_Latn_BA',0),(600,'sr_Latn_CS',0),(601,'sr_Latn_ME',0),(602,'sr_Latn_RS',0),(603,'sv_FI',0),(604,'sv_SE',0),(605,'sw_KE',0),(606,'syr_SY',0),(607,'ta_IN',0),(608,'te_IN',0),(609,'tg_Cyrl_TJ',0),(610,'th_TH',0),(611,'tk_TM',0),(612,'tn_ZA',0),(613,'tr_TR',0),(614,'tt_RU',0),(615,'tzm_Latn_DZ',0),(616,'ug_CN',0),(617,'uk_UA',0),(618,'ur_PK',0),(619,'uz_Cyrl_UZ',0),(620,'uz_Latn_UZ',0),(621,'vi_VN',0),(622,'wo_SN',0),(623,'xh_ZA',0),(624,'yo_NG',0),(625,'zh_CN',0),(626,'zh_HK',0),(627,'zh_MO',0),(628,'zh_SG',0),(629,'zh_TW',0),(630,'zu_ZA',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=1187 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pim_versioning_version`
--

LOCK TABLES `pim_versioning_version` WRITE;
/*!40000 ALTER TABLE `pim_versioning_version` DISABLE KEYS */;
INSERT INTO `pim_versioning_version` VALUES (928,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','91','a:5:{s:4:\"code\";s:22:\"fixtures_currency_csv0\";s:5:\"label\";s:24:\"Currencies data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_currency_csv0\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Currencies data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/currencies.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(929,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','92','a:5:{s:4:\"code\";s:20:\"fixtures_locale_csv1\";s:5:\"label\";s:21:\"Locales data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:298:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_locale_csv1\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Locales data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:298:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/locales.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(930,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','93','a:5:{s:4:\"code\";s:24:\"fixtures_user_group_csv2\";s:5:\"label\";s:25:\"User groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_group_csv2\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"User groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/user_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(931,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','94','a:5:{s:4:\"code\";s:22:\"fixtures_category_csv3\";s:5:\"label\";s:24:\"Categories data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_category_csv3\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Categories data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/categories.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(932,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','95','a:5:{s:4:\"code\";s:29:\"fixtures_attribute_group_csv4\";s:5:\"label\";s:30:\"Attribute groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:307:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_attribute_group_csv4\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"Attribute groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:307:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/attribute_groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(933,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','96','a:5:{s:4:\"code\";s:24:\"fixtures_group_type_csv5\";s:5:\"label\";s:25:\"Group types data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_group_type_csv5\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Group types data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:302:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/group_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(934,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','97','a:5:{s:4:\"code\";s:23:\"fixtures_attribute_csv6\";s:5:\"label\";s:24:\"Attributes data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"fixtures_attribute_csv6\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Attributes data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/attributes.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(935,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','98','a:5:{s:4:\"code\";s:21:\"fixtures_channel_csv7\";s:5:\"label\";s:22:\"Channels data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_channel_csv7\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Channels data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/channels.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(936,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','99','a:5:{s:4:\"code\";s:30:\"fixtures_association_type_csv8\";s:5:\"label\";s:26:\"Associations data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:30:\"fixtures_association_type_csv8\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Associations data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/association_types.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(937,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','100','a:5:{s:4:\"code\";s:31:\"fixtures_attribute_options_csv9\";s:5:\"label\";s:31:\"Attribute options data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"fixtures_attribute_options_csv9\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:31:\"Attribute options data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:308:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/attribute_options.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(938,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','101','a:5:{s:4:\"code\";s:21:\"fixtures_family_csv10\";s:5:\"label\";s:22:\"Families data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"fixtures_family_csv10\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Families data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:299:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/families.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(939,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','102','a:5:{s:4:\"code\";s:18:\"fixtures_job_yml11\";s:5:\"label\";s:18:\"Jobs data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:231:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"fixtures_job_yml11\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\"Jobs data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:231:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/jobs.yml\",\"uploadAllowed\":true,\"invalid_items_file_format\":\"yaml\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(940,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','103','a:5:{s:4:\"code\";s:29:\"fixtures_family_variant_csv12\";s:5:\"label\";s:29:\"Family variants data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:306:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"fixtures_family_variant_csv12\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Family variants data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:306:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/family_variants.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(941,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','104','a:5:{s:4:\"code\";s:20:\"fixtures_group_csv13\";s:5:\"label\";s:20:\"Groups data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:297:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"fixtures_group_csv13\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Groups data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:297:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/groups.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(942,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','105','a:5:{s:4:\"code\";s:24:\"fixtures_user_role_csv14\";s:5:\"label\";s:24:\"User roles data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"fixtures_user_role_csv14\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"User roles data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:301:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/user_roles.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(943,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','106','a:5:{s:4:\"code\";s:19:\"fixtures_user_csv15\";s:5:\"label\";s:19:\"Users data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:296:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"fixtures_user_csv15\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"Users data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:296:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/users.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false}\";}}',NULL,1,'2018-05-30 13:22:04',0),(944,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','107','a:5:{s:4:\"code\";s:28:\"fixtures_product_model_csv16\";s:5:\"label\";s:27:\"Product model data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:491:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"fixtures_product_model_csv16\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Product model data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:491:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/product_models.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyVariantColumn\":\"family_variant\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:04',0),(945,'admin','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','108','a:5:{s:4:\"code\";s:22:\"fixtures_product_csv17\";s:5:\"label\";s:21:\"Product data fixtures\";s:9:\"connector\";s:13:\"Data fixtures\";s:4:\"type\";s:8:\"fixtures\";s:13:\"configuration\";s:494:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"fixtures_product_csv17\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Product data fixtures\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:13:\"Data fixtures\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"fixtures\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:494:\"{\"filePath\":\"\\/Users\\/juliensanchez\\/workspace\\/akeneo\\/pcd\\/tests\\/legacy\\/features\\/Context\\/catalog\\/default\\/products.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"escape\":\"\\\\\",\"withHeader\":true,\"uploadAllowed\":true,\"invalid_items_file_format\":\"csv\",\"user_to_notify\":null,\"is_user_authenticated\":false,\"decimalSeparator\":\".\",\"dateFormat\":\"yyyy-MM-dd\",\"enabled\":true,\"categoriesColumn\":\"categories\",\"familyColumn\":\"family\",\"groupsColumn\":\"groups\",\"enabledComparison\":true,\"realTimeVersioning\":true}\";}}',NULL,1,'2018-05-30 13:22:04',0),(946,'system','Akeneo\\Channel\\Component\\Model\\Locale','421','a:1:{s:4:\"code\";s:5:\"af_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"af_ZA\";}}',NULL,1,'2018-05-30 13:22:04',0),(947,'system','Akeneo\\Channel\\Component\\Model\\Locale','422','a:1:{s:4:\"code\";s:5:\"am_ET\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"am_ET\";}}',NULL,1,'2018-05-30 13:22:04',0),(948,'system','Akeneo\\Channel\\Component\\Model\\Locale','423','a:1:{s:4:\"code\";s:5:\"ar_AE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_AE\";}}',NULL,1,'2018-05-30 13:22:04',0),(949,'system','Akeneo\\Channel\\Component\\Model\\Locale','424','a:1:{s:4:\"code\";s:5:\"ar_BH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_BH\";}}',NULL,1,'2018-05-30 13:22:04',0),(950,'system','Akeneo\\Channel\\Component\\Model\\Locale','425','a:1:{s:4:\"code\";s:5:\"ar_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_DZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(951,'system','Akeneo\\Channel\\Component\\Model\\Locale','426','a:1:{s:4:\"code\";s:5:\"ar_EG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_EG\";}}',NULL,1,'2018-05-30 13:22:04',0),(952,'system','Akeneo\\Channel\\Component\\Model\\Locale','427','a:1:{s:4:\"code\";s:5:\"ar_IQ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_IQ\";}}',NULL,1,'2018-05-30 13:22:04',0),(953,'system','Akeneo\\Channel\\Component\\Model\\Locale','428','a:1:{s:4:\"code\";s:5:\"ar_JO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_JO\";}}',NULL,1,'2018-05-30 13:22:04',0),(954,'system','Akeneo\\Channel\\Component\\Model\\Locale','429','a:1:{s:4:\"code\";s:5:\"ar_KW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_KW\";}}',NULL,1,'2018-05-30 13:22:04',0),(955,'system','Akeneo\\Channel\\Component\\Model\\Locale','430','a:1:{s:4:\"code\";s:5:\"ar_LB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LB\";}}',NULL,1,'2018-05-30 13:22:04',0),(956,'system','Akeneo\\Channel\\Component\\Model\\Locale','431','a:1:{s:4:\"code\";s:5:\"ar_LY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_LY\";}}',NULL,1,'2018-05-30 13:22:04',0),(957,'system','Akeneo\\Channel\\Component\\Model\\Locale','432','a:1:{s:4:\"code\";s:5:\"ar_MA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_MA\";}}',NULL,1,'2018-05-30 13:22:04',0),(958,'system','Akeneo\\Channel\\Component\\Model\\Locale','433','a:1:{s:4:\"code\";s:6:\"arn_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"arn_CL\";}}',NULL,1,'2018-05-30 13:22:04',0),(959,'system','Akeneo\\Channel\\Component\\Model\\Locale','434','a:1:{s:4:\"code\";s:5:\"ar_OM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_OM\";}}',NULL,1,'2018-05-30 13:22:04',0),(960,'system','Akeneo\\Channel\\Component\\Model\\Locale','435','a:1:{s:4:\"code\";s:5:\"ar_QA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_QA\";}}',NULL,1,'2018-05-30 13:22:04',0),(961,'system','Akeneo\\Channel\\Component\\Model\\Locale','436','a:1:{s:4:\"code\";s:5:\"ar_SA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SA\";}}',NULL,1,'2018-05-30 13:22:04',0),(962,'system','Akeneo\\Channel\\Component\\Model\\Locale','437','a:1:{s:4:\"code\";s:5:\"ar_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_SY\";}}',NULL,1,'2018-05-30 13:22:04',0),(963,'system','Akeneo\\Channel\\Component\\Model\\Locale','438','a:1:{s:4:\"code\";s:5:\"ar_TN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_TN\";}}',NULL,1,'2018-05-30 13:22:04',0),(964,'system','Akeneo\\Channel\\Component\\Model\\Locale','439','a:1:{s:4:\"code\";s:5:\"ar_YE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ar_YE\";}}',NULL,1,'2018-05-30 13:22:04',0),(965,'system','Akeneo\\Channel\\Component\\Model\\Locale','440','a:1:{s:4:\"code\";s:5:\"as_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"as_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(966,'system','Akeneo\\Channel\\Component\\Model\\Locale','441','a:1:{s:4:\"code\";s:10:\"az_Cyrl_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Cyrl_AZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(967,'system','Akeneo\\Channel\\Component\\Model\\Locale','442','a:1:{s:4:\"code\";s:10:\"az_Latn_AZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"az_Latn_AZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(968,'system','Akeneo\\Channel\\Component\\Model\\Locale','443','a:1:{s:4:\"code\";s:5:\"ba_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ba_RU\";}}',NULL,1,'2018-05-30 13:22:04',0),(969,'system','Akeneo\\Channel\\Component\\Model\\Locale','444','a:1:{s:4:\"code\";s:5:\"be_BY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"be_BY\";}}',NULL,1,'2018-05-30 13:22:04',0),(970,'system','Akeneo\\Channel\\Component\\Model\\Locale','445','a:1:{s:4:\"code\";s:5:\"bg_BG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bg_BG\";}}',NULL,1,'2018-05-30 13:22:04',0),(971,'system','Akeneo\\Channel\\Component\\Model\\Locale','446','a:1:{s:4:\"code\";s:5:\"bn_BD\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_BD\";}}',NULL,1,'2018-05-30 13:22:04',0),(972,'system','Akeneo\\Channel\\Component\\Model\\Locale','447','a:1:{s:4:\"code\";s:5:\"bn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bn_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(973,'system','Akeneo\\Channel\\Component\\Model\\Locale','448','a:1:{s:4:\"code\";s:5:\"bo_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"bo_CN\";}}',NULL,1,'2018-05-30 13:22:04',0),(974,'system','Akeneo\\Channel\\Component\\Model\\Locale','449','a:1:{s:4:\"code\";s:5:\"br_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"br_FR\";}}',NULL,1,'2018-05-30 13:22:04',0),(975,'system','Akeneo\\Channel\\Component\\Model\\Locale','450','a:1:{s:4:\"code\";s:10:\"bs_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Cyrl_BA\";}}',NULL,1,'2018-05-30 13:22:04',0),(976,'system','Akeneo\\Channel\\Component\\Model\\Locale','451','a:1:{s:4:\"code\";s:10:\"bs_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"bs_Latn_BA\";}}',NULL,1,'2018-05-30 13:22:04',0),(977,'system','Akeneo\\Channel\\Component\\Model\\Locale','452','a:1:{s:4:\"code\";s:5:\"ca_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ca_ES\";}}',NULL,1,'2018-05-30 13:22:04',0),(978,'system','Akeneo\\Channel\\Component\\Model\\Locale','453','a:1:{s:4:\"code\";s:5:\"co_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"co_FR\";}}',NULL,1,'2018-05-30 13:22:04',0),(979,'system','Akeneo\\Channel\\Component\\Model\\Locale','454','a:1:{s:4:\"code\";s:5:\"cs_CZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cs_CZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(980,'system','Akeneo\\Channel\\Component\\Model\\Locale','455','a:1:{s:4:\"code\";s:5:\"cy_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"cy_GB\";}}',NULL,1,'2018-05-30 13:22:04',0),(981,'system','Akeneo\\Channel\\Component\\Model\\Locale','456','a:1:{s:4:\"code\";s:5:\"da_DK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"da_DK\";}}',NULL,1,'2018-05-30 13:22:04',0),(982,'system','Akeneo\\Channel\\Component\\Model\\Locale','457','a:1:{s:4:\"code\";s:5:\"de_AT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_AT\";}}',NULL,1,'2018-05-30 13:22:04',0),(983,'system','Akeneo\\Channel\\Component\\Model\\Locale','458','a:1:{s:4:\"code\";s:5:\"de_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_CH\";}}',NULL,1,'2018-05-30 13:22:04',0),(984,'system','Akeneo\\Channel\\Component\\Model\\Locale','459','a:1:{s:4:\"code\";s:5:\"de_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_DE\";}}',NULL,1,'2018-05-30 13:22:04',0),(985,'system','Akeneo\\Channel\\Component\\Model\\Locale','460','a:1:{s:4:\"code\";s:5:\"de_LI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LI\";}}',NULL,1,'2018-05-30 13:22:04',0),(986,'system','Akeneo\\Channel\\Component\\Model\\Locale','461','a:1:{s:4:\"code\";s:5:\"de_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"de_LU\";}}',NULL,1,'2018-05-30 13:22:04',0),(987,'system','Akeneo\\Channel\\Component\\Model\\Locale','462','a:1:{s:4:\"code\";s:6:\"dsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"dsb_DE\";}}',NULL,1,'2018-05-30 13:22:04',0),(988,'system','Akeneo\\Channel\\Component\\Model\\Locale','463','a:1:{s:4:\"code\";s:5:\"dv_MV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"dv_MV\";}}',NULL,1,'2018-05-30 13:22:04',0),(989,'system','Akeneo\\Channel\\Component\\Model\\Locale','464','a:1:{s:4:\"code\";s:5:\"el_GR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"el_GR\";}}',NULL,1,'2018-05-30 13:22:04',0),(990,'system','Akeneo\\Channel\\Component\\Model\\Locale','465','a:1:{s:4:\"code\";s:6:\"en_029\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"en_029\";}}',NULL,1,'2018-05-30 13:22:04',0),(991,'system','Akeneo\\Channel\\Component\\Model\\Locale','466','a:1:{s:4:\"code\";s:5:\"en_AU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_AU\";}}',NULL,1,'2018-05-30 13:22:04',0),(992,'system','Akeneo\\Channel\\Component\\Model\\Locale','467','a:1:{s:4:\"code\";s:5:\"en_BZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_BZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(993,'system','Akeneo\\Channel\\Component\\Model\\Locale','468','a:1:{s:4:\"code\";s:5:\"en_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_CA\";}}',NULL,1,'2018-05-30 13:22:04',0),(994,'system','Akeneo\\Channel\\Component\\Model\\Locale','469','a:1:{s:4:\"code\";s:5:\"en_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_GB\";}}',NULL,1,'2018-05-30 13:22:04',0),(995,'system','Akeneo\\Channel\\Component\\Model\\Locale','470','a:1:{s:4:\"code\";s:5:\"en_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IE\";}}',NULL,1,'2018-05-30 13:22:04',0),(996,'system','Akeneo\\Channel\\Component\\Model\\Locale','471','a:1:{s:4:\"code\";s:5:\"en_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(997,'system','Akeneo\\Channel\\Component\\Model\\Locale','472','a:1:{s:4:\"code\";s:5:\"en_JM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_JM\";}}',NULL,1,'2018-05-30 13:22:04',0),(998,'system','Akeneo\\Channel\\Component\\Model\\Locale','473','a:1:{s:4:\"code\";s:5:\"en_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_MY\";}}',NULL,1,'2018-05-30 13:22:04',0),(999,'system','Akeneo\\Channel\\Component\\Model\\Locale','474','a:1:{s:4:\"code\";s:5:\"en_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_NZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(1000,'system','Akeneo\\Channel\\Component\\Model\\Locale','475','a:1:{s:4:\"code\";s:5:\"en_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_PH\";}}',NULL,1,'2018-05-30 13:22:04',0),(1001,'system','Akeneo\\Channel\\Component\\Model\\Locale','476','a:1:{s:4:\"code\";s:5:\"en_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_SG\";}}',NULL,1,'2018-05-30 13:22:04',0),(1002,'system','Akeneo\\Channel\\Component\\Model\\Locale','477','a:1:{s:4:\"code\";s:5:\"en_TT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_TT\";}}',NULL,1,'2018-05-30 13:22:04',0),(1003,'system','Akeneo\\Channel\\Component\\Model\\Locale','478','a:1:{s:4:\"code\";s:5:\"en_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_US\";}}',NULL,1,'2018-05-30 13:22:04',0),(1004,'system','Akeneo\\Channel\\Component\\Model\\Locale','479','a:1:{s:4:\"code\";s:5:\"en_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1005,'system','Akeneo\\Channel\\Component\\Model\\Locale','480','a:1:{s:4:\"code\";s:5:\"en_ZW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"en_ZW\";}}',NULL,1,'2018-05-30 13:22:04',0),(1006,'system','Akeneo\\Channel\\Component\\Model\\Locale','481','a:1:{s:4:\"code\";s:5:\"es_AR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_AR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1007,'system','Akeneo\\Channel\\Component\\Model\\Locale','482','a:1:{s:4:\"code\";s:5:\"es_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_BO\";}}',NULL,1,'2018-05-30 13:22:04',0),(1008,'system','Akeneo\\Channel\\Component\\Model\\Locale','483','a:1:{s:4:\"code\";s:5:\"es_CL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CL\";}}',NULL,1,'2018-05-30 13:22:04',0),(1009,'system','Akeneo\\Channel\\Component\\Model\\Locale','484','a:1:{s:4:\"code\";s:5:\"es_CO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CO\";}}',NULL,1,'2018-05-30 13:22:04',0),(1010,'system','Akeneo\\Channel\\Component\\Model\\Locale','485','a:1:{s:4:\"code\";s:5:\"es_CR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_CR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1011,'system','Akeneo\\Channel\\Component\\Model\\Locale','486','a:1:{s:4:\"code\";s:5:\"es_DO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_DO\";}}',NULL,1,'2018-05-30 13:22:04',0),(1012,'system','Akeneo\\Channel\\Component\\Model\\Locale','487','a:1:{s:4:\"code\";s:5:\"es_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_EC\";}}',NULL,1,'2018-05-30 13:22:04',0),(1013,'system','Akeneo\\Channel\\Component\\Model\\Locale','488','a:1:{s:4:\"code\";s:5:\"es_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_ES\";}}',NULL,1,'2018-05-30 13:22:04',0),(1014,'system','Akeneo\\Channel\\Component\\Model\\Locale','489','a:1:{s:4:\"code\";s:5:\"es_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_GT\";}}',NULL,1,'2018-05-30 13:22:04',0),(1015,'system','Akeneo\\Channel\\Component\\Model\\Locale','490','a:1:{s:4:\"code\";s:5:\"es_HN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_HN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1016,'system','Akeneo\\Channel\\Component\\Model\\Locale','491','a:1:{s:4:\"code\";s:5:\"es_MX\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_MX\";}}',NULL,1,'2018-05-30 13:22:04',0),(1017,'system','Akeneo\\Channel\\Component\\Model\\Locale','492','a:1:{s:4:\"code\";s:5:\"es_NI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_NI\";}}',NULL,1,'2018-05-30 13:22:04',0),(1018,'system','Akeneo\\Channel\\Component\\Model\\Locale','493','a:1:{s:4:\"code\";s:5:\"es_PA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1019,'system','Akeneo\\Channel\\Component\\Model\\Locale','494','a:1:{s:4:\"code\";s:5:\"es_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1020,'system','Akeneo\\Channel\\Component\\Model\\Locale','495','a:1:{s:4:\"code\";s:5:\"es_PR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1021,'system','Akeneo\\Channel\\Component\\Model\\Locale','496','a:1:{s:4:\"code\";s:5:\"es_PY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_PY\";}}',NULL,1,'2018-05-30 13:22:04',0),(1022,'system','Akeneo\\Channel\\Component\\Model\\Locale','497','a:1:{s:4:\"code\";s:5:\"es_SV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_SV\";}}',NULL,1,'2018-05-30 13:22:04',0),(1023,'system','Akeneo\\Channel\\Component\\Model\\Locale','498','a:1:{s:4:\"code\";s:5:\"es_US\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_US\";}}',NULL,1,'2018-05-30 13:22:04',0),(1024,'system','Akeneo\\Channel\\Component\\Model\\Locale','499','a:1:{s:4:\"code\";s:5:\"es_UY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_UY\";}}',NULL,1,'2018-05-30 13:22:04',0),(1025,'system','Akeneo\\Channel\\Component\\Model\\Locale','500','a:1:{s:4:\"code\";s:5:\"es_VE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"es_VE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1026,'system','Akeneo\\Channel\\Component\\Model\\Locale','501','a:1:{s:4:\"code\";s:5:\"et_EE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"et_EE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1027,'system','Akeneo\\Channel\\Component\\Model\\Locale','502','a:1:{s:4:\"code\";s:5:\"eu_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"eu_ES\";}}',NULL,1,'2018-05-30 13:22:04',0),(1028,'system','Akeneo\\Channel\\Component\\Model\\Locale','503','a:1:{s:4:\"code\";s:5:\"fa_IR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fa_IR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1029,'system','Akeneo\\Channel\\Component\\Model\\Locale','504','a:1:{s:4:\"code\";s:5:\"fi_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fi_FI\";}}',NULL,1,'2018-05-30 13:22:04',0),(1030,'system','Akeneo\\Channel\\Component\\Model\\Locale','505','a:1:{s:4:\"code\";s:6:\"fil_PH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"fil_PH\";}}',NULL,1,'2018-05-30 13:22:04',0),(1031,'system','Akeneo\\Channel\\Component\\Model\\Locale','506','a:1:{s:4:\"code\";s:5:\"fo_FO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fo_FO\";}}',NULL,1,'2018-05-30 13:22:04',0),(1032,'system','Akeneo\\Channel\\Component\\Model\\Locale','507','a:1:{s:4:\"code\";s:5:\"fr_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_BE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1033,'system','Akeneo\\Channel\\Component\\Model\\Locale','508','a:1:{s:4:\"code\";s:5:\"fr_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1034,'system','Akeneo\\Channel\\Component\\Model\\Locale','509','a:1:{s:4:\"code\";s:5:\"fr_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_CH\";}}',NULL,1,'2018-05-30 13:22:04',0),(1035,'system','Akeneo\\Channel\\Component\\Model\\Locale','510','a:1:{s:4:\"code\";s:5:\"fr_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1036,'system','Akeneo\\Channel\\Component\\Model\\Locale','511','a:1:{s:4:\"code\";s:5:\"fr_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_LU\";}}',NULL,1,'2018-05-30 13:22:04',0),(1037,'system','Akeneo\\Channel\\Component\\Model\\Locale','512','a:1:{s:4:\"code\";s:5:\"fr_MC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_MC\";}}',NULL,1,'2018-05-30 13:22:04',0),(1038,'system','Akeneo\\Channel\\Component\\Model\\Locale','513','a:1:{s:4:\"code\";s:5:\"fy_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fy_NL\";}}',NULL,1,'2018-05-30 13:22:04',0),(1039,'system','Akeneo\\Channel\\Component\\Model\\Locale','514','a:1:{s:4:\"code\";s:5:\"ga_IE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ga_IE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1040,'system','Akeneo\\Channel\\Component\\Model\\Locale','515','a:1:{s:4:\"code\";s:5:\"gd_GB\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gd_GB\";}}',NULL,1,'2018-05-30 13:22:04',0),(1041,'system','Akeneo\\Channel\\Component\\Model\\Locale','516','a:1:{s:4:\"code\";s:5:\"gl_ES\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gl_ES\";}}',NULL,1,'2018-05-30 13:22:04',0),(1042,'system','Akeneo\\Channel\\Component\\Model\\Locale','517','a:1:{s:4:\"code\";s:6:\"gsw_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"gsw_FR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1043,'system','Akeneo\\Channel\\Component\\Model\\Locale','518','a:1:{s:4:\"code\";s:5:\"gu_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"gu_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1044,'system','Akeneo\\Channel\\Component\\Model\\Locale','519','a:1:{s:4:\"code\";s:10:\"ha_Latn_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"ha_Latn_NG\";}}',NULL,1,'2018-05-30 13:22:04',0),(1045,'system','Akeneo\\Channel\\Component\\Model\\Locale','520','a:1:{s:4:\"code\";s:5:\"he_IL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"he_IL\";}}',NULL,1,'2018-05-30 13:22:04',0),(1046,'system','Akeneo\\Channel\\Component\\Model\\Locale','521','a:1:{s:4:\"code\";s:5:\"hi_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hi_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1047,'system','Akeneo\\Channel\\Component\\Model\\Locale','522','a:1:{s:4:\"code\";s:5:\"hr_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_BA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1048,'system','Akeneo\\Channel\\Component\\Model\\Locale','523','a:1:{s:4:\"code\";s:5:\"hr_HR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hr_HR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1049,'system','Akeneo\\Channel\\Component\\Model\\Locale','524','a:1:{s:4:\"code\";s:6:\"hsb_DE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"hsb_DE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1050,'system','Akeneo\\Channel\\Component\\Model\\Locale','525','a:1:{s:4:\"code\";s:5:\"hu_HU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hu_HU\";}}',NULL,1,'2018-05-30 13:22:04',0),(1051,'system','Akeneo\\Channel\\Component\\Model\\Locale','526','a:1:{s:4:\"code\";s:5:\"hy_AM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"hy_AM\";}}',NULL,1,'2018-05-30 13:22:04',0),(1052,'system','Akeneo\\Channel\\Component\\Model\\Locale','527','a:1:{s:4:\"code\";s:5:\"id_ID\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"id_ID\";}}',NULL,1,'2018-05-30 13:22:04',0),(1053,'system','Akeneo\\Channel\\Component\\Model\\Locale','528','a:1:{s:4:\"code\";s:5:\"ig_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ig_NG\";}}',NULL,1,'2018-05-30 13:22:04',0),(1054,'system','Akeneo\\Channel\\Component\\Model\\Locale','529','a:1:{s:4:\"code\";s:5:\"ii_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ii_CN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1055,'system','Akeneo\\Channel\\Component\\Model\\Locale','530','a:1:{s:4:\"code\";s:5:\"is_IS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"is_IS\";}}',NULL,1,'2018-05-30 13:22:04',0),(1056,'system','Akeneo\\Channel\\Component\\Model\\Locale','531','a:1:{s:4:\"code\";s:5:\"it_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_CH\";}}',NULL,1,'2018-05-30 13:22:04',0),(1057,'system','Akeneo\\Channel\\Component\\Model\\Locale','532','a:1:{s:4:\"code\";s:5:\"it_IT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"it_IT\";}}',NULL,1,'2018-05-30 13:22:04',0),(1058,'system','Akeneo\\Channel\\Component\\Model\\Locale','533','a:1:{s:4:\"code\";s:10:\"iu_Cans_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Cans_CA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1059,'system','Akeneo\\Channel\\Component\\Model\\Locale','534','a:1:{s:4:\"code\";s:10:\"iu_Latn_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"iu_Latn_CA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1060,'system','Akeneo\\Channel\\Component\\Model\\Locale','535','a:1:{s:4:\"code\";s:5:\"ja_JP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ja_JP\";}}',NULL,1,'2018-05-30 13:22:04',0),(1061,'system','Akeneo\\Channel\\Component\\Model\\Locale','536','a:1:{s:4:\"code\";s:5:\"ka_GE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ka_GE\";}}',NULL,1,'2018-05-30 13:22:04',0),(1062,'system','Akeneo\\Channel\\Component\\Model\\Locale','537','a:1:{s:4:\"code\";s:5:\"kk_KZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kk_KZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(1063,'system','Akeneo\\Channel\\Component\\Model\\Locale','538','a:1:{s:4:\"code\";s:5:\"kl_GL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kl_GL\";}}',NULL,1,'2018-05-30 13:22:04',0),(1064,'system','Akeneo\\Channel\\Component\\Model\\Locale','539','a:1:{s:4:\"code\";s:5:\"km_KH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"km_KH\";}}',NULL,1,'2018-05-30 13:22:04',0),(1065,'system','Akeneo\\Channel\\Component\\Model\\Locale','540','a:1:{s:4:\"code\";s:5:\"kn_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"kn_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1066,'system','Akeneo\\Channel\\Component\\Model\\Locale','541','a:1:{s:4:\"code\";s:6:\"kok_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"kok_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1067,'system','Akeneo\\Channel\\Component\\Model\\Locale','542','a:1:{s:4:\"code\";s:5:\"ko_KR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ko_KR\";}}',NULL,1,'2018-05-30 13:22:04',0),(1068,'system','Akeneo\\Channel\\Component\\Model\\Locale','543','a:1:{s:4:\"code\";s:5:\"ky_KG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ky_KG\";}}',NULL,1,'2018-05-30 13:22:04',0),(1069,'system','Akeneo\\Channel\\Component\\Model\\Locale','544','a:1:{s:4:\"code\";s:5:\"lb_LU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lb_LU\";}}',NULL,1,'2018-05-30 13:22:04',0),(1070,'system','Akeneo\\Channel\\Component\\Model\\Locale','545','a:1:{s:4:\"code\";s:5:\"lo_LA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lo_LA\";}}',NULL,1,'2018-05-30 13:22:04',0),(1071,'system','Akeneo\\Channel\\Component\\Model\\Locale','546','a:1:{s:4:\"code\";s:5:\"lt_LT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lt_LT\";}}',NULL,1,'2018-05-30 13:22:04',0),(1072,'system','Akeneo\\Channel\\Component\\Model\\Locale','547','a:1:{s:4:\"code\";s:5:\"lv_LV\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"lv_LV\";}}',NULL,1,'2018-05-30 13:22:04',0),(1073,'system','Akeneo\\Channel\\Component\\Model\\Locale','548','a:1:{s:4:\"code\";s:5:\"mi_NZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mi_NZ\";}}',NULL,1,'2018-05-30 13:22:04',0),(1074,'system','Akeneo\\Channel\\Component\\Model\\Locale','549','a:1:{s:4:\"code\";s:5:\"mk_MK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mk_MK\";}}',NULL,1,'2018-05-30 13:22:04',0),(1075,'system','Akeneo\\Channel\\Component\\Model\\Locale','550','a:1:{s:4:\"code\";s:5:\"ml_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ml_IN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1076,'system','Akeneo\\Channel\\Component\\Model\\Locale','551','a:1:{s:4:\"code\";s:5:\"mn_MN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mn_MN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1077,'system','Akeneo\\Channel\\Component\\Model\\Locale','552','a:1:{s:4:\"code\";s:10:\"mn_Mong_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"mn_Mong_CN\";}}',NULL,1,'2018-05-30 13:22:04',0),(1078,'system','Akeneo\\Channel\\Component\\Model\\Locale','553','a:1:{s:4:\"code\";s:6:\"moh_CA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"moh_CA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1079,'system','Akeneo\\Channel\\Component\\Model\\Locale','554','a:1:{s:4:\"code\";s:5:\"mr_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mr_IN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1080,'system','Akeneo\\Channel\\Component\\Model\\Locale','555','a:1:{s:4:\"code\";s:5:\"ms_BN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_BN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1081,'system','Akeneo\\Channel\\Component\\Model\\Locale','556','a:1:{s:4:\"code\";s:5:\"ms_MY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ms_MY\";}}',NULL,1,'2018-05-30 13:22:05',0),(1082,'system','Akeneo\\Channel\\Component\\Model\\Locale','557','a:1:{s:4:\"code\";s:5:\"mt_MT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"mt_MT\";}}',NULL,1,'2018-05-30 13:22:05',0),(1083,'system','Akeneo\\Channel\\Component\\Model\\Locale','558','a:1:{s:4:\"code\";s:5:\"nb_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nb_NO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1084,'system','Akeneo\\Channel\\Component\\Model\\Locale','559','a:1:{s:4:\"code\";s:5:\"ne_NP\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ne_NP\";}}',NULL,1,'2018-05-30 13:22:05',0),(1085,'system','Akeneo\\Channel\\Component\\Model\\Locale','560','a:1:{s:4:\"code\";s:5:\"nl_BE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_BE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1086,'system','Akeneo\\Channel\\Component\\Model\\Locale','561','a:1:{s:4:\"code\";s:5:\"nl_NL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nl_NL\";}}',NULL,1,'2018-05-30 13:22:05',0),(1087,'system','Akeneo\\Channel\\Component\\Model\\Locale','562','a:1:{s:4:\"code\";s:5:\"nn_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"nn_NO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1088,'system','Akeneo\\Channel\\Component\\Model\\Locale','563','a:1:{s:4:\"code\";s:6:\"nso_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"nso_ZA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1089,'system','Akeneo\\Channel\\Component\\Model\\Locale','564','a:1:{s:4:\"code\";s:5:\"oc_FR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"oc_FR\";}}',NULL,1,'2018-05-30 13:22:05',0),(1090,'system','Akeneo\\Channel\\Component\\Model\\Locale','565','a:1:{s:4:\"code\";s:5:\"or_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"or_IN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1091,'system','Akeneo\\Channel\\Component\\Model\\Locale','566','a:1:{s:4:\"code\";s:5:\"pa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pa_IN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1092,'system','Akeneo\\Channel\\Component\\Model\\Locale','567','a:1:{s:4:\"code\";s:5:\"pl_PL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pl_PL\";}}',NULL,1,'2018-05-30 13:22:05',0),(1093,'system','Akeneo\\Channel\\Component\\Model\\Locale','568','a:1:{s:4:\"code\";s:6:\"prs_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"prs_AF\";}}',NULL,1,'2018-05-30 13:22:05',0),(1094,'system','Akeneo\\Channel\\Component\\Model\\Locale','569','a:1:{s:4:\"code\";s:5:\"ps_AF\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ps_AF\";}}',NULL,1,'2018-05-30 13:22:05',0),(1095,'system','Akeneo\\Channel\\Component\\Model\\Locale','570','a:1:{s:4:\"code\";s:5:\"pt_BR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_BR\";}}',NULL,1,'2018-05-30 13:22:05',0),(1096,'system','Akeneo\\Channel\\Component\\Model\\Locale','571','a:1:{s:4:\"code\";s:5:\"pt_PT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"pt_PT\";}}',NULL,1,'2018-05-30 13:22:05',0),(1097,'system','Akeneo\\Channel\\Component\\Model\\Locale','572','a:1:{s:4:\"code\";s:6:\"qut_GT\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"qut_GT\";}}',NULL,1,'2018-05-30 13:22:05',0),(1098,'system','Akeneo\\Channel\\Component\\Model\\Locale','573','a:1:{s:4:\"code\";s:6:\"quz_BO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_BO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1099,'system','Akeneo\\Channel\\Component\\Model\\Locale','574','a:1:{s:4:\"code\";s:6:\"quz_EC\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_EC\";}}',NULL,1,'2018-05-30 13:22:05',0),(1100,'system','Akeneo\\Channel\\Component\\Model\\Locale','575','a:1:{s:4:\"code\";s:6:\"quz_PE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"quz_PE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1101,'system','Akeneo\\Channel\\Component\\Model\\Locale','576','a:1:{s:4:\"code\";s:5:\"rm_CH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rm_CH\";}}',NULL,1,'2018-05-30 13:22:05',0),(1102,'system','Akeneo\\Channel\\Component\\Model\\Locale','577','a:1:{s:4:\"code\";s:5:\"ro_RO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ro_RO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1103,'system','Akeneo\\Channel\\Component\\Model\\Locale','578','a:1:{s:4:\"code\";s:5:\"ru_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ru_RU\";}}',NULL,1,'2018-05-30 13:22:05',0),(1104,'system','Akeneo\\Channel\\Component\\Model\\Locale','579','a:1:{s:4:\"code\";s:5:\"rw_RW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"rw_RW\";}}',NULL,1,'2018-05-30 13:22:05',0),(1105,'system','Akeneo\\Channel\\Component\\Model\\Locale','580','a:1:{s:4:\"code\";s:6:\"sah_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sah_RU\";}}',NULL,1,'2018-05-30 13:22:05',0),(1106,'system','Akeneo\\Channel\\Component\\Model\\Locale','581','a:1:{s:4:\"code\";s:5:\"sa_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sa_IN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1107,'system','Akeneo\\Channel\\Component\\Model\\Locale','582','a:1:{s:4:\"code\";s:5:\"se_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_FI\";}}',NULL,1,'2018-05-30 13:22:05',0),(1108,'system','Akeneo\\Channel\\Component\\Model\\Locale','583','a:1:{s:4:\"code\";s:5:\"se_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_NO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1109,'system','Akeneo\\Channel\\Component\\Model\\Locale','584','a:1:{s:4:\"code\";s:5:\"se_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"se_SE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1110,'system','Akeneo\\Channel\\Component\\Model\\Locale','585','a:1:{s:4:\"code\";s:5:\"si_LK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"si_LK\";}}',NULL,1,'2018-05-30 13:22:05',0),(1111,'system','Akeneo\\Channel\\Component\\Model\\Locale','586','a:1:{s:4:\"code\";s:5:\"sk_SK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sk_SK\";}}',NULL,1,'2018-05-30 13:22:05',0),(1112,'system','Akeneo\\Channel\\Component\\Model\\Locale','587','a:1:{s:4:\"code\";s:5:\"sl_SI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sl_SI\";}}',NULL,1,'2018-05-30 13:22:05',0),(1113,'system','Akeneo\\Channel\\Component\\Model\\Locale','588','a:1:{s:4:\"code\";s:6:\"sma_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_NO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1114,'system','Akeneo\\Channel\\Component\\Model\\Locale','589','a:1:{s:4:\"code\";s:6:\"sma_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sma_SE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1115,'system','Akeneo\\Channel\\Component\\Model\\Locale','590','a:1:{s:4:\"code\";s:6:\"smj_NO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_NO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1116,'system','Akeneo\\Channel\\Component\\Model\\Locale','591','a:1:{s:4:\"code\";s:6:\"smj_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smj_SE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1117,'system','Akeneo\\Channel\\Component\\Model\\Locale','592','a:1:{s:4:\"code\";s:6:\"smn_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"smn_FI\";}}',NULL,1,'2018-05-30 13:22:05',0),(1118,'system','Akeneo\\Channel\\Component\\Model\\Locale','593','a:1:{s:4:\"code\";s:6:\"sms_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"sms_FI\";}}',NULL,1,'2018-05-30 13:22:05',0),(1119,'system','Akeneo\\Channel\\Component\\Model\\Locale','594','a:1:{s:4:\"code\";s:5:\"sq_AL\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sq_AL\";}}',NULL,1,'2018-05-30 13:22:05',0),(1120,'system','Akeneo\\Channel\\Component\\Model\\Locale','595','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_BA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1121,'system','Akeneo\\Channel\\Component\\Model\\Locale','596','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_CS\";}}',NULL,1,'2018-05-30 13:22:05',0),(1122,'system','Akeneo\\Channel\\Component\\Model\\Locale','597','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_ME\";}}',NULL,1,'2018-05-30 13:22:05',0),(1123,'system','Akeneo\\Channel\\Component\\Model\\Locale','598','a:1:{s:4:\"code\";s:10:\"sr_Cyrl_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Cyrl_RS\";}}',NULL,1,'2018-05-30 13:22:05',0),(1124,'system','Akeneo\\Channel\\Component\\Model\\Locale','599','a:1:{s:4:\"code\";s:10:\"sr_Latn_BA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_BA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1125,'system','Akeneo\\Channel\\Component\\Model\\Locale','600','a:1:{s:4:\"code\";s:10:\"sr_Latn_CS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_CS\";}}',NULL,1,'2018-05-30 13:22:05',0),(1126,'system','Akeneo\\Channel\\Component\\Model\\Locale','601','a:1:{s:4:\"code\";s:10:\"sr_Latn_ME\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_ME\";}}',NULL,1,'2018-05-30 13:22:05',0),(1127,'system','Akeneo\\Channel\\Component\\Model\\Locale','602','a:1:{s:4:\"code\";s:10:\"sr_Latn_RS\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"sr_Latn_RS\";}}',NULL,1,'2018-05-30 13:22:05',0),(1128,'system','Akeneo\\Channel\\Component\\Model\\Locale','603','a:1:{s:4:\"code\";s:5:\"sv_FI\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_FI\";}}',NULL,1,'2018-05-30 13:22:05',0),(1129,'system','Akeneo\\Channel\\Component\\Model\\Locale','604','a:1:{s:4:\"code\";s:5:\"sv_SE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sv_SE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1130,'system','Akeneo\\Channel\\Component\\Model\\Locale','605','a:1:{s:4:\"code\";s:5:\"sw_KE\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"sw_KE\";}}',NULL,1,'2018-05-30 13:22:05',0),(1131,'system','Akeneo\\Channel\\Component\\Model\\Locale','606','a:1:{s:4:\"code\";s:6:\"syr_SY\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"syr_SY\";}}',NULL,1,'2018-05-30 13:22:05',0),(1132,'system','Akeneo\\Channel\\Component\\Model\\Locale','607','a:1:{s:4:\"code\";s:5:\"ta_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ta_IN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1133,'system','Akeneo\\Channel\\Component\\Model\\Locale','608','a:1:{s:4:\"code\";s:5:\"te_IN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"te_IN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1134,'system','Akeneo\\Channel\\Component\\Model\\Locale','609','a:1:{s:4:\"code\";s:10:\"tg_Cyrl_TJ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"tg_Cyrl_TJ\";}}',NULL,1,'2018-05-30 13:22:05',0),(1135,'system','Akeneo\\Channel\\Component\\Model\\Locale','610','a:1:{s:4:\"code\";s:5:\"th_TH\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"th_TH\";}}',NULL,1,'2018-05-30 13:22:05',0),(1136,'system','Akeneo\\Channel\\Component\\Model\\Locale','611','a:1:{s:4:\"code\";s:5:\"tk_TM\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tk_TM\";}}',NULL,1,'2018-05-30 13:22:05',0),(1137,'system','Akeneo\\Channel\\Component\\Model\\Locale','612','a:1:{s:4:\"code\";s:5:\"tn_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tn_ZA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1138,'system','Akeneo\\Channel\\Component\\Model\\Locale','613','a:1:{s:4:\"code\";s:5:\"tr_TR\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tr_TR\";}}',NULL,1,'2018-05-30 13:22:05',0),(1139,'system','Akeneo\\Channel\\Component\\Model\\Locale','614','a:1:{s:4:\"code\";s:5:\"tt_RU\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"tt_RU\";}}',NULL,1,'2018-05-30 13:22:05',0),(1140,'system','Akeneo\\Channel\\Component\\Model\\Locale','615','a:1:{s:4:\"code\";s:11:\"tzm_Latn_DZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"tzm_Latn_DZ\";}}',NULL,1,'2018-05-30 13:22:05',0),(1141,'system','Akeneo\\Channel\\Component\\Model\\Locale','616','a:1:{s:4:\"code\";s:5:\"ug_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ug_CN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1142,'system','Akeneo\\Channel\\Component\\Model\\Locale','617','a:1:{s:4:\"code\";s:5:\"uk_UA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"uk_UA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1143,'system','Akeneo\\Channel\\Component\\Model\\Locale','618','a:1:{s:4:\"code\";s:5:\"ur_PK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"ur_PK\";}}',NULL,1,'2018-05-30 13:22:05',0),(1144,'system','Akeneo\\Channel\\Component\\Model\\Locale','619','a:1:{s:4:\"code\";s:10:\"uz_Cyrl_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Cyrl_UZ\";}}',NULL,1,'2018-05-30 13:22:05',0),(1145,'system','Akeneo\\Channel\\Component\\Model\\Locale','620','a:1:{s:4:\"code\";s:10:\"uz_Latn_UZ\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"uz_Latn_UZ\";}}',NULL,1,'2018-05-30 13:22:05',0),(1146,'system','Akeneo\\Channel\\Component\\Model\\Locale','621','a:1:{s:4:\"code\";s:5:\"vi_VN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"vi_VN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1147,'system','Akeneo\\Channel\\Component\\Model\\Locale','622','a:1:{s:4:\"code\";s:5:\"wo_SN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"wo_SN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1148,'system','Akeneo\\Channel\\Component\\Model\\Locale','623','a:1:{s:4:\"code\";s:5:\"xh_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"xh_ZA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1149,'system','Akeneo\\Channel\\Component\\Model\\Locale','624','a:1:{s:4:\"code\";s:5:\"yo_NG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"yo_NG\";}}',NULL,1,'2018-05-30 13:22:05',0),(1150,'system','Akeneo\\Channel\\Component\\Model\\Locale','625','a:1:{s:4:\"code\";s:5:\"zh_CN\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_CN\";}}',NULL,1,'2018-05-30 13:22:05',0),(1151,'system','Akeneo\\Channel\\Component\\Model\\Locale','626','a:1:{s:4:\"code\";s:5:\"zh_HK\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_HK\";}}',NULL,1,'2018-05-30 13:22:05',0),(1152,'system','Akeneo\\Channel\\Component\\Model\\Locale','627','a:1:{s:4:\"code\";s:5:\"zh_MO\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_MO\";}}',NULL,1,'2018-05-30 13:22:05',0),(1153,'system','Akeneo\\Channel\\Component\\Model\\Locale','628','a:1:{s:4:\"code\";s:5:\"zh_SG\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_SG\";}}',NULL,1,'2018-05-30 13:22:05',0),(1154,'system','Akeneo\\Channel\\Component\\Model\\Locale','629','a:1:{s:4:\"code\";s:5:\"zh_TW\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zh_TW\";}}',NULL,1,'2018-05-30 13:22:05',0),(1155,'system','Akeneo\\Channel\\Component\\Model\\Locale','630','a:1:{s:4:\"code\";s:5:\"zu_ZA\";}','a:1:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"zu_ZA\";}}',NULL,1,'2018-05-30 13:22:05',0),(1156,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Category','32','a:4:{s:4:\"code\";s:7:\"default\";s:6:\"parent\";N;s:11:\"label-en_US\";s:14:\"Master catalog\";s:11:\"label-fr_FR\";s:18:\" Catalog principal\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"default\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Master catalog\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:18:\" Catalog principal\";}}',NULL,1,'2018-05-30 13:22:05',0),(1157,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','8','a:4:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:0:\"\";s:11:\"label-en_US\";s:5:\"Other\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:100;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Other\";}}',NULL,1,'2018-05-30 13:22:05',0),(1158,'system','Pim\\Bundle\\CatalogBundle\\Entity\\Attribute','29','a:30:{s:4:\"code\";s:3:\"sku\";s:4:\"type\";s:22:\"pim_catalog_identifier\";s:5:\"group\";s:5:\"other\";s:6:\"unique\";b:1;s:22:\"useable_as_grid_filter\";b:1;s:18:\"allowed_extensions\";s:0:\"\";s:13:\"metric_family\";N;s:19:\"default_metric_unit\";N;s:19:\"reference_data_name\";N;s:17:\"available_locales\";s:0:\"\";s:14:\"max_characters\";N;s:15:\"validation_rule\";N;s:17:\"validation_regexp\";N;s:15:\"wysiwyg_enabled\";N;s:10:\"number_min\";N;s:10:\"number_max\";N;s:16:\"decimals_allowed\";N;s:16:\"negative_allowed\";N;s:8:\"date_min\";N;s:8:\"date_max\";N;s:13:\"max_file_size\";N;s:20:\"minimum_input_length\";N;s:10:\"sort_order\";i:1;s:11:\"localizable\";b:0;s:19:\"auto_option_sorting\";N;s:11:\"label-en_US\";s:3:\"SKU\";s:11:\"label-fr_FR\";s:3:\"SKU\";s:7:\"options\";N;s:5:\"scope\";s:6:\"Global\";s:8:\"required\";b:1;}','a:10:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"pim_catalog_identifier\";}s:5:\"group\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"other\";}s:6:\"unique\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:22:\"useable_as_grid_filter\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}s:10:\"sort_order\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";i:1;}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"SKU\";}s:5:\"scope\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Global\";}s:8:\"required\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";b:1;}}',NULL,1,'2018-05-30 13:22:05',0),(1159,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup','8','a:4:{s:4:\"code\";s:5:\"other\";s:10:\"sort_order\";i:100;s:10:\"attributes\";s:3:\"sku\";s:11:\"label-en_US\";s:5:\"Other\";}','a:1:{s:10:\"attributes\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"sku\";}}',NULL,2,'2018-05-30 13:22:05',0),(1160,'system','Akeneo\\Channel\\Component\\Model\\Channel','5','a:7:{s:4:\"code\";s:9:\"ecommerce\";s:10:\"currencies\";s:7:\"EUR,USD\";s:7:\"locales\";s:11:\"en_US,fr_FR\";s:11:\"label-en_US\";s:9:\"Ecommerce\";s:11:\"label-de_DE\";s:9:\"Ecommerce\";s:11:\"label-fr_FR\";s:9:\"Ecommerce\";s:8:\"category\";s:7:\"default\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"ecommerce\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"EUR,USD\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"en_US,fr_FR\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"Ecommerce\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"default\";}}',NULL,1,'2018-05-30 13:22:05',0),(1161,'system','Akeneo\\Channel\\Component\\Model\\Channel','6','a:7:{s:4:\"code\";s:6:\"mobile\";s:10:\"currencies\";s:3:\"EUR\";s:7:\"locales\";s:5:\"fr_FR\";s:11:\"label-en_US\";s:6:\"Mobile\";s:11:\"label-de_DE\";s:5:\"Mobil\";s:11:\"label-fr_FR\";s:6:\"Mobile\";s:8:\"category\";s:7:\"default\";}','a:7:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"mobile\";}s:10:\"currencies\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:3:\"EUR\";}s:7:\"locales\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"fr_FR\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Mobile\";}s:11:\"label-de_DE\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:5:\"Mobil\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Mobile\";}s:8:\"category\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:7:\"default\";}}',NULL,1,'2018-05-30 13:22:05',0),(1162,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','9','a:3:{s:4:\"code\";s:6:\"X_SELL\";s:11:\"label-en_US\";s:10:\"Cross sell\";s:11:\"label-fr_FR\";s:14:\"Vente croisée\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"X_SELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:10:\"Cross sell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:14:\"Vente croisée\";}}',NULL,1,'2018-05-30 13:22:05',0),(1163,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','10','a:3:{s:4:\"code\";s:6:\"UPSELL\";s:11:\"label-en_US\";s:6:\"Upsell\";s:11:\"label-fr_FR\";s:16:\"Vente incitative\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"UPSELL\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"Upsell\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"Vente incitative\";}}',NULL,1,'2018-05-30 13:22:05',0),(1164,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','11','a:3:{s:4:\"code\";s:12:\"SUBSTITUTION\";s:11:\"label-en_US\";s:12:\"Substitution\";s:11:\"label-fr_FR\";s:12:\"Remplacement\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"SUBSTITUTION\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Substitution\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"Remplacement\";}}',NULL,1,'2018-05-30 13:22:05',0),(1165,'system','Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType','12','a:3:{s:4:\"code\";s:4:\"PACK\";s:11:\"label-en_US\";s:4:\"Pack\";s:11:\"label-fr_FR\";s:4:\"Pack\";}','a:3:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"PACK\";}s:11:\"label-en_US\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}s:11:\"label-fr_FR\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:4:\"Pack\";}}',NULL,1,'2018-05-30 13:22:05',0),(1166,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','109','a:5:{s:4:\"code\";s:17:\"add_product_value\";s:5:\"label\";s:24:\"Mass add products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:17:\"add_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1167,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','110','a:5:{s:4:\"code\";s:20:\"update_product_value\";s:5:\"label\";s:20:\"Mass update products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"update_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass update products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1168,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','111','a:5:{s:4:\"code\";s:20:\"remove_product_value\";s:5:\"label\";s:27:\"Mass remove products values\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_product_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove products values\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1169,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','112','a:5:{s:4:\"code\";s:16:\"move_to_category\";s:5:\"label\";s:23:\"Mass move to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:16:\"move_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass move to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1170,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','113','a:5:{s:4:\"code\";s:15:\"add_association\";s:5:\"label\";s:23:\"Mass associate products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_association\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:23:\"Mass associate products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1171,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','114','a:5:{s:4:\"code\";s:15:\"add_to_category\";s:5:\"label\";s:22:\"Mass add to categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:15:\"add_to_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"Mass add to categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1172,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','115','a:5:{s:4:\"code\";s:20:\"remove_from_category\";s:5:\"label\";s:27:\"Mass remove from categories\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"remove_from_category\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:27:\"Mass remove from categories\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1173,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','116','a:5:{s:4:\"code\";s:12:\"add_to_group\";s:5:\"label\";s:25:\"Mass add product to group\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"add_to_group\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"Mass add product to group\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1174,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','117','a:5:{s:4:\"code\";s:22:\"edit_common_attributes\";s:5:\"label\";s:28:\"Mass edit product attributes\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:22:\"edit_common_attributes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:28:\"Mass edit product attributes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1175,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','118','a:5:{s:4:\"code\";s:19:\"add_attribute_value\";s:5:\"label\";s:24:\"Mass add attribute value\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:19:\"add_attribute_value\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"Mass add attribute value\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1176,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','119','a:5:{s:4:\"code\";s:26:\"set_attribute_requirements\";s:5:\"label\";s:33:\"Set family attribute requirements\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"set_attribute_requirements\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Set family attribute requirements\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1177,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','120','a:5:{s:4:\"code\";s:34:\"delete_products_and_product_models\";s:5:\"label\";s:20:\"Mass delete products\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:11:\"mass_delete\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"delete_products_and_product_models\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Mass delete products\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:11:\"mass_delete\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1178,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','121','a:5:{s:4:\"code\";s:29:\"add_to_existing_product_model\";s:5:\"label\";s:29:\"Add to existing product model\";s:9:\"connector\";s:26:\"Akeneo Mass Edit Connector\";s:4:\"type\";s:9:\"mass_edit\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"add_to_existing_product_model\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:29:\"Add to existing product model\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"Akeneo Mass Edit Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:9:\"mass_edit\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1179,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','122','a:5:{s:4:\"code\";s:24:\"csv_product_quick_export\";s:5:\"label\";s:24:\"CSV product quick export\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:388:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products-quick-export.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product-models-quick-export.csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"csv_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:24:\"CSV product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:388:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products-quick-export.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product-models-quick-export.csv\"}\";}}',NULL,1,'2018-05-30 13:22:06',0),(1180,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','123','a:5:{s:4:\"code\";s:37:\"csv_product_grid_context_quick_export\";s:5:\"label\";s:37:\"CSV product quick export grid context\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:436:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.csv\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"csv_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:37:\"CSV product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:436:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.csv\",\"delimiter\":\";\",\"enclosure\":\"\\\"\",\"withHeader\":true,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.csv\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.csv\"}\";}}',NULL,1,'2018-05-30 13:22:06',0),(1181,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','124','a:5:{s:4:\"code\";s:25:\"xlsx_product_quick_export\";s:5:\"label\";s:25:\"XLSX product quick export\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:401:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.xlsx\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"xlsx_product_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:25:\"XLSX product quick export\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:401:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_%locale%_%scope%.xlsx\"}\";}}',NULL,1,'2018-05-30 13:22:06',0),(1182,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','125','a:5:{s:4:\"code\";s:38:\"xlsx_product_grid_context_quick_export\";s:5:\"label\";s:38:\"XLSX product quick export grid context\";s:9:\"connector\";s:21:\"Akeneo XLSX Connector\";s:4:\"type\";s:12:\"quick_export\";s:13:\"configuration\";s:427:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.xlsx\"}\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"xlsx_product_grid_context_quick_export\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:38:\"XLSX product quick export grid context\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:21:\"Akeneo XLSX Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:12:\"quick_export\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:427:\"{\"filePath\":\"\\/tmp\\/php\\/export_%job_label%_%datetime%.xlsx\",\"withHeader\":true,\"linesPerFile\":10000,\"user_to_notify\":null,\"is_user_authenticated\":false,\"filters\":null,\"selected_properties\":null,\"with_media\":true,\"locale\":null,\"scope\":null,\"ui_locale\":null,\"filePathProduct\":\"\\/tmp\\/1_products_export_grid_context_%locale%_%scope%.xlsx\",\"filePathProductModel\":\"\\/tmp\\/2_product_models_export_grid_context_%locale%_%scope%.xlsx\"}\";}}',NULL,1,'2018-05-30 13:22:06',0),(1183,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','126','a:5:{s:4:\"code\";s:26:\"csv_default_product_import\";s:5:\"label\";s:26:\"CSV default product import\";s:9:\"connector\";s:20:\"Akeneo CSV Connector\";s:4:\"type\";s:6:\"import\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"csv_default_product_import\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:26:\"CSV default product import\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:20:\"Akeneo CSV Connector\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:6:\"import\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1184,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','127','a:5:{s:4:\"code\";s:34:\"compute_product_models_descendants\";s:5:\"label\";s:34:\"Compute product models descendants\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:34:\"compute_product_models_descendants\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"Compute product models descendants\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:34:\"compute_product_models_descendants\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1185,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','128','a:5:{s:4:\"code\";s:39:\"compute_completeness_of_products_family\";s:5:\"label\";s:39:\"compute completeness of products family\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:39:\"compute_completeness_of_products_family\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute completeness of products family\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:39:\"compute_completeness_of_products_family\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0),(1186,'system','Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance','129','a:5:{s:4:\"code\";s:40:\"compute_family_variant_structure_changes\";s:5:\"label\";s:33:\"Compute variant structure changes\";s:9:\"connector\";s:8:\"internal\";s:4:\"type\";s:40:\"compute_family_variant_structure_changes\";s:13:\"configuration\";s:2:\"[]\";}','a:5:{s:4:\"code\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:5:\"label\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:33:\"Compute variant structure changes\";}s:9:\"connector\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:8:\"internal\";}s:4:\"type\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:40:\"compute_family_variant_structure_changes\";}s:13:\"configuration\";a:2:{s:3:\"old\";s:0:\"\";s:3:\"new\";s:2:\"[]\";}}',NULL,1,'2018-05-30 13:22:06',0);
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

-- Dump completed on 2018-05-30 15:22:07
