<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection;

use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\PimEnterpriseActivityManagerExtension;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimEnterpriseActivityManagerExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimEnterpriseActivityManagerExtension::class);
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
