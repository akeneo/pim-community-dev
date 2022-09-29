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

namespace Specification\Akeneo\PerformanceAnalytics\Domain;

use PhpSpec\ObjectBehavior;

final class PeriodTypeSpec extends ObjectBehavior
{
    public function it_creates_a_day_period_type()
    {
        $this->beConstructedThrough('day');
        $this->toString()->shouldReturn('day');
    }

    public function it_creates_a_day_period_type_from_string()
    {
        $this->beConstructedThrough('fromString', ['day']);
        $this->toString()->shouldReturn('day');
    }

    public function it_creates_a_week_period_type()
    {
        $this->beConstructedThrough('week');
        $this->toString()->shouldReturn('week');
    }

    public function it_creates_a_week_period_type_from_string()
    {
        $this->beConstructedThrough('fromString', ['week']);
        $this->toString()->shouldReturn('week');
    }

    public function it_creates_a_month_period_type()
    {
        $this->beConstructedThrough('month');
        $this->toString()->shouldReturn('month');
    }

    public function it_creates_a_month_period_type_from_string()
    {
        $this->beConstructedThrough('fromString', ['month']);
        $this->toString()->shouldReturn('month');
    }

    public function it_creates_a_year_period_type()
    {
        $this->beConstructedThrough('year');
        $this->toString()->shouldReturn('year');
    }

    public function it_creates_a_year_period_type_from_string()
    {
        $this->beConstructedThrough('fromString', ['year']);
        $this->toString()->shouldReturn('year');
    }

    public function it_cannot_be_constructed_with_unknown_period_type()
    {
        $this->beConstructedThrough('fromString', ['unknown']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
