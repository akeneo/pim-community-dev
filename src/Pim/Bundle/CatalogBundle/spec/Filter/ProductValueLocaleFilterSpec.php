<?php

namespace spec\Pim\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class ProductValueLocaleFilterSpec extends ObjectBehavior
{
    public function it_does_not_filter_a_product_value_if_locale_option_is_empty(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_it_is_not_in_locales_option(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isLocalizable()->willReturn(true);
        $price->getLocale()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US']])->shouldReturn(true);
    }

    public function it_does_not_filter_a_product_value_if_it_is_in_locales_options(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isLocalizable()->willReturn(false);
        $price->getLocale()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US', 'fr_FR']])->shouldReturn(false);
    }

    public function it_does_not_filter_a_product_value_if_it_is_not_scopable(ValueInterface $price, AttributeInterface $priceAttribute)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isLocalizable()->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US']])->shouldReturn(false);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:product_value:view', ['locales' => ['en_US']]]);
    }
}
