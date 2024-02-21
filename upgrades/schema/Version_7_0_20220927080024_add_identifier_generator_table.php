<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220927080024_add_identifier_generator_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator (
                `uuid` binary(16) PRIMARY KEY,
                `code` VARCHAR(100) NOT NULL,
                `conditions` JSON NOT NULL DEFAULT ('{}'),
                `structure` JSON NOT NULL DEFAULT ('{}'),
                `labels` JSON NOT NULL DEFAULT ('{}') ,
                `target` VARCHAR(100) NOT NULL,
                `delimiter` VARCHAR(100),
                UNIQUE INDEX unique_identifier_generator_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
