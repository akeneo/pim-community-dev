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
-- Dumping data for table `pim_catalog_association`
--

/*!40000 ALTER TABLE `pim_catalog_association` DISABLE KEYS */;
INSERT INTO `pim_catalog_association` VALUES (9,865,49),(8,866,49),(7,868,49),(6,867,49);
/*!40000 ALTER TABLE `pim_catalog_association` ENABLE KEYS */;

--
-- Dumping data for table `pim_catalog_association_group`
--

/*!40000 ALTER TABLE `pim_catalog_association_group` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_group` VALUES (8,240),(9,241);
/*!40000 ALTER TABLE `pim_catalog_association_group` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_association_product`
--

/*!40000 ALTER TABLE `pim_catalog_association_product` DISABLE KEYS */;
INSERT INTO `pim_catalog_association_product` VALUES (7,47),(7,48),(9,47);
/*!40000 ALTER TABLE `pim_catalog_association_product` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_completeness`
--

/*!40000 ALTER TABLE `pim_catalog_completeness` DISABLE KEYS */;
INSERT INTO `pim_catalog_completeness` VALUES (773,21058,209,49,0,19),(774,21039,210,49,2,19),(775,21058,210,49,0,19),(776,21090,210,49,0,19);
/*!40000 ALTER TABLE `pim_catalog_completeness` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_group_product`
--

/*!40000 ALTER TABLE `pim_catalog_group_product` DISABLE KEYS */;
INSERT INTO `pim_catalog_group_product` VALUES (49,239),(49,240),(49,241);
/*!40000 ALTER TABLE `pim_catalog_group_product` ENABLE KEYS */;

--
-- Dumping data for table `pim_catalog_product`
--

