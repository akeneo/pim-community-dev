<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211124163100_add_index_to_improve_search_on_job_execution extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->skipIf($this->indexesExist(), 'Indexed IDX_STARTED_TIME, IDX_USER and IDX_STATUS already exists in akeneo_batch_job_execution');

        $this->addSql('CREATE INDEX user_idx ON akeneo_batch_job_execution (user)');
        $this->addSql('CREATE INDEX status_idx ON akeneo_batch_job_execution (status)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexesExist(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexIndexedByName = array_column($indexes, null, 'Key_name');

        return isset(
            $indexIndexedByName['user_idx'],
            $indexIndexedByName['status_idx']
        );
    }
}
