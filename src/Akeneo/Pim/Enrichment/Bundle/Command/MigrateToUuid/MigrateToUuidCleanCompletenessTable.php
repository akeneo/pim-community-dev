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
            $this->logger->notice('Option --dry-run is set, will continue to next step.', $context->logContext->toArray());

            return true;
        }

        $this->logger->notice(
            'Will clean ghost records for pim_catalog_completeness table',
            $context->logContext->toArray()
        );

        $cleanedRows = 0;
        while ($this->shouldBeExecuted()) {
            $this->connection->executeQuery(
                <<<SQL
                WITH ids_to_remove AS (
                    SELECT c.id
                    FROM pim_catalog_completeness c
                    LEFT JOIN pim_catalog_product p ON p.id = c.product_id
                    WHERE p.id IS NULL
                    LIMIT :limit
                )
                DELETE c.*
                FROM pim_catalog_completeness c
                INNER JOIN ids_to_remove ON c.id = ids_to_remove.id
                SQL,
                [
                    'limit' => self::BATCH_SIZE,
                ],
                [
                    'limit' => \PDO::PARAM_INT
                ]
            );

            $cleanedRows += self::BATCH_SIZE;
            $this->logger->notice(
                \sprintf('Cleaned rows: %d', $cleanedRows),
                $context->logContext->toArray(['cleaned_completeness_rows_counter' => $cleanedRows])
            );
        }

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
                SELECT * FROM pim_catalog_completeness c
                LEFT JOIN pim_catalog_product p ON p.id = c.product_id
                WHERE p.id IS NULL
            ) AS is_existing
            SQL
        )->fetchOne();
    }
}
