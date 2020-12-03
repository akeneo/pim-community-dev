<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class GetUpToDateProductEvaluationQuery implements GetProductEvaluationQueryInterface
{
    private GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery;

    private GetLatestProductScoresQueryInterface $getLatestProductScoresQuery;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLatestProductScoresQueryInterface $getLatestProductScoresQuery
    ) {
        $this->getCriteriaEvaluationsByProductIdQuery = $getCriteriaEvaluationsByProductIdQuery;
        $this->getLatestProductScoresQuery = $getLatestProductScoresQuery;
    }

    public function execute(ProductId $productId): ProductEvaluation
    {
        $productScores = $this->getLatestProductScoresQuery->byProductId($productId);
        $productCriteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);

        return new ProductEvaluation($productId, $productScores, $productCriteriaEvaluations);
    }
}
