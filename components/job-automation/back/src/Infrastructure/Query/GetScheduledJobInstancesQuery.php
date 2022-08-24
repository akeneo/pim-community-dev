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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Application\GetDueJobInstances\GetScheduledJobInstancesInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Doctrine\DBAL\Connection;

final class GetScheduledJobInstancesQuery implements GetScheduledJobInstancesInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @return ScheduledJobInstance[]
     */
    public function all(): array
    {
        $sql = <<<SQL
SELECT code, job_name, type, raw_parameters, scheduled, automation FROM akeneo_batch_job_instance
WHERE scheduled = 1 
SQL;

        $results = $this->connection->executeQuery($sql)->fetchAllAssociative();

        return array_map(
            function (array $result) {
                $automation = json_decode($result['automation'], true);
                $rawParameters = unserialize($result['raw_parameters']);
                $isScheduled = '1' === $result['scheduled'];
                $setupDate = new \DateTimeImmutable($automation['setup_date']);
                $lastExecutionDate = $automation['last_execution_date'] ? new \DateTimeImmutable($automation['last_execution_date']) : null;

                return new ScheduledJobInstance(
                    $result['code'],
                    $result['job_name'],
                    $result['type'],
                    $rawParameters,
                    $isScheduled,
                    $automation['cron_expression'],
                    $setupDate,
                    $lastExecutionDate,
                );
            },
            $results,
        );
    }
}
