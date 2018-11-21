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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Helper;

use Doctrine\DBAL\Connection;

/**
 * This class is responsible for helping in DB setup in tests.
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
        $this->insertImages();
    }

    private function resetTables(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_reference_entity_attribute;
            DELETE FROM akeneo_reference_entity_record;
            DELETE FROM akeneo_reference_entity_reference_entity;
            DELETE FROM pim_catalog_attribute_group;
            DELETE FROM pim_catalog_attribute;
            DELETE FROM oro_user;
            DELETE FROM pim_catalog_channel;
            DELETE FROM pim_catalog_category;
            DELETE FROM pimee_product_asset_category;
            DELETE FROM pim_catalog_locale;
            DELETE FROM akeneo_file_storage_file_info;
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
        INSERT INTO `oro_user` (`id`, `ui_locale_id`, `username`, `email`, `name_prefix`, `first_name`, `middle_name`, `last_name`, `name_suffix`, `birthday`, `image`, `enabled`, `salt`, `password`, `confirmation_token`, `password_requested`, `last_login`, `login_count`, `createdAt`, `updatedAt`, `product_grid_filters`, `emailNotifications`, `phone`, `timezone`, `catalogLocale_id`, `catalogScope_id`, `defaultTree_id`)
        VALUES
	        (1, 1, 'admin', 'admin@example.com', NULL, 'John', NULL, 'Doe', NULL, NULL, NULL, 1, '9zpd00l1ijkg0s44og4wck0cwoo00c8', '2S/jxiyQSBEsCWqvH3YOpelBwjfj2MhmDV8mFcrOD6pcGa9VzUlVuJ7R64fG68llfHpyjXJbvD9gnAzMDnsn8w==', NULL, NULL, NULL, 0, '2018-09-14 23:46:33', '2018-09-14 23:46:33', '[]', 0, NULL, 'UTC', 1, 1, 1, );
SQL;
        $this->sqlConnection->executeQuery($resetUsers);
    }

    private function insertImages(): void
    {
        $imagesQuery = <<<SQL
        INSERT INTO akeneo_file_storage_file_info (file_key, original_filename, mime_type, size, extension, hash, storage) 
        VALUES
        ('test/image_1.jpg', 'image_1.jpg', 'image/jpeg', 295739, 'jpg', '4d78843ea9e6c93b8677b80cb926c9c74c17aa6e', 'catalogStorage'), 
        ('test/image_2.jpg', 'image_2.jpg', 'image/jpeg', 97573, 'jpg', '61e3f10aad1db6bfa7d2eff35fee97d377dec01d', 'catalogStorage');
SQL;
        $this->sqlConnection->executeUpdate($imagesQuery);
    }
}
