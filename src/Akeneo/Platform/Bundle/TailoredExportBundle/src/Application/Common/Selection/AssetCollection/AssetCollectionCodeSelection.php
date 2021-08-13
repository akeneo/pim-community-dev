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

final class AssetCollectionCodeSelection implements AssetCollectionSelectionInterface
{
    public const TYPE = 'code';

    private string $separator;
    private string $assetFamilyCode;
    private string $attributeCode;

    public function __construct(
        string $separator,
        string $assetFamilyCode,
        string $attributeCode
    ) {
        $this->separator = $separator;
        $this->assetFamilyCode = $assetFamilyCode;
        $this->attributeCode = $attributeCode;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getAssetFamilyCode(): string
    {
        return $this->assetFamilyCode;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
