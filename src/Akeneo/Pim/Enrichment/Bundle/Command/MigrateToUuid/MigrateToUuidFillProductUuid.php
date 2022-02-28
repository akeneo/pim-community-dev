<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

    public function shouldBeExecuted(): bool
    {
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return true;
        }

        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM pim_catalog_product
                WHERE uuid IS NULL
                LIMIT 1
            ) AS missing
        SQL;

        return (bool) $this->connection->fetchOne($sql);
    }

    public function addMissing(bool $dryRun, OutputInterface $output): bool
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

        return true;
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
            WHERE {uuid_column_name} IS NULL
        SQL;

        return (int) $this->connection->fetchOne(\strtr($sql, ['{uuid_column_name}' => $uuidColumnName]));
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
        $values = array_map(fn (string $productId): string => \strtr(
            '({product_id}, UUID_TO_BIN("{uuid}"), 1, CONCAT(md5(rand()), md5(rand())), "{}", NOW(), NOW())',
            [
                '{product_id}' => $productId,
                '{uuid}' => Uuid::uuid4()->toString(),
            ]
        ), $productIdsWithNullUuids);

        $insertSql = <<<SQL
            INSERT INTO pim_catalog_product (id, uuid, is_enabled, identifier, raw_values, created, updated)
            VALUES {values}
            ON DUPLICATE KEY UPDATE uuid=VALUES(uuid)
        SQL;

        $this->connection->executeQuery(\strtr($insertSql, ['{values}' => implode(', ', $values)]));
        $this->connection->commit();
    }

    private function getProductIdsWithNullUuids(): array
    {
        $sqlProductIdsWithNullUuids = <<<SQL
            SELECT id
            FROM pim_catalog_product 
            WHERE uuid is NULL
            LIMIT {batch_size}
        SQL;

        return $this->connection->fetchFirstColumn(
            \strtr($sqlProductIdsWithNullUuids, ['{batch_size}' => self::BATCH_SIZE])
        );
    }
}
