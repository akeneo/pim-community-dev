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
class MigrateToUuidCleanGhostRecords implements MigrateToUuidStep
{
    private const BATCH_SIZE = 10000;

    /** @var string[] */
    private array $ignoredTables = [
        'pim_catalog_product',
        'pim_versioning_version',
        'pimee_workflow_published_product',
        'pim_comment_comment',
    ];

    use MigrateToUuidTrait;
    use StatusAwareTrait;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getName(): string
    {
        return 'clean_ghost_records';
    }

    public function getDescription(): string
    {
        return 'Remove database foreign records which do not match any product';
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

        $sql = <<<SQL
            SELECT f.{id_column_name}
            FROM {table_name} f
            LEFT JOIN pim_catalog_product p ON p.id = f.{id_column_name}
            WHERE f.{id_column_name} >= :currentProductId
            AND p.id IS NULL
            ORDER BY f.{id_column_name} ASC
            LIMIT :limit
            SQL;

        $cleanedRows = 0;

        foreach (self::TABLES as $tableName => $tableProperties) {
            if (\in_array($tableName, $this->ignoredTables) || !$this->tableExists($tableName)) {
                continue;
            }

            $this->logger->notice(
                \sprintf('Will clean ghost records for %s table', $tableName),
                $context->logContext->toArray()
            );

            $currentProductId = 0;
            do {
                $idsToRemove = $this->connection->executeQuery(
                    \strtr(
                        $sql,
                        [
                            '{table_name}' => $tableName,
                            '{id_column_name}' => $tableProperties[self::ID_COLUMN_INDEX],
                        ]
                    ),
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
                        \strtr(
                            'DELETE FROM {table_name} WHERE {id_column_name} IN (:idsToRemove)',
                            [
                                '{table_name}' => $tableName,
                                '{id_column_name}' => $tableProperties[self::ID_COLUMN_INDEX],
                            ]
                        ),
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
        }

        return true;
    }

    public function getMissingCount(): int
    {
        $count = 0;

        $sql = <<<SQL
            SELECT COUNT(f.id) AS count FROM {table_name} f
            LEFT JOIN pim_catalog_product p ON p.id = f.{id_column_name}
            WHERE p.id IS NULL
            SQL;

        foreach (self::TABLES as $tableName => $tableProperties) {
            if (!\in_array($tableName, $this->ignoredTables) && $this->tableExists($tableName)) {
                $count += (int) $this->connection->executeQuery(\strtr(
                    $sql,
                    [
                        '{table_name}' => $tableName,
                        '{id_column_name}' => $tableProperties[self::ID_COLUMN_INDEX],
                    ]
                ))->fetchOne();
            }
        }

        return $count;
    }

    public function shouldBeExecuted(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1 FROM {table_name} f
                LEFT JOIN pim_catalog_product p ON p.id = f.{id_column_name}
                WHERE p.id IS NULL
                LIMIT 1
            ) AS is_existing
            SQL;

        foreach (self::TABLES as $tableName => $tableProperties) {
            if (!\in_array($tableName, $this->ignoredTables) && $this->tableExists($tableName)) {
                $result = (bool) $this->connection->executeQuery(\strtr(
                    $sql,
                    [
                        '{table_name}' => $tableName,
                        '{id_column_name}' => $tableProperties[self::ID_COLUMN_INDEX],
                    ]
                ))->fetchOne();

                if (true === $result) {
                    return true;
                }
            }
        }

        return false;
    }
}
