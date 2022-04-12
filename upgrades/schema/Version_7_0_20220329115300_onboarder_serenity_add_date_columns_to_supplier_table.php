<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds created_at and updated_at columns to the Onboarder Serenity supplier table
 */
final class Version_7_0_20220329115300_onboarder_serenity_add_date_columns_to_supplier_table extends AbstractMigration
{
    private const TABLE_NAME = 'akeneo_onboarder_serenity_supplier';
    private const CREATED_AT_COLUMN = 'created_at';
    private const UPDATED_AT_COLUMN = 'updated_at';

    public function up(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME)) {
            if (!$schema->getTable(self::TABLE_NAME)->hasColumn(self::CREATED_AT_COLUMN)) {
                $sql = <<<SQL
                    ALTER TABLE `akeneo_onboarder_serenity_supplier`
                    ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
                SQL;

                $this->addSql($sql);
            }

            if (!$schema->getTable(self::TABLE_NAME)->hasColumn(self::UPDATED_AT_COLUMN)) {
                $sql = <<<SQL
                    ALTER TABLE `akeneo_onboarder_serenity_supplier`
                    ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
                SQL;

                $this->addSql($sql);
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
