<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class PricesPresenterSpec extends ObjectBehavior
{
    function let(
        PresenterInterface $pricesPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($pricesPresenter, $localeResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_price()
    {
        $this->supportsChange('pim_catalog_price_collection')->shouldBe(true);
    }

    function it_presents_prices_change_using_the_injected_renderer(
        $pricesPresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value,
        Collection $collection,
        ProductPriceInterface $eur,
        ProductPriceInterface $usd,
        ProductPriceInterface $gbp
    ) {
        $value->getData()->willReturn($collection);
        $collection->getIterator()->willReturn(new \ArrayIterator([
            $eur->getWrappedObject(),
            $gbp->getWrappedObject(),
            $usd->getWrappedObject()
        ]));
        $eur->getData()->willReturn(15.67);
        $eur->getCurrency()->willReturn('EUR');
        $usd->getData()->willReturn(22.34);
        $usd->getCurrency()->willReturn('USD');
        $gbp->getData()->willReturn(null);
        $gbp->getCurrency()->willReturn('GBP');

        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $pricesPresenter->present(['data' => 15.67, 'currency' => 'EUR'], ['locale' => 'en_US'])->willReturn('€15.67');
        $pricesPresenter->present(['data' => 22.34, 'currency' => 'USD'], ['locale' => 'en_US'])->willReturn('$22.34');
        $pricesPresenter->present(['data' => 12.34, 'currency' => 'EUR'], ['locale' => 'en_US'])->willReturn('£12.34');
        $pricesPresenter->present(['data' => 25.67, 'currency' => 'GBP'], ['locale' => 'en_US'])->willReturn('€25.67');
        $pricesPresenter->present(['data' => 20.12, 'currency' => 'USD'], ['locale' => 'en_US'])->willReturn('$20.12');

        $change = [
            'data' => [
                ['currency' => 'EUR', 'data' => '12.34'],
                ['currency' => 'GBP', 'data' => '25.67'],
                ['currency' => 'USD', 'data' => '20.12'],
            ]
        ];

        $renderer
            ->renderOriginalDiff(['€15.67', '$22.34'], ['£12.34', '€25.67', '$20.12'])
            ->willReturn('diff between two price collections');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, $change)->shouldReturn('diff between two price collections');
    }

    function it_presents_french_prices(
        $pricesPresenter,
        $localeResolver,
        ProductValueInterface $value,
        RendererInterface $renderer
    ) {
        $value->getData()->willReturn([]);
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');

        $pricesPresenter->present(['data' => 15.12, 'currency' => 'EUR'], ['locale' => 'fr_FR'])->willReturn('15.12 €');
        $pricesPresenter->present(['data' => 15.48, 'currency' => 'USD'], ['locale' => 'fr_FR'])->willReturn('15.48 $');

        $renderer->renderNewDiff([], ["15.12 €", "15.48 $"])->willReturn('15.12 €<br/>15.48 $');
        $this->setRenderer($renderer);

        $this->presentNew($value, ['data' => [
            ['data' => 15.12, 'currency' => 'EUR'],
            ['data' => 15.48, 'currency' => 'USD'],
        ]])->shouldReturn('15.12 €<br/>15.48 $');
    }
}
