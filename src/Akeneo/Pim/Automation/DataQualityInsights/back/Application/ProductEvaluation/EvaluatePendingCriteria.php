<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluatePendingCriteria
{
    public const NO_LIMIT = -1;

    public function __construct(
        private CriterionEvaluationRepositoryInterface                 $repository,
        private CriteriaEvaluationRegistry                             $evaluationRegistry,
        private CriteriaApplicabilityRegistry                          $applicabilityRegistry,
        private GetPendingCriteriaEvaluationsByEntityIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        private GetEvaluableProductValuesQueryInterface                $getEvaluableProductValuesQuery,
        private SynchronousCriterionEvaluationsFilterInterface         $synchronousCriterionEvaluationsFilter,
        private LoggerInterface                                        $logger,
        private ProductEntityIdFactoryInterface                        $idFactory
    ) {
    }

    public function evaluateAllCriteria(ProductEntityIdCollection $productIdCollection): void
    {
        $productsCriteriaEvaluations = $this->getPendingCriteriaEvaluationsQuery->execute($productIdCollection);
        foreach ($productsCriteriaEvaluations as $productId => $productCriteria) {
            $productValues = $this->getEvaluableProductValuesQuery->byProductId($this->idFactory->create((string) $productId));
            foreach ($productCriteria as $productCriterion) {
                $this->evaluateCriterion($productCriterion, $productValues);
            }
            $this->repository->update($productCriteria);
        }
    }

    public function evaluateSynchronousCriteria(ProductEntityIdCollection $productIds): void
    {
        $productsCriteriaEvaluations = $this->getPendingCriteriaEvaluationsQuery->execute($productIds);
        foreach ($productsCriteriaEvaluations as $productId => $productCriteria) {
            $productValues = $this->getEvaluableProductValuesQuery->byProductId($this->idFactory->create((string) $productId));

            $synchronousCriteria = $this->synchronousCriterionEvaluationsFilter->filter($productCriteria->getIterator());
            foreach ($synchronousCriteria as $synchronousCriterion) {
                $this->evaluateCriterion($synchronousCriterion, $productValues);
            }

            $asynchronousCriteria = new AsynchronousCriterionEvaluationsFilterIterator($productCriteria->getIterator());
            foreach ($asynchronousCriteria as $asynchronousCriterion) {
                $this->evaluateCriterionApplicability($asynchronousCriterion, $productValues);
            }

            $this->repository->update($productCriteria);
        }
    }

    private function evaluateCriterion(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): void
    {
        try {
            $evaluationService = $this->evaluationRegistry->get($criterionEvaluation->getCriterionCode());
            $criterionEvaluation->start();
            $result = $evaluationService->evaluate($criterionEvaluation, $productValues);
            $criterionEvaluation->end($result);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Failed to evaluate criterion {criterion_code} for product id {product_id}',
                ['criterion_code' => $criterionEvaluation->getCriterionCode(), 'product_id' => $criterionEvaluation->getEntityId(), 'message' => $exception->getMessage()]
            );
            $criterionEvaluation->flagAsError();
        }
    }

    private function evaluateCriterionApplicability(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): void
    {
        $applicabilityService = $this->applicabilityRegistry->get($criterionEvaluation->getCriterionCode());

        if (null !== $applicabilityService) {
            $criterionEvaluationApplicability = $applicabilityService->evaluateApplicability($productValues);
            $criterionEvaluation->applicabilityEvaluated($criterionEvaluationApplicability);
        }
    }
}
