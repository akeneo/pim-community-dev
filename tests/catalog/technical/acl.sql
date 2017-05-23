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

-- The following ACL setup means:
-- Admins: all API rights granted
-- Managers: overall API access granted but all rights on entities (list and edit) denied
-- Redactors: overall API access denied

--
-- Dumping data for table `acl_classes`
--

/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` VALUES
    (1,'pim_api_overall_access'),
    (2,'pim_api_attribute_list'),
    (3,'pim_api_attribute_edit'),
    (4,'pim_api_attribute_option_list'),
    (5,'pim_api_attribute_option_edit'),
    (6,'pim_api_category_list'),
    (7,'pim_api_category_edit'),
    (8,'pim_api_channel_list'),
    (9,'pim_api_family_list'),
    (10,'pim_api_family_edit'),
    (11,'pim_api_locale_list'),
    (12,'pim_api_attribute_group_list'),
    (13,'pim_api_attribute_group_edit'),
    (14,'pim_api_currency_list'),
    (15,'pim_api_channel_edit')
;
/*!40000 ALTER TABLE `acl_classes` ENABLE KEYS */;

--
-- Dumping data for table `acl_entries`
--

/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries` VALUES
    (1,2,NULL,2,NULL,0,0,1,'all',0,0),
    (2,3,NULL,2,NULL,0,0,1,'all',0,0),
    (3,4,NULL,2,NULL,0,0,1,'all',0,0),
    (4,5,NULL,2,NULL,0,0,1,'all',0,0),
    (5,6,NULL,2,NULL,0,0,1,'all',0,0),
    (6,7,NULL,2,NULL,0,0,1,'all',0,0),
    (7,8,NULL,2,NULL,0,0,1,'all',0,0),
    (8,9,NULL,2,NULL,0,0,1,'all',0,0),
    (9,10,NULL,2,NULL,0,0,1,'all',0,0),
    (10,11,NULL,2,NULL,0,0,1,'all',0,0),
    (11,1,NULL,3,NULL,0,0,1,'all',0,0),
    (12,12,NULL,2,NULL,0,0,1,'all',0,0),
    (13,13,NULL,2,NULL,0,0,1,'all',0,0),
    (14,14,NULL,2,NULL,0,0,1,'all',0,0),
    (15,15,NULL,2,NULL,0,0,1,'all',0,0)
;
/*!40000 ALTER TABLE `acl_entries` ENABLE KEYS */;

--
-- Dumping data for table `acl_object_identities`
--

/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities` VALUES
    (1,NULL,1,'action',1),
    (2,NULL,2,'action',1),
    (3,NULL,3,'action',1),
    (4,NULL,4,'action',1),
    (5,NULL,5,'action',1),
    (6,NULL,6,'action',1),
    (7,NULL,7,'action',1),
    (8,NULL,8,'action',1),
    (9,NULL,9,'action',1),
    (10,NULL,10,'action',1),
    (11,NULL,11,'action',1),
    (12,NULL,12,'action',1),
    (13,NULL,13,'action',1),
    (14,NULL,14,'action',1),
    (15,NULL,15,'action',1)
;
/*!40000 ALTER TABLE `acl_object_identities` ENABLE KEYS */;

--
-- Dumping data for table `acl_object_identity_ancestors`
--

/*!40000 ALTER TABLE `acl_object_identity_ancestors` DISABLE KEYS */;
INSERT INTO `acl_object_identity_ancestors` VALUES
    (1,1),
    (2,2),
    (3,3),
    (4,4),
    (5,5),
    (6,6),
    (7,7),
    (8,8),
    (9,9),
    (10,10),
    (11,11),
    (12,12),
    (13,13),
    (14,14),
    (15,15)
;
/*!40000 ALTER TABLE `acl_object_identity_ancestors` ENABLE KEYS */;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
