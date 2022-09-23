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

use Akeneo\Platform\Syndication\Application\Common\MediaPathGeneratorInterface;
use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindAssetMainMediaDataInterface;

class AssetCollectionMediaFileSelectionApplier implements SelectionApplierInterface
{
    private FindAssetMainMediaDataInterface $findAssetMainMediaData;
    private MediaPathGeneratorInterface $mediaPathGenerator;

    public function __construct(
        FindAssetMainMediaDataInterface $findAssetMainMediaData,
        MediaPathGeneratorInterface $mediaPathGenerator
    ) {
        $this->findAssetMainMediaData = $findAssetMainMediaData;
        $this->mediaPathGenerator = $mediaPathGenerator;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof AssetCollectionMediaFileSelection
            || !$value instanceof AssetCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
        }

        $assetMainMediaFileData = $this->findAssetMainMediaData->forAssetFamilyAndAssetCodes(
            $selection->getAssetFamilyCode(),
            $value->getAssetCodes(),
            $selection->getChannel(),
            $selection->getLocale()
        );

        $selectedData = array_map(
            function (array $data) use ($selection, $value) {
                switch ($selection->getProperty()) {
                    case AssetCollectionMediaFileSelection::FILE_KEY_PROPERTY:
                        return $data['fileKey'];
                    case AssetCollectionMediaFileSelection::FILE_PATH_PROPERTY:
                        $exportDirectory = $this->mediaPathGenerator->generate(
                            $value->getEntityIdentifier(),
                            $selection->getAttributeCode(),
                            $value->getChannelReference(),
                            $value->getLocaleReference()
                        );
                        return sprintf('%s%s', $exportDirectory, $data['originalFilename']);
                    case AssetCollectionMediaFileSelection::ORIGINAL_FILENAME_PROPERTY:
                        return $data['originalFilename'];
                    default:
                        throw new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity');
                }
            },
            $assetMainMediaFileData
        );

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof AssetCollectionMediaFileSelection
            && $value instanceof AssetCollectionValue;
    }
}
