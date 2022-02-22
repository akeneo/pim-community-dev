<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillForeignUuid implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Fill foreign tables with product uuids';
    }

    /**
     * {@inheritDoc}
     */
    public function shouldBeExecuted(): bool
    {
        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            if (!$this->tableExists($tableName)) {
                continue;
            }

            $isColumnExist = $this->columnExists($tableName, $columnNames[1]);
            $sql = <<<SQL
                SELECT EXISTS (
                    SELECT 1
                    FROM {table_name}
                    WHERE {column_name} 
                    IS {not} NULL
                    {extra_condition}
                    LIMIT 1
                ) as missing
            SQL;

            $query = strtr($sql, [
                '{table_name}' => $tableName,
                '{column_name}' => $isColumnExist ? $columnNames[1] : $columnNames[0],
                '{not}' => $isColumnExist ? '' : 'NOT',
                '{extra_condition}' => $tableName === 'pim_versioning_version'
                    ? ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"'
                    : '',
            ]);

            if ((bool) $this->connection->fetchOne($query)) {
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

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        foreach ($this->getTablesWithoutProductTable() as $tableName => $columnNames) {
            $count = $this->getMissingForeignUuidCount($tableName, $columnNames[1], $columnNames[0]);
            $output->writeln(sprintf('    Will add %d foreign uuids in "%s" table', $count, $tableName));
            if ($count > 0 && !$dryRun) {
                $this->fillMissingForeignUuidInsert($tableName, $columnNames[0], $columnNames[1]);
            }
        }
    }

    private function getMissingForeignUuidCount(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        if (!$this->tableExists($tableName)) {
            return 0;
        }

        if ($this->columnExists($tableName, $uuidColumnName)) {
            return $this->getNullCellCount($tableName, $uuidColumnName);
        }

        return $this->getNullCellCount($tableName, $idColumnName, true);
    }

    private function getNullCellCount(string $tableName, string $columnName, bool $not = false): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM %s 
            WHERE %s IS %s NULL
        SQL;

        $query = sprintf($sql, $tableName, $columnName, $not ? 'NOT' : '');
        if ($tableName === 'pim_versioning_version') {
            $query .= ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"';
        }

        return (int) $this->connection->fetchOne($query);
    }

    private function fillMissingForeignUuidInsert(string $tableName, string $idColumnName, string $uuidColumnName): void
    {
        $sql = <<<SQL
            UPDATE %s t, pim_catalog_product p 
            SET t.%s = p.uuid
            WHERE t.%s=p.id
        SQL;

        if ($tableName === 'pim_versioning_version') {
            $sql .= ' AND t.resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"';
        }

        $this->connection->executeQuery(sprintf(
            $sql,
            $tableName,
            $uuidColumnName,
            $idColumnName
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
