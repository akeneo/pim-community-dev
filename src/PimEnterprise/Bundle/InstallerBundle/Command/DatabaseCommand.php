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

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * pimee_activity_manager_completeness_per_attribute_group is the table used to calculate the project completeness.
     * pimee_activity_manager_project_product allows to know the product affected a project.
     */
    protected function createNotMappedTables(OutputInterface $output)
    {
        parent::createNotMappedTables($output);

        $output->writeln('<info>Initialize activity manager </info>');

        if (AkeneoStorageUtilsExtension::DOCTRINE_ORM === $this->getStorageDriver()) {
            $sql = <<<'SQL'
DROP TABLE IF EXISTS `pimee_activity_manager_completeness_per_attribute_group`;
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

DROP TABLE IF EXISTS `pimee_activity_manager_project_product`;
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
DROP TABLE IF EXISTS `pimee_activity_manager_completeness_per_attribute_group`;
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

DROP TABLE IF EXISTS `pimee_activity_manager_project_product`;
CREATE TABLE `pimee_activity_manager_project_product` (
  `project_id` INT NOT NULL,
  `product_id` VARCHAR(36) NOT NULL,
  PRIMARY KEY (`project_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pimee_activity_manager_project_product`
    ADD CONSTRAINT FK_E004BCB8166D1F9C FOREIGN KEY (`project_id`) REFERENCES `pimee_activity_manager_project` (id);
ALTER TABLE `pimee_activity_manager_project_product`
    ADD CONSTRAINT project_fake_product_id_foreign_key UNIQUE (`project_id`, `product_id`)

DROP TABLE IF EXISTS `pimee_activity_manager_product_category`;
CREATE TABLE `pimee_activity_manager_product_category` (
  `product_id` VARCHAR(36) NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        }

        $this->getContainer()
            ->get('doctrine')
            ->getConnection()
            ->exec($sql);
    }
}
