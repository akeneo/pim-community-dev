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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Number;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Number\NumberSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NumberValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class NumberSelectionApplier implements SelectionApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$selection instanceof NumberSelection || !$value instanceof NumberValue) {
            throw new \InvalidArgumentException('Cannot apply Number selection on this entity');
        }

        // Doing an str_replace on a number will cast it to a string and then replace the default decimal separator (a dot)
        return str_replace(static::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), $value->getData());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof NumberSelection && $value instanceof NumberValue;
    }
}
