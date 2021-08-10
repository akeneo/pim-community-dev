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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleAssociations;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleAssociations\SimpleAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SimpleAssociationsValue;
use PhpSpec\ObjectBehavior;

class SimpleAssociationsCodeSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection_on_products()
    {
        $selection = new SimpleAssociationsCodeSelection('products', ',');
        $value = new SimpleAssociationsValue(
            ['1111111171', '13620748'],
            ['athena', 'hat'],
            ['summerSale2020', 'summerSale2021']
        );

        $this->applySelection($selection, $value)
            ->shouldReturn('1111111171,13620748');
    }

    public function it_applies_the_selection_on_product_models()
    {
        $selection = new SimpleAssociationsCodeSelection('product_models', '|');
        $value = new SimpleAssociationsValue(
            ['1111111171', '13620748'],
            ['athena', 'hat'],
            ['summerSale2020', 'summerSale2021']
        );

        $this->applySelection($selection, $value)
            ->shouldReturn('athena|hat');
    }

    public function it_applies_the_selection_on_groups()
    {
        $selection = new SimpleAssociationsCodeSelection('groups', '|');
        $value = new SimpleAssociationsValue(
            ['1111111171', '13620748'],
            ['athena', 'hat'],
            ['summerSale2020', 'summerSale2021']
        );

        $this->applySelection($selection, $value)
            ->shouldReturn('summerSale2020|summerSale2021');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply simple associations code selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_simple_associations_code_selection_with_simple_association_value()
    {
        $selection = new SimpleAssociationsCodeSelection('products', '/');
        $value = new SimpleAssociationsValue([], [], []);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
