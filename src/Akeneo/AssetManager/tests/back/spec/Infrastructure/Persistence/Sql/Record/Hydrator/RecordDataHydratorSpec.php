<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDataHydrator;
use PhpSpec\ObjectBehavior;

class RecordDataHydratorSpec extends ObjectBehavior
{
    function let(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->beConstructedWith($findCodesByIdentifiers);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_record_data_attribute(
        RecordAttribute $recordAttribute,
        TextAttribute $textAttribute
    ) {
        $this->supports($recordAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_hydrates_record_collection_data_only_if_the_records_still_exists(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        RecordAttribute $recordAttribute
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('referenceEntityType');
        $recordAttribute->getRecordType()->willReturn($referenceEntityIdentifier);

        $findCodesByIdentifiers
            ->find(['phillipe_starck_123456'])
            ->willReturn(['phillipe_starck']);

        $recordData = $this->hydrate('phillipe_starck_123456', $recordAttribute);
        $recordData->shouldBeAnInstanceOf(RecordData::class);
        $recordData->normalize()->shouldReturn('phillipe_starck');
    }

    function it_returns_an_empty_data_if_the_records_does_not_exists_anymore(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        RecordAttribute $recordAttribute
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('referenceEntityType');
        $recordAttribute->getRecordType()->willReturn($referenceEntityIdentifier);

        $findCodesByIdentifiers
            ->find(['phillipe_starck_123456'])
            ->willReturn([]);

        $recordData = $this->hydrate('phillipe_starck_123456', $recordAttribute);
        $recordData->shouldBeAnInstanceOf(EmptyData::class);
    }
}
