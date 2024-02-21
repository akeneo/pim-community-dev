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
-- We use auto-generated id to allow to add ACL in Enterprise Edition, without any conflict on the primary key.
--
-- Dumping data for table `acl_classes`
--

/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
INSERT INTO `acl_classes` (`class_type`) VALUES
    ('pim_api_overall_access'),
    ('pim_api_attribute_list'),
    ('pim_api_attribute_edit'),
    ('pim_api_attribute_option_list'),
    ('pim_api_attribute_option_edit'),
    ('pim_api_category_list'),
    ('pim_api_category_edit'),
    ('pim_api_channel_list'),
    ('pim_api_family_list'),
    ('pim_api_family_variant_list'),
    ('pim_api_family_edit'),
    ('pim_api_locale_list'),
    ('pim_api_attribute_group_list'),
    ('pim_api_attribute_group_edit'),
    ('pim_api_currency_list'),
    ('pim_api_channel_edit'),
    ('pim_api_association_type_list'),
    ('pim_api_association_type_edit'),
    ('pim_api_family_variant_edit')
;
/*!40000 ALTER TABLE `acl_classes` ENABLE KEYS */;

--
-- Dumping data for table `acl_entries`
--

/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
INSERT INTO `acl_entries`
(
    `class_id`,
    `object_identity_id`,
    `security_identity_id`,
    `field_name`,
    `ace_order`,
    `mask`,
    `granting`,
    `granting_strategy`,
    `audit_success`,
    `audit_failure`
)
SELECT id, NULL, 2, NULL, 0, 0, 1, 'all', 0, 0 FROM `acl_classes`;

INSERT INTO `acl_entries`
(
    `class_id`,
    `object_identity_id`,
    `security_identity_id`,
    `field_name`,
    `ace_order`,
    `mask`,
    `granting`,
    `granting_strategy`,
    `audit_success`,
    `audit_failure`
)
SELECT id, NULL, 4, NULL, 0, 0, 1, 'all', 0, 0 FROM `acl_classes` WHERE `class_type` = 'pim_api_overall_access';
/*!40000 ALTER TABLE `acl_entries` ENABLE KEYS */;

--
-- Dumping data for table `acl_object_identities`
--

/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
INSERT INTO `acl_object_identities`
(
    `parent_object_identity_id`,
    `class_id`,
    `object_identifier`,
    `entries_inheriting`
)
SELECT NULL, id, 'action', 1 FROM `acl_classes`;
/*!40000 ALTER TABLE `acl_object_identities` ENABLE KEYS */;

--
-- Dumping data for table `acl_object_identity_ancestors`
--

/*!40000 ALTER TABLE `acl_object_identity_ancestors` DISABLE KEYS */;
INSERT INTO `acl_object_identity_ancestors` (`object_identity_id`, `ancestor_id`)
SELECT id, id FROM `acl_object_identities`;
/*!40000 ALTER TABLE `acl_object_identity_ancestors` ENABLE KEYS */;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
