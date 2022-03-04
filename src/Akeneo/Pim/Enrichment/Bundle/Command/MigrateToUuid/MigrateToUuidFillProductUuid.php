<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidFillProductUuid implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    private const BATCH_SIZE = 1000;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getDescription(): string
    {
        return 'Generates uuid4 for every product';
    }

    public function getName(): string
    {
        return 'fill_product_uuid';
    }

    public function getMissingCount(): int
    {
        return $this->getMissingProductUuidCount();
    }

    public function shouldBeExecuted(): bool
    {
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return true; //TODO should return false instead ?
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

    public function addMissing(Context $context, OutputInterface $output): bool
    {
        $logContext = $context->logContext;
        $batchesCount = 0;
        $processedItems = 0;
        while ($this->shouldContinue($context, $output) > 0) {
            $logContext->addContext('substep', 'missing_product_uuid_batch_' . $batchesCount++);
            if (!$context->dryRun()) {
                $count = $this->getMissingProductUuidCount();
                $processedItems += min($count, self::BATCH_SIZE);

                $stepStartTime = \microtime(true);
                $this->fillMissingProductUuids();
                $stepDuration = \microtime(true) - $stepStartTime;

                $this->logger->notice(
                    \sprintf('batch done in %0.2f seconds', $stepDuration),
                    $logContext->toArray(['processed_uuids_counter' => $processedItems])
                );
            } else {
                $this->logger->notice("Option --dry-run is set, will continue to next step.", $logContext->toArray());
                break;
            }
        }

        return true;
    }

    private function shouldContinue(Context $context, OutputInterface $output): bool
    {
        $logContext = $context->logContext;
        if ($context->withStats()) {
            $count = $this->getMissingProductUuidCount();
            $shouldBeExecuted = $count > 0;
            if ($shouldBeExecuted) {
                $this->logger->notice('Will add uuids', $logContext->toArray(['product_id_to_fill_count' => min(self::BATCH_SIZE, $count)]));
            }

            return $shouldBeExecuted;
        }

        $shouldBeExecuted = $this->shouldBeExecuted();
        if ($shouldBeExecuted) {
            $this->logger->notice('Will add uuids', $logContext->toArray(['product_id_to_fill_count' => self::BATCH_SIZE]));
        }

        return $shouldBeExecuted;
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
