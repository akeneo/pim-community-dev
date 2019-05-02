<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\NumberData;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\NumberDataHydrator;
use PhpSpec\ObjectBehavior;

class NumberDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_text_attribute(
        TextAttribute $text,
        NumberAttribute $number
    ) {
        $this->supports($text)->shouldReturn(false);
        $this->supports($number)->shouldReturn(true);
    }

    function it_hydrates_number_data(NumberAttribute $numberAttribute)
    {
        $textData = $this->hydrate('332', $numberAttribute);
        $textData->shouldBeAnInstanceOf(NumberData::class);
        $textData->normalize()->shouldReturn('332');
    }
}
