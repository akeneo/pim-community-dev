<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait MigrateToUuidTrait
{
    protected function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            'SHOW TABLES LIKE :tableName',
            ['tableName' => $tableName]
        );

        return count($rows) >= 1;
    }

    protected function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]
        );

        return count($rows) >= 1;
    }

    protected function triggerExists(string $triggerName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            'SHOW TRIGGERS LIKE :triggerName',
            ['triggerName' => $triggerName]
        );

        return count($rows) >= 1;
    }
}
