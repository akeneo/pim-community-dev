<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductModelScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsolidateProductModelScores
{
    public function __construct(
        private GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsQuery,
        private ComputeScores                                  $computeScores,
        private ProductModelScoreRepositoryInterface           $productModelScoreRepository,
        private Clock                                          $clock
    ) {
    }

    public function consolidate(ProductEntityIdCollection $productModelIdCollection): void
    {
        $productModelScores = [];
        foreach ($productModelIdCollection as $productModelId) {
            $criteriaEvaluations = $this->getCriteriaEvaluationsQuery->execute($productModelId);
            $scores = $this->computeScores->fromCriteriaEvaluations($criteriaEvaluations);
            $productModelScores[] = new ProductScores($productModelId, $this->clock->getCurrentTime(), $scores);
        }

        if (!empty($productModelScores)) {
            $this->productModelScoreRepository->saveAll($productModelScores);
        }
    }
}
