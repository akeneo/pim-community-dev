<?php

namespace spec\Pim\Bundle\TransformBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

class SerializerPassSpec extends ObjectBehavior
{
    function let(ContainerBuilder $container, ParameterBag $bag, ReferenceFactory $factory)
    {
        $container->getParameterBag()->willReturn($bag);

        $this->beConstructedWith('pim_serializer', $factory);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_throws_an_exception_if_called_on_an_unknown_serializer_service_id($container)
    {
        $container->hasDefinition('pim_serializer')->willReturn(false);

        $this
            ->shouldThrow(
                new \LogicException(
                    sprintf(
                        'Resolver "%s" is called on an incorrect serializer service id',
                        'Pim\Bundle\TransformBundle\DependencyInjection\Compiler\SerializerPass'
                    )
                )
            )
            ->duringProcess($container);
    }

    function it_sets_arguments_of_pim_serializer_with_tagged_normalizers_and_denormalizers_by_default(
        $container,
        $bag,
        $factory,
        Definition $definition,
        Reference $fooNormalizer,
        Reference $barNormalizer,
        Reference $bazEncoder
    ) {
        // Mock service definition
        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->getDefinition('pim_serializer')->willReturn($definition);
        $definition->getClass()->willReturn('%pim_serializer.class%');
        $bag->resolveValue('%pim_serializer.class%')->willReturn('Symfony\Component\Serializer\Serializer');

        $container->findTaggedServiceIds('pim_serializer.normalizer')->willReturn(
            [
                'normalizer.foo' => [[]],
                'normalizer.bar' => [[]]
            ]
        );
        $container->findTaggedServiceIds('pim_serializer.encoder')->willReturn(
            [
                'encoder.baz' => [[]]
            ]
        );

        $factory->createReference('normalizer.foo')->willReturn($fooNormalizer);
        $factory->createReference('normalizer.bar')->willReturn($barNormalizer);
        $factory->createReference('encoder.baz')->willReturn($bazEncoder);

        $definition->setArguments(
            [
                [$fooNormalizer, $barNormalizer],
                [$bazEncoder]
            ]
        )->shouldBeCalled();

        $this->process($container);
    }

    function it_sorts_arguments_by_priority(
        $container,
        $bag,
        $factory,
        Definition $definition,
        Reference $fooNormalizer,
        Reference $barNormalizer,
        Reference $bazEncoder,
        Reference $quxEncoder
    ) {
        // Mock service definition
        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->getDefinition('pim_serializer')->willReturn($definition);
        $definition->getClass()->willReturn('%pim_serializer.class%');
        $bag->resolveValue('%pim_serializer.class%')->willReturn('Symfony\Component\Serializer\Serializer');

        $container->findTaggedServiceIds('pim_serializer.normalizer')->willReturn(
            [
                'normalizer.foo' => [['priority' => 10]],
                'normalizer.bar' => [['priority' => 50]]
            ]
        );
        $container->findTaggedServiceIds('pim_serializer.encoder')->willReturn(
            [
                'encoder.baz' => [[]],
                'encoder.qux' => [['priority' => 90]]
            ]
        );

        $factory->createReference('normalizer.foo')->willReturn($fooNormalizer);
        $factory->createReference('normalizer.bar')->willReturn($barNormalizer);
        $factory->createReference('encoder.baz')->willReturn($bazEncoder);
        $factory->createReference('encoder.qux')->willReturn($quxEncoder);

        $definition->setArguments(
            [
                [$barNormalizer, $fooNormalizer],
                [$bazEncoder, $quxEncoder]
            ]
        )->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_an_exception_if_no_encoder_nor_normalizer_tag_services($container)
    {
        // Mock service definition
        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->findTaggedServiceIds('pim_serializer.normalizer')->willReturn([]);

        $this
            ->shouldThrow(
                new \RuntimeException(
                    'You must tag at least one service as "pim_serializer.normalizer" to use the Serializer service'
                )
            )
            ->duringProcess($container);
    }
}
