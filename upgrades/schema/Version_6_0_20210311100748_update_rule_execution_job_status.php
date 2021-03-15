<?php declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Updates the status of 'running' rule execution jobs to 'FAILED'
 * It ensures that rule executions that crashed (so their status is still 'Running')
 * are correctly displayed in the UI, instead of perpetually staying in "RUNNING" or "STOPPING"
 *
 * This DOES NOT stop a still running execution from proceeding, the right status
 * will be saved back when the process finishes. The only drawback is that such processes will
 * temporarily show as failed in the UI (until the job actually finishes)
 */
final class Version_6_0_20210311100748_update_rule_execution_job_status extends AbstractMigration
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
