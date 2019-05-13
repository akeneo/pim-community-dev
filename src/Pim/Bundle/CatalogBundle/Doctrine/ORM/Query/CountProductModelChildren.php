<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\CountProductModelChildrenInterface;

/**
 * @todo pull-up 3.x Move to `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql`
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountProductModelChildren implements CountProductModelChildrenInterface
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
            select count(distinct level_1.id) + count(distinct level_2.id)
            from pim_catalog_product_model level_1
            left join pim_catalog_product_model level_2 on level_2.parent_id = level_1.id
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
