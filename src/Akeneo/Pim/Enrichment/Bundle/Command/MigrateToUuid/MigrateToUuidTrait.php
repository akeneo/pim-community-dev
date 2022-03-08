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
            <<<SQL
                SHOW TABLES LIKE :tableName
            SQL,
            ['tableName' => $tableName]
        );

        return count($rows) >= 1;
    }

    protected function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }

    protected function triggerExists(string $triggerName): bool
    {
        $schema = $this->connection->getDatabase();
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS
                WHERE TRIGGER_NAME = :triggerName AND TRIGGER_SCHEMA = :schema
            ) AS is_existing
        SQL;

        return (bool) $this->connection->fetchOne($sql, ['triggerName' => $triggerName, 'schema' => $schema]);
    }
}
