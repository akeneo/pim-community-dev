<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpToDateCriteriaEvaluationsByEntityIdQuery implements GetCriteriaEvaluationsByEntityIdQueryInterface
{
    public function __construct(
        private GetCriteriaEvaluationsByEntityIdQueryInterface $getLatestCriteriaEvaluationsByEntityIdQuery,
        private HasUpToDateEvaluationQueryInterface            $hasUpToDateEvaluationQuery
    ) {
    }

    public function execute(ProductEntityIdInterface $entityId): Read\CriterionEvaluationCollection
    {
        if (false === $this->hasUpToDateEvaluationQuery->forEntityId($entityId)) {
            return new Read\CriterionEvaluationCollection();
        }

        return $this->getLatestCriteriaEvaluationsByEntityIdQuery->execute($entityId);
    }
}
