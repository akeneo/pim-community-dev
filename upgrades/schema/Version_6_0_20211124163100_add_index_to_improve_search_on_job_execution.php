<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211124163100_add_index_to_improve_search_on_job_execution extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->skipIf($this->indexesExists(), 'Indexed IDX_STARTED_TIME, IDX_USER and IDX_STATUS already exists in akeneo_batch_job_execution');

        $this->addSql('CREATE INDEX user_idx ON akeneo_batch_job_execution (user)');
        $this->addSql('CREATE INDEX status_idx ON akeneo_batch_job_execution (status)');
        $this->addSql('CREATE INDEX start_time_idx ON akeneo_batch_job_execution (start_time)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexesExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return isset(
            $indexesIndexedByName['user_idx'],
            $indexesIndexedByName['status_idx'],
            $indexesIndexedByName['start_time_idx'],
        );
    }
}
