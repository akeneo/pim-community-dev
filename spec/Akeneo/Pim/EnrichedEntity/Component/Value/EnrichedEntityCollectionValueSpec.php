<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\Value;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\Pim\EnrichedEntity\Component\Value\EnrichedEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class EnrichedEntityCollectionValueSpec extends ObjectBehavior {
    function let(
        AttributeInterface $designer,
        LocaleInterface $locale,
        ChannelInterface $channel,
        Record $starck,
        Record $dyson
    ) {
        $designer->isScopable()->willReturn(true);
        $designer->isLocalizable()->willReturn(true);
        $this->beConstructedWith($designer, $locale, $channel, [$starck, $dyson]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityCollectionValue::class);
        $this->shouldHaveType(ValueInterface::class);
    }

    function it_gets_a_list_of_record($starck, $dyson)
    {
        $this->getData()->shouldReturn([$starck, $dyson]);
    }

    function it_is_castable_into_a_string(Record $starck, Record $dyson, RecordIdentifier $starckIdentifier, RecordIdentifier $dysonIdentifier)
    {
        $starck->getIdentifier()->willReturn($starckIdentifier);
        $starckIdentifier->__toString()->willReturn('starck');
        $dyson->getIdentifier()->willReturn($dysonIdentifier);
        $dysonIdentifier->__toString()->willReturn('dyson');
        $this->__toString()->shouldReturn('starck, dyson');
    }
}
