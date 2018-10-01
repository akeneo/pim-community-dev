<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use PhpSpec\ObjectBehavior;

class AttributeCodeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['description']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AttributeCode::class);
    }

    public function it_can_be_transformed_into_a_string()
    {
        $this->__toString()->shouldReturn('description');
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('fromString', ['badId!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('fromString', ['']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }

    public function it_is_possible_to_compare_it()
    {
        $this->equals(AttributeCode::fromString('description'))->shouldReturn(true);
        $this->equals(AttributeCode::fromString('title'))->shouldReturn(false);
    }
}
