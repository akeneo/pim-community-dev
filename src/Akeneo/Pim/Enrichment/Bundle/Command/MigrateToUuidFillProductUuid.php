<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillProductUuid implements MigrateToUuidStep
{
    private const BATCH_SIZE = 1000;

    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Generates uuid4 for every product';
    }

    /**
     * {@inheritDoc}
     */
    public function shouldBeExecuted(): bool
    {
        // @todo: improve
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        return $this->getMissingProductUuidCount();
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        $count = $this->getMissingProductUuidCount();
        while ($count > 0) {
            $output->writeln(sprintf('    Will add %d uuids (still missing: %d)', min(self::BATCH_SIZE, $count), $count));
            if (!$dryRun) {
                $this->fillMissingProductUuidsInsert();
                $count = $this->getMissingProductUuidCount();
            } else {
                $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                $count = 0;
            }
        }
    }

    private function getMissingUuidCount(string $tableName, string $uuidColumnName): int
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s IS NULL', $tableName, $uuidColumnName);

        $result = $this->connection->fetchOne($sql);

        return (int) $result;
    }

    private function getIdCount(string $tableName): int
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s', $tableName);

        $result = $this->connection->fetchOne($sql);

        return (int) $result;
    }

    private function getMissingProductUuidCount(): int
    {
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return $this->getIdCount('pim_catalog_product');
        } else {
            return $this->getMissingUuidCount('pim_catalog_product', 'uuid');
        }
    }

    private function fillMissingProductUuidsInsert(): void
    {
        // TODO CREATE INDEX toto ON pim_catalog_product (uuid);

        $this->connection->beginTransaction();
        $productIdsWithoutUuids = $this->connection->fetchFirstColumn(sprintf('SELECT id FROM pim_catalog_product WHERE uuid is NULL LIMIT %d', self::BATCH_SIZE));
        $values = array_map(fn (string $productId): string => sprintf('(%s, UUID_TO_BIN("%s"), 1, CONCAT(md5(rand()), md5(rand())), "{}", NOW(), NOW())', $productId, Uuid::uuid4()->toString()), $productIdsWithoutUuids);

        $sql = sprintf('
INSERT INTO pim_catalog_product (id, uuid, is_enabled, identifier, raw_values, created, updated)
VALUES %s ON DUPLICATE KEY UPDATE uuid=VALUES(uuid)', implode(', ', $values));

        $this->connection->executeQuery($sql);
        $this->connection->commit();
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
