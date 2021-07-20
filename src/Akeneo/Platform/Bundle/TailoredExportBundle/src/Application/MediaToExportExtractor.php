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

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetFileInfoInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\File\FileSelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\MediaToExport;
use Akeneo\Platform\TailoredExport\Domain\MediaToExportExtractorInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\MediaExporterPathGenerator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

final class MediaToExportExtractor implements MediaToExportExtractorInterface
{
    private GetFileInfoInterface $getFileInfoCollection;

    public function __construct(GetFileInfoInterface $getFileInfoCollection)
    {
        $this->getFileInfoCollection = $getFileInfoCollection;
    }

    /**
     * @return MediaToExport[]
     */
    public function extract(ColumnCollection $columnCollection, ValueCollection $valueCollection): array
    {
        $mediaToExports = [];

        /** @var Column $column */
        foreach ($columnCollection as $column) {
            foreach ($column->getSourceCollection() as $source) {
                $selection = $source->getSelection();

                if ($selection instanceof FileSelectionInterface
                    && ($value = $valueCollection->getFromSource($source)) instanceof FileValue
                ) {
                    $mediaToExports[] = $this->extractFromFileSource($selection, $value);
                }

                if ($selection instanceof AssetCollectionSelectionInterface
                    && ($value = $valueCollection->getFromSource($source)) instanceof AssetCollectionValue
                ) {
                    $mediaToExports = array_merge($mediaToExports, $this->extractFromAssetCollectionSource($selection, $value));
                }
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
    private function extractFromAssetCollectionSource(AssetCollectionSelectionInterface $selection, AssetCollectionValue $value): array
    {
        $fileInfoCollection = $this->getFileInfoCollection
            ->forAssetFamilyAndAssetCodes(
                $selection->getAssetFamilyCode(),
                $value->getAssetCodes(),
                $value->getChannelReference(),
                $value->getLocaleReference()
            );

        $mediaToExports = array_map(
//            function(FileInfo $fileInfo) use ($selection, $value) {
            function($fileInfo) use ($selection, $value) {
                $exportDirectory = MediaExporterPathGenerator::generate(
                    $value->getEntityIdentifier(),
                    $selection->getAttributeCode(),
                    $value->getChannelReference(),
                    $value->getLocaleReference()
                );

                $path = sprintf('%s%s', $exportDirectory, $fileInfo['originalFilename']);

                return new MediaToExport(
                    $fileInfo['filePath'],
                    Storage::FILE_STORAGE_ALIAS,
                    $path
                );
            },
            $fileInfoCollection
        );

        return $mediaToExports;
    }
}
