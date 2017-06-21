<?php

namespace spec\Pim\Component\Catalog\Model;

use Pim\Component\Catalog\Model\VariantAttributeSet;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VariantAttributeSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(VariantAttributeSet::class);
    }

    function it_is_a_variant_family()
    {
        $this->shouldImplement(VariantAttributeSetInterface::class);
    }
}
