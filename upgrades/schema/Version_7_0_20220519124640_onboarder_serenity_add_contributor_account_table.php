<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds the Onboarder Serenity contributor account table
 */
final class Version_7_0_20220519124640_onboarder_serenity_add_contributor_account_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_serenity_contributor_account` (
                `id` varchar(36) NOT NULL,
                `email` varchar(255) NOT NULL,
                `password` varchar(255) DEFAULT NULL,
                `access_token` varchar(255) DEFAULT NULL,
                `access_token_created_at` DATETIME DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `last_logged_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `akeneo_onboarder_serenity_contributor_account_email_uindex` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
