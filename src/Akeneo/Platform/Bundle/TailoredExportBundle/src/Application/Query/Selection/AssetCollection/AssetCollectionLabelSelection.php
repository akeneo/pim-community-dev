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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection;

final class AssetCollectionLabelSelection implements AssetCollectionSelectionInterface
{
    public const TYPE = 'label';

    private string $separator;
    private string $locale;
    private string $assetFamilyCode;

    public function __construct(
        string $separator,
        string $locale,
        string $assetFamilyCode
    ) {
        $this->separator = $separator;
        $this->locale = $locale;
        $this->assetFamilyCode = $assetFamilyCode;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getAssetFamilyCode(): string
    {
        return $this->assetFamilyCode;
    }
}
