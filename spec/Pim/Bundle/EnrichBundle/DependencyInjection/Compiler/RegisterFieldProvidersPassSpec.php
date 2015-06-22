<?php

namespace spec\Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterViewElementsPassSpec extends ObjectBehavior
{
    function let(ReferenceFactory $factory, ContainerBuilder $container, Definition $registryDef)
    {
        $this->beConstructedWith($factory);

        $container->hasDefinition(RegisterViewElementsPass::REGISTRY_ID)->willReturn(true);
        $container->getDefinition(RegisterViewElementsPass::REGISTRY_ID)->willReturn($registryDef);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\DependencyInjection\Compiler\RegisterViewElementsPass');
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_adds_tagged_view_elements_to_the_view_element_registry(
        $container,
        $registryDef,
        $factory,
        Reference $fooRef,
        Reference $barRef
    ) {
        $container->findTaggedServiceIds(RegisterViewElementsPass::VIEW_ELEMENT_TAG)->willReturn(
            [
                'foo' => [['name' => 'pim_enrich.view_element', 'type' => 'form_tab', 'position' => 10]],
                'bar' => [['name' => 'pim_enrich.view_element', 'type' => 'form_button', 'position' => 20]]
            ]
        );

        $factory->createReference('foo')->willReturn($fooRef);
        $factory->createReference('bar')->willReturn($barRef);

        $registryDef->addMethodCall(
            'add',
            [
                $fooRef,
                'form_tab',
                10
            ]
        )->shouldBeCalled();

        $registryDef->addMethodCall(
            'add',
            [
                $barRef,
                'form_button',
                20
            ]
        )->shouldBeCalled();

        $this->process($container);
    }

    function it_does_nothing_if_the_view_element_registry_is_not_registered_in_the_container($container)
    {
        $container->hasDefinition(RegisterViewElementsPass::REGISTRY_ID)->willReturn(false);

        $container->getDefinition(RegisterViewElementsPass::REGISTRY_ID)->shouldNotBeCalled();

        $this->process($container);
    }

    function it_throws_an_exception_if_a_view_element_does_not_have_a_type($container)
    {
        $container->findTaggedServiceIds(RegisterViewElementsPass::VIEW_ELEMENT_TAG)->willReturn(
            [
                'foo' => [['name' => 'pim_enrich.view_element']]
            ]
        );

        $this
            ->shouldThrow(new \LogicException('No type provided for the "foo" view element'))
            ->duringProcess($container);
    }

    function it_sets_default_view_element_position_if_not_specified(
        $container,
        $registryDef,
        $factory,
        Reference $fooRef
    ) {
        $container->findTaggedServiceIds(RegisterViewElementsPass::VIEW_ELEMENT_TAG)->willReturn(
            [
                'foo' => [['name' => 'pim_enrich.view_element', 'type' => 'button']]
            ]
        );

        $factory->createReference('foo')->willReturn($fooRef);

        $registryDef->addMethodCall(
            'add',
            [
                $fooRef,
                'button',
                RegisterViewElementsPass::DEFAULT_POSITION
            ]
        )->shouldBeCalled();

        $this->process($container);
    }

    function it_can_handle_view_elements_with_multiple_tags(
        $container,
        $registryDef,
        $factory,
        Reference $fooRef
    ) {
        $container->findTaggedServiceIds(RegisterViewElementsPass::VIEW_ELEMENT_TAG)->willReturn(
            [
                'foo' => [
                    ['name' => 'pim_enrich.view_element', 'type' => 'form_button', 'position' => 10],
                    ['name' => 'pim_enrich.view_element', 'type' => 'page_button', 'position' => 100],
                    ['name' => 'pim_enrich.view_element', 'type' => 'link', 'position' => 50],
                ]
            ]
        );

        $factory->createReference('foo')->willReturn($fooRef);

        $registryDef->addMethodCall(
            'add',
            [
                $fooRef,
                'form_button',
                10
            ]
        )->shouldBeCalled();

        $registryDef->addMethodCall(
            'add',
            [
                $fooRef,
                'page_button',
                100
            ]
        )->shouldBeCalled();

        $registryDef->addMethodCall(
            'add',
            [
                $fooRef,
                'link',
                50
            ]
        )->shouldBeCalled();

        $this->process($container);
    }
}
