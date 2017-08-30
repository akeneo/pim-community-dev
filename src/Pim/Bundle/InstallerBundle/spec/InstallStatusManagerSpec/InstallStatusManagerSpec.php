<?php

namespace spec\Pim\Bundle\InstallerBundle\InstallStatusManager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;

class InstallStatusManagerSpec extends ObjectBehavior
{
    function let(Registry $doctrine, string $databaseName)
    {
        $this->beConstructedWith($projectRoot, $databaseName);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InstallStatusManager::class);
    }
}
