<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Event;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWasUpdated
{
    public function __construct(private string $productIdentifier)
    {
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }
}
