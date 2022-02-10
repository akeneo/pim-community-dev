<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillForeignUuid implements MigrateToUuidStep
{
    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Fill foreign tables with product uuids';
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach (MigrateToUuidCreateColumns::TABLES as $tableName => $columnNames) {
            if ($tableName === 'pim_catalog_product') {
                continue;
            }
            $missingUuidCount = $this->getMissingCount2($tableName, $columnNames[1], $columnNames[0]);
            $count += $missingUuidCount;
        }

        return $count;
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        foreach (MigrateToUuidCreateColumns::TABLES as $tableName => $columnNames) {
            if ($tableName === 'pim_catalog_product') {
                continue;
            }

            $count = $this->getMissingCount2($tableName, $columnNames[1], $columnNames[0]);
            $output->writeln(sprintf('    Will add %d foreign uuids in "%s" table', $count, $tableName));
            if ($count > 0 && !$dryRun) {
                $this->fillMissingForeignUuidInsert($tableName, $columnNames[0], $columnNames[1]);
            }
        }
    }

    private function getMissingCount2(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        if ($this->columnExists($tableName, $uuidColumnName)) {
            return $this->getMissingUuidCount($tableName, $uuidColumnName);
        }

        return $this->getMissingUuidCount($tableName, $idColumnName, true);
    }

    private function getMissingUuidCount(string $tableName, string $uuidColumnName, bool $not = false): int
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s IS %s NULL', $tableName, $uuidColumnName, $not ? 'NOT' : '');
        if ($tableName === 'pim_versioning_version') {
            $sql .= ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"';
        }

        $result = $this->connection->fetchOne($sql);

        return (int) $result;
    }

    private function fillMissingForeignUuidInsert(string $tableName, string $idColumnName, string $uuidColumnName): void
    {
        // TODO CREATE INDEX toto ON pim_catalog_product (uuid);

        $sql = 'UPDATE %s t, pim_catalog_product p SET t.%s = p.uuid WHERE t.%s=p.id';
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

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]);

        return count($rows) >= 1;
    }
}
