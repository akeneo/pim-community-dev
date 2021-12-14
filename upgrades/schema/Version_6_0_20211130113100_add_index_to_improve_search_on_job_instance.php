<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211130113100_add_index_to_improve_search_on_job_instance extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->indexExists(), 'Index code_idx already exists in akeneo_batch_job_instance');

        $this->addSql('CREATE INDEX code_idx ON akeneo_batch_job_instance (code)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExists(): bool
    {
        $indices = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_instance')->fetchAllAssociative();
        $indicesIndexedByName = array_column($indices, null, 'Key_name');

        return array_key_exists('code_idx', $indicesIndexedByName);
    }
}
