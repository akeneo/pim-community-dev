<?php

namespace spec\Akeneo\ActivityManager\Bundle;

use Akeneo\ActivityManager\Bundle\ActivityManagerBundle;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PhpSpec\ObjectBehavior;
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

    function it_builds_container(ContainerBuilder $container, DoctrineOrmMappingsPass $doctrineOrmMappingsPass)
    {
        $container->addCompilerPass($doctrineOrmMappingsPass)->shouldBeCalled();

        $this->build($container);
    }
}
