<?php

namespace spec\Akeneo\ActivityManager\Bundle\Factory;

use PhpSpec\ObjectBehavior;

class ProjectCreatedNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_returns_factory() {
        $filters = [];

        $this->create($filters)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }
}
