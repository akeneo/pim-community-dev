<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetAssetValue implements ValueUserIntent
{
    /**
     * @param array<string> $assetCodes
     */
    public function __construct(
        private string $attributeCode,
        private ?string $channelCode,
        private ?string $localeCode,
        private array $assetCodes,
    ) {
        Assert::notEmpty($assetCodes);
        Assert::allStringNotEmpty($assetCodes);
    }

    /**
     * @return array<string>
     */
    public function assetCodes(): array
    {
        return $this->assetCodes;
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
