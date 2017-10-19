<?php

namespace spec\Pim\Component\Catalog\Completeness;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\MissingRequiredAttributes\MissingRequiredValues;
use Pim\Component\Catalog\Model\AttributeInterface;

class MissingRequiredAttributesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MissingRequiredValues::class);
    }

    function it_returns_an_empty_list_of_attributes()
    {
        $this->getAttributes()->shouldReturn([]);
    }

    function it_adds_an_attribute(AttributeInterface $attribute)
    {
        $this->add($attribute);

        $this->getAttributes()->shouldReturn([$attribute]);
    }

    function it_adds_multiple_attributes(AttributeInterface $attribute1, AttributeInterface $attribute2)
    {
        $attribute1->getCode()->willReturn('attribute_1');
        $attribute2->getCode()->willReturn('attribute_2');

        $this->add($attribute1);
        $this->add($attribute2);

        $this->getAttributes()->shouldReturn([$attribute1, $attribute2]);
    }

    function it_does_not_add_multiple_time_the_same_attribute(AttributeInterface $sameAttribute)
    {
        $sameAttribute->getCode()->willReturn('sameAttributeCode');

        $this->add($sameAttribute);
        $this->add($sameAttribute);

        $this->getAttributes()->shouldReturn([$sameAttribute]);
    }

    function it_returns_the_list_of_missing_required_attribute_codes(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attribute1->getCode()->willReturn('attribute_1');
        $attribute2->getCode()->willReturn('attribute_2');

        $this->add($attribute1);
        $this->add($attribute2);

        $this->getAttributeCodes()->shouldReturn(['attribute_1', 'attribute_2']);
    }
}
