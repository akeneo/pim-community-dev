<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillUuids implements MigrateToUuidStep
{
    private const BATCH_SIZE = 1000;

    public function __construct(private Connection $connection)
    {
    }

    public function getMissingCount(OutputInterface $output): int
    {
        $count = 0;
        foreach (MigrateToUuidCreateColumns::TABLES as $tableName => $columnNames) {
            $missingUuidCount = $this->getMissingUuidCount($tableName, $columnNames[1]);
            $output->writeln(sprintf('... missing %d uuid for %s table', $missingUuidCount, $tableName));
            $count += $missingUuidCount;
        }

        return $count;
    }

    public function addMissing(OutputInterface $output): void
    {
        $count = $this->getMissingProductUuidCount();
        $output->writeln(sprintf('... missing %d uuids in product table', $count));
        while ($count > 0) {
            $this->fillMissingProductUuidsInsert();
            $count = $this->getMissingProductUuidCount();
            $output->writeln(sprintf('... missing %d uuids in products table', $count));
        }

        foreach (MigrateToUuidCreateColumns::TABLES as $tableName => $columnNames) {
            if ($tableName === 'pim_catalog_product') {
                continue;
            }
            $count = $this->getMissingUuidCount($tableName, $columnNames[1]);
            $output->writeln(sprintf('... missing %d foreign uuids in %s table', $count, $tableName));
            if ($count > 0) {
                $this->fillMissingForeignUuidInsert($tableName, $columnNames[0], $columnNames[1]);
            }
        }
    }

    private function getMissingUuidCount(string $tableName, string $uuidColumnName): int
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s IS NULL', $tableName, $uuidColumnName);
        if ($tableName === 'pim_versioning_version') {
            $sql .= ' AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"';
        }

        $result = $this->connection->fetchOne($sql);

        return (int) $result;
    }

    private function getMissingProductUuidCount(): int
    {
        return $this->getMissingUuidCount('pim_catalog_product', 'uuid');
    }

    private function fillMissingProductUuidsSingleUpdate(): void
    {
        $productIdsWithoutUuids = $this->connection->fetchFirstColumn(sprintf('SELECT id FROM pim_catalog_product WHERE uuid is NULL LIMIT %d', self::BATCH_SIZE));
        $parameters = [];
        $sql = '';
        foreach ($productIdsWithoutUuids as $productId) {
            $sql .= sprintf('
                UPDATE pim_catalog_product set uuid=UUID_TO_BIN(?) WHERE id=? LIMIT 1;
            ');
            $parameters[] = Uuid::uuid4()->toString();
            $parameters[] = $productId;
        }
        $this->connection->executeQuery($sql, $parameters);
    }

    private function fillMissingProductUuidsInsert(): void
    {
        // TODO CREATE INDEX toto ON pim_catalog_product (uuid);

        $this->connection->beginTransaction();
        $productIdsWithoutUuids = $this->connection->fetchFirstColumn(sprintf('SELECT id FROM pim_catalog_product WHERE uuid is NULL LIMIT %d', self::BATCH_SIZE));
        $values = array_map(fn (string $productId): string => sprintf('(%s, UUID_TO_BIN("%s"), 1, CONCAT(md5(rand()), md5(rand())), "{}", NOW(), NOW())', $productId, Uuid::uuid4()->toString()), $productIdsWithoutUuids);

        $sql = sprintf('INSERT INTO pim_catalog_product (id, uuid, is_enabled, identifier, raw_values, created, updated) VALUES %s ON DUPLICATE KEY UPDATE uuid=VALUES(uuid)', implode(', ', $values));

        $this->connection->executeQuery($sql);
        $this->connection->commit();
    }

    private function fillMissingForeignUuidInsert(string $tableName, string $idColumnName, string $uuidColumnName): void
    {
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
}
