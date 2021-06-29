<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpToDateCriteriaEvaluationsByProductIdQuery implements GetCriteriaEvaluationsByProductIdQueryInterface
{
    /** @var GetCriteriaEvaluationsByProductIdQueryInterface */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    /** @var HasUpToDateEvaluationQueryInterface */
    private $hasUpToDateEvaluationQuery;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
    }

    public function execute(ProductId $productId): Read\CriterionEvaluationCollection
    {
        if (false === $this->hasUpToDateEvaluationQuery->forProductId($productId)) {
            return new Read\CriterionEvaluationCollection();
        }

        return $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);
    }
}
