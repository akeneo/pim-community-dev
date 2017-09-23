<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add the non mapped sql table for team work assistant
 */
class Version_1_7_20161219666666_teamwork_assistant extends AbstractMigration implements ContainerAwareInterface
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

        $sql = <<<'SQL'
CREATE TABLE `pimee_teamwork_assistant_project` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `datagrid_view_id` INT NOT NULL,
  `owner_id` INT NOT NULL,
  `channel_id` INT NOT NULL,
  `locale_id` INT NOT NULL,
  `code` VARCHAR(150) NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `description` LONGTEXT DEFAULT NULL,
  `due_date` DATE NOT NULL,
  `productFilters` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)',
  UNIQUE INDEX UNIQ_5C87483D1E2E9CAF (datagrid_view_id),
  INDEX IDX_5C87483D7E3C61F9 (`owner_id`),
  INDEX IDX_5C87483D72F5A1AA (`channel_id`),
  INDEX IDX_5C87483DE559DFD1 (`locale_id`),
  UNIQUE INDEX `channel_locale_label_idx` (`channel_id`, `locale_id`, `label`),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE `pimee_teamwork_assistant_project` ADD CONSTRAINT FK_5C87483D1E2E9CAF FOREIGN KEY (datagrid_view_id) REFERENCES `@pim_datagrid.entity.datagrid_view@` (id);
ALTER TABLE `pimee_teamwork_assistant_project` ADD CONSTRAINT FK_5C87483D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `@pim_user.entity.user@` (id);
ALTER TABLE `pimee_teamwork_assistant_project` ADD CONSTRAINT FK_5C87483D72F5A1AA FOREIGN KEY (channel_id) REFERENCES `@pim_catalog.entity.channel@` (id);
ALTER TABLE `pimee_teamwork_assistant_project` ADD CONSTRAINT FK_5C87483DE559DFD1 FOREIGN KEY (locale_id) REFERENCES `@pim_catalog.entity.locale@` (id);
SQL;

        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM === $storage) {
            $sql .= <<<'SQL'
CREATE TABLE `@pimee_teamwork_assistant.completeness_per_attribute_group@` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `attribute_group_id` INT NOT NULL,
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL,
    `is_complete` TINYINT(1) NOT NULL,
    `calculated_at` DATETIME NOT NULL,
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

CREATE TABLE `@pimee_teamwork_assistant.project_product@` (
  `project_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index`  (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `@pimee_teamwork_assistant.model.project@` (id) ON DELETE CASCADE,
  CONSTRAINT product_selection_product_foreign_key FOREIGN KEY (`product_id`) REFERENCES `@pim_catalog.entity.product@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        } else {
            $sql .= <<<'SQL'
CREATE TABLE `@pimee_teamwork_assistant.completeness_per_attribute_group@` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL,
    `product_id` VARCHAR(36) NOT NULL,
    `attribute_group_id` INT NOT NULL,
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL,
    `is_complete` TINYINT(1) NOT NULL,
    `calculated_at` DATETIME NOT NULL,
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`),
    KEY `attr_grp_completeness_locale_index` (`locale_id`),
    KEY `attr_grp_completeness_channel_index` (`channel_id`),
    KEY `attr_grp_completeness_product_index` (`product_id`),
    KEY `attr_grp_completeness_attribute_group_index` (`attribute_group_id`),
    CONSTRAINT `attr_grp_completeness_locale_foreign_key` FOREIGN KEY (`locale_id`) REFERENCES `@pim_catalog.entity.locale@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_channel_foreign_key` FOREIGN KEY (`channel_id`) REFERENCES `@pim_catalog.entity.channel@` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_attribute_group_foreign_key` FOREIGN KEY (`attribute_group_id`) REFERENCES `@pim_catalog.entity.attribute_group@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `@pimee_teamwork_assistant.project_product@` (
  `project_id` INT NOT NULL,
  `product_id` VARCHAR(36) NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index` (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `@pimee_teamwork_assistant.model.project@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

        $sql .= <<<'SQL'
CREATE TABLE `pimee_teamwork_assistant_project_user_group` (
  `project_id` INT NOT NULL,
  `user_group_id` SMALLINT NOT NULL,
  INDEX IDX_826785A5166D1F9C (`project_id`),
  INDEX IDX_826785A51ED93D47 (`user_group_id`),
  PRIMARY KEY(`project_id`, `user_group_id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE `pimee_teamwork_assistant_project_user_group` ADD CONSTRAINT FK_826785A5166D1F9C FOREIGN KEY (project_id) REFERENCES `pimee_teamwork_assistant_project` (id);
ALTER TABLE `pimee_teamwork_assistant_project_user_group` ADD CONSTRAINT FK_826785A51ED93D47 FOREIGN KEY (user_group_id) REFERENCES `@pim_user.entity.group@` (id);

CREATE TABLE `pimee_teamwork_assistant_project_status` (
  `id` INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL,
  `project_id` INT NOT NULL,
  `isComplete` TINYINT(1) DEFAULT NULL,
  `hasBeenNotified` TINYINT(1) DEFAULT NULL,
  INDEX IDX_2A911294A76ED395 (`user_id`),
  INDEX IDX_2A911294166D1F9C (`project_id`),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE `pimee_teamwork_assistant_project_status` ADD CONSTRAINT FK_2A911294A76ED395 FOREIGN KEY (user_id) REFERENCES `@pim_user.entity.user@` (id);
ALTER TABLE `pimee_teamwork_assistant_project_status` ADD CONSTRAINT FK_2A911294166D1F9C FOREIGN KEY (project_id) REFERENCES `pimee_teamwork_assistant_project` (id) ON DELETE CASCADE;
ALTER TABLE `pimee_security_attribute_group_access` ADD KEY `attr_grp_editable_permission_index` (`edit_attributes`, `attribute_group_id`);

INSERT INTO akeneo_batch_job_instance (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`) VALUES
('project_calculation', 'Project calculation', 'project_calculation', 0, 'teamwork assistant', 'a:0:{}', 'project_calculation'),
('refresh_project_completeness_calculation', 'Refresh project completeness', 'refresh_project_completeness_calculation', 0, 'teamwork assistant', 'a:0:{}', 'refresh_project_completeness_calculation');
SQL;

        $sql = $this->container->get('pimee_teamwork_assistant.table_name_mapper')->createQuery($sql);

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
