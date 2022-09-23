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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\AssetCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionMediaLinkSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\FindAssetMainMediaAttributeInterface;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\MediaLinkAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\FindAssetMainMediaDataInterface;

class AssetCollectionMediaLinkSelectionApplier implements SelectionApplierInterface
{
    private FindAssetMainMediaDataInterface $findAssetMainMediaData;
    private FindAssetMainMediaAttributeInterface $findAssetMainMediaAttribute;

    public function __construct(
        FindAssetMainMediaDataInterface $findAssetMainMediaData,
        FindAssetMainMediaAttributeInterface $findAssetMainMediaAttribute
    ) {
        $this->findAssetMainMediaData = $findAssetMainMediaData;
        $this->findAssetMainMediaAttribute = $findAssetMainMediaAttribute;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof AssetCollectionMediaLinkSelection
            || !$value instanceof AssetCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
        }

        $assetMainMediaLinkData = $this->findAssetMainMediaData->forAssetFamilyAndAssetCodes(
            $selection->getAssetFamilyCode(),
            $value->getAssetCodes(),
            $selection->getChannel(),
            $selection->getLocale()
        );

        if ($selection->withPrefixAndSuffix()) {
            $assetMainMediaLinkData = $this->applyPrefixAndSuffix($selection, $assetMainMediaLinkData);
        }

        return implode($selection->getSeparator(), $assetMainMediaLinkData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof AssetCollectionMediaLinkSelection
            && $value instanceof AssetCollectionValue;
    }

    private function applyPrefixAndSuffix(AssetCollectionMediaLinkSelection $selection, array $assetMainMediaLinkData): array
    {
        $attributeAsMainMedia = $this->findAssetMainMediaAttribute->forAssetFamily($selection->getAssetFamilyCode());
        if (!$attributeAsMainMedia instanceof MediaLinkAsMainMedia) {
            throw new \InvalidArgumentException('Asset main media is not a media link');
        }

        return array_map(
            static fn (string $mediaLinkData) => sprintf('%s%s%s', $attributeAsMainMedia->getPrefix(), $mediaLinkData, $attributeAsMainMedia->getSuffix()),
            $assetMainMediaLinkData
        );
    }
}
