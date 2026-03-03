<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductModelCodesQueryInterface;
use Doctrine\DBAL\Connection;

class FindNonExistingProductModelCodesQuery implements FindNonExistingProductModelCodesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $query = <<<SQL
        SELECT code FROM pim_catalog_product_model WHERE code IN (:product_model_codes)
SQL;

        $results = $this->connection->executeQuery(
            $query,
            ['product_model_codes' => $productModelCodes],
            ['product_model_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        $nonExistingProductModelCodes = array_values(array_diff($productModelCodes, $results));

        return $nonExistingProductModelCodes;
    }
}
