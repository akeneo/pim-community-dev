<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordCollectionDataHydrator;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlRecordsExists;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RecordCollectionDataHydratorSpec extends ObjectBehavior
{
    function let(SqlRecordsExists $recordsExists)
    {
        $this->beConstructedWith($recordsExists);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_record_collection_data_attribute(
        RecordAttribute $recordAttribute,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $this->supports($recordAttribute)->shouldReturn(false);
        $this->supports($recordCollectionAttribute)->shouldReturn(true);
    }

    function it_hydrates_record_collection_data_only_if_the_records_still_exists(
        SqlRecordsExists $recordsExists,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('referenceEntityType');
        $recordCollectionAttribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $recordsExists->withReferenceEntityAndCodes($referenceEntityIdentifier, ['phillipe_starck', 'patricia_urquiola'])
            ->willReturn(['phillipe_starck', 'patricia_urquiola']);
        $recordData = $this->hydrate(['phillipe_starck', 'patricia_urquiola'], $recordCollectionAttribute);
        $recordData->shouldBeAnInstanceOf(RecordCollectionData::class);
        $recordData->normalize()->shouldReturn(['phillipe_starck', 'patricia_urquiola']);
    }

    function it_returns_an_empty_data_if_none_of_the_records_still_exists(
        SqlRecordsExists $recordsExists,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('referenceEntityType');
        $recordCollectionAttribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $recordsExists->withReferenceEntityAndCodes($referenceEntityIdentifier, ['phillipe_starck', 'patricia_urquiola'])
            ->willReturn([]);
        $recordData = $this->hydrate(['phillipe_starck', 'patricia_urquiola'], $recordCollectionAttribute);
        $recordData->shouldBeAnInstanceOf(EmptyData::class);
    }
}
