<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanPresenterSpec extends ObjectBehavior
{
    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_boolean_type()
    {
        $this->supportsChange('pim_catalog_boolean')->shouldBe(true);
        $this->supportsChange('other')->shouldBe(false);
    }

    function it_presents_boolean_change_using_the_injected_renderer(
        RendererInterface $renderer,
        TranslatorInterface $translator,
        ProductValueInterface $value
    ) {
        $translator->trans('Yes')->willReturn('Yes');
        $translator->trans('No')->willReturn('No');

        $value->getData()->willReturn(false);

        $renderer->renderOriginalDiff('No', 'Yes')->willReturn('diff between two booleans');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->presentOriginal($value, ['data' => '1'])->shouldReturn('diff between two booleans');
    }
}
