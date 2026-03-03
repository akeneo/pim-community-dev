<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetImageValue implements ValueUserIntent
{
    public function __construct(
        private string $attributeCode,
        private ?string $channelCode,
        private ?string $localeCode,
        private string $imagePath
    ) {
        Assert::stringNotEmpty($this->imagePath);
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }

    public function value(): string
    {
        return $this->imagePath;
    }
}
