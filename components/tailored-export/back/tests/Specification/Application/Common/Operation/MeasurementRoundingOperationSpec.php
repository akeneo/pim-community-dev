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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\Operation;

use PhpSpec\ObjectBehavior;

class MeasurementRoundingOperationSpec extends ObjectBehavior
{
    public function it_can_be_a_standard_rounding_operation(): void
    {
        $precision = 3;
        $this->beConstructedWith('standard', $precision);

        $this->getType()->shouldReturn('standard');
        $this->getPrecision()->shouldReturn($precision);
    }

    public function it_can_be_a_round_up_operation(): void
    {
        $this->beConstructedWith('round_up', 3);
    }

    public function it_can_be_a_round_down_operation(): void
    {
        $this->beConstructedWith('round_down', 3);
    }

    public function it_throws_an_exception_when_type_is_not_authorized(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', ['foo', 1]);
    }

    public function it_throws_an_exception_when_precision_is_less_than_0(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', ['foo', -1]);
    }

    public function it_throws_an_exception_when_precision_is_greater_than_12(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', ['foo', 13]);
    }
}
