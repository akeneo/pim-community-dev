<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211119154203_add_is_stoppable_in_job_execution extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->skipIf(
            $schema->getTable('akeneo_batch_job_execution')->hasColumn('is_stoppable'),
            'is_stoppable column already exists in akeneo_batch_job_execution'
        );

        $this->addSql("ALTER TABLE akeneo_batch_job_execution ADD is_stoppable TINYINT(1) NULL");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
