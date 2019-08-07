<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Query;

use Akeneo\Tool\Component\BatchQueue\Query\DeleteJobExecutionMessageOrphansQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlDeleteJobExecutionMessageOrphansQuery implements DeleteJobExecutionMessageOrphansQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_batch_job_execution_queue
    USING akeneo_batch_job_execution_queue
    LEFT JOIN akeneo_batch_job_execution job_execution ON job_execution.id = akeneo_batch_job_execution_queue.job_execution_id
    WHERE job_execution.id IS NULL;
SQL;

        $this->dbConnection->executeQuery($sql);
    }
}
