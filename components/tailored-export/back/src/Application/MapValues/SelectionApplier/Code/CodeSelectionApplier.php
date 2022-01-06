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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Code;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Code\CodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\CodeValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class CodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof CodeSelection
            || !$value instanceof CodeValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Code selection on this entity');
        }

        return $value->getData();
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $value instanceof CodeValue;
    }
}
