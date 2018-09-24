<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Integration\Persistence\Helper;

use Doctrine\DBAL\Connection;

/**
 * This class is responsible for helping calling web routes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseHelper
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function resetDatabase(): void
    {
        $this->resetTables();
        $this->insertDefaultLocales();
        $this->insertDefaultCategories();
        $this->insertDefaultChannels();
        $this->insertDefaultAssetTree();
        $this->insertUsers();
    }

    private function resetTables(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_attribute;
            DELETE FROM akeneo_enriched_entity_record;
            DELETE FROM akeneo_enriched_entity_enriched_entity;
            DELETE FROM pim_catalog_attribute_group;
            DELETE FROM pim_catalog_attribute;
            DELETE FROM oro_user;
            DELETE FROM pim_catalog_channel;
            DELETE FROM pim_catalog_category;
            DELETE FROM pimee_product_asset_category;
            DELETE FROM pim_catalog_locale;
SQL;
        $this->sqlConnection->executeQuery($resetQuery);
    }

    private function insertDefaultLocales(): void
    {
        $resetLocale = <<<SQL
        INSERT INTO `pim_catalog_locale` (`id`, `code`, `is_activated`)
        VALUES
            (1, 'de_DE', 1),
            (2, 'en_US', 1),
            (3, 'fr_FR', 1);
            (4, 'az_Cyrl_AZ', 0);
SQL;
        $this->sqlConnection->executeQuery($resetLocale);
    }

    private function insertDefaultCategories(): void
    {
        $resetCategory = <<<SQL
INSERT INTO `pim_catalog_category` (`id`, `parent_id`, `code`, `created`, `root`, `lvl`, `lft`, `rgt`)
        VALUES
            (1, NULL, 'master', '2018-09-04 20:00:54', 1, 0, 1, 2);
SQL;
        $this->sqlConnection->executeQuery($resetCategory);
    }

    private function insertDefaultChannels(): void
    {
        $resetChannel = <<<SQL
        INSERT INTO `pim_catalog_channel` (`id`, `category_id`, `code`, `conversionUnits`)
        VALUES
            (1, 1, 'mobile', 'a:0:{}'),
            (2, 1, 'print', 'a:0:{}'),
            (3, 1, 'ecommerce', 'a:0:{}');
            
        INSERT INTO `pim_catalog_channel_locale` (`channel_id`, `locale_id`)
        VALUES
            (1, 1),
            (2, 2),
            (3, 2),
            (3, 3);
SQL;
        $this->sqlConnection->executeQuery($resetChannel);
    }

    private function insertDefaultAssetTree(): void
    {
        $resetAssetTree = <<<SQL
        INSERT INTO `pimee_product_asset_category` (`id`, `parent_id`, `code`, `created`, `root`, `lvl`, `lft`, `rgt`) 
        VALUES
          (1,	NULL,	'asset_main_catalog',	'2018-09-05 22:45:32',	1,	0,	1,	2);
SQL;
        $this->sqlConnection->executeQuery($resetAssetTree);
    }

    private function insertUsers(): void
    {
        $resetUsers = <<<SQL
        INSERT INTO `oro_user` (`id`, `ui_locale_id`, `username`, `email`, `name_prefix`, `first_name`, `middle_name`, `last_name`, `name_suffix`, `birthday`, `image`, `enabled`, `salt`, `password`, `confirmation_token`, `password_requested`, `last_login`, `login_count`, `createdAt`, `updatedAt`, `product_grid_filters`, `emailNotifications`, `phone`, `timezone`, `assetDelayReminder`, `proposalsToReviewNotification`, `proposalsStateNotification`, `catalogLocale_id`, `catalogScope_id`, `defaultTree_id`, `defaultAssetTree_id`)
        VALUES
	        (1, 1, 'admin', 'admin@example.com', NULL, 'John', NULL, 'Doe', NULL, NULL, NULL, 1, '9zpd00l1ijkg0s44og4wck0cwoo00c8', '2S/jxiyQSBEsCWqvH3YOpelBwjfj2MhmDV8mFcrOD6pcGa9VzUlVuJ7R64fG68llfHpyjXJbvD9gnAzMDnsn8w==', NULL, NULL, NULL, 0, '2018-09-14 23:46:33', '2018-09-14 23:46:33', '[]', 0, NULL, 'UTC', 5, 1, 1, 1, 1, 1, 1);
SQL;
        $this->sqlConnection->executeQuery($resetUsers);
    }
}
