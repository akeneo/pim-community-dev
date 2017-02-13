<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add the non mapped sql table for activity manager
 */
class Version_1_7_20161219666666_activity_manager extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $storage = $this->container->getParameter('pim_catalog_product_storage_driver');
        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM === $storage) {
            $sql = <<<'SQL'
DROP TABLE IF EXISTS `@pimee_activity_manager.completeness_per_attribute_group@`;
CREATE TABLE `@pimee_activity_manager.completeness_per_attribute_group@` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL, 
    `product_id` INT NOT NULL, 
    `attribute_group_id` INT NOT NULL, 
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL, 
    `is_complete` TINYINT(1) NOT NULL,
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`),
    KEY `attr_grp_completeness_locale_index` (`locale_id`),
    KEY `attr_grp_completeness_channel_index` (`channel_id`),
    KEY `attr_grp_completeness_product_index` (`product_id`),
    KEY `attr_grp_completeness_attribute_group_index` (`attribute_group_id`),
    CONSTRAINT `attr_grp_completeness_locale_foreign_key` FOREIGN KEY (`locale_id`) REFERENCES `@pim_catalog.entity.locale@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_channel_foreign_key` FOREIGN KEY (`channel_id`) REFERENCES `@pim_catalog.entity.channel@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_product_foreign_key` FOREIGN KEY (`product_id`) REFERENCES `@pim_catalog.entity.product@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_attribute_group_foreign_key` FOREIGN KEY (`attribute_group_id`) REFERENCES `@pim_catalog.entity.attribute_group@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `@pimee_activity_manager.project_product@`;
CREATE TABLE `@pimee_activity_manager.project_product@` (
  `project_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index`  (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `@pimee_activity_manager.model.project@` (id) ON DELETE CASCADE,
  CONSTRAINT product_selection_product_foreign_key FOREIGN KEY (`product_id`) REFERENCES `@pim_catalog.entity.product@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        } else {
            $sql = <<<'SQL'
DROP TABLE IF EXISTS `@pimee_activity_manager.completeness_per_attribute_group@`;
CREATE TABLE `@pimee_activity_manager.completeness_per_attribute_group@` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL, 
    `product_id` VARCHAR(36) NOT NULL, 
    `attribute_group_id` INT NOT NULL, 
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL, 
    `is_complete` TINYINT(1) NOT NULL,
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`),
    KEY `attr_grp_completeness_locale_index` (`locale_id`),
    KEY `attr_grp_completeness_channel_index` (`channel_id`),
    KEY `attr_grp_completeness_product_index` (`product_id`),
    KEY `attr_grp_completeness_attribute_group_index` (`attribute_group_id`),
    CONSTRAINT `attr_grp_completeness_locale_foreign_key` FOREIGN KEY (`locale_id`) REFERENCES `@pim_catalog.entity.locale@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_channel_foreign_key` FOREIGN KEY (`channel_id`) REFERENCES `@pim_catalog.entity.channel@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_attribute_group_foreign_key` FOREIGN KEY (`attribute_group_id`) REFERENCES `@pim_catalog.entity.attribute_group@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `@pimee_activity_manager.project_product@`;
CREATE TABLE `@pimee_activity_manager.project_product@` (
  `project_id` INT NOT NULL,
  `product_id` VARCHAR(36) NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index` (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `@pimee_activity_manager.model.project@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `@pim_catalog.entity.product#categories@`;
CREATE TABLE `@pim_catalog.entity.product#categories@` (
  `product_id` VARCHAR(36) NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `category_id`),
  KEY `link_category_project_index` (`product_id`),
  KEY `link_category_product_index` (`product_id`),
  CONSTRAINT `link_category_product_foreign_key` FOREIGN KEY (`category_id`) REFERENCES `@pim_catalog.entity.category@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        }

        $sql = $this->container->get('pimee_activity_manager.table_name_mapper')->createQuery($sql);

        $this->connection->exec($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
