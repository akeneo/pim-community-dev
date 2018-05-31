<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Domain\Model\Record;

use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['an_identifier']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordIdentifier::class);
    }

    public function it_can_be_transformed_into_a_string()
    {
        $this->__toString()->shouldReturn('an_identifier');
    }

    public function it_is_possible_to_compare_it()
    {
        $this->equals(RecordIdentifier::fromString('an_identifier'))->shouldReturn(true);
        $this->equals(RecordIdentifier::fromString('other_identifier'))->shouldReturn(false);
    }
}
