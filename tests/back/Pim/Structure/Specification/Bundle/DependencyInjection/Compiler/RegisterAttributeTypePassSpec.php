<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterAttributeTypePassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_attribute_types_to_the_registry(
        ContainerBuilder $container,
        Definition $registryDefinition
    ) {
        $container->hasDefinition(RegisterAttributeTypePass::ATTRIBUTE_TYPE_REGISTRY)
            ->willReturn(true);

        $container->getDefinition(RegisterAttributeTypePass::ATTRIBUTE_TYPE_REGISTRY)
            ->willReturn($registryDefinition);

        $container->findTaggedServiceIds(RegisterAttributeTypePass::ATTRIBUTE_TYPE_TAG)
            ->willReturn(['attTypeId' => [['alias' => 'my_type']]]);

        $registryDefinition->addMethodCall('register', Argument::any())->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_exception_when_registry_is_not_configured(
        ContainerBuilder $container
    ) {
        $this->shouldThrow('\LogicException')->during('process', [$container]);
    }
}
