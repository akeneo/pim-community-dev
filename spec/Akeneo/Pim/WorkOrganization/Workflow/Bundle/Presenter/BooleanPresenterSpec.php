<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanPresenterSpec extends ObjectBehavior
{
    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface');
    }

    function it_supports_boolean_type()
    {
        $this->supportsChange('pim_catalog_boolean')->shouldBe(true);
        $this->supportsChange('other')->shouldBe(false);
    }

    function it_presents_boolean_change_using_the_injected_renderer(
        RendererInterface $renderer,
        TranslatorInterface $translator,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $translator->trans('Yes')->willReturn('Yes');
        $translator->trans('No')->willReturn('No');

        $value->getData()->willReturn(false);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('enabled');

        $renderer->renderDiff('No', 'Yes')->willReturn('diff between two booleans');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present($value, ['data' => '1'])->shouldReturn('diff between two booleans');
    }
}
