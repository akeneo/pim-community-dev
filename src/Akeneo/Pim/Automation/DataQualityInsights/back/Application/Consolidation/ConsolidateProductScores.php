<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsolidateProductScores
{
    public function __construct(
        private GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsQuery,
        private ComputeScores $computeScores,
        private ProductScoreRepositoryInterface $productScoreRepository,
        private Clock $clock
    ) {
    }

    public function consolidate(ProductEntityIdCollection $productIdCollection): void
    {
        $productsScores = [];
        foreach ($productIdCollection as $productId) {
            $criteriaEvaluations = $this->getCriteriaEvaluationsQuery->execute($productId);
            $scores = $this->computeScores->fromCriteriaEvaluations($criteriaEvaluations);
            $productsScores[] = new ProductScores($productId, $this->clock->getCurrentTime(), $scores);
        }

        if (!empty($productsScores)) {
            $this->productScoreRepository->saveAll($productsScores);
        }
    }
}
