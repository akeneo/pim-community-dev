<?php

declare(strict_types=1);

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds the Onboarder supplier table
 */
final class Version_6_0_20220222071200_onboarder_add_supplier_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `akeneo_onboarder_v2_supplier` (
              `identifier` varchar(36) NOT NULL,
              `code` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
              `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
              PRIMARY KEY (`identifier`),
              CONSTRAINT UC_supplier_code UNIQUE (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
