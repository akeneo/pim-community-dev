<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\ReferenceEntity\Component\Value;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class ReferenceEntityValueSpec extends ObjectBehavior
{
    function let(
        AttributeInterface $brand,
        LocaleInterface $locale,
        ChannelInterface $channel,
        Record $adidas,
        RecordIdentifier $adidasIdentifier
    ) {
        $brand->isScopable()->willReturn(true);
        $brand->isLocalizable()->willReturn(true);

        $adidas->getIdentifier()->willReturn($adidasIdentifier);
        $adidasIdentifier->__toString()->willReturn('adidas');

        $this->beConstructedWith(
            $brand,
            $channel,
            $locale,
            $adidas
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityValue::class);
        $this->shouldHaveType(ValueInterface::class);
    }

    function it_gets_a_record_as_data(Record $adidas)
    {
        $this->getData()->shouldReturn($adidas);
    }

    function it_can_be_casted_as_a_string()
    {
        $this->__toString()->shouldReturn('adidas');
    }
}
