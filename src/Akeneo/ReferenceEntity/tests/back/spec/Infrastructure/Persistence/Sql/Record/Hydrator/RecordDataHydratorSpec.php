<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDataHydrator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RecordDataHydratorSpec extends ObjectBehavior
{
    function let(RecordExistsInterface $recordExists)
    {
        $this->beConstructedWith($recordExists);
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
        RecordExistsInterface $recordExists,
        RecordAttribute $recordAttribute
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('referenceEntityType');
        $recordAttribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $recordExists->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            Argument::that(function (RecordCode $recordCode) {
                return 'phillipe_starck' === $recordCode->normalize();
            })
        )->willReturn(true);
        $recordData = $this->hydrate('phillipe_starck', $recordAttribute);
        $recordData->shouldBeAnInstanceOf(RecordData::class);
        $recordData->normalize()->shouldReturn('phillipe_starck');
    }

    function it_returns_an_empty_data_if_the_records_does_not_exists_anymore(
        RecordExistsInterface $recordExists,
        RecordAttribute $recordAttribute
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('referenceEntityType');
        $recordAttribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $recordExists->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            Argument::that(function (RecordCode $recordCode) {
                return 'phillipe_starck' === $recordCode->normalize() || 'patricia_urquiola' === $recordCode->normalize();
            })
        )->willReturn(false);
        $recordData = $this->hydrate('phillipe_starck', $recordAttribute);
        $recordData->shouldBeAnInstanceOf(EmptyData::class);
    }
}
