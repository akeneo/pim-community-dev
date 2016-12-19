<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection;

use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\ActivityManagerExtension;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ActivityManagerExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ActivityManagerExtension::class);
    }

    function it_is_an_extension()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\DependencyInjection\Extension');
    }

    function it_loads_extension(ContainerBuilder $containerBuilder)
    {
        $this->load([], $containerBuilder);
    }
}
