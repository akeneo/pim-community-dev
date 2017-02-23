<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\RegisterProjectRemoverPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterProjectRemoverPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType(RegisterProjectRemoverPass::class);
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_registers_project_remover_rules(ContainerBuilder $container, Definition $chainedProjectRemover)
    {
        $container->hasDefinition(RegisterProjectRemoverPass::CHAINED_PROJECT_REMOVER)->willReturn(true);
        $container
            ->getDefinition(RegisterProjectRemoverPass::CHAINED_PROJECT_REMOVER)
            ->willReturn($chainedProjectRemover);

        $container->findTaggedServiceIds(RegisterProjectRemoverPass::REMOVER_TAG)->willReturn([
            'pimee_team_work_assistant.project_remover.channel' => [[]],
            'pimee_team_work_assistant.project_remover.locale' => [[]],
        ]);

        $chainedProjectRemover->setArguments(Argument::that(function ($params) {
            $projectRemovers = $params[0];

            return
                $projectRemovers[0] instanceof Reference &&
                'pimee_team_work_assistant.project_remover.channel' === $projectRemovers[0]->__toString() &&
                $projectRemovers[1] instanceof Reference &&
                'pimee_team_work_assistant.project_remover.locale' === $projectRemovers[1]->__toString();
        }))->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_an_exception_if_there_is_not_registered_chained_calculation_step(ContainerBuilder $container)
    {
        $container->hasDefinition(RegisterProjectRemoverPass::CHAINED_PROJECT_REMOVER)->willReturn(false);

        $this->shouldThrow(\LogicException::class)->during('process', [$container]);
    }
}
