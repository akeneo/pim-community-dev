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
        $rows = [];
        for ($rowNumber = 1; $rowNumber <= self::BATCH_SIZE; $rowNumber++) {
            $rows[] = \sprintf("ROW(%d, '%s')", $rowNumber, Uuid::uuid4()->toString());
        }

        $sql = <<<SQL
        WITH
        product_uuid AS (
            SELECT * FROM (VALUES
                {rows}
            ) as t(rn, uuid)
        ),
        product_to_migrate AS (
            SELECT id, row_number() over () as rn
            FROM pim_catalog_product
            WHERE uuid is NULL
            LIMIT {batch_size}
        )
        UPDATE pim_catalog_product p, product_to_migrate, product_uuid
        SET p.uuid = UUID_TO_BIN(product_uuid.uuid)
        WHERE p.id = product_to_migrate.id AND product_to_migrate.rn = product_uuid.rn;
        SQL;

        $sql = strtr($sql, [
            '{rows}' => \implode(',', $rows),
            '{batch_size}' => self::BATCH_SIZE,
        ]);

        $this->connection->executeQuery($sql);
    }
}
