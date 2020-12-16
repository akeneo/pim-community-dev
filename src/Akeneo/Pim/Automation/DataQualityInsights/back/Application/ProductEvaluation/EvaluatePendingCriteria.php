<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluatePendingCriteria
{
    public const NO_LIMIT = -1;

    /** @var CriterionEvaluationRepositoryInterface */
    private $repository;

    /** @var CriteriaEvaluationRegistry */
    private $evaluationRegistry;

    /** @var GetPendingCriteriaEvaluationsByProductIdsQueryInterface */
    private $getPendingCriteriaEvaluationsQuery;

    /** @var LoggerInterface */
    private $logger;

    /** @var GetEvaluableProductValuesQueryInterface */
    private $getEvaluableProductValuesQuery;

    /** @var CriteriaApplicabilityRegistry */
    private $applicabilityRegistry;

    /** @var SynchronousCriterionEvaluationsFilterInterface */
    private $synchronousCriterionEvaluationsFilter;

    public function __construct(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        CriteriaApplicabilityRegistry $applicabilityRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        SynchronousCriterionEvaluationsFilterInterface $synchronousCriterionEvaluationsFilter,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->evaluationRegistry = $evaluationRegistry;
        $this->getPendingCriteriaEvaluationsQuery = $getPendingCriteriaEvaluationsQuery;
        $this->logger = $logger;
        $this->getEvaluableProductValuesQuery = $getEvaluableProductValuesQuery;
        $this->applicabilityRegistry = $applicabilityRegistry;
        $this->synchronousCriterionEvaluationsFilter = $synchronousCriterionEvaluationsFilter;
    }

    public function evaluateAllCriteria(array $productIds): void
    {
        $productsCriteriaEvaluations = $this->getPendingCriteriaEvaluationsQuery->execute($productIds);
        foreach ($productsCriteriaEvaluations as $productId => $productCriteria) {
            $productValues = $this->getEvaluableProductValuesQuery->byProductId(new ProductId($productId));
            foreach ($productCriteria as $productCriterion) {
                $this->evaluateCriterion($productCriterion, $productValues);
            }
            $this->repository->update($productCriteria);
        }
    }

    public function evaluateSynchronousCriteria(array $productIds): void
    {
        $productsCriteriaEvaluations = $this->getPendingCriteriaEvaluationsQuery->execute($productIds);
        foreach ($productsCriteriaEvaluations as $productId => $productCriteria) {
            $productValues = $this->getEvaluableProductValuesQuery->byProductId(new ProductId($productId));

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
                ['criterion_code' => $criterionEvaluation->getCriterionCode(), 'product_id' => $criterionEvaluation->getProductId(), 'message' => $exception->getMessage()]
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
