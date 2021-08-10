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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Number;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Number\NumberSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NumberValue;
use PhpSpec\ObjectBehavior;

class NumberSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new NumberSelection(',');
        $value = new NumberValue('10');
        $decimalValue = new NumberValue('10.89');

        $this->applySelection($selection, $value)
            ->shouldReturn('10');

        $this->applySelection($selection, $decimalValue)
            ->shouldReturn('10,89');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Number selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_number_selection_with_number_value()
    {
        $selection = new NumberSelection(',');
        $value = new NumberValue('10');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
