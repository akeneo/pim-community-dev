<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetProductUuids implements GetProductUuids
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromIdentifier(string $identifier): ?UuidInterface
    {
        $uuids = $this->fromIdentifiers([$identifier]);

        return $uuids[$identifier] ?? null;
    }

    public function fromIdentifiers(array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $result = $this->connection->fetchAllAssociative(
            <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(uuid) AS uuid, pim_catalog_product_unique_data.raw_data AS identifier
FROM pim_catalog_product
LEFT JOIN pim_catalog_product_unique_data
    ON pim_catalog_product.uuid = pim_catalog_product_unique_data.product_uuid
    AND pim_catalog_product_unique_data.attribute_id = (SELECT id FROM main_identifier)
WHERE raw_data in (:identifiers)
SQL,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $indexedUuidsByIdentifier = [];
        foreach ($result as $data) {
            $indexedUuidsByIdentifier[(string) $data['identifier']] = Uuid::fromString($data['uuid']);
        }

        return $indexedUuidsByIdentifier;
    }

    public function fromUuid(UuidInterface $uuid): ?UuidInterface
    {
        return $this->fromUuids([$uuid])[$uuid->toString()] ?? null;
    }

    public function fromUuids(array $uuids): array
    {
        if ([] === $uuids) {
            return [];
        }

        return \array_reduce(
            $this->connection->fetchFirstColumn(
                'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE uuid IN (:uuids)',
                ['uuids' => \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids)],
                ['uuids' => Connection::PARAM_STR_ARRAY],
            ),
            static fn (array $carry, string $uuid): array => $carry + [$uuid => Uuid::fromString($uuid)],
            []
        );
    }
}
