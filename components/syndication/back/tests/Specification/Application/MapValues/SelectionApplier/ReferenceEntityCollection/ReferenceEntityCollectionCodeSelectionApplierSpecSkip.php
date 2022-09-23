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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\ReferenceEntityCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionCodeSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new ReferenceEntityCollectionCodeSelection('/');
        $value = new ReferenceEntityCollectionValue(['record_code1', 'record_code2', 'record_code...']);

        $this->applySelection($selection, $value)
            ->shouldReturn('record_code1/record_code2/record_code...');
    }

    public function it_applies_the_selection_with_mapped_replacement_values()
    {
        $selection = new ReferenceEntityCollectionCodeSelection('/');
        $value = new ReferenceEntityCollectionValue(
            [
                'record_code1',
                'record_code2'
            ],
            [
                'record_code2' => 'replacement_option',
            ]
        );

        $this->applySelection($selection, $value)
            ->shouldReturn('record_code1/replacement_option');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_reference_entity_collection_code_selection_with_reference_entity_collection_value()
    {
        $selection = new ReferenceEntityCollectionCodeSelection('/');
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
