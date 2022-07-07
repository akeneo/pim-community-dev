<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220603081946_add_columns_in_dqi_dictionary_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add columns `update_at` and `enabled` in the DQI dictionary table';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
ALTER TABLE pimee_data_quality_insights_text_checker_dictionary 
ADD COLUMN enabled TINYINT DEFAULT 1,
ADD COLUMN updated_at DATETIME NULL;
SQL;

        $this->addSql($sql);

    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
