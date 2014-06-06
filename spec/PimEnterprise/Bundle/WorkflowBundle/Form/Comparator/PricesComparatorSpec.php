<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model;

class PricesComparatorSpec extends ObjectBehavior
{
    function let(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getId()->willReturn(713705);
        $value->getScope()->willReturn('ecommerce');
        $attribute->getId()->willReturn(1337);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_prices_collection_type($value, $attribute) {
        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_prices_data(
        $value,
        Collection $prices,
        Model\ProductPrice $eur,
        Model\ProductPrice $usd
    ) {
        $submittedData = [
            'prices' => [
                'EUR' => [
                    'data' => '10',
                    'currency' => 'EUR',
                ],
                'USD' => [
                    'data' => '20',
                    'currency' => 'USD',
                ],
            ],
        ];

        $value->getPrices()->willReturn($prices);
        $prices->offsetGet('EUR')->willReturn($eur);
        $prices->offsetGet('USD')->willReturn($usd);
        $eur->getData()->willReturn(10);
        $usd->getData()->willReturn(30);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'prices' => [
                'USD' => [
                    'data' => '20',
                    'currency' => 'USD',
                ],
            ],
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
            ],
        ]);
    }

    function it_ignores_changes_brought_on_unavailable_currencies(
        $value,
        Collection $prices,
        Model\ProductPrice $eur
    ) {
        $submittedData = [
            'prices' => [
                'EUR' => [
                    'data' => '10',
                    'currency' => 'EUR',
                ],
                'USD' => [
                    'data' => '20',
                    'currency' => 'USD',
                ],
            ],
        ];

        $value->getPrices()->willReturn($prices);
        $prices->offsetGet('EUR')->willReturn($eur);
        $prices->offsetGet('USD')->willReturn(null);
        $eur->getData()->willReturn(20);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'prices' => [
                'EUR' => [
                    'data' => '10',
                    'currency' => 'EUR',
                ],
            ],
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
            ],
        ]);
    }

    function it_detects_no_changes(
        $value,
        Collection $prices,
        Model\ProductPrice $eur
    ) {
        $submittedData = [
            'prices' => [
                'EUR' => [
                    'data' => '10',
                    'currency' => 'EUR',
                ],
            ],
        ];

        $value->getPrices()->willReturn($prices);
        $prices->offsetGet('EUR')->willReturn($eur);
        $eur->getData()->willReturn(10);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_change_when_the_new_prices_are_not_defined(
        $value
    ) {
        $submittedData = [];

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
