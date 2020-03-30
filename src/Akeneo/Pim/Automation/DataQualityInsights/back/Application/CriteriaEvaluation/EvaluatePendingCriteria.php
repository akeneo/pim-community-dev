<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetPendingCriteriaEvaluationsByProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Psr\Log\LoggerInterface;

class EvaluatePendingCriteria
{
    public const NO_LIMIT = -1;

    /** @var CriterionEvaluationRepositoryInterface */
    private $repository;

    /** @var CriteriaEvaluationRegistry */
    private $registry;

    /** @var GetPendingCriteriaEvaluationsByProductIdsQueryInterface */
    private $getPendingCriteriaEvaluationsQuery;

    /** @var LoggerInterface */
    private $logger;

    /** @var GetEvaluableProductValuesQueryInterface */
    private $getEvaluableProductValuesQuery;

    public function __construct(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $registry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->registry = $registry;
        $this->getPendingCriteriaEvaluationsQuery = $getPendingCriteriaEvaluationsQuery;
        $this->logger = $logger;
        $this->getEvaluableProductValuesQuery = $getEvaluableProductValuesQuery;
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
            $evaluatedCriteria = new Write\CriterionEvaluationCollection();
            $synchronousCriteria = new SynchronousCriterionEvaluationsFilterIterator($productCriteria->getIterator());
            foreach ($synchronousCriteria as $synchronousCriterion) {
                $this->evaluateCriterion($synchronousCriterion, $productValues);
                $evaluatedCriteria->add($synchronousCriterion);
            }
            $this->repository->update($evaluatedCriteria);
        }
    }

    private function evaluateCriterion(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): void
    {
        try {
            $evaluationService = $this->registry->get($criterionEvaluation->getCriterionCode());
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
}
