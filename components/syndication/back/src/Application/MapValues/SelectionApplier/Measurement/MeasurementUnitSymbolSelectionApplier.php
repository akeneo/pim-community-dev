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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Measurement;

use Akeneo\Platform\Syndication\Application\Common\Selection\Measurement\MeasurementUnitSymbolSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindUnitSymbolInterface;

class MeasurementUnitSymbolSelectionApplier implements SelectionApplierInterface
{
    public function __construct(
        private FindUnitSymbolInterface $findUnitSymbol
    ) {
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof MeasurementUnitSymbolSelection
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement unit symbol selection on this entity');
        }

        $unitSymbol = $this->findUnitSymbol->byFamilyCodeAndUnitCode(
            $selection->getMeasurementFamilyCode(),
            $value->getUnitCode(),
        );

        return $unitSymbol;
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof MeasurementUnitSymbolSelection
            && $value instanceof MeasurementValue;
    }
}
