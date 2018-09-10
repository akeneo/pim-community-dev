<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ProductValueLocaleFilterSpec extends ObjectBehavior
{
    public function it_does_not_filter_a_product_value_if_locale_option_is_empty(ValueInterface $price)
    {
        $price->getLocaleCode()->willReturn(null);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_it_is_not_in_locales_option(ValueInterface $price)
    {
        $price->isLocalizable()->willReturn(true);
        $price->getLocaleCode()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US']])->shouldReturn(true);
    }

    public function it_does_not_filter_a_product_value_if_it_is_in_locales_options(ValueInterface $price)
    {
        $price->isLocalizable()->willReturn(false);
        $price->getLocaleCode()->willReturn('fr_FR');

        $this->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US', 'fr_FR']])->shouldReturn(false);
    }

    public function it_does_not_filter_a_product_value_if_it_is_not_scopable(ValueInterface $price)
    {
        $price->isLocalizable()->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US']])->shouldReturn(false);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:product_value:view', ['locales' => ['en_US']]]);
    }
}
