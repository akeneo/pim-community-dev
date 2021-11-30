<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211130113100_add_index_to_improve_search_on_job_instance extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->skipIf($this->indexExist(), 'Index code_idx already exist in akeneo_batch_job_instance');

        $this->addSql('CREATE INDEX code_idx ON akeneo_batch_job_instance (code)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExist(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_instance')->fetchAllAssociative();
        $indexIndexedByName = array_column($indexes, null, 'Key_name');

        return isset(
            $indexIndexedByName['code_idx'],
        );
    }
}
