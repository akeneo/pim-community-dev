<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20220520114800_add_start_time_index_on_job_execution extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->indexExists()) {
            $this->write('Indexed IDX_START_TIME already exists in akeneo_batch_job_execution');

            return;
        }

        $this->addSql('CREATE INDEX start_time_idx ON akeneo_batch_job_execution (start_time)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return isset(
            $indexesIndexedByName['start_time_idx'],
        );
    }
}
