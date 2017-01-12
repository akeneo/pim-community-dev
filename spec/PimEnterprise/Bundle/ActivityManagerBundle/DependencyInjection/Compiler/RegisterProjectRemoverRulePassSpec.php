<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\RegisterProjectRemoverRulePass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterProjectRemoverRulePassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType(RegisterProjectRemoverRulePass::class);
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_registers_project_remover_rules(ContainerBuilder $container, Definition $chainedProjectRemoverRule)
    {
        $container->hasDefinition(RegisterProjectRemoverRulePass::CHAINED_PROJECT_REMOVER)->willReturn(true);
        $container
            ->getDefinition(RegisterProjectRemoverRulePass::CHAINED_PROJECT_REMOVER)
            ->willReturn($chainedProjectRemoverRule);

        $container->findTaggedServiceIds(RegisterProjectRemoverRulePass::RULE_TAG)->willReturn([
            'pimee_activity_manager.project_remover.channel_rule' => [[]],
            'pimee_activity_manager.project_remover.locale_rule' => [[]],
        ]);

        $chainedProjectRemoverRule->setArguments(Argument::that(function ($params) {
            $projectRemoverRules = $params[0];

            return
                $projectRemoverRules[0] instanceof Reference &&
                'pimee_activity_manager.project_remover.channel_rule' === $projectRemoverRules[0]->__toString() &&
                $projectRemoverRules[1] instanceof Reference &&
                'pimee_activity_manager.project_remover.locale_rule' === $projectRemoverRules[1]->__toString();
        }))->shouldBeCalled();

        $this->process($container);
    }

    function it_throws_an_exception_if_there_is_not_registered_chained_calculation_step(ContainerBuilder $container)
    {
        $container->hasDefinition(RegisterProjectRemoverRulePass::CHAINED_PROJECT_REMOVER)->willReturn(false);

        $this->shouldThrow(\LogicException::class)->during('process', [$container]);
    }
}
