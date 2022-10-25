<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Event;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsCompletenessWereChangedEvent
{
    /**
     * @param array<string, ProductCompletenessCollection> $changedProductsCompleteness
     */
    public function __construct(
        private array $changedProductsCompleteness
    ) {
        Assert::allIsInstanceOf($this->changedProductsCompleteness, ProductCompletenessCollection::class);
    }

    /**
     * @return array<string, ProductCompletenessCollection>
     */
    public function changedProductsCompleteness(): array
    {
        return $this->changedProductsCompleteness;
    }


}