/*!40000 ALTER TABLE `pim_catalog_product_model` DISABLE KEYS */;
INSERT INTO `pim_catalog_product_model` (`id`, `code`, `family_variant_id`, `parent_id`, `created`, `updated`, `raw_values`) VALUES
(147,    'bar',  27, NULL,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"bar\"}}}'),
(148,    'baz',  27,   NULL,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"baz\"}}}'),
(149,    'foo',  27, NULL,    '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"foo\"}},\"a_file\":{\"<all_channels>\":{\"<all_locales>\":\"8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt\"}},\"an_image\":{\"<all_channels>\":{\"<all_locales>\":\"3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg\"}},\"a_date\":{\"<all_channels>\":{\"<all_locales>\":\"2016-06-13T00:00:00+02:00\"}},\"a_metric\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"987654321987.1234\",\"unit\":\"KILOWATT\",\"base_data\":\"987654321987123.4000\",\"base_unit\":\"WATT\",\"family\":\"Power\"}}},\"a_metric_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":98,\"unit\":\"CENTIMETER\",\"base_data\":\"0.98\",\"base_unit\":\"METER\",\"family\":\"Length\"}}},\"a_metric_without_decimal_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":-20,\"unit\":\"CELSIUS\",\"base_data\":\"253.150000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_metric_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"-20.5000\",\"unit\":\"CELSIUS\",\"base_data\":\"252.650000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"optionA\",\"optionB\"]}},\"a_number_float\":{\"<all_channels>\":{\"<all_locales>\":\"12.5678\"}},\"a_number_float_negative\":{\"<all_channels>\":{\"<all_locales>\":\"-99.8732\"}},\"a_number_integer\":{\"<all_channels>\":{\"<all_locales>\":42}},\"a_number_integer_negative\":{\"<all_channels>\":{\"<all_locales>\":-42}},\"a_price\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":\"45.00\",\"currency\":\"USD\"},{\"amount\":\"56.53\",\"currency\":\"EUR\"}]}},\"a_price_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":-45,\"currency\":\"USD\"},{\"amount\":56,\"currency\":\"EUR\"}]}},\"a_ref_data_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"fabricA\",\"fabricB\"]}},\"a_ref_data_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"colorB\"}},\"a_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"optionB\"}},\"a_text\":{\"<all_channels>\":{\"<all_locales>\":\"this is a text\"}},\"123\":{\"<all_channels>\":{\"<all_locales>\":\"a text for an attribute with numerical code\"}},\"a_text_area\":{\"<all_channels>\":{\"<all_locales>\":\"this is a very very very very very long  text\"}},\"a_yes_no\":{\"<all_channels>\":{\"<all_locales>\":true}},\"a_localizable_image\":{\"<all_channels>\":{\"en_US\":\"7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg\",\"fr_FR\":\"0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg\"}},\"a_scopable_price\":{\"ecommerce\":{\"<all_locales>\":[{\"amount\":\"15.00\",\"currency\":\"EUR\"},{\"amount\":\"20.00\",\"currency\":\"USD\"}]},\"tablet\":{\"<all_locales>\":[{\"amount\":\"17.00\",\"currency\":\"EUR\"},{\"amount\":\"24.00\",\"currency\":\"USD\"}]}},\"a_localized_and_scopable_text_area\":{\"ecommerce\":{\"en_US\":\"a text area for ecommerce in English\"},\"tablet\":{\"en_US\":\"a text area for tablets in English\",\"fr_FR\":\"une zone de texte pour les tablettes en français\"}}}'),
(150,    'qux',  25, NULL,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"a_text\": {\"<all_channels>\": {\"<all_locales>\": \"this is a text\"}}}'),
(151,    'quux', 25, 150,   '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"a_simple_select\": {\"<all_channels>\": {\"<all_locales>\": \"optionB\"}}}');
/*!40000 ALTER TABLE `pim_catalog_product_model` ENABLE KEYS */;

/*!40000 ALTER TABLE `pim_catalog_product` DISABLE KEYS */;
INSERT INTO `pim_catalog_product` (`id`, `identifier`, `family_id`, `family_variant_id`, `product_model_id`, `is_enabled`, `created`, `updated`, `raw_values`) VALUES
(47,    'bar', NULL, NULL,  NULL, 0,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"bar\"}}}'),
(48,    'baz', NULL, NULL,  NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"baz\"}}}'),
(49,    'foo', 466,  NULL,  NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"foo\"}},\"a_file\":{\"<all_channels>\":{\"<all_locales>\":\"8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt\"}},\"an_image\":{\"<all_channels>\":{\"<all_locales>\":\"3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg\"}},\"a_date\":{\"<all_channels>\":{\"<all_locales>\":\"2016-06-13T00:00:00+02:00\"}},\"a_metric\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"987654321987.1234\",\"unit\":\"KILOWATT\",\"base_data\":\"987654321987123.4000\",\"base_unit\":\"WATT\",\"family\":\"Power\"}}},\"a_metric_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":98,\"unit\":\"CENTIMETER\",\"base_data\":\"0.98\",\"base_unit\":\"METER\",\"family\":\"Length\"}}},\"a_metric_without_decimal_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":-20,\"unit\":\"CELSIUS\",\"base_data\":\"253.150000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_metric_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"-20.5000\",\"unit\":\"CELSIUS\",\"base_data\":\"252.650000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"optionA\",\"optionB\"]}},\"a_number_float\":{\"<all_channels>\":{\"<all_locales>\":\"12.5678\"}},\"a_number_float_negative\":{\"<all_channels>\":{\"<all_locales>\":\"-99.8732\"}},\"a_number_integer\":{\"<all_channels>\":{\"<all_locales>\":42}},\"a_number_integer_negative\":{\"<all_channels>\":{\"<all_locales>\":-42}},\"a_price\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":\"45.00\",\"currency\":\"USD\"},{\"amount\":\"56.53\",\"currency\":\"EUR\"}]}},\"a_price_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":-45,\"currency\":\"USD\"},{\"amount\":56,\"currency\":\"EUR\"}]}},\"a_ref_data_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"fabricA\",\"fabricB\"]}},\"a_ref_data_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"colorB\"}},\"a_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"optionB\"}},\"a_text\":{\"<all_channels>\":{\"<all_locales>\":\"this is a text\"}},\"123\":{\"<all_channels>\":{\"<all_locales>\":\"a text for an attribute with numerical code\"}},\"a_text_area\":{\"<all_channels>\":{\"<all_locales>\":\"this is a very very very very very long  text\"}},\"a_yes_no\":{\"<all_channels>\":{\"<all_locales>\":true}},\"a_localizable_image\":{\"<all_channels>\":{\"en_US\":\"7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg\",\"fr_FR\":\"0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg\"}},\"a_scopable_price\":{\"ecommerce\":{\"<all_locales>\":[{\"amount\":\"15.00\",\"currency\":\"EUR\"},{\"amount\":\"20.00\",\"currency\":\"USD\"}]},\"tablet\":{\"<all_locales>\":[{\"amount\":\"17.00\",\"currency\":\"EUR\"},{\"amount\":\"24.00\",\"currency\":\"USD\"}]}},\"a_localized_and_scopable_text_area\":{\"ecommerce\":{\"en_US\":\"a text area for ecommerce in English\"},\"tablet\":{\"en_US\":\"a text area for tablets in English\",\"fr_FR\":\"une zone de texte pour les tablettes en français\"}}}'),
(50,    'qux', 466,  25,   151, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"qux\"}},\"a_yes_no\": {\"<all_channels>\": {\"<all_locales>\": true}}}'),
(51,    'product_invalid_file', NULL,  NULL, NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"product_invalid_file\"}},\"a_file\": {\"<all_channels>\": {\"<all_locales>\": \"file/path/that/does/not/exists/intentionnaly.png\"}}}'),
(52,    'product_invalid_simple_reference_data', NULL,  NULL, NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"product_invalid_simple_reference_data\"}},\"a_ref_data_simple_select\": {\"<all_channels>\": {\"<all_locales>\": \"invalid_reference_data_value\"}}}'),
(53,    'product_invalid_multi_reference_data', NULL,  NULL, NULL, 1,  '2016-06-14 11:12:50',  '2016-06-14 11:12:50',  '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"product_invalid_multi_reference_data\"}},\"a_ref_data_multi_select\": {\"<all_channels>\": {\"<all_locales>\": [\"fabricA\", \"invalid_reference_data_value\"]}}}');

/*!40000 ALTER TABLE `pim_catalog_product` ENABLE KEYS */;

/*!40000 ALTER TABLE `pim_catalog_category_product_model` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_product_model` VALUES (150,896),(151,899),(151,897);
/*!40000 ALTER TABLE `pim_catalog_category_product_model` ENABLE KEYS */;

/*!40000 ALTER TABLE `pim_catalog_category_product` DISABLE KEYS */;
INSERT INTO `pim_catalog_category_product` VALUES (49,897),(49,899),(50,898);
/*!40000 ALTER TABLE `pim_catalog_category_product` ENABLE KEYS */;


--
-- Dumping data for table `pim_catalog_product_template`
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-08-04 16:51:35
