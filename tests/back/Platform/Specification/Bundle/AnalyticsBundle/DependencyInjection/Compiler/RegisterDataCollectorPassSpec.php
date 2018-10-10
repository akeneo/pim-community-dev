<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\AnalyticsBundle\DependencyInjection\Compiler\RegisterDataCollectorPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterDataCollectorPassSpec extends ObjectBehavior
{
    function let(ContainerBuilder $container, Definition $registryDef)
    {
        $container->hasDefinition(RegisterDataCollectorPass::REGISTRY_ID)->willReturn(true);
        $container->getDefinition(RegisterDataCollectorPass::REGISTRY_ID)->willReturn($registryDef);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisterDataCollectorPass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_adds_tagged_data_collector_to_the_registry(
        $container,
        $registryDef
    ) {
        $container->findTaggedServiceIds(RegisterDataCollectorPass::COLLECTOR_TAG)->willReturn(
            [
                'foo' => [['name' => 'pim_enrich.view_element', 'type' => 'form_tab', 'position' => 10]],
                'bar' => [['name' => 'pim_enrich.view_element', 'type' => 'form_button', 'position' => 20]]
            ]
        );

        $registryDef->addMethodCall(Argument::any(), Argument::any())->shouldBeCalled();

        $this->process($container);
    }

    function it_does_nothing_if_the_registry_is_not_registered_in_the_container($container)
    {
        $container->hasDefinition(RegisterDataCollectorPass::REGISTRY_ID)->willReturn(false);

        $container->getDefinition(RegisterDataCollectorPass::REGISTRY_ID)->shouldNotBeCalled();

        $this->process($container);
    }
}
