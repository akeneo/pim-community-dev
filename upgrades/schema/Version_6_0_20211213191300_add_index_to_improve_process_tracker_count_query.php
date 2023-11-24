<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211213191300_add_index_to_improve_process_tracker_count_query extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->indexExists()) {
            $this->write('Index job_instance_id_user_status_is_visible_idx already exists in akeneo_batch_job_instance');

            return;
        }

        $this->addSql('CREATE INDEX job_instance_id_user_status_is_visible_idx ON akeneo_batch_job_execution (job_instance_id, user, status, is_visible)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExists(): bool
    {
        $indices = $this->connection->executeQuery('SHOW INDEX FROM akeneo_batch_job_execution')->fetchAllAssociative();
        $indicesIndexedByName = array_column($indices, null, 'Key_name');

        return array_key_exists('job_instance_id_user_status_is_visible_idx', $indicesIndexedByName);
    }
}
