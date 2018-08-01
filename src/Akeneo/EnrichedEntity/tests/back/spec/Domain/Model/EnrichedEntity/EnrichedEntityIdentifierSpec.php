<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use PhpSpec\ObjectBehavior;

class EnrichedEntityIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['an_identifier_55']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityIdentifier::class);
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('fromString', ['Industry!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_can_be_transformed_into_a_string()
    {
        $this->__toString()->shouldReturn('an_identifier_55');
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }

    public function it_is_possible_to_compare_it()
    {
        $this->equals(EnrichedEntityIdentifier::fromString('an_identifier_55'))->shouldReturn(true);
        $this->equals(EnrichedEntityIdentifier::fromString('other_identifier'))->shouldReturn(false);
    }
}
