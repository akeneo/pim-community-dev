<?php

namespace spec\Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductQueryFilterPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_filters_to_the_filter_registry(
        ContainerBuilder $container,
        Definition $registryDefinition
    ) {
        $container->hasDefinition(RegisterProductQueryFilterPass::QUERY_FILTER_REGISTRY)
            ->willReturn(true);

        $container->getDefinition(RegisterProductQueryFilterPass::QUERY_FILTER_REGISTRY)
            ->willReturn($registryDefinition);

        $container->findTaggedServiceIds(RegisterProductQueryFilterPass::QUERY_FILTER_TAG)
            ->willReturn(['filterId' => [['priority' => '22']]]);

        $registryDefinition->addMethodCall('register', Argument::any())->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_exception_when_registry_is_not_configured(
        ContainerBuilder $container
    ) {
        $this->shouldThrow('\LogicException')->during('process', [$container]);
    }
}
