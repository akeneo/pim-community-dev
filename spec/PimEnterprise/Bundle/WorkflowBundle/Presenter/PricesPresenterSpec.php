<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class PricesPresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_is_a_value_instance_and_change_has_a_prices_key(
        Model\AbstractProductValue $value
    ) {
        $this->supports($value, ['prices' => 'foo'])->shouldBe(true);
    }

    function it_presents_prices_change_using_the_injected_renderer(
        RendererInterface $renderer,
        Model\AbstractProductValue $value,
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

        $renderer->renderDiff(['15 EUR', '22 USD'], ['12 EUR', '25 GBP', '20 USD'])->willReturn('diff between two price collections');

        $this->setRenderer($renderer);
        $this->present($value, $change)->shouldReturn('diff between two price collections');
    }
}
