<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use PhpSpec\ObjectBehavior;

class AttributeOrderSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromInteger', [0]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOrder::class);
    }

    public function it_can_be_transformed_into_a_integer()
    {
        $this->intValue()->shouldReturn(0);
    }

    public function it_cannot_be_constructed_with_negative_integers()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromInteger', [-1]);
    }

    public function it_is_possible_to_compare_it()
    {
        $sameOrder = AttributeOrder::fromInteger(0);
        $this->equals($sameOrder)->shouldReturn(true);

        $otherOrder = AttributeOrder::fromInteger(2);
        $this->equals($otherOrder)->shouldReturn(false);
    }
}
