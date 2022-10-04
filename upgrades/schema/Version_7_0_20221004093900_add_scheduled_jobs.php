<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221004093900_add_scheduled_jobs extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addScheduledJob(
            'aggregate_volume_queries',
            'Aggregate the result of all the volume queries that should not be executed live',
            []
        );
    }

    private function addScheduledJob(string $jobCode, string $label, array $rawParameters): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance 
                (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES
            (
                :code,
                :label,
             
                :code,
                0,
                'internal',
                :raw_parameters,
                'scheduled_job'
            )
            ON DUPLICATE KEY UPDATE code = code;
        SQL;

        $this->addSql(
            $sql,
            ['code' => $jobCode, 'label' => $label, 'raw_parameters' => \serialize($rawParameters)]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
