<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductQueryFilterPassSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('product');
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType(CompilerPassInterface::class);
    }

    function it_adds_tagged_filters_to_the_filter_registry(
        ContainerBuilder $container,
        Definition $registryDefinition
    ) {
        $container->hasDefinition('pim_catalog.query.filter.product_registry')
            ->willReturn(true);

        $container->getDefinition('pim_catalog.query.filter.product_registry')
            ->willReturn($registryDefinition);

        $container->findTaggedServiceIds('pim_catalog.elasticsearch.query.product_filter')
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
