<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterComparatorsPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_comparator(
        ContainerBuilder $container,
        Definition $comparator,
        Definition $barDefinition,
        Definition $fooDefinition,
        Definition $bazDefinition,
        Definition $tifoDefinition
    ) {
        $container->hasDefinition('pim_catalog.comparator.registry')->willReturn(true);
        $container->getDefinition('pim_catalog.comparator.registry')->willReturn($comparator);
        $container->findTaggedServiceIds('pim_catalog.attribute.comparator')->willReturn([
            'comparator.bar' => [['priority' => -10]],
            'comparator.foo' => [['priority' => 10]],
            'comparator.baz' => [[]],
        ]);
        $container->findTaggedServiceIds('pim_catalog.field.comparator')->willReturn([
            'comparator.tifo' => [[]],
        ]);
        $container->getDefinition('comparator.bar')->willReturn($barDefinition);
        $container->getDefinition('comparator.foo')->willReturn($fooDefinition);
        $container->getDefinition('comparator.baz')->willReturn($bazDefinition);
        $container->getDefinition('comparator.tifo')->willReturn($tifoDefinition);

        $barDefinition->setPublic(false)->shouldBeCalled();
        $fooDefinition->setPublic(false)->shouldBeCalled();
        $bazDefinition->setPublic(false)->shouldBeCalled();
        $tifoDefinition->setPublic(false)->shouldBeCalled();
        $comparator
            ->addMethodCall('addAttributeComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.bar', -10))
            ->shouldBeCalled();
        $comparator
            ->addMethodCall('addAttributeComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.foo', 10))
            ->shouldBeCalled();
        $comparator
            ->addMethodCall('addAttributeComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.baz', 0))
            ->shouldBeCalled();
        $comparator
            ->addMethodCall('addFieldComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.tifo', 0))
            ->shouldBeCalled();

        $this->process($container);
    }

    private function isAnArrayContainingAReferenceAndAPriority($service, $priority)
    {
        return Argument::allOf(
            Argument::withEntry(0, Argument::allOf(
                Argument::type('Symfony\Component\DependencyInjection\Reference'),
                Argument::which('__toString', $service)
            ))
        );
    }
}
