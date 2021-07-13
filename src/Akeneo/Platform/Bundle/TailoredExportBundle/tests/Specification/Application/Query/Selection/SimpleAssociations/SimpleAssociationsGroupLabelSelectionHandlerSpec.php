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
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleAssociations\SimpleAssociationsGroupLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use PhpSpec\ObjectBehavior;

class SimpleAssociationsGroupLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(FindGroupLabelsInterface $findGroupLabelsInterface)
    {
        $this->beConstructedWith($findGroupLabelsInterface);
    }

    public function it_applies_the_selection_on_groups(FindGroupLabelsInterface $findGroupLabelsInterface)
    {
        $selection = new SimpleAssociationsGroupLabelSelection('en_US', ',');
        $value = new SimpleAssociationsValue(
            ['1111111171', '13620748'],
            ['athena', 'hat'],
            ['summerSale2020', 'summerSale2021']
        );

        $findGroupLabelsInterface->byCodes(['summerSale2020', 'summerSale2021'], 'en_US')->willReturn([
            'summerSale2020' => 'Summer sale 2020'
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('Summer sale 2020,[summerSale2021]');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply simple associations group label selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_simple_association_group_label_selection_with_simple_association_value()
    {
        $selection = new SimpleAssociationsGroupLabelSelection('en_US', '/');
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
