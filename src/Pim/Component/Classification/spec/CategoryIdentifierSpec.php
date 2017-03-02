<?php

namespace spec\Pim\Component\Classification;

use Pim\Component\Classification\CategoryIdenfier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryIdenfierSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('my_category_code');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryIdenfierSpec::class);
    }

    function it_prints_the_category_identifier()
    {
        $this->__toString()->shouldReturn('my_category_code');
    }
}
