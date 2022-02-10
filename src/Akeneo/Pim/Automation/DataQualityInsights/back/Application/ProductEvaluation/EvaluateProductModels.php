<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProductModels
{
    public function __construct(
        private EvaluatePendingCriteria $evaluatePendingProductModelCriteria
    ) {
    }

    public function __invoke(array $productModelIds): void
    {
        $this->evaluatePendingProductModelCriteria->evaluateAllCriteria($productModelIds);
    }
}
