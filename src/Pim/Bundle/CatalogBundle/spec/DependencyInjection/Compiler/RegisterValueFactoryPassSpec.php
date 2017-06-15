<?php

namespace spec\Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterValueFactoryPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterValueFactoryPassSpec extends ObjectBehavior
{
    const PRODUCT_VALUE_FACTORY_TAG = 'pim_catalog.factory.value';
    const PRODUCT_VALUE_FACTORY = 'pim_catalog.factory.value';

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisterValueFactoryPass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf(CompilerPassInterface::class);
    }

    function it_throws_an_exception_if_called_on_an_unknown_service_id(
        ContainerBuilder $containerBuilder
    ) {
        $containerBuilder->hasDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn(false);
        $containerBuilder->getDefinition(Argument::any())->shouldNotBeCalled();
        $containerBuilder->findTaggedServiceIds(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new \LogicException('Product value factory must be configured'))
            ->during('process', [$containerBuilder]);
    }

    function it_throws_an_exception_if_no_product_value_factory_are_registred(
        ContainerBuilder $containerBuilder,
        Definition $registry
    ) {
        $containerBuilder->hasDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn(true);
        $containerBuilder->getDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn($registry);
        $containerBuilder->findTaggedServiceIds(static::PRODUCT_VALUE_FACTORY_TAG)->willReturn([]);

        $registry->addMethodCall(Argument::cetera())->shouldNotBeCalled();

        $message = sprintf(
            'You must tag at least one service as "%s" to use the product value factory service',
            static::PRODUCT_VALUE_FACTORY_TAG
        );

        $this
            ->shouldThrow(new \RuntimeException($message))
            ->during('process', [$containerBuilder]);
    }

    function it_registers_product_value_factories(
        ContainerBuilder $containerBuilder,
        Definition $registry
    ) {
        $containerBuilder->hasDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn(true);
        $containerBuilder->getDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn($registry);
        $containerBuilder->findTaggedServiceIds(static::PRODUCT_VALUE_FACTORY_TAG)->willReturn([
                'factory.foo' => [[]],
                'factory.bar' => [[]],
            ]);

        $registry->addMethodCall('registerFactory', [new Reference('factory.foo', 1, true),])->shouldBeCalled();
        $registry->addMethodCall('registerFactory', [new Reference('factory.bar', 1, true),])->shouldBeCalled();

        $this->process($containerBuilder);
    }

    function it_registers_product_value_factories_according_to_their_priorities(
        ContainerBuilder $containerBuilder,
        Definition $registry
    ) {
        $containerBuilder->hasDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn(true);
        $containerBuilder->getDefinition(static::PRODUCT_VALUE_FACTORY)->willReturn($registry);
        $containerBuilder->findTaggedServiceIds(static::PRODUCT_VALUE_FACTORY_TAG)->willReturn([
                'factory.foo' => [['priority' => 50]],
                'factory.bar' => [['priority' => 10]],
            ]);

        $registry->addMethodCall('registerFactory', [new Reference('factory.bar', 1, true),])->shouldBeCalled();
        $registry->addMethodCall('registerFactory', [new Reference('factory.foo', 1, true),])->shouldBeCalled();

        $this->process($containerBuilder);
    }
}
