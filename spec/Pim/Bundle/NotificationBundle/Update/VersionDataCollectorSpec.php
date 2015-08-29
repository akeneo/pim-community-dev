<?php

namespace spec\Pim\Bundle\NotificationBundle\Update;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VersionDataCollectorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('doctrine/orm');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\VersionDataCollector');
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\DataCollectorInterface');
    }

    function it_collects_pim_version_edition_and_storage_driver()
    {
        $this->collect()->shouldHaveCount(3);
    }
}
