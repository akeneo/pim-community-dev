<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class RankSpec extends ObjectBehavior
{
    public function it_can_be_constructed_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['rank_2']);

        $this->__toString()->shouldReturn('rank_2');
    }

    public function it_can_be_constructed_from_an_integer()
    {
        $this->beConstructedThrough('fromInt', [3]);

        $this->__toString()->shouldReturn('rank_3');
    }

    public function it_can_be_constructed_from_a_rate_of_rank_1()
    {
        $this->beConstructedThrough('fromRate', [new Rate(90)]);

        $this->__toString()->shouldReturn('rank_1');
        $this->toInt()->shouldReturn(1);
        $this->toLetter()->shouldReturn('A');
    }

    public function it_can_be_constructed_from_a_rate_of_rank_2()
    {
        $this->beConstructedThrough('fromRate', [new Rate(80)]);

        $this->__toString()->shouldReturn('rank_2');
        $this->toInt()->shouldReturn(2);
        $this->toLetter()->shouldReturn('B');
    }

    public function it_can_be_constructed_from_a_rate_of_rank_3()
    {
        $this->beConstructedThrough('fromRate', [new Rate(70)]);

        $this->__toString()->shouldReturn('rank_3');
        $this->toInt()->shouldReturn(3);
        $this->toLetter()->shouldReturn('C');
    }

    public function it_can_be_constructed_from_a_rate_of_rank_4()
    {
        $this->beConstructedThrough('fromRate', [new Rate(60)]);

        $this->__toString()->shouldReturn('rank_4');
        $this->toInt()->shouldReturn(4);
        $this->toLetter()->shouldReturn('D');
    }

    public function it_can_be_constructed_from_a_rate_of_rank_5()
    {
        $this->beConstructedThrough('fromRate', [new Rate(59)]);

        $this->__toString()->shouldReturn('rank_5');
        $this->toInt()->shouldReturn(5);
        $this->toLetter()->shouldReturn('E');
    }

    public function it_throws_an_exception_if_the_code_is_invalid()
    {
        $this->beConstructedThrough('fromString', ['rang_1']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_integer_value_is_greater_than_five()
    {
        $this->beConstructedThrough('fromInt', [6]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_integer_value_is_lower_than_one()
    {
        $this->beConstructedThrough('fromString', ['rank_0']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
