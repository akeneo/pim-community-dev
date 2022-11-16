<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Application\Command;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class NotifyProductsAreEnriched
{
    /**
     * @param array<ProductIsEnriched> $productsAreEnriched
     */
    public function __construct(
        private array $productsAreEnriched,
    ) {
        Assert::allIsInstanceOf($this->productsAreEnriched, ProductIsEnriched::class);
    }

    /**
     * @return array<ProductIsEnriched>
     */
    public function getProductsAreEnriched(): array
    {
        return $this->productsAreEnriched;
    }

    /**
     * @return array<UuidInterface>
     */
    public function getProductUuids(): array
    {
        return \array_map(fn (ProductIsEnriched $productIsEnriched) => $productIsEnriched->productUuid(), $this->productsAreEnriched);
    }
}
