<?php

namespace spec\Pim\Bundle\InstallerBundle\InstallStatusManager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;

class InstallStatusManagerSpec extends ObjectBehavior
{
    public function let()
    {
        $projectRoot = realpath(__DIR__ . '/../../../..');
        $this->beConstructedWith($projectRoot, '/tmp', 'prod');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InstallStatusManager::class);
    }
}
