<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Domain\Query\CountJobExecutionInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CountJobExecution implements CountJobExecutionInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function all(): int
    {
        $sql = <<<SQL
SELECT count(*) as count FROM akeneo_batch_job_execution;
SQL;

        $result = $this->connection->executeQuery($sql)->fetchOne();

        return (int) $result;
    }
}
