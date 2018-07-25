<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ProductValueChannelFilterSpec extends ObjectBehavior
{
    public function it_does_not_filter_a_product_value_if_channel_option_is_empty(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_it_is_not_in_channels_option(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isScopable()->willReturn(true);
        $price->getScope()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US']])->shouldReturn(true);
    }

    public function it_does_not_filter_a_product_value_if_it_is_in_channels_options(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isScopable()->willReturn(false);
        $price->getScope()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US', 'fr_FR']])->shouldReturn(false);
    }

    public function it_does_not_filter_a_product_value_if_it_is_not_scopable(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isScopable()->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US']])->shouldReturn(false);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]]);
    }
}
