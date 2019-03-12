<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\OptionCollectionDataHydrator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_hydrates_option_collection_data_if_the_option_still_exists(OptionCollectionAttribute $optionCollectionAttribute)
    {
        $optionCollectionAttribute->normalize()->willReturn(['options' => [['code' => 'blue'], ['code' => 'red']]]);
        $optionCollectionData = $this->hydrate(['blue', 'red'], $optionCollectionAttribute);
        $optionCollectionData->shouldBeAnInstanceOf(OptionCollectionData::class);
        $optionCollectionData->normalize()->shouldReturn(['blue', 'red']);
    }

    function it_returns_an_empty_data_if_the_options_does_not_exist_anymore(OptionCollectionAttribute $optionCollectionAttribute)
    {
        $optionCollectionAttribute->normalize()->willReturn(['options' => []]);
        $optionCollectionData = $this->hydrate(['blue', 'red'], $optionCollectionAttribute);
        $optionCollectionData->shouldBeAnInstanceOf(EmptyData::class);
    }
}
