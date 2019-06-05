<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use PhpSpec\ObjectBehavior;

class RecordDataSpec extends ObjectBehavior
{
    public function let(RecordCode $recordCode)
    {
        $recordCode->__toString()->willReturn('starck');

        $this->beConstructedThrough('fromRecordCode', [$recordCode]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', ['breuer']);
        $this->shouldBeAnInstanceOf(RecordData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_string()
    {
        $this->beConstructedThrough('createFromNormalize', [null]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_record_code()
    {
        $this->beConstructedThrough('createFromNormalize', ['']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('starck');
    }

    /**
     * @see https://akeneo.atlassian.net/browse/PIM-8294
     */
    public function it_can_contain_the_zero_string()
    {
        $this->beConstructedThrough('createFromNormalize', ['0']);
        $this->normalize()->shouldReturn('0');
    }
}
