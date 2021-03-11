<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

class BooleanPresenterSpec extends ObjectBehavior
{
    function let(
        RendererInterface $renderer,
        TranslatorInterface $translator
    ) {
        $this->setTranslator($translator);
        $this->setRenderer($renderer);
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
        TranslatorInterface $translator
    ) {
        $translator->trans('Yes')->shouldBeCalled()->willReturn('Yes');
        $translator->trans('No')->shouldBeCalled()->willReturn('No');
        $renderer->renderDiff('No', 'Yes')->shouldBeCalled()->willReturn('diff between two booleans');

        $this->present(false, ['data' => '1'])->shouldReturn('diff between two booleans');
    }

    function it_presents_an_empty_boolean_value_as_an_empty_string(
        RendererInterface $renderer,
        TranslatorInterface $translator
    ) {
        $translator->trans('Yes')->shouldBeCalled()->willReturn('Yes');
        $renderer->renderDiff('', 'Yes')->shouldBeCalled()->willReturn('diff between empty and boolean');
        $this->present(null, ['data' => true])->shouldReturn('diff between empty and boolean');

        $translator->trans('No')->shouldBeCalled()->willReturn('No');
        $renderer->renderDiff('No', '')->shouldBeCalled()->willReturn('diff between boolean and empty');
        $this->present(false, ['data' => null])->shouldReturn('diff between boolean and empty');
    }
}
