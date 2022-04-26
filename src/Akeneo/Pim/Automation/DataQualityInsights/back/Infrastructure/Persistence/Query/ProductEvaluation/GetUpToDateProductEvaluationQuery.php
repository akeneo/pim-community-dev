<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;

final class GetUpToDateProductEvaluationQuery implements GetProductEvaluationQueryInterface
{
    public function __construct(
        private GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        private GetProductScoresQueryInterface                  $getProductScoresQuery,
        private GetScoresByCriteriaStrategy                     $getScoresByCriteria,
    ) {
    }

    public function execute(ProductUuid $productUuid): ProductEvaluation
    {
        $productScores = ($this->getScoresByCriteria)($this->getProductScoresQuery->byProductUuid($productUuid));
        $productCriteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productUuid);

        return new ProductEvaluation($productUuid, $productScores, $productCriteriaEvaluations);
    }
}
