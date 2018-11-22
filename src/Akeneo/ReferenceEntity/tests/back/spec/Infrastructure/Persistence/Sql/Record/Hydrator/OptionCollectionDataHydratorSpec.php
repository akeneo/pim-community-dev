<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\OptionCollectionDataHydrator;
use PhpSpec\ObjectBehavior;

class OptionCollectionDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OptionCollectionDataHydrator::class);
    }

    function it_only_supports_data_for_option_attribute(
        OptionCollectionAttribute $optionCollectionAttribute,
        TextAttribute $textAttribute
    ) {
        $this->supports($optionCollectionAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_hydrates_record_data()
    {
        $optionCollectionData = $this->hydrate(['blue', 'red']);
        $optionCollectionData->shouldBeAnInstanceOf(OptionCollectionData::class);
        $optionCollectionData->normalize()->shouldReturn(['blue', 'red']);
    }
}
