<?php

namespace spec\Pim\Component\Catalog\Model;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantAttributeSet;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;

class VariantAttributeSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(VariantAttributeSet::class);
    }

    function it_is_a_family_variant()
    {
        $this->shouldImplement(VariantAttributeSetInterface::class);
    }

    function it_set_attributes_by_adding_axes(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $axis1,
        AttributeInterface $axis2
    ) {
        $this->setAxes([$axis1, $axis2]);
        $this->setAttributes([$attribute1, $attribute2]);
        $this->getAttributes()->toArray()->shouldReturn([$attribute1, $attribute2, $axis1, $axis2]);
    }
}
