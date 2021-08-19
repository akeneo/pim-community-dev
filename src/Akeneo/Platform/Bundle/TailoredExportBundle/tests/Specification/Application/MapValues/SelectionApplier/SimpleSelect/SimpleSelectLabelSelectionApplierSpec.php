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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SimpleSelect;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleSelect\SimpleSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SimpleSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;
use PhpSpec\ObjectBehavior;

class SimpleSelectLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindAttributeOptionLabelsInterface $findAttributeOptionLabels)
    {
        $this->beConstructedWith($findAttributeOptionLabels);
    }

    public function it_applies_the_selection(FindAttributeOptionLabelsInterface $findAttributeOptionLabels)
    {
        $selection = new SimpleSelectLabelSelection(
            'fr_FR',
            'color'
        );
        $value = new SimpleSelectValue('red');

        $findAttributeOptionLabels->byAttributeCodeAndOptionCodes(
            'color',
            ['red'],
            'fr_FR'
        )->willReturn([
            'red' => 'rouge',
        ]);

        $this->applySelection($selection, $value)->shouldReturn('rouge');
    }

    public function it_applies_the_selection_and_fallback_when_no_translation_is_found(
        FindAttributeOptionLabelsInterface $findAttributeOptionLabels
    ) {
        $selection = new SimpleSelectLabelSelection(
            'fr_FR',
            'color'
        );
        $value = new SimpleSelectValue('red');

        $findAttributeOptionLabels->byAttributeCodeAndOptionCodes(
            'color',
            ['red'],
            'fr_FR'
        )->willReturn([]);

        $this->applySelection($selection, $value)->shouldReturn('[red]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Simple Select selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_simple_select_label_selection_with_simple_select_value()
    {
        $selection = new SimpleSelectLabelSelection(
            'fr_FR',
            'color'
        );
        $value = new SimpleSelectValue('red');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
