<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\Command;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Pim\Bundle\InstallerBundle\Command\DatabaseCommand as BaseDatabaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override the database command to be able to clean category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatabaseCommand extends BaseDatabaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        parent::launchCommands($input, $output);

        $this->commandExecutor->runCommand('pimee:installer:clean-category-accesses');
        $this->commandExecutor->runCommand('pimee:installer:clean-attribute-group-accesses');

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * pimee_team_work_assistant_completeness_per_attribute_group is the table used to calculate the project completeness.
     * pimee_team_work_assistant_project_product allows to know the product affected a project.
     */
    protected function createNotMappedTables(OutputInterface $output)
    {
        parent::createNotMappedTables($output);

        $output->writeln('<info>Initialize team work assistant </info>');

        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM === $this->getStorageDriver()) {
            $sql = <<<'SQL'
DROP TABLE IF EXISTS `@pimee_team_work_assistant.completeness_per_attribute_group@`;
CREATE TABLE `@pimee_team_work_assistant.completeness_per_attribute_group@` (
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

DROP TABLE IF EXISTS `@pimee_team_work_assistant.project_product@`;
CREATE TABLE `@pimee_team_work_assistant.project_product@` (
  `project_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index`  (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `@pimee_team_work_assistant.model.project@` (id) ON DELETE CASCADE,
  CONSTRAINT product_selection_product_foreign_key FOREIGN KEY (`product_id`) REFERENCES `@pim_catalog.entity.product@` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        } else {
            $sql = <<<'SQL'
DROP TABLE IF EXISTS `@pimee_team_work_assistant.completeness_per_attribute_group@`;
CREATE TABLE `@pimee_team_work_assistant.completeness_per_attribute_group@` (
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

DROP TABLE IF EXISTS `@pimee_team_work_assistant.project_product@`;
CREATE TABLE `@pimee_team_work_assistant.project_product@` (
  `project_id` INT NOT NULL,
  `product_id` VARCHAR(36) NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index` (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `@pimee_team_work_assistant.model.project@` (id) ON DELETE CASCADE
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

        $sql = $this->getContainer()->get('pimee_team_work_assistant.table_name_mapper')->createQuery($sql);

        $this->getContainer()
            ->get('doctrine')
            ->getConnection()
            ->exec($sql);
    }
}
