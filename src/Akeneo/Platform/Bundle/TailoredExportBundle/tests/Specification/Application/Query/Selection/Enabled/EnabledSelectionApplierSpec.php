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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Enabled;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\EnabledValue;
use PhpSpec\ObjectBehavior;

class EnabledSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection_when_value_is_true()
    {
        $selection = new EnabledSelection();
        $value = new EnabledValue(true);

        $this->applySelection($selection, $value)
            ->shouldReturn('1');
    }

    public function it_applies_the_selection_when_value_is_false()
    {
        $selection = new EnabledSelection();
        $value = new EnabledValue(false);

        $this->applySelection($selection, $value)
            ->shouldReturn('0');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Enabled selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_enabled_selection_with_enabled_value()
    {
        $selection = new EnabledSelection();
        $value = new EnabledValue(true);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
