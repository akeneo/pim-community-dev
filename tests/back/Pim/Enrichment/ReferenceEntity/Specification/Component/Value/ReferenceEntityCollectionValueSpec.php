<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ReferenceEntityCollectionValueSpec extends ObjectBehavior {
    function let(
        RecordCode $starckCode,
        RecordCode $dysonCode
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['designer', [$starckCode, $dysonCode], 'ecommerce', 'en_US']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionValue::class);
        $this->shouldHaveType(ValueInterface::class);
    }

    function it_gets_a_list_of_record($starckCode, $dysonCode)
    {
        $this->getData()->shouldReturn([$starckCode, $dysonCode]);
    }

    function it_is_castable_into_a_string(RecordCode $starckCode, RecordCode $dysonCode)
    {
        $starckCode->__toString()->willReturn('starck');
        $dysonCode->__toString()->willReturn('dyson');

        $this->__toString()->shouldReturn('starck, dyson');
    }
}
