<?php

namespace spec\Pim\Component\Catalog\Model;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantAttributeSet;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use PhpSpec\ObjectBehavior;

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

    function it_cannot_set_the_same_attribute_multiple_times(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $this->setAttributes([$attribute1, $attribute2]);
        $this->setAttributes([$attribute1, $attribute2]);

        $attributes = $this->getAttributes();
        $attributes->count()->shouldReturn(2);
        $attributes->contains($attribute1)->shouldReturn(true);
        $attributes->contains($attribute2)->shouldReturn(true);
    }

    function it_cannot_set_the_same_axis_multiple_times(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $this->setAxes([$attribute1, $attribute2]);
        $this->setAxes([$attribute1, $attribute2]);

        $axis = $this->getAxes();
        $axis->count()->shouldReturn(2);
        $axis->contains($attribute1)->shouldReturn(true);
        $axis->contains($attribute2)->shouldReturn(true);
    }

    function it_sets_axes_with_no_attributes(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3
    ) {
        $this->setAxes([$attribute1, $attribute2, $attribute3]);
        $axes = $this->getAxes();
        $axes->count()->shouldReturn(3);
        $axes->contains($attribute1)->shouldReturn(true);
        $axes->contains($attribute2)->shouldReturn(true);
        $axes->contains($attribute3)->shouldReturn(true);
    }

    function it_removes_attributes_used_as_axis_from_the_attribute_list(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3
    ) {
        $this->setAttributes([$attribute1, $attribute2, $attribute3]);

        $this->setAxes([$attribute1, $attribute2]);
        $axes = $this->getAxes();
        $axes->count()->shouldReturn(2);
        $axes->contains($attribute1)->shouldReturn(true);
        $axes->contains($attribute2)->shouldReturn(true);
        $axes->contains($attribute3)->shouldReturn(false);

        $attributes = $this->getAttributes();
        $attributes->count()->shouldReturn(1);
        $attributes->contains($attribute1)->shouldReturn(false);
        $attributes->contains($attribute2)->shouldReturn(false);
        $attributes->contains($attribute3)->shouldReturn(true);
    }

    function it_returns_the_label_of_the_axis_with_given_locale_code(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attribute1->setLocale('en_US')->shouldBeCalled();
        $attribute1->getLabel()->willReturn('Attribute 1 label');
        $attribute2->setLocale('en_US')->shouldBeCalled();
        $attribute2->getLabel()->willReturn('Attribute 2 label');

        $this->setAxes([$attribute1, $attribute2]);
        $this->getAxesLabels('en_US')->shouldReturn([
            'Attribute 1 label',
            'Attribute 2 label'
        ]);
    }
}
