<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author JMLeroux <jean-marie.leroux@akeneo.com>
 */
final class Version_7_0_20221027090000_add_scheduled_job_twa_projects_recalculate extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addScheduledJob(
            'twa_projects_recalculate',
            'Recalculate all enrichment projects',
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
