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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\MultiSelect;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;
use PhpSpec\ObjectBehavior;

class MultiSelectLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindAttributeOptionLabelsInterface $findAttributeOptionLabels)
    {
        $this->beConstructedWith($findAttributeOptionLabels);
    }

    public function it_applies_the_selection(FindAttributeOptionLabelsInterface $findAttributeOptionLabels)
    {
        $selection = new MultiSelectLabelSelection('/', 'fr_FR', 'an_attribute_code');
        $value = new MultiSelectValue(['option_code1', 'option_code2']);

        $findAttributeOptionLabels->byAttributeCodeAndOptionCodes(
            'an_attribute_code',
            ['option_code1', 'option_code2'],
            'fr_FR'
        )->willReturn([
            'option_code1' => 'Le label en FR',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('Le label en FR/[option_code2]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Multi Select selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_multiselect_label_selection_with_multiselect_value()
    {
        $selection = new MultiSelectLabelSelection('/', 'fr_FR', 'an_attribute_code');
        $value = new MultiSelectValue(['option_code1', 'option_code2']);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
