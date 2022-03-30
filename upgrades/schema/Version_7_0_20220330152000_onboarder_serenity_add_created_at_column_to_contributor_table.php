<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220330152000_onboarder_serenity_add_created_at_column_to_contributor_table extends AbstractMigration
{
    private const TABLE_NAME = 'akeneo_onboarder_serenity_supplier_contributor';
    private const CREATED_AT_COLUMN = 'created_at';

    public function up(Schema $schema): void
    {
        if ($schema->hasTable(self::TABLE_NAME)) {
            if (!$schema->getTable(self::TABLE_NAME)->hasColumn(self::CREATED_AT_COLUMN)) {
                $sql = <<<SQL
                    ALTER TABLE `akeneo_onboarder_serenity_supplier_contributor`
                    ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
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
