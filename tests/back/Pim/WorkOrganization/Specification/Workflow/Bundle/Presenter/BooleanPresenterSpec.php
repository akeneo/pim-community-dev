<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

class BooleanPresenterSpec extends ObjectBehavior
{
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
        TranslatorInterface $translator
    ) {
        $translator->trans('Yes')->willReturn('Yes');
        $translator->trans('No')->willReturn('No');

        $renderer->renderDiff('No', 'Yes')->willReturn('diff between two booleans');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present(['data' => false], ['data' => '1'])->shouldReturn('diff between two booleans');
    }
}
