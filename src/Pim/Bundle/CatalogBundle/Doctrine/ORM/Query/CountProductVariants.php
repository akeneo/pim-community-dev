<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductAndProductModel\Query\CountProductVariantsInterface;

/**
 * @todo pull-up 3.x Move to `Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql`
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountProductVariants implements CountProductVariantsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forProductModels(array $productModels): int
    {
        $productModelIds = \array_map(
            function (ProductModelInterface $productModel) {
                return $productModel->getId();
            },
            $productModels
        );

        $sql = <<<'SQL'
            select count(product.id)
            from pim_catalog_product_model level_1
            left join pim_catalog_product_model level_2 on level_2.parent_id = level_1.id
            left join pim_catalog_product product
                on product.product_model_id = level_1.id
                or product.product_model_id = level_2.id
            where level_1.id in (:productModelIds)
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            ['productModelIds' => $productModelIds],
            ['productModelIds' => Connection::PARAM_INT_ARRAY]
        );

        return (int)$stmt->fetchColumn();
    }
}
