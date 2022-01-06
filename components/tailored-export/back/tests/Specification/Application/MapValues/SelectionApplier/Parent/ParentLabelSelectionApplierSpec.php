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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Parent;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent\ParentLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductModelLabelsInterface;
use PhpSpec\ObjectBehavior;

class ParentLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindProductModelLabelsInterface $findProductModelLabels)
    {
        $this->beConstructedWith($findProductModelLabels);
    }

    public function it_applies_the_selection(FindProductModelLabelsInterface $findProductModelLabels)
    {
        $selection = new ParentLabelSelection('fr_FR', 'ecommerce');
        $value = new ParentValue('tshirt_cool');

        $findProductModelLabels->byCodes(
            ['tshirt_cool'],
            'ecommerce',
            'fr_FR'
        )->willReturn([
            'tshirt_cool' => 'Un tshirt sympa'
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('Un tshirt sympa');
    }

    public function it_applies_the_selection_and_fallback_when_no_translation_is_found(FindProductModelLabelsInterface $findProductModelLabels)
    {
        $selection = new ParentLabelSelection('fr_FR', 'ecommerce');
        $value = new ParentValue('tshirt_cool');

        $findProductModelLabels->byCodes(
            ['tshirt_cool'],
            'ecommerce',
            'fr_FR'
        )->willReturn([]);

        $this->applySelection($selection, $value)
            ->shouldReturn('[tshirt_cool]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Parent selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_parent_label_selection_with_parent_value()
    {
        $selection = new ParentLabelSelection('fr_FR', 'ecommerce');
        $value = new ParentValue('tshirt_cool');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
