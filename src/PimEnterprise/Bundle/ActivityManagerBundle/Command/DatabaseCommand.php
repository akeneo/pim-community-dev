<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Command;

use Pim\Bundle\InstallerBundle\Command\DatabaseCommand as PimDatabaseCommand;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * All tables are not mapped to doctrine entity, we need to create some sql manually.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class DatabaseCommand extends PimDatabaseCommand
{
    /**
     * {@inheritdoc}
     *
     * akeneo_activity_manager_completeness_per_attribute_group is table used to calculate the project completeness.
     * akeneo_activity_manager_project_product allow to to know the product affected a project.
     */
    protected function createNotMappedTables(OutputInterface $output)
    {
        parent::createNotMappedTables($output);

        $output->writeln('<info>Initialize activity manager </info>');

        $sql = <<<'SQL'
DROP TABLE IF EXISTS `akeneo_activity_manager_completeness_per_attribute_group`;
CREATE TABLE `akeneo_activity_manager_completeness_per_attribute_group` (
    `locale_id` INT NOT NULL,
    `channel_id` INT NOT NULL, 
    `product_id` INT NOT NULL, 
    `attribute_group_id` INT NOT NULL, 
    `has_at_least_one_required_attribute_filled` TINYINT(1) NOT NULL, 
    `is_complete` TINYINT(1) NOT NULL,
    UNIQUE INDEX searchunique_idx (`channel_id`, `locale_id`, `product_id`, `attribute_group_id`), 
    PRIMARY KEY(`locale_id`, `channel_id`, `product_id`, `attribute_group_id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

ALTER TABLE `akeneo_activity_manager_completeness_per_attribute_group`
    ADD CONSTRAINT FK_BEBC7820E559DFD1 FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (id);
ALTER TABLE `akeneo_activity_manager_completeness_per_attribute_group` 
    ADD CONSTRAINT FK_BEBC782072F5A1AA FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (id);
ALTER TABLE `akeneo_activity_manager_completeness_per_attribute_group` 
    ADD CONSTRAINT FK_BEBC78204584665A FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (id);
ALTER TABLE `akeneo_activity_manager_completeness_per_attribute_group` 
    ADD CONSTRAINT FK_BEBC782062D643B7 FOREIGN KEY (`attribute_group_id`) REFERENCES `pim_catalog_attribute_group` (id);

DROP TABLE IF EXISTS `akeneo_activity_manager_project_product`;
CREATE TABLE `akeneo_activity_manager_project_product` (
  `project_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `akeneo_activity_manager_project_product`
    ADD CONSTRAINT FK_E004BCB8166D1F9C FOREIGN KEY (`project_id`) REFERENCES `akeneo_activity_manager_project` (id);
ALTER TABLE `akeneo_activity_manager_project_product`
    ADD CONSTRAINT FK_E004BCB84584665A FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (id);
SQL;

        $this->getContainer()
            ->get('doctrine')
            ->getConnection()
            ->exec($sql);
    }
}
