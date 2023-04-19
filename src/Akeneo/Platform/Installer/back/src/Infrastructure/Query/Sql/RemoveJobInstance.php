<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\Sql;

use Akeneo\Platform\Installer\Domain\Query\Sql\RemoveJobInstanceInterface;
use Doctrine\DBAL\Connection;

final class RemoveJobInstance implements RemoveJobInstanceInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function remove(): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_batch_job_instance WHERE type = 'fixtures';
        SQL;

        $this->connection->executeQuery($sql)->execute();
    }
}
