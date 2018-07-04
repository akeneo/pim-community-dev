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
-- Dumping data for table `acme_reference_data_color`
--

/*!40000 ALTER TABLE `acme_reference_data_color` DISABLE KEYS */;
INSERT INTO `acme_reference_data_color` VALUES (7,'colorA',1,'colorA','#colora',31,95,52,28,72,0,45,35),(8,'colorB',1,'colorB','#colorb',49,54,16,46,29,79,12,95),(9,'colorc',1,'colorc','#colorc',10,45,20,88,8,59,19,32);
/*!40000 ALTER TABLE `acme_reference_data_color` ENABLE KEYS */;

--
-- Dumping data for table `acme_reference_data_fabric`
--

/*!40000 ALTER TABLE `acme_reference_data_fabric` DISABLE KEYS */;
INSERT INTO `acme_reference_data_fabric` VALUES (5,'fabricA',1,'fabricA',NULL),(6,'fabricB',1,'fabricB',NULL);
/*!40000 ALTER TABLE `acme_reference_data_fabric` ENABLE KEYS */;

--
-- Dumping data for table `akeneo_file_storage_file_info`
--

/*!40000 ALTER TABLE `akeneo_file_storage_file_info` DISABLE KEYS */;
INSERT INTO `akeneo_file_storage_file_info` VALUES (35,'8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt','fileA.txt','text/plain',1048576,'txt','6545089471ba53d660d22d7b8dc8dd67904b1e28','catalogStorage'),(36,'3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg','imageA.jpg','image/jpeg',1048576,'jpg','a9453e6ce89dbfd776ecae609e1c23e9cfad8277','catalogStorage'),(37,'7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg','imageB-en_US.jpg','image/jpeg',1048576,'jpg','16850b6741c6e0d7622edb29465488571a2e63df','catalogStorage'),(38,'0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg','imageB-fr_FR.jpg','image/jpeg',1048576,'jpg','058c6f380b0888afadf7341f8baaf58b538e5774','catalogStorage');
/*!40000 ALTER TABLE `akeneo_file_storage_file_info` ENABLE KEYS */;

--
-- Dumping data for table `pim_catalog_association_type`
--

