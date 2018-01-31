<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;

class UpdateExtensionSpec extends ObjectBehavior
{
    function let(ConfigManager $configManager)
    {
        $this->beConstructedWith($configManager, 'https://updates.akeneo.com/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\Twig\UpdateExtension');
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
