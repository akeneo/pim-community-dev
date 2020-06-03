<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

class FindNonExistingProductIdentifiersQuery implements FindNonExistingProductIdentifiersQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productIdentifiers): array
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
        )->fetchAll(\PDO::FETCH_COLUMN);

        $nonExistingProductIdentifiers = array_values(array_diff($productIdentifiers, $results));

        return $nonExistingProductIdentifiers;
    }
}