/*!40000 ALTER TABLE `pim_catalog_association_type` DISABLE KEYS */;
DELETE FROM `pim_catalog_association_type`;
INSERT INTO `pim_catalog_association_type` (`id`, `code`, `created`, `updated`) VALUES
(865, 'X_SELL', '2016-10-04 16:14:44', '2016-10-04 16:14:44'),
(866, 'UPSELL', '2016-10-04 16:14:44', '2016-10-04 16:14:44'),
(867, 'SUBSTITUTION', '2016-10-04 16:14:44', '2016-10-04 16:14:44'),
(868, 'PACK', '2016-10-04 16:14:44', '2016-10-04 16:14:44');
/*!40000 ALTER TABLE `pim_catalog_association_type` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_attribute_group`
--

/*!40000 ALTER TABLE `pim_catalog_attribute_group` DISABLE KEYS */;
DELETE FROM `pim_catalog_attribute_group`;
INSERT INTO `pim_catalog_attribute_group` VALUES (593,'other',100,'2016-08-04 14:28:49','2016-08-04 14:28:49'),(594,'attributeGroupA',0,'2016-08-04 14:28:49','2016-08-04 14:28:49'),(595,'attributeGroupB',0,'2016-08-04 14:28:49','2016-08-04 14:28:49');
/*!40000 ALTER TABLE `pim_catalog_attribute_group` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_attribute`
--
--
DELETE FROM `pim_catalog_attribute`;
/*!40000 ALTER TABLE `pim_catalog_attribute` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute` VALUES
(2523,594,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,1,1,0,0,'sku','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_identifier','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2524,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_date','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_date','date','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2525,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_file','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_file','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2526,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'an_image','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_image','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2527,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,'Power','KILOWATT',NULL,'',NULL,0,0,0,0,'a_metric','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2528,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_multi_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_multiselect','options','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2529,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_float','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2530,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_float_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2531,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_integer','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2532,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_price','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2533,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_ref_data_multi_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_reference_data_multiselect','reference_data_options','a:1:{s:19:\"reference_data_name\";s:7:\"fabrics\";}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2534,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_ref_data_simple_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_reference_data_simpleselect','reference_data_option','a:1:{s:19:\"reference_data_name\";s:5:\"color\";}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2535,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_simple_select','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_simpleselect','option','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2536,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_text','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_text','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2537,594,0,0,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_text_area','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_textarea','textarea','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2538,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_yes_no','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_boolean','boolean','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2539,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,0,'a_localizable_image','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_image','media','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2540,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,1,'a_scopable_price','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2541,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,1,1,'a_localized_and_scopable_text_area','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_textarea','textarea','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(2542,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,'Length','METER',NULL,'',NULL,0,0,0,0,'a_metric_without_decimal','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34'),
(2543,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL,'Temperature','CELSIUS',NULL,'',NULL,0,0,0,0,'a_metric_without_decimal_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34'),
(2544,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'Temperature','CELSIUS',NULL,'',NULL,0,0,0,0,'a_metric_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_metric','metric','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34'),
(2545,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_price_without_decimal','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_price_collection','prices','a:1:{s:19:"reference_data_name";N;}','2016-10-05 13:52:34','2016-10-05 13:52:34'),
(2546,595,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'a_number_integer_negative','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_number','decimal','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:49','2016-08-04 14:28:49'),
(2547,594,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,0,0,0,0,'123','Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product','pim_catalog_text','text','a:1:{s:19:\"reference_data_name\";N;}','2016-08-04 14:28:50','2016-08-04 14:28:50');
/*!40000 ALTER TABLE `pim_catalog_attribute` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_attribute_option`
--

/*!40000 ALTER TABLE `pim_catalog_attribute_option` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_option` VALUES (3801,2528,'optionA',1),(3802,2528,'optionB',1),(3803,2535,'optionA',1),(3804,2535,'optionB',1);
/*!40000 ALTER TABLE `pim_catalog_attribute_option` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_attribute_requirement`
--

/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_requirement` VALUES (6705,466,2523,209,1),(6706,466,2523,210,1),(6707,466,2524,209,1),(6708,466,2525,209,1),(6709,466,2527,209,1),(6710,466,2528,209,1),(6711,466,2529,209,1),(6712,466,2530,209,1),(6713,466,2531,209,1),(6714,466,2532,209,1),(6715,466,2533,209,1),(6716,466,2534,209,1),(6717,466,2535,209,1),(6718,466,2536,209,1),(6719,466,2537,209,1),(6720,466,2538,209,1),(6721,466,2526,209,1),(6722,466,2539,209,1),(6723,466,2540,209,1),(6724,466,2541,209,1),(6725,466,2524,210,1),(6726,466,2525,210,1),(6727,466,2527,210,1),(6728,466,2528,210,1),(6729,466,2529,210,1),(6730,466,2530,210,1),(6731,466,2531,210,1),(6732,466,2532,210,1),(6733,466,2533,210,1),(6734,466,2534,210,1),(6735,466,2535,210,1),(6736,466,2536,210,1),(6737,466,2537,210,1),(6738,466,2538,210,1),(6739,466,2526,210,1),(6740,466,2539,210,1),(6741,466,2540,210,1),(6742,466,2541,210,1);
/*!40000 ALTER TABLE `pim_catalog_attribute_requirement` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_attribute_translation`
--

/*!40000 ALTER TABLE `pim_catalog_attribute_translation` DISABLE KEYS */;
INSERT INTO `pim_catalog_attribute_translation` VALUES (3521,2523,'SKU','en_US');
/*!40000 ALTER TABLE `pim_catalog_attribute_translation` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_category`
--

/*!40000 ALTER TABLE `pim_catalog_category` DISABLE KEYS */;
DELETE FROM `pim_catalog_category`;
INSERT INTO `pim_catalog_category` VALUES
(895,NULL,'master','2016-08-04 14:28:49',895,0,1,10),
(896,895,'categoryA','2016-08-04 14:28:49',895,1,2,7),
(897,896,'categoryA1','2016-08-04 14:28:49',895,2,3,4),
(898,896,'categoryA2','2016-08-04 14:28:49',895,2,5,6),
(899,895,'categoryB','2016-08-04 14:28:49',895,1,8,9);
/*!40000 ALTER TABLE `pim_catalog_category` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_channel`
--

/*!40000 ALTER TABLE `pim_catalog_channel` DISABLE KEYS */;
DELETE FROM `pim_catalog_channel`;
INSERT INTO `pim_catalog_channel` VALUES (209,895,'ecommerce','a:0:{}'),(210,895,'tablet','a:0:{}');
/*!40000 ALTER TABLE `pim_catalog_channel` ENABLE KEYS */;

