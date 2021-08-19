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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use PhpSpec\ObjectBehavior;

class MeasurementValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('10', 'kilogram');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(MeasurementValue::class);
    }

    public function it_returns_the_value()
    {
        $this->getValue()->shouldReturn('10');
    }

    public function it_returns_the_unit()
    {
        $this->getUnit()->shouldReturn('kilogram');
    }
}
