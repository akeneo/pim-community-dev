<?php

namespace spec\Pim\Bundle\DashboardBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Widget\Registry;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class WidgetsExtensionSpec extends ObjectBehavior
{
    function let(Registry $registry, EngineInterface $templating)
    {
        $this->beConstructedWith($registry, $templating);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DashboardBundle\Twig\WidgetsExtension');
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_widgets_extension');
    }

    function it_provides_a_render_widgets_function()
    {
        $functions = $this->getFunctions();
        $functions->shouldHaveCount(1);
        $function = $functions[0];

        $function->shouldBeAnInstanceOf('\Twig_SimpleFunction');
        $function->getName()->shouldReturn('render_widgets');
    }

    function it_renders_widgets_by_their_position(WidgetInterface $foo, WidgetInterface $bar, $registry, $templating)
    {
        $foo->getTemplate()->willReturn('foo:bar');
        $foo->getParameters()->willReturn(['foo' => 'bar']);

        $bar->getTemplate()->willReturn('baz:qux');
        $bar->getParameters()->willReturn(['baz' => 'qux']);

        $registry->getAll()->willReturn([1 => $foo, 5 => $bar]);

        $templating->render('foo:bar', ['foo' => 'bar'])->shouldBeCalled()->willReturn('<div>Foo</div>');
        $templating->render('baz:qux', ['baz' => 'qux'])->shouldBeCalled()->willReturn('<p>Bar</p>');

        $this->renderWidgets()->shouldReturn('<div>Foo</div><p>Bar</p>');
    }
}
