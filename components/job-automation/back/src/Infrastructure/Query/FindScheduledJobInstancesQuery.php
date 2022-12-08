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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use Doctrine\DBAL\Connection;

final class FindScheduledJobInstancesQuery implements FindScheduledJobInstancesQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
    ) {
    }

    /**
     * @return ScheduledJobInstance[]
     */
    public function all(): array
    {
        $sql = <<<SQL
SELECT code, label, type, raw_parameters, scheduled, automation FROM akeneo_batch_job_instance
WHERE scheduled = 1 
SQL;

        $results = $this->connection->executeQuery($sql)->fetchAllAssociative();

        return array_map(
            function (array $result) {
                $automation = json_decode($result['automation'], true);
                $rawParameters = unserialize($result['raw_parameters']);
                $setupDate = new \DateTimeImmutable($automation['setup_date']);
                $lastExecutionDate = $automation['last_execution_date'] ? new \DateTimeImmutable($automation['last_execution_date']) : null;

                return new ScheduledJobInstance(
                    $result['code'],
                    $result['label'],
                    $result['type'],
                    $rawParameters,
                    $automation['notification_users'],
                    $automation['notification_user_groups'],
                    $automation['cron_expression'],
                    $setupDate,
                    $lastExecutionDate,
                    $this->resolveScheduledJobRunningUsername->fromJobCode($result['code']),
                );
            },
            $results,
        );
    }
}
