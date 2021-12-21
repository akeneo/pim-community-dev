<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\FindJobType\FindJobTypeInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobType implements FindJobTypeInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function visible(): array
    {
        $sql = <<<SQL
SELECT DISTINCT type FROM akeneo_batch_job_execution job_execution
INNER JOIN akeneo_batch_job_instance job_instance ON job_instance.id = job_execution.job_instance_id
WHERE job_execution.is_visible = 1;
SQL;

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }
}
