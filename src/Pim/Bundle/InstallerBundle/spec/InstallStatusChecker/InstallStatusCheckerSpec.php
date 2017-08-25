<?php

namespace spec\Pim\Bundle\InstallerBundle\InstallStatusChecker;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\InstallerBundle\InstallStatusChecker\InstallStatusChecker;

class InstallStatusCheckerSpec extends ObjectBehavior
{
    protected $fs;

    public function let()
    {
        $projectRoot = realpath(__DIR__ . '/../../../..');
        $this->beConstructedWith($projectRoot, '/tmp', 'prod');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InstallStatusChecker::class);
    }
}
