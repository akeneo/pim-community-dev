<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
