<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsWereCreatedOrUpdated
{
    /**
     * @param (ProductWasCreated|ProductWasUpdated)[] $events
     */
    public function __construct(
        public readonly array $events,
    ) {
        Assert::notEmpty($this->events);
        Assert::allIsInstanceOfAny($this->events, [ProductWasCreated::class, ProductWasUpdated::class]);
    }
}
