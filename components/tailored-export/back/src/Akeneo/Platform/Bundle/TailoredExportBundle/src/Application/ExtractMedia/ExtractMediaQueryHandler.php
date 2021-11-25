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

namespace Akeneo\Platform\TailoredExport\Application\ExtractMedia;

use Akeneo\Platform\TailoredExport\Application\Common\MediaPathGeneratorInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FileSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\FindMediaFileInfoCollectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\MediaFileInfo;

class ExtractMediaQueryHandler
{
    private FindMediaFileInfoCollectionInterface $findMediaFileInfoCollection;
    private MediaPathGeneratorInterface $mediaPathGenerator;

    public function __construct(
        FindMediaFileInfoCollectionInterface $findMediaFileInfoCollection,
        MediaPathGeneratorInterface $mediaPathGenerator
    ) {
        $this->findMediaFileInfoCollection = $findMediaFileInfoCollection;
        $this->mediaPathGenerator = $mediaPathGenerator;
    }

    /**
     * @return ExtractedMedia[]
     */
    public function handle(ExtractMediaQuery $extractMediaQuery): array
    {
        $mediaToExports = [];

        foreach ($extractMediaQuery->getColumnCollection()->getAllSources() as $source) {
            $selection = $source->getSelection();
            $value = $extractMediaQuery->getValueCollection()->getFromSource($source);

            if ($selection instanceof FileSelectionInterface && $value instanceof FileValue) {
                $mediaToExports[] = $this->extractFromFileSource($selection, $value);
            }

            if ($selection instanceof AssetCollectionSelectionInterface && $value instanceof AssetCollectionValue) {
                $mediaToExports = array_merge(
                    $mediaToExports,
                    $this->extractFromAssetCollectionSource($selection, $value)
                );
            }
        }

        return $mediaToExports;
    }

    private function extractFromFileSource(FileSelectionInterface $selection, FileValue $value): ExtractedMedia
    {
        $exportDirectory = $this->mediaPathGenerator->generate(
            $value->getEntityIdentifier(),
            $selection->getAttributeCode(),
            $value->getChannelReference(),
            $value->getLocaleReference()
        );

        $path = sprintf('%s%s', $exportDirectory, $value->getOriginalFilename());

        return new ExtractedMedia(
            $value->getKey(),
            $value->getStorage(),
            $path
        );
    }

    /**
     * @return ExtractedMedia[]
     */
    private function extractFromAssetCollectionSource(
        AssetCollectionSelectionInterface $selection,
        AssetCollectionValue $value
    ): array {
        $mainMediaFileInfoCollection = $this->findMediaFileInfoCollection
            ->forAssetFamilyAndAssetCodes(
                $selection->getAssetFamilyCode(),
                $value->getAssetCodes()
            );

        return array_reduce(
            $mainMediaFileInfoCollection,
            function (array $accumulator, MediaFileInfo $fileInfo) use ($selection, $value) {
                $exportDirectory = $this->mediaPathGenerator->generate(
                    $value->getEntityIdentifier(),
                    $selection->getAttributeCode(),
                    $value->getChannelReference(),
                    $value->getLocaleReference()
                );

                $path = sprintf('%s%s', $exportDirectory, $fileInfo->getOriginalFilename());

                $accumulator[] = new ExtractedMedia(
                    $fileInfo->getFileKey(),
                    $fileInfo->getStorage(),
                    $path
                );

                return $accumulator;
            },
            []
        );
    }
}
