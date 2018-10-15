<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\Compiler\RegisterProjectRemoverPass;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\AkeneoPimTeamworkAssistantBundle;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkeneoPimTeamworkAssistantBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AkeneoPimTeamworkAssistantBundle::class);
    }

    function it_is_bundle()
    {
        $this->shouldHaveType(Bundle::class);
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
