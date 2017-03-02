<?php

namespace spec\Pim\Component\Structuring;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Classification\CategoryInterface;
use Pim\Component\Structuring\SubjectValue;
use Pim\Component\Structuring\SubjectValueInterface;
use Pim\Component\Structuring\ValueInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertySpec extends ObjectBehavior
{
    function let(CategoryInterface $category, AttributeInterface $attribute, ValueInterface $value)
    {
        $this->beConstructedWith($category, $attribute, $value);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Property::class);
    }

    function it_is_a_subject_value()
    {
        $this->shouldImplement(PropertyInterface::class);
    }

    function it_has_a_subject($category)
    {
        $this->getSubject()->shouldReturn($category);
    }

    function it_has_a_value($value)
    {
        $this->getValue()->shouldReturn($value);
    }

    // Do we need attributes or constant ?
    function it_has_a_Type($attribute)
    {
        $this->getType()->shouldReturn($attribute);
    }
}
