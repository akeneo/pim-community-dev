<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class V20230124090000FillProductIdentifiersColumn implements ZddMigration
{
    public function __construct(private readonly Connection $connection, private readonly LoggerInterface $logger)
    {
    }

    public function getName(): string
    {
        return 'FillProductIdentifiersColumn';
    }

    public function migrate(): void
    {
        if (!$this->requiredTablesAndColumnsExist()) {
            throw new \LogicException(
                'The required "pim_catalog_product_identifiers" table and/or the "pim_catalog_attribute.main_identifier" columns have not been created yet'
            );
        }
        $this->fillIdentifiersColumn();

        $this->log('Zdd migration finished');
    }

    private function requiredTablesAndColumnsExist(): bool
    {
        if (!\in_array('pim_catalog_product_identifiers', $this->connection->getSchemaManager()->listTableNames())) {
            return false;
        }
        $attributeColumnNames = \array_map(
            static fn (Column $column) => $column->getName(),
            $this->connection->getSchemaManager()->listTableColumns('pim_catalog_attribute')
        );

        return \in_array('main_identifier', $attributeColumnNames);
    }

    private function fillIdentifiersColumn(): void
    {
        $identifierAttributes = $this->connection->fetchFirstColumn(
            <<<SQL
            SELECT code
            FROM pim_catalog_attribute
            WHERE attribute_type = 'pim_catalog_identifier'
            SQL
        );
        $identifiers = \array_map(
            static fn (string $attributeCode): string => \sprintf(
                <<<SQL
                CONCAT('%s#', raw_values->>'$.%s."<all_channels>"."<all_locales>"')
                SQL,
                $attributeCode,
                $attributeCode
            ),
            $identifierAttributes
        );

        $updatedRows = 0;
        foreach ($this->getUuidsToInsert() as $uuids) {
            $updatedRows += $this->connection->executeStatement(
                \sprintf(
                    <<<SQL
                    REPLACE INTO pim_catalog_product_identifiers(uuid, identifiers)
                    SELECT uuid, JSON_ARRAY(%s)
                    FROM pim_catalog_product
                    WHERE uuid IN (:uuids);
                    SQL,
                    implode(', ', $identifiers)
                ),
                ['uuids' => $uuids],
                ['uuids' => Connection::PARAM_STR_ARRAY]
            );
            $this->log('Rows filled so far: ' . $updatedRows);
        }
    }

    /**
     * @return iterable<string[]>
     */
    private function getUuidsToInsert(): iterable
    {
        while (true) {
            $uuids = $this->connection->fetchFirstColumn(
                <<<SQL
                SELECT uuid FROM pim_catalog_product product
                WHERE NOT EXISTS (
                    SELECT * from pim_catalog_product_identifiers identifiers WHERE identifiers.uuid = product.uuid
                )
                LIMIT 100;
                SQL
            );
            if ([] === $uuids) {
                return;
            }

            yield $uuids;
        }
    }

    private function log(string $message): void
    {
        $this->logger->notice($message, ['zdd_migration' => $this->getName()]);
    }
}
