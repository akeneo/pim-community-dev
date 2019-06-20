<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\TextDataHydrator;
use PhpSpec\ObjectBehavior;

class TextDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TextDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_text_attribute(
        TextAttribute $text,
        ImageAttribute $image
    ) {
        $this->supports($text)->shouldReturn(true);
        $this->supports($image)->shouldReturn(false);
    }

    function it_hydrates_text_data(TextAttribute $textAttribute)
    {
        $textData = $this->hydrate('A description', $textAttribute);
        $textData->shouldBeAnInstanceOf(TextData::class);
        $textData->normalize()->shouldReturn('A description');
    }
}
