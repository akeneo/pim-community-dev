<?php

namespace spec\Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\TextDataHydrator;
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

    function it_hydrates_text_data(TextAttribute $text)
    {
        $textData = $this->hydrate('A description');
        $textData->shouldBeAnInstanceOf(TextData::class);
        $textData->normalize()->shouldReturn('A description');
    }
}
