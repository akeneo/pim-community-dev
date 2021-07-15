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

use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\File\FileSelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\MediaToExport;
use Akeneo\Platform\TailoredExport\Domain\MediaToExportExtractorInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\MediaExporterPathGenerator;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;

final class MediaToExportExtractor implements MediaToExportExtractorInterface
{
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
                if (!$selection instanceof FileSelectionInterface) {
                    continue;
                }

                $value = $valueCollection->getFromSource($source);

                if (!$value instanceof FileValue) {
                    continue;
                }

                $mediaToExports[$value->getKey()] = new MediaToExport(
                    $value->getKey(),
                    $value->getStorage(),
                    $this->generateFilePath(
                        $selection,
                        $value
                    )
                );
            }
        }

        return $mediaToExports;
    }

    private function generateFilePath(FileSelectionInterface $selection, FileValue $value): string
    {
        $exportDirectory = MediaExporterPathGenerator::generate(
            $value->getEntityIdentifier(),
            $selection->getAttributeCode(),
            $value->getChannelReference(),
            $value->getLocaleReference()
        );

        return sprintf('%s%s', $exportDirectory, $value->getOriginalFilename());
    }
}
