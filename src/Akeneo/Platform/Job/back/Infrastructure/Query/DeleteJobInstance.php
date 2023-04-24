<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceInterface;
use Doctrine\DBAL\Connection;

final class DeleteJobInstance implements DeleteJobInstanceInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @param string[] $codes
     */
    public function byCodes(array $codes): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_batch_job_instance WHERE code IN (:codes)
        SQL;

        $this->connection->executeQuery($sql, ['codes' => $codes], ['codes' => Connection::PARAM_STR_ARRAY])->execute();
    }
}
