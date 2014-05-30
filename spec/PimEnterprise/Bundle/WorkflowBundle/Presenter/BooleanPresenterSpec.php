<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

class BooleanPresenterSpec extends ObjectBehavior
{
    function let(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->beConstructedWith($renderer, $factory);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_boolean_key(
        $renderer,
        $factory,
        \Diff $diff,
        Model\AbstractProductValue $value
    ) {
        $this->supports($value, ['boolean' => '1'])->shouldBe(true);
    }

    function it_presents_boolean_change_using_the_injected_renderer(
        $renderer,
        $factory,
        TranslatorInterface $translator,
        \Diff $diff,
        Model\AbstractProductValue $value
    ) {
        $translator->trans('Yes')->willReturn('Yes');
        $translator->trans('No')->willReturn('No');

        $value->getData()->willReturn(false);

        $factory->create('No', 'Yes')->willReturn($diff);
        $diff->render($renderer)->willReturn('diff between two booleans');

        $this->setTranslator($translator);
        $this->present($value, ['boolean' => '1'])->shouldReturn('diff between two booleans');
    }
}
