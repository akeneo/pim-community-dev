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

namespace Akeneo\AssetManager\Integration\Persistence\Helper;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Doctrine\DBAL\Connection;

/**
 * This class is responsible for helping in DB setup in tests.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseHelper
{
    private Connection $sqlConnection;

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
        $this->insertUsers();
        $this->insertImages();
    }

    private function resetTables(): void
    {
        $resetQuery = <<<SQL
            SET foreign_key_checks = 0;

            DELETE FROM akeneo_batch_job_execution;
            DELETE FROM akeneo_batch_step_execution;
            DELETE FROM akeneo_batch_warning;
            DELETE FROM akeneo_asset_manager_attribute;
            DELETE FROM akeneo_asset_manager_asset;
            DELETE FROM akeneo_asset_manager_asset_family;
            DELETE FROM akeneo_asset_manager_asset_family_permissions;
            DELETE FROM pim_catalog_attribute_group;
            DELETE FROM pim_catalog_attribute;
            DELETE FROM oro_user;
            DELETE FROM oro_access_group;
            DELETE FROM oro_user_access_group;
            DELETE FROM pim_catalog_channel;
            DELETE FROM pim_catalog_category;
            DELETE FROM pim_catalog_locale;
            DELETE FROM akeneo_file_storage_file_info;

            SET foreign_key_checks = 1;
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
            (3, 'fr_FR', 1),
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

    private function insertUsers(): void
    {
        $resetUsers = <<<SQL
INSERT INTO `oro_user` (`id`, `file_info_id`, `ui_locale_id`, `username`, `email`, `name_prefix`, `first_name`, `middle_name`, `last_name`, `name_suffix`, `image`, `enabled`, `salt`, `password`, `confirmation_token`, `password_requested`, `last_login`, `login_count`, `createdAt`, `updatedAt`, `product_grid_filters`, `emailNotifications`, `phone`, `timezone`, `properties`, `catalogLocale_id`, `catalogScope_id`, `defaultTree_id`)
VALUES
        (1,NULL,2,'admin','admin@example.com',NULL,'John',NULL,'Doe',NULL,NULL,1,'9zpd00l1ijkg0s44og4wck0cwoo00c8','2S/jxiyQSBEsCWqvH3YOpelBwjfj2MhmDV8mFcrOD6pcGa9VzUlVuJ7R64fG68llfHpyjXJbvD9gnAzMDnsn8w==',NULL,NULL,NULL,0,'2018-11-26 09:58:57','2018-11-26 09:58:57','[]',0,NULL,'UTC','{\"proposals_to_review_notification\":true,\"proposals_state_notifications\":true}',2,3,1);
SQL;
        $this->sqlConnection->executeQuery($resetUsers);
    }

    private function insertImages(): void
    {
        $imagesQuery = <<<SQL
        INSERT INTO akeneo_file_storage_file_info (file_key, original_filename, mime_type, size, extension, hash, storage)
        VALUES
        ('test/image_1.jpg', 'image_1.jpg', 'image/jpeg', 295739, 'jpg', '4d78843ea9e6c93b8677b80cb926c9c74c17aa6e', '%s'),
        ('test/image_2.jpg', 'image_2.jpg', 'image/jpeg', 97573, 'jpg', '61e3f10aad1db6bfa7d2eff35fee97d377dec01d', '%s');
SQL;
        $this->sqlConnection->executeUpdate(sprintf($imagesQuery, Storage::FILE_STORAGE_ALIAS, Storage::FILE_STORAGE_ALIAS));
    }
}
