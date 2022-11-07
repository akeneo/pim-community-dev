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
            'SELECT BIN_TO_UUID(uuid) as uuid, identifier FROM pim_catalog_product WHERE identifier in (:identifiers)',
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