--
-- Dumping data for table `pim_catalog_currency`
--

/*!40000 ALTER TABLE `pim_catalog_channel_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_currency` VALUES (10101,'USD',1),(10102,'EUR',1);
/*!40000 ALTER TABLE `pim_catalog_channel_currency` ENABLE KEYS */;

--
-- Dumping data for table `pim_catalog_channel_currency`
--

/*!40000 ALTER TABLE `pim_catalog_channel_currency` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_currency` VALUES (209,10101),(210,10102);
/*!40000 ALTER TABLE `pim_catalog_channel_currency` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_channel_locale`
--

/*!40000 ALTER TABLE `pim_catalog_channel_locale` DISABLE KEYS */;
INSERT INTO `pim_catalog_channel_locale` VALUES (209,21058),(210,21039),(210,21058),(210,21090);
/*!40000 ALTER TABLE `pim_catalog_channel_locale` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_family`
--

/*!40000 ALTER TABLE `pim_catalog_family` DISABLE KEYS */;
DELETE FROM `pim_catalog_family`;
INSERT INTO `pim_catalog_family` VALUES
(466,2523,NULL,'familyA','2016-08-04 14:28:50','2016-08-04 14:28:50'),
(467,2523,NULL,'familyB','2016-08-04 14:28:50','2016-08-04 14:28:50');
/*!40000 ALTER TABLE `pim_catalog_family` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_family_translation`
--

/*!40000 ALTER TABLE `pim_catalog_family_translation` DISABLE KEYS */;
DELETE FROM `pim_catalog_family_translation`;
INSERT INTO `pim_catalog_family_translation` (`id`, `foreign_key`, `label`, `locale`)
VALUES
	(1,466,'Une famille A','fr_FR'),
	(2,466,'A family A','en_US');
/*!40000 ALTER TABLE `pim_catalog_family_translation` ENABLE KEYS */;

--
-- Dumping data for table `pim_catalog_family_attribute`
--

/*!40000 ALTER TABLE `pim_catalog_family_attribute` DISABLE KEYS */;
DELETE FROM `pim_catalog_family_attribute`;
INSERT INTO `pim_catalog_family_attribute` VALUES (466,2523),(466,2524),(466,2525),(466,2526),(466,2527),(466,2528),(466,2529),(466,2530),(466,2531),(466,2532),(466,2533),(466,2534),(466,2535),(466,2536),(466,2537),(466,2538),(466,2539),(466,2540),(466,2541);
/*!40000 ALTER TABLE `pim_catalog_family_attribute` ENABLE KEYS */;


/*!40000 ALTER TABLE `pim_catalog_family_variant` DISABLE KEYS */;
DELETE FROM `pim_catalog_family_variant`;
INSERT INTO `pim_catalog_family_variant` (`id`, `family_id`, `code`) VALUES
(25,	466,	'familyVariantA1'),
(26,	466,	'familyVariantA2'),
(27,	466,	'familyVariantB1');
/*!40000 ALTER TABLE `pim_catalog_family_variant` DISABLE KEYS */;

--
-- Dumping data for table `pim_catalog_group`
--

