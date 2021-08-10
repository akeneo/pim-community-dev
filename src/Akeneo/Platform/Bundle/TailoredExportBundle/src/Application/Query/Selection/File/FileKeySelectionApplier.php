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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\File\FileKeySelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;

class FileKeySelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof FileKeySelection
            || !$value instanceof FileValue
        ) {
            throw new \InvalidArgumentException('Cannot apply File selection on this entity');
        }

        return $value->getKey();
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof FileKeySelection
            && $value instanceof FileValue;
    }
}
