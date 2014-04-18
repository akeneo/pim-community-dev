<?php

namespace spec\Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterMassEditActionsPassSpec extends ObjectBehavior
{
    function let(ReferenceFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_register_mass_edit_actions_in_a_specific_operator(
        ContainerBuilder $container,
        ReferenceFactory $factory,
        Definition $productOperatorDef,
        Definition $someOperatorDef,
        Reference $fooRef,
        Reference $barRef
    ) {
        $container->findTaggedServiceIds('pim_enrich.mass_edit_action')->willReturn([
            'foo' => [['alias' => 'foo-action']],
            'bar' => [['alias' => 'bar-action', 'operator' => 'some_operator']],
        ]);

        $container->getDefinition('pim_enrich.mass_edit_action.operator.product')->willReturn($productOperatorDef);
        $container->getDefinition('some_operator')->willReturn($someOperatorDef);

        $factory->createReference('foo')->willReturn($fooRef);
        $factory->createReference('bar')->willReturn($barRef);

        $productOperatorDef->addMethodCall('registerMassEditAction', ['foo-action', $fooRef, null])->shouldBeCalled();
        $someOperatorDef->addMethodCall('registerMassEditAction', ['bar-action', $barRef, null])->shouldBeCalled();

        $this->process($container);
    }
}
