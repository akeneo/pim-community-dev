<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Domain\Model\Record;

use PhpSpec\ObjectBehavior;

class RecordIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString',['a_identifier']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier::class);
    }

    public function it_is_constructed_from_string()
    {
        $this->__toString()->shouldReturn('a_identifier');
    }

    public function it_is_possible_to_compare_it()
    {
        $this->equals(\Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier::fromString('a_identifier'))->shouldReturn(true);
        $this->equals(\Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier::fromString('other_identifier'))->shouldReturn(false);
    }
}
