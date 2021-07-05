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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Parent;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Parent\ParentLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ParentValue;
use PhpSpec\ObjectBehavior;

class ParentLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->beConstructedWith($getProductModelLabels);
    }

    public function it_applies_the_selection(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $selection = new ParentLabelSelection('fr_FR', 'ecommerce');
        $value = new ParentValue('tshirt_cool');

        $getProductModelLabels->byCodesAndLocaleAndScope(
            ['tshirt_cool'],
            'fr_FR',
            'ecommerce'
        )->willReturn([
            'tshirt_cool' => 'Un tshirt sympa'
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('Un tshirt sympa');
    }

    public function it_does_not_applies_selection_on_not_supported_selections_and_values()
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

    public function it_does_not_supports_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
