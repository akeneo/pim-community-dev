<?php

namespace spec\Pim\Bundle\NotificationBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateExtensionSpec extends ObjectBehavior
{
    function let(ConfigManager $configManager)
    {
        $this->beConstructedWith($configManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Twig\UpdateExtension');
    }

    function it_indicates_if_last_patch_should_be_fetched($configManager)
    {
        $configManager->get('pim_notification.version_update')->willReturn(true);

        $this->isLastPatchEnabled()->shouldReturn(true);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldBe('pim_notification_update_extension');
    }
}
