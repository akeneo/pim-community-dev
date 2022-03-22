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

    protected function indexExists(string $tableName, string $indexName): bool
    {
        $rows = $this->connection->executeQuery(
            \strtr(
                'SHOW INDEX FROM {table_name} WHERE KEY_NAME = :index',
                ['{table_name}' => $tableName]
            ),
            [
                'index' => $indexName,
            ]
        )->fetchAllAssociative();

        return count($rows) > 0;
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

    protected function constraintExists(string $tableName, string $constraintName): bool
    {
        $sql = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM information_schema.table_constraints
            WHERE constraint_schema = :schema AND TABLE_NAME = :table_name
                AND CONSTRAINT_NAME = :constraint_name
        ) AS is_existing
        SQL;

        return (bool) $this->connection->executeQuery(
            $sql,
            [
                'schema' => $this->connection->getDatabase(),
                'table_name' => $tableName,
                'constraint_name' => $constraintName,
            ]
        )->fetchOne();
    }
}
