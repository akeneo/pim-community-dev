<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetProductUuids;
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
}
