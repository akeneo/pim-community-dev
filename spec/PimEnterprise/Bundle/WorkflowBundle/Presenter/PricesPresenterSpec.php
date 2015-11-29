<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class PricesPresenterSpec extends ObjectBehavior
{
    function let(
        LocalizerInterface $priceLocalizer,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($priceLocalizer, $localeResolver);
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
        $priceLocalizer,
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

        $localeResolver->getFormats()->willReturn(['decimal_separator' => '.']);
        $priceLocalizer->localize(15.67, ['decimal_separator' => '.'])->willReturn(15.67);
        $priceLocalizer->localize(22.34, ['decimal_separator' => '.'])->willReturn(22.34);
        $priceLocalizer->localize(
            [
                ["currency" => "EUR", "data" => "12.34"],
                ["currency" => "GBP", "data" => "25.67"],
                ["currency" => "USD", "data" => "20.12"]
            ],
            ["decimal_separator" => "."]
        )->willReturn([
            ["currency" => "EUR", "data" => "12.34"],
            ["currency" => "GBP", "data" => "25.67"],
            ["currency" => "USD", "data" => "20.12"]
        ]);

        $change = [
            'data' => [
                ['currency' => 'EUR', 'data' => '12.34'],
                ['currency' => 'GBP', 'data' => '25.67'],
                ['currency' => 'USD', 'data' => '20.12'],
            ]
        ];

        $renderer
            ->renderOriginalDiff(['15.67 EUR', '22.34 USD'], ['12.34 EUR', '25.67 GBP', '20.12 USD'])
            ->willReturn('diff between two price collections');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, $change)->shouldReturn('diff between two price collections');
    }

    function it_presents_french_prices(
        $priceLocalizer,
        $localeResolver,
        ProductValueInterface $value,
        RendererInterface $renderer
    ) {
        $value->getData()->willReturn([]);
        $localeResolver->getFormats()->willReturn(['decimal_separator' => ',']);
        $priceLocalizer->localize(15.67, ['decimal_separator' => ','])->willReturn(15.67);
        $priceLocalizer->localize(
            [
                ["data" => 15.12, "currency" => "EUR"],
                ["data" => 15.48, "currency" => "USD"]
            ],
            ["decimal_separator" => ","]
        )->willReturn([
            ["data" => '15,12', "currency" => "EUR"],
            ["data" => '15,48', "currency" => "USD"]
        ]);

        $renderer->renderNewDiff([], ["15,12 EUR", "15,48 USD"])->willReturn('15,12 EUR<br/>15,48 USD');

        $this->setRenderer($renderer);

        $this->presentNew($value, ['data' => [
            ['data' => 15.12, 'currency' => 'EUR'],
            ['data' => 15.48, 'currency' => 'USD'],
        ]])->shouldReturn('15,12 EUR<br/>15,48 USD');
    }
}
