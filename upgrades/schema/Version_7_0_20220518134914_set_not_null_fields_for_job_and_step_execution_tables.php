<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220518134914_set_not_null_fields_for_job_and_step_execution_tables extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE akeneo_batch_job_execution SET is_stoppable TINYINT(1) DEFAULT 0 NOT NULL");
        $this->addSql("ALTER TABLE akeneo_batch_job_execution SET step_count INT DEFAULT 1 NOT NULL");
        $this->addSql("ALTER TABLE akeneo_batch_job_execution SET is_visible TINYINT(1) DEFAULT 1 NOT NULL");

        $this->addSql("ALTER TABLE akeneo_batch_step_execution SET is_trackable TINYINT(1) DEFAULT 0 NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
