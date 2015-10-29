<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class PricesPresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_price()
    {
        $this->supportsChange('pim_catalog_price_collection')->shouldBe(true);
    }

    function it_presents_prices_change_using_the_injected_renderer(
        RendererInterface $renderer,
        Model\ProductValueInterface $value,
        Collection $collection,
        Model\ProductPrice $eur,
        Model\ProductPrice $usd,
        Model\ProductPrice $gbp
    ) {
        $value->getData()->willReturn($collection);
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
            'data' => [
                [
                    'currency' => 'EUR',
                    'data' => '12',
                ],
                [
                    'currency' => 'GBP',
                    'data' => '25',
                ],
                [
                    'currency' => 'USD',
                    'data' => '20',
                ],
            ]
        ];

        $renderer
            ->renderOriginalDiff(['15 EUR', '22 USD'], ['12 EUR', '25 GBP', '20 USD'])
            ->willReturn('diff between two price collections');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, $change)->shouldReturn('diff between two price collections');
    }
}
