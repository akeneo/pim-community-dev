<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsCompletenessWereChanged
{
    /**
     * @param array<ProductCompletenessWasChanged> $productsCompletenessWereChanged
     */
    public function __construct(
        private array $productsCompletenessWereChanged
    ) {
        Assert::notSame($this->productsCompletenessWereChanged, []);
        Assert::allIsInstanceOf($this->productsCompletenessWereChanged, ProductCompletenessWasChanged::class);
    }

    /**
     * @return array<ProductCompletenessWasChanged>
     */
    public function all(): array
    {
        return $this->productsCompletenessWereChanged;
    }
}
