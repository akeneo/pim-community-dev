<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\ProductAndProductModel\Query\CountProductVariantsInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @todo pull-up 3.x Move to `Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql`
 */
final class CountProductVariants implements CountProductVariantsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forProductModels(array $productModelCodes): int
    {
        if (0 === count($productModelCodes)) {
            return 0;
        }

        $sql = <<<'SQL'
            select count(distinct product.id)
            from pim_catalog_product_model level_1
            left join pim_catalog_product_model level_2 on level_2.parent_id = level_1.id
            left join pim_catalog_product product
                on product.product_model_id = level_1.id
                or product.product_model_id = level_2.id
            where level_1.code in (:productModelCodes)
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        return (int)$stmt->fetchColumn();
    }
}
