<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSerializerPassSpec extends ObjectBehavior
{
    function let(ContainerBuilder $container, ParameterBag $bag)
    {
        $container->getParameterBag()->willReturn($bag);

        $this->beConstructedWith('pim_serializer');
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
                        RegisterSerializerPass::class
                    )
                )
            )
            ->duringProcess($container);
    }

    function it_sets_arguments_of_pim_serializer_with_tagged_normalizers_and_encoders_by_default(
        $container,
        $bag,
        Definition $definition
    ) {
        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->getDefinition('pim_serializer')->willReturn($definition);
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

        $definition->setArguments(Argument::that(function ($params) {
            $result =
                $params[0][0] instanceof Reference &&
                'normalizer.foo' === $params[0][0]->__toString() &&
                $params[0][1] instanceof Reference &&
                'normalizer.bar' === $params[0][1]->__toString() &&
                $params[1][0] instanceof Reference &&
                'encoder.baz' === $params[1][0]->__toString()
            ;

            return $result;
        }))->shouldBeCalled();

        $this->process($container);
    }

    function it_sorts_arguments_by_priority($container, $bag, Definition $definition)
    {
        $container->hasDefinition('pim_serializer')->willReturn(true);
        $container->getDefinition('pim_serializer')->willReturn($definition);
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

        $definition->setArguments(Argument::that(function ($params) {
            $result =
                $params[0][0] instanceof Reference &&
                'normalizer.bar' === $params[0][0]->__toString() &&
                $params[0][1] instanceof Reference &&
                'normalizer.foo' === $params[0][1]->__toString() &&
                $params[1][0] instanceof Reference &&
                'encoder.baz' === $params[1][0]->__toString() &&
                $params[1][1] instanceof Reference &&
                'encoder.qux' === $params[1][1]->__toString()
            ;

            return $result;
        }))->shouldBeCalled();

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
