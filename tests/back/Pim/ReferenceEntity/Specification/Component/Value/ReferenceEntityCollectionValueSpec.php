<?php

namespace Specification\Akeneo\Pim\ReferenceEntity\Component\Value;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\Pim\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ReferenceEntityCollectionValueSpec extends ObjectBehavior {
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
        $this->shouldHaveType(ReferenceEntityCollectionValue::class);
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
