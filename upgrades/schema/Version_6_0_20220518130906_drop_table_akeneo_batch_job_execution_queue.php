<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20220518130906_drop_table_akeneo_batch_job_execution_queue extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS akeneo_batch_job_execution_queue');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
