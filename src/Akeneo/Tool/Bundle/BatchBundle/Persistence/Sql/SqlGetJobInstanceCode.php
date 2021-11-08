<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Query\GetJobInstanceCode;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetJobInstanceCode implements GetJobInstanceCode
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromJobExecutionId(int $jobExecutionId): ?string
    {
        $sql = <<<SQL
        SELECT instance.code
        FROM akeneo_batch_job_execution execution
            JOIN akeneo_batch_job_instance instance ON instance.id = execution.job_instance_id
        WHERE execution.id = :id
        SQL;

        $code = $this->connection->executeQuery($sql, ['id' => $jobExecutionId])->fetchOne();

        return false === $code ? null : $code;
    }
}
