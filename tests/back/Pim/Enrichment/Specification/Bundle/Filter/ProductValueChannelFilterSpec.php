<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ProductValueChannelFilterSpec extends ObjectBehavior
{
    public function it_does_not_filter_a_product_value_if_channel_option_is_empty(ValueInterface $price)
    {
        $price->getLocaleCode()->willReturn(null);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_it_is_not_in_channels_option(ValueInterface $price)
    {
        $price->isScopable()->willReturn(true);
        $price->getScopeCode()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US']])->shouldReturn(true);
    }

    public function it_does_not_filter_a_product_value_if_it_is_in_channels_options(ValueInterface $price)
    {
        $price->isScopable()->willReturn(false);
        $price->getScopeCode()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US', 'fr_FR']])->shouldReturn(false);
    }

    public function it_does_not_filter_a_product_value_if_it_is_not_scopable(ValueInterface $price)
    {
        $price->isScopable()->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US']])->shouldReturn(false);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]]);
    }
}
