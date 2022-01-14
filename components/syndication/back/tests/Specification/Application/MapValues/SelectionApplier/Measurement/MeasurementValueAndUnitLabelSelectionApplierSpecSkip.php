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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Measurement;

use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\Measurement\MeasurementValueAndUnitLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Domain\Query\FindUnitLabelInterface;
use PhpSpec\ObjectBehavior;

class MeasurementValueAndUnitLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindUnitLabelInterface $findUnitLabel)
    {
        $this->beConstructedWith($findUnitLabel);
    }

    public function it_applies_the_selection(FindUnitLabelInterface $findUnitLabel)
    {
        $selection = new MeasurementValueAndUnitLabelSelection(',', 'Weight', 'fr_FR');
        $value = new MeasurementValue('10.4', 'kilogram');

        $findUnitLabel->byFamilyCodeAndUnitCode(
            'Weight',
            'kilogram',
            'fr_FR'
        )->willReturn('Kilogramme');

        $this->applySelection($selection, $value)
            ->shouldReturn('10,4 Kilogramme');
    }

    public function it_applies_the_selection_and_fallback_when_no_translation_is_found(FindUnitLabelInterface $findUnitLabel)
    {
        $selection = new MeasurementValueAndUnitLabelSelection('.', 'Weight', 'fr_FR');
        $value = new MeasurementValue('10', 'kilogram');

        $findUnitLabel->byFamilyCodeAndUnitCode(
            'Weight',
            'kilogram',
            'fr_FR'
        )->willReturn(null);

        $this->applySelection($selection, $value)
            ->shouldReturn('10 [kilogram]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Measurement value and unit label selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_measurement_value_and_unit_label_selection_with_measurement_value()
    {
        $selection = new MeasurementValueAndUnitLabelSelection('.', 'Weight', 'fr_FR');
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
