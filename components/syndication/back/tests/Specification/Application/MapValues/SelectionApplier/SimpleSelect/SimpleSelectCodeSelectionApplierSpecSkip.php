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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SimpleSelect;

use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SimpleSelect\SimpleSelectCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleSelectValue;
use PhpSpec\ObjectBehavior;

class SimpleSelectCodeSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new SimpleSelectCodeSelection('/');
        $value = new SimpleSelectValue('option1');

        $this->applySelection($selection, $value)->shouldReturn('option1');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Simple Select selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_simple_select_code_selection_with_simple_select_value()
    {
        $selection = new SimpleSelectCodeSelection('/');
        $value = new SimpleSelectValue('nice_option');

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
