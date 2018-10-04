<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;

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

    function it_keeps_axes_when_setting_attributes(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $axis1,
        AttributeInterface $axis2
    ) {
        $attribute1->getCode()->willReturn('attribute_1');
        $attribute2->getCode()->willReturn('attribute_2');
        $axis1->getCode()->willReturn('axis_1');
        $axis2->getCode()->willReturn('axis_2');
        $this->setAxes([$axis1, $axis2]);
        $this->setAttributes([$attribute1, $attribute2]);
        $this->getAttributes()->toArray()->shouldReturn([$attribute1, $attribute2, $axis1, $axis2]);
    }

    function it_does_not_add_axis_when_adding_attribute_with_same_code(
        AttributeInterface $axis1,
        AttributeInterface $attribute1
    ) {
        $axis1->getCode()->willReturn('axis_1');
        $attribute1->getCode()->willReturn('axis_1');
        $this->setAxes([$axis1]);
        $this->setAttributes([$attribute1]);
        $this->getAttributes()->toArray()->shouldReturn([$attribute1]);
        $this->getAxes()->toArray()->shouldReturn([$axis1]);
    }

    function it_keeps_attributes_when_setting_axes(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $axis1,
        AttributeInterface $axis2
    ) {
        $attribute1->getCode()->willReturn('attribute_1');
        $attribute2->getCode()->willReturn('attribute_2');
        $axis1->getCode()->willReturn('axis_1');
        $axis2->getCode()->willReturn('axis_2');

        $this->setAttributes([$attribute1, $attribute2]);
        $this->setAxes([$axis1, $axis2]);
        $this->getAttributes()->toArray()->shouldReturn([$attribute1, $attribute2, $axis1, $axis2]);
    }

    function it_removes_axes_in_attributes_when_updating_axes(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $axis1,
        AttributeInterface $axis2,
        AttributeInterface $axis3,
        AttributeInterface $axis4
    ) {
        $attribute1->getCode()->willReturn('attribute_1');
        $attribute2->getCode()->willReturn('attribute_2');
        $axis1->getCode()->willReturn('axis_1');
        $axis2->getCode()->willReturn('axis_2');
        $axis3->getCode()->willReturn('axis_3');
        $axis4->getCode()->willReturn('axis_4');

        $this->setAttributes([$attribute1, $attribute2]);
        $this->setAxes([$axis1, $axis2]);
        $this->getAttributes()->toArray()->shouldReturn([$attribute1, $attribute2, $axis1, $axis2]);

        $this->setAxes([$axis3, $axis4]);

        $this->getAttributes()->toArray()->shouldHaveCount(4);
        $this->getAttributes()->toArray()->shouldContain($attribute1);
        $this->getAttributes()->toArray()->shouldContain($attribute2);
        $this->getAttributes()->toArray()->shouldContain($axis3);
        $this->getAttributes()->toArray()->shouldContain($axis4);
    }

    function it_does_not_add_attribute_when_adding_axis_with_same_code(
        AttributeInterface $axis1,
        AttributeInterface $attribute1
    ) {
        $axis1->getCode()->willReturn('axis_1');
        $attribute1->getCode()->willReturn('axis_1');
        $this->setAttributes([$attribute1]);
        $this->setAxes([$axis1]);
        $this->getAttributes()->toArray()->shouldReturn([$attribute1]);
        $this->getAxes()->toArray()->shouldReturn([$axis1]);
    }
}
