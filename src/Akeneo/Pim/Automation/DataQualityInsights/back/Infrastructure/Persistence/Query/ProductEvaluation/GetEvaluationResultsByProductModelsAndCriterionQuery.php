<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetEvaluationResultsByProductModelsAndCriterionQuery implements GetEvaluationResultsByProductsAndCriterionQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductEntityIdCollection $productModelIdCollection, CriterionCode $criterionCode): array
    {
        Assert::isInstanceOf($productModelIdCollection, ProductModelIdCollection::class);

        $query = <<<SQL
SELECT product_id, result
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:productModelIds) AND criterion_code = :criterionCode;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            [
                'productModelIds' => array_map(fn (string $productModelId) => (int) $productModelId, $productModelIdCollection->toArrayString()),
                'criterionCode' => $criterionCode,
            ],
            [
                'productModelIds' => Connection::PARAM_INT_ARRAY,
            ]
        );

        $evaluationResults = [];
        while ($evaluation = $stmt->fetchAssociative()) {
            $evaluationResults[(string) $evaluation['product_id']] = $this->hydrateEvaluationResult($criterionCode, $evaluation['result']);
        }

        return $evaluationResults;
    }

    private function hydrateEvaluationResult(CriterionCode $criterionCode, ?string $rawResult): ?Read\CriterionEvaluationResult
    {
        if (null === $rawResult) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, $rawResult);

        return Read\CriterionEvaluationResult::fromArray($rawResult);
    }
}
