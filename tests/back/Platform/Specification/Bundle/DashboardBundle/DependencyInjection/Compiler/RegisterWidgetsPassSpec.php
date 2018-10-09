<?php

namespace Specification\Akeneo\Platform\Bundle\DashboardBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterWidgetsPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_add_tagged_widgets_to_the_widget_registry(ContainerBuilder $container, Definition $definition)
    {
        $container->hasDefinition('pim_dashboard.widget.registry')->willReturn(true);
        $container->getDefinition('pim_dashboard.widget.registry')->willReturn($definition);
        $container->findTaggedServiceIds('pim_dashboard.widget')->willReturn(
            [
                'pim_dashboard.widget.foo' => [0 => ['position' => 10]],
                'pim_dashboard.widget.bar' => [],
            ]
        );

        $definition->addMethodCall('add', Argument::that(function ($params) {
            return
                $params[0] instanceof Reference &&
                'pim_dashboard.widget.foo' === $params[0]->__toString() &&
                10 === $params[1]
            ;
        }))->shouldBeCalled();

        $definition->addMethodCall('add', Argument::that(function ($params) {
            $result =
                $params[0] instanceof Reference &&
                'pim_dashboard.widget.bar' === $params[0]->__toString() &&
                0 === $params[1]
            ;

            return $result;
        }))->shouldBeCalled();

        $this->process($container);
    }

    function it_does_nothing_if_the_widget_registry_is_not_available(ContainerBuilder $container)
    {
        $container->hasDefinition('pim_dashboard.widget.registry')->willReturn(false);
        $container->getDefinition('pim_dashboard.widget.registry')->shouldNotBeCalled();

        $this->process($container);
    }
}
