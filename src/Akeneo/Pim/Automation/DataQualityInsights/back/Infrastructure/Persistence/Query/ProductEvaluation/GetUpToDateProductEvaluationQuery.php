<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class GetUpToDateProductEvaluationQuery implements GetProductEvaluationQueryInterface
{
    public function __construct(
        private GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        private GetProductScoresQueryInterface                  $getProductScoresQuery,
        private GetScoresByCriteriaStrategy                     $getScoresByCriteria,
    ) {
    }

    public function execute(ProductId $productId): ProductEvaluation
    {
        $productScores = ($this->getScoresByCriteria)($this->getProductScoresQuery->byProductId($productId));
        $productCriteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);

        return new ProductEvaluation($productId, $productScores, $productCriteriaEvaluations);
    }
}
