<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20210819080024_add_warning_count_in_step_execution extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
ALTER TABLE akeneo_batch_step_execution 
ADD COLUMN warning_count INT NOT NULL DEFAULT 0;
SQL
        );

        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pim_one_time_task (
    `code` VARCHAR(100) PRIMARY KEY,
    `status` VARCHAR(100) NOT NULL,
    `start_time` DATETIME,
    `end_time` DATETIME,
    `values` JSON NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
