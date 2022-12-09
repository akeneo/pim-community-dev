<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityOptionAttributeCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityOptionAttributeCodeSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindRecordsAttributeValueInterface $findRecordsAttributeValue): void
    {
        $this->beConstructedWith($findRecordsAttributeValue);
    }

    public function it_applies_the_selection(FindRecordsAttributeValueInterface $findRecordsAttributeValue): void
    {
        $selection = new ReferenceEntityOptionAttributeCodeSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityValue('record_code1');

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1'],
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'record_code1' => 'option_code1',
        ]);

        $this->applySelection($selection, $value)->shouldReturn('option_code1');
    }

    public function it_applies_the_selection_ignoring_case(FindRecordsAttributeValueInterface $findRecordsAttributeValue): void
    {
        $selection = new ReferenceEntityOptionAttributeCodeSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityValue('record_code1');

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1'],
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'reCorD_cOde1' => 'option_code1',
        ]);

        $this->applySelection($selection, $value)->shouldReturn('option_code1');
    }

    public function it_applies_the_selection_and_fallback_when_no_value_is_found(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue
    ): void {
        $selection = new ReferenceEntityOptionAttributeCodeSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityValue('record_code1');

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1'],
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([]);

        $this->applySelection($selection, $value)->shouldReturn('');
    }

    public function it_does_not_apply_selection_on_unsupported_selections_and_values(): void
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Reference Entity selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_reference_entity_text_attribute_selection_with_reference_entity_value(): void
    {
        $selection = new ReferenceEntityOptionAttributeCodeSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityValue('nice_record');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values(): void
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
