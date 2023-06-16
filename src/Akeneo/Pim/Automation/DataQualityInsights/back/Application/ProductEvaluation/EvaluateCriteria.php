<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateCriteria
{
    public function __construct(
        private CriteriaEvaluationRegistry $evaluationRegistry,
        private GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        private CriteriaByFeatureRegistry $criteriaByFeatureRegistry,
        private CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param CriterionCode[] $productCriterionCodes If empty all criteria are evaluated
     * @return void
     */
    public function forEntityIds(ProductEntityIdCollection $entityIdCollection, array $productCriterionCodes): void
    {
        if ([] === $productCriterionCodes) {
            $productCriterionCodes = $this->criteriaByFeatureRegistry->getAllCriterionCodes();
        }

        foreach ($entityIdCollection as $entityId) {
            $entityValues = $this->getEvaluableProductValuesQuery->byProductId($entityId);

            $criteriaEvaluationCollection = new CriterionEvaluationCollection();
            foreach ($productCriterionCodes as $productCriterionCode) {
                $productCriterion = new CriterionEvaluation(
                    $productCriterionCode,
                    $entityId,
                    CriterionEvaluationStatus::pending()
                );
                $this->evaluateCriterion($productCriterion, $entityValues);
                $criteriaEvaluationCollection->add($productCriterion);
            }

            // Updating the evaluation is needed to have the exact datetime of the evaluation.
            // So the GetOutdatedXXXUuidsByDateAndCriteriaQuery can work next time we have to launch evaluations
            $this->criterionEvaluationRepository->update($criteriaEvaluationCollection);
        }
    }

    private function evaluateCriterion(CriterionEvaluation $criterionEvaluation, ProductValuesCollection $entityValues): void
    {
        try {
            $evaluationService = $this->evaluationRegistry->get($criterionEvaluation->getCriterionCode());
            $criterionEvaluation->start();
            $result = $evaluationService->evaluate($criterionEvaluation, $entityValues);
            $criterionEvaluation->end($result);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Failed to evaluate criterion {criterion_code} for product id {product_id}',
                ['criterion_code' => $criterionEvaluation->getCriterionCode(), 'product_id' => $criterionEvaluation->getEntityId(), 'message' => $exception->getMessage()]
            );
            $criterionEvaluation->flagAsError();
        }
    }
}
