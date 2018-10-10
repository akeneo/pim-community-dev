<?php

namespace Specification\Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;

class InstallStatusManagerSpec extends ObjectBehavior
{
    function let(Registry $doctrine)
    {
        $this->beConstructedWith($doctrine, 'databaseName');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InstallStatusManager::class);
    }
}
