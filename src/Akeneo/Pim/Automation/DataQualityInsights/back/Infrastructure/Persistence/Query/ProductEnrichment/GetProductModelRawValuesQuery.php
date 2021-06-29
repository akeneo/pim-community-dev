<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductRawValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelRawValuesQuery implements GetProductRawValuesQueryInterface
{
    /** * @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(ProductId $productId): array
    {
        $query = <<<SQL
SELECT
    JSON_MERGE(
        COALESCE(product_model_parent.raw_values, '{}'),
        product_model.raw_values
    ) AS raw_values
    FROM pim_catalog_product_model AS product_model
    LEFT JOIN pim_catalog_product_model AS product_model_parent ON product_model_parent.id = product_model.parent_id
WHERE product_model.id = :product_model_id;
SQL;

        $statement = $this->dbConnection->executeQuery($query,
            [
                'product_model_id' => $productId->toInt(),
            ],
            [
                'product_model_id' => \PDO::PARAM_INT,
            ]
        );

        $result = $statement->fetchColumn();

        return false === $result ? [] : json_decode($result, true);
    }
}
