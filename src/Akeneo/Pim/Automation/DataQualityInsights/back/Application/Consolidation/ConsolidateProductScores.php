<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsolidateProductScores
{
    private GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsQuery;

    private ComputeProductScores $computeProductScores;

    private ProductScoreRepositoryInterface $productScoreRepository;

    private Clock $clock;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsQuery,
        ComputeProductScores $computeProductScores,
        ProductScoreRepositoryInterface $productScoreRepository,
        Clock $clock
    ) {
        $this->getCriteriaEvaluationsQuery = $getCriteriaEvaluationsQuery;
        $this->computeProductScores = $computeProductScores;
        $this->productScoreRepository = $productScoreRepository;
        $this->clock = $clock;
    }

    public function consolidate(array $productIds): void
    {
        $productsScores = [];
        foreach ($productIds as $productId) {
            $productId = new ProductId($productId);
            $criteriaEvaluations = $this->getCriteriaEvaluationsQuery->execute($productId);
            $scores = $this->computeProductScores->fromCriteriaEvaluations($criteriaEvaluations);
            $productsScores[] = new ProductScores($productId, $this->clock->getCurrentTime(), $scores);
        }

        if (!empty($scores)) {
            $this->productScoreRepository->saveAll($productsScores);
        }
    }
}
