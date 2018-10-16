<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordCollectionDataHydrator;
use PhpSpec\ObjectBehavior;

class RecordCollectionDataHydratorSpec extends ObjectBehavior
{
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

    function it_hydrates_record_collection_data()
    {
        $recordData = $this->hydrate(['phillipe_starck', 'patricia_urquiola']);
        $recordData->shouldBeAnInstanceOf(RecordCollectionData::class);
        $recordData->normalize()->shouldReturn(['phillipe_starck', 'patricia_urquiola']);
    }
}
