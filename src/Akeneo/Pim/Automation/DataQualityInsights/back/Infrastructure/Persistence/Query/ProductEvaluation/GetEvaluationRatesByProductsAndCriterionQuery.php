<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationRatesByProductsAndCriterionQuery implements GetEvaluationRatesByProductsAndCriterionQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function toArrayInt(array $productIds, CriterionCode $criterionCode): array
    {
        $query = <<<SQL
SELECT product_id, JSON_EXTRACT(result, '$.rates') AS rates
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id IN (:productIds) AND criterion_code = :criterionCode;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            [
                'productIds' => array_map(fn (ProductId $productId) => $productId->toInt(), $productIds),
                'criterionCode' => $criterionCode,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
            ]
        );

        $evaluationRates = [];
        while ($productSpelling = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $evaluationRates[$productSpelling['product_id']] =
                isset($productSpelling['rates']) ? json_decode($productSpelling['rates'], true, 512, JSON_THROW_ON_ERROR) : [];
        }

        return $evaluationRates;
    }
}
