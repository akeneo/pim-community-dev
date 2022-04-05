<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override the database command to be able to clean category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class EnterpriseDatabaseCommand extends DatabaseCommand
{
    protected static $defaultName = 'pim:installer:db';
    protected static $defaultDescription = 'Prepare database and load fixtures';

    /**
     * {@inheritdoc}
     */
    protected function launchCommands(): void
    {
        parent::launchCommands();

        $this->commandExecutor->runCommand('pimee:installer:clean-category-accesses');
        $this->commandExecutor->runCommand('pimee:installer:clean-attribute-group-accesses');
    }

    /**
     * {@inheritdoc}
     *
     * pimee_teamwork_assistant_completeness_per_attribute_group is the table used to calculate the project
     * completeness.
     * pimee_teamwork_assistant_project_product allows to know the product affected a project.
     */
    protected function createNotMappedTables(OutputInterface $output)
    {
        parent::createNotMappedTables($output);

        $output->writeln('<info>Initialize teamwork assistant </info>');

        $sql = <<<'SQL'
DROP TABLE IF EXISTS `pimee_teamwork_assistant_completeness_per_attribute_group`;
CREATE TABLE `pimee_teamwork_assistant_completeness_per_attribute_group` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL, 
    `product_id` INT NOT NULL, 
    `attribute_group_id` INT NOT NULL, 
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL, 
    `is_complete` TINYINT(1) NOT NULL,
    `calculated_at` DATETIME NOT NULL,
    `product_uuid` BINARY(16) DEFAULT NULL,
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`),
    KEY `attr_grp_completeness_locale_index` (`locale_id`),
    KEY `attr_grp_completeness_channel_index` (`channel_id`),
    KEY `attr_grp_completeness_product_index` (`product_id`),
    KEY `attr_grp_completeness_attribute_group_index` (`attribute_group_id`),
    CONSTRAINT `attr_grp_completeness_locale_foreign_key` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_channel_foreign_key` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_product_foreign_key` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (id) ON DELETE CASCADE,
    CONSTRAINT `attr_grp_completeness_attribute_group_foreign_key` FOREIGN KEY (`attribute_group_id`) REFERENCES `pim_catalog_attribute_group` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `pimee_teamwork_assistant_project_product`;
CREATE TABLE `pimee_teamwork_assistant_project_product` (
  `project_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_uuid` BINARY(16) DEFAULT NULL,
  PRIMARY KEY (`project_id`,`product_id`),
  KEY `product_selection_project_index` (`project_id`),
  KEY `product_selection_product_index`  (`product_id`),
  CONSTRAINT product_selection_project_foreign_key FOREIGN KEY (`project_id`) REFERENCES `pimee_teamwork_assistant_project` (id) ON DELETE CASCADE,
  CONSTRAINT product_selection_product_foreign_key FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SQL;

        $this->connection->exec($sql);
    }
}
