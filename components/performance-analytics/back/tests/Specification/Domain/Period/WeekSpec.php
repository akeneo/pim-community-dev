<?php

namespace Specification\Akeneo\PerformanceAnalytics\Domain\Period;

use PhpSpec\ObjectBehavior;

class WeekSpec extends ObjectBehavior
{
    public function it_returns_a_formatted_week()
    {
        $this->beConstructedThrough('fromDate', [new \DateTimeImmutable('2022-01-15')]);
        $this->toString()->shouldReturn('2022-W02');
    }

    public function it_returns_a_formatted_week_for_the_first_january()
    {
        $this->beConstructedThrough('fromDate', [new \DateTimeImmutable('2022-01-01')]);
        $this->toString()->shouldReturn('2021-W52');
    }

    public function it_can_be_created_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['2021-W52']);
        $this->toString()->shouldReturn('2021-W52');
    }

    public function it_can_not_be_created_from_an_invalid_format()
    {
        $this->beConstructedThrough('fromString', ['2022-02']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
