<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle;

use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\RegisterProjectRemoverPass;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\PimEnterpriseTeamWorkAssistantBundle;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimEnterpriseTeamWorkAssistantBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimEnterpriseTeamWorkAssistantBundle::class);
    }

    function it_is_bundle()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }

    function it_builds_container(ContainerBuilder $container)
    {
        $container->addCompilerPass(Argument::type(DoctrineOrmMappingsPass::class))->shouldBeCalled();
        $container->addCompilerPass(Argument::type(ResolveDoctrineTargetModelPass::class))->shouldBeCalled();
        $container->addCompilerPass(Argument::type(RegisterCalculationStepPass::class))->shouldBeCalled();
        $container->addCompilerPass(Argument::type(RegisterProjectRemoverPass::class))->shouldBeCalled();

        $this->build($container);
    }
}
