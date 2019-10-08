<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAncestorProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $identifiers
     *
     * @return string[]
     */
    public function fromProductIdentifiers(array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $sql = <<<SQL
WITH sub_product_model AS (
    SELECT parent.parent_id, parent.code
    FROM pim_catalog_product product
    INNER JOIN pim_catalog_product_model parent ON parent.id = product.product_model_id
    WHERE product.identifier IN (:identifiers)
)
SELECT sub_product_model.code
FROM sub_product_model
UNION
SELECT root.code
FROM sub_product_model
INNER JOIN pim_catalog_product_model root ON root.id = sub_product_model.parent_id;
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'identifiers' => $identifiers,
            ],
            [
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll(FetchMode::COLUMN, 0);
    }
}
