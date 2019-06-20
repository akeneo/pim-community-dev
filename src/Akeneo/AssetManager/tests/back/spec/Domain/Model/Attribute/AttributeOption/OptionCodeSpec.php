<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use PhpSpec\ObjectBehavior;

class OptionCodeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['red']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OptionCode::class);
    }

    public function it_can_be_transformed_into_a_string()
    {
        $this->__toString()->shouldReturn('red');
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('fromString', ['']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }

    public function it_cannot_be_constructed_with_invalid_caracters()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['/']);
    }
}
