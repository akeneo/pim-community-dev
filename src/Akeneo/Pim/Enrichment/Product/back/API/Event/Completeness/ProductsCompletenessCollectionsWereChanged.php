<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsCompletenessCollectionsWereChanged
{
    /**
     * @param array<ProductCompletenessCollectionWasChanged> $productsCompletenessCollections
     */
    public function __construct(
        private array $productsCompletenessCollections
    ) {
        Assert::notSame($this->productsCompletenessCollections, []);
        Assert::allIsInstanceOf($this->productsCompletenessCollections, ProductCompletenessCollectionWasChanged::class);
    }

    /**
     * @return array<ProductCompletenessCollectionWasChanged>
     */
    public function productsCompletenessCollections(): array
    {
        return $this->productsCompletenessCollections;
    }
}
