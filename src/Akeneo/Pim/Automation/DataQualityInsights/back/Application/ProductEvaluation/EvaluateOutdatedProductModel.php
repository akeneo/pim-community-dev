<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Evaluate the pending criteria of a product model if it has an outdated evaluation.
 */
class EvaluateOutdatedProductModel
{
    public function __construct(
        private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        private EvaluateProductModels $evaluateProductModels,
    ) {
    }

    public function __invoke(ProductId $productModelId): void
    {
        if (false === $this->hasUpToDateEvaluationQuery->forProductId($productModelId)) {
            ($this->evaluateProductModels)([$productModelId->toInt()]);
        }
    }
}
