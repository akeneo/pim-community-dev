<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

class PricesComparatorSpec extends ObjectBehavior
{
    function it_get_changes_between_prices(
        AbstractProductValue $value,
        Collection $prices,
        ProductPrice $eur,
        ProductPrice $usd
    ) {
        $submittedData = [
            'id' => '123',
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
            'id' => '123',
        ]);
    }

    function it_ignores_changes_brought_on_unavailable_currencies(
        AbstractProductValue $value,
        Collection $prices,
        ProductPrice $eur
    ) {
        $submittedData = [
            'id' => '123',
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
            'id' => '123',
        ]);
    }

    function it_detects_no_changes(
        AbstractProductValue $value,
        Collection $prices,
        ProductPrice $eur
    ) {
        $submittedData = [
            'prices' => [
                'EUR' => [
                    'data' => '10',
                    'currency' => 'EUR',
                ],
            ],
            'id' => '123',
        ];

        $value->getPrices()->willReturn($prices);
        $prices->offsetGet('EUR')->willReturn($eur);
        $eur->getData()->willReturn(10);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
