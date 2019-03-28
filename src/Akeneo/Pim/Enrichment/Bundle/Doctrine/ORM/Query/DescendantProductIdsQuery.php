<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductIdsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DescendantProductIdsQuery implements DescendantProductIdsQueryInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchFromProductModelIds(array $productModelIds): array
    {
        if (empty($productModelIds)) {
            return [];
        }

        $sql = <<<SQL
SELECT id FROM pim_catalog_product
WHERE product_model_id IN (:productModelIds)
SQL;

        $resultRows = $this->connection->executeQuery(
            $sql,
            ['productModelIds' => $productModelIds],
            ['productModelIds' => Connection::PARAM_INT_ARRAY]
        )->fetchAll();

        return array_map(function ($rowData) {
            return (int) $rowData['id'];
        }, $resultRows);
    }
}
