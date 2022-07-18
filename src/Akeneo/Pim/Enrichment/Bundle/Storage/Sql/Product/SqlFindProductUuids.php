<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This class is temporary and should not be called if you don't need to refactor.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductUuids
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * Returns an array uuid as Uuid => identifier
     *
     * @param string[] $identifiers
     * @return array<string, UuidInterface>
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function fromIdentifiers(array $identifiers): array
    {
        if (count($identifiers) === 0) {
            return [];
        }

        $result = $this->connection->fetchAllKeyValue(
            'SELECT identifier, BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE identifier IN (:identifiers)',
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );

        return array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $result);
    }
}
