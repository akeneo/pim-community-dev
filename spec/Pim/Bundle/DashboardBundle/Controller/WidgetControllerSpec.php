<?php

namespace spec\Pim\Bundle\DashboardBundle\Controller;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DashboardBundle\Widget\Registry;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class WidgetControllerSpec extends ObjectBehavior
{
    function let(Registry $registry, EngineInterface $templating)
    {
        $this->beConstructedWith($registry, $templating);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DashboardBundle\Controller\WidgetController');
    }

    function it_renders_widgets_by_their_position(WidgetInterface $foo, WidgetInterface $bar, $registry, $templating)
    {
        $foo->getTemplate()->willReturn('foo:bar');
        $foo->getParameters()->willReturn(['foo' => 'bar']);

        $bar->getTemplate()->willReturn('baz:qux');
        $bar->getParameters()->willReturn(['baz' => 'qux']);

        $registry->getAll()->willReturn([1 => $bar, 5 => $foo]);

        $templating->render('foo:bar', ['foo' => 'bar'])->shouldBeCalled()->willReturn('<div>Foo</div>');
        $templating->render('baz:qux', ['baz' => 'qux'])->shouldBeCalled()->willReturn('<p>Bar</p>');

        $response = $this->listAction();
        $response->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\Response');
        $response->getContent()->shouldReturn('<p>Bar</p><div>Foo</div>');
    }

    function it_provides_data_to_widgets(WidgetInterface $foo, $registry)
    {
        $foo->getData()->willReturn(['bar' => 'baz']);

        $registry->get('foo')->willReturn($foo);

        $response = $this->dataAction('foo');
        $response->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\JsonResponse');
        $response->getContent()->shouldReturn('{"bar":"baz"}');
    }
}
