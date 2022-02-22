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

class GetJobInstanceSchedule
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function byJobInstanceCode(string $jobInstanceCode): ?array
    {
        $sql = <<<SQL
SELECT cron_expression
FROM akeneo_batch_job_instance_schedule
WHERE job_instance_code = :job_instance_code
SQL;

        $statement = $this->connection->executeQuery($sql, ['job_instance_code' => $jobInstanceCode]);
        $cronExpression = $statement->fetchOne();

        return $cronExpression ? ['cron_expression' => $cronExpression] : null;
    }
}
