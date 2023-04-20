<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobByCodesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class DeleteJobByCodes implements DeleteJobByCodesInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * @param string[] $codes
     * @throws Exception
     */
    public function delete(array $codes): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_batch_job_instance WHERE code IN (:codes)
        SQL;

        $this->connection->executeQuery($sql, ['codes' => $codes])->execute();
    }
}
