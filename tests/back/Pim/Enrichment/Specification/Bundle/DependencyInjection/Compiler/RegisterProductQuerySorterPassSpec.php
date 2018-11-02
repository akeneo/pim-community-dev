<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductQuerySorterPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_sorters_to_the_sorter_registry(
        ContainerBuilder $container,
        Definition $registryDefinition
    ) {
        $container->hasDefinition(RegisterProductQuerySorterPass::QUERY_SORTER_REGISTRY)
            ->willReturn(true);

        $container->getDefinition(RegisterProductQuerySorterPass::QUERY_SORTER_REGISTRY)
            ->willReturn($registryDefinition);

        $container->findTaggedServiceIds(RegisterProductQuerySorterPass::QUERY_SORTER_TAG)
            ->willReturn(['sorterId' => [['priority' => '22']]]);

        $registryDefinition->addMethodCall('register', Argument::any())->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_exception_when_registry_is_not_configured(
        ContainerBuilder $container
    ) {
        $this->shouldThrow('\LogicException')->during('process', [$container]);
    }
}
