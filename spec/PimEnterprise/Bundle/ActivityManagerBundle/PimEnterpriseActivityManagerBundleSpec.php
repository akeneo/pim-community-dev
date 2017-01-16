<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle;

use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\RegisterProjectRemoverPass;
use PimEnterprise\Bundle\ActivityManagerBundle\PimEnterpriseActivityManagerBundle;
use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimEnterpriseActivityManagerBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimEnterpriseActivityManagerBundle::class);
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
