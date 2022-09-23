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

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Date;

use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\Date\DateSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\DateValue;
use PhpSpec\ObjectBehavior;

class DateSelectionApplierSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new DateSelection('yyyy-mm-dd');
        $value = new DateValue(new \DateTimeImmutable('2020-07-01 16:30:00', new \DateTimeZone('UTC')));

        $this->applySelection($selection, $value)
            ->shouldReturn('2020-07-01');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Date selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_date_selection_with_date_value()
    {
        $selection = new DateSelection('yyyy-mm-dd');
        $value = new DateValue(new \DateTimeImmutable('2020-07-01 16:30:00', new \DateTimeZone('UTC')));

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
