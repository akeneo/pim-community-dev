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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\CriterionNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Psr\Log\LoggerInterface;

class EvaluatePendingCriteria
{
    public const NO_LIMIT = -1;

    /** @var CriterionEvaluationRepositoryInterface */
    private $repository;

    /** @var CriteriaEvaluationRegistry */
    private $registry;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $registry,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    public function evaluateAllCriteria(array $productIds): void
    {
        $criterionEvaluations = $this->repository->findPendingByProductIds($productIds);
        foreach ($criterionEvaluations as $criterionEvaluation) {
            $this->evaluateCriterion($criterionEvaluation);
        }
    }

    public function evaluateSynchronousCriteria(array $productIds): void
    {
        $criterionEvaluations = $this->repository->findPendingByProductIds($productIds);
        $synchronousCriterionEvaluations = new SynchronousCriterionEvaluationsFilterIterator(new \ArrayIterator($criterionEvaluations));
        foreach ($synchronousCriterionEvaluations as $criterionEvaluation) {
            $this->evaluateCriterion($criterionEvaluation);
        }
    }

    private function evaluateCriterion(CriterionEvaluation $criterionEvaluation): void
    {
        try {
            $evaluationService = $this->registry->get($criterionEvaluation->getCriterionCode());
            $criterionEvaluation->start();
        } catch (CriterionNotFoundException $e) {
            $criterionEvaluation->flagAsError();
            $this->repository->update($criterionEvaluation);

            return;
        }

        $this->repository->update($criterionEvaluation);

        try {
            $result = $evaluationService->evaluate($criterionEvaluation);
            $criterionEvaluation->end($result);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Failed to evaluate criterion {criterion_code} for product id {product_id}',
                ['criterion_code' => $criterionEvaluation->getCriterionCode(), 'product_id' => $criterionEvaluation->getProductId(), 'message' => $exception->getMessage()]
            );
            $criterionEvaluation->flagAsError();
        }
        $this->repository->update($criterionEvaluation);
    }
}
