<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\FilterPartialCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsolidateProductScores
{
    public function __construct(
        private GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsQuery,
        private ComputeScores $computeScores,
        private ProductScoreRepositoryInterface $productScoreRepository,
        private Clock $clock,
        private FilterPartialCriteriaEvaluations $filterPartialCriteriaEvaluations,
    ) {
    }

    public function consolidate(ProductEntityIdCollection $productIdCollection): void
    {
        $productsScores = [];
        foreach ($productIdCollection as $productId) {
            $criteriaEvaluations = $this->getCriteriaEvaluationsQuery->execute($productId);
            $partialCriteriaEvaluations = ($this->filterPartialCriteriaEvaluations)($criteriaEvaluations);

            $scores = $this->computeScores->fromCriteriaEvaluations($criteriaEvaluations);
            $scoresPartialCriteria = $this->computeScores->fromCriteriaEvaluations($partialCriteriaEvaluations);

            $productsScores[] = new ProductScores($productId, $this->clock->getCurrentTime(), $scores, $scoresPartialCriteria);
        }

        if (!empty($productsScores)) {
            $this->productScoreRepository->saveAll($productsScores);
        }
    }
}
