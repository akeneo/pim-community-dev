<?php

namespace spec\Pim\Bundle\TransformBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;

class ReplacePimSerializerArgumentsPassSpec extends ObjectBehavior
{
    function let(ContainerBuilder $container, ParameterBag $bag)
    {
        $container->getParameterBag()->willReturn($bag);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_sets_arguments_of_pim_serializer_with_tagged_normalizers_and_denormalizers_by_default(
        $container,
        $bag,
        ReferenceFactory $factory,
        Definition $definition,
        Reference $fooNormalizerReference,
        Reference $barNormalizerReference,
        Reference $fooEncoderReference,
        Reference $barEncoderReference
    ) {
        $this->beConstructedWith('pim_serializer', $factory);

        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->getDefinition('pim_serializer')->willReturn($definition);
        $definition->getClass()->willReturn('%pim_serializer.class%');
        $bag->resolveValue('%pim_serializer.class%')->willReturn('Symfony\Component\Serializer\Serializer');

        $container->findTaggedServiceIds('pim_serializer.normalizer')->willReturn(
            [
                'normalizer.foo' => [[]],
                'normalizer.bar' => [[]],
            ]
        );
        $container->findTaggedServiceIds('pim_serializer.encoder')->willReturn(
            [
                'encoder.foo' => [[]],
                'encoder.bar' => [[]],
            ]
        );

        $factory->createReference('normalizer.foo')->willReturn($fooNormalizerReference);
        $factory->createReference('normalizer.bar')->willReturn($barNormalizerReference);
        $factory->createReference('encoder.foo')->willReturn($fooEncoderReference);
        $factory->createReference('encoder.bar')->willReturn($barEncoderReference);

        $definition->setArguments([[$fooNormalizerReference, $barNormalizerReference], [$fooEncoderReference, $barEncoderReference]])->shouldBeCalled();

        $this->process($container);
    }

    function it_sorts_arguments_by_priorities(
        $container,
        $bag,
        ReferenceFactory $factory,
        Definition $definition,
        Reference $fooNormalizerReference,
        Reference $barNormalizerReference,
        Reference $fooEncoderReference,
        Reference $barEncoderReference
    ) {
        $this->beConstructedWith('pim_serializer', $factory);

        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->getDefinition('pim_serializer')->willReturn($definition);
        $definition->getClass()->willReturn('%pim_serializer.class%');
        $bag->resolveValue('%pim_serializer.class%')->willReturn('Symfony\Component\Serializer\Serializer');

        $container->findTaggedServiceIds('pim_serializer.normalizer')->willReturn(
            [
                'normalizer.foo' => [['priority' => 10]],
                'normalizer.bar' => [['priority' => 100]],
            ]
        );
        $container->findTaggedServiceIds('pim_serializer.encoder')->willReturn(
            [
                'encoder.foo' => [[ 'priority' => 10]],
                'encoder.bar' => [[]],
            ]
        );

        $factory->createReference('normalizer.foo')->willReturn($fooNormalizerReference);
        $factory->createReference('normalizer.bar')->willReturn($barNormalizerReference);
        $factory->createReference('encoder.foo')->willReturn($fooEncoderReference);
        $factory->createReference('encoder.bar')->willReturn($barEncoderReference);

        $definition->setArguments([[$barNormalizerReference, $fooNormalizerReference], [$barEncoderReference, $fooEncoderReference]])->shouldBeCalled();

        $this->process($container);
    }

    function it_sets_arguments_of_configured_serializer_with_tagged_normalizers_and_denormalizers(
        $container,
        $bag,
        ReferenceFactory $factory,
        Definition $definition,
        Reference $fooNormalizerReference,
        Reference $barNormalizerReference,
        Reference $fooEncoderReference,
        Reference $barEncoderReference
    ) {
        $this->beConstructedWith('custom_serializer', $factory);

        $container->hasDefinition('custom_serializer')->willReturn(true);
        $container->getDefinition('custom_serializer')->willReturn($definition);
        $definition->getClass()->willReturn('%custom_serializer.class%');
        $bag->resolveValue('%custom_serializer.class%')->willReturn('Symfony\Component\Serializer\Serializer');

        $container->findTaggedServiceIds('custom_serializer.normalizer')->willReturn(
            [
                'normalizer.foo' => [[]],
                'normalizer.bar' => [[]],
            ]
        );
        $container->findTaggedServiceIds('custom_serializer.encoder')->willReturn(
            [
                'encoder.foo' => [[]],
                'encoder.bar' => [[]],
            ]
        );

        $factory->createReference('normalizer.foo')->willReturn($fooNormalizerReference);
        $factory->createReference('normalizer.bar')->willReturn($barNormalizerReference);
        $factory->createReference('encoder.foo')->willReturn($fooEncoderReference);
        $factory->createReference('encoder.bar')->willReturn($barEncoderReference);

        $definition->setArguments([[$fooNormalizerReference, $barNormalizerReference], [$fooEncoderReference, $barEncoderReference]])->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_exception_when_trying_to_change_arguments_on_a_non_serializer_service(
        $container,
        $bag,
        Definition $definition
    ) {
        $this->beConstructedWith('unrelated_service');
        $container->hasDefinition('unrelated_service')->willReturn(true);
        $container->getDefinition('unrelated_service')->willReturn($definition);
        $definition->getClass()->willReturn('%unrelated_service.class%');
        $bag->resolveValue('%unrelated_service.class%')->willReturn('spec\Pim\Bundle\TransformBundle\DependencyInjection\Compiler\UnrelatedService');

        $e = new \LogicException('Service "unrelated_service" must be an instance of "Symfony\Component\Serializer\Serializer", got "spec\Pim\Bundle\TransformBundle\DependencyInjection\Compiler\UnrelatedService"');

        $this->shouldThrow($e)->duringProcess($container);
    }
}

class UnrelatedService {}
