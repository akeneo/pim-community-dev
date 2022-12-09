<?php

namespace Specification\Akeneo\Category\Domain\ValueObject;
use Akeneo\Category\Domain\ValueObject\Code;
use PhpSpec\ObjectBehavior;

class CodeSpec extends ObjectBehavior
{
    public function it_can_be_constructed_with_a_string()
    {
        $this->beConstructedWith('category_code');
        $this->shouldHaveType(Code::class);
        $this->__toString()->shouldReturn('category_code');
    }

    public function it_can_be_constructed_with_0_as_code()
    {
        $this->beConstructedWith('0');
        $this->shouldHaveType(Code::class);
        $this->__toString()->shouldReturn('0');
    }
}
