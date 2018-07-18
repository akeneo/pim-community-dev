<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\AkeneoPimTeamworkAssistantExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimEnterpriseTeamworkAssistantExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AkeneoPimTeamworkAssistantExtension::class);
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
