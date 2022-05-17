<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidAddConstraints implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getDescription(): string
    {
        return 'Add constraints on uuid foreign columns';
    }

    public function getName(): string
    {
        return 'add_constraints_on_uuid_columns';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach (self::TABLES as $tableName => $tableProperties) {
            if ($this->tableExists($tableName)) {
                if (null !== $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX] && !$this->hasPrimaryKey($tableName, $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX])) {
                    $count++;
                }
                if (null !== $tableProperties[MigrateToUuidStep::FOREIGN_KEY_INDEX] && !$this->constraintExists($tableName, $tableProperties[MigrateToUuidStep::FOREIGN_KEY_INDEX])) {
                    $count++;
                }
                foreach ($tableProperties[MigrateToUuidStep::UNIQUE_CONSTRAINTS_INDEX] as $constraintName => $constraintColumns) {
                    if (!$this->constraintExists($tableName, $constraintName)) {
                        $count++;
                    }
                }
                foreach ($tableProperties[MigrateToUuidStep::INDEXES_INDEX] as $indexColumns) {
                    if (null == $this->getIndexName($tableName, $indexColumns)) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $updatedItems = 0;

        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            if ($this->tableExists($tableName)) {
                $logContext->addContext('substep', 'add_constraint_' . $tableName);
                if (null !== $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX] && !$this->hasPrimaryKey($tableName, $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX])) {
                    $this->logger->notice(sprintf('Will add %s primary key', $tableName), $logContext->toArray());
                    if (!$context->dryRun()) {
                        $this->setPrimaryKey($tableName, $tableProperties);
                        $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                    }
                }
                if (null !== $tableProperties[MigrateToUuidStep::FOREIGN_KEY_INDEX] && !$this->constraintExists($tableName, $tableProperties[MigrateToUuidStep::FOREIGN_KEY_INDEX])) {
                    $this->logger->notice(sprintf('Will add %s foreign key', $tableName), $logContext->toArray());
                    if (!$context->dryRun()) {
                        $this->addForeignKey($tableName, $tableProperties);
                        $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                    }
                }
                foreach ($tableProperties[MigrateToUuidStep::UNIQUE_CONSTRAINTS_INDEX] as $constraintName => $constraintColumns) {
                    if (!$this->constraintExists($tableName, $constraintName)) {
                        $this->logger->notice(sprintf('Will add %s constraint %s', $tableName, $constraintName), $logContext->toArray());
                        if (!$context->dryRun()) {
                            $this->addUniqueConstraint($tableName, $constraintName, $constraintColumns);
                            $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                        }
                    }
                }
                foreach ($tableProperties[MigrateToUuidStep::INDEXES_INDEX] as $indexName => $indexColumns) {
                    if (null == $this->getIndexName($tableName, $indexColumns)) {
                        $this->logger->notice(sprintf('Will add %s constraint %s', $tableName, $indexName), $logContext->toArray());
                        if (!$context->dryRun()) {
                            $this->addIndex($tableName, $indexName, $indexColumns);
                            $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                        }
                    }
                }
            }
        }

        return true;
    }

    private function hasPrimaryKey(string $tableName, array $primaryKeyColumns): bool
    {
        $expected = \json_encode($primaryKeyColumns);
        $real = \json_encode($this->getPrimaryKey($tableName));

        return $expected === $real;
    }

    /**
     * This method switches primary key to use a new one with a uuid.
     * To keep performance, we add a temporary index named `migrate_to_uuid_temp_index_to_delete`.
     * This index has to be dropped once everything is migrated.
     */
    private function setPrimaryKey(string $tableName, array $tableProperties): void
    {
        $sql = <<<SQL
            ALTER TABLE {tableName}
                ADD CONSTRAINT migrate_to_uuid_temp_index_to_delete UNIQUE ({formerColumnNames}),
                DROP PRIMARY KEY,
                ADD PRIMARY KEY ({newColumnNames}),
                ALGORITHM=INPLACE,
                LOCK=NONE
        SQL;

        $newColumnNames = $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX];
        $formerColumnNames = $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX];
        $indexOfUuid = \array_search($tableProperties[MigrateToUuidStep::UUID_COLUMN_INDEX], $tableProperties[MigrateToUuidStep::PRIMARY_KEY_UUID_INDEX]);
        $formerColumnNames[$indexOfUuid] = $tableProperties[MigrateToUuidStep::ID_COLUMN_INDEX];

        $query = \strtr($sql, [
            '{tableName}' => $tableName,
            '{formerColumnNames}' => \implode(', ', array_map(fn (string $columnName): string => sprintf('`%s`', $columnName), $formerColumnNames)),
            '{newColumnNames}' => \implode(', ', array_map(fn (string $columnName): string => sprintf('`%s`', $columnName), $newColumnNames)),
        ]);

        $this->connection->executeQuery($query);
    }

    private function addForeignKey(string $tableName, array $tableProperties): void
    {
        $sql = <<<SQL
            ALTER TABLE {tableName} ADD CONSTRAINT {constraintName} FOREIGN KEY ({uuidColumnName}) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE,
            ALGORITHM=INPLACE,
            LOCK=NONE; 
        SQL;

        $query = \strtr($sql, [
            '{tableName}' => $tableName,
            '{constraintName}' => $tableProperties[MigrateToUuidStep::FOREIGN_KEY_INDEX],
            '{uuidColumnName}' => $tableProperties[MigrateToUuidStep::UUID_COLUMN_INDEX],
        ]);

        $this->connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->connection->executeQuery($query);
        } finally {
            $this->connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function addUniqueConstraint(string $tableName, string $constraintName, array $columnNames): void
    {
        $sql = <<<SQL
            ALTER TABLE {tableName} ADD CONSTRAINT {constraintName} UNIQUE ({columnNames}),
            ALGORITHM=INPLACE,
            LOCK=NONE
        SQL;

        $query = \strtr($sql, [
            '{tableName}' => $tableName,
            '{constraintName}' => $constraintName,
            '{columnNames}' => \implode(', ', array_map(fn (string $columnName): string => sprintf('`%s`', $columnName), $columnNames)),
        ]);

        $this->connection->executeQuery($query);
    }

    private function addIndex(string $tableName, string $indexName, array $columnNames): void
    {
        $sql = <<<SQL
            ALTER TABLE {tableName}
                ADD INDEX {indexName} ({columnNames}),
                ALGORITHM=INPLACE,
                LOCK=NONE
        SQL;

        $query = \strtr($sql, [
            '{tableName}' => $tableName,
            '{indexName}' => $indexName,
            '{columnNames}' => \implode(', ', array_map(fn (string $columnName): string => sprintf('`%s`', $columnName), $columnNames)),
        ]);

        $this->connection->executeQuery($query);
    }
}
