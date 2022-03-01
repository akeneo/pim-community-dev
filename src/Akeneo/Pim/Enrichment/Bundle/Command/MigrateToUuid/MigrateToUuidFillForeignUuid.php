<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidFillForeignUuid implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

    private const BATCH_SIZE = 50000;

    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Fill foreign tables with product uuids';
    }

    public function shouldBeExecuted(): bool
    {
        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            if ($this->shouldBeExecutedForTable($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
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

    public function addMissing(Context $context, OutputInterface $output): bool
    {
        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            while ($this->shouldContinue($context, $output, $tableName, $columnNames[self::ID_COLUMN_INDEX], $columnNames[self::UUID_COLUMN_INDEX])) {
                if (!$context->dryRun()) {
                    $this->fillMissingForeignUuidInsert($tableName, $columnNames[0], $columnNames[1]);
                } else {
                    $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                    break;
                }
            }
        }

        return true;
    }

    private function shouldContinue(
        Context $context,
        OutputInterface $output,
        string $tableName,
        string $idColumnName,
        string $uuidColumnName
    ): bool {
        if ($context->withStats()) {
            $count = $this->getMissingForeignUuidCount($tableName, $uuidColumnName, $idColumnName);
            $output->writeln(sprintf('    Missing %d foreign uuids in "%s" table', $count, $tableName));
            if ($count > 0) {
                $output->writeln(sprintf('    Will add %d foreign uuids in "%s" table', min($count, self::BATCH_SIZE), $tableName));
                return true;
            }

            return false;
        }

        $shouldContinue = $this->shouldBeExecutedForTable($tableName, $uuidColumnName);
        if ($shouldContinue) {
            $output->writeln(sprintf('    Will add up to %d foreign uuids in "%s" table', self::BATCH_SIZE, $tableName));
        }

        return $shouldContinue;
    }

    private function shouldBeExecutedForTable($tableName, $uuidColumnName): bool
    {
        if (!$this->tableExists($tableName)) {
            return false;
        }

        if (!$this->columnExists($tableName, $uuidColumnName)) {
            return true;
        }

        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM {table_name}
                WHERE {column_name} 
                IS NULL
                {extra_condition}
                LIMIT 1
            ) as missing
        SQL;

        $query = \strtr($sql, [
            '{table_name}' => $tableName,
            '{column_name}' => $uuidColumnName,
            '{extra_condition}' => $tableName === 'pim_versioning_version'
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

        if ($this->columnExists($tableName, $uuidColumnName)) {
            return $this->getNullForeignUuidCellsCount($tableName, $uuidColumnName, $idColumnName);
        }

        return $this->getNotNullForeignIdCellsCount($tableName, $idColumnName);
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
            '{extra_condition}' => ($tableName === 'pim_versioning_version') ?
                ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"' :
                ''
        ]);

        return (int) $this->connection->fetchOne($query);
    }

    private function getNotNullForeignIdCellsCount(string $tableName, string $idColumnName): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM {table_name}
            INNER JOIN pim_catalog_product p ON p.id = {table_name}.{id_column_name}
            WHERE {table_name}.{id_column_name} IS NOT NULL
            {extra_condition}
        SQL;

        $query = \strtr($sql, [
            '{table_name}' => $tableName,
            '{id_column_name}' => $idColumnName,
            '{extra_condition}' => ($tableName === 'pim_versioning_version') ?
                ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"' :
                ''
        ]);

        return (int) $this->connection->fetchOne($query);
    }

    private function fillMissingForeignUuidInsert(string $tableName, string $idColumnName, string $uuidColumnName): void
    {
        $sql = <<<SQL
            WITH batched_id AS (
                SELECT {id_column_name}
                FROM {table_name}
                WHERE {uuid_column_name} IS NULL
                {extra_condition}
                LIMIT {limit}
            )
            UPDATE {table_name} t, pim_catalog_product p, batched_id b
            SET t.{uuid_column_name}=p.uuid
            WHERE t.{id_column_name}=p.id
                AND t.{id_column_name}=b.{id_column_name}
        SQL;

        $this->connection->executeQuery(\strtr(
            $sql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumnName,
                '{id_column_name}' => $idColumnName,
                '{extra_condition}' => $tableName === 'pim_versioning_version' ?
                    ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"' :
                    '',
                '{limit}' => self::BATCH_SIZE,
            ]
        ));
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
