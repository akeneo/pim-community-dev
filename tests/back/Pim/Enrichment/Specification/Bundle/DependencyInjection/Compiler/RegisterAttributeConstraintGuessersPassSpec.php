<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterAttributeConstraintGuessersPassSpec extends ObjectBehavior
{
    public function it_is_a_compiler_pass()
    {
        $this->shouldImplement('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_does_not_process_attribute_constraint_guesser_when_chained_service_is_not_defined(
        ContainerBuilder $container,
        Definition $service
    ) {
        $container->hasDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)
            ->willReturn(false)
            ->shouldBeCalled();

        $container->getDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)
            ->shouldNotBeCalled();

        $container->findTaggedServiceIds(RegisterAttributeConstraintGuessersPass::SERVICE_TAG)
            ->shouldNotBeCalled();

        $service->addMethodCall('addConstraintGuesser', Argument::type('array'))
            ->shouldNotBeCalled();

        $this->process($container)
            ->shouldReturn(null);
    }

    function it_adds_constraint_guessers_in_the_chained_guesser_service(
        ContainerBuilder $container,
        Definition $service
    ) {
        $service->addMethodCall('addConstraintGuesser', Argument::type('array'))
            ->shouldBeCalledTimes(3);

        $container->hasDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)
            ->willReturn(true)
            ->shouldBeCalled();

        $container->getDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)
            ->willReturn($service)
            ->shouldBeCalled();

        $container->findTaggedServiceIds(RegisterAttributeConstraintGuessersPass::SERVICE_TAG)
            ->willReturn([
                3 => 'test1',
                5 => 'test2',
                7 => 'test3'
            ])
            ->shouldBeCalled();

        $this->process($container);
    }
}
