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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Date;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\DateFormat;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\DateValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class DateSelectionHandler implements SelectionHandlerInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof DateSelection
            || !$value instanceof DateValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Date selection on this entity');
        }

        return DateFormat::format($value->getData(), $selection->getFormat());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof DateSelection
            && $value instanceof DateValue;
    }
}
