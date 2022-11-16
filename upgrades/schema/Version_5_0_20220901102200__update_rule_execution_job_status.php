<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20220901102200__update_rule_execution_job_status extends AbstractMigration
{
    private const RULE_EXECUTION_JOB_NAME = 'rule_engine_execute_rules';

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
UPDATE akeneo_batch_job_execution job_execution
INNER JOIN akeneo_batch_job_instance job_instance ON job_execution.job_instance_id = job_instance.id
SET job_execution.status = :failedStatus, job_execution.exit_code = :failedExitCode
WHERE job_instance.code = :jobCode
AND job_execution.health_check_time IS NULL
AND job_execution.status IN (:runningStatuses);
SQL,
            [
                'jobCode' => self::RULE_EXECUTION_JOB_NAME,
                'failedStatus' => BatchStatus::FAILED,
                'failedExitCode' => ExitStatus::FAILED,
                'runningStatuses' => [BatchStatus::STARTED, BatchStatus::STOPPING],
            ],
            [
                'runningStatuses' => Connection::PARAM_INT_ARRAY,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
