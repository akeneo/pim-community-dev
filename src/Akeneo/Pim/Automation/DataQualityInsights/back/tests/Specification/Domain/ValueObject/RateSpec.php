<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use PhpSpec\ObjectBehavior;

class RateSpec extends ObjectBehavior
{
    public function it_throws_an_exception_if_the_rate_is_lesser_than_zero()
    {
        $this->beConstructedWith(-1);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_rate_is_greater_than_100()
    {
        $this->beConstructedWith(101);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_represents_a_rate_equal_to_100_by_the_letter_A()
    {
        $this->beConstructedWith(100);
        $this->__toString()->shouldReturn('A');
    }

    public function it_represents_a_rate_equal_to_90_by_the_letter_A()
    {
        $this->beConstructedWith(90);
        $this->__toString()->shouldReturn('A');
    }

    public function it_represents_a_rate_equal_to_89_by_the_letter_B()
    {
        $this->beConstructedWith(89);
        $this->__toString()->shouldReturn('B');
    }

    public function it_represents_a_rate_equal_to_80_by_the_letter_B()
    {
        $this->beConstructedWith(80);
        $this->__toString()->shouldReturn('B');
    }

    public function it_represents_a_rate_equal_to_79_by_the_letter_C()
    {
        $this->beConstructedWith(79);
        $this->__toString()->shouldReturn('C');
    }

    public function it_represents_a_rate_equal_to_70_by_the_letter_C()
    {
        $this->beConstructedWith(70);
        $this->__toString()->shouldReturn('C');
    }

    public function it_represents_a_rate_equal_to_69_by_the_letter_D()
    {
        $this->beConstructedWith(69);
        $this->__toString()->shouldReturn('D');
    }

    public function it_represents_a_rate_equal_to_60_by_the_letter_D()
    {
        $this->beConstructedWith(60);
        $this->__toString()->shouldReturn('D');
    }

    public function it_represents_a_rate_equal_to_59_by_the_letter_E()
    {
        $this->beConstructedWith(59);
        $this->__toString()->shouldReturn('E');
    }

    public function it_represents_a_rate_equal_to_0_by_the_letter_E()
    {
        $this->beConstructedWith(0);
        $this->__toString()->shouldReturn('E');
    }
}
