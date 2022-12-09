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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionTextAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionTextAttributeSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindRecordsAttributeValueInterface $findRecordsAttributeValue): void
    {
        $this->beConstructedWith($findRecordsAttributeValue);
    }

    public function it_applies_the_selection(FindRecordsAttributeValueInterface $findRecordsAttributeValue): void
    {
        $selection = new ReferenceEntityCollectionTextAttributeSelection(
            ';',
            'a_reference_entity_code',
            'a_reference_entity_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(['record_code1', 'record_code2', 'record_code3']);

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code3'],
            'a_reference_entity_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'record_code1' => 'label1',
            'record_code2' => 'label2',
            'record_code3' => 'label3',
        ]);

        $this->applySelection($selection, $value)->shouldReturn('label1;label2;label3');
    }

    public function it_applies_the_selection_ignoring_case_and_mapped_values(FindRecordsAttributeValueInterface $findRecordsAttributeValue): void
    {
        $selection = new ReferenceEntityCollectionTextAttributeSelection(
            ';',
            'a_reference_entity_code',
            'a_reference_entity_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(
            ['record_code1', 'record_code2', 'record_code3'],
            ['record_code2' => 'DO NOT REPLACE'],
        );

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code3'],
            'a_reference_entity_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'recOrd_Code1' => 'label1',
            'record_code2' => 'label2',
            'record_CodE3' => 'label3',
        ]);

        $this->applySelection($selection, $value)->shouldReturn('label1;DO NOT REPLACE;label3');
    }

    public function it_applies_the_selection_and_fallback_when_no_value_is_found(
        FindRecordsAttributeValueInterface $findRecordsAttributeValue
    ): void {
        $selection = new ReferenceEntityCollectionTextAttributeSelection(
            ',',
            'a_reference_entity_code',
            'a_reference_entity_attribute_identifier',
            'ecommerce',
            'br_FR',
        );
        $value = new ReferenceEntityCollectionValue(['record_code1', 'record_code2', 'record_code3']);

        $findRecordsAttributeValue->find(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code3'],
            'a_reference_entity_attribute_identifier',
            'ecommerce',
            'br_FR',
        )->willReturn([
            'record_code2' => 'label2',
        ]);

        $this->applySelection($selection, $value)->shouldReturn(',label2,');
    }

    public function it_does_not_apply_selection_on_unsupported_selections_and_values(): void
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_reference_entity_collection_text_attribute_selection_with_reference_entity_collection_value(): void
    {
        $selection = new ReferenceEntityCollectionTextAttributeSelection(
            '|',
            'a_reference_entity_code',
            'a_reference_entity_attribute_identifier',
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
