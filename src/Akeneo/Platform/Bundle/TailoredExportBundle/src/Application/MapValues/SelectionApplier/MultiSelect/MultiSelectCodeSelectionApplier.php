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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\MultiSelect;

use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\MultiSelect\MultiSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;

class MultiSelectCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MultiSelectCodeSelection
            || !$value instanceof MultiSelectValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Multi Select selection on this entity');
        }

        return implode($selection->getSeparator(), $value->getOptionCodes());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MultiSelectCodeSelection
            && $value instanceof MultiSelectValue;
    }
}
