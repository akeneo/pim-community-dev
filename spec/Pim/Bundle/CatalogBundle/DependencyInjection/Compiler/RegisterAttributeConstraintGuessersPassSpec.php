<?php

namespace spec\Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterAttributeConstraintGuessersPassSpec extends ObjectBehavior {

    function it_does_not_process_attribute_constraint_guesser_when_chained_service_is_not_defined(ContainerBuilder $container)
    {
        $container->hasDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)->willReturn(false);
        $this->process($container)->shouldReturn(null);
    }

    function it_adds_constraint_guessers_in_the_chained_guesser_service(
        ContainerBuilder $container,
        Definition $service
    ) {
        $service->addMethodCall('addConstraintGuesser', Argument::type('array'))->shouldBeCalledTimes(3);

        $container->hasDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)->willReturn(true);
        $container->getDefinition(RegisterAttributeConstraintGuessersPass::SERVICE_CHAINED)->willReturn($service);
        $container->findTaggedServiceIds(RegisterAttributeConstraintGuessersPass::SERVICE_TAG)->willReturn([
            3 => 'test1',
            5 => 'test2',
            7 => 'test3'
        ]);

        $this->process($container);
    }
}
