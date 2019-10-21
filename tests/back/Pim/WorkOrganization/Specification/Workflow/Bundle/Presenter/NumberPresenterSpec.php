<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

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
        $this->supports('pim_catalog_number')->shouldBe(true);
    }

    function it_presents_french_format_numbers(
        $numberPresenter,
        $localeResolver,
        RendererInterface $renderer
    ) {
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
        $numberPresenter->present(150.1234, ['locale' => 'fr_FR'])
            ->willReturn('150,1234');
        $numberPresenter->present(null, ['locale' => 'fr_FR'])
            ->willReturn(null);

        $this->setRenderer($renderer);
        $renderer->renderDiff(null, '150,1234')->willReturn('150,1234');

        $this->present(null, ['data' => 150.1234])->shouldReturn("150,1234");
    }
}
