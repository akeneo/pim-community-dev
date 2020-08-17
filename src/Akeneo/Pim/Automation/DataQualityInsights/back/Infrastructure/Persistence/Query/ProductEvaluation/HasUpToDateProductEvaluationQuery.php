<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class HasUpToDateProductEvaluationQuery implements HasUpToDateEvaluationQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function forProductId(ProductId $productId): bool
    {
        $upToDateProducts = $this->forProductIds([$productId]);

        return !empty($upToDateProducts);
    }

    public function forProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $productIds = array_map(function (ProductId $productId) {
            return $productId->toInt();
        }, $productIds);

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
            ['product_ids' => $productIds],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($result)) {
            return [];
        }

        return array_map(function ($resultRow) {
            return new ProductId(intval($resultRow['id']));
        }, $result);
    }
}
