<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanPresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_boolean_type()
    {
        $this->supports('pim_catalog_boolean')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_presents_boolean_change_using_the_injected_renderer(
        RendererInterface $renderer,
        TranslatorInterface $translator,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $translator->trans('Yes')->willReturn('Yes');
        $translator->trans('No')->willReturn('No');
        $attributeRepository->findOneByIdentifier('enabled')->willReturn($attribute);
        $attribute->getCode()->willReturn('enabled');

        $renderer->renderDiff('No', 'Yes')->willReturn('diff between two booleans');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present(['data' => '1'], false)->shouldReturn('diff between two booleans');
    }
}
