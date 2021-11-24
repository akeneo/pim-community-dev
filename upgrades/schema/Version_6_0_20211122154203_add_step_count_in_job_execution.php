<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211122154203_add_step_count_in_job_execution extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('akeneo_batch_job_execution')->hasColumn('step_count'),
            'step_count column already exists in akeneo_batch_job_execution'
        );

        $this->addSql("ALTER TABLE akeneo_batch_job_execution ADD step_count INT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
