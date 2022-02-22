<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Doctrine\DBAL\Connection;

class SaveJobInstanceSchedule
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function save(string $jobInstanceCode, string $cronExpression): void
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance_schedule (job_instance_code, cron_expression)
VALUES (:job_instance_code, :cron_expression)
ON DUPLICATE KEY UPDATE cron_expression = :cron_expression
SQL;

        $this->connection->executeQuery($sql, [
            'job_instance_code' => $jobInstanceCode,
            'cron_expression' => $cronExpression,
        ]);
    }
}
