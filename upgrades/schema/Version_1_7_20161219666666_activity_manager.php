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
CREATE TABLE `pimee_activity_manager_completeness_per_attribute_group` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL, 
    `product_id` INT NOT NULL, 
    `attribute_group_id` INT NOT NULL, 
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL, 
    `is_complete` TINYINT(1) NOT NULL,
    UNIQUE INDEX searchunique_idx (`channel_id`, `locale_id`, `product_id`, `attribute_group_id`), 
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC7820E559DFD1 FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (id);
ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC782072F5A1AA FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (id);
ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC78204584665A FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (id);
ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC782062D643B7 FOREIGN KEY (`attribute_group_id`) REFERENCES `pim_catalog_attribute_group` (id);

CREATE TABLE `pimee_activity_manager_project_product` (
  `project_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pimee_activity_manager_project_product`
    ADD CONSTRAINT FK_E004BCB8166D1F9C FOREIGN KEY (`project_id`) REFERENCES `pimee_activity_manager_project` (id);
ALTER TABLE `pimee_activity_manager_project_product`
    ADD CONSTRAINT FK_E004BCB84584665A FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (id);
SQL;
        } else {
            $sql = <<<'SQL'
CREATE TABLE `pimee_activity_manager_completeness_per_attribute_group` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL, 
    `product_id` VARCHAR(36) NOT NULL, 
    `attribute_group_id` INT NOT NULL, 
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL, 
    `is_complete` TINYINT(1) NOT NULL,
    UNIQUE INDEX searchunique_idx (`channel_id`, `locale_id`, `product_id`, `attribute_group_id`), 
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC7820E559DFD1 FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (id);
ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC782072F5A1AA FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (id);
ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC782062D643B7 FOREIGN KEY (`attribute_group_id`) REFERENCES `pim_catalog_attribute_group` (id);
ALTER TABLE `pimee_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT completeness_fake_product_id_foreign_key UNIQUE (`locale_id`, `channel_id`, `product_id`, `attribute_group_id`);

CREATE TABLE `pimee_activity_manager_project_product` (
  `project_id` INT NOT NULL,
  `product_id` VARCHAR(36) NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pimee_activity_manager_project_product`
    ADD CONSTRAINT project_product_foreign_key FOREIGN KEY (`project_id`) REFERENCES `pimee_activity_manager_project` (id);
ALTER TABLE `pimee_activity_manager_project_product`
    ADD CONSTRAINT project_product_uniq_foreign_key UNIQUE (`project_id`, `product_id`);

DROP TABLE IF EXISTS `pimee_activity_manager_product_category`;
CREATE TABLE `pimee_activity_manager_product_category` (
  `product_id` VARCHAR(36) NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pimee_activity_manager_product_category`
    ADD CONSTRAINT product_category_foreign_key FOREIGN KEY (`project_id`) REFERENCES `pim_catalog_category` (id);
ALTER TABLE `pimee_activity_manager_product_category`
    ADD CONSTRAINT product_category_uniq_foreign_key UNIQUE (`product_id`, `category_id`);
SQL;
        }

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
