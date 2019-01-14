<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectLastCompletedFetchProductsExecutionDatetimeQuery
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): ?string
    {
        $sqlQuery = <<<SQL
SELECT je.start_time as last_execution_datetime 
FROM akeneo_batch_job_execution je 
INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id 
WHERE ji.code = :job_name 
AND je.status = :status 
ORDER BY je.start_time DESC LIMIT 0, 1
SQL;

        $bindParams = [
            'status' => BatchStatus::COMPLETED,
            'job_name' => JobInstanceNames::FETCH_PRODUCTS,
        ];

        $stmt = $this->connection->executeQuery($sqlQuery, $bindParams);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return $result['last_execution_datetime'];
    }
}
