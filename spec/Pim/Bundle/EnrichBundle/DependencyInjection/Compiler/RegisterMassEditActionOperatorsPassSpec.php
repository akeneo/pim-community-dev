<?php

namespace spec\Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class RegisterMassEditActionOperatorsPassSpec extends ObjectBehavior
{
    function let(ReferenceFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_operators_to_the_operator_registry(
        ContainerBuilder $container,
        ReferenceFactory $factory,
        Reference $fooRef,
        Definition $registryDef
    ) {
        $container->findTaggedServiceIds('pim_enrich.mass_edit_action_operator')->willReturn([
            'foo' => [['datagrid' => 'foo-grid']],
        ]);

        $factory->createReference('foo')->willReturn($fooRef);
        $container->hasDefinition('pim_enrich.mass_edit_action.operator.registry')->willReturn(true);
        $container->getDefinition('pim_enrich.mass_edit_action.operator.registry')->willReturn($registryDef);

        $registryDef->addMethodCall('register', ['foo-grid', $fooRef])->shouldBeCalled();

        $this->process($container);
    }

    function it_does_nothing_if_the_registry_is_not_a_registered_service(ContainerBuilder $container)
    {
        $container->hasDefinition('pim_enrich.mass_edit_action.operator.registry')->willReturn(false);

        $container->getDefinition('pim_enrich.mass_edit_action.operator.registry')->shouldNotBeCalled();

        $this->process($container);
    }
}
