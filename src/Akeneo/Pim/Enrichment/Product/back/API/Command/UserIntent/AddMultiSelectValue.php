<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddMultiSelectValue implements ValueUserIntent
{
    /**
     * @param array<string> $optionCodes
     */
    public function __construct(
        private string $attributeCode,
        private ?string $channelCode,
        private ?string $localeCode,
        private array $optionCodes
    ) {
        Assert::notEmpty($optionCodes);
        Assert::allStringNotEmpty($optionCodes);
    }

    /**
     * @return array<string>
     */
    public function optionCodes(): array
    {
        return $this->optionCodes;
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }
}
