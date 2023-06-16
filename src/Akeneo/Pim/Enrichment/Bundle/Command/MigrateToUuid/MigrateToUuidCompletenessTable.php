<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;

/**
 * The process is different for completeness
 * Creating a temporary table without indexes and foreign keys allows us to speed up the process
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidCompletenessTable implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    const TABLE_NAME = 'pim_catalog_completeness';
    const INSERT_BATCH_SIZE = 100000;

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getDescription(): string
    {
        return 'Migrates the completeness table';
    }

    public function getName(): string
    {
        return 'migrate_completeness_table';
    }

    public function shouldBeExecuted(): bool
    {
        return (bool) $this->connection->executeQuery(
            <<<SQL
SELECT EXISTS(SELECT * FROM pim_catalog_completeness WHERE product_uuid is null)
SQL
        )->fetchOne();
    }

    public function getMissingCount(): int
    {
        return $this->getMissingForeignUuidCount(
            'pim_catalog_completeness',
            'product_uuid',
            'product_id'
        );
    }

    private function getMissingForeignUuidCount(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        if (!$this->tableExists($tableName)) {
            return 0;
        }

        return $this->getNullForeignUuidCellsCount($tableName, $uuidColumnName);
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        if ($context->dryRun()) {
            return true;
        }

        if ($this->getMissingForeignUuidCount('pim_catalog_completeness', 'product_uuid', '') === 0) {
            return true;
        }

        $this->connection->executeQuery(
            <<<SQL
DROP TABLE IF EXISTS pim_catalog_completeness_temp
SQL
        );

        $this->logger->notice(sprintf('Will create the temporary completeness table'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
CREATE TABLE pim_catalog_completeness_temp (SELECT id, locale_id, channel_id, missing_count, required_count, product_uuid FROM pim_catalog_completeness WHERE 1 = 0)
SQL
        );

        // Add primary key to speed up the lookup during inserts
        $this->logger->notice(sprintf('Will set the primary key and autoincrement on id'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
ALTER TABLE pim_catalog_completeness_temp MODIFY COLUMN id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY
SQL
        );

        // Insert uuids
        $this->logger->notice(sprintf('Will insert data into temporary table'), $logContext->toArray());
        do {
            $count = $this->connection->executeQuery(
                <<<SQL
INSERT INTO pim_catalog_completeness_temp
SELECT c.id, c.locale_id, c.channel_id, c.missing_count, c.required_count, p.uuid as product_uuid
FROM pim_catalog_completeness c
JOIN pim_catalog_product p on c.product_id = p.id
WHERE c.id > :max_migrated_id
LIMIT :batch_size
SQL,
                [
                    'max_migrated_id' => $this->getMaxMigratedId() ?? 0,
                    'batch_size' => self::INSERT_BATCH_SIZE
                ],
                [
                    'batch_size' => Types::INTEGER
                ]
            )->rowCount();
        } while ($count > 0);

        // Put index on the uuid column
        $this->logger->notice(sprintf('Will index on the temporary completeness table uuid column'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
CREATE INDEX product_uuid ON pim_catalog_completeness_temp (product_uuid)
SQL
        );

        // Set uuids as not nullable
        $this->logger->notice(sprintf('Will set the uuid column as not nullable'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
ALTER TABLE pim_catalog_completeness_temp
MODIFY `product_uuid` BINARY(16) NOT NULL;
SQL
        );

        // Drop original table
        $this->logger->notice(sprintf('Will drop the original table'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
DROP TABLE pim_catalog_completeness;
SQL
        );

        // Replace with temporary table which is now ready
        $this->logger->notice(sprintf('Will rename the temporary table'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
RENAME TABLE pim_catalog_completeness_temp TO pim_catalog_completeness;
SQL
        );

        // Temporarily disable foreign key checks
        $this->logger->notice(sprintf('Will disable foreign key checks'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
SET FOREIGN_KEY_CHECKS=0
SQL
        );

        // Add channel foreign key
        $this->logger->notice(sprintf('Will add foreign key towards channels'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
ALTER TABLE pim_catalog_completeness
    ADD CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`)
    REFERENCES `pim_catalog_channel` (`id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
SQL
        );

        // Add locale foreign key
        $this->logger->notice(sprintf('Will add foreign key towards locales'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
ALTER TABLE pim_catalog_completeness
    ADD CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`)
    REFERENCES `pim_catalog_locale` (`id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
SQL
        );

        // Temporarily disable unique checks
        $this->logger->notice(sprintf('Will disable unique checks'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
SET UNIQUE_CHECKS=0
SQL
        );

        $this->logger->notice(sprintf('Will create unique constraint'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
ALTER TABLE pim_catalog_completeness
    ADD CONSTRAINT `channel_locale_product_unique_idx` UNIQUE (`channel_id`,`locale_id`,`product_uuid`)
SQL
        );

        // Re-enable checks
        $this->logger->notice(sprintf('Will re-enable checks'), $logContext->toArray());
        $this->connection->executeQuery(
            <<<SQL
SET UNIQUE_CHECKS=1
SQL
        );
        $this->connection->executeQuery(
            <<<SQL
SET FOREIGN_KEY_CHECKS=1
SQL
        );

        return true;
    }

    private function getNullForeignUuidCellsCount(string $tableName, string $uuidColumnName): int
    {
        $sql = <<<SQL
SELECT COUNT(*)
FROM {table_name}
WHERE {table_name}.{uuid_column_name} IS NULL
SQL;

        $query = \strtr($sql, [
            '{table_name}' => $tableName,
            '{uuid_column_name}' => $uuidColumnName
        ]);

        return (int) $this->connection->fetchOne($query);
    }

    private function getMaxMigratedId(): mixed
    {
        return $this->connection->executeQuery(
            <<<SQL
SELECT MAX(id) from pim_catalog_completeness_temp
SQL
        )->fetchOne();
    }
}
