<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCriteriaEvaluationsByProductModelIdQuery implements GetCriteriaEvaluationsByProductIdQueryInterface
{


    public function __construct(
        private Connection                            $db,
        private Clock                                 $clock,
        private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds,
    ) {
    }


    public function execute(ProductEntityIdInterface $productId): Read\CriterionEvaluationCollection
    {
        if (!($productId instanceof ProductModelId)) {
            throw new \InvalidArgumentException("GetCriteriaEvaluationsByProductModelIdQuery must be used with ProductModelId");
        }

        $sql = <<<SQL
SELECT
       evaluation.product_id,
       evaluation.criterion_code,
       evaluation.status,
       evaluation.evaluated_at,
       evaluation.result
FROM pim_data_quality_insights_product_model_criteria_evaluation AS evaluation
WHERE evaluation.product_id = :product_id
SQL;

        $rows = $this->db->executeQuery(
            $sql,
            ['product_id' => $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        )->fetchAllAssociative();

        $criteriaEvaluations = new Read\CriterionEvaluationCollection();
        foreach ($rows as $rawCriterionEvaluation) {
            $criterionCode = new CriterionCode($rawCriterionEvaluation['criterion_code']);
            $criteriaEvaluations->add(new Read\CriterionEvaluation(
                $criterionCode,
                $productId,
                null !== $rawCriterionEvaluation['evaluated_at'] ? $this->clock->fromString($rawCriterionEvaluation['evaluated_at']) : null,
                new CriterionEvaluationStatus($rawCriterionEvaluation['status']),
                $this->hydrateCriterionEvaluationResult($criterionCode, $rawCriterionEvaluation['result']),
            ));
        }

        return $criteriaEvaluations;
    }

    private function hydrateCriterionEvaluationResult(CriterionCode $criterionCode, $rawResult): ?Read\CriterionEvaluationResult
    {
        if (null === $rawResult) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, $rawResult);

        return Read\CriterionEvaluationResult::fromArray($rawResult);
    }
}
