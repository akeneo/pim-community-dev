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

use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileUrlSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindAssetMainMediaDataInterface;

class AssetCollectionMediaFileUrlSelectionApplier implements SelectionApplierInterface
{
    private FindAssetMainMediaDataInterface $findAssetMainMediaData;

    public function __construct(
        FindAssetMainMediaDataInterface $findAssetMainMediaData,
    ) {
        $this->findAssetMainMediaData = $findAssetMainMediaData;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): ?string
    {
        if (
            !$selection instanceof AssetCollectionMediaFileUrlSelection
            || !$value instanceof AssetCollectionValue
            || 'url' !== $target->getType()
        ) {
            throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
        }

        if (!isset($value->getAssetCodes()[$selection->getPosition()])) {
            return null;
        }

        $assetMainMediaFileData = $this->findAssetMainMediaData->forAssetFamilyAndAssetCodes(
            $selection->getAssetFamilyCode(),
            [$value->getAssetCodes()[$selection->getPosition()]],
            $selection->getChannel(),
            $selection->getLocale()
        );

        $assetMainMedia = current($assetMainMediaFileData);

        return is_string($assetMainMedia) ? $assetMainMedia : $assetMainMedia['fileKey'];
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof AssetCollectionMediaFileUrlSelection
            && $value instanceof AssetCollectionValue && 'url' === $target->getType();
    }
}
