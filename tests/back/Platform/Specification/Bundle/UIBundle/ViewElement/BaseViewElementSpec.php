<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\BaseViewElement;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VisibilityCheckerInterface;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BaseViewElementSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('form_tab', 'form_tab.html.twig', ['name' => 'Tab']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BaseViewElement::class);
    }

    function it_is_a_view_element()
    {
        $this->shouldImplement(ViewElementInterface::class);
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('form_tab');
    }

    function it_has_a_template()
    {
        $this->getTemplate()->shouldReturn('form_tab.html.twig');
    }

    function it_has_template_parameters()
    {
        $this->getParameters()->shouldReturn(['name' => 'Tab']);
    }

    function it_is_visible_by_default()
    {
        $this->isVisible()->shouldReturn(true);
    }

    function it_uses_visibility_checkers_to_determine_whether_it_should_be_visible(VisibilityCheckerInterface $checker)
    {
        $checker->isVisible(Argument::cetera())->willReturn(false);
        $this->addVisibilityChecker($checker)->shouldReturn($this);

        $this->isVisible()->shouldReturn(false);
    }

    function it_passes_configuration_to_the_used_visibility_checkers(VisibilityCheckerInterface $checker)
    {
        $checker->isVisible(['foo' => 'bar'], [])->shouldBeCalled()->willReturn(true);
        $this->addVisibilityChecker($checker, ['foo' => 'bar'])->shouldReturn($this);

        $this->isVisible()->shouldReturn(true);
    }
}
