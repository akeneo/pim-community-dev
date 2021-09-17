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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\AssetCollection;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\FindMediaFileInfoCollectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\MediaFileInfo;

class AssetCollectionMediaFileSelectionApplier implements SelectionApplierInterface
{
    private FindMediaFileInfoCollectionInterface $findMediaFileInfoCollection;

    public function __construct(FindMediaFileInfoCollectionInterface $findMediaFileInfoCollection)
    {
        $this->findMediaFileInfoCollection = $findMediaFileInfoCollection;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof AssetCollectionMediaFileSelection
            || !$value instanceof AssetCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
        }

        $assetCodes = $value->getAssetCodes();
        $assetFamilyCode = $selection->getAssetFamilyCode();
        $mediaFileChannel = $selection->getChannel();
        $mediaFileLocale = $selection->getLocale();
        $mediaFileInfoCollection = $this->findMediaFileInfoCollection->forScopedAndLocalizedAssetFamilyAndAssetCodes(
            $assetFamilyCode,
            $assetCodes,
            $mediaFileChannel,
            $mediaFileLocale
        );

        $selectedData = array_map(static fn (MediaFileInfo $mediaFileInfo) => $mediaFileInfo->getFileKey(), $mediaFileInfoCollection);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof AssetCollectionMediaFileSelection
            && $value instanceof AssetCollectionValue;
    }
}
