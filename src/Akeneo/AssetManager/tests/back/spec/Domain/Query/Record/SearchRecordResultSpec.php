<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchRecordResult;
use PhpSpec\ObjectBehavior;

class SearchRecordResultSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], 5, 10);
        $this->shouldHaveType(SearchRecordResult::class);
    }

    function it_normalizes_itself(RecordItem $recordItem)
    {
        $this->beConstructedWith([$recordItem], 1, 2);
        $recordItem->normalize()->willReturn(['identifier' => 'record_identifier']);
        $this->normalize()->shouldReturn([
            'items'         => [
                [
                    'identifier' => 'record_identifier',
                ],
            ],
            'matches_count' => 1,
            'total_count'   => 2,
        ]);
    }

    function it_can_be_constructed_only_with_a_list_of_record_items()
    {
        $this->beConstructedWith([1], 5, 10);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
