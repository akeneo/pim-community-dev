<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211129163800_add_index_on_is_visible_in_job_execution extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->skipIf($this->indexExists(), 'Indexed is_visible_idx already exists in akeneo_batch_job_execution');

        $this->addSql('CREATE INDEX is_visible_idx ON akeneo_batch_job_execution (is_visible)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return array_key_exists('is_visible_idx', $indexesIndexedByName);
    }
}
