<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adding the created_at & updated_at columns in record table
 */
final class Version_5_0_20200623100751_add_record_created_updated_at extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $alterTable = <<<SQL
ALTER TABLE akeneo_reference_entity_record
ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
;
SQL;

        $this->addSql($alterTable);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
