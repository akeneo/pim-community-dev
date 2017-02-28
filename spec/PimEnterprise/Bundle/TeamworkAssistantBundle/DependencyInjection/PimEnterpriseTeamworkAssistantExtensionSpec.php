<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\DependencyInjection;

use PimEnterprise\Bundle\TeamworkAssistantBundle\DependencyInjection\PimEnterpriseTeamworkAssistantExtension;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimEnterpriseTeamworkAssistantExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimEnterpriseTeamworkAssistantExtension::class);
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
