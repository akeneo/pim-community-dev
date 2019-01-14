<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\SearchRecord;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchRecord;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SearchRecordSpec extends ObjectBehavior
{
    function let(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindRecordItemsForIdentifiersAndQueryInterface $findRecordItemsForIdentifiersAndQuery,
        CountRecordsInterface $countRecords
    ) {
        $this->beConstructedWith($findIdentifiersForQuery, $findRecordItemsForIdentifiersAndQuery, $countRecords);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchRecord::class);
    }

    function it_returns_search_result_from_a_record_query(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindRecordItemsForIdentifiersAndQueryInterface $findRecordItemsForIdentifiersAndQuery,
        CountRecordsInterface $countRecords,
        RecordItem $stark,
        RecordItem $dyson
    ) {
        $stark->normalize()->willReturn(['identifier' => 'stark']);
        $dyson->normalize()->willReturn(['identifier' => 'dyson']);
        $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            1,
            null,
            []
        );
        $identifiersResult = new IdentifiersForQueryResult(['stark', 'dyson'], 2);
        $findIdentifiersForQuery->__invoke($recordQuery)->willReturn($identifiersResult);
        $findRecordItemsForIdentifiersAndQuery->__invoke(['stark', 'dyson'], $recordQuery)
            ->willReturn([$stark, $dyson]);
        $countRecords->forReferenceEntity(
            Argument::that(
                function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                    return 'brand' === (string) $referenceEntityIdentifier;
                }
            )
        )->willReturn(10);

        $result = $this->__invoke($recordQuery);

        $result->normalize()->shouldReturn([
            'items' => [
                ['identifier' => 'stark'],
                ['identifier' => 'dyson']
            ],
            'matches_count' => 2,
            'total_count' => 10,
        ]);
    }
}
