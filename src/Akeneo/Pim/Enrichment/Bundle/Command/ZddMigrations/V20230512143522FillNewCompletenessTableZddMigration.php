<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class V20230512143522FillNewCompletenessTableZddMigration implements ZddMigration
{
    private const NEW_TABLE = 'pim_catalog_product_completeness';
    private const LEGACY_TABLE = 'pim_catalog_completeness';
    private bool $shouldLog = true;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function migrate(): void
    {
        if (!$this->tableExists(self::NEW_TABLE)) {
            throw new \RuntimeException(sprintf('The "%s" table does not exist yet', self::NEW_TABLE));
        }

        if (!$this->tableExists(self::LEGACY_TABLE)) {
            $this->log(
                \sprintf('The %s table does not exist anymore, no need to migrate data', self::LEGACY_TABLE)
            );

            return;
        }

        $this->log('Start migrating completeness data...');

        $migratedRows = 0;
        $batches = 0;
        foreach ($this->getProductUuids() as $uuids) {
            $migratedRows += $this->insertCompleteness($uuids);
            $batches++;
            if ($batches >= 100) {
                $this->log(\sprintf('Completeness rows handled so far: %d', $migratedRows));
                $batches = 0;
            }
        }

        $this->log(\sprintf('Migration successful, %d completeness row handled', $migratedRows));
    }

    public function migrateNotZdd(): void
    {
        $this->shouldLog = false;
        $this->migrate();
    }

    public function getName(): string
    {
        return 'FillNewCompletenessTable';
    }

    private function insertCompleteness(array $uuids): int
    {
        $sql = <<<SQL
INSERT INTO pim_catalog_product_completeness(product_uuid, completeness)
WITH channels AS (SELECT id, code FROM pim_catalog_channel),
     locales AS (SELECT id, code FROM pim_catalog_locale),
     completeness_by_locale AS (
         SELECT product_uuid, channel_id, JSON_OBJECTAGG(locales.code, JSON_OBJECT('required', required_count, 'missing', missing_count)) AS completeness
         FROM pim_catalog_completeness
            INNER JOIN locales ON locales.id = pim_catalog_completeness.locale_id
         WHERE product_uuid IN (:uuids)
         GROUP BY product_uuid, channel_id
     )
SELECT c.product_uuid, JSON_OBJECTAGG(channels.code, completeness)
FROM pim_catalog_completeness c
         INNER JOIN channels ON c.channel_id = channels.id
         INNER JOIN locales ON c.locale_id = locales.id
         INNER JOIN completeness_by_locale ON c.product_uuid = completeness_by_locale.product_uuid AND c.channel_id = completeness_by_locale.channel_id
GROUP BY c.product_uuid
ON DUPLICATE KEY UPDATE completeness = VALUES(completeness);
SQL;
        return \intval($this->connection->executeStatement(
            $sql,
            ['uuids' => \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids)],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        ));
    }

    /**
     * @return iterable<UuidInterface[]>
     */
    private function getProductUuids(): iterable
    {
        $searchAfter = 0;
        do {
            $rows = $this->connection->fetchAllAssociative(
                'select id, BIN_TO_UUID(uuid) as uuid from pim_catalog_product where id > :searchAfter order by id asc LIMIT 100',
                ['searchAfter' => $searchAfter]
            );
            $uuids = \array_map(
                static fn (array $row): UuidInterface => Uuid::fromString($row['uuid']),
                $rows
            );
            $searchAfter = \end($rows)['id'];
            yield $uuids;
        } while (count($uuids) === 100);
    }

    private function tableExists(string $tableName): bool
    {
        return \intval($this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            ['tableName' => $tableName]
        )->rowCount()) >= 1;
    }

    private function log(string $message): void
    {
        if (!$this->shouldLog) {
            return;
        }
        $this->logger->notice($message, ['zdd_migration' => $this->getName()]);
    }
}
