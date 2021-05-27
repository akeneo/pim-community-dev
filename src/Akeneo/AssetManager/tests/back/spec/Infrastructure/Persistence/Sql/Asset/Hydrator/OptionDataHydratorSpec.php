<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\OptionDataHydrator;
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
        MediaLinkAttribute $mediaLinkAttribute
    ) {
        $this->supports($optionAttribute)->shouldReturn(true);
        $this->supports($mediaLinkAttribute)->shouldReturn(false);
    }

    function it_hydrates_option_data_if_the_option_still_exists(OptionAttribute $optionAttribute)
    {
        $optionAttribute->hasAttributeOption(
            Argument::that(fn(OptionCode $code) => 'blue' === (string) $code
            ))->willReturn(true);
        $optionCollectionData = $this->hydrate('blue', $optionAttribute);
        $optionCollectionData->shouldBeAnInstanceOf(OptionData::class);
        $optionCollectionData->normalize()->shouldReturn('blue');
    }

    function it_returns_an_empty_data_if_the_options_does_not_exist_anymore(OptionAttribute $optionAttribute)
    {
        $optionAttribute->hasAttributeOption(
            Argument::that(fn(OptionCode $code) => 'red' === (string) $code)
        )->willReturn(false);
        $optionCollectionData = $this->hydrate('red', $optionAttribute);
        $optionCollectionData->shouldBeAnInstanceOf(EmptyData::class);
    }
}
