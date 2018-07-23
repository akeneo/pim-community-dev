<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record;

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('from', ['an_enriched_identifier', 'a_record_identifier']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordIdentifier::class);
    }

    public function it_cannot_be_constructed_with_empty_strings()
    {
        $this->beConstructedThrough('from', ['', '']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('from', ['badId!', 'record_identifier']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('from', ['valid_identifier', 'badId!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_is_possible_to_compare_it()
    {
        $sameIdentifier = RecordIdentifier::from(
            'an_enriched_identifier',
            'a_record_identifier'
        );
        $differentIdentifier = RecordIdentifier::from(
            'an_other_enriched_entity_identifier',
            'other_record_identifier'
        );
        $this->equals($sameIdentifier)->shouldReturn(true);
        $this->equals($differentIdentifier)->shouldReturn(false);
    }
}
