<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Domain\Query\CountJobQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CountJobQuery implements CountJobQueryInterface
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

        $result = $this->connection->query($sql)->fetch();

        return (int) $result['count'];
    }
}
