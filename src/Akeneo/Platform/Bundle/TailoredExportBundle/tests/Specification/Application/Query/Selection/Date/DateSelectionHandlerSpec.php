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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Date;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Date\DateSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\DateValue;
use PhpSpec\ObjectBehavior;

class DateSelectionHandlerSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new DateSelection('yyyy-mm-dd');
        $value = new DateValue(new \DateTimeImmutable('2020-07-01 16:30:00', new \DateTimeZone('UTC')));

        $this->applySelection($selection, $value)
            ->shouldReturn('2020-07-01');
    }

    public function it_does_not_applies_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Date selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_categories_code_selection_with_categories_value()
    {
        $selection = new DateSelection('yyyy-mm-dd');
        $value = new DateValue(new \DateTimeImmutable('2020-07-01 16:30:00', new \DateTimeZone('UTC')));

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_supports_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
