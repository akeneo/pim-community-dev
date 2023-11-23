<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20220518130906_drop_table_akeneo_batch_job_execution_queue extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->tableDoesNotExist()) {
            $this->write('The table akeneo_batch_job_execution_queue does not exist');

            return;
        }

        if (!$this->queueJobIsEmpty()) {
            throw new \RuntimeException('Some jobs need have not be launched, please launch `bin/console akeneo:batch:migrate-job-messages-from-old-queue` to migrate them or execute `TRUNCATE akeneo_batch_job_execution_queue` if you didn\'t want to migrate them');
        }

        $this->addSql('DROP TABLE IF EXISTS akeneo_batch_job_execution_queue');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function tableDoesNotExist(): bool
    {
        $rows = $this->connection->executeQuery('SHOW TABLES LIKE "akeneo_batch_job_execution_queue"');

        return $rows->rowCount() === 0;
    }

    private function queueJobIsEmpty(): bool
    {
        $result = $this->connection->executeQuery('
            SELECT EXISTS(
                SELECT *
                FROM akeneo_batch_job_execution_queue
                WHERE consumer IS NULL
            )')->fetchOne();

        return $result === '0';
    }
}
