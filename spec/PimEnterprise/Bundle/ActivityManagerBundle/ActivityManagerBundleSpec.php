<?php

namespace spec\Akeneo\ActivityManager\Bundle;

use Akeneo\ActivityManager\Bundle\ActivityManagerBundle;
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
        $container->addCompilerPass(
            Argument::type('Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')
        )->shouldBeCalled();

        $container->addCompilerPass(
            Argument::type('Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass')
        )->shouldBeCalled();

        $this->build($container);
    }
}
