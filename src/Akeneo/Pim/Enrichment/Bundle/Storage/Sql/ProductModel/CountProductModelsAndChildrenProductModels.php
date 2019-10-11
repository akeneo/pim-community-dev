<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CountProductModelsAndChildrenProductModelsInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountProductModelsAndChildrenProductModels implements CountProductModelsAndChildrenProductModelsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forProductModelCodes(array $productModelCodes): int
    {
        if (0 === count($productModelCodes)) {
            return 0;
        }

        $sql = <<<'SQL'
            select count(distinct product_model.id)
            from (
                (
                    select level_1.id
                    from pim_catalog_product_model level_1
                    where level_1.code in (:productModelCodes)
                )
                union
                (
                    select level_2.id
                    from pim_catalog_product_model level_1
                        inner join pim_catalog_product_model level_2 on level_2.parent_id = level_1.id
                    where level_1.code in (:productModelCodes)
                )
            ) product_model
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        return (int)$stmt->fetchColumn();
    }
}
