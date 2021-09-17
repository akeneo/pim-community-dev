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

use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionMediaLinkSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindMediaLinksInterface;

class AssetCollectionMediaLinkSelectionApplier implements SelectionApplierInterface
{
    private FindMediaLinksInterface $findMediaLinks;

    public function __construct(FindMediaLinksInterface $findMediaFileInfoCollection)
    {
        $this->findMediaLinks = $findMediaFileInfoCollection;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof AssetCollectionMediaLinkSelection
            || !$value instanceof AssetCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
        }

        $assetCodes = $value->getAssetCodes();
        $assetFamilyCode = $selection->getAssetFamilyCode();
        $mediaLinkChannel = $selection->getChannel();
        $mediaLinkLocale = $selection->getLocale();
        $mediaLinks = $this->findMediaLinks->forScopedAndLocalizedAssetFamilyAndAssetCodes(
            $assetFamilyCode,
            $assetCodes,
            $mediaLinkChannel,
            $mediaLinkLocale
        );

        return implode($selection->getSeparator(), $mediaLinks);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof AssetCollectionMediaLinkSelection
            && $value instanceof AssetCollectionValue;
    }
}
