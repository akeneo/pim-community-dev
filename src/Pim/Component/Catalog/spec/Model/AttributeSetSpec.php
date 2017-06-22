<?php

namespace spec\Pim\Component\Catalog\Model;

use Pim\Component\Catalog\Model\AttributeSet;
use Pim\Component\Catalog\Model\AttributeSetInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeSet::class);
    }

    function it_is_a_variant_family()
    {
        $this->shouldImplement(AttributeSetInterface::class);
    }
}
