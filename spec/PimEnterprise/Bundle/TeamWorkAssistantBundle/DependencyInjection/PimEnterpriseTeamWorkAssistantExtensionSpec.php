<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection;

use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\PimEnterpriseTeamWorkAssistantExtension;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimEnterpriseTeamWorkAssistantExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimEnterpriseTeamWorkAssistantExtension::class);
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
