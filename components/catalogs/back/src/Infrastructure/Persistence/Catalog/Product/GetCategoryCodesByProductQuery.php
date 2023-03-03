<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetCategoryCodesByProductQueryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryCodesByProductQuery implements GetCategoryCodesByProductQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(array $productUuids): array
    {
        $query = <<<SQL
            SELECT product_uuid, JSON_ARRAYAGG(category_codes) as category_codes
            FROM (
                     SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
                     FROM pim_catalog_product product
                            INNER JOIN pim_catalog_category_product category_product ON product.uuid = category_product.product_uuid
                            INNER JOIN pim_catalog_category category ON category.id = category_product.category_id
                     WHERE product.uuid IN (:product_uuids)
                   UNION ALL
                     SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
                     FROM pim_catalog_product product
                            INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
                            INNER JOIN pim_catalog_category_product_model category_model ON model.id = category_model.product_model_id
                            INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
                     WHERE product.uuid IN (:product_uuids)
                   UNION ALL
                     SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
                     FROM pim_catalog_product product
                       INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
                       INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
                       INNER JOIN pim_catalog_category_product_model category_model ON parent.id = category_model.product_model_id
                       INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
                     WHERE product.uuid IN (:product_uuids)
            ) all_results
            GROUP BY product_uuid
        SQL;

        $uuidsAsBytes = \array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), $productUuids);

        /** @var array<string, string[]> */
        $results = $this->connection->fetchAllAssociative(
            $query,
            ['product_uuids' => $uuidsAsBytes],
            ['product_uuids' => Connection::PARAM_STR_ARRAY],
        );
        return $results;
    }
}
