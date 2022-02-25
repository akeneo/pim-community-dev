<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HasUpToDateProductModelEvaluationQuery implements HasUpToDateEvaluationQueryInterface
{
    public function __construct(private Connection $dbConnection)
    {
    }

    public function forProductId(ProductId $productId): bool
    {
        $upToDateProducts = $this->forProductIdCollection(ProductIdCollection::fromProductId($productId));
        return !is_null($upToDateProducts);
    }

    public function forProductIdCollection(ProductIdCollection $productIdCollection): ?ProductIdCollection
    {
        if ($productIdCollection->isEmpty()) {
            return null;
        }

        $query = <<<SQL
SELECT product_model.id
FROM pim_catalog_product_model AS product_model
         LEFT JOIN pim_catalog_product_model AS parent ON parent.id = product_model.parent_id
WHERE product_model.id IN (:product_ids)
  AND EXISTS(
        SELECT 1 FROM pim_data_quality_insights_product_model_criteria_evaluation AS evaluation
        WHERE evaluation.product_id = product_model.id
          AND evaluation.evaluated_at >=
              IF(parent.updated > product_model.updated, parent.updated, product_model.updated)
    )
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_ids' => $productIdCollection->toArrayInt()],
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

        return ProductIdCollection::fromStrings($ids);
    }
}
