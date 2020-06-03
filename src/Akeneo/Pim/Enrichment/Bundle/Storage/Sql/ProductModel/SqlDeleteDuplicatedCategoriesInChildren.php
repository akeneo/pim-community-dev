<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\DeleteDuplicatedCategoriesInChildren;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlDeleteDuplicatedCategoriesInChildren implements DeleteDuplicatedCategoriesInChildren
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function forProductModelCodes(array $productModelCodes): void
    {
        $query = <<<SQL
DELETE category_product
FROM pim_catalog_product_model AS product_model
    LEFT JOIN pim_catalog_product_model AS sub_model ON sub_model.parent_id = product_model.id
    INNER JOIN pim_catalog_category_product_model AS category_product_model ON category_product_model.product_model_id = product_model.id
    INNER JOIN pim_catalog_product AS product ON product.product_model_id IN (product_model.id, sub_model.id)
    INNER JOIN pim_catalog_category_product AS category_product
        ON category_product.product_id = product.id
        AND category_product.category_id = category_product_model.category_id
WHERE product_model.code IN (:produtcModelCodes);

DELETE category_sub_model
FROM pim_catalog_product_model AS product_model
    INNER JOIN pim_catalog_product_model AS sub_model ON sub_model.parent_id = product_model.id
    INNER JOIN pim_catalog_category_product_model AS category_product_model ON category_product_model.product_model_id = product_model.id
    INNER JOIN pim_catalog_category_product_model AS category_sub_model
        ON category_sub_model.product_model_id = sub_model.id
        AND category_sub_model.category_id = category_product_model.category_id
WHERE product_model.code IN (:produtcModelCodes);
SQL;

        $this->dbConnection->executeQuery(
            $query,
            ['produtcModelCodes' => $productModelCodes],
            ['produtcModelCodes' => Connection::PARAM_STR_ARRAY]
        );
    }
}
