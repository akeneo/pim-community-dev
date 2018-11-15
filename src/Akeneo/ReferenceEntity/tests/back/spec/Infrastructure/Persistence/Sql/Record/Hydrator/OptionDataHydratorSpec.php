<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\OptionDataHydrator;
use PhpSpec\ObjectBehavior;

class OptionDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OptionDataHydrator::class);
    }

    function it_only_supports_data_for_option_attribute(
        OptionAttribute $optionAttribute,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $this->supports($optionAttribute)->shouldReturn(true);
        $this->supports($recordCollectionAttribute)->shouldReturn(false);
    }

    function it_hydrates_record_data()
    {
        $optionData = $this->hydrate('blue');
        $optionData->shouldBeAnInstanceOf(OptionData::class);
        $optionData->normalize()->shouldReturn('blue');
    }
}
