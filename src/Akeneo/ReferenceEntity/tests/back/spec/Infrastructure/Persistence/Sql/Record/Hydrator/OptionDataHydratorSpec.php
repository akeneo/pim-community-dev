<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\OptionDataHydrator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_hydrates_option_data_if_the_option_still_exists(OptionAttribute $optionAttribute)
    {
        $optionAttribute->hasAttributeOption(
            Argument::that(function (OptionCode $code) {
                return 'blue' === (string) $code;
            }
            ))->willReturn(true);
        $optionCollectionData = $this->hydrate('blue', $optionAttribute);
        $optionCollectionData->shouldBeAnInstanceOf(OptionData::class);
        $optionCollectionData->normalize()->shouldReturn('blue');
    }

    function it_returns_an_empty_data_if_the_options_does_not_exist_anymore(OptionAttribute $optionAttribute)
    {
        $optionAttribute->hasAttributeOption(
            Argument::that(function (OptionCode $code) {
                return 'red' === (string) $code;
            })
        )->willReturn(false);
        $optionCollectionData = $this->hydrate('red', $optionAttribute);
        $optionCollectionData->shouldBeAnInstanceOf(EmptyData::class);
    }
}
