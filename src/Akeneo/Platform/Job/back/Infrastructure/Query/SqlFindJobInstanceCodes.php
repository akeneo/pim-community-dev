<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\ServiceAPI\Query\FindJobInstanceCodes;
use Akeneo\Platform\Job\ServiceAPI\Query\JobInstanceQuery;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SqlFindJobInstanceCodes implements FindJobInstanceCodes
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(JobInstanceQuery $query): array
    {
        $whereClause = '';

        if (null !== $query->isScheduled) {
            $whereClause = 'WHERE scheduled = ' . ($query->isScheduled ? 1 : 0);
        }

        $sql = <<<SQL
SELECT DISTINCT code FROM akeneo_batch_job_instance
$whereClause
SQL;

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }
}
