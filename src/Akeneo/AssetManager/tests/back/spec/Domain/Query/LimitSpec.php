<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query;

use Akeneo\AssetManager\Domain\Query\Limit;
use PhpSpec\ObjectBehavior;

class LimitSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(10);
        $this->shouldHaveType(Limit::class);
    }

    function it_returns_its_integer_value()
    {
        $this->beConstructedWith(10);
        $this->intValue()->shouldBe(10);
    }

    function it_cannot_be_created_with_zero()
    {
        $this->beConstructedWith(0);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_created_with_a_negative_number()
    {
        $this->beConstructedWith(-1);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
