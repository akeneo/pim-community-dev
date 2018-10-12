<?php

namespace Specification\Akeneo\Platform\Bundle\DashboardBundle\Controller;

use Akeneo\Platform\Bundle\DashboardBundle\Controller\WidgetController;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\Registry;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WidgetControllerSpec extends ObjectBehavior
{
    function let(Registry $registry, EngineInterface $templating)
    {
        $this->beConstructedWith($registry, $templating);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WidgetController::class);
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
        $response->shouldBeAnInstanceOf(Response::class);
        $response->getContent()->shouldReturn('<p>Bar</p><div>Foo</div>');
    }

    function it_provides_data_to_widgets(WidgetInterface $foo, $registry)
    {
        $foo->getData()->willReturn(['bar' => 'baz']);

        $registry->get('foo')->willReturn($foo);

        $response = $this->dataAction('foo');
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn('{"bar":"baz"}');
    }
}
