<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection;

final class AssetCollectionMediaLinkSelection implements AssetCollectionSelectionInterface
{
    public const TYPE = 'media_link';

    public function __construct(
        private string $separator,
        private ?string $channel,
        private ?string $locale,
        private string $assetFamilyCode,
        private string $attributeCode,
        private bool $withPrefixAndSuffix,
    ) {
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getAssetFamilyCode(): string
    {
        return $this->assetFamilyCode;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    public function withPrefixAndSuffix(): bool
    {
        return $this->withPrefixAndSuffix;
    }
}
