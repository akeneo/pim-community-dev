<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidFillForeignUuid implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    private const BATCH_SIZE = 10000;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getDescription(): string
    {
        return 'Fill foreign tables with product uuids';
    }

    public function getName(): string
    {
        return 'fill_foreign_uuid';
    }

    public function shouldBeExecuted(): bool
    {
        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            if ($this->shouldBeExecutedForTable($tableName, $columnNames[self::UUID_COLUMN_INDEX], $columnNames[self::ID_COLUMN_INDEX])) {
                return true;
            }
        }

        return false;
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            $missingUuidCount = $this->getMissingForeignUuidCount($tableName, $columnNames[1], $columnNames[0]);
            $count += $missingUuidCount;
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;

        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            if (!$this->tableExists($tableName)) {
                continue;
            }

            $processedItems = 0;
            $logContext->addContext('substep', $tableName);

            $this->logger->notice(
                \sprintf('Fill foreign uuids for table %s', $tableName),
                $logContext->toArray()
            );

            do {
                $updatedRows = 0;
                if (!$context->dryRun()) {
                    $updatedRows = $this->fillMissingForeignUuidInsert($tableName, $columnNames[0], $columnNames[1]);
                    $processedItems += $updatedRows;
                    $this->logger->notice(
                        \sprintf('Processed rows: %d', $processedItems),
                        $logContext->toArray(['processed_foreign_uuids_count' => $processedItems])
                    );
                } else {
                    $this->logger->notice("Option --dry-run is set, will continue to next step.", $logContext->toArray());
                }
            } while ($updatedRows > 0);
        }

        return true;
    }

    private function shouldBeExecutedForTable(string $tableName, string $uuidColumnName, string $idColumnName): bool
    {
        if (!$this->tableExists($tableName)) {
            return false;
        }

        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM {table_name} t
                INNER JOIN pim_catalog_product p ON p.id = t.{id_column_name}
                WHERE {column_name} IS NULL
                {extra_condition}
            ) as missing
        SQL;

        $query = \strtr($sql, [
            '{table_name}' => $tableName,
            '{column_name}' => $uuidColumnName,
            '{id_column_name}' => $idColumnName,
            '{extra_condition}' => \in_array($tableName, ['pim_versioning_version', 'pim_comment_comment'])
                ? ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"'
                : '',
        ]);

        return (bool) $this->connection->fetchOne($query);
    }

    private function getMissingForeignUuidCount(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        if (!$this->tableExists($tableName)) {
            return 0;
        }

        return $this->getNullForeignUuidCellsCount($tableName, $uuidColumnName, $idColumnName);
    }

    private function getNullForeignUuidCellsCount(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM {table_name}
            INNER JOIN pim_catalog_product p ON p.id = {table_name}.{id_column_name}
            WHERE {table_name}.{uuid_column_name} IS NULL
            {extra_condition}
        SQL;

        $query = \strtr($sql, [
            '{table_name}' => $tableName,
            '{uuid_column_name}' => $uuidColumnName,
            '{id_column_name}' => $idColumnName,
            '{extra_condition}' => \in_array($tableName, ['pim_versioning_version', 'pim_comment_comment']) ?
                ' AND resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"' :
                ''
        ]);

        return (int) $this->connection->fetchOne($query);
    }

    private function fillMissingForeignUuidInsert(string $tableName, string $idColumnName, string $uuidColumnName): int
    {
        $sql = <<<SQL
            WITH batched_id AS (
                SELECT {id_column_name}
                FROM {table_name}
                WHERE {uuid_column_name} IS NULL
                LIMIT {limit}
            )
            UPDATE {table_name} t, pim_catalog_product p, batched_id b
            SET t.{uuid_column_name}=p.uuid
            WHERE t.{id_column_name}=p.id
                AND t.{id_column_name}=b.{id_column_name}
        SQL;
        if (\in_array($tableName, ['pim_versioning_version', 'pim_comment_comment'])) {
            $sql = <<<SQL
                WITH batched_id AS (
                    SELECT v.id, p.uuid
                    FROM {table_name} v, pim_catalog_product p
                    WHERE resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"
                    AND resource_uuid IS NULL
                    AND p.id = CAST(v.resource_id AS UNSIGNED)
                    LIMIT {limit}
                )
                UPDATE {table_name} v, batched_id b
                SET v.resource_uuid = b.uuid
                WHERE v.id = b.id
            SQL;
        }

        $this->connection->executeQuery(\strtr(
            $sql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumnName,
                '{id_column_name}' => $idColumnName,
                '{limit}' => self::BATCH_SIZE,
            ]
        ));

        return (int) ($this->connection->executeQuery('SELECT ROW_COUNT()')->fetchOne() ?? 0);
    }

    private function getTablesWithoutProductTable(): array
    {
        return array_filter(
            self::TABLES,
            fn (string $tableName): bool => $tableName !== 'pim_catalog_product',
            ARRAY_FILTER_USE_KEY
        );
    }
}
