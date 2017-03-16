<?php

namespace spec\Pim\Component\Catalog\Model;

use Pim\Component\Catalog\Model\AttributeCompleteness;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Prophecy\Argument;

class AttributeCompletenessSpec extends ObjectBehavior
{
    function let(CompletenessInterface $completeness, AttributeInterface $attribute)
    {
        $this->beConstructedWith($completeness, $attribute, true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeCompleteness::class);
    }

    function it_is_complete()
    {
        $this->isComplete()->shouldReturn(true);
    }
}
