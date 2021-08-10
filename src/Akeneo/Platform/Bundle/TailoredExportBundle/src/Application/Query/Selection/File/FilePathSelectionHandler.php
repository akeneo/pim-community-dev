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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\File;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\MediaExporterPathGenerator;

class FilePathSelectionHandler implements SelectionHandlerInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof FilePathSelection
            || !$value instanceof FileValue
        ) {
            throw new \InvalidArgumentException('Cannot apply File selection on this entity');
        }

        $exportDirectory = MediaExporterPathGenerator::generate(
            $value->getEntityIdentifier(),
            $selection->getAttributeCode(),
            $value->getChannelReference(),
            $value->getLocaleReference()
        );

        return sprintf('%s%s', $exportDirectory, $value->getOriginalFilename());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof FilePathSelection
            && $value instanceof FileValue;
    }
}
