<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

class BooleanPresenterSpec extends ObjectBehavior
{
    function let(
        TranslatorInterface $translator
    ) {
        $this->setTranslator($translator);
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
        TranslatorInterface $translator
    ) {
        $translator->trans('Yes')->shouldBeCalled()->willReturn('Yes');
        $translator->trans('No')->shouldBeCalled()->willReturn('No');

        $this->present(false, ['data' => '1'])->shouldReturn([
            'before' => 'No',
            'after' => 'Yes'
        ]);
    }

    function it_presents_an_empty_boolean_value_as_an_empty_string(
        TranslatorInterface $translator
    ) {
        $translator->trans('Yes')->shouldBeCalled()->willReturn('Yes');
        $this->present(null, ['data' => true])->shouldReturn([
            'before' => '',
            'after' => 'Yes'
        ]);

        $translator->trans('No')->shouldBeCalled()->willReturn('No');
        $this->present(false, ['data' => null])->shouldReturn([
            'before' => 'No',
            'after' => ''
        ]);
    }
}
