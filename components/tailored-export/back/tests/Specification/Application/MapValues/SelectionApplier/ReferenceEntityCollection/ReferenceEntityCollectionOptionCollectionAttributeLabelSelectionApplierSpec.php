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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionOptionCollectionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionOptionCollectionAttributeLabelSelectionApplierSpec extends ObjectBehavior
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
        $selection = new ReferenceEntityCollectionOptionCollectionAttributeLabelSelection(
            ',',
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            '|',
            'fr_FR',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(['record_code1', 'record_code2', 'record_code3']);

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code3'],
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'record_code1' => ['oPTIOn_code1', 'option_CODE2'],
            'record_code2' => ['optiON_code2'],
            'record_code3' => ['option_cODe3', 'OPTion_code1'],
        ]);
        $findReferenceEntityOptionAttributeLabels->find(
            'a_reference_entity_option_attribute_identifier',
        )->willReturn([
            'opTION_code1' => [
                'en_US' => 'Option 1 EN',
                'fr_FR' => 'Option 1 FR',
            ],
            'option_CODe2' => [
                'en_US' => 'Option 2 EN',
                'fr_FR' => 'Option 2 FR',
            ],
            'optiON_code3' => [
                'en_US' => 'Option 3 EN',
                'fr_FR' => 'Option 3 FR',
            ],
        ]);

        $this->applySelection($selection, $value)->shouldReturn('Option 1 FR|Option 2 FR,Option 2 FR,Option 3 FR|Option 1 FR');
    }

    public function it_applies_the_selection_ignoring_case_and_mapped_values(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ): void {
        $selection = new ReferenceEntityCollectionOptionCollectionAttributeLabelSelection(
            ',',
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            ';',
            'fr_FR',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(
            ['record_code1', 'record_code2', 'record_code3'],
            ['record_code2' => 'I AM MAPPED'],
        );

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code3'],
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'RECORD_CODE1' => ['option_code1', 'option_code3'],
            'record_code2' => ['option_code2'],
            'RECORD_CODE3' => ['option_code3'],
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
            ],
            'option_code3' => [
                'en_US' => 'Option 3 EN',
                'fr_FR' => 'Option 3 FR',
            ],
        ]);

        $this->applySelection($selection, $value)->shouldReturn('Option 1 FR;Option 3 FR,I AM MAPPED,Option 3 FR');
    }

    public function it_applies_the_selection_and_fallbacks_on_code_when_no_translation_is_found(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue,
        FindReferenceEntityOptionAttributeLabelsInterface $findReferenceEntityOptionAttributeLabels,
    ): void {
        $selection = new ReferenceEntityCollectionOptionCollectionAttributeLabelSelection(
            ';',
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            '|',
            'fr_FR',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(['record_code1', 'record_code2', 'record_code3']);

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code3'],
            'a_reference_entity_option_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'record_code1' => ['option_code3', 'option_code1', 'option_code2'],
            'record_code2' => null,
            'record_code3' => ['option_code3'],
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
            ],
            'option_code3' => [
                'en_US' => 'Option 3 EN',
            ],
        ]);

        $this->applySelection($selection, $value)->shouldReturn('[option_code3]|Option 1 FR|Option 2 FR;;[option_code3]');
    }

    public function it_does_not_apply_selection_on_unsupported_selections_and_values(): void
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_reference_entity_text_attribute_selection_with_reference_entity_value(): void
    {
        $selection = new ReferenceEntityCollectionOptionCollectionAttributeLabelSelection(
            ',',
            'a_reference_entity_code',
            'a_reference_entity_option_attribute_identifier',
            ';',
            'en_US',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(['nice_record']);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values(): void
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
