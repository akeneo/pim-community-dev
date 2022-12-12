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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityOptionCollectionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityOptionCollectionAttributeLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ): void {
        $this->beConstructedWith($findRecordsAttributeValue, $findReferenceEntityOptionAttributeLabels);
    }

    public function it_applies_the_selection(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ): void {
        $selection = new ReferenceEntityOptionCollectionAttributeLabelSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            ';',
            'en_US',
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
            'reCord_Code1' => ['option_CODe1', 'oPTIon_code2'],
        ]);
        $findReferenceEntityOptionAttributeLabels->find(
            'a_reference_entity_option_attribute_identifier',
        )->willReturn([
            'option_cODE1' => [
                'en_US' => 'Option 1 EN',
                'fr_FR' => 'Option 1 FR',
            ],
            'optiON_code2' => [
                'en_US' => 'Option 2 EN',
                'fr_FR' => 'Option 2 FR',
            ],
        ]);

        $this->applySelection($selection, $value)->shouldReturn('Option 1 EN;Option 2 EN');
    }

    public function it_applies_the_selection_and_fallbacks_on_code_when_no_translation_is_found(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ): void {
        $selection = new ReferenceEntityOptionCollectionAttributeLabelSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            ';',
            'de_DE',
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
            'record_code1' => ['option_code1', 'option_code2'],
        ]);
        $findReferenceEntityOptionAttributeLabels->find(
            'a_reference_entity_option_attribute_identifier',
        )->willReturn([
            'option_code1' => [
                'en_US' => 'Option 1 EN',
                'fr_FR' => 'Option 1 FR',
            ],
            'option_code2' => [
                'en_US' => 'Option 2 EN',
                'fr_FR' => 'Option 2 FR',
                'de_DE' => 'Option 2 DE',
            ],
        ]);

        $this->applySelection($selection, $value)->shouldReturn('[option_code1];Option 2 DE');
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
        $selection = new ReferenceEntityOptionCollectionAttributeLabelSelection(
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            ';',
            'en_US',
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
