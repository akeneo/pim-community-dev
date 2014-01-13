<?php

namespace spec\Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory;

class RegisterWidgetsPassSpec extends ObjectBehavior
{
    function let(ReferenceFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_add_tagged_widgets_to_the_widget_registry(
        ContainerBuilder $container,
        Definition $definition,
        Reference $fooReference,
        Reference $barReference,
        $factory
    ) {
        $container->hasDefinition('pim_catalog.widget.registry')->willReturn(true);
        $container->getDefinition('pim_catalog.widget.registry')->willReturn($definition);
        $container->findTaggedServiceIds('pim_catalog.widget')->willReturn(
            array(
                'pim_catalog.widget.foo' => array(0 => array('alias' => 'foo')),
                'pim_catalog.widget.bar' => array(),
            )
        );

        $factory->createReference('pim_catalog.widget.foo')->willReturn($fooReference);
        $factory->createReference('pim_catalog.widget.bar')->willReturn($barReference);

        $definition->addMethodCall('add', array('foo', $fooReference))->shouldBeCalled();
        $definition->addMethodCall('add', array('pim_catalog.widget.bar', $barReference))->shouldBeCalled();

        $this->process($container);
    }

    function it_does_nothing_if_the_widget_registry_is_not_available(ContainerBuilder $container)
    {
        $container->hasDefinition('pim_catalog.widget.registry')->willReturn(false);
        $container->getDefinition('pim_catalog.widget.registry')->shouldNotBeCalled();

        $this->process($container);
    }
}
