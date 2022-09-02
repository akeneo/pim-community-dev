<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationRatesByProductModelsAndCriterionQuery implements GetEvaluationRatesByProductsAndCriterionQueryInterface
{
    public function __construct(
        private Connection                            $dbConnection,
        private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds
    ) {
    }

    public function execute(ProductEntityIdCollection $productIdCollection, CriterionCode $criterionCode): array
    {
        $ratesPath = sprintf('$."%s"', TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates']);

        $query = <<<SQL
SELECT product_id, JSON_EXTRACT(result, '$ratesPath') AS rates
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:productIds) AND criterion_code = :criterionCode;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            [
                'productIds' => $productIdCollection->toArrayString(),
                'criterionCode' => $criterionCode,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
            ]
        );

        $evaluationRates = [];
        while ($evaluationResult = $stmt->fetchAssociative()) {
            $evaluationRates[$evaluationResult['product_id']] = $this->formatEvaluationRates($criterionCode, $evaluationResult);
        }

        return $evaluationRates;
    }

    private function formatEvaluationRates(CriterionCode $criterionCode, array $evaluationResult): array
    {
        if (!isset($evaluationResult['rates'])) {
            return [];
        }

        $rates = json_decode($evaluationResult['rates'], true, 512, JSON_THROW_ON_ERROR);
        $rates = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, [TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => $rates]);

        return $rates['rates'] ?? [];
    }
}