/*!40000 ALTER TABLE `pim_catalog_group` DISABLE KEYS */;
DELETE FROM `pim_catalog_group`;
INSERT INTO `pim_catalog_group` VALUES (240,323,'groupA'),(241,323,'groupB');
/*!40000 ALTER TABLE `pim_catalog_group` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_group_type`
--

/*!40000 ALTER TABLE `pim_catalog_group_type` DISABLE KEYS */;
DELETE FROM `pim_catalog_group_type`;
INSERT INTO `pim_catalog_group_type` VALUES (323,'RELATED');
/*!40000 ALTER TABLE `pim_catalog_group_type` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_locale`
--

/*!40000 ALTER TABLE `pim_catalog_locale` DISABLE KEYS */;
DELETE FROM `pim_catalog_locale`;
INSERT INTO `pim_catalog_locale` VALUES (21001,'af_ZA',0),(21002,'am_ET',0),(21003,'ar_AE',0),(21004,'ar_BH',0),(21005,'ar_DZ',0),(21006,'ar_EG',0),(21007,'ar_IQ',0),(21008,'ar_JO',0),(21009,'ar_KW',0),(21010,'ar_LB',0),(21011,'ar_LY',0),(21012,'ar_MA',0),(21013,'arn_CL',0),(21014,'ar_OM',0),(21015,'ar_QA',0),(21016,'ar_SA',0),(21017,'ar_SY',0),(21018,'ar_TN',0),(21019,'ar_YE',0),(21020,'as_IN',0),(21021,'az_Cyrl_AZ',0),(21022,'az_Latn_AZ',0),(21023,'ba_RU',0),(21024,'be_BY',0),(21025,'bg_BG',0),(21026,'bn_BD',0),(21027,'bn_IN',0),(21028,'bo_CN',0),(21029,'br_FR',0),(21030,'bs_Cyrl_BA',0),(21031,'bs_Latn_BA',0),(21032,'ca_ES',0),(21033,'co_FR',0),(21034,'cs_CZ',0),(21035,'cy_GB',0),(21036,'da_DK',0),(21037,'de_AT',0),(21038,'de_CH',0),(21039,'de_DE',1),(21040,'de_LI',0),(21041,'de_LU',0),(21042,'dsb_DE',0),(21043,'dv_MV',0),(21044,'el_GR',0),(21045,'en_029',0),(21046,'en_AU',0),(21047,'en_BZ',0),(21048,'en_CA',0),(21049,'en_GB',0),(21050,'en_IE',0),(21051,'en_IN',0),(21052,'en_JM',0),(21053,'en_MY',0),(21054,'en_NZ',0),(21055,'en_PH',0),(21056,'en_SG',0),(21057,'en_TT',0),(21058,'en_US',1),(21059,'en_ZA',0),(21060,'en_ZW',0),(21061,'es_AR',0),(21062,'es_BO',0),(21063,'es_CL',0),(21064,'es_CO',0),(21065,'es_CR',0),(21066,'es_DO',0),(21067,'es_EC',0),(21068,'es_ES',0),(21069,'es_GT',0),(21070,'es_HN',0),(21071,'es_MX',0),(21072,'es_NI',0),(21073,'es_PA',0),(21074,'es_PE',0),(21075,'es_PR',0),(21076,'es_PY',0),(21077,'es_SV',0),(21078,'es_US',0),(21079,'es_UY',0),(21080,'es_VE',0),(21081,'et_EE',0),(21082,'eu_ES',0),(21083,'fa_IR',0),(21084,'fi_FI',0),(21085,'fil_PH',0),(21086,'fo_FO',0),(21087,'fr_BE',0),(21088,'fr_CA',0),(21089,'fr_CH',0),(21090,'fr_FR',1),(21091,'fr_LU',0),(21092,'fr_MC',0),(21093,'fy_NL',0),(21094,'ga_IE',0),(21095,'gd_GB',0),(21096,'gl_ES',0),(21097,'gsw_FR',0),(21098,'gu_IN',0),(21099,'ha_Latn_NG',0),(21100,'he_IL',0),(21101,'hi_IN',0),(21102,'hr_BA',0),(21103,'hr_HR',0),(21104,'hsb_DE',0),(21105,'hu_HU',0),(21106,'hy_AM',0),(21107,'id_ID',0),(21108,'ig_NG',0),(21109,'ii_CN',0),(21110,'is_IS',0),(21111,'it_CH',0),(21112,'it_IT',0),(21113,'iu_Cans_CA',0),(21114,'iu_Latn_CA',0),(21115,'ja_JP',0),(21116,'ka_GE',0),(21117,'kk_KZ',0),(21118,'kl_GL',0),(21119,'km_KH',0),(21120,'kn_IN',0),(21121,'kok_IN',0),(21122,'ko_KR',0),(21123,'ky_KG',0),(21124,'lb_LU',0),(21125,'lo_LA',0),(21126,'lt_LT',0),(21127,'lv_LV',0),(21128,'mi_NZ',0),(21129,'mk_MK',0),(21130,'ml_IN',0),(21131,'mn_MN',0),(21132,'mn_Mong_CN',0),(21133,'moh_CA',0),(21134,'mr_IN',0),(21135,'ms_BN',0),(21136,'ms_MY',0),(21137,'mt_MT',0),(21138,'nb_NO',0),(21139,'ne_NP',0),(21140,'nl_BE',0),(21141,'nl_NL',0),(21142,'nn_NO',0),(21143,'nso_ZA',0),(21144,'oc_FR',0),(21145,'or_IN',0),(21146,'pa_IN',0),(21147,'pl_PL',0),(21148,'prs_AF',0),(21149,'ps_AF',0),(21150,'pt_BR',0),(21151,'pt_PT',0),(21152,'qut_GT',0),(21153,'quz_BO',0),(21154,'quz_EC',0),(21155,'quz_PE',0),(21156,'rm_CH',0),(21157,'ro_RO',0),(21158,'ru_RU',0),(21159,'rw_RW',0),(21160,'sah_RU',0),(21161,'sa_IN',0),(21162,'se_FI',0),(21163,'se_NO',0),(21164,'se_SE',0),(21165,'si_LK',0),(21166,'sk_SK',0),(21167,'sl_SI',0),(21168,'sma_NO',0),(21169,'sma_SE',0),(21170,'smj_NO',0),(21171,'smj_SE',0),(21172,'smn_FI',0),(21173,'sms_FI',0),(21174,'sq_AL',0),(21175,'sr_Cyrl_BA',0),(21176,'sr_Cyrl_CS',0),(21177,'sr_Cyrl_ME',0),(21178,'sr_Cyrl_RS',0),(21179,'sr_Latn_BA',0),(21180,'sr_Latn_CS',0),(21181,'sr_Latn_ME',0),(21182,'sr_Latn_RS',0),(21183,'sv_FI',0),(21184,'sv_SE',0),(21185,'sw_KE',0),(21186,'syr_SY',0),(21187,'ta_IN',0),(21188,'te_IN',0),(21189,'tg_Cyrl_TJ',0),(21190,'th_TH',0),(21191,'tk_TM',0),(21192,'tn_ZA',0),(21193,'tr_TR',0),(21194,'tt_RU',0),(21195,'tzm_Latn_DZ',0),(21196,'ug_CN',0),(21197,'uk_UA',0),(21198,'ur_PK',0),(21199,'uz_Cyrl_UZ',0),(21200,'uz_Latn_UZ',0),(21201,'vi_VN',0),(21202,'wo_SN',0),(21203,'xh_ZA',0),(21204,'yo_NG',0),(21205,'zh_CN',0),(21206,'zh_HK',0),(21207,'zh_MO',0),(21208,'zh_SG',0),(21209,'zh_TW',0),(21210,'zu_ZA',0);
/*!40000 ALTER TABLE `pim_catalog_locale` ENABLE KEYS */;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-08-04 16:51:35
