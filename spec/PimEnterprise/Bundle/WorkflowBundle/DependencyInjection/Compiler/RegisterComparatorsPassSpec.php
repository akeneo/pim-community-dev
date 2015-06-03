<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

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

    function it_adds_tagged_presenter_to_the_product_draft_twig_extension(
        ContainerBuilder $container,
        Definition $comparator,
        Definition $barDefinition,
        Definition $fooDefinition,
        Definition $bazDefinition
    ) {
        $container->hasDefinition('pimee_workflow.comparator.registry')->willReturn(true);
        $container->getDefinition('pimee_workflow.comparator.registry')->willReturn($comparator);
        $container->findTaggedServiceIds('pimee_workflow.attribute.comparator')->willReturn([
            'comparator.bar' => [['priority' => -10]],
            'comparator.foo' => [['priority' => 10]],
            'comparator.baz' => [[]],
        ]);
        $container->getDefinition('comparator.bar')->willReturn($barDefinition);
        $container->getDefinition('comparator.foo')->willReturn($fooDefinition);
        $container->getDefinition('comparator.baz')->willReturn($bazDefinition);

        $barDefinition->setPublic(false)->shouldBeCalled();
        $fooDefinition->setPublic(false)->shouldBeCalled();
        $bazDefinition->setPublic(false)->shouldBeCalled();
        $comparator
            ->addMethodCall('addAttributeComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.bar', -10))
            ->shouldBeCalled();
        $comparator
            ->addMethodCall('addAttributeComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.foo', 10))
            ->shouldBeCalled();
        $comparator
            ->addMethodCall('addAttributeComparator', $this->isAnArrayContainingAReferenceAndAPriority('comparator.baz', 0))
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
