<?php

namespace spec\Akeneo\ActivityManager\Bundle;

use Akeneo\ActivityManager\Bundle\ActivityManagerBundle;
use Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ActivityManagerBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ActivityManagerBundle::class);
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

        $this->build($container);
    }
}
