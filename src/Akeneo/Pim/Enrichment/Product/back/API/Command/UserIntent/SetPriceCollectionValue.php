<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetPriceCollectionValue implements ValueUserIntent
{
    /**
     * @param PriceValue[] $priceValues
     */
    public function __construct(
        private string $attributeCode,
        private ?string $channelCode,
        private ?string $localeCode,
        private array $priceValues = []
    ) {
        Assert::allIsInstanceOf($this->priceValues, PriceValue::class);
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    /**
     * @return PriceValue[]
     */
    public function priceValues(): array
    {
        return $this->priceValues;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }
}
