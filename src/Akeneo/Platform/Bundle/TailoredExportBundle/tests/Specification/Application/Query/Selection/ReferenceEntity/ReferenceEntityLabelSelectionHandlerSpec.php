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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ReferenceEntityValue;
use PhpSpec\ObjectBehavior;

class ReferenceEntityLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(FindRecordLabelsInterface $findRecordLabels)
    {
        $this->beConstructedWith($findRecordLabels);
    }

    public function it_applies_the_selection(FindRecordLabelsInterface $findRecordLabels)
    {
        $selection = new ReferenceEntityLabelSelection(
            'fr_FR',
            'a_reference_entity_code'
        );
        $value = new ReferenceEntityValue('record_code1');

        $findRecordLabels->byReferenceEntityCodeAndRecordCodes(
            'a_reference_entity_code',
            ['record_code1'],
            'fr_FR'
        )->willReturn([
            'record_code1' => 'label1',
        ]);

        $this->applySelection($selection, $value)->shouldReturn('label1');
    }

    public function it_applies_the_selection_and_fallback_when_no_translation_is_found(
        FindRecordLabelsInterface $findRecordLabels
    ) {
        $selection = new ReferenceEntityLabelSelection(
            'fr_FR',
            'a_reference_entity_code'
        );
        $value = new ReferenceEntityValue('record_code1');

        $findRecordLabels->byReferenceEntityCodeAndRecordCodes(
            'a_reference_entity_code',
            ['record_code1'],
            'fr_FR'
        )->willReturn([]);

        $this->applySelection($selection, $value)->shouldReturn('[record_code1]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Reference Entity selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_reference_entity_label_selection_with_reference_entity_value()
    {
        $selection = new ReferenceEntityLabelSelection(
            '/',
            'fr_FR',
            'a_reference_entity_code'
        );
        $value = new ReferenceEntityValue('nice_record');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
