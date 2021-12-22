<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductsIndex;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProducts
{
    public function __construct(
        private EvaluatePendingCriteria $evaluatePendingProductCriteria,
        private ConsolidateProductScores $consolidateProductScores,
        private UpdateProductsIndex $updateProductsIndex
    ) {
    }

    public function __invoke(array $productIds): void
    {
        $this->evaluatePendingProductCriteria->evaluateAllCriteria($productIds);
        $this->consolidateProductScores->consolidate($productIds);
        $this->updateProductsIndex->execute($productIds);
    }
}
