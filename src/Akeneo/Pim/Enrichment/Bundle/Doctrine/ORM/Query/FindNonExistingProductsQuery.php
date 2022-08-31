<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductsQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

class FindNonExistingProductsQuery implements FindNonExistingProductsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $query = <<<SQL
        SELECT identifier FROM pim_catalog_product WHERE identifier IN (:product_identifiers)
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return array_values(array_diff($productIdentifiers, $results));
    }

    public function byProductUuids(array $productUuids): array
    {
        if (empty($productUuids)) {
            return [];
        }

        $productUuidsAsBytes = \array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), $productUuids);

        $query = <<<SQL
        SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE uuid IN (:product_uuids)
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['product_uuids' => $productUuidsAsBytes],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return array_values(array_diff($productUuids, $results));
    }
}
