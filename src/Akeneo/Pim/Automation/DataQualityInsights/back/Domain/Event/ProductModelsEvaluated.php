<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Event;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelsEvaluated
{
    public function __construct(
        private ProductEntityIdCollection $productModelIdCollection
    ) {
    }

    public function getProductModelIds(): ProductEntityIdCollection
    {
        return $this->productModelIdCollection;
    }
}
