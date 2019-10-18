<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

class NumberPresenterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        PresenterInterface $numberPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith($attributeRepository, $numberPresenter, $localeResolver);
    }

    function it_supports_number()
    {
        $this->supports('pim_catalog_number')->shouldBe(true);
    }

    function it_presents_french_format_numbers(
        $numberPresenter,
        $localeResolver,
        ValueInterface $value,
        RendererInterface $renderer
    ) {
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
        $numberPresenter->present(150.1234, ['locale' => 'fr_FR'])
            ->willReturn('150,1234');
        $numberPresenter->present(null, ['locale' => 'fr_FR'])
            ->willReturn(null);

        $this->setRenderer($renderer);
        $renderer->renderDiff(null, '150,1234')->willReturn('150,1234');

        $this->present(['data' => 150.1234], null)->shouldReturn("150,1234");
    }
}
