<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUuidMappingQuery implements GetUuidMappingQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromProductIdentifiers(array $productIdentifiers, array $productUuids): UuidMapping
    {
        if (empty($productIdentifiers) && empty($productUuids)) {
            return UuidMapping::createFromMapping([]);
        }

        $query = <<<SQL
            SELECT BIN_TO_UUID(uuid) as uuid, id, identifier
            FROM pim_catalog_product
            WHERE identifier IN (:product_identifiers) OR uuid IN (:product_uuids)
        SQL;

        $productUuidsAsBytes = \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $mapping = $this->connection->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers, 'product_uuids' => $productUuidsAsBytes],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY, 'product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return UuidMapping::createFromMapping($mapping);
    }

    public function fromProductIds(array $productIds, array $productUuids): UuidMapping
    {
        if (empty($productIds) && empty($productUuids)) {
            return UuidMapping::createFromMapping([]);
        }

        $query = <<<SQL
            SELECT BIN_TO_UUID(uuid) as uuid, id, identifier
            FROM pim_catalog_product
            WHERE id IN (:product_ids) OR uuid IN (:product_uuids)
        SQL;

        $productUuidsAsBytes = \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $mapping = $this->connection->executeQuery(
            $query,
            ['product_ids' => $productIds, 'product_uuids' => $productUuidsAsBytes],
            ['product_ids' => Connection::PARAM_STR_ARRAY, 'product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return UuidMapping::createFromMapping($mapping);
    }
}
