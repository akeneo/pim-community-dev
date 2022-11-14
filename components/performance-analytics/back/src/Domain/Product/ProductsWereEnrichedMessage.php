<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\Message;
use Webmozart\Assert\Assert;

final class ProductsWereEnrichedMessage implements Message
{
    /**
     * @param array<ProductWasEnriched> $productIsEnrichedCollection
     */
    private function __construct(
        private array $productIsEnrichedCollection
    ) {
        Assert::allIsInstanceOf($this->productIsEnrichedCollection, ProductWasEnriched::class);
    }

    /**
     * @param array<ProductWasEnriched> $productIsEnrichedCollection
     */
    public static function fromCollection(array $productIsEnrichedCollection): ProductsWereEnrichedMessage
    {
        return new ProductsWereEnrichedMessage($productIsEnrichedCollection);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function normalize(): array
    {
        return \array_map(fn (ProductWasEnriched $productWasEnriched) => $productWasEnriched->normalize(), $this->productIsEnrichedCollection);
    }
}
