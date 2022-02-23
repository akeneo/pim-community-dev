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

final class AssetCollectionMediaFileSelection implements AssetCollectionSelectionInterface
{
    public const TYPE = 'media_file';
    public const FILE_KEY_PROPERTY = 'file_key';
    public const FILE_PATH_PROPERTY = 'file_path';
    public const ORIGINAL_FILENAME_PROPERTY = 'original_filename';

    public function __construct(
        private string $separator,
        private ?string $channel,
        private ?string $locale,
        private string $assetFamilyCode,
        private string $attributeCode,
        private string $property,
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

    public function getProperty(): string
    {
        return $this->property;
    }
}
