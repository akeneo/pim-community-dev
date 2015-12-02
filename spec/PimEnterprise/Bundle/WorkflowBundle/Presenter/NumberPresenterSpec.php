<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class NumberPresenterSpec extends ObjectBehavior
{
    function let(
        PresenterInterface $numberPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($numberPresenter, $localeResolver);
    }

    function it_supports_number()
    {
        $this->supportsChange('pim_catalog_number')->shouldBe(true);
    }

    function it_presents_french_format_numbers(
        $numberPresenter,
        $localeResolver,
        ProductValueInterface $value,
        RendererInterface $renderer
    ) {
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
        $numberPresenter->present(150.1234, ['locale' => 'fr_FR'])
            ->willReturn('150,1234');
        $numberPresenter->present(null, ['locale' => 'fr_FR'])
            ->willReturn(null);

        $this->setRenderer($renderer);
        $renderer->renderNewDiff(null, '150,1234')->willReturn('150,1234');

        $this->presentNew($value, ['data' => 150.1234])
            ->shouldReturn("150,1234");
    }
}
