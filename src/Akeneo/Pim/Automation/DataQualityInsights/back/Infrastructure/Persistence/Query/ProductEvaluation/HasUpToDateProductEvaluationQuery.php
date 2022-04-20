<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HasUpToDateProductEvaluationQuery implements HasUpToDateEvaluationQueryInterface
{
    public function __construct(private Connection $dbConnection, private ProductEntityIdFactoryInterface $idFactory)
    {
    }

    public function forProductId(ProductEntityIdInterface $productId): bool
    {
        $productIdCollection = $this->idFactory->createCollection([(string)$productId]);
        $upToDateProducts = $this->forProductIdCollection($productIdCollection);

        return !is_null($upToDateProducts);
    }

    public function forProductIdCollection(ProductEntityIdCollection $productIdCollection): ?ProductEntityIdCollection
    {
        if ($productIdCollection->isEmpty()) {
            return null;
        }

        $query = <<<SQL
SELECT product.id
FROM pim_catalog_product AS product
LEFT JOIN pim_catalog_product_model AS parent ON parent.id = product.product_model_id
LEFT JOIN pim_catalog_product_model AS grand_parent ON grand_parent.id = parent.parent_id
WHERE product.id IN (:product_ids)
    AND EXISTS(
        SELECT 1 FROM pim_data_quality_insights_product_criteria_evaluation AS evaluation
        WHERE evaluation.product_uuid = product.uuid
        AND evaluation.evaluated_at >=
            IF(grand_parent.updated > parent.updated AND grand_parent.updated > product.updated, grand_parent.updated,
                IF(parent.updated > product.updated, parent.updated, product.updated)
            )
    )
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_ids' => $productIdCollection->toArrayString()],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );

        $result = $stmt->fetchAllAssociative();

        if (!is_array($result)) {
            return null;
        }

        $ids = array_map(function ($resultRow) {
            return $resultRow['id'];
        }, $result);

        if (empty($ids)) {
            return null;
        }

        return $this->idFactory->createCollection($ids);
    }
}
