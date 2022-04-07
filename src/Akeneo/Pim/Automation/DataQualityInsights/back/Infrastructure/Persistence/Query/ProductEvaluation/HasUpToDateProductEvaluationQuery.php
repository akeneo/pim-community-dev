<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HasUpToDateProductEvaluationQuery implements HasUpToDateEvaluationQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function forProductId(ProductEntityIdInterface $productId): bool
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
SELECT product.id
FROM pim_catalog_product AS product
LEFT JOIN pim_catalog_product_model AS parent ON parent.id = product.product_model_id
LEFT JOIN pim_catalog_product_model AS grand_parent ON grand_parent.id = parent.parent_id
WHERE product.id IN (:product_ids)
    AND EXISTS(
        SELECT 1 FROM pim_data_quality_insights_product_criteria_evaluation AS evaluation
        WHERE evaluation.product_id = product.id
        AND evaluation.evaluated_at >=
            IF(grand_parent.updated > parent.updated AND grand_parent.updated > product.updated, grand_parent.updated,
                IF(parent.updated > product.updated, parent.updated, product.updated)
            )
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
