<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Event;

use Webmozart\Assert\Assert;

class ProductModelsWereCreatedOrUpdated
{
    /**
     * @param (ProductModelWasCreated|ProductModelWasUpdated)[] $productModelEvent
     */
    public function __construct(
        public readonly array $events,
    )
    {
        Assert::notEmpty($this->events);
        Assert::allIsInstanceOfAny($this->events, [ProductModelWasCreated::class, ProductModelWasUpdated::class]);
    }
}
