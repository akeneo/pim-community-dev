<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Event;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsEvaluated extends Event
{
    public function __construct(
        private ProductEntityIdCollection $productIdCollection
    ) {
    }

    public function getProductIds(): ProductEntityIdCollection
    {
        return $this->productIdCollection;
    }
}
