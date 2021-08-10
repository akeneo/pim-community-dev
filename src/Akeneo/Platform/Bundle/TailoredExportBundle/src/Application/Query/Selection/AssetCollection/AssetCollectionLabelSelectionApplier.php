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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAssetLabelsInterface;

class AssetCollectionLabelSelectionApplier implements SelectionApplierInterface
{
    private FindAssetLabelsInterface $findAssetLabels;

    public function __construct(FindAssetLabelsInterface $findAssetLabels)
    {
        $this->findAssetLabels = $findAssetLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof AssetCollectionLabelSelection
            || !$value instanceof AssetCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
        }

        $assetCodes = $value->getAssetCodes();
        $assetFamilyCode = $selection->getAssetFamilyCode();
        $assetTranslations = $this->findAssetLabels->byAssetFamilyCodeAndAssetCodes(
            $assetFamilyCode,
            $assetCodes,
            $selection->getLocale()
        );

        $selectedData = array_map(fn ($assetCode) => $assetTranslations[$assetCode] ??
            sprintf('[%s]', $assetCode), $assetCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof AssetCollectionLabelSelection
            && $value instanceof AssetCollectionValue;
    }
}
