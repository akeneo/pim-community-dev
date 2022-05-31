<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * Query to fetch category codes by a given list of product uuids.
 *
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetCategoryCodesByProductUuids
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param array<Uuid> $uuids
     * @return array ['0a45937f-8e4b-4d14-b60c-4971515d37ff' => ['category1'], '738e95aa-7581-4c52-aa04-59a75dbfccc4' => ['category2']]
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchCategoryCodes(array $uuids): array
    {
        $results = [];

        foreach ($uuids as $uuid) {
            $results[$uuid->toString()] = [];
        }

        $forProductQuery = <<<SQL
SELECT BIN_TO_UUID(product_uuid) as product_uuid, JSON_ARRAYAGG(category_codes) as category_codes
FROM (
         SELECT product.uuid as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_category_product category_product ON product.uuid = category_product.product_uuid
                INNER JOIN pim_catalog_category category ON category.id = category_product.category_id
         WHERE product.uuid IN (:productUuids)
       UNION ALL
         SELECT product.uuid as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
                INNER JOIN pim_catalog_category_product_model category_model ON model.id = category_model.product_model_id
                INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.uuid IN (:productUuids)
       UNION ALL
         SELECT product.uuid as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
           INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
           INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
           INNER JOIN pim_catalog_category_product_model category_model ON parent.id = category_model.product_model_id
           INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.uuid IN (:productUuids)
) all_results
GROUP BY product_uuid
SQL;

        $productUuids = \array_map(
            function ($uuid) {
                return $uuid->getBytes();
            },
            $uuids
        );

        $queryResults = $this->connection->fetchAllAssociative(
            $forProductQuery,
            ['productUuids' => $productUuids],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($queryResults as $queryResult) {
            $categoryCodes = json_decode($queryResult['category_codes']);
            sort($categoryCodes);
            // @todo https://akeneo.atlassian.net/browse/PIM-9220
            $categoryCodes = array_values(array_unique($categoryCodes));
            $results[$queryResult['product_uuid']] = $categoryCodes;
        }

        return $results;
    }
}
