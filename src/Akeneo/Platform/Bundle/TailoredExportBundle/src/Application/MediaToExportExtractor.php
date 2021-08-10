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

namespace Akeneo\Platform\TailoredExport\Application;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetMainMediaFileInfoCollectionInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\MediaFileInfo;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\File\FileSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\Model\ValueCollection;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\MediaExporterPathGenerator;

final class MediaToExportExtractor implements MediaToExportExtractorInterface
{
    private GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection;

    public function __construct(GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection)
    {
        $this->getMainMediaFileInfoCollection = $getMainMediaFileInfoCollection;
    }

    /**
     * @return MediaToExport[]
     */
    public function extract(ColumnCollection $columnCollection, ValueCollection $valueCollection): array
    {
        $mediaToExports = [];

        /** @var SourceInterface $source */
        foreach ($columnCollection->getAllSources() as $source) {
            $selection = $source->getSelection();
            $value = $valueCollection->getFromSource($source);

            if ($selection instanceof FileSelectionInterface && $value instanceof FileValue) {
                $mediaToExports[] = $this->extractFromFileSource($selection, $value);
            }

            if ($selection instanceof AssetCollectionSelectionInterface && $value instanceof AssetCollectionValue) {
                $mediaToExports = array_merge($mediaToExports, $this->extractFromAssetCollectionSource($selection, $value));
            }
        }

        return $mediaToExports;
    }

    private function extractFromFileSource(FileSelectionInterface $selection, FileValue $value): MediaToExport
    {
        $exportDirectory = MediaExporterPathGenerator::generate(
            $value->getEntityIdentifier(),
            $selection->getAttributeCode(),
            $value->getChannelReference(),
            $value->getLocaleReference()
        );

        $path = sprintf('%s%s', $exportDirectory, $value->getOriginalFilename());

        return new MediaToExport(
            $value->getKey(),
            $value->getStorage(),
            $path
        );
    }

    /**
     * @return MediaToExport[]
     */
    private function extractFromAssetCollectionSource(
        AssetCollectionSelectionInterface $selection,
        AssetCollectionValue $value
    ): array {
        $mainMediaFileInfoCollection = $this->getMainMediaFileInfoCollection
            ->forAssetFamilyAndAssetCodes(
                $selection->getAssetFamilyCode(),
                $value->getAssetCodes()
            );

        return array_reduce(
            $mainMediaFileInfoCollection,
            function (array $accumulator, MediaFileInfo $fileInfo) use ($selection, $value) {
                $exportDirectory = MediaExporterPathGenerator::generate(
                    $value->getEntityIdentifier(),
                    $selection->getAttributeCode(),
                    $value->getChannelReference(),
                    $value->getLocaleReference()
                );

                $path = sprintf('%s%s', $exportDirectory, $fileInfo->getOriginalFilename());

                $accumulator[] = new MediaToExport(
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
