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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntityCollection\ReferenceEntityCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindRecordLabelsInterface $findRecordLabels)
    {
        $this->beConstructedWith($findRecordLabels);
    }

    public function it_applies_the_selection(FindRecordLabelsInterface $findRecordLabels)
    {
        $selection = new ReferenceEntityCollectionLabelSelection(
            '/',
            'fr_FR',
            'a_reference_entity_code'
        );
        $value = new ReferenceEntityCollectionValue(['record_code1', 'record_code2', 'record_code...']);

        $findRecordLabels->byReferenceEntityCodeAndRecordCodes(
            'a_reference_entity_code',
            ['record_code1', 'record_code2', 'record_code...'],
            'fr_FR'
        )->willReturn([
            'record_code2' => 'label2',
            'record_code...' => 'label...',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('[record_code1]/label2/label...');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_reference_entity_collection_label_selection_with_reference_entity_collection_value()
    {
        $selection = new ReferenceEntityCollectionLabelSelection(
            '/',
            'fr_FR',
            'a_reference_entity_code'
        );
        $value = new ReferenceEntityCollectionValue([]);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
