<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200921153252_dqi_create_table_attribute_group_activation extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pim_data_quality_insights_attribute_group_activation (
    attribute_group_code VARCHAR(100) NOT NULL PRIMARY KEY,
    activated TINYINT NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO pim_data_quality_insights_attribute_group_activation (attribute_group_code, activated, updated_at)
SELECT code, 1, created
FROM pim_catalog_attribute_group;
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
