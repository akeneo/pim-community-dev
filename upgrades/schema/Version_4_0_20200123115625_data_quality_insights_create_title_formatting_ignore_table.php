<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_4_0_20200123115625_data_quality_insights_create_title_formatting_ignore_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_data_quality_insights_title_formatting_ignore (
    product_id INT NOT NULL,
    ignored_suggestions JSON NOT NULL,
    PRIMARY KEY (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
