<?php

namespace spec\Pim\Bundle\NotificationBundle\Update;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OperatingSystemDataCollectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\OperatingSystemDataCollector');
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\DataCollectorInterface');
    }

    function it_collects_php_version_and_os_version()
    {
        $this->collect()->shouldHaveCount(2);
    }
}
