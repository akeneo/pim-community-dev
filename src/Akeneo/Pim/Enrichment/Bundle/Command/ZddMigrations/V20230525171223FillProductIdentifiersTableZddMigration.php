<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class V20230525171223FillProductIdentifiersTableZddMigration implements ZddMigration
{
    private bool $shouldLog = true;

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'FillProductIdentifiersTable';
    }

    public function migrate(): void
    {
        if (!$this->requiredTableExists()) {
            throw new \RuntimeException(
                'The required "pim_catalog_product_identifiers" table have not been created yet'
            );
        }

        $this->fillIdentifiersColumn();

        $this->log('Zdd migration finished');
    }

    public function migrateNotZdd(): void
    {
        $this->shouldLog = false;
        $this->migrate();
    }

    private function fillIdentifiersColumn(): void
    {
        $migratedRows = 0;
        $batches = 0;
        foreach ($this->getIdentifiersToInsert() as $identifiers) {
            $migratedRows += $this->insertIdentifiers($identifiers);
            $batches++;
            if ($batches >= 100) {
                $this->log(\sprintf('Identifiers rows handled so far: %d', $migratedRows));
                $batches = 0;
            }
        }

        $this->log(\sprintf('Migration successful, %d identifiers row handled', $migratedRows));
    }

    /**
     * @return iterable<array<string, array<string, string>>>
     */
    private function getIdentifiersToInsert(): iterable
    {
        $sql = <<<SQL
            WITH identifier_codes AS (
                SELECT code as identifiercode
                FROM pim_catalog_attribute
                WHERE attribute_type = 'pim_catalog_identifier'
            ),
            raw_values_per_product AS (
                SELECT id, uuid, raw_values
                FROM pim_catalog_product
                WHERE id > :searchAfter
                ORDER BY id
                LIMIT 100
            )
            SELECT id,
                   BIN_TO_UUID(uuid) as uuid,
                   JSON_OBJECTAGG(
                       identifiercode,
                       JSON_EXTRACT(raw_values, CONCAT('$.', identifiercode, '."<all_channels>"."<all_locales>"'))
                   ) AS identifiers
            FROM identifier_codes, raw_values_per_product
            GROUP BY id, raw_values_per_product.uuid
            SQL;

        $searchAfter = 0;
        do {
            $identifiers = [];
            $rows = $this->connection->fetchAllAssociative(
                $sql,
                ['searchAfter' => $searchAfter],
                ['searchAfter' => ParameterType::INTEGER],
            );

            if ([] === $rows) {
                return;
            }

            foreach ($rows as $row) {
                $identifiers[$row['uuid']] = \array_filter(
                    \json_decode($row['identifiers'], true),
                    static fn (?string $identifierValue): bool => null !== $identifierValue,
                );
            }

            $searchAfter = \end($rows)['id'];

            yield $identifiers;
        } while (count($identifiers) === 100);
    }

    /**
     * @param array<string, array<string, string>> $identifiers
     */
    private function insertIdentifiers(array $identifiers): int
    {
        $statement = $this->connection->prepare(
            \sprintf(
                <<<SQL
                INSERT INTO pim_catalog_product_identifiers(product_uuid, identifiers)
                VALUES %s
                ON DUPLICATE KEY UPDATE identifiers = VALUES(identifiers);
                SQL,
                \implode(', ', \array_fill(0, \count($identifiers), '(?, ?)'))
            )
        );

        $paramIndex = 0;
        foreach ($identifiers as $uuid => $identifierValues) {
            $statement->bindValue(++$paramIndex, Uuid::fromString($uuid)->getBytes(), ParameterType::BINARY);
            $values = [];
            foreach ($identifierValues as $attributeCode => $value) {
                $values[] = \sprintf('%s#%s', $attributeCode, $value);
            }
            $statement->bindValue(++$paramIndex, \json_encode($values));
        }

        return $statement->executeStatement();
    }

    private function requiredTableExists(): bool
    {
        return \intval($this->connection->executeQuery(
            <<<SQL
            SHOW TABLES LIKE 'pim_catalog_product_identifiers'
            SQL
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
