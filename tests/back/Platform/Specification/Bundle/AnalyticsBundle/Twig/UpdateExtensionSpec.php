<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\Twig;

use Akeneo\Platform\Bundle\AnalyticsBundle\Twig\UpdateExtension;
use Akeneo\Platform\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateExtensionSpec extends ObjectBehavior
{
    function let(ConfigManager $configManager)
    {
        $this->beConstructedWith($configManager, 'https://updates.akeneo.com/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateExtension::class);
    }

    function it_indicates_if_last_patch_should_be_fetched($configManager)
    {
        $configManager->get('pim_analytics.version_update')->willReturn(true);

        $this->isLastPatchEnabled()->shouldReturn(true);
    }

    function it_provides_update_server_url()
    {
        $this->getUpdateServerUrl()->shouldReturn('https://updates.akeneo.com/');
    }
}
