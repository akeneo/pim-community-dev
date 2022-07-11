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

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Doctrine\DBAL\Connection;

/** @TODO pull up to 6.0 remove this class */
class GetWarningCount
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(int $jobExecutionId): int
    {
        $statement = $this->connection->executeQuery('
            SELECT COUNT(*)
            FROM akeneo_batch_step_execution s
            LEFT JOIN akeneo_batch_warning w ON s.id = w.step_execution_id
            WHERE s.job_execution_id = :job_execution_id
        ', [
           'job_execution_id' => $jobExecutionId
        ]);

        return (int) $statement->fetchColumn();
    }
}
