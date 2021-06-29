<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface HasUpToDateEvaluationQueryInterface
{
    public function forProductId(ProductId $productId): bool;

    /**
     * @param  ProductId[] $productIds
     *
     * @return ProductId[] List of the ids of the products that have an up-to-date evaluation.
     */
    public function forProductIds(array $productIds): array;
}
