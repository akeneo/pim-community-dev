<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\ProductModel\Query\DescendantProductModelIdsQueryInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DescendantProductModelIdsQuery implements DescendantProductModelIdsQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchFromParentProductModelId(int $parentProductModelId): array
    {
        $sql = <<<SQL
SELECT id FROM pim_catalog_product_model
WHERE parent_id = :parentId
SQL;

        $resultRows = $this->connection->executeQuery(
            $sql,
            ['parentId' => $parentProductModelId]
        )->fetchAll();

        return array_map(function ($rowData) {
            return (int) $rowData['id'];
        }, $resultRows);
    }
}
