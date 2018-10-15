<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use PhpSpec\ObjectBehavior;

class RecordCollectionDataSpec extends ObjectBehavior
{
    public function let(
        RecordCode $starckRecordCode,
        RecordCode $breuerRecordCode
    ) {
        $starckRecordCode->__toString()->willReturn('starck');
        $breuerRecordCode->__toString()->willReturn('breuer');

        $this->beConstructedThrough('fromRecordCodes', [[$starckRecordCode, $breuerRecordCode]]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', [['breuer', 'paul']]);
        $this->shouldBeAnInstanceOf(RecordCollectionData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_array()
    {
        $this->beConstructedThrough('createFromNormalize', ['Hello']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_array()
    {
        $this->beConstructedThrough('createFromNormalize', [[]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_something_else_than_a_record_code(
        RecordIdentifier $starckRecordIdentifier
    ) {
        $this->beConstructedThrough('fromRecordCodes', [[$starckRecordIdentifier]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(['starck', 'breuer']);
    }
}
