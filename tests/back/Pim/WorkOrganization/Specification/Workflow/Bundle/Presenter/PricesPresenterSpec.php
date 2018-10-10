<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as LocalizationPresenter;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

class PricesPresenterSpec extends ObjectBehavior
{
    function let(
        LocalizationPresenter $pricesPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($pricesPresenter, $localeResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_price()
    {
        $this->supportsChange('pim_catalog_price_collection')->shouldBe(true);
    }

    function it_presents_prices_change_using_the_injected_renderer(
        $pricesPresenter,
        $localeResolver,
        RendererInterface $renderer,
        ValueInterface $value,
        Collection $collection,
        ProductPriceInterface $eur,
        ProductPriceInterface $usd,
        ProductPriceInterface $gbp,
        ProductPriceInterface $jpy
    ) {
        $value->getData()->willReturn($collection);
        $collection->getIterator()->willReturn(new \ArrayIterator([
            $eur->getWrappedObject(),
            $gbp->getWrappedObject(),
            $usd->getWrappedObject(),
            $jpy->getWrappedObject()
        ]));
        $eur->getData()->willReturn(15.67);
        $eur->getCurrency()->willReturn('EUR');
        $usd->getData()->willReturn(22.34);
        $usd->getCurrency()->willReturn('USD');
        $gbp->getData()->willReturn(null);
        $gbp->getCurrency()->willReturn('GBP');
        $jpy->getData()->willReturn(150);
        $jpy->getCurrency()->willReturn('JPY');

        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $pricesPresenter->present(['amount' => 15.67, 'currency' => 'EUR'], ['locale' => 'en_US'])->willReturn('€15.67');
        $pricesPresenter->present(['amount' => 22.34, 'currency' => 'USD'], ['locale' => 'en_US'])->willReturn('$22.34');
        $pricesPresenter->present(['amount' => 150,   'currency' => 'JPY'], ['locale' => 'en_US'])->willReturn('¥150');

        $pricesPresenter->present(['amount' => 12.34, 'currency' => 'EUR'], ['locale' => 'en_US'])->willReturn('£12.34');
        $pricesPresenter->present(['amount' => 25.67, 'currency' => 'GBP'], ['locale' => 'en_US'])->willReturn('€25.67');
        $pricesPresenter->present(['amount' => 20.12, 'currency' => 'USD'], ['locale' => 'en_US'])->willReturn('$20.12');
        $pricesPresenter->present(['amount' => null,  'currency' => 'JPY'], ['locale' => 'en_US'])->willReturn('');

        $change = [
            'data' => [
                ['currency' => 'EUR', 'amount' => '12.34'],
                ['currency' => 'GBP', 'amount' => '25.67'],
                ['currency' => 'USD', 'amount' => '20.12'],
                ['currency' => 'JPY', 'amount' => null],
            ]
        ];

        $renderer
            ->renderDiff(['€15.67', '$22.34', '¥150'], ['£12.34', '€25.67', '$20.12'])
            ->willReturn('diff between two price collections');

        $this->setRenderer($renderer);
        $this->present($value, $change)->shouldReturn('diff between two price collections');
    }

    function it_presents_french_prices(
        $pricesPresenter,
        $localeResolver,
        ValueInterface $value,
        RendererInterface $renderer
    ) {
        $value->getData()->willReturn([]);
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');

        $pricesPresenter->present(['amount' => 15.12, 'currency' => 'EUR'], ['locale' => 'fr_FR'])->willReturn('15.12 €');
        $pricesPresenter->present(['amount' => 15.48, 'currency' => 'USD'], ['locale' => 'fr_FR'])->willReturn('15.48 $');

        $renderer->renderDiff([], ["15.12 €", "15.48 $"])->willReturn('15.12 €<br/>15.48 $');
        $this->setRenderer($renderer);

        $this->present($value, ['data' => [
            ['amount' => 15.12, 'currency' => 'EUR'],
            ['amount' => 15.48, 'currency' => 'USD'],
        ]])->shouldReturn('15.12 €<br/>15.48 $');
    }
}
