<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use PhpSpec\ObjectBehavior;

class AttributeIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', ['an_enriched_identifier', 'description']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIdentifier::class);
    }

    public function it_cannot_be_constructed_with_empty_strings()
    {
        $this->beConstructedThrough('create', ['', '']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('create', ['badId!', 'description']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('create', ['valid_identifier', 'badId!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['enriched_entity_identifier', '']);
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['', 'description']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [str_repeat('a', 256), 'description']);
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['enriched_entity_identifier', str_repeat('a', 256)]);
    }

    public function it_is_possible_to_compare_it()
    {
        $sameIdentifier = AttributeIdentifier::create(
            'an_enriched_identifier',
            'description'
        );
        $differentIdentifier = AttributeIdentifier::create(
            'an_other_enriched_entity_identifier',
            'title'
        );
        $this->equals($sameIdentifier)->shouldReturn(true);
        $this->equals($differentIdentifier)->shouldReturn(false);
    }

    public function it_normalize_itself()
    {
        $this->normalize()->shouldReturn([
            'enriched_entity_identifier' => 'an_enriched_identifier',
            'identifier' => 'description'
        ]);
    }
}
