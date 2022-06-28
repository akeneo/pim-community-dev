<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetDescendantVariantProductUuidsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Doctrine\DBAL\Connection;

class GetDescendantVariantProductUuids implements GetDescendantVariantProductUuidsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductModelIds(ProductEntityIdCollection $productModelIdCollection): array
    {
        if ($productModelIdCollection->isEmpty()) {
            return [];
        }

        $sql = <<<SQL
WITH
filter_product_model AS (
    SELECT id, parent_id, code FROM pim_catalog_product_model WHERE id IN (:ids)
)
SELECT
    BIN_TO_UUID(product.uuid) as uuid
FROM filter_product_model
    INNER JOIN pim_catalog_product product ON filter_product_model.id = product.product_model_id
UNION DISTINCT
SELECT
    BIN_TO_UUID(product.uuid) as uuid
FROM filter_product_model
    INNER JOIN pim_catalog_product_model product_model ON filter_product_model.id = product_model.parent_id
    INNER JOIN pim_catalog_product product             ON product_model.id = product.product_model_id
SQL;

        return $this->connection->executeQuery(
            $sql,
            ['ids' => array_map(fn ($productId) => (int)$productId, $productModelIdCollection->toArrayString())],
            ['ids' => Connection::PARAM_INT_ARRAY]
        )->fetchFirstColumn();
    }
}
