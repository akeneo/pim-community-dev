<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidCleanCompletenessTable implements MigrateToUuidStep
{
    private const BATCH_SIZE = 10000;

    use MigrateToUuidTrait;
    use StatusAwareTrait;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getName(): string
    {
        return 'clean_completeness_table';
    }

    public function getDescription(): string
    {
        return 'Remove pim_catalog_completeness records which do not match any product';
    }

    public function addMissing(Context $context): bool
    {
        if ($context->dryRun()) {
            $this->logger->notice(
                'Option --dry-run is set, will continue to next step.',
                $context->logContext->toArray()
            );

            return true;
        }

        $this->logger->notice(
            'Will clean ghost records for pim_catalog_completeness table',
            $context->logContext->toArray()
        );

        $cleanedRows = 0;
        $currentProductId = 0;

        do {
            $idsToRemove = $this->connection->executeQuery(
                <<<SQL
                SELECT c.product_id
                FROM pim_catalog_completeness c
                LEFT JOIN pim_catalog_product p ON p.id = c.product_id
                WHERE c.product_id >= :currentProductId
                AND p.id IS NULL
                LIMIT :limit
                SQL,
                [
                    'currentProductId' => $currentProductId,
                    'limit' => self::BATCH_SIZE,
                ],
                [
                    'currentProductId' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchFirstColumn();

            if ([] !== $idsToRemove) {
                $this->connection->executeQuery(
                    'DELETE FROM pim_catalog_completeness WHERE product_id IN (:idsToRemove)',
                    ['idsToRemove' => $idsToRemove],
                    ['idsToRemove' => Connection::PARAM_INT_ARRAY]
                );

                $cleanedRows += \count($idsToRemove);
                $this->logger->notice(
                    \sprintf('Non-existing product ids cleaned: %d', $cleanedRows),
                    $context->logContext->toArray(['cleaned_completeness_rows_counter' => $cleanedRows])
                );
            }
            $currentProductId = \end($idsToRemove);
        } while (\count($idsToRemove) >= self::BATCH_SIZE);

        return true;
    }

    public function getMissingCount(): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(c.id) AS count FROM pim_catalog_completeness c
            LEFT JOIN pim_catalog_product p ON p.id = c.product_id
            WHERE p.id IS NULL
            SQL
        )->fetchOne();
    }

    public function shouldBeExecuted(): bool
    {
        return (bool) $this->connection->executeQuery(
            <<<SQL
            SELECT EXISTS (
                SELECT 1 FROM pim_catalog_completeness c
                LEFT JOIN pim_catalog_product p ON p.id = c.product_id
                WHERE p.id IS NULL
                LIMIT 1
            ) AS is_existing
            SQL
        )->fetchOne();
    }
}
