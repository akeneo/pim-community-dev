<?php

namespace spec\Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler;

use Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterCalculationStepPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RegisterCalculationStepPass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_registers_calculation_step_by_priority(
        ContainerBuilder $container,
        Definition $chainCalculationStep
    ) {
        $container->hasDefinition('activity_manager.calculation_step.chained')->willReturn(true);
        $container->getDefinition('activity_manager.calculation_step.chained')->willReturn($chainCalculationStep);

        $container->findTaggedServiceIds('activity_manager.calculation_step')->willReturn([
            'calculation_step.foo' => [['priority' => 10]],
            'calculation_step.bar' => [['priority' => 50]],
        ]);

        $chainCalculationStep->setArguments(Argument::that(function ($params) {
            $calculationSteps = $params[0];
            $result =
                $calculationSteps[0] instanceof Reference &&
                'calculation_step.bar' === $calculationSteps[0]->__toString() &&
                $calculationSteps[1] instanceof Reference &&
                'calculation_step.foo' === $calculationSteps[1]->__toString()
            ;

            return $result;
        }))->shouldBeCalled();

        $this->process($container)->shouldReturn(null);
    }

    function it_throws_an_exception_if_there_is_not_registered_chained_calculation_step(ContainerBuilder $container)
    {
        $container->hasDefinition('activity_manager.calculation_step.chained')->willReturn(false);

        $this->shouldThrow(\LogicException::class)->during('process', [$container]);
    }
}
