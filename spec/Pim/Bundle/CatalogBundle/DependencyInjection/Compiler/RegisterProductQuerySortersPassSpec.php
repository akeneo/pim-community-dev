<?php

namespace spec\Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySortersPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductQuerySortersPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_sorters_to_the_sorter_registry(
        ContainerBuilder $container,
        Definition $registryDefinition
    ) {
        $container->hasDefinition(RegisterProductQuerySortersPass::QUERY_SORTER_REGISTRY)
            ->willReturn(true);

        $container->getDefinition(RegisterProductQuerySortersPass::QUERY_SORTER_REGISTRY)
            ->willReturn($registryDefinition);

        $container->findTaggedServiceIds(RegisterProductQuerySortersPass::QUERY_SORTER_TAG)
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
