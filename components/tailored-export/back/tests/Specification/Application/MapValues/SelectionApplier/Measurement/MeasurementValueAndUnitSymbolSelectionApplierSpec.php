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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Measurement;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueAndUnitSymbolSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitSymbolInterface;
use PhpSpec\ObjectBehavior;

class MeasurementValueAndUnitSymbolSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindUnitSymbolInterface $findUnitSymbol)
    {
        $this->beConstructedWith($findUnitSymbol);
    }

    public function it_applies_the_selection(FindUnitSymbolInterface $findUnitSymbol)
    {
        $selection = new MeasurementValueAndUnitSymbolSelection(',', 'Weight');
        $value = new MeasurementValue('10.4', 'kilogram');

        $findUnitSymbol->byFamilyCodeAndUnitCode(
            'Weight',
            'kilogram',
        )->willReturn('Kg');

        $this->applySelection($selection, $value)
            ->shouldReturn('10,4 Kg');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Measurement value and unit symbol selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_measurement_value_and_unit_symbol_selection_with_measurement_value()
    {
        $selection = new MeasurementValueAndUnitSymbolSelection('.', 'Weight');
        $value = new MeasurementValue('10', 'kilogram');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
