<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\AkeneoPimTeamworkAssistantExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AkeneoPimTeamworkAssistantExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AkeneoPimTeamworkAssistantExtension::class);
    }

    function it_is_an_extension()
    {
        $this->shouldHaveType(Extension::class);
    }
}
