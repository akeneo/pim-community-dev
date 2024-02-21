<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Query to fetch category codes by a given list of product identifiers.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryCodesByProductUuids
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param array<UuidInterface> $uuids
     * @return array<string, array<string>> ['uuid1' => ['category1'], 'uuid2' => ['category2']]
     */
    public function fetchCategoryCodes(array $uuids): array
    {
        Assert::allIsInstanceOf($uuids, UuidInterface::class);

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $uuids);

        $results = [];
        foreach ($uuids as $uuid) {
            $results[$uuid->toString()] = [];
        }

        $forProductQuery = <<<SQL
SELECT product_uuid, JSON_ARRAYAGG(category_codes) as category_codes
FROM (
         SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_category_product category_product ON product.uuid = category_product.product_uuid
                INNER JOIN pim_catalog_category category ON category.id = category_product.category_id
         WHERE product.uuid IN (?)
       UNION ALL
         SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
                INNER JOIN pim_catalog_category_product_model category_model ON model.id = category_model.product_model_id
                INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.uuid IN (?)
       UNION ALL
         SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
           INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
           INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
           INNER JOIN pim_catalog_category_product_model category_model ON parent.id = category_model.product_model_id
           INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.uuid IN (?)
) all_results
GROUP BY product_uuid
SQL;

        $queryResults = $this->connection->fetchAllAssociative(
            $forProductQuery,
            [$uuidsAsBytes, $uuidsAsBytes, $uuidsAsBytes],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
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
