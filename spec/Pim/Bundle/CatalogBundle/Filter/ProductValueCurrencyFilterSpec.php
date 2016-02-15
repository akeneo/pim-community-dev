<?php

namespace spec\Pim\Bundle\CatalogBundle\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class ProductValueCurrencyFilterSpec extends ObjectBehavior
{
    public function it_filters_a_product_value_if_it_is_not_in_currency_option(
        ProductValueInterface $price
    ) {

        $priceAttributeEur = new ProductPrice(111, 'EUR');
        $priceAttributeUsd = new ProductPrice(666, 'USD');

        $price->getPrices()->willReturn(new ArrayCollection([
            $priceAttributeEur,
            $priceAttributeUsd
        ]));

        $price->removePrice($priceAttributeEur)->shouldNotBeCalled();
        $price->removePrice($priceAttributeUsd)->shouldBeCalled();

        $options = [
            'currencies' => ['EUR']
        ];
        $this->filterObject($price, 'pim.internal_api.product_value.view', $options)->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_empty_currency_option(
        ProductValueInterface $price
    ) {

        $priceAttributeEur = new ProductPrice(111, 'EUR');
        $priceAttributeUsd = new ProductPrice(666, 'USD');

        $price->getPrices()->willReturn(new ArrayCollection([
            $priceAttributeEur,
            $priceAttributeUsd
        ]));

        $price->removePrice($priceAttributeEur)->shouldBeCalled();
        $price->removePrice($priceAttributeUsd)->shouldBeCalled();

        $options = [];
        $this->filterObject($price, 'pim.internal_api.product_value.view', $options)->shouldReturn(false);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')
            ->during(
                'filterObject',
                [
                    $anOtherObject,
                    'pim.internal_api.product_value.view',
                    []
                ]
            );
    }
}
