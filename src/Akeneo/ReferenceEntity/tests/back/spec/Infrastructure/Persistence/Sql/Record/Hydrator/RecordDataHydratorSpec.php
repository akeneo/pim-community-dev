<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDataHydrator;
use PhpSpec\ObjectBehavior;

class RecordDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_record_data_attribute(
        RecordAttribute $recordAttribute,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $this->supports($recordAttribute)->shouldReturn(true);
        $this->supports($recordCollectionAttribute)->shouldReturn(false);
    }

    function it_hydrates_record_data()
    {
        $recordData = $this->hydrate('phillipe_starck');
        $recordData->shouldBeAnInstanceOf(RecordData::class);
        $recordData->normalize()->shouldReturn('phillipe_starck');
    }
}
