<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

class PricesPresenterSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_prices_key()
    {
        $this->supportsChange(['prices' => 'foo'])->shouldBe(true);
    }

    function it_presents_metric_change_using_the_injected_renderer(
        $renderer,
        $factory,
        \Diff $diff,
        Collection $collection,
        ProductPrice $eur,
        ProductPrice $usd,
        ProductPrice $gbp
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([
            $eur->getWrappedObject(),
            $gbp->getWrappedObject(),
            $usd->getWrappedObject()
        ]));
        $eur->getData()->willReturn(15);
        $eur->getCurrency()->willReturn('EUR');
        $usd->getData()->willReturn(22);
        $usd->getCurrency()->willReturn('USD');
        $gbp->getData()->willReturn(null);
        $gbp->getCurrency()->willReturn('GBP');

        $change = [
            'prices' => [
                'EUR' => [
                    'currency' => 'EUR',
                    'data' => '12',
                ],
                'GBP' => [
                    'currency' => 'GBP',
                    'data' => '25',
                ],
                'USD' => [
                    'currency' => 'USD',
                    'data' => '20',
                ],
            ]
        ];

        $factory->create(['15 EUR', '22 USD'], ['12 EUR', '25 GBP', '20 USD'])->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between two price collections');

        $this->present($collection, $change)->shouldReturn('diff between two price collections');
    }
}
