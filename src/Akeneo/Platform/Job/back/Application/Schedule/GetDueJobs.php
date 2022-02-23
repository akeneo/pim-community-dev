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

namespace Akeneo\Platform\Job\Application\Schedule;

use Cron\CronExpression;
use Doctrine\DBAL\Connection;

class GetDueJobs
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    private function getSchedules(): array
    {
        $sql = <<<SQL
SELECT job_instance_code, cron_expression
FROM akeneo_batch_job_instance_schedule;
SQL;

        return $this->connection->fetchAllAssociative($sql);
    }

    public function getDueJobs(): array
    {
        $schedules = $this->getSchedules();

        $dueJobs = [];
        foreach ($schedules as $schedule) {
            $cronExpression = new CronExpression($schedule['cron_expression']);

            if ($cronExpression->isDue()) {
                $dueJobs[] = $schedule['job_instance_code'];
            }
        }

        return $dueJobs;
    }
}
