<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidFillProductUuid implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

    private const BATCH_SIZE = 1000;

    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Generates uuid4 for every product';
    }

    public function getMissingCount(): int
    {
        return $this->getMissingProductUuidCount();
    }
    /**
     * {@inheritDoc}
     */
    public function shouldBeExecuted(): bool
    {
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return false;
        }

        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM pim_catalog_product
                WHERE uuid IS NULL
                LIMIT 1
            ) AS missing
        SQL;

        return (bool) $this->connection->fetchOne(\sprintf($sql, 'uuid'));
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        $count = $this->getMissingProductUuidCount();
        while ($count > 0) {
            $output->writeln(sprintf('    Will add %d uuids (still missing: %d)', min(self::BATCH_SIZE, $count), $count));
            if (!$dryRun) {
                $this->fillMissingProductUuids();
                $count = $this->getMissingProductUuidCount();
            } else {
                $output->writeln(sprintf('    Option --dry-run is set, will continue to next step.'));
                $count = 0;
            }
        }
    }

    private function getMissingProductUuidCount(): int
    {
        return $this->columnExists('pim_catalog_product', 'uuid') ?
            $this->getNullUuidCount('uuid') :
            $this->getProductCount();
    }

    private function getNullUuidCount(string $uuidColumnName): int
    {
        $sql = <<<SQL
            SELECT COUNT(*) 
            FROM pim_catalog_product
            WHERE %s IS NULL
        SQL;

        return (int) $this->connection->fetchOne(sprintf($sql, $uuidColumnName));
    }

    private function getProductCount(): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM pim_catalog_product
        SQL;

        return (int) $this->connection->fetchOne($sql);
    }

    private function fillMissingProductUuids(): void
    {
        $this->connection->beginTransaction();

        $productIdsWithNullUuids = $this->getProductIdsWithNullUuids();

        /**
         * We need to insert mysql valid row, even if the ON DUPLICATE KEY updates only uuid.
         * The double md5(rand()) is here to be sure there will not be any collision on identifier.
         */
        $values = array_map(fn (string $productId): string => sprintf(
            '(%s, UUID_TO_BIN("%s"), 1, CONCAT(md5(rand()), md5(rand())), "{}", NOW(), NOW())',
            $productId,
            Uuid::uuid4()->toString()
        ), $productIdsWithNullUuids);

        $insertSql = <<<SQL
            INSERT INTO pim_catalog_product (id, uuid, is_enabled, identifier, raw_values, created, updated)
            VALUES %s
            ON DUPLICATE KEY UPDATE uuid=VALUES(uuid)
        SQL;

        $this->connection->executeQuery(sprintf($insertSql, implode(', ', $values)));
        $this->connection->commit();
    }

    private function getProductIdsWithNullUuids(): array
    {
        $sqlProductIdsWithNullUuids = <<<SQL
            SELECT id
            FROM pim_catalog_product 
            WHERE uuid is NULL
            LIMIT %d
        SQL;

        return $this->connection->fetchFirstColumn(
            sprintf($sqlProductIdsWithNullUuids, self::BATCH_SIZE)
        );
    }
}
