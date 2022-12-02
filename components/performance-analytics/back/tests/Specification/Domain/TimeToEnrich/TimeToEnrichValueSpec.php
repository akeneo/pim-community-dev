<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\TimeToEnrichValue;
use PhpSpec\ObjectBehavior;

final class TimeToEnrichValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromHours', [52.8]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TimeToEnrichValue::class);
    }

    public function it_returns_the_value_in_days()
    {
        $this->inDays()->shouldBe(2.20);
    }

    public function it_returns_the_value_in_hours()
    {
        $this->inHours()->shouldBe(52.8);
    }

    public function it_cannot_be_created_with_a_negative_number_of_hours()
    {
        $this->beConstructedThrough('fromHours', [-1]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_created_with_zero_hour_as_value()
    {
        $this->beConstructedThrough('fromHours', [0]);
        $this->inDays()->shouldBe((float) 0);
        $this->inHours()->shouldBe((float) 0);
    }
}
