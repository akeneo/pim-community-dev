<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductWasCompletedOnChannelLocaleCollection
{
    /**
     * @param array<ProductWasCompletedOnChannelLocale> $productWasCompletedOnChannelLocaleCollection
     */
    public function __construct(
        private array $productWasCompletedOnChannelLocaleCollection
    ) {
        Assert::notSame($this->productWasCompletedOnChannelLocaleCollection, []);
        Assert::allIsInstanceOf($this->productWasCompletedOnChannelLocaleCollection, ProductWasCompletedOnChannelLocale::class);
    }

    /**
     * @return array<ProductWasCompletedOnChannelLocale>
     */
    public function all(): array
    {
        return $this->productWasCompletedOnChannelLocaleCollection;
    }
}
