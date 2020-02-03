<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\Twig;

use Akeneo\Platform\Bundle\AnalyticsBundle\Twig\UpdateExtension;
use Akeneo\Platform\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateExtensionSpec extends ObjectBehavior
{
    function let(ConfigManager $configManager, VersionProviderInterface $versionProvider)
    {
        $this->beConstructedWith($configManager, $versionProvider, 'https://updates.akeneo.com/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateExtension::class);
    }

    function it_indicates_if_last_patch_should_be_fetched($configManager, $versionProvider)
    {
        $configManager->get('pim_analytics.version_update')->willReturn(true);
        $versionProvider->isSaaSVersion()->willReturn(false);

        $this->isLastPatchEnabled()->shouldReturn(true);
    }

    function it_indicates_the_last_patch_should_nerver_be_fetched_for_a_saas_version($configManager, $versionProvider)
    {
        $configManager->get('pim_analytics.version_update')->willReturn(true);
        $versionProvider->isSaaSVersion()->willReturn(true);

        $this->isLastPatchEnabled()->shouldReturn(false);
    }

    function it_provides_update_server_url()
    {
        $this->getUpdateServerUrl()->shouldReturn('https://updates.akeneo.com/');
    }
}
