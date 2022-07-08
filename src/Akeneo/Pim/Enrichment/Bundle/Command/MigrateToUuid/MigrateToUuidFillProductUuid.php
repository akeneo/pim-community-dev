<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

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

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $processedItems = 0;
        while ($this->shouldContinue($context) > 0) {
            $logContext->addContext('substep', 'missing_product_uuid_batch');
            if (!$context->dryRun()) {
                $this->fillMissingProductUuids();
                $processedItems += self::BATCH_SIZE;
                $this->logger->notice(
                    \sprintf('Processed rows: %d', $processedItems),
                    $logContext->toArray(['processed_uuids_counter' => $processedItems])
                );
            } else {
                $this->logger->notice("Option --dry-run is set, will continue to next step.", $logContext->toArray());
                break;
            }
        }

        return true;
    }

    private function shouldContinue(Context $context): bool
    {
        if ($context->withStats()) {
            return $this->getMissingProductUuidCount() > 0;
        }
        return $this->shouldBeExecuted();
    }

    private function getMissingProductUuidCount(): int
    {
        return $this->getNullUuidCount('uuid');
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
