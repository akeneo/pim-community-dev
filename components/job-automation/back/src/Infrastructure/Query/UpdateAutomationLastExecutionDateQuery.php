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

use Akeneo\Platform\JobAutomation\Domain\Query\UpdateAutomationLastExecutionDateQueryInterface;
use Doctrine\DBAL\Connection;

final class UpdateAutomationLastExecutionDateQuery implements UpdateAutomationLastExecutionDateQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function forJobInstanceCode(string $jobInstanceCode, \DateTimeImmutable $lastExecutionDate): void
    {
        $sql = <<<SQL
    UPDATE akeneo_batch_job_instance SET automation = JSON_SET(automation, '$.last_execution_date', :lastExecutionDate) WHERE code = :jobInstanceCode;
SQL;
        $this->connection->executeQuery(
            $sql,
            [
                'lastExecutionDate' => $lastExecutionDate->format(DATE_ATOM),
                'jobInstanceCode' => $jobInstanceCode,
            ],
        );
    }
}
