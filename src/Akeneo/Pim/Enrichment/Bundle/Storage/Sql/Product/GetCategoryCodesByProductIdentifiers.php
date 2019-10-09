<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;

/**
 * Query to fetch category codes by a given list of product identifiers.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryCodesByProductIdentifiers
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array ['identifier1' => ['category1'], 'identifier2' => ['category2']]
     */
    public function fetchCategoryCodes(array $identifiers): array
    {
        $identifiers = (function (string ...$identifier) {
            return $identifier;
        })(... $identifiers);

        $results = [];

        foreach ($identifiers as $identifier) {
            $results[$identifier] = [];
        }

        $forProductQuery = <<<SQL
SELECT product_identifier, JSON_ARRAYAGG(category_codes) as category_codes
FROM (
         SELECT product.identifier as product_identifier, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_category_product category_product ON product.id = category_product.product_id
                INNER JOIN pim_catalog_category category ON category.id = category_product.category_id
         WHERE product.identifier IN (?)
       UNION ALL
         SELECT product.identifier as product_identifier, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
                INNER JOIN pim_catalog_category_product_model category_model ON model.id = category_model.product_model_id
                INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.identifier IN (?)
       UNION ALL
         SELECT product.identifier as product_identifier, category.code as category_codes
         FROM pim_catalog_product product
           INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
           INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
           INNER JOIN pim_catalog_category_product_model category_model ON parent.id = category_model.product_model_id
           INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.identifier IN (?)
) all_results
GROUP BY product_identifier
SQL;

        $queryResults = $this->connection->fetchAll(
            $forProductQuery,
            [$identifiers, $identifiers, $identifiers],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]);


        foreach ($queryResults as $queryResult) {
            $categoryCodes = json_decode($queryResult['category_codes']);
            sort($categoryCodes);
            $results[$queryResult['product_identifier']] = $categoryCodes;
        }

        return $results;
    }
}
